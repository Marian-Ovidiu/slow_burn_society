<?php
/**
 * Plugin Name: SlowBurn Cart Reservations
 * Description: Prenotazioni stock temporanee (TTL) per CPT custom, via REST API.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

class SlowBurn_Cart_Reservations {
    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'cart_reservations';

        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('rest_api_init', [$this, 'register_routes']);

        // cron per pulire scaduti (ogni 15 min)
        add_action('slowburn_cart_cleanup', [$this, 'cleanup_expired']);
        if (!wp_next_scheduled('slowburn_cart_cleanup')) {
            wp_schedule_event(time() + 60, 'quarterhourly', 'slowburn_cart_cleanup');
        }
        add_filter('cron_schedules', function($s) {
            if (!isset($s['quarterhourly'])) {
                $s['quarterhourly'] = ['interval' => 15 * 60, 'display' => 'Every 15 Minutes'];
            }
            return $s;
        });
    }

    public function activate() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            cart_id VARCHAR(64) NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            qty INT UNSIGNED NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY unique_cart_post (cart_id, post_id),
            KEY post_id (post_id),
            KEY expires_at (expires_at),
            PRIMARY KEY (id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /** Helpers **/
    private function wp_stock($post_id) {
        // meta_key "disponibilita" (int). Se usi ACF, è comunque in postmeta.
        $raw = get_post_meta($post_id, 'disponibilita', true);
        return max(0, intval($raw));
    }
    private function active_reserved($post_id) {
        global $wpdb;
        $now = current_time('mysql');
        $sum = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(qty),0) FROM {$this->table} WHERE post_id=%d AND expires_at > %s",
            $post_id, $now
        ));
        return intval($sum);
    }
    private function available($post_id) {
        return max(0, $this->wp_stock($post_id) - $this->active_reserved($post_id));
    }
    public function cleanup_expired() {
        global $wpdb;
        $now = current_time('mysql');
        $wpdb->query($wpdb->prepare("DELETE FROM {$this->table} WHERE expires_at <= %s", $now));
    }

    /** REST **/
    public function register_routes() {
        register_rest_route('slowburn/v1', '/available/(?P<post_id>\d+)', [
            'methods'  => 'GET',
            'permission_callback' => '__return_true',
            'callback' => function($req) {
                $post_id = intval($req['post_id']);
                $this->cleanup_expired();
                return wp_send_json(['available' => $this->available($post_id)]);
            }
        ]);

        register_rest_route('slowburn/v1', '/reserve', [
            'methods'  => 'POST',
            'permission_callback' => '__return_true', // puoi mettere controllo nonce se vuoi
            'callback' => function($req) {
                global $wpdb;
                $this->cleanup_expired();

                $cart_id = substr(sanitize_text_field($req['cart_id'] ?? ''), 0, 64);
                $post_id = intval($req['post_id'] ?? 0);
                $qty     = max(0, intval($req['qty'] ?? 0));
                $ttl_min = max(1, min(120, intval($req['ttl_min'] ?? 30)));

                if (!$cart_id || !$post_id) return new WP_REST_Response(['error'=>'bad_request'], 400);

                // quantità corrente prenotata da questo carrello
                $current = intval($wpdb->get_var($wpdb->prepare(
                    "SELECT qty FROM {$this->table} WHERE cart_id=%s AND post_id=%d",
                    $cart_id, $post_id
                )));
                $delta = $qty - $current;

                if ($delta > 0) {
                    $available = $this->available($post_id);
                    if ($delta > $available) {
                        return new WP_REST_Response(['error'=>'not_enough_stock', 'available'=>$available], 409);
                    }
                }

                $expires_at = gmdate('Y-m-d H:i:s', time() + $ttl_min * 60);
                $now = current_time('mysql');

                // upsert
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$this->table} WHERE cart_id=%s AND post_id=%d",
                    $cart_id, $post_id
                ));
                if ($qty === 0) {
                    if ($exists) {
                        $wpdb->delete($this->table, ['cart_id'=>$cart_id, 'post_id'=>$post_id], ['%s','%d']);
                    }
                } else if ($exists) {
                    $wpdb->update($this->table,
                        ['qty'=>$qty, 'expires_at'=>$expires_at, 'updated_at'=>$now],
                        ['cart_id'=>$cart_id, 'post_id'=>$post_id],
                        ['%d','%s','%s'], ['%s','%d']
                    );
                } else {
                    $wpdb->insert($this->table, [
                        'cart_id'=>$cart_id, 'post_id'=>$post_id, 'qty'=>$qty,
                        'expires_at'=>$expires_at, 'created_at'=>$now, 'updated_at'=>$now
                    ], ['%s','%d','%d','%s','%s','%s']);
                }

                return wp_send_json(['ok'=>true, 'expires_at'=>$expires_at]);
            }
        ]);

        register_rest_route('slowburn/v1', '/release', [
            'methods'=>'POST', 'permission_callback'=>'__return_true',
            'callback'=>function($req){
                global $wpdb;
                $cart_id = substr(sanitize_text_field($req['cart_id'] ?? ''), 0, 64);
                $post_id = intval($req['post_id'] ?? 0);
                if (!$cart_id || !$post_id) return new WP_REST_Response(['error'=>'bad_request'], 400);
                $wpdb->delete($this->table, ['cart_id'=>$cart_id, 'post_id'=>$post_id], ['%s','%d']);
                return wp_send_json(['ok'=>true]);
            }
        ]);

        register_rest_route('slowburn/v1', '/release-all', [
            'methods'=>'POST', 'permission_callback'=>'__return_true',
            'callback'=>function($req){
                global $wpdb;
                $cart_id = substr(sanitize_text_field($req['cart_id'] ?? ''), 0, 64);
                if (!$cart_id) return new WP_REST_Response(['error'=>'bad_request'], 400);
                $wpdb->delete($this->table, ['cart_id'=>$cart_id], ['%s']);
                return wp_send_json(['ok'=>true]);
            }
        ]);

        register_rest_route('slowburn/v1', '/commit', [
            'methods'=>'POST', 'permission_callback'=>function(){ return current_user_can('edit_posts') || is_user_logged_in(); },
            'callback'=>function($req){
                // NB: Commit stock reale → da chiamare solo dopo pagamento (metti tu la tua logica permessi)
                global $wpdb;
                $cart_id = substr(sanitize_text_field($req['cart_id'] ?? ''), 0, 64);
                if (!$cart_id) return new WP_REST_Response(['error'=>'bad_request'], 400);

                $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table} WHERE cart_id=%s", $cart_id));
                foreach ($rows as $r) {
                    $current = intval(get_post_meta($r->post_id, 'disponibilita', true));
                    $new = max(0, $current - intval($r->qty));
                    update_post_meta($r->post_id, 'disponibilita', $new);
                }
                $wpdb->delete($this->table, ['cart_id'=>$cart_id], ['%s']);
                return wp_send_json(['ok'=>true]);
            }
        ]);
    }
}
new SlowBurn_Cart_Reservations();
