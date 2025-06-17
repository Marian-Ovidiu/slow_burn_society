<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Kit;
use Models\Options\OpzioniGlobaliFields;
use Models\Options\OpzioniProdottoFields;
use Models\Prodotto;

class HomeController extends BaseController
{   
    public function index()
    {
    $dataHero = OpzioniGlobaliFields::get();
    $subdata = OpzioniProdottoFields::get(); 
        $this->render('home', [
            'latest' => Kit::all(),
            'subdata' => $subdata,
            'dataHero' => $dataHero,
            'products' => Prodotto::all()
        ]);
    }
}
