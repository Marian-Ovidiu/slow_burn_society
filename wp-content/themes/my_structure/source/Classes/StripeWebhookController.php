<?php

namespace Classes;

use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController
{
    public static function handle()
    {
        header('Content-Type: application/json; charset=utf-8');

        $endpointSecret = my_env('STRIPE_WEBHOOK_SECRET');
        if (!$endpointSecret) { http_response_code(500); echo json_encode(['error'=>'Webhook secret missing']); return; }

        $payload   = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            http_response_code(400); echo json_encode(['error'=>'Invalid signature']); return;
        } catch (\Throwable $e) {
            http_response_code(400); echo json_encode(['error'=>'Invalid payload']); return;
        }

        // idempotenza per evento
        $evtKey = 'sbs_whevt_' . $event->id;
        if (get_transient($evtKey)) { http_response_code(200); echo json_encode(['ok'=>true,'idempotent'=>true]); return; }

        Stripe::setApiKey(my_env('SECRET_KEY'));

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $pi = $event->data->object; // \Stripe\PaymentIntent
                $items = \Classes\IntentRepo::instance()->load($pi->id);
                if ($items) {
                    if (function_exists('sbs_expand_cart_items')) {
                        $expanded = \sbs_expand_cart_items($items);
                    } elseif (method_exists(\Classes\StripePayments::class, 'expandItems')) {
                        $expanded = \Classes\StripePayments::expandItems($items);
                    } else {
                        $expanded = [];
                    }
                    if ($expanded) {
                        $ok = \Classes\InventoryRepo::instance()->decrementManyAtomically($expanded);
                        if ($ok) { \Classes\IntentRepo::instance()->delete($pi->id); }
                    }
                }
                break;

            case 'payment_intent.payment_failed':
            case 'payment_intent.canceled':
            case 'payment_intent.processing':
                // opzionale: log/state
                break;
        }

        set_transient($evtKey, 1, 12 * HOUR_IN_SECONDS);
        http_response_code(200);
        echo json_encode(['ok'=>true]);
    }
}
