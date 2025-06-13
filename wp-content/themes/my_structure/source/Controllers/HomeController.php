<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Options\OpzioniGlobaliFields;
use Models\Options\OpzioniProdottoFields;

class HomeController extends BaseController
{   
    public function index()
    {
    $dataHero = OpzioniGlobaliFields::get();
    $data = OpzioniProdottoFields::get();
      

  $products = [
    [
        'name' => 'Starter Pack – Basic Roll',
        'price' => 4.50,
        'image' => 'https://images.pexels.com/photos/8449110/pexels-photo-8449110.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Tutto il necessario per iniziare a rollare. Perfetto per i principianti.',
        'gallery' => null,
        'details' => [
            '1x Cartine Raw Classic',
            '1x Filtro Tips RAW',
            '1x Accendino Clipper Micro'
        ],
    ],
    [
        'name' => 'Grinder Pack – Easy Grind',
        'price' => 6.90,
        'image' => 'https://images.pexels.com/photos/8449115/pexels-photo-8449115.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Pack ideale per grindare e rollare in modo veloce e pulito.',
        'gallery' => null,
        'details' => [
            '1x Grinder Acrilico',
            '1x Mini Tray Roll',
            '1x Portacartine Marrone'
        ],
    ],
    [
        'name' => 'Travel Pack – On the Go',
        'price' => 8.90,
        'image' => 'https://images.pexels.com/photos/8681104/pexels-photo-8681104.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Kit perfetto per portarsi tutto dietro. Compatto, pratico e figo.',
        'gallery' => null,
        'details' => [
            '1x Scatolina Portatutto',
            '1x Buste Zip Mini (10pz)',
            '1x Accendino Clipper Large'
        ],
    ],
    [
        'name' => 'Rolling Pro Kit',
        'price' => 11.90,
        'image' => 'https://images.pexels.com/photos/12118487/pexels-photo-12118487.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Pacco per rollatori esperti. Tutto il necessario per sessioni perfette.',
        'gallery' => null,
        'details' => [
            '1x Rolling Tray RAW',
            '1x Grinder Metallo',
            '1x Accendino Clipper Limited',
            '1x Filtro Tips RAW'
        ],
    ],
    [
        'name' => 'Full Combo Pack – Deluxe',
        'price' => 16.90,
        'image' => 'https://images.pexels.com/photos/2347311/pexels-photo-2347311.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Pacco completo per chi non vuole farsi mancare nulla. Full vibes.',
        'gallery' => null,
        'details' => [
            '1x Grinder Metallo',
            '1x Cartine RAW',
            '1x Tips RAW',
            '1x Portasigarette Metallo',
            '1x Accendino Clipper',
            '1x Portacartine in pelle'
        ],
    ],
    [
        'name' => 'OCB Tech Pack',
        'price' => 9.90,
        'image' => 'https://images.pexels.com/photos/8449106/pexels-photo-8449106.jpeg?auto=compress&cs=tinysrgb&h=300',
        'description' => 'Un mix OCB perfetto per gli amanti del marchio.',
        'gallery' => null,
        'details' => [
            '1x OCB Inject-a-Roll',
            '1x Cartine OCB Slim',
            '1x Filtro OCB',
            '1x Accendino Clipper'
        ],
    ]
    ];
  
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
            'products' => $products,
            'dataHero' => $dataHero,
            'data' => $data
        ]);
    }
}
