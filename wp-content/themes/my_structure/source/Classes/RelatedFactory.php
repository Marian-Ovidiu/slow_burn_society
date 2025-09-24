<?php

namespace Classes;

use Models\Prodotto;
use Models\Kit;

/**
 * RelatedFactory – costruisce una lista mista di correlati (kit + prodotti)
 * con struttura card uniforme, pronta per Blade/Alpine.
 *
 * - Ritorna array di card con chiavi:
 *   type: 'product'|'kit'
 *   id, title, permalink, image,
 *   price (float), price_formatted (string),
 *   disponibilita (int), available (bool),
 *   cart: payload per il carrello (id/type/name/image/price/qty/maxQty)
 *
 * API:
 *   RelatedFactory::forProduct(Prodotto $p, array $opts = []): array
 *   RelatedFactory::forKit(Kit $k, array $opts = []): array
 *   RelatedFactory::forEntity(Prodotto|Kit $e, array $opts = []): array
 *
 * Opzioni supportate ($opts):
 *   - min (default 3)  : minimo elementi da restituire (se disponibili)
 *   - max (default 8)  : massimo elementi
 *   - exclude_ids      : array di ID (numerici per product, 'kit:ID' gestiti interni)
 *   - prefer_category  : bool, prova ad includere elementi della stessa categoria
 */
class RelatedFactory
{
    // ---------- API pubblica -------------------------------------------------

    /** Router unico: passa Prodotto o Kit e lui sceglie la strategia */
    public static function forEntity($entity, array $opts = []): array
    {
        if ($entity instanceof Prodotto) {
            return static::forProduct($entity, $opts);
        }
        if ($entity instanceof Kit) {
            return static::forKit($entity, $opts);
        }
        return [];
    }

    /** Correlati per pagina PRODOTTO (mix kit + prodotti) */
    public static function forProduct(Prodotto $prodotto, array $opts = []): array
    {
        global $wpdb;

        $min  = (int)($opts['min'] ?? 3);
        $max  = (int)($opts['max'] ?? 8);
        $pref = (bool)($opts['prefer_category'] ?? true);

        $toFloat      = fn($v) => static::toFloat($v);
        $catToString  = fn($c) => static::categoryToString($c);

        $pid = (int)$prodotto->id;
        $cat = $catToString($prodotto->categoria ?? '');

        $mix = [];

        // 1) KIT che contengono questo prodotto
        $mix = array_merge($mix, static::kitsContainingProduct($pid));

        // 2) Se pochi kit e ho categoria, prova kit stessa categoria
        if ($pref && static::countType($mix, 'kit') < 2 && !empty($cat)) {
            $mix = array_merge($mix, static::kitsByCategory($cat));
        }

        // 3) Prodotti stessa categoria (escludi il corrente)
        $mix = array_merge($mix, static::productsByCategory($cat, [$pid]));

        // 4) Fallback se pochi: kit random + prodotti random (escludendo il corrente)
        [$fallbackK, $fallbackP] = static::fallbackPools([$pid]);
        $mix = array_merge($mix, $fallbackK, $fallbackP);

        // 5) Dedupe (type:id) + shuffle + slice
        $mix = static::dedupe($mix);
        if (!empty($mix)) shuffle($mix);

        return array_slice($mix, 0, max(0, $max));
    }

    /** Correlati per pagina KIT (mix random, escluso kit corrente e items interni) */
    public static function forKit(Kit $kit, array $opts = []): array
    {
        global $wpdb;

        $min = (int)($opts['min'] ?? 3);
        $max = (int)($opts['max'] ?? 8);

        // ID prodotti dentro al kit (da escludere dal pool prodotti)
        $productIdsInKit = static::productIdsInKit($kit);

        // Pool kit (escludi questo)
        $kitsPool = static::allKits(excludeIds: [(int)$kit->id]);

        // Pool prodotti (escludi quelli nel kit)
        $productsPool = static::allProducts(excludeIds: $productIdsInKit);

        $pool = array_merge($kitsPool, $productsPool);
        if (!empty($pool)) shuffle($pool);

        // Prendi max, cerca di garantire almeno min se possibile
        $take = min($max, count($pool));
        $selected = array_slice($pool, 0, $take);

        if (count($selected) < $min) {
            $needed = $min - count($selected);
            $extra  = array_slice($pool, $take, $needed);
            $selected = array_merge($selected, $extra);
        }

        return $selected;
    }

