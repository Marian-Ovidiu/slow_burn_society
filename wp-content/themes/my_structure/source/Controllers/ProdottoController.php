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
        global $wpdb;

        $slug = $this->resolveSlug();
        if (!$slug) {
            return $this->render('404', [], 404);
        }

        // Assicurati di avere Prodotto::findBySlug($slug)
        $prodotto = Prodotto::findBySlug($slug);
        if (!$prodotto) {
            return $this->render('404', [], 404);
        }

        // — prezzo numerico
        $priceRaw = (string)($prodotto->prezzo ?? '');
        $priceNum = (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', $priceRaw));
        if (!is_finite($priceNum)) $priceNum = 0.0;

        // — gallery da immagine_1..4 (ACF può essere array/url)
        $imgs = [];
        foreach (['immagine_1', 'immagine_2', 'immagine_3', 'immagine_4'] as $k) {
            $val = $prodotto->$k ?? null;
            if (is_array($val) && !empty($val['url']))       $imgs[] = $val['url'];
            elseif (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) $imgs[] = $val;
        }
        if (empty($imgs) && !empty($prodotto->featured_image)) {
            $imgs[] = $prodotto->featured_image;
        }

        // — stock: override da tabella custom se presente
        $stockTbl = $wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1",
            (int)$prodotto->id
        ));
        $disponibilita = ($stockTbl !== null) ? (int)$stockTbl : (int)($prodotto->disponibilita ?? 0);
        $available     = $disponibilita > 0;

        // — payload per Blade + Alpine/cart
        $product = [
            'id'               => (int)$prodotto->id,
            'slug'             => $slug,
            'title'            => (string)$prodotto->title,
            'pretitolo'        => (string)($prodotto->pretitolo ?? ''),
            'permalink'        => (string)$prodotto->url,
            'description_html' => apply_filters('the_content', (string)$prodotto->content),
            'titolo_descrizione' => (string)($prodotto->titolo_descrizione ?? ''),
            'descrizione'      => (string)($prodotto->descrizione ?? ''),
            'categoria'        => (string)($prodotto->categoria ?? ''),

            'price'            => $priceNum,
            'price_formatted'  => number_format($priceNum, 2, ',', '.'),

            'image'            => $imgs[0] ?? $prodotto->featured_image ?? '',
            'gallery'          => $imgs,

            'disponibilita'    => $disponibilita,
            'available'        => $available,

            'cart' => [
                'id'    => (int)$prodotto->id,
                'name'  => (string)$prodotto->title,
                'image' => $imgs[0] ?? $prodotto->featured_image ?? '',
                'price' => $priceNum,
            ],
        ];

        // (opz.) correlati sulla stessa categoria ACF
        $related = [];
        if (!empty($prodotto->categoria)) {
            $candidati = Prodotto::where([
                'posts_per_page' => 16, // prendi un po' di post e poi filtra
                'post__not_in'   => [(int)$prodotto->id],
            ]);
            foreach ($candidati as $p) {
                if (($p->categoria ?? '') !== $prodotto->categoria) continue;

                $pPriceRaw = (string)($p->prezzo ?? '');
                $pNum = (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', $pPriceRaw));
                if (!is_finite($pNum)) $pNum = 0.0;

                $stockRel = $wpdb->get_var($wpdb->prepare(
                    "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1",
                    (int)$p->id
                ));
                $dispRel = ($stockRel !== null) ? (int)$stockRel : (int)($p->disponibilita ?? 0);

                $related[] = [
                    'id'              => (int)$p->id,
                    'title'           => (string)$p->title,
                    'permalink'       => (string)$p->url,
                    'image'           => $p->featured_image ?? '',
                    'price'           => $pNum,
                    'price_formatted' => number_format($pNum, 2, ',', '.'),
                    'disponibilita'   => $dispRel,
                    'available'       => $dispRel > 0,
                ];
                if (count($related) >= 8) break;
            }
        }

        $productForJs = [
            'id'            => $product['id'],
            'title'         => $product['title'],
            'price'         => $product['price'],
            'image'         => $product['image'],
            'gallery'       => $product['gallery'],
            'description'   => $product['descrizione'] ?: wp_strip_all_tags($product['description_html']),
            'disponibilita' => $product['disponibilita'],
            'cart'          => $product['cart'],
        ];
        $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js');
        return $this->render('single-prodotto', [
            'product'      => $product,
            'productForJs' => $productForJs,
            'related'      => $related,
        ]);
    }

    // Dentro il controller (metodo privato)
    private function resolveSlug(): ?string
    {
        // 1) Querystring ?slug=...
        if (!empty($_GET['slug'])) {
            return sanitize_title($_GET['slug']);
        }

        // 2) WordPress: query var "name"
        $name = get_query_var('name');
        if (!empty($name)) {
            return sanitize_title($name);
        }

        // 3) WordPress: oggetto interrogato (es. template singolo)
        $obj = get_queried_object();
        if ($obj && !empty($obj->post_name)) {
            return sanitize_title($obj->post_name);
        }

        // 4) Parse dell’URL (es. /prodotto/{slug} o /prodotti/{slug})
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        if (preg_match('#/(?:prodotto|prodotti)/([^/]+)/?$#i', $path, $m)) {
            return sanitize_title($m[1]);
        }

        return null;
    }
}
