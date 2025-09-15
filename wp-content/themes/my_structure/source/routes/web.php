<?php
use Classes\StripePayments;
use Controllers\CartController;
use Controllers\PageController;

$router = \Core\Router::getInstance();

/*$router->post('/create-payment-intent', StripePayments::class, 'createIntent');*/
$router->post('/create-payment-intent', [StripePayments::class, 'createIntent']);
$router->post('/checkout/finalize', [StripePayments::class, 'finalize']);

$router->get('/checkout', [\Controllers\CheckoutController::class, 'show']);

// Recupera gli articoli del carrello (opzionale se lo gestisci lato client)
$router->get('/cart', [CartController::class, 'get']);

// Salva i dettagli del carrello (es. se vuoi loggarlo o inviarlo via email)
$router->post('/cart/save', [CartController::class, 'save']);
$router->post('/webhooks/stripe', [\Classes\StripeWebhookController::class, 'handle']);

$router->get('/grazie', [PageController::class, 'grazie']);
