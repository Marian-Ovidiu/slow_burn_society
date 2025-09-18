<?php

namespace Controllers;

use Core\Bases\BaseController;

class PaymentsController extends BaseController
{
    public function createPaymentIntent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        header('Content-Type: application/json');

        $raw     = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: [];

        $items     = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $cartToken = isset($payload['cart_token']) ? \preg_replace('/[^a-f0-9\-]/i', '', $payload['cart_token']) : null;
        $email     = isset($payload['email']) ? \sanitize_email($payload['email']) : null; // retro-compat
        $customer  = is_array($payload['customer'] ?? null) ? $payload['customer'] : [];
        $shipping  = is_array($payload['shipping'] ?? null) ? $payload['shipping'] : [];

        // normalizza/estrai customer
        $firstName = \sanitize_text_field($customer['first_name'] ?? '');
        $lastName  = \sanitize_text_field($customer['last_name'] ?? '');
        $custEmail = \sanitize_email($customer['email'] ?? ($email ?? ''));

        // normalizza/estrai shipping
        $street    = \sanitize_text_field($shipping['street'] ?? '');
        $streetNo  = \sanitize_text_field($shipping['street_no'] ?? '');
        $city      = \sanitize_text_field($shipping['city'] ?? '');
        $cap       = \sanitize_text_field($shipping['cap'] ?? '');
        $province  = \strtoupper(\sanitize_text_field($shipping['province'] ?? ''));
        $country   = \sanitize_text_field($shipping['country'] ?? 'IT');

        if (!$items || !$cartToken) {
            echo json_encode(['success' => false, 'data' => ['message' => 'Items o cart_token mancanti']]);
            return;
        }

        // 1) Calcolo importi lato server (centesimi)
        $calc = $this->calculateAmounts($items);

