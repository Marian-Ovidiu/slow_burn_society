<?php

namespace Classes;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Models\Prodotto;
use Models\Kit;

class StripePayments
{
    /**
     * Body atteso:
     * {
     *   "items": [ {"id":123,"qty":2}, {"kitId":10,"qty":1} ]
     * }
     * Ritorna: { clientSecret, intentId }
     */
    public static function createIntent()
    {
        Stripe::setApiKey(my_env('SECRET_KEY'));

        $data  = json_decode(file_get_contents("php://input"), true) ?: [];
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        if (empty($items)) {
            wp_send_json_error(['message' => 'items mancanti'], 400);
            return;
        }

        // 1) total server-side
        $amountCents = self::computeAmountFromItems($items); // in centesimi
        if ($amountCents <= 0) {
            wp_send_json_error(['message' => 'Importo calcolato non valido'], 400);
            return;
        }

        try {
            // 2) crea PaymentIntent
            $pi = PaymentIntent::create([
                'amount'                    => $amountCents,
                'currency'                  => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'description'               => 'Acquisto Shop',
            ]);

            // 3) salva il carrello per la finalizzazione stock
            \Classes\IntentRepo::instance()->save($pi->id, $items);

            wp_send_json_success([
                'clientSecret' => $pi->client_secret,
                'intentId'     => $pi->id,
            ]);
        } catch (\Throwable $e) {
            error_log('[createIntent] ' . $e->getMessage());
            wp_send_json_error(['message' => 'Errore Stripe nella creazione del pagamento'], 500);
        }
    }

    /**
     * Finalizza l’ordine: scala lo stock (atomicamente) se l’intent è "succeeded".
     * Body atteso:
     * {
     *   "intentId": "pi_xxx"
     * }
     * Ritorna: { ok: true }
     *
     * ⚠️ Chiama questo SOLO server-to-server (o usa il webhook Stripe, consigliato).
     */
    public static function finalize()
    {
        Stripe::setApiKey(my_env('SECRET_KEY'));
        $data     = json_decode(file_get_contents("php://input"), true) ?: [];
        $intentId = trim((string)($data['intentId'] ?? ''));

        if ($intentId === '') {
            wp_send_json_error(['message' => 'intentId mancante'], 400);
            return;
        }

        try {
            // idempotency semplice
            $idemKey = 'sbs_idemp_' . substr(sha1($intentId), 0, 20);
            if (get_transient($idemKey)) {
                wp_send_json_success(['ok' => true, 'idempotent' => true]);
                return;
            }

            $pi = PaymentIntent::retrieve($intentId);
            if ($pi->status !== 'succeeded') {
                wp_send_json_error(['message' => 'Pagamento non riuscito o non confermato'], 402);
                return;
            }

            // carrello salvato a createIntent
            $items = \Classes\IntentRepo::instance()->load($intentId);
            if (empty($items)) {
                wp_send_json_error(['message' => 'Carrello non trovato per questo intent'], 404);
                return;
            }

            // espandi kit -> componenti
            $expanded = self::expandItems($items); // [['id'=>pid,'qty'=>n], ...]
            if (empty($expanded)) {
                wp_send_json_error(['message' => 'Carrello vuoto dopo espansione'], 400);
                return;
            }

            // decremento atomico
            $ok = \Classes\InventoryRepo::instance()->decrementManyAtomically($expanded);
            if (!$ok) {
                wp_send_json_error(['message' => 'Stock insufficiente durante la finalizzazione'], 409);
                return;
            }

            \Classes\IntentRepo::instance()->delete($intentId);
            set_transient($idemKey, 1, 12 * HOUR_IN_SECONDS);

            // TODO: crea ordine / invia email conferma ordine, ecc.
            wp_send_json_success(['ok' => true]);
        } catch (\Throwable $e) {
            error_log('[finalize] ' . $e->getMessage());
            wp_send_json_error(['message' => 'Errore durante la finalizzazione'], 500);
        }
    }

    /* ===================== Helpers ===================== */

    // Calcola il totale in CENTESIMI (prezzi da Prodotto/Kit)
    private static function computeAmountFromItems(array $items): int
    {
        $total = 0.0;
        foreach ($items as $line) {
            $qty = (int)($line['qty'] ?? 0);
            if ($qty <= 0) continue;

            if (!empty($line['kitId'])) {
                $kit = Kit::find((int)$line['kitId']);
                if (!$kit) continue;
                $price = (float)$kit->priceFloat(); // già normalizzato nel tuo modello
                $total += $price * $qty;
            } else {
                $prod = Prodotto::find((int)($line['id'] ?? 0));
                if (!$prod) continue;
                $price = self::priceFloatFromRaw($prod->prezzo);
                $total += $price * $qty;
            }
        }
        return (int) round($total * 100);
    }

    // Normalizza prezzo Prodotto da ACF (es. "€ 1.234,50" -> 1234.50)
    private static function priceFloatFromRaw($raw): float
    {
        $s = (string)$raw;
        $s = str_replace(['€',' '], '', $s);
        $s = str_replace('.', '', $s);   // migliaia
        $s = str_replace(',', '.', $s);  // decimali
        return (float)$s;
    }

    // Espansione kit -> componenti (usa helper globale se presente)
    private static function expandItems(array $items): array
    {
        if (function_exists('sbs_expand_cart_items')) {
            return sbs_expand_cart_items($items);
        }
        // Fallback minimo
        $flat = [];
        foreach ($items as $line) {
            $qty = (int)($line['qty'] ?? 0);
            if ($qty <= 0) continue;

            if (!empty($line['kitId'])) {
                $kitId = (int)$line['kitId'];
                $prods = (array) get_field('prodotti', $kitId) ?: [];
                foreach ($prods as $p) {
                    $pid = is_object($p) ? (int)($p->ID ?? 0) : (int)$p;
                    if ($pid <= 0) continue;
                    $perKit = 1;
                    $flat[$pid] = ($flat[$pid] ?? 0) + ($qty * $perKit);
                }
            } else {
                $pid = (int)($line['id'] ?? 0);
                if ($pid <= 0) continue;
                $flat[$pid] = ($flat[$pid] ?? 0) + $qty;
            }
        }
        return array_map(fn($pid,$q)=>['id'=>(int)$pid,'qty'=>(int)$q], array_keys($flat), $flat);
    }
}
