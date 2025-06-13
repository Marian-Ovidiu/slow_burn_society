<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Options\OpzioniGlobaliFields;
use Models\Options\OpzioniProdottoFields;
use Models\Prodotto;

class HomeController extends BaseController
{   
    public function index()
    {
    $dataHero = OpzioniGlobaliFields::get();
    $subdata = OpzioniProdottoFields::get();
      
$latest = [
    [
        'name' => 'Cartine Raw Classic',
        'price' => 1.20,
        'image' => 'https://images.pexels.com/photos/8449110/pexels-photo-8449110.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Cartine in fibra di canapa naturale, a combustione lenta.',
        'gallery' => [
            'https://images.pexels.com/photos/8449110/pexels-photo-8449110.jpeg',
            'https://images.pexels.com/photos/8449108/pexels-photo-8449108.jpeg',
        ],
        'details' => ['50 fogli', '100% naturale', 'Slow burn'],
    ],
    [
        'name' => 'Filtro Tips RAW Perforati',
        'price' => 0.80,
        'image' => 'https://images.pexels.com/photos/8449106/pexels-photo-8449106.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Filtri in cartoncino non trattato, perfetti per ogni rollata.',
        'gallery' => null,
        'details' => ['50 filtri', 'Perforati', 'Cartoncino grezzo'],
    ],
    [
        'name' => 'Grinder Acrilico Trasparente',
        'price' => 2.50,
        'image' => 'https://images.pexels.com/photos/8449115/pexels-photo-8449115.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Grinder leggero e resistente, ideale da portare in giro.',
        'gallery' => null,
        'details' => ['2 parti', 'Plastica dura', 'Diametro 60mm'],
    ],
    [
        'name' => 'Accendino Clipper Micro',
        'price' => 1.00,
        'image' => 'https://images.pexels.com/photos/2347311/pexels-photo-2347311.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Accendino piccolo, ricaricabile e tascabile.',
        'gallery' => null,
        'details' => ['Antivento', 'Fiamma regolabile', 'Ricaricabile'],
    ],
    [
        'name' => 'Rolling Tray RAW – Medium',
        'price' => 6.90,
        'image' => 'https://images.pexels.com/photos/12118487/pexels-photo-12118487.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Vassoio in metallo RAW per rollare senza sprechi.',
        'gallery' => null,
        'details' => ['Dimensione: 27x16cm', 'Metallo', 'Anti-sporco'],
    ],
    [
        'name' => 'Buste Zip Mini – 10 pezzi',
        'price' => 1.00,
        'image' => 'https://images.pexels.com/photos/8681104/pexels-photo-8681104.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Buste trasparenti richiudibili, ideali per l’erba o accessori.',
        'gallery' => null,
        'details' => ['10 pezzi', 'Chiusura zip', 'Formato 8x10cm'],
    ],
];
        $this->render('home', [
            'latest' => $latest,
            'subdata' => $subdata,
            'dataHero' => $dataHero,
            'products' => Prodotto::all()
        ]);
    }
}