    // ---------- Helpers di dominio (query + normalizzazioni) ----------------

    private static function toFloat($val): float
    {
        if (is_numeric($val)) return (float)$val;
        $s = (string)($val ?? '');
        $s = preg_replace('/[^\d,\.]/', '', $s);
        $s = str_replace(',', '.', $s);
        $num = (float)$s;
        return is_finite($num) ? $num : 0.0;
    }

    private static function categoryToString($cat): string
    {
        if (empty($cat)) return '';
        if (is_string($cat)) return $cat;

        if ($cat instanceof \WP_Term) {
            return $cat->slug ?: ($cat->name ?? '');
        }

        if (is_array($cat)) {
            $first = reset($cat);
            if ($first instanceof \WP_Term) return $first->slug ?: ($first->name ?? '');
            if (is_numeric($first)) {
                $t = get_term((int)$first);
                return ($t && !is_wp_error($t)) ? ($t->slug ?: ($t->name ?? '')) : '';
            }
            if (is_string($first)) return $first;
        }

        if (is_object($cat)) {
            if (!empty($cat->slug)) return (string)$cat->slug;
            if (!empty($cat->name)) return (string)$cat->name;
        }
        return '';
    }

    private static function imageForProduct(int $pid, ?Prodotto $pp = null): string
    {
        $imgArr = is_array($pp->immagine_1 ?? null) ? $pp->immagine_1 : (array) get_field('immagine_1', $pid);
        if (!empty($imgArr['url'])) return (string)$imgArr['url'];
        $thumb = get_the_post_thumbnail_url($pid, 'large');
        return $thumb ? $thumb : '';
    }

    private static function imageForKit(Kit $kk): string
    {
        if (is_array($kk->immagine_kit ?? null) && !empty($kk->immagine_kit['url'])) {
            return (string)$kk->immagine_kit['url'];
        }
        $thumb = get_the_post_thumbnail_url((int)$kk->id, 'large');
        return $thumb ? $thumb : '';
    }

    private static function stockForProduct(int $pid): int
    {
        global $wpdb;
        $tbl = $wpdb->get_var($wpdb->prepare(
            "SELECT stock FROM {$wpdb->prefix}sbs_inventory WHERE product_id = %d LIMIT 1",
            $pid
        ));
        if ($tbl !== null) return (int)$tbl;

        $raw = get_field('disponibilita', $pid);
        if (is_numeric($raw)) return (int)$raw;
        if (is_bool($raw))    return $raw ? 1 : 0;
        if (is_string($raw))  return in_array(mb_strtolower(trim($raw)), ['1','true','si','sì','disponibile','available'], true) ? 1 : 0;
        return 0;
    }

    private static function productCard(int $pid, ?Prodotto $pp = null): array
    {
        $pp     = $pp ?: Prodotto::find($pid);
        $title  = (string)($pp->title ?? get_the_title($pid));
        $image  = static::imageForProduct($pid, $pp);
        $price  = static::toFloat($pp->prezzo ?? get_field('prezzo', $pid));
        $disp   = static::stockForProduct($pid);
        $url    = (string)($pp->url ?? get_permalink($pid));

        return [
            'type'            => 'product',
            'entity'          => 'product',
            'id'              => $pid,
            'title'           => $title,
            'permalink'       => $url,
            'image'           => $image,
            'price'           => $price,
            'price_formatted' => number_format($price, 2, ',', '.'),
            'disponibilita'   => $disp,
            'available'       => $disp > 0,
            'cart'            => [
                'id'        => (string)$pid,
                'productId' => (int)$pid,
                'type'      => 'product',
                'name'      => $title,
                'image'     => $image,
                'price'     => (float)$price,
                'qty'       => 1,
                'maxQty'    => (int)$disp,
            ],
        ];
    }

    private static function kitCard(Kit $kk): array
    {
        $id    = (int)$kk->id;
        $title = (string)($kk->nome ?: $kk->title);
        $image = static::imageForKit($kk);
        $price = static::toFloat($kk->prezzo ?? 0);
        $disp  = (int)($kk->disponibilita ?? 0);
        $url   = (string)($kk->url ?? get_permalink($id));

        return [
            'type'            => 'kit',
            'entity'          => 'kit',
            'id'              => $id,
            'title'           => $title,
            'permalink'       => $url,
            'image'           => $image,
            'price'           => $price,
            'price_formatted' => number_format($price, 2, ',', '.'),
            'disponibilita'   => $disp,
            'available'       => $disp > 0,
            'cart'            => [
                'id'     => 'kit:' . $id,
                'kitId'  => $id,
                'type'   => 'kit',
                'name'   => $title,
                'image'  => $image,
                'price'  => (float)$price,
                'qty'    => 1,
                'maxQty' => (int)$disp,
            ],
        ];
    }

