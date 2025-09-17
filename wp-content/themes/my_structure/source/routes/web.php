<?php
use Controllers\CartController;
use Controllers\PageController;
use Controllers\PaymentsController;

$router = \Core\Router::getInstance();

/** Checkout + Stripe */
$router->get('/checkout', [\Controllers\CheckoutController::class, 'show']);

$router->post('/create-payment-intent', [PaymentsController::class, 'createPaymentIntent']);
$router->post('/update-intent-email',   [\Classes\StripePayments::class, 'updateIntentEmail']);
$router->post('/checkout/finalize', [\Controllers\PaymentsController::class, 'finalize']);
$router->post('/webhooks/stripe', [\Classes\StripeWebhookController::class, 'handle']);

/** Cart (client-side ma con audit server) */
$router->get('/cart',        [CartController::class, 'get']);   // opzionale
$router->post('/cart/save',  [CartController::class, 'save']);  // opzionale
$router->post('/cart/event', [CartController::class, 'event']); // audit eventi (beacon)


$router->get('/grazie', [PageController::class, 'grazie']);
// app/routes.php (o dove definisci le rotte)
$router->post('/update-intent-email', [\Classes\StripePayments::class, 'updateIntentEmail']);

