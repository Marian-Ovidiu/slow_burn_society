<?php

namespace Classes;

class StripeWebhookController
{
    /** Entry point: POST /webhooks/stripe */
    public function handle()
    {
        header('Content-Type: application/json');

        $payload = @file_get_contents('php://input');
        $sig     = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        $whsec = $this->getWebhookSecret();
        if (!$whsec) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Webhook secret mancante']);
            return;
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $whsec);
        } catch (\UnexpectedValueException $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
            return;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid signature']);
            return;
        }

        $type = $event['type'] ?? '';
        switch ($type) {
            case 'payment_intent.succeeded':
                $pi = $event['data']['object'] ?? null;
                if ($pi && !empty($pi['id'])) {
                    $this->onPaymentIntentSucceeded($pi); // passiamo l’oggetto completo
                }
                break;

            case 'payment_intent.payment_failed':
                $pi = $event['data']['object'] ?? null;
                if ($pi && !empty($pi['id'])) {
                    $this->updateIntentStatus($pi['id'], 'payment_failed');
                }
                break;

            case 'payment_intent.canceled':
                $pi = $event['data']['object'] ?? null;
                if ($pi && !empty($pi['id'])) {
                    $this->updateIntentStatus($pi['id'], 'canceled');
                }
                break;
        }

        echo json_encode(['ok' => true]);
    }

    /* ===================== SUCCESS HANDLER ====================== */

    private function onPaymentIntentSucceeded(array $pi): void
    {
        global $wpdb;
        $intentId  = $pi['id'];
        $tablePI   = $wpdb->prefix . 'sbs_payment_intents';
        // 1) Upsert snapshot DAL PAYMENTINTENT (fonte-verità)
        $meta      = (array)($pi['metadata'] ?? []);

        $cartToken = (string)($meta['cart_token'] ?? '');
        $email     = sanitize_email($pi['receipt_email'] ?? ($meta['email'] ?? ''));
        $currency  = strtoupper((string)($pi['currency'] ?? 'eur')); // es. 'eur'
        $amountTotal = (int)($pi['amount'] ?? 0);

        // Totali dai metadata se presenti (altrimenti fallback)
        $amountSubtotal = isset($meta['amount_subtotal']) ? (int)$meta['amount_subtotal'] : $amountTotal;
        $amountShipping = isset($meta['amount_shipping']) ? (int)$meta['amount_shipping'] : 0;
        $amountDiscount = isset($meta['amount_discount']) ? (int)$meta['amount_discount'] : 0;
        $amountTax      = isset($meta['amount_tax'])      ? (int)$meta['amount_tax']      : 0;

        // Items: presi dai metadata (minificati alla creazione)
        $items = [];
        if (!empty($meta['items_json'])) {
            $items = json_decode((string)$meta['items_json'], true);
            if (!is_array($items)) $items = [];
        }

        // Upsert riga PI: stato direttamente 'paid' (write-after-success)
        $wpdb->replace($tablePI, [
            'intent_id'       => $intentId,
            'cart_token'      => $cartToken,
            'status'          => 'paid',
            'items_json'      => wp_json_encode($this->normalizeItems($items), JSON_UNESCAPED_UNICODE),
            'amount_subtotal' => $amountSubtotal,
            'amount_shipping' => $amountShipping,
            'amount_discount' => $amountDiscount,
            'amount_tax'      => $amountTax,
            'amount_total'    => $amountTotal,
            'currency'        => strtoupper($currency),
            'email'           => $email ?: null,
            'user_id'         => get_current_user_id() ?: null,
            'expires_at'      => gmdate('Y-m-d H:i:s', time() + 10 * 60),
            'client_ip'       => $this->clientIp(),
            'user_agent'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 255),
            'referrer'        => substr($_SERVER['HTTP_REFERER'] ?? '', 512),
            'utm_json'        => null,
            'created_at'      => current_time('mysql'),
            'updated_at'      => current_time('mysql'),
        ], [
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ]);

        // 2) Decremento stock (usando items ora presenti)
        $decrements = $this->expandDecrements($this->normalizeItems($items));
        foreach ($decrements as $pid => $qty) {
            $pid = (int)$pid;
            $qty = max(0, (int)$qty);
            if ($pid > 0 && $qty > 0) {
                $this->decrementStock($pid, $qty);
            }
        }

        // 3) Event log (opzionale)
        $this->logCartEvent(
            $cartToken,
            'stock_decremented',
            ['intent_id' => $intentId, 'decrements' => $decrements],
            $intentId
        );

        // 4) Invio email (leggerà dal DB appena scritto)
        try {
            $mailer = new \Classes\OrderMailer();
            $mailer->sendReceiptForIntent($intentId);
        } catch (\Throwable $e) {
            error_log('OrderMailer error for ' . $intentId . ': ' . $e->getMessage());
        }
    }

    /* ================== ITEMS / STOCK HELPERS =================== */

    private function normalizeItems(array $items): array
    {
        return array_map(function ($it) {
            return [
                'id'    => $it['id']    ?? null,
                'kitId' => $it['kitId'] ?? null,
                'qty'   => (int)($it['qty'] ?? 1),
            ];
        }, $items);
    }

    private function expandDecrements(array $items): array
    {
        $result = []; // product_id => qty to subtract
        foreach ($items as $it) {
            $qty = max(1, (int)($it['qty'] ?? 1));

            if (!empty($it['id'])) {
                $pid = (int)$it['id'];
                if ($pid > 0) $result[$pid] = ($result[$pid] ?? 0) + $qty;
                continue;
            }

            if (!empty($it['kitId'])) {
                $kitId = (int)$it['kitId'];
                if ($kitId > 0) {
                    $composition = $this->getKitComposition($kitId);
                    foreach ($composition as $pid => $perKitQty) {
                        $pid = (int)$pid;
                        $perKitQty = max(1, (int)$perKitQty);
                        $result[$pid] = ($result[$pid] ?? 0) + ($perKitQty * $qty);
                    }
                }
            }
        }
        return $result;
    }

    private function getKitComposition(int $kitId): array
    {
        // 1) JSON
        $json = get_post_meta($kitId, 'kit_items_json', true);
        if (!empty($json)) {
            $arr = json_decode($json, true);
            if (is_array($arr)) {
                $out = [];
                foreach ($arr as $row) {
                    $pid = (int)($row['id'] ?? 0);
                    $q   = max(1, (int)($row['qty'] ?? 1));
                    if ($pid > 0) $out[$pid] = ($out[$pid] ?? 0) + $q;
                }
                if ($out) return $out;
            }
        }
        // 2) ACF repeater
        $acfRows = get_post_meta($kitId, 'kit_items', true);
        if (is_array($acfRows) && !empty($acfRows)) {
            $out = [];
            foreach ($acfRows as $row) {
                $pid = (int)($row['product'] ?? $row['id'] ?? 0);
                $q   = max(1, (int)($row['qty'] ?? 1));
                if ($pid > 0) $out[$pid] = ($out[$pid] ?? 0) + $q;
            }
            if ($out) return $out;
        }
        // 3) ACF lista semplice
        if (function_exists('get_field')) {
            $list = (array)get_field('prodotti', $kitId) ?: [];
            $out = [];
            foreach ($list as $p) {
                $pid = is_object($p) ? (int)($p->ID ?? 0) : (int)$p;
                if ($pid > 0) $out[$pid] = ($out[$pid] ?? 0) + 1;
            }
            if ($out) return $out;
        }
        return [];
    }

    private function decrementStock(int $productId, int $qty): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_inventory';

        $current = $wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM {$table} WHERE product_id = %d",
            $productId
        ));

        if ($current === null) {
            $raw   = get_post_meta($productId, 'disponibilita', true);
            $base  = is_numeric($raw) ? (int)$raw : 0;
            $new   = max(0, $base - $qty);

            $wpdb->replace($table, [
                'product_id' => $productId,
                'stock'      => $new,
                'updated_at' => current_time('mysql'),
            ], ['%d', '%d', '%s']);

            if (function_exists('sbs_update_product_disponibilita_field')) {
                sbs_update_product_disponibilita_field($productId, $new);
            } else {
                update_post_meta($productId, 'disponibilita', $new);
            }
            return;
        }

        $wpdb->query($wpdb->prepare(
            "UPDATE {$table} SET stock = GREATEST(0, stock - %d), updated_at = %s WHERE product_id = %d",
            $qty,
            current_time('mysql'),
            $productId
        ));

        $newStock = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM {$table} WHERE product_id = %d",
            $productId
        ));

        if (function_exists('sbs_update_product_disponibilita_field')) {
            sbs_update_product_disponibilita_field($productId, $newStock);
        } else {
            update_post_meta($productId, 'disponibilita', $newStock);
        }
    }

    /* ============================ UTILS ========================= */

    private function updateIntentStatus(string $intentId, string $status): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_payment_intents';
        $wpdb->update(
            $table,
            ['status' => $status, 'updated_at' => current_time('mysql')],
            ['intent_id' => $intentId],
            ['%s', '%s'],
            ['%s']
        );
    }

    private function getWebhookSecret(): ?string
    {
        $candidates = ['STRIPE_WEBHOOK_SECRET', 'STRIPE_WHSEC'];
        foreach ($candidates as $key) {
            $v = function_exists('my_env') ? my_env($key) : getenv($key);
            if ($v) return $v;
        }
        return null;
    }

    private function logCartEvent(string $cartToken, string $type, array $meta = [], ?string $intentId = null): void
    {
        if (!$cartToken && !$intentId) return;
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'sbs_cart_events', [
            'cart_token' => substr($cartToken ?? '', 0, 36),
            'intent_id'  => $intentId ? substr($intentId, 0, 64) : null,
            'type'       => substr($type, 0, 32),
            'item_id'    => null,
            'qty'        => null,
            'meta_json'  => $meta ? wp_json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'client_ip'  => $this->clientIp(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'created_at' => current_time('mysql'),
        ], ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']);
    }

    private function clientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = explode(',', $_SERVER[$k])[0];
                return trim($ip);
            }
        }
        return '';
    }
}
