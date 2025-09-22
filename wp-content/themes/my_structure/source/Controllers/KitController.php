<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Kit;
use Models\Prodotto;

class KitController extends BaseController
{
    public function archive()
    {
        $this->render('archivio-kit', []);
    }

    public function single()
    {
        // Asset JS
        $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js');

        global $wpdb;

        $slug = $this->resolveSlug();
        if (!$slug) {
            return $this->render('404', [], 404);
        }

        $k = Kit::findBySlug($slug);
        if (!$k) {
            return $this->render('404', [], 404);
        }

        // --- helpers robusti -------------------------------------------------
        $get = function ($src, array $keys, $default = null) {
            foreach ($keys as $key) {
                if (is_array($src) && array_key_exists($key, $src)) return $src[$key];
                if (is_object($src) && isset($src->$key)) return $src->$key;
            }
            return $default;
        };
        $toFloat = function ($val): float {
            if (is_numeric($val)) return (float) $val;
            $s = (string) ($val ?? '');
            $s = preg_replace('/[^\d,\.]/', '', $s);
            $s = str_replace(',', '.', $s);
            $num = (float) $s;
            return is_finite($num) ? $num : 0.0;
        };
        $firstUrl = function ($maybe) {
            if (is_array($maybe) && !empty($maybe['url'])) return (string) $maybe['url'];
            if (is_string($maybe) && filter_var($maybe, FILTER_VALIDATE_URL)) return $maybe;
            return '';
        };

        // --- prezzo kit ------------------------------------------------------
        $priceNum = $toFloat($get($k, ['prezzo'], 0));

        // --- immagine principale del kit (NO gallery) -----------------------
        $kitImageMain = '';
        if (is_array($k->immagine_kit) && !empty($k->immagine_kit['url'])) {
            $kitImageMain = (string) $k->immagine_kit['url'];
        } else {
            $kitImageMain = $firstUrl($get($k, ['featured_image'])) ?: '';
        }

        // --- normalizza i prodotti contenuti nel kit ------------------------
        $items = [];
        $minStock = null;

        $rawItems = $get($k, ['prodotti'], []);
        if (!is_array($rawItems) && !($rawItems instanceof \Traversable)) {
            $rawItems = [];
        }

        foreach ($rawItems as $it) {
            // estrai ID
            if (is_numeric($it)) {
                $pid = (int) $it;
            } elseif (is_object($it)) {
                $pid = (int) ($it->id ?? $it->ID ?? $it->post_id ?? 0);
            } elseif (is_array($it)) {
                $pid = (int) ($it['id'] ?? $it['ID'] ?? $it['post_id'] ?? 0);
            } else {
                $pid = 0;
            }

            $p = $pid ? Prodotto::find($pid) : null;

            // prezzo componente
            $pPrice = $p ? $get($p, ['prezzo'], 0) : $get($it, ['prezzo', 'price'], 0);
            $pNum   = $toFloat($pPrice);

            // url componente (senza shorthand ?:)
            if ($p) {
                $defaultPermalink = function_exists('get_permalink') ? get_permalink($pid) : '';
                $pUrlRaw = $get($p, ['url'], $defaultPermalink);
                $pUrl = (string) $pUrlRaw;
            } else {
                $pUrl = (string) ($get($it, ['url', 'permalink'], ''));
            }

            // stock componente (tabella custom prioritaria)
            $pDisp = 0;
            if ($pid) {
                $pStockTbl = $wpdb->get_var($wpdb->prepare(
                    "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1",
                    (int) $pid
                ));
                if ($pStockTbl !== null) {
                    $pDisp = (int) $pStockTbl;
                } else {
                    $pDisp = (int) ($p ? $get($p, ['disponibilita'], 0) : $get($it, ['disponibilita'], 0));
                }
            } else {
                $pDisp = (int) $get($it, ['disponibilita'], 0);
            }

            // immagine_1 (array ACF) + fallback semplice
            $pImageArr = is_array($p->immagine_1 ?? null) ? $p->immagine_1 : (array) get_field('immagine_1', $pid);
            $fallbackImg = '';
            if (!empty($pImageArr['url'])) {
                $fallbackImg = (string) $pImageArr['url'];
            }

            $items[] = [
                'id'              => $pid ?: (string) $get($it, ['id', 'ID', 'post_id'], ''),
                'title'           => $p ? (string) $get($p, ['title'], '') : (string) $get($it, ['title', 'name'], ''),
                'url'             => $pUrl,
                'immagine_1'      => $pImageArr,
                'image'           => $fallbackImg,
                'short'           => (string) $get($it, ['short', 'mini_descrizione', 'excerpt'], ''),
                'price'           => $pNum,
                'price_formatted' => number_format($pNum, 2, ',', '.'),
                'disponibilita'   => $pDisp,
                'available'       => $pDisp > 0,
            ];

            $minStock = ($minStock === null) ? $pDisp : min($minStock, $pDisp);
        }

        // --- disponibilità kit ---------------------------------------------
        $disponibilitaKit = (isset($k->disponibilita) && is_numeric($k->disponibilita))
            ? (int) $k->disponibilita
            : (int) ($minStock ?? 0);

        $availableKit = $disponibilitaKit > 0;

        // --- payload per Blade ----------------------------------------------
        $kit = [
            'id'               => (int) $k->id,
            'slug'             => (string) $slug,
            'title'            => (string) $k->title,
            'pretitolo'        => (string) ($k->pretitolo ?? ''),
            'permalink'        => (string) ($k->url ?? (function_exists('get_permalink') ? get_permalink((int) $k->id) : '')),
            'description_html' => apply_filters('the_content', (string) $k->content),
            'descrizione'      => (string) ($k->descrizione ?? ''),
            'categoria'        => (string) ($k->categoria ?? ''),

            'price'            => $priceNum,
            'price_formatted'  => number_format($priceNum, 2, ',', '.'),

            'image'            => $kitImageMain,         // SOLO image
            'gallery'          => [$kitImageMain],       // retro-compat

            'disponibilita'    => $disponibilitaKit,
            'available'        => $availableKit,

            'cart' => [
                'id'     => 'kit:' . (int) $k->id,
                'kitId'  => (int) $k->id,
                'type'   => 'kit',
                'name'   => (string) $k->title,
                'image'  => $kitImageMain,
                'price'  => $priceNum,
                'maxQty' => $disponibilitaKit,
            ],
        ];

        $kitForJs = [
            'id'            => $kit['id'],
            'title'         => $kit['title'],
            'price'         => $kit['price'],
            'image'         => $kit['image'],
            'gallery'       => $kit['gallery'],
            'description'   => $kit['descrizione'] ?: wp_strip_all_tags($kit['description_html']),
            'cart'          => $kit['cart'],
            'disponibilita' => $kit['disponibilita'],
        ];

        // --- correlati RANDOM: almeno 3 (mix kit/prodotti), escluso kit corrente ---
        $relatedKits = $this->buildRelatedRandom($k, $items, $toFloat, 4, 4);

        return $this->render('single-kit', [
            'kit'         => $kit,
            'kitForJs'    => $kitForJs,
            'items'       => $items,        // prodotti contenuti nel kit
            'relatedKits' => $relatedKits,  // lista mista random (kit e/o prodotti)
        ]);
    }

