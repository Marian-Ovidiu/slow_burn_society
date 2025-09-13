<?php

namespace Controllers;

use Core\Bases\BaseController;

class CheckoutController extends BaseController {
    public function show() {
         $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js'); // creerai resources/js/checkout.js
        $this->render('checkout', []);           // creerai resources/views/checkout.blade.php
    }
}