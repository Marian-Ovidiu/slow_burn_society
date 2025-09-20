<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Prodotto;
use Models\Kit;

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

        $prodotto = Prodotto::findBySlug($slug);
        if (!$prodotto) {
            return $this->render('404', [], 404);
        }

        // --- helpers --------------------------------------------------------
        $toFloat = function ($val): float {
            if (is_numeric($val)) return (float)$val;
            $s = (string)($val ?? '');
            $s = preg_replace('/[^\d,\.]/', '', $s);
            $s = str_replace(',', '.', $s);
            $num = (float)$s;
            return is_finite($num) ? $num : 0.0;
        };

        // Normalizza WP_Term / array di termini / ID / string in uno slug (o name) stringa
        $catToString = function ($cat): string {
            if (empty($cat)) return '';
            if (is_string($cat)) return $cat;

            // singolo WP_Term
            if ($cat instanceof \WP_Term) {
                return $cat->slug ?: ($cat->name ?? '');
            }

            // array (ACF taxonomy può restituire array di WP_Term o IDs)
            if (is_array($cat)) {
                $first = reset($cat);
                if ($first instanceof \WP_Term) {
                    return $first->slug ?: ($first->name ?? '');
                }
                if (is_numeric($first)) {
                    $t = get_term((int)$first);
                    if ($t && !is_wp_error($t)) return $t->slug ?: ($t->name ?? '');
                }
                if (is_string($first)) return $first;
            }

            // oggetto generico con ->slug / ->name
            if (is_object($cat)) {
                if (!empty($cat->slug)) return (string)$cat->slug;
                if (!empty($cat->name)) return (string)$cat->name;
            }
            return '';
        };

        // — prezzo numerico
        $priceNum = $toFloat($prodotto->prezzo ?? 0);

        // — gallery (immagine_1..4) + fallback featured
        $imgs = [];
        foreach (['immagine_1','immagine_2','immagine_3','immagine_4'] as $k) {
            $val = $prodotto->$k ?? null;
            if (is_array($val) && !empty($val['url']))       $imgs[] = $val['url'];
            elseif (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) $imgs[] = $val;
        }
        if (empty($imgs) && !empty($prodotto->featured_image)) {
            $imgs[] = $prodotto->featured_image;
        }

        // — stock: override tabella custom se presente
        $stockTbl = $wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1",
            (int)$prodotto->id
        ));
        $disponibilita = ($stockTbl !== null) ? (int)$stockTbl : (int)($prodotto->disponibilita ?? 0);
        $available     = $disponibilita > 0;

        // categoria normalizzata (slug o name)
        $categoriaStr = $catToString($prodotto->categoria ?? '');

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
            'categoria'        => $categoriaStr, // 👈 niente cast diretto di WP_Term

            'price'            => $priceNum,
            'price_formatted'  => number_format($priceNum, 2, ',', '.'),

            'image'            => $imgs[0] ?? ($prodotto->featured_image ?? ''),
            'gallery'          => $imgs,

            'disponibilita'    => $disponibilita,
            'available'        => $available,

            'cart' => [
                'id'    => (int)$prodotto->id,
                'name'  => (string)$prodotto->title,
                'image' => $imgs[0] ?? ($prodotto->featured_image ?? ''),
                'price' => $priceNum,
            ],
        ];

        // — correlati misti (kit + prodotti)
        $relatedItems = $this->buildRelatedForProduct($prodotto, $toFloat, $catToString);
        // retro-compat: solo prodotti
        $relatedOnlyProducts = array_values(array_filter($relatedItems, fn($r) => ($r['type'] ?? '') === 'product'));

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
            'product'       => $product,
            'productForJs'  => $productForJs,
            'relatedItems'  => $relatedItems,         // MIX: kit + prodotti
            'related'       => $relatedOnlyProducts,  // SOLO prodotti (compatibilità)
        ]);
    }

    /**
     * Correlati per pagina PRODOTTO (mix kit + prodotti).
     */
    private function buildRelatedForProduct(Prodotto $prodotto, callable $toFloat, callable $catToString): array
    {
        global $wpdb;

        $mix = [];
        $pid = (int)$prodotto->id;
        $cat = $catToString($prodotto->categoria ?? ''); // 👈 normalizzato

        // ---- KIT che contengono questo prodotto ----------------------------
        $kitArgs = [
            'post_type'      => 'kit',
            'posts_per_page' => 8,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
            'meta_query'     => [[
                'key'     => 'prodotti',
                'value'   => '"' . $pid . '"',
                'compare' => 'LIKE',
            ]],
        ];

        $qKits = new \WP_Query($kitArgs);
        foreach ($qKits->posts as $p) {
            $kk = new Kit($p);
            $imgUrl = '';
            if (is_array($kk->immagine_kit ?? null) && !empty($kk->immagine_kit['url'])) {
                $imgUrl = (string)$kk->immagine_kit['url'];
            } else {
                $thumb = get_the_post_thumbnail_url($kk->id, 'large');
                $imgUrl = $thumb ? $thumb : '';
            }

            $kPrice = $toFloat($kk->prezzo ?? 0);
            $kDisp  = (int)($kk->disponibilita ?? 0);

            $mix[] = [
                'type'            => 'kit',
                'id'              => (int)$kk->id,
                'title'           => (string)($kk->nome ?: $kk->title),
                'permalink'       => (string)($kk->url ?? get_permalink((int)$kk->id)),
                'image'           => $imgUrl,
                'price'           => $kPrice,
                'price_formatted' => number_format($kPrice, 2, ',', '.'),
                'disponibilita'   => $kDisp,
                'available'       => $kDisp > 0,
                'cart'            => [
                    'id'     => 'kit:' . (int)$kk->id,
                    'kitId'  => (int)$kk->id,
                    'type'   => 'kit',
                    'name'   => (string)($kk->nome ?: $kk->title),
                    'image'  => $imgUrl,
                    'price'  => $kPrice,
                    'maxQty' => $kDisp,
                ],
            ];
        }
        wp_reset_postdata();

        // Se pochi kit, prendi kit della stessa categoria del prodotto
        if (count(array_filter($mix, fn($r) => $r['type'] === 'kit')) < 2 && !empty($cat)) {
            $qMoreKits = new \WP_Query([
                'post_type'      => 'kit',
                'posts_per_page' => 8,
                'post_status'    => 'publish',
                'no_found_rows'  => true,
                'orderby'        => 'rand',
                'meta_query'     => [[
                    'key'     => 'categoria',
                    'value'   => $cat,
                    'compare' => 'LIKE',
                ]],
            ]);
            foreach ($qMoreKits->posts as $p) {
                $kk = new Kit($p);

                $imgUrl = '';
                if (is_array($kk->immagine_kit ?? null) && !empty($kk->immagine_kit['url'])) {
                    $imgUrl = (string)$kk->immagine_kit['url'];
                } else {
                    $thumb = get_the_post_thumbnail_url($kk->id, 'large');
                    $imgUrl = $thumb ? $thumb : '';
                }

                $kPrice = $toFloat($kk->prezzo ?? 0);
                $kDisp  = (int)($kk->disponibilita ?? 0);

                $mix[] = [
                    'type'            => 'kit',
                    'id'              => (int)$kk->id,
                    'title'           => (string)($kk->nome ?: $kk->title),
                    'permalink'       => (string)($kk->url ?? get_permalink((int)$kk->id)),
                    'image'           => $imgUrl,
                    'price'           => $kPrice,
                    'price_formatted' => number_format($kPrice, 2, ',', '.'),
                    'disponibilita'   => $kDisp,
                    'available'       => $kDisp > 0,
                    'cart'            => [
                        'id'     => 'kit:' . (int)$kk->id,
                        'kitId'  => (int)$kk->id,
                        'type'   => 'kit',
                        'name'   => (string)($kk->nome ?: $kk->title),
                        'image'  => $imgUrl,
                        'price'  => $kPrice,
                        'maxQty' => $kDisp,
                    ],
                ];
            }
            wp_reset_postdata();
        }

        // ---- Prodotti della stessa categoria (escluso il corrente) ----------
        $prodArgs = [
            'post_type'      => 'prodotto',
            'posts_per_page' => 12,
            'post__not_in'   => [$pid],
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
        ];
        if (!empty($cat)) {
            $prodArgs['meta_query'] = [[
                'key'     => 'categoria',
                'value'   => $cat,
                'compare' => 'LIKE',
            ]];
        }

        $qProd = new \WP_Query($prodArgs);
        foreach ($qProd->posts as $p) {
            $ppId = (int)$p->ID;
            $pp   = Prodotto::find($ppId);

            // image: immagine_1 o featured
            $imgArr = is_array($pp->immagine_1 ?? null) ? $pp->immagine_1 : (array) get_field('immagine_1', $ppId);
            $imgUrl = !empty($imgArr['url'])
                ? (string)$imgArr['url']
                : (get_the_post_thumbnail_url($ppId, 'large') ?: '');

            // prezzo
            $pPrice = $toFloat($pp->prezzo ?? get_field('prezzo', $ppId));

            // disponibilità: tabella custom prioritaria
            $stockTbl = $wpdb->get_var($wpdb->prepare(
                "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1",
                $ppId
            ));
            if ($stockTbl !== null) {
                $pDisp = (int)$stockTbl;
            } else {
                $raw = $pp->disponibilita ?? get_field('disponibilita', $ppId);
                if (is_numeric($raw)) $pDisp = (int)$raw;
                else $pDisp = $raw ? 1 : 0;
            }

            $mix[] = [
                'type'            => 'product',
                'id'              => $ppId,
                'title'           => (string)($pp->title ?? get_the_title($ppId)),
                'permalink'       => (string)($pp->url ?? get_permalink($ppId)),
                'image'           => $imgUrl,
                'price'           => $pPrice,
                'price_formatted' => number_format($pPrice, 2, ',', '.'),
                'disponibilita'   => $pDisp,
                'available'       => ($pDisp > 0),
                'cart'            => [
                    'id'    => $ppId,
                    'type'  => 'product',
                    'name'  => (string)($pp->title ?? get_the_title($ppId)),
                    'image' => $imgUrl,
                    'price' => $pPrice,
                ],
            ];
        }
        wp_reset_postdata();

        // ---- Dedupe, riempimento minimo e slicing ---------------------------
        // Dedupe
        $seen = [];
        $dedup = [];
        foreach ($mix as $r) {
            $key = ($r['type'] ?? '') . ':' . ($r['id'] ?? '');
            if ($key && !isset($seen[$key])) {
                $seen[$key] = true;
                $dedup[] = $r;
            }
        }
        $mix = $dedup;

        // Minimo 3: se non bastano, riempi con ultimi contenuti a caso
        if (count($mix) < 3) {
            // fallback KIT
            $qFK = new \WP_Query([
                'post_type'      => 'kit',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
                'no_found_rows'  => true,
                'orderby'        => 'rand',
            ]);
            foreach ($qFK->posts as $p) {
                if (count($mix) >= 3) break;
                $kk = new Kit($p);
                $imgUrl = '';
                if (is_array($kk->immagine_kit ?? null) && !empty($kk->immagine_kit['url'])) {
                    $imgUrl = (string)$kk->immagine_kit['url'];
                } else {
                    $thumb = get_the_post_thumbnail_url($kk->id, 'large');
                    $imgUrl = $thumb ? $thumb : '';
                }
                $kPrice = $toFloat($kk->prezzo ?? 0);
                $kDisp  = (int)($kk->disponibilita ?? 0);
                $mix[] = [
                    'type' => 'kit',
                    'id' => (int)$kk->id,
                    'title' => (string)($kk->nome ?: $kk->title),
                    'permalink' => (string)($kk->url ?? get_permalink((int)$kk->id)),
                    'image' => $imgUrl,
                    'price' => $kPrice,
                    'price_formatted' => number_format($kPrice, 2, ',', '.'),
                    'disponibilita' => $kDisp,
                    'available' => $kDisp > 0,
                    'cart' => [
                        'id' => 'kit:' . (int)$kk->id,
                        'kitId' => (int)$kk->id,
                        'type' => 'kit',
                        'name' => (string)($kk->nome ?: $kk->title),
                        'image' => $imgUrl,
                        'price' => $kPrice,
                        'maxQty' => $kDisp,
                    ],
                ];
            }
            wp_reset_postdata();

            // fallback PRODOTTI
            if (count($mix) < 3) {
                $qFP = new \WP_Query([
                    'post_type'      => 'prodotto',
                    'posts_per_page' => 6,
                    'post__not_in'   => [$pid],
                    'post_status'    => 'publish',
                    'no_found_rows'  => true,
                    'orderby'        => 'rand',
                ]);
                foreach ($qFP->posts as $p) {
                    if (count($mix) >= 3) break;

                    $ppId = (int)$p->ID;
                    $pp   = Prodotto::find($ppId);

                    $imgArr = is_array($pp->immagine_1 ?? null) ? $pp->immagine_1 : (array) get_field('immagine_1', $ppId);
                    $imgUrl = !empty($imgArr['url']) ? (string)$imgArr['url'] : (get_the_post_thumbnail_url($ppId, 'large') ?: '');
                    $pPrice = $toFloat($pp->prezzo ?? get_field('prezzo', $ppId));

                    $stockTbl = $wpdb->get_var($wpdb->prepare(
                        "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1",
                        $ppId
                    ));
                    if ($stockTbl !== null) $pDisp = (int)$stockTbl;
                    else {
                        $raw = $pp->disponibilita ?? get_field('disponibilita', $ppId);
                        $pDisp = is_numeric($raw) ? (int)$raw : ($raw ? 1 : 0);
                    }

                    $mix[] = [
                        'type'            => 'product',
                        'id'              => $ppId,
                        'title'           => (string)($pp->title ?? get_the_title($ppId)),
                        'permalink'       => (string)($pp->url ?? get_permalink($ppId)),
                        'image'           => $imgUrl,
                        'price'           => $pPrice,
                        'price_formatted' => number_format($pPrice, 2, ',', '.'),
                        'disponibilita'   => $pDisp,
                        'available'       => ($pDisp > 0),
                        'cart'            => [
                            'id'    => $ppId,
                            'type'  => 'product',
                            'name'  => (string)($pp->title ?? get_the_title($ppId)),
                            'image' => $imgUrl,
                            'price' => $pPrice,
                        ],
                    ];
                }
                wp_reset_postdata();
            }
        }

        shuffle($mix);
        return array_slice($mix, 4, 4); // 👈 prendi i primi fino a 8 (non da index 4)
    }

    private function resolveSlug(): ?string
    {
        if (!empty($_GET['slug'])) {
            return sanitize_title($_GET['slug']);
        }
        $name = get_query_var('name');
        if (!empty($name)) {
            return sanitize_title($name);
        }
        $obj = get_queried_object();
        if ($obj && !empty($obj->post_name)) {
            return sanitize_title($obj->post_name);
        }
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        if (preg_match('#/(?:prodotto|prodotti)/([^/]+)/?$#i', $path, $m)) {
            return sanitize_title($m[1]);
        }
        return null;
    }
}
