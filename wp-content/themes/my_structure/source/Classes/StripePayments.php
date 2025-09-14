<?php

// source/Classes/StripePayments.php (estratto rilevante)
namespace Classes;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Models\Prodotto;
use Models\Kit;

class StripePayments
{
    // Espandi items del carrello in [{id, qty}] (prodotti puri) partendo da prodotti/kit
    public static function expandItems(array $items): array {
        if (function_exists('sbs_expand_cart_items')) return \sbs_expand_cart_items($items);
        $flat = [];
        foreach ($items as $line) {
            $qtyLine = max(1, (int)($line['qty'] ?? 1));
            if (!empty($line['kitId'])) {
                $kitId = (int)$line['kitId'];
                $kit = new Kit(get_post($kitId));
                foreach (($kit->prodotti ?? []) as $p) {
                    $pid = is_object($p) ? (int)($p->ID ?? 0) : (int)$p;
                    if ($pid > 0) $flat[$pid] = ($flat[$pid] ?? 0) + $qtyLine;
                }
            } else {
                $pid = (int)($line['id'] ?? 0);
                if ($pid > 0) $flat[$pid] = ($flat[$pid] ?? 0) + $qtyLine;
            }
        }
        return array_map(fn($pid,$q)=>['id'=>(int)$pid,'qty'=>(int)$q], array_keys($flat), $flat);
    }

    // Calcolo totale in centesimi (EUR) lato server
    private static function totalFromItems(array $items): int {
        $sum = 0;
        foreach ($items as $line) {
            if (!empty($line['kitId'])) {
                $kit = new Kit(get_post((int)$line['kitId']));
                $price = (float) str_replace([',','€',' '], ['.','',''], (string)($kit->prezzo ?? 0));
                $sum += (int) round($price * 100) * max(1,(int)$line['qty']);
            } else {
                $p = Prodotto::find((int)$line['id']);
                $price = (float) ($p->prezzo ?? 0);
                $sum += (int) round($price * 100) * max(1,(int)$line['qty']);
            }
        }
        return max(0, $sum);
    }

    public static function createIntent()
    {
        Stripe::setApiKey(my_env('SECRET_KEY'));

        $body  = json_decode(file_get_contents('php://input'), true) ?: [];
        $items = is_array($body['items'] ?? null) ? $body['items'] : [];

        if (!$items) {
            wp_send_json_error(['message'=>'items missing'], 400);
            return;
        }

        $amount = self::totalFromItems($items); // in centesimi
        if ($amount <= 0) {
            wp_send_json_error(['message'=>'amount invalid'], 400);
            return;
        }

        // crea PI
        $pi = PaymentIntent::create([
            'amount'   => $amount,
            'currency' => 'eur',
            'automatic_payment_methods' => ['enabled' => true],
            // opzionale: metadata
            'metadata' => ['source' => 'sbs_custom_checkout'],
        ]);

        // salva gli items legati a questo PI (per il webhook)
        \Classes\IntentRepo::instance()->save($pi->id, $items);

        wp_send_json_success([
            'clientSecret' => $pi->client_secret,
            'intentId'     => $pi->id,
        ]);
    }
}
