<?php

namespace Controllers;

use Core\Bases\BaseController;

class PostController extends BaseController
{
    public function single(){
        $this->render('post_single', []);
    }
}