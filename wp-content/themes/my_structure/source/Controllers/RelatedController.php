<?php

namespace Controllers;

use Core\Bases\BaseController;
use WP_Query;

class RelatedController extends BaseController
{
    /**
     * GET /related?in_cart_ids=1,2,3&limit=3
     * Ritorna prodotti/kit correlati per categoria, esclusi quelli già nel carrello.
     * Output JSON: [{ id, title, price, image, permalink }]
     */
    public function related()
    {
        // Sanitizza input
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
        if ($limit < 1) $limit = 1;
        if ($limit > 6) $limit = 6;

        $in_cart_ids = [];
        if (!empty($_GET['in_cart_ids'])) {
            $in_cart_ids = array_filter(array_map('intval', explode(',', $_GET['in_cart_ids'])));
        }

        // Categorie dai prodotti in carrello (tassonomia product_cat)
        $cat_ids = [];
        foreach ($in_cart_ids as $pid) {
            $terms = get_the_terms($pid, 'product_cat');
            if (is_array($terms)) {
                foreach ($terms as $t) {
                    $cat_ids[$t->term_id] = true;
                }
            }
        }
        $cat_ids = array_keys($cat_ids);

        // Query principale: stessa/e categoria/e
        $args = [
            'post_type'      => ['prodotto', 'kit'], // includi entrambi
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'rand',
            'post__not_in'   => $in_cart_ids,
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
        ];

        if (!empty($cat_ids)) {
            $args['tax_query'] = [[
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat_ids,
                'operator' => 'IN',
            ]];
        }

        $q = new WP_Query($args);
        $posts = $q->posts;

        // Fallback: random su entrambi i post type (se nessuna corrispondenza per categoria)
        if (!$posts) {
            $fallback = new WP_Query([
                'post_type'      => ['prodotto', 'kit'],
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'post__not_in'   => $in_cart_ids,
                'orderby'        => 'rand',
            ]);
            $posts = $fallback->posts;
        }

        // Helpers locali
        $resolve_image = function (int $id, string $post_type): string {
            // prova in ordine i campi richiesti
            $field_key = $post_type === 'kit' ? 'immagine_kit' : 'immagine_1';

            // se hai ACF, get_field potrebbe restituire: URL, ID, o array (es. ['ID'=>..,'url'=>..])
            if (function_exists('get_field')) {
                $val = get_field($field_key, $id);
                if (!empty($val)) {
                    // Array con chiavi note
                    if (is_array($val)) {
                        if (!empty($val['url'])) return (string)$val['url'];
                        if (!empty($val['ID'])) {
                            $u = wp_get_attachment_image_url((int)$val['ID'], 'medium');
                            if ($u) return $u;
                        }
                    }
                    // ID numerico
                    if (is_numeric($val)) {
                        $u = wp_get_attachment_image_url((int)$val, 'medium');
                        if ($u) return $u;
                    }
                    // URL stringa
                    if (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) {
                        return $val;
                    }
                }
            }

            // fallback: featured image
            $thumb = get_the_post_thumbnail_url($id, 'medium');
            return $thumb ? $thumb : '';
        };

        $resolve_price = function (int $id, string $post_type): float {
            // prova varie meta comuni
            $keys = ['_price', 'price', 'prezzo', 'kit_price', 'prezzo_kit'];
            foreach ($keys as $k) {
                $v = get_post_meta($id, $k, true);
                if ($v !== '' && $v !== null) {
                    // normalizza decimali con virgola
                    $v = is_string($v) ? str_replace(['€', ' '], '', $v) : $v;
                    $num = floatval(str_replace(',', '.', (string)$v));
                    if ($num > 0) return $num;
                }
            }
            return 0.0;
        };

        // Mappatura output
        $items = array_map(function ($p) use ($resolve_image, $resolve_price) {
            $id        = (int)$p->ID;
            $post_type = get_post_type($id) ?: 'prodotto'; // 'prodotto' | 'kit'
            $title     = get_the_title($id);
            $price     = $resolve_price($id, $post_type);
            $image     = $resolve_image($id, $post_type);
            $link      = get_permalink($id);

            return [
                'id'        => $id,
                'type'      => ($post_type === 'kit' ? 'kit' : 'product'), // 👈 AGGIUNTO
                'title'     => $title,
                'price'     => $price,
                'image'     => $image,
                'permalink' => $link,
            ];
        }, $posts ?: []);

        // Output JSON
        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($items, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