    // ---------- Query builders ----------------------------------------------

    /** Tutti i kit random (con esclusioni) */
    private static function allKits(array $excludeIds = []): array
    {
        $res = [];
        $q = new \WP_Query([
            'post_type'      => 'kit',
            'posts_per_page' => 24,
            'post__not_in'   => array_map('intval', $excludeIds),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
        ]);
        foreach ($q->posts as $p) {
            $res[] = static::kitCard(new Kit($p));
        }
        wp_reset_postdata();
        return $res;
    }

    /** Tutti i prodotti random (con esclusioni) */
    private static function allProducts(array $excludeIds = []): array
    {
        $res = [];
        $q = new \WP_Query([
            'post_type'      => 'prodotto',
            'posts_per_page' => 24,
            'post__not_in'   => array_map('intval', $excludeIds),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
        ]);
        foreach ($q->posts as $p) {
            $res[] = static::productCard((int)$p->ID);
        }
        wp_reset_postdata();
        return $res;
    }

    /** Kit che contengono un prodotto specifico */
    private static function kitsContainingProduct(int $productId): array
    {
        $out = [];
        $q = new \WP_Query([
            'post_type'      => 'kit',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
            'meta_query'     => [[
                'key'     => 'prodotti',
                'value'   => '"' . $productId . '"',
                'compare' => 'LIKE',
            ]],
        ]);
        foreach ($q->posts as $p) {
            $out[] = static::kitCard(new Kit($p));
        }
        wp_reset_postdata();
        return $out;
    }

    /** Kit della stessa categoria */
    private static function kitsByCategory(string $cat): array
    {
        if ($cat === '') return [];
        $out = [];
        $q = new \WP_Query([
            'post_type'      => 'kit',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
            'meta_query'     => [[
                'key'     => 'categoria',
                'value'   => $cat,
                'compare' => 'LIKE',
            ]],
        ]);
        foreach ($q->posts as $p) {
            $out[] = static::kitCard(new Kit($p));
        }
        wp_reset_postdata();
        return $out;
    }

    /** Prodotti della stessa categoria (con esclusioni) */
    private static function productsByCategory(string $cat, array $excludeIds = []): array
    {
        $args = [
            'post_type'      => 'prodotto',
            'posts_per_page' => 24,
            'post__not_in'   => array_map('intval', $excludeIds),
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'orderby'        => 'rand',
        ];
        if ($cat !== '') {
            $args['meta_query'] = [[
                'key'     => 'categoria',
                'value'   => $cat,
                'compare' => 'LIKE',
            ]];
        }
        $out = [];
        $q = new \WP_Query($args);
        foreach ($q->posts as $p) {
            $out[] = static::productCard((int)$p->ID);
        }
        wp_reset_postdata();
        return $out;
    }

    /** Fallback pools se mancano elementi */
    private static function fallbackPools(array $excludeProductIds = []): array
    {
        $fallbackK = static::allKits();
        $fallbackP = static::allProducts($excludeProductIds);
        return [$fallbackK, $fallbackP];
    }

    private static function productIdsInKit(Kit $k): array
    {
        $ids = [];
        $raw = is_array($k->prodotti ?? null) ? $k->prodotti : [];
        foreach ($raw as $it) {
            $pid = is_object($it) ? (int)($it->ID ?? $it->id ?? 0) : (int)$it;
            if ($pid > 0) $ids[] = $pid;
        }
        return array_values(array_unique($ids));
    }

    private static function countType(array $arr, string $type): int
    {
        $c = 0;
        foreach ($arr as $r) if (($r['type'] ?? '') === $type) $c++;
        return $c;
    }

    private static function dedupe(array $arr): array
    {
        $seen = [];
        $out  = [];
        foreach ($arr as $r) {
            $key = ($r['type'] ?? '') . ':' . ($r['id'] ?? '');
            if ($key && !isset($seen[$key])) {
                $seen[$key] = true;
                $out[] = $r;
            }
        }
        return $out;
    }
}
