<?php
namespace Controllers;

use Core\Bases\BaseController;
use Models\Prodotto;

class ProdottoController extends BaseController
{
    public function archive()
    {
        $this->render('archivio-prodotto', []);
    }

    public function single()
    {
        $this->render('single-prodotto', []);
    }
}
