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
        $this->addJs('cart', 'cart.js');
        $this->addJs('checkout', 'checkout.js');

        $order = [];

        // 1) Payment Intent ID dalla query (?pi=... | ?payment_intent=...)
        $piId = $_GET['pi'] ?? $_GET['payment_intent'] ?? null;
        if (!$piId) {
            return $this->render('grazie', ['o' => $order]);
        }

        global $wpdb;

        // 2) Possono esserci più righe (in pratica 1), ma teniamoci larghi
        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}sbs_payment_intents WHERE intent_id = %s", $piId)
        );
        if (!$rows) {
            return $this->render('grazie', ['o' => $order]);
        }

        // ---------- Helpers locali ----------
        $toFloat = function ($val): float {
            if ($val === null || $val === '') return 0.0;
            $s = str_replace(['€', ' '], '', (string)$val);
            if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
                if (substr_count($s, '.') > substr_count($s, ',')) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } else {
                $s = str_replace(',', '.', $s);
            }
            return (float)$s;
        };

        $getName = function ($model): string {
            return $model->title ?? $model->nome ?? $model->pretitolo ?? 'Prodotto';
        };

        $getImage = function (int $postId, $model, bool $isKit): ?string {
            if ($isKit) {
                $img = $model->immagine_kit['url'] ?? null;
                if ($img) return $img;
            } else {
                $img = $model->immagine_1['url'] ?? ($model->immagine_2['url'] ?? null);
                if ($img) return $img;
            }
            $thumb = get_the_post_thumbnail_url($postId, 'medium');
            if ($thumb) return $thumb;
            return $model->featured_image ?? null;
        };
        // -----------------------------------

        $grandSubtotal = 0.0;

        foreach ($rows as $row) {
            $items = json_decode($row->items_json ?? '[]', true) ?: [];

            foreach ($items as $it) {
                $qty = max(1, (int)($it['qty'] ?? 1));
                $postId = (int)($it['id'] ?? ($it['kitId'] ?? 0));
                if ($postId <= 0) continue;

                $postType = get_post_type($postId) ?: '';

                if ($postType === 'kit') {
                    // KIT
                    $kit = \Models\Kit::find($postId);
                    if (!$kit) continue;

                    $price = $toFloat($kit->prezzo ?? $kit->price ?? 0);
                    $lineSubtotal = $price * $qty;
                    $grandSubtotal += $lineSubtotal;

                    $order[] = (object)[
                        'id'             => $row->intent_id,
                        'created_at'     => $row->created_at,
                        'email'          => $row->email,
                        'payment_method' => $row->payment_method ?? 'Carta',
                        'product_id'     => $postId,
                        'name'           => $getName($kit),
                        'qty'            => $qty,
                        'price'          => $price,
                        'subtotal_item'  => $lineSubtotal,
                        'image'          => $getImage($postId, $kit, true),
                        'shipping'       => $row->amount_shipping / 100,
                        'discount'       => $row->amount_discount / 100,
                        'total'          => $row->amount_total / 100,
                    ];
                } else {
                    // PRODOTTO
                    $prod = \Models\Prodotto::find($postId);
                    if (!$prod) continue;

                    $price = $toFloat($prod->prezzo ?? $prod->price ?? 0);
                    $lineSubtotal = $price * $qty;
                    $grandSubtotal += $lineSubtotal;

                    $order[] = (object)[
                        'id'             => $row->intent_id,
                        'created_at'     => $row->created_at,
                        'email'          => $row->email,
                        'payment_method' => $row->payment_method ?? 'Carta',
                        'product_id'     => $postId,
                        'name'           => $getName($prod),
                        'qty'            => $qty,
                        'price'          => $price,
                        'subtotal_item'  => $lineSubtotal,
                        'image'          => $getImage($postId, $prod, false),
                        'shipping'       => $row->amount_shipping / 100,
                        'discount'       => $row->amount_discount / 100,
                        'total'          => $row->amount_total / 100,
                    ];
                }
            }
        }

        // Imposta il subtotale ordine su ogni riga (comodo per la view)
        foreach ($order as $line) {
            $line->subtotal_order = $grandSubtotal;
        }

        return $this->render('grazie', ['o' => $order]);
    }
}
