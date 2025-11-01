<?php
use Controllers\CartController;
use Controllers\PageController;
use Controllers\PaymentsController;
use Controllers\RelatedController;
use Middleware\AccessMiddleware;

AccessMiddleware::handle($_SERVER['REQUEST_URI']);

$router = \Core\Router::getInstance();

/** Checkout + Stripe */
$router->get('/checkout', [\Controllers\CheckoutController::class, 'show']);

$router->post('/create-payment-intent', [PaymentsController::class, 'createPaymentIntent']);
$router->post('/update-intent-details', [\Controllers\PaymentsController::class, 'updateIntentDetails']);
$router->post('/webhooks/stripe', [\Classes\StripeWebhookController::class, 'handle']);

/** Cart (client-side ma con audit server) */
$router->get('/cart',        [CartController::class, 'get']);   // opzionale
$router->post('/cart/save',  [CartController::class, 'save']);  // opzionale
$router->post('/cart/event', [CartController::class, 'event']); // audit eventi (beacon)
$router->post('/checkout/finalize', [\Controllers\PaymentsController::class, 'finalize']);
$router->get('/related', [RelatedController::class, 'index']);
$router->get('/grazie', [PageController::class, 'grazie']);

