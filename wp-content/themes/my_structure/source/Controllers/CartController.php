<?php

namespace Controllers;

use Core\Bases\BaseController;

class CartController extends BaseController
{
    /** Pagina carrello (se usi una blade dedicata) */
    public function index()
    {
        $this->render('components.cartPage', []);
    }

    /** Facoltativo: GET /cart — nel tuo setup il carrello è client-side */
    public function get()
    {
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'note' => 'Carrello gestito lato client (localStorage).']);
    }

    /** Facoltativo: POST /cart/save — se vuoi salvare snapshot lato server */
    public function save()
    {
        header('Content-Type: application/json');
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: [];
        // qui potresti salvare in transients / post_meta / tabella custom
        echo json_encode(['ok' => true]);
    }

    /** POST /cart/event — audit leggero (navigator.sendBeacon) */
    public function event()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false]);
            return;
        }

        $raw = file_get_contents('php://input');
        $p = json_decode($raw, true) ?: [];

        $cartToken = preg_replace('/[^a-f0-9\-]/i', '', $p['cart_token'] ?? '');
        if (!$cartToken) {
            echo json_encode(['ok' => false]);
            return;
        }

        $type    = substr($p['type'] ?? 'unknown', 0, 32);
        $intent  = substr($p['intent_id'] ?? '', 0, 64) ?: null;
        $itemId  = substr($p['item_id'] ?? '', 0, 64) ?: null;
        $qty     = isset($p['qty']) ? (int)$p['qty'] : null;
        $meta    = isset($p['meta']) ? (array)$p['meta'] : [];

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'sbs_cart_events', [
            'cart_token' => $cartToken,
            'intent_id'  => $intent,
            'type'       => $type,
            'item_id'    => $itemId,
            'qty'        => $qty,
            'meta_json'  => $meta ? wp_json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'client_ip'  => $this->clientIp(),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 255),
            'created_at' => current_time('mysql'),
        ], ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']);

        echo json_encode(['ok' => true]);
    }

    private function clientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = explode(',', $_SERVER[$k])[0];
                return trim($ip);
            }
        }
        return '';
    }
}
