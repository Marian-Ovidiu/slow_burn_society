<?php

namespace Classes;

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController
{
    // (opzionale) se conosci la field key ACF, mettila qui:
    private const ACF_DISP_FIELD_KEY = null; // es. 'field_64e8c0f0a1234' oppure lascialo null

    public static function handle()
    {
        header('Content-Type: application/json; charset=utf-8');

        $endpointSecret = my_env('STRIPE_WEBHOOK_SECRET');
        if (!$endpointSecret) {
            http_response_code(500);
            echo json_encode(['error' => 'Webhook secret missing']);
            return;
        }

        $payload   = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid signature']);
            return;
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payload']);
            return;
        }

        // Idempotenza per evento
        $evtKey = 'sbs_whevt_' . $event->id;
        if (function_exists('get_transient') && get_transient($evtKey)) {
            http_response_code(200);
            echo json_encode(['ok' => true, 'idempotent' => true]);
            return;
        }

        // Non è strettamente necessario per leggere l'evento, ma lo usi altrove
        Stripe::setApiKey(my_env('SECRET_KEY'));

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $pi = $event->data->object; // \Stripe\PaymentIntent

                // Carica gli items legati al PaymentIntent salvati alla createIntent()
                $items = \Classes\IntentRepo::instance()->load($pi->id);

                if (is_array($items) && $items) {
                    // Espandi eventuali kit -> [{id, qty}]
                    if (function_exists('sbs_expand_cart_items')) {
                        $expanded = \sbs_expand_cart_items($items);
                    } elseif (method_exists(\Classes\StripePayments::class, 'expandItems')) {
                        $expanded = \Classes\StripePayments::expandItems($items);
                    } else {
                        $expanded = [];
                    }

                    if ($expanded) {
                        $repo = \Classes\InventoryRepo::instance();

                        // 1) Decrementa dal tuo magazzino (tabella sbs_inventory)
                        $ok = $repo->decrementManyAtomically($expanded);

                        if ($ok) {
                            // 2) Sincronizza ACF/meta "disponibilita" per ogni prodotto
                            foreach ($expanded as $it) {
                                $pid  = (int)($it['id'] ?? 0);
                                if ($pid <= 0) continue;

                                $curr = (int)$repo->getStock($pid); // stock attuale dopo il decremento
                                self::syncDisponibilitaField($pid, $curr);
                            }

                            // 3) Pulisci il legame PI -> items
                            \Classes\IntentRepo::instance()->delete($pi->id);
                        } else {
                            self::log('decrementManyAtomically ha fallito: possibile stock insufficiente');
                        }
                    } else {
                        self::log('expanded items vuoto per PI '.$pi->id);
                    }
                } else {
                    self::log('IntentRepo->load vuoto per PI '.$pi->id);
                }
                break;

            case 'payment_intent.payment_failed':
            case 'payment_intent.canceled':
            case 'payment_intent.processing':
                // opzionale: log
                break;
        }

        if (function_exists('set_transient')) {
            set_transient($evtKey, 1, 12 * HOUR_IN_SECONDS);
        }
        http_response_code(200);
        echo json_encode(['ok' => true]);
    }

    /**
     * Aggiorna il campo disponibilita per il post $productId.
     * - Se ACF è presente, prova prima con field key (se impostata) poi con field name.
     * - Altrimenti usa update_post_meta.
     * Pulisce la cache del post.
     */
    private static function syncDisponibilitaField(int $productId, int $newStock): void
    {
        $newStock = max(0, (int)$newStock);

        try {
            $updated = false;

            if (function_exists('update_field')) {
                // Se conosci la field key ACF, usa quella (più affidabile del field name)
                if (self::ACF_DISP_FIELD_KEY) {
                    $updated = (bool) update_field(self::ACF_DISP_FIELD_KEY, $newStock, $productId);
                }

                // Altrimenti prova con il field name 'disponibilita'
                if (!$updated) {
                    $updated = (bool) update_field('disponibilita', $newStock, $productId);
                }
            }

            if (!$updated) {
                // Fallback: meta standard
                $updated = (bool) update_post_meta($productId, 'disponibilita', $newStock);
            }

            // Pulisci cache del post/meta per vedere subito il valore aggiornato
            if (function_exists('clean_post_cache')) {
                clean_post_cache($productId);
            }
            if (function_exists('wp_cache_delete')) {
                wp_cache_delete($productId, 'post_meta');
            }

            self::log(sprintf(
                'syncDisponibilitaField PID=%d -> %d (updated=%s)',
                $productId, $newStock, $updated ? 'yes' : 'no'
            ));
        } catch (\Throwable $e) {
            self::log('syncDisponibilitaField error PID='.$productId.' : '.$e->getMessage());
        }
    }

    private static function log(string $msg): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[SBS webhook] '.$msg);
        }
    }
}
