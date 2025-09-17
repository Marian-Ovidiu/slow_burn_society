<?php

namespace Classes;

use Stripe\StripeClient;

class StripePayments
{
    /**
     * POST /create-payment-intent
     */
    public function createIntent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'data' => ['message' => 'Metodo non consentito']]);
            return;
        }

        header('Content-Type: application/json');

        // --- Input -----------------------------------------------------------
        $raw     = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: [];

        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $email = isset($payload['email']) ? sanitize_email($payload['email']) : null;

        // cart_token obbligatorio
        $cartToken = preg_replace('/[^a-f0-9\-]/i', '', $payload['cart_token'] ?? '');
        if (!$items || !$cartToken) {
            echo json_encode(['success' => false, 'data' => ['message' => 'Items o cart_token mancanti']]);
            return;
        }

        // --- Calcolo importi lato server (fonte di verità) -------------------
        try {
            $amounts = $this->calculateAmounts($items); // centesimi
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'data' => ['message' => $e->getMessage()]]); // es. totale < 0,50€
            return;
        }

        try {
            // --- Stripe client ------------------------------------------------
            $sk = my_env('STRIPE_SK');
            if (!$sk) throw new \Exception('Stripe SK mancante');
            $sc = new StripeClient($sk);

            // Idempotency key “nuova” (evita riuso di PI chiusi)
            $idempotencyKey = 'pi:' . $cartToken . ':' . $amounts['amount_total'] . ':' . bin2hex(random_bytes(8));

            // Metadata minificati per poterli usare nel webhook
            $itemsJson = wp_json_encode($this->normalizeItems($items), JSON_UNESCAPED_UNICODE);
            if (strlen($itemsJson) > 450) {
                // Stripe metadata value limit ~500 chars => ruduce/limita
                $itemsJson = substr($itemsJson, 0, 450);
            }

            $pi = $sc->paymentIntents->create(
                [
                    'amount'    => (int)$amounts['amount_total'],
                    'currency'  => 'eur',
                    'automatic_payment_methods' => ['enabled' => true],
                    'metadata'  => [
                        'cart_token'       => $cartToken,
                        'email'            => $email ?: '',
                        'items_json'       => $itemsJson,
                        'amount_subtotal'  => (string)$amounts['amount_subtotal'],
                        'amount_shipping'  => (string)$amounts['amount_shipping'],
                        'amount_discount'  => (string)$amounts['amount_discount'],
                        'amount_tax'       => (string)$amounts['amount_tax'],
                        'amount_total'     => (string)$amounts['amount_total'],
                        'currency'         => 'EUR',
                    ],
                    // facoltativo: mostra ricevuta da Stripe
                    'receipt_email' => $email ?: null,
                ],
                ['idempotency_key' => $idempotencyKey]
            );

            echo json_encode([
                'success' => true,
                'data'    => [
                    'intentId'     => $pi->id,
                    'clientSecret' => $pi->client_secret,
                ]
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe API error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'data' => ['message' => $e->getMessage()]]); // es. importo invalido
        } catch (\Throwable $e) {
            error_log('PI create failure: ' . $e->getMessage());
            echo json_encode(['success' => false, 'data' => ['message' => 'Errore server creazione pagamento']]);
        }
    }

    /**
     * (Opzionale ma consigliato) Aggiorna l’email sul PI prima della conferma,
     * così il webhook avrà l’email finale anche se non scriviamo nulla a DB ora.
     *
     * POST /update-intent-email
     */
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
            $sk = my_env('STRIPE_SK');
            $sc = new \Stripe\StripeClient($sk);
            $sc->paymentIntents->update($intentId, [
                'metadata'      => ['email' => $email],
                'receipt_email' => $email,
            ]);

            global $wpdb;
            $table = $wpdb->prefix . 'sbs_payment_intents';
            $wpdb->update(
                $table,
                [
                    'email'      => $email,
                    'updated_at' => current_time('mysql'),
                ],
                ['intent_id' => $intentId],
                ['%s', '%s'],
                ['%s']
            );

            // NOTA: in questo nuovo flusso NON aggiorniamo più il DB qui.
            // Scriveremo tutto solo nel webhook a pagamento riuscito.

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            error_log('updateIntentEmail error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'data' => ['message' => 'Impossibile aggiornare email']]);
        }
    }

    /* ========================== HELPERS ========================== */

    private function calculateAmounts(array $items): array
    {
        $subtotal = 0;
        foreach ($items as $it) {
            $qty  = max(1, (int)($it['qty'] ?? 1));
            $unit = $this->getUnitPriceCents($it);
            $subtotal += ($unit * $qty);
        }

        $shipping = ($subtotal >= 3500) ? 0 : 499;
        $discount = 0;
        $tax      = 0;
        $total    = $subtotal + $shipping - $discount + $tax;

        if ($total < 50) {
            throw new \Exception('Totale ordine troppo basso o nullo');
        }

        return [
            'amount_subtotal' => (int)$subtotal,
            'amount_shipping' => (int)$shipping,
            'amount_discount' => (int)$discount,
            'amount_tax'      => (int)$tax,
            'amount_total'    => (int)$total,
        ];
    }

    private function getUnitPriceCents(array $it): int
    {
        if (isset($it['price'])) {
            $n = (float)$it['price'];
            if ($n > 0) return (int)round($n * 100);
        }
        return 0;
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
}