        try {
            // 2) Stripe client
            $sk = $this->getStripeSecretKey();
            if (!$sk) {
                throw new \Exception('Stripe Secret Key mancante');
            }
            $sc = new \Stripe\StripeClient($sk);

            // Normalizzo items una sola volta
            $normalizedItems = $this->normalizeItems($items);

            // fingerprint idempotenza: solo stato carrello
            $fingerprint = [
                'cart_token' => $cartToken,
                'amount'     => (int)$calc['amount_total'],
                'items'      => array_map(function ($it) {
                    return [
                        'id'    => $it['id']    ? (int)$it['id']    : null,
                        'kitId' => $it['kitId'] ? (int)$it['kitId'] : null,
                        'qty'   => (int)$it['qty'],
                    ];
                }, $normalizedItems),
            ];

            $idempotencyKey = 'pi:' . hash('sha256', json_encode($fingerprint));

            // Metadata items: tenta JSON, se troppo lungo usa formato compatto "p:123x2,k:45x1"
            $itemsJson = wp_json_encode($normalizedItems, JSON_UNESCAPED_UNICODE);
            $itemsMeta = [];
            if (strlen($itemsJson) <= 450) {
                $itemsMeta['items_json'] = $itemsJson;
            } else {
                $compact = [];
                foreach ($normalizedItems as $it) {
                    $compact[] = ($it['id'] ? ('p:' . (int)$it['id']) : ('k:' . (int)$it['kitId'])) . 'x' . (int)$it['qty'];
                }
                $itemsMeta['items_compact'] = implode(',', $compact);
            }

            // 3) Prepara payload PI con metadata + shipping + receipt_email
            $shippingLine1 = \trim($street . ' ' . $streetNo);
            $customerName  = \trim($firstName . ' ' . $lastName);

            $piParams = [
                'amount'   => $calc['amount_total'],
                'currency' => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => array_merge([
                    'cart_token'       => $cartToken,
                    'email'            => $custEmail ?: '',
                    'amount_subtotal'  => (string)$calc['amount_subtotal'],
                    'amount_shipping'  => (string)$calc['amount_shipping'],
                    'amount_discount'  => (string)$calc['amount_discount'],
                    'amount_tax'       => (string)$calc['amount_tax'],
                    'amount_total'     => (string)$calc['amount_total'],
                    'currency'         => 'EUR',
                    'first_name'       => $firstName,
                    'last_name'        => $lastName,
                    'ship_line1'       => $shippingLine1,
                    'ship_city'        => $city,
                    'ship_postal'      => $cap,
                    'ship_state'       => $province,
                    'ship_country'     => $country,
                ], $itemsMeta),
            ];

            if ($custEmail) {
                // utile per inviare ricevuta da Stripe
                $piParams['receipt_email'] = $custEmail;
            }

            // se ho uno shipping completo, lo passo a Stripe (opzionale ma comodo)
            if ($shippingLine1 || $city || $cap || $province || $country) {
                $piParams['shipping'] = [
                    'name'    => $customerName ?: $custEmail ?: 'Cliente',
                    'address' => [
                        'line1'       => $shippingLine1 ?: null,
                        'city'        => $city ?: null,
                        'postal_code' => $cap ?: null,
                        'state'       => $province ?: null,
                        'country'     => $country ?: 'IT',
                    ],
                ];
            }

            // 4) Crea PI su Stripe
            $pi = $sc->paymentIntents->create($piParams, ['idempotency_key' => $idempotencyKey]);

            $intentId     = $pi->id;
            $clientSecret = $pi->client_secret;

            // 5) Audit sintetico
            $this->logCartEvent($cartToken, 'pi_created', [
                'intent_id'    => $intentId,
                'amount_total' => $calc['amount_total'],
                'currency'     => 'EUR'
            ], $intentId);

            // 6) Audit per-item (riempie item_id e qty nelle righe di log)
            foreach ($normalizedItems as $it) {
                $itemId = $it['id'] ? (string)$it['id'] : ($it['kitId'] ? ('kit:' . (int)$it['kitId']) : null);
                $this->logCartEvent(
                    $cartToken,
                    'pi_item',
                    [],
                    $intentId,
                    $itemId,
                    (int)($it['qty'] ?? 1)
                );
            }

            echo json_encode(['success' => true, 'data' => [
                'intentId'     => $intentId,
                'clientSecret' => $clientSecret
            ]]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'data' => ['message' => $e->getMessage()]], JSON_UNESCAPED_UNICODE);
        }
    }

    /* -------------------- CONFIG SPEDIZIONE -------------------- */
    private const FREE_SHIP_THRESHOLD_CENTS = 3500;  // 35,00 €
    private const SHIPPING_FEE_CENTS        = 499;   // 4,99 €

    /* -------------------- CACHE PREZZI (per richiesta) -------------------- */
    private $priceCache = [];

    /** Calcola importi in centesimi (server = fonte di verità) */
    private function calculateAmounts(array $items): array
    {
        $items = $this->normalizeItems($items); // 👈 normalizza SEMPRE qui
        $subtotal = 0;

        foreach ($items as $it) {
            $unit = $this->getUnitPriceCents($it);   // lookup server
            $qty  = \max(1, (int)($it['qty'] ?? 1));
            if ($unit < 0) $unit = 0;
            $subtotal += $unit * $qty;
        }

        $shipping = ($subtotal >= self::FREE_SHIP_THRESHOLD_CENTS) ? 0 : self::SHIPPING_FEE_CENTS;
        $discount = 0;
        $tax      = 0;
        $total    = $subtotal + $shipping - $discount + $tax;

        return [
            'amount_subtotal' => (int)$subtotal,
            'amount_shipping' => (int)$shipping,
            'amount_discount' => (int)$discount,
            'amount_tax'      => (int)$tax,
            'amount_total'    => (int)$total,
        ];
    }


    /** ID/kitId → prezzo in centesimi (da meta WP + fallback) */
    private function getUnitPriceCents(array $it): int
    {
        // Hook override
        $filtered = \apply_filters('sbs_get_unit_price_cents', null, $it);
        if ($filtered !== null && \is_numeric($filtered)) {
            return \max(0, (int)$filtered);
        }

        // Tolleranza: se "id" arriva come "kit:123", trattalo come kitId=123
        $id    = $it['id']    ?? null;
        $kitId = $it['kitId'] ?? null;

        if (\is_string($id) && \strpos($id, 'kit:') === 0) {
            $kitId = \substr($id, 4);
            $id    = null;
        }

        if (!empty($kitId)) {
            return $this->getKitPriceCents((int)$kitId);
        }
        if (!empty($id)) {
            return $this->getProductPriceCents((int)$id);
        }
        return 0;
    }


    /** Prezzo prodotto (post ID) in centesimi */
    private function getProductPriceCents(int $postId): int
    {
        if ($postId <= 0) return 0;

        if (isset($this->priceCache['product'][$postId])) {
            return $this->priceCache['product'][$postId];
        }

        // 1) meta già in cents
        foreach (['price_cents', '_price_cents', 'sbs_price_cents'] as $k) {
            $val = \get_post_meta($postId, $k, true);
            if ($val !== '' && $val !== null) {
                $c = $this->toCents($val, true);
                if ($c !== null) return $this->priceCache['product'][$postId] = $c;
            }
        }
        // 2) meta in euro
        foreach (['price', '_price', 'prezzo', 'sbs_price', '_regular_price', '_sale_price'] as $k) {
            $val = \get_post_meta($postId, $k, true);
            if ($val !== '' && $val !== null) {
                $c = $this->toCents($val, false);
                if ($c !== null) return $this->priceCache['product'][$postId] = $c;
            }
        }
        // 3) fallback via filtro
        $fallback = \apply_filters('sbs_product_price_cents_fallback', null, $postId);
        if ($fallback !== null && \is_numeric($fallback)) {
            return $this->priceCache['product'][$postId] = \max(0, (int)$fallback);
        }
        return $this->priceCache['product'][$postId] = 0;
    }

    /** Prezzo kit (post ID) in centesimi */
    private function getKitPriceCents(int $kitId): int
    {
        if ($kitId <= 0) return 0;
        if (isset($this->priceCache['kit'][$kitId])) {
            return $this->priceCache['kit'][$kitId];
        }

        foreach (['kit_price_cents', 'price_cents', 'sbs_price_cents'] as $k) {
            $val = \get_post_meta($kitId, $k, true);
            if ($val !== '' && $val !== null) {
                $c = $this->toCents($val, true);
                if ($c !== null) return $this->priceCache['kit'][$kitId] = $c;
            }
        }
        foreach (['kit_price', 'price', 'prezzo', 'sbs_price', '_regular_price', '_sale_price'] as $k) {
            $val = \get_post_meta($kitId, $k, true);
            if ($val !== '' && $val !== null) {
                $c = $this->toCents($val, false);
                if ($c !== null) return $this->priceCache['kit'][$kitId] = $c;
            }
        }

        // Composizione kit (JSON o ACF repeater)
        $json = \get_post_meta($kitId, 'kit_items_json', true);
        if (!empty($json)) {
            $arr = \json_decode($json, true);
            if (\is_array($arr)) {
                $sum = 0;
                foreach ($arr as $row) {
                    $pid = (int)($row['id'] ?? 0);
                    $qty = \max(1, (int)($row['qty'] ?? 1));
                    if ($pid > 0) $sum += $this->getProductPriceCents($pid) * $qty;
                }
                return $this->priceCache['kit'][$kitId] = $sum;
            }
        }

        $acfRows = \get_post_meta($kitId, 'kit_items', true);
        if (\is_array($acfRows) && !empty($acfRows)) {
            $sum = 0;
            foreach ($acfRows as $row) {
                $pid = (int)($row['product'] ?? $row['id'] ?? 0);
                $qty = \max(1, (int)($row['qty'] ?? 1));
                if ($pid > 0) $sum += $this->getProductPriceCents($pid) * $qty;
            }
            return $this->priceCache['kit'][$kitId] = $sum;
        }

        $fallback = \apply_filters('sbs_kit_price_cents_fallback', null, $kitId);
        if ($fallback !== null && \is_numeric($fallback)) {
            return $this->priceCache['kit'][$kitId] = \max(0, (int)$fallback);
        }
        return $this->priceCache['kit'][$kitId] = 0;
    }

    /** Converte un valore prezzo a centesimi */
    private function toCents($value, bool $alreadyCents = false): ?int
    {
        if ($value === null || $value === '') return null;

        if ($alreadyCents) {
            if (\is_numeric($value)) return \max(0, (int)$value);
            $digits = \preg_replace('/[^\d\-]/', '', (string)$value);
            if ($digits === '' || $digits === '-') return null;
            return \max(0, (int)$digits);
        }

        $str = \trim((string)$value);
        $str = \preg_replace('/[^0-9\.,\-]/', '', $str);
        if ($str === '' || $str === '-') return null;

        if (\strpos($str, ',') !== false && \strpos($str, '.') !== false) {
            if (\substr_count($str, '.') > \substr_count($str, ',')) {
                $str = \str_replace('.', '', $str);
                $str = \str_replace(',', '.', $str);
            } else {
                $str = \str_replace(',', '', $str);
            }
        } else {
            $str = \str_replace(',', '.', $str);
        }

        $float = (float)$str;
        $cents = (int)\round($float * 100);
        return \max(0, $cents);
    }

    private function normalizeItems(array $items): array
    {
        $out = [];
        foreach ($items as $it) {
            $rawId    = $it['id']    ?? null;
            $rawKitId = $it['kitId'] ?? null;

            // Se "id" arriva come "kit:123", spostalo in kitId
            if (\is_string($rawId) && \strpos($rawId, 'kit:') === 0) {
                $rawKitId = \substr($rawId, 4);
                $rawId    = null;
            }

            // Togli l'eventuale prefisso "kit:" anche su kitId per sicurezza
            if (\is_string($rawKitId) && \strpos($rawKitId, 'kit:') === 0) {
                $rawKitId = \substr($rawKitId, 4);
            }

            $pid   = \is_numeric($rawId)    ? (int)$rawId    : null;
            $kid   = \is_numeric($rawKitId) ? (int)$rawKitId : null;
            $qty   = \max(1, (int)($it['qty'] ?? 1));

            $out[] = [
                'id'    => $pid ?: null,
                'kitId' => $kid ?: null,
                'qty'   => $qty,
            ];
        }
        return $out;
    }

    private function mapStripeStatus(string $status): string
    {
        switch ($status) {
            case 'succeeded':
                return 'paid';
            case 'processing':
                return 'processing';
            case 'requires_action':
                return 'requires_action';
            case 'requires_payment_method':
            case 'canceled':
                return 'failed';
            default:
                return $status;
        }
    }

    /** ===== Helpers keys/env/log ===== */

    private function getStripeSecretKey(): ?string
    {
        // Se hai creato l'helper suggerito, usa quello
        if (function_exists('sbs_stripe_sk')) {
            $sk = sbs_stripe_sk();
            return $sk ?: null;
        }

        // Fallback: prova da env/ costanti note
        $candidates = [
            function_exists('my_env') ? my_env('STRIPE_SK')  : getenv('STRIPE_SK'),
            function_exists('my_env') ? my_env('STRIPE_TEST_SK') : getenv('STRIPE_TEST_SK'),
        ];
        foreach ($candidates as $v) {
            if (!empty($v)) return $v;
        }

        return 'sk_test_51S6smBItz3qkEFXxHRWm65G9MghvpZ7baeQXC7AwXbjlWY6CFfAeC3h8uzNdLAR0I6hy3RdBWuHMFkI1PPI36xjY00ddNrTG3G';
    }

    private function clientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = \explode(',', $_SERVER[$k])[0];
                return \trim($ip);
            }
        }
        return '';
    }

    private function logCartEvent(
        string $cartToken,
        string $type,
        array $meta = [],
        ?string $intentId = null,
        ?string $itemId = null,
        ?int $qty = null
    ): void {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'sbs_cart_events', [
            'cart_token' => $cartToken,
            'intent_id'  => $intentId,
            'type'       => $type,
            'item_id'    => $itemId,
            'qty'        => $qty,
            'meta_json'  => $meta ? \wp_json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'client_ip'  => $this->clientIp(),
            'user_agent' => \substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'created_at' => \current_time('mysql'),
        ], ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']);
    }

    // PaymentsController.php
    public function updateIntentDetails()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            status_header(405);
            wp_send_json_error(['message' => 'Method not allowed']);
        }

        $raw     = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: [];

        $intentId  = sanitize_text_field($payload['intent_id'] ?? '');
        if (!$intentId) wp_send_json_error(['message' => 'intent_id missing']);

        // customer
        $firstName = sanitize_text_field($payload['first_name'] ?? '');
        $lastName  = sanitize_text_field($payload['last_name'] ?? '');
        $email     = sanitize_email($payload['email'] ?? '');

        // shipping
        $street    = sanitize_text_field($payload['street'] ?? '');
        $streetNo  = sanitize_text_field($payload['street_no'] ?? '');
        $city      = sanitize_text_field($payload['city'] ?? '');
        $cap       = sanitize_text_field($payload['cap'] ?? '');
        $province  = strtoupper(sanitize_text_field($payload['province'] ?? ''));
        $country   = sanitize_text_field($payload['country'] ?? 'IT');

        $shippingLine1 = trim($street . ' ' . $streetNo);
        $customerName  = trim($firstName . ' ' . $lastName);

        try {
            // 1) Stripe update (SECRET KEY)
            $sk = $this->getStripeSecretKey();
            $sc = new \Stripe\StripeClient($sk);

            $update = [];
            // aggiorna shipping SOLO se almeno qualcosa è presente
            if ($shippingLine1 || $city || $cap || $province || $country) {
                $update['shipping'] = [
                    'name'    => $customerName ?: ($email ?: 'Cliente'),
                    'address' => [
                        'line1'       => $shippingLine1 ?: null,
                        'city'        => $city ?: null,
                        'postal_code' => $cap ?: null,
                        'state'       => $province ?: null,
                        'country'     => $country ?: 'IT',
                    ],
                ];
            }
            if ($email) {
                $update['receipt_email'] = $email;
            }
            if ($firstName || $lastName || $email) {
                $update['metadata'] = array_filter([
                    'first_name' => $firstName ?: null,
                    'last_name'  => $lastName  ?: null,
                    'email'      => $email     ?: null,
                ]);
            }

            if (!empty($update)) {
                $sc->paymentIntents->update($intentId, $update);
            }

            // 2) DB sync (opzionale: se la riga non esiste ancora non succede nulla)
            global $wpdb;
            $table = $wpdb->prefix . 'sbs_payment_intents';
            $shippingJson = wp_json_encode([
                'line1'       => $shippingLine1,
                'city'        => $city,
                'postal_code' => $cap,
                'state'       => $province,
                'country'     => $country,
            ], JSON_UNESCAPED_UNICODE);

            $wpdb->update($table, [
                'email'         => $email ?: null,
                'first_name'    => $firstName ?: null,
                'last_name'     => $lastName  ?: null,
                'shipping_json' => $shippingJson,
                'updated_at'    => current_time('mysql'),
            ], ['intent_id' => $intentId], ['%s', '%s', '%s', '%s', '%s'], ['%s']);

            wp_send_json_success(['updated' => true]);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    // === ADD inside class Controllers\PaymentsController ===

    public function finalize()
    {
        header('Content-Type: application/json');

        // Accetta sia POST che GET
        $piId = $_POST['pi'] ?? $_GET['pi'] ?? null;
        if (!$piId) {
            echo json_encode(['ok' => false, 'error' => 'missing_pi']);
            return;
        }

        try {
            // 1) Stato reale da Stripe
            $sk = $this->getStripeSecretKey();
            if (!$sk) throw new \Exception('Stripe Secret Key mancante');
            $sc = new \Stripe\StripeClient($sk);
            $pi = $sc->paymentIntents->retrieve($piId);

            $status = (string)($pi->status ?? '');
            $mapped = $this->mapStripeStatus($status);

            // 2) Esiste già in DB?
            global $wpdb;
            $tablePI = $wpdb->prefix . 'sbs_payment_intents';
            $row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$tablePI} WHERE intent_id = %s LIMIT 1", $piId)
            );

            if ($status !== 'succeeded') {
                echo json_encode([
                    'ok'       => true,
                    'status'   => $mapped,
                    'in_db'    => (bool)$row,
                    'wrote_db' => false,
                ]);
                return;
            }

            // 3) Succeeded: se non ho la riga o non è "paid", faccio il fallback idempotente
            $wrote = false;
            if (!$row || $row->status !== 'paid') {
                $this->syncFromPaymentIntentArray($pi); // upsert + stock idempotente
                $wrote = true;
            }

            echo json_encode([
                'ok'       => true,
                'status'   => 'paid',
                'in_db'    => true,
                'wrote_db' => $wrote,
            ]);
        } catch (\Throwable $e) {
            error_log('finalize error: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Fallback di sync: copia della logica del webhook (upsert + decremento stock) in forma riusabile.
     * Accetta l’oggetto PI dello Stripe SDK (stdClass).
     */
    private function syncFromPaymentIntentArray($piObj): void
    {
        // Normalizzo in array (lo Stripe SDK espone oggetti "array-like")
        $pi   = json_decode(json_encode($piObj), true);
        $meta = (array)($pi['metadata'] ?? []);
        $ship = (array)($pi['shipping'] ?? []);
        $addr = (array)($ship['address'] ?? []);

        $firstName = sanitize_text_field($meta['first_name'] ?? '');
        $lastName  = sanitize_text_field($meta['last_name']  ?? '');

        if (!$firstName && !$lastName) {
            $full = trim((string)($ship['name'] ?? ''));
            if ($full !== '') {
                $parts    = preg_split('/\s+/', $full, 2);
                $firstName = sanitize_text_field($parts[0] ?? '');
                $lastName  = sanitize_text_field($parts[1] ?? '');
            }
        }

        $shippingJson = wp_json_encode([
            'line1'       => trim((string)($addr['line1'] ?? '')),
            'city'        => (string)($addr['city'] ?? ''),
            'postal_code' => (string)($addr['postal_code'] ?? ''),
            'state'       => (string)($addr['state'] ?? ''),
            'country'     => strtoupper((string)($addr['country'] ?? 'IT')),
        ], JSON_UNESCAPED_UNICODE);

        $intentId    = (string)($pi['id'] ?? '');
        $cartToken   = (string)($meta['cart_token'] ?? '');
        $email       = sanitize_email($pi['receipt_email'] ?? ($meta['email'] ?? ''));
        $currency    = strtoupper((string)($pi['currency'] ?? 'EUR'));
        $amountTotal = (int)($pi['amount'] ?? 0);

        // Totali dai metadata (fallback sensato)
        $amountSubtotal = isset($meta['amount_subtotal']) ? (int)$meta['amount_subtotal'] : $amountTotal;
        $amountShipping = isset($meta['amount_shipping']) ? (int)$meta['amount_shipping'] : 0;
        $amountDiscount = isset($meta['amount_discount']) ? (int)$meta['amount_discount'] : 0;
        $amountTax      = isset($meta['amount_tax'])      ? (int)$meta['amount_tax']      : 0;

        // Items
        $items = [];
        if (!empty($meta['items_json'])) {
            $items = json_decode((string)$meta['items_json'], true);
            if (!is_array($items)) $items = [];
        }
        if (!$items) {
            // Fallback: prova a recuperarli da una riga (se già esiste)
            global $wpdb;
            $tablePI = $wpdb->prefix . 'sbs_payment_intents';
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT items_json FROM {$tablePI} WHERE intent_id = %s LIMIT 1",
                $intentId
            ), ARRAY_A);
            if (!empty($row['items_json'])) {
                $items = json_decode($row['items_json'], true) ?: [];
            }
        }

        // 1) Upsert riga PI (idempotente) — ⚠️ 22 colonne → 22 formati
        global $wpdb;
        $tablePI = $wpdb->prefix . 'sbs_payment_intents';

        $ok = $wpdb->replace($tablePI, [
            'intent_id'       => $intentId,
            'cart_token'      => $cartToken,
            'status'          => 'paid',
            'items_json'      => wp_json_encode($this->normalizeItems($items), JSON_UNESCAPED_UNICODE),
            'amount_subtotal' => $amountSubtotal,
            'amount_shipping' => $amountShipping,
            'amount_discount' => $amountDiscount,
            'amount_tax'      => $amountTax,
            'amount_total'    => $amountTotal,
            'currency'        => $currency,
            'email'           => $email ?: null,
            'first_name'      => $firstName ?: null,
            'last_name'       => $lastName ?: null,
            'shipping_json'   => $shippingJson,
            'user_id'         => get_current_user_id() ?: 0,
            'expires_at'      => gmdate('Y-m-d H:i:s', time() + 10 * 60),
            'client_ip'       => $this->clientIp(),
            'user_agent'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'referrer'        => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 512),
            'utm_json'        => null,
            'created_at'      => current_time('mysql'),
            'updated_at'      => current_time('mysql'),
        ], [
            '%s',
            '%s',
            '%s',
            '%s',   // intent_id, cart_token, status, items_json
            '%d',
            '%d',
            '%d',
            '%d',
            '%d', // importi
            '%s',
            '%s',
            '%s',
            '%s',
            '%s', // currency, email, first_name, last_name, shipping_json
            '%d',
            '%s',
            '%s',
            '%s',
            '%s', // user_id, expires_at, client_ip, user_agent, referrer
            '%s',
            '%s'                 // utm_json, created_at, updated_at
        ]);

        if ($ok === false) {
            error_log('sbs_payment_intents REPLACE failed: ' . $wpdb->last_error);
        }

        // 2) Decremento stock — SOLO se non già fatto (idempotente)
        if (!$this->hasStockBeenDecremented($intentId)) {
            $decrements = $this->expandDecrements($this->normalizeItems($items));
            foreach ($decrements as $pid => $qty) {
                $pid = (int)$pid;
                $qty = (int)$qty;
                if ($pid > 0 && $qty > 0) {
                    $this->decrementStock($pid, $qty);
                }
            }
            // Log così evitiamo doppio decremento
            $this->logCartEvent($cartToken, 'stock_decremented', [
                'intent_id'  => $intentId,
                'decrements' => isset($decrements) ? $decrements : [],
            ], $intentId);
        }
    }

    private function hasStockBeenDecremented(string $intentId): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_cart_events';
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM {$table} WHERE intent_id = %s AND type = 'stock_decremented' LIMIT 1",
            $intentId
        ));
        return (bool)$exists;
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
}
