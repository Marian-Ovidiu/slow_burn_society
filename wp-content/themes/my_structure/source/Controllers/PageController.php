<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\GalleriaFields;
use Models\Kit;
use Models\Prodotto;

class PageController extends BaseController
{
    public function galleria()
    {
        $this->addJs('highlight', 'highlight.js', [], true);
        $this->addVarJs('highlight', 'highlights', GalleriaFields::get()->highlights);
        $this->render('galleria', ['galleria' => GalleriaFields::get()]);
    }

    public function grazie()
    {
        // $this->addJs('cart', 'cart.js');  // se ti serve per x-init che svuota il carrello, ok
        $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js');

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // 1) dalla sessione (se pay() ha chiamato persistOrder)
        $order = $_SESSION['last_order'] ?? null;

        unset($_SESSION['last_order']);

        // 2) fallback opzionale: se c'è ?pi=xxx prova a recuperare gli items salvati all'Intent
        if (!$order && !empty($_GET['pi'])) {
            try {
                $piId = (string)$_GET['pi'];
                if (class_exists(\Classes\IntentRepo::class)) {
                    $items = \Classes\IntentRepo::instance()->load($piId); // es: [{id,qty}] o [{kitId,qty}]
                    if ($items) {
                        $orderItems = [];
                        $subtotal   = 0.0;

                        // helper: prezzo -> float
                        $toFloat = static function ($v): float {
                            $s = (string)$v;
                            $s = str_replace(['€', ' '], '', $s);
                            $s = str_replace(',', '.', $s);
                            return (float)$s;
                        };
                        // helper: prima immagine se presente
                        $imgUrl = static function ($m): ?string {
                            // adatta ai tuoi campi
                            return $m->immagine_1['url'] ?? $m->image_url ?? null;
                        };
                        // helper: nome
                        $titleOf = static function ($m): string {
                            return $m->pretitolo ?? $m->title ?? $m->nome ?? 'Prodotto';
                        };

                        foreach ($items as $line) {
                            $qty = max(1, (int)($line['qty'] ?? 1));

                            if (!empty($line['kitId'])) {
                                $kitId = (int)$line['kitId'];
                                if ($kitId > 0 && ($kit = \Models\Kit::find($kitId))) {
                                    $price = $toFloat($kit->prezzo ?? 0);
                                    $sub   = $price * $qty;
                                    $orderItems[] = [
                                        'name'     => $titleOf($kit),
                                        'qty'      => $qty,
                                        'price'    => $price,
                                        'subtotal' => $sub,
                                        'image'    => $imgUrl($kit),
                                    ];
                                    $subtotal += $sub;
                                }
                            } else {
                                $pid = (int)($line['id'] ?? 0);
                                if ($pid > 0 && ($p = \Models\Prodotto::find($pid))) {
                                    $price = $toFloat($p->prezzo ?? 0);
                                    $sub   = $price * $qty;
                                    $orderItems[] = [
                                        'name'     => $titleOf($p),
                                        'qty'      => $qty,
                                        'price'    => $price,
                                        'subtotal' => $sub,
                                        'image'    => $imgUrl($p),
                                    ];
                                    $subtotal += $sub;
                                }
                            }
                        }

                        // spedizione & totale
                        $shipping = ($subtotal > 35) ? 0.0 : 4.99;
                        $total    = $subtotal + $shipping;

                        $order = (object)[
                            'id'             => null,
                            'number'         => null,
                            'created_at'     => date('Y-m-d H:i:s'),
                            'email'          => null,
                            'payment_method' => 'Carta',
                            'items'          => $orderItems,  // <-- con qty!
                            'subtotal'       => $subtotal,
                            'shipping'       => $shipping,
                            'discount'       => 0.0,
                            'total'          => $total,
                            'invoice_url'    => null,
                            'view_url'       => null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // fallback silenzioso
            }
        }

        return $this->render('grazie', ['o' => $order]);
    }


    protected function buildOrderFromQuery(array $q): object
    {
        $items = [];
        if (!empty($q['items']) && is_array($q['items'])) {
            foreach ($q['items'] as $i) {
                $qty   = (int)($i['qty'] ?? 1);
                $price = (float)($i['price'] ?? 0);
                $items[] = [
                    'name'     => $i['name'] ?? 'Prodotto',
                    'qty'      => $qty,
                    'price'    => $price,
                    'subtotal' => isset($i['subtotal']) ? (float)$i['subtotal'] : ($qty * $price),
                    'image'    => $i['image'] ?? null,
                ];
            }
        }

        $subtotal = isset($q['subtotal']) ? (float)$q['subtotal'] : array_sum(array_map(fn($i) => $i['subtotal'] ?? 0, $items));
        $shipping = (float)($q['shipping'] ?? 0);
        $discount = (float)($q['discount'] ?? 0);
        $total    = isset($q['total']) ? (float)$q['total'] : max(0, $subtotal + $shipping - $discount);

        return (object)[
            'id'         => $q['order_id'] ?? $q['id'] ?? null,
            'number'     => $q['order'] ?? $q['number'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'email'      => $q['email'] ?? null,
            'payment_method' => $q['pm'] ?? null,
            'items'      => $items,
            'subtotal'   => $subtotal,
            'shipping'   => $shipping,
            'discount'   => $discount,
            'total'      => $total,
            'invoice_url' => $q['invoice_url'] ?? null,
            'view_url'   => $q['view_url'] ?? null,
        ];
    }
}
