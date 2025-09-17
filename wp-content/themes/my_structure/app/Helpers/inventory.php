<?php
// === MIGRATIONS: creazione tabelle iniziali (v3) ======================
add_action('init', function () {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset_collate = $wpdb->get_charset_collate();

    // ----------------- 1) Tabella Payment Intents -----------------
    $table1 = $wpdb->prefix . 'sbs_payment_intents';
    $sql1 = "
    CREATE TABLE {$table1} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      intent_id VARCHAR(64) NULL,
      cart_token VARCHAR(64) NULL,
      status VARCHAR(32) NULL,
      items_json LONGTEXT NULL,
      amount_subtotal INT NOT NULL DEFAULT 0,
      amount_shipping INT NOT NULL DEFAULT 0,
      amount_discount INT NOT NULL DEFAULT 0,
      amount_tax INT NOT NULL DEFAULT 0,
      amount_total INT NOT NULL DEFAULT 0,
      currency VARCHAR(8) DEFAULT 'EUR',
      email VARCHAR(190) NULL,
      first_name VARCHAR(100) NULL,
      last_name VARCHAR(100) NULL,
      shipping_json LONGTEXT NULL,
      shipping_status VARCHAR(16) NULL,
      user_id BIGINT UNSIGNED NULL,
      client_ip VARCHAR(64) NULL,
      user_agent VARCHAR(255) NULL,
      referrer VARCHAR(512) NULL,
      utm_json LONGTEXT NULL,
      expires_at DATETIME NULL,
      created_at DATETIME NULL,
      updated_at DATETIME NULL,
      PRIMARY KEY (id),
      KEY idx_intent (intent_id),
      KEY idx_token (cart_token),
      KEY idx_status (status),
      KEY idx_email (email)
    ) {$charset_collate};
    ";
    dbDelta($sql1);

    // ----------------- 2) Tabella Cart Events (audit) -----------------
    $table2 = $wpdb->prefix . 'sbs_cart_events';
    $sql2 = "
    CREATE TABLE {$table2} (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      cart_token VARCHAR(64) NOT NULL,
      intent_id VARCHAR(64) NULL,
      type VARCHAR(64) NOT NULL,
      item_id VARCHAR(64) NULL,
      qty INT NULL,
      meta_json LONGTEXT NULL,
      client_ip VARCHAR(64) NULL,
      user_agent VARCHAR(255) NULL,
      created_at DATETIME NULL,
      PRIMARY KEY (id),
      KEY idx_cart (cart_token),
      KEY idx_intent (intent_id),
      KEY idx_type (type)
    ) {$charset_collate};
    ";
    dbDelta($sql2);

    // ----------------- 3) Tabella Inventory -----------------
    $table3 = $wpdb->prefix . 'sbs_inventory';
    $sql3 = "
    CREATE TABLE {$table3} (
      product_id BIGINT UNSIGNED NOT NULL,
      stock INT NOT NULL DEFAULT 0,
      updated_at DATETIME NULL,
      PRIMARY KEY (product_id)
    ) {$charset_collate};
    ";
    dbDelta($sql3);
}, 20);


// === REST API: Inventory ==============================================
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
            if ($kitId <= 0) {
                $out[] = ['id' => "kit:$kitId", 'available' => false];
                continue;
            }

            // ACF: campo "prodotti" sul post del KIT
            $products  = (array) get_field('prodotti', $kitId) ?: [];
            $available = !empty($products);
            if ($available) {
                foreach ($products as $p) {
                    $pid = is_object($p) ? (int) ($p->ID ?? 0) : (int) $p;
                    if ($pid <= 0) {
                        $available = false;
                        break;
                    }
                    $stock = $repo->getStock($pid);
                    if ($stock <= 0) {
                        $available = false;
                        break;
                    }
                }
            }
            $out[] = ['id' => "kit:$kitId", 'available' => $available];
        } else {
            $pid   = (int) $raw;
            $stock = $repo->getStock($pid);
            $out[] = [
                'id'        => $pid,
                'stock'     => max(0, (int)$stock),
                'available' => ((int)$stock) > 0
            ];
        }
    }

    return new \WP_REST_Response($out, 200);
}

// === Backfill iniziale inventory da meta ACF ==========================
add_action('init', function () {
    if (get_option('sbs_inventory_backfilled_v1')) return;

    $q = new WP_Query([
        'post_type'      => 'prodotto',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => ['publish', 'draft', 'pending', 'private']
    ]);

    $repo = \Classes\InventoryRepo::instance();
    foreach (($q->posts ?? []) as $pid) {
        $raw   = get_post_meta($pid, 'disponibilita', true);
        $stock = is_numeric($raw) ? (int)$raw : 0;
        $repo->setStock((int)$pid, max(0, $stock));
    }
    update_option('sbs_inventory_backfilled_v1', 1);
}, 20);

// === Bridge update stock da admin =====================================
add_action('acf/save_post', function ($post_id) {
    if (get_post_type($post_id) !== 'prodotto') return;
    $raw   = get_post_meta($post_id, 'disponibilita', true);
    $stock = is_numeric($raw) ? (int)$raw : 0;
    \Classes\InventoryRepo::instance()->setStock((int)$post_id, max(0, $stock));
}, 20);

function sbs_update_product_disponibilita_field(int $productId, int $newStock): void
{
    if (function_exists('update_field')) {
        update_field('disponibilita', $newStock, $productId);
    } else {
        update_post_meta($productId, 'disponibilita', $newStock);
    }
}

// === Helpers stripe ===================================================
if (!function_exists('sbs_stripe_pk')) {
    function sbs_stripe_pk(): string
    {
        $v = function_exists('my_env') ? my_env('STRIPE_PK') : getenv('STRIPE_PK');
        return $v ?: 'pk_test_XXX';
    }
}
if (!function_exists('sbs_stripe_sk')) {
    function sbs_stripe_sk(): string
    {
        $v = function_exists('my_env') ? my_env('STRIPE_SK') : getenv('STRIPE_SK');
        return $v ?: 'sk_test_XXX';
    }
}
if (!function_exists('sbs_stripe_whsec')) {
    function sbs_stripe_whsec(): string
    {
        $v = function_exists('my_env') ? my_env('STRIPE_WEBHOOK_SECRET') : getenv('STRIPE_WEBHOOK_SECRET');
        return $v ?: 'whsec_xxx';
    }
}
if (!function_exists('sbs_finalize_secret')) {
    function sbs_finalize_secret(): string
    {
        $v = function_exists('my_env') ? my_env('SBS_FINALIZE_SECRET') : getenv('SBS_FINALIZE_SECRET');
        return $v ?: 'finalize_xxx';
    }
}
