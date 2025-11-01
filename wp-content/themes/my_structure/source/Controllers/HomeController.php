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
            $permalink = function_exists('get_permalink') ? (get_permalink($p->id) ?: '#') : '#';
            $forJs['permalink'] = $permalink;

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
            $permalink = function_exists('get_permalink') ? (get_permalink($k->id) ?: '#') : '#';
            $kitsForJs[$k->id]['permalink'] = $permalink;
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

    public function blog()
    {
    // --- Filtri da querystring ---
    $q     = isset($_GET['q'])   ? sanitize_text_field($_GET['q']) : '';
    $cat   = isset($_GET['cat']) ? sanitize_key($_GET['cat'])      : ''; // slug categoria
    $sort  = isset($_GET['sort'])? sanitize_key($_GET['sort'])     : 'new';
    $paged = max(1, get_query_var('paged') ?: (isset($_GET['paged']) ? (int) $_GET['paged'] : 1));

    // --- Query articoli ---
    $args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'paged'          => $paged,
        's'              => $q,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    if ($cat) {
        // Slug della categoria (es. "cbd", "accessori")
        $args['category_name'] = $cat;
    }

    // Ordina per "più letti" se richiesto (usa la tua meta key views)
    if ($sort === 'pop') {
        $args['meta_key'] = 'post_views_count'; // cambia se usi un'altra chiave
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
    }

    $query = new \WP_Query($args);

    // --- Featured: preferisci sticky; se non c'è, prendi il primo della lista ---
    $featured = null;
    $sticky   = get_option('sticky_posts');

    if (!empty($sticky)) {
        $featQ = new \WP_Query([
            'post__in'            => $sticky,
            'posts_per_page'      => 1,
            'ignore_sticky_posts' => 1,
        ]);
        if ($featQ->have_posts()) {
            $featQ->the_post();
            $featured = get_post();
            wp_reset_postdata();
        }
    }
    if (!$featured && $query->have_posts()) {
        $featured = $query->posts[0];
    }

    // --- Dati sidebar ---
    $categories  = get_categories(['hide_empty' => true]);
    $popularTags = get_terms([
        'taxonomy'   => 'post_tag',
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 12,
        'hide_empty' => true,
    ]);

    // --- Pagination compat con i tuoi helper Blade (usa il WP global) ---
    global $wp_query;
    $wp_query = $query; // così get_next_posts_link / get_previous_posts_link funzionano

    // La tua view usa $latest come lista dei post
    $latest = $query->posts;

    $this->render('home_for_blog', compact('latest', 'featured', 'categories', 'popularTags'));
    }
}