    /**
     * Correlati casuali: mix di kit e prodotti, escluso il kit corrente.
     * Prova a restituire almeno $min (default 3) e max $max (default 8).
     * Esclude i prodotti già dentro il kit per varietà.
     */
    private function buildRelatedRandom(Kit $k, array $items, callable $toFloat, int $min = 3, int $max = 8): array
    {
        // 1) colleziona gli ID prodotto contenuti nel kit (per escluderli dai correlati prodotto)
        $productIdsInKit = [];
        foreach ($items as $it) {
            $pid = (int) ($it['id'] ?? 0);
            if ($pid > 0) $productIdsInKit[] = $pid;
        }
        $productIdsInKit = array_values(array_unique($productIdsInKit));

        // 2) pool KIT (escludi quello corrente)
        $kitsPool = [];
        $qKits = new \WP_Query([
            'post_type'      => 'kit',
            'posts_per_page' => 24,
            'post__not_in'   => [(int) $k->id],
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand', // randomizza a livello query (oltre a shuffle lato PHP)
        ]);
        foreach ($qKits->posts as $post) {
            $kk = new Kit($post);
            $price = $toFloat($kk->prezzo ?? 0);

            // immagine
            $img = '';
            if (is_array($kk->immagine_kit ?? null) && !empty($kk->immagine_kit['url'])) {
                $img = (string) $kk->immagine_kit['url'];
            } else {
                $thumb = get_the_post_thumbnail_url($kk->id, 'large');
                $img = $thumb ? $thumb : '';
            }

            $disp = (int) ($kk->disponibilita ?? 0);

            $kitsPool[] = [
                'type'            => 'kit',
                'entity'          => 'kit',
                'id'              => (int) $kk->id,
                'title'           => (string) ($kk->nome ?: $kk->title),
                'permalink'       => (string) ($kk->url ?? get_permalink((int) $kk->id)),
                'image'           => $img,
                'price'           => $price,
                'price_formatted' => number_format($price, 2, ',', '.'),
                'disponibilita'   => $disp,
                'available'       => $disp > 0,
                'cart'            => [
                    'id'     => 'kit:' . (int) $kk->id,
                    'kitId'  => (int) $kk->id,
                    'type'   => 'kit',
                    'name'   => (string) ($kk->nome ?: $kk->title),
                    'image'  => $img,
                    'price'  => (float) $price,
                    'qty'    => 1,
                    'maxQty' => (int) $disp,
                ],
            ];
        }
        wp_reset_postdata();

        // 3) pool PRODOTTI (escludi quelli dentro al kit)
        $productsPool = [];
        $qProd = new \WP_Query([
            'post_type'      => 'prodotto',
            'posts_per_page' => 24,
            'post__not_in'   => $productIdsInKit,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
        ]);
        foreach ($qProd->posts as $post) {
            $pid = (int) $post->ID;
            $pp  = Prodotto::find($pid);

            // immagine 1
            $imgArr = is_array($pp->immagine_1 ?? null) ? $pp->immagine_1 : (array) get_field('immagine_1', $pid);
            $imgUrl = !empty($imgArr['url']) ? (string) $imgArr['url'] : (get_the_post_thumbnail_url($pid, 'large') ?: '');

            $pPrice = $toFloat($pp->prezzo ?? get_field('prezzo', $pid));
            $rawDisp = isset($pp->disponibilita) ? $pp->disponibilita : get_field('disponibilita', $pid);
            if (is_numeric($rawDisp)) $pDisp = (int) $rawDisp;
            elseif (is_bool($rawDisp)) $pDisp = $rawDisp ? 1 : 0;
            else $pDisp = 0;

            $productsPool[] = [
                'type'            => 'product',
                'entity'          => 'product',
                'id'              => $pid,
                'title'           => (string) ($pp->title ?? get_the_title($pid)),
                'permalink'       => (string) ($pp->url ?? get_permalink($pid)),
                'image'           => $imgUrl,
                'immagine_1'      => $imgArr, // utile se in view preferisci p.immagine_1.url
                'price'           => $pPrice,
                'price_formatted' => number_format($pPrice, 2, ',', '.'),
                'disponibilita'   => $pDisp,
                'available'       => ($pDisp > 0),
                'cart'            => [
                    'id'       => (string) $pid,   // prodotto -> ID numerico (stringa)
                    'productId' => (int) $pid,
                    'type'     => 'product',
                    'name'     => (string) ($pp->title ?? get_the_title($pid)),
                    'image'    => $imgUrl,
                    'price'    => (float) $pPrice,
                    'qty'      => 1,
                    'maxQty'   => (int) $pDisp,
                ],
            ];
        }
        wp_reset_postdata();

        // 4) mescola i pool e prendi un mix random
        $pool = array_merge($kitsPool, $productsPool);
        if (!empty($pool)) {
            shuffle($pool);
        }

        $take = count($pool);
        if ($take > $max) $take = $max;
        if ($take < $min) $take = min($min, count($pool)); // se non bastano elementi, prendi quanti ce ne sono

        $selected = array_slice($pool, 0, $take);

        // se per qualche motivo < $min ma ci sono ancora elementi, prova a riempire
        if (count($selected) < $min && count($pool) > count($selected)) {
            $needed = $min - count($selected);
            $extra  = array_slice($pool, $take, $needed);
            $selected = array_merge($selected, $extra);
        }

        return $selected;
    }

    // Risolve lo slug come nel controller prodotto
    private function resolveSlug(): ?string
    {
        if (!empty($_GET['slug'])) return sanitize_title($_GET['slug']);
        $name = get_query_var('name');
        if (!empty($name)) return sanitize_title($name);
        $obj = get_queried_object();
        if ($obj && !empty($obj->post_name)) return sanitize_title($obj->post_name);
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        if (preg_match('#/(?:kit|kits|bundle|bundles)/([^/]+)/?$#i', $path, $m)) {
            return sanitize_title($m[1]);
        }
        return null;
    }
}
