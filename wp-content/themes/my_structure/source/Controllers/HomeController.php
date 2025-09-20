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

        // --- PRODOTTI -> payload per JS ---
        $products = Prodotto::all();
        $productsForJs = [];
        foreach ($products as $p) {
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
                'price'        => (float) ($p->prezzo ?? 0),
                'image'        => $p->immagine_1['url'] ?? '',
                'description'  => $p->descrizione ?? '',
                'gallery'      => $gallery,
                'stock'        => $stock,
                'availability' => $p->disponibilita ?? 'Disponibile',
                'category'     => $p->categoria ?? null,
                'brand'        => $p->brand ?? null,
            ];

            // 👇 aggiunto type: 'product'
            $forJs['cart'] = [
                'id'    => (int) $forJs['id'],
                'name'  => $forJs['name'],
                'price' => (float) $forJs['price'],
                'image' => $forJs['image'],
                'stock' => (int) $forJs['stock'],
                'type'  => 'product',
            ];

            $productsForJs[$p->id] = $forJs;
        }

        // --- KIT -> payload per JS ---
        $latest = Kit::all();
        $kitsForJs = [];
        foreach ($latest as $k) {
            // prezzo numerico safe: "€ 12,50" -> 12.50
            $priceNumeric = (float) str_replace(['€', ' ', ','], ['', '', '.'], (string) ($k->prezzo ?? 0));

            // mappa i prodotti del kit (titolo + immagine_1 ACF)
            $mappedProducts = [];
            if (!empty($k->prodotti) && is_iterable($k->prodotti)) {
                $prodotti = $k->prodotti;
                foreach ($prodotti as $product) {
                    $pid = $product->ID;
                    $img1 = function_exists('get_field') ? (get_field('immagine_1', $pid) ?: []) : [];
                    $prod = Prodotto::find($pid);
                    $disp = true;
                    if (!$prod->disponibilita) {
                        $disp = false;
                    }

                    $mappedProducts[] = [
                        'title' => $product->post_title ?? '',
                        'image' => is_array($img1) ? ($img1['url'] ?? '') : '',
                        'disponibilita' => $disp
                    ];
                }
            }

            $kitsForJs[$k->id] = [
                'id'            => $k->id,
                'title'         => $k->nome,
                'name'          => $k->nome,
                'description'   => $k->descrizione,
                'image'         => $k->immagine_kit['url'] ?? '',
                'price'         => $priceNumeric, // numerico
                'products'      => $mappedProducts,
                'disponibilita' => $k->disponibilita,
                // payload pronto per addToCart
                // 👇 aggiunti kitId e type: 'kit'
                'cart'          => [
                    'id'     => (int) $k->id,
                    'kitId'  => (int) $k->id,   // <— chiave per distinguerlo lato BE
                    'name'   => $k->nome,
                    'image'  => $k->immagine_kit['url'] ?? '',
                    'price'  => (float) $priceNumeric,
                    'type'   => 'kit',
                ],
            ];
        }

        $this->addJs('cart', 'cart.js');
        $this->addJs('shop', 'shop.js');

        $this->render('home', [
            'latest'        => $latest,
            'subdata'       => $subdata,
            'dataHero'      => $dataHero,
            'products'      => $products,
            'productsForJs' => $productsForJs,
            'kitsForJs'     => $kitsForJs,
        ]);
    }
}
