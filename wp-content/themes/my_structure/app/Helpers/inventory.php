<?php

add_action('rest_api_init', function () {
    register_rest_route('sbs/v1', '/inventory', [
        'methods'  => 'GET',
        'callback' => 'sbs_inventory',
        'permission_callback' => '__return_true',
    ]);
});

function sbs_inventory(\WP_REST_Request $req)
{
    $idsParam = (string) $req->get_param('ids');
    $ids = array_filter(array_map('trim', explode(',', $idsParam)));

    if (!$ids) {
        return new \WP_REST_Response(['error' => 'ids missing'], 400);
    }
    if (count($ids) > 100) $ids = array_slice($ids, 0, 100);

    $repo = \Classes\InventoryRepo::instance();
    $out  = [];

    foreach ($ids as $raw) {
        if (strpos($raw, 'kit:') === 0) {
            $kitId = (int) substr($raw, 4);
            if ($kitId <= 0) { $out[] = ['id' => "kit:$kitId", 'available' => false]; continue; }

            $products = (array) get_field('prodotti', $kitId) ?: [];
            $available = !empty($products);
            if ($available) {
                foreach ($products as $p) {
                    $pid = is_object($p) ? (int) ($p->ID ?? 0) : (int) $p;
                    if ($pid <= 0) { $available = false; break; }
                    $stock = $repo->getStock($pid);
                    if ($stock <= 0) { $available = false; break; }
                }
            }

            $out[] = ['id' => "kit:$kitId", 'available' => $available];
        } else {
            $pid   = (int) $raw;
            $stock = $repo->getStock($pid);
            $out[] = ['id' => $pid, 'stock' => max(0, $stock), 'available' => $stock > 0];
        }
    }

    return new \WP_REST_Response($out, 200);
}


add_action('after_setup_theme', function () {
    $current = get_option('sbs_inventory_schema_v', '');
    if ($current === '1') return;

    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table = $wpdb->prefix . 'sbs_inventory';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
      product_id BIGINT(20) UNSIGNED NOT NULL,
      stock INT NOT NULL DEFAULT 0,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (product_id)
    ) {$charset_collate};";

    dbDelta($sql);
    update_option('sbs_inventory_schema_v', '1');
});

// --- BACKFILL UNA-TANTUM DA ACF -> DB ---
add_action('init', function () {
    if (get_option('sbs_inventory_backfilled_v1')) return;

    $q = new WP_Query([
        'post_type'      => 'prodotto',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => ['publish','draft','pending','private']
    ]);

    $repo = \Classes\InventoryRepo::instance();
    foreach (($q->posts ?? []) as $pid) {
        $raw   = get_post_meta($pid, 'disponibilita', true);
        $stock = is_numeric($raw) ? (int)$raw : 0;
        $repo->setStock((int)$pid, max(0, $stock));
    }
    update_option('sbs_inventory_backfilled_v1', 1);
}, 20);

// --- BRIDGE: quando aggiorni ACF, aggiorna anche il DB (finché l'admin usa ACF) ---
add_action('acf/save_post', function ($post_id) {
    if (get_post_type($post_id) !== 'prodotto') return;
    $raw   = get_post_meta($post_id, 'disponibilita', true);
    $stock = is_numeric($raw) ? (int)$raw : 0;
    \Classes\InventoryRepo::instance()->setStock((int)$post_id, max(0, $stock));
}, 20);

function sbs_update_product_disponibilita_field(int $productId, int $newStock): void
{
    // Preferisci ACF se presente
    if (function_exists('update_field')) {
        update_field('disponibilita', $newStock, $productId);
    } else {
        update_post_meta($productId, 'disponibilita', $newStock);
    }
}
