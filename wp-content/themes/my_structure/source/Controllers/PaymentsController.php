<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Kit;
use Models\Prodotto;

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

            // Idempotenza soft (cart_token + totale + items)
            $idempotencyKey = 'pi:' . \sha1($cartToken . '|' . $calc['amount_total'] . '|' . \wp_json_encode($items));

            // 3) Prepara payload PI con metadata + shipping + receipt_email
            $shippingLine1 = \trim($street . ' ' . $streetNo);
            $customerName  = \trim($firstName . ' ' . $lastName);

            $piParams = [
                'amount'                    => $calc['amount_total'],
                'currency'                  => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'                  => [
                    'cart_token'  => $cartToken,
                    'email'       => $custEmail ?: '',
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'ship_line1'  => $shippingLine1,
                    'ship_city'   => $city,
                    'ship_postal' => $cap,
                    'ship_state'  => $province,
                    'ship_country' => $country,
                ],
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

            // 5) UPSERT snapshot nel DB
            global $wpdb;
            $table = $wpdb->prefix . 'sbs_payment_intents';

            // shipping_json compatto
            $shippingJson = \wp_json_encode([
                'line1'       => $shippingLine1,
                'city'        => $city,
                'postal_code' => $cap,
                'state'       => $province,
                'country'     => $country,
            ], JSON_UNESCAPED_UNICODE);

            $wpdb->replace($table, [
                'intent_id'       => $intentId,
                'cart_token'      => $cartToken,
                'status'          => 'pending_payment',
                'items_json'      => \wp_json_encode($this->normalizeItems($items), JSON_UNESCAPED_UNICODE),
                'amount_subtotal' => (int) $calc['amount_subtotal'],
                'amount_shipping' => (int) $calc['amount_shipping'],
                'amount_discount' => (int) $calc['amount_discount'],
                'amount_tax'      => (int) $calc['amount_tax'],
                'amount_total'    => (int) $calc['amount_total'],
                'currency'        => 'EUR',
                'email'           => $custEmail ?: null,
                'first_name'      => $firstName ?: null,       // <-- NEW
                'last_name'       => $lastName  ?: null,       // <-- NEW
                'shipping_json'   => $shippingJson,            // <-- NEW
                'user_id'         => get_current_user_id() ?: null,
                'expires_at'      => \gmdate('Y-m-d H:i:s', time() + 10 * 60),
                'client_ip'       => $this->clientIp(),
                'user_agent'      => \substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'referrer'        => \substr($_SERVER['HTTP_REFERER'] ?? '', 0, 512),
                'utm_json'        => null,
                'created_at'      => \current_time('mysql'),
                'updated_at'      => \current_time('mysql'),
            ], ['%s','%s','%s','%s','%d','%d','%d','%d','%d','%s','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s'
            ]);

            // 6) Audit
            $this->logCartEvent($cartToken, 'pi_created', [
                'intent_id'    => $intentId,
                'amount_total' => $calc['amount_total'],
                'currency'     => 'EUR'
            ], $intentId);

            echo json_encode(['success' => true, 'data' => [
                'intentId'     => $intentId,
                'clientSecret' => $clientSecret
            ]]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'data' => ['message' => $e->getMessage()]], JSON_UNESCAPED_UNICODE);
        }
    }

    /** /checkout/finalize — opzionale: verifica PI e aggiorna stato */
    public function finalize()
    {
        header('Content-Type: application/json');
        $piId = $_POST['pi'] ?? $_GET['pi'] ?? null;
        if (!$piId) {
            echo json_encode(['ok' => false]);
            return;
        }

        try {
            $sk = $this->getStripeSecretKey();
            if (!$sk) throw new \Exception('Stripe Secret Key mancante');
            $sc = new \Stripe\StripeClient($sk);
            $pi = $sc->paymentIntents->retrieve($piId);

            $status = (string)$pi->status; // 'succeeded', 'requires_action', etc.
            $mapped = $this->mapStripeStatus($status);

            global $wpdb;
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}sbs_payment_intents WHERE intent_id = %s",
                    $piId
                )
            );

            if ($rows) {
                foreach ($rows as $row) {
                    $items = json_decode($row->items_json ?? '[]', true) ?: [];
                    foreach ($items as $it) {
                        $qty = max(1, (int)($it['qty'] ?? 1));
                        $postType = get_post_type((int)$it['id']);

                        $pid = (int)$it['id'];
                        if ($postType !== 'kit') {
                            if ($pid > 0) {
                                $current = (int)get_post_meta($pid, 'disponibilita', true);
                                $newVal  = max(0, $current - $qty);
                                update_post_meta($pid, 'disponibilita', $newVal);
                            }
                        } else {
                            $kit = Kit::find($it['id']);
                            if ($kit) {
                                foreach ($kit->prodotti as $prodotto) {
                                    $current = (int)get_post_meta($prodotto->ID, 'disponibilita', true);
                                    $newVal  = max(0, $current - $qty);
                                    update_post_meta($prodotto->ID, 'disponibilita', $newVal);
                                }
                            }
                        }
                    }
                }
            }

            echo json_encode(['ok' => true, 'status' => $mapped]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
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
        $subtotal = 0;

        foreach ($items as $it) {
            $unit = $this->getUnitPriceCents($it);          // lookup server
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
        // Hook per override esterno
        $filtered = \apply_filters('sbs_get_unit_price_cents', null, $it);
        if ($filtered !== null && \is_numeric($filtered)) {
            return \max(0, (int)$filtered);
        }

        if (!empty($it['kitId'])) {
            return $this->getKitPriceCents((int)$it['kitId']);
        }
        if (!empty($it['id'])) {
            return $this->getProductPriceCents((int)$it['id']);
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
        return array_map(function ($it) {
            return [
                'id'    => $it['id']    ?? null,
                'kitId' => $it['kitId'] ?? null,
                'qty'   => (int)($it['qty'] ?? 1),
            ];
        }, $items);
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

    public function updateIntentEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: [];

        $intentId = isset($payload['intent_id']) ? trim($payload['intent_id']) : '';
        $email    = isset($payload['email']) ? sanitize_email($payload['email']) : '';

        if (!$intentId || !is_email($email)) {
            echo json_encode(['success' => false, 'data' => ['message' => 'Intent o email non validi']]);
            return;
        }

        try {
            // 1) Stripe: aggiorna metadata e (facoltativo) receipt_email
            $sk = my_env('STRIPE_SK');
            $sc = new \Stripe\StripeClient($sk);
            $sc->paymentIntents->update($intentId, [
                'metadata'      => ['email' => $email],
                'receipt_email' => $email, // opzionale
            ]);

            // 2) DB: aggiorna la riga
            global $wpdb;
            $table = $wpdb->prefix . 'sbs_payment_intents';
            $wpdb->update(
                $table,
                ['email' => $email, 'updated_at' => current_time('mysql')],
                ['intent_id' => $intentId],
                ['%s', '%s'],
                ['%s']
            );

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            error_log('updateIntentEmail error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'data' => ['message' => 'Impossibile aggiornare email']]);
        }
    }
}
