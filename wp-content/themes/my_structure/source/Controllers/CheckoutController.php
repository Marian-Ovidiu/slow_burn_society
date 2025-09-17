<?php

namespace Controllers;

use Core\Bases\BaseController;

class CheckoutController extends BaseController
{
    public function show()
    {
        $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js');

        global $wpdb;
        $table = $wpdb->prefix . 'sbs_inventory';
        $rows  = $wpdb->get_results("SELECT product_id, stock FROM {$table}", ARRAY_A);
        $inventoryMap = [];
        foreach ($rows as $r) {
            $inventoryMap[(int)$r['product_id']] = max(0, (int)$r['stock']);
        }

        $this->render('checkout', [
            'inventoryMap' => $inventoryMap,
        ]);
    }


    // <-- NUOVO: il checkout JS postera' qui l'ordine dopo pagamento OK
    public function storeOrder()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        // Mini validazione difensiva
        if (!is_array($payload) || empty($payload['items']) || !is_array($payload['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Payload non valido']);
            return;
        }

        $_SESSION['last_order'] = $payload;

        // Rispondi e lascia il redirect al client
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }
}
