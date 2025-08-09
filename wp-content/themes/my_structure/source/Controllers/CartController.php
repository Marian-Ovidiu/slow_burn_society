<?php

namespace Controllers;

use Core\Bases\BaseController;


class CartController extends BaseController
{   
    public function index()
    {
   
    $this->render('components.cartPage', []);
    }
}
