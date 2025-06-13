<?php
use Classes\StripePayments;
$router = \Core\Router::getInstance();

/*$router->post('/create-payment-intent', StripePayments::class, 'createIntent');*/
$router->post('/create-payment-intent', [StripePayments::class, 'createIntent']);
$router->post('/complete-donation', [StripePayments::class, 'completePayment']);
