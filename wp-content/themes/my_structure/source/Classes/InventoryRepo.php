<?php

namespace Classes;

class InventoryRepo
{
    private static $instance;
    /** @var \wpdb */
    private $db;
    private $table;

    private function __construct()
    {
        global $wpdb;
        $this->db    = $wpdb;
        $this->table = $wpdb->prefix . 'sbs_inventory';
    }

    public static function instance(): self
    {
        return self::$instance ?? (self::$instance = new self());
    }

    public function getStock(int $productId): int
    {
        if ($productId <= 0) return 0;
        $row = $this->db->get_row(
            $this->db->prepare("SELECT stock FROM {$this->table} WHERE product_id = %d", $productId)
        );
        return $row ? max(0, (int)$row->stock) : 0;
    }

    public function setStock(int $productId, int $stock): void
    {
        $stock = max(0, $stock);
        $this->db->replace(
            $this->table,
            ['product_id' => $productId, 'stock' => $stock, 'updated_at' => current_time('mysql')],
            ['%d', '%d', '%s']
        );
    }

    // Step 3 userà questo:
    public function decrementManyAtomically(array $items): bool
    {
        // normalizza + ordina
        $norm = [];
        foreach ($items as $it) {
            $id  = (int)($it['id'] ?? 0);
            $qty = (int)($it['qty'] ?? 0);
            if ($id > 0 && $qty > 0) $norm[] = ['id' => $id, 'qty' => $qty];
        }
        usort($norm, fn($a, $b) => $a['id'] <=> $b['id']);

        $this->db->query('START TRANSACTION');
        foreach ($norm as $it) {
            $affected = $this->db->query($this->db->prepare(
                "UPDATE {$this->table}
             SET stock = stock - %d
             WHERE product_id = %d AND stock >= %d",
                $it['qty'],
                $it['id'],
                $it['qty']
            ));
            if ($affected === 0) {
                $this->db->query('ROLLBACK');
                return false;
            }
        }
        $this->db->query('COMMIT');
        return true;
    }
}

function sbs_expand_cart_items(array $cart): array
{
    $flat = [];
    foreach ($cart as $line) {
        $qtyLine = (int)($line['qty'] ?? 0);
        if ($qtyLine <= 0) continue;

        if (!empty($line['kitId'])) {
            $kitId = (int)$line['kitId'];
            if ($kitId <= 0) continue;
            $products = (array) get_field('prodotti', $kitId) ?: [];
            foreach ($products as $p) {
                $pid = is_object($p) ? (int)($p->ID ?? 0) : (int)$p;
                if ($pid <= 0) continue;
                $perKit = 1;
                $flat[$pid] = ($flat[$pid] ?? 0) + ($qtyLine * $perKit);
            }
        } else {
            $pid = (int)($line['id'] ?? 0);
            if ($pid <= 0) continue;
            $flat[$pid] = ($flat[$pid] ?? 0) + $qtyLine;
        }
    }
    return array_map(
        fn($pid, $q) => ['id' => (int)$pid, 'qty' => (int)$q],
        array_keys($flat),
        $flat
    );
}


add_action('rest_api_init', function () {
    register_rest_route('sbs/v1', '/checkout/validate', [
        'methods'  => 'POST',
        'callback' => 'sbs_checkout_validate',
        'permission_callback' => '__return_true',
    ]);
});

function sbs_checkout_validate(\WP_REST_Request $req)
{
    $body  = $req->get_json_params();
    $cart  = is_array($body['items'] ?? null) ? $body['items'] : [];
    if (!$cart) return new \WP_REST_Response(['error' => 'items missing'], 400);

    $expanded = sbs_expand_cart_items($cart);
    if (!$expanded) return new \WP_REST_Response(['error' => 'no valid items'], 400);
    $repo = \Classes\InventoryRepo::instance();

    $result = [];
    $okAll  = true;
    foreach ($expanded as $it) {
        $pid = (int)$it['id'];
        $need = (int)$it['qty'];
        $stock = $repo->getStock($pid);
        $ok = $stock >= $need;
        if (!$ok) $okAll = false;
        $result[] = ['id' => $pid, 'requested' => $need, 'stock' => $stock, 'ok' => $ok];
    }

    $status = $okAll ? 200 : 409;
    return new \WP_REST_Response([
        'ok'    => $okAll,
        'items' => $result
    ], $status);
}

add_action('rest_api_init', function () {
    register_rest_route('sbs/v1', '/checkout/finalize', [
        'methods'  => 'POST',
        'callback' => 'sbs_checkout_finalize',
        'permission_callback' => '__return_true', // valuta auth se serve
    ]);
});

function sbs_checkout_finalize(\WP_REST_Request $req)
{
    $body  = $req->get_json_params();
    $cart  = is_array($body['items'] ?? null) ? $body['items'] : [];
    $key   = trim((string)($body['idempotency_key'] ?? ''));
    if (!$cart || $key === '') return new \WP_REST_Response(['error' => 'missing items/idempotency_key'], 400);

    $expanded = sbs_expand_cart_items($cart);

    // Idempotency (super semplice via options; meglio una tabella dedicata se vuoi log)
    $optKey = 'sbs_idemp_' . substr(sha1($key), 0, 20);
    if (get_option($optKey)) {
        return new \WP_REST_Response(['ok' => true, 'idempotent' => true], 200);
    }

    $repo = \Classes\InventoryRepo::instance();
    $ok   = $repo->decrementManyAtomically($expanded);

    if (!$ok) {
        return new \WP_REST_Response(['ok' => false, 'error' => 'insufficient_stock'], 409);
    }

    // mark done (no autoload)
    update_option($optKey, time(), false);

    return new \WP_REST_Response(['ok' => true], 200);
}

function sbs_can_finalize(\WP_REST_Request $req)
{
    $secret = my_env('SBS_FINALIZE_SECRET') ?: '';
    return hash_equals($secret, (string)$req->get_header('X-SBS-Secret'));
}
register_rest_route('sbs/v1', '/checkout/finalize', [
    'methods' => 'POST',
    'callback' => 'sbs_checkout_finalize',
    'permission_callback' => 'sbs_can_finalize',
]);
