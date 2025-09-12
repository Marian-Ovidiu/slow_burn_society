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
        $subdata  = OpzioniProdottoFields::get();

        $products = Prodotto::all();

        // Mappa “payload JS” per ogni prodotto (keyed by id)
        $productsForJs = [];
        foreach ($products as $p) {
            // Stock robusto: se disponibilita è stringa tipo "Disponibile" non castare a 0 a caso
            $rawStock = $p->disponibilita ?? ($p->stock ?? 0);
            $stock    = is_numeric($rawStock) ? (int) $rawStock : (int) ($p->stock ?? 0);

            $gallery = array_values(array_filter([
                $p->immagine_1['url'] ?? null,
                $p->immagine_2['url'] ?? null,
                $p->immagine_3['url'] ?? null,
                $p->immagine_4['url'] ?? null,
            ]));

            $forJs = [
                'id'           => $p->id,
                'title'        => $p->title,
                'name'         => $p->title,
                'price'        => (float) $p->prezzo,
                'image'        => $p->immagine_1['url'] ?? '',
                'description'  => $p->descrizione ?? '',
                'gallery'      => $gallery,
                'stock'        => $stock,
                'availability' => $p->disponibilita ?? 'Disponibile',
                'category'     => $p->categoria ?? null,
                'brand'        => $p->brand ?? null,
            ];

            // Mini payload già pronto per addToCart (così eviti logica in Blade)
            $forJs['cart'] = [
                'id'    => $forJs['id'],
                'name'  => $forJs['name'],
                'price' => $forJs['price'],
                'image' => $forJs['image'],
                'stock' => $forJs['stock'],
            ];

            $productsForJs[$p->id] = $forJs;
        }

        $this->addJs('cart', 'cart.js');
        $this->addJs('shop', 'shop.js');

        $this->render('home', [
            'latest'         => Kit::all(),
            'subdata'        => $subdata,
            'dataHero'       => $dataHero,
            'products'       => $products,
            'productsForJs'  => $productsForJs, // <<< passa questo alla view
        ]);
    }
}
