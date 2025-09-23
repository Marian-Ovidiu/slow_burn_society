<?php

namespace Controllers;

use Core\Bases\BaseController;
use Models\Prodotto;
use Models\Kit;

class RelatedController extends BaseController
{
    public function index()
    {
        try {
            // Query string: ?in_cart_ids=1,2,3&limit=3
            $exclude = array_filter(array_map('intval', explode(',', (string) ($_GET['in_cart_ids'] ?? ''))));
            $limit   = max(1, (int) ($_GET['limit'] ?? 3));

            // Raccogli candidati (prodotti + kit), poi filtra con regole “vendibile”
            $items = array_merge(
                $this->collectSellableProducts($exclude),
                $this->collectSellableKits($exclude)
            ); // <-- niente virgola finale

            // rimuovi duplicati su id+type
            $dedup = [];
            $uniq  = [];
            foreach ($items as $it) {
                $key = ($it['type'] ?? 'x') . ':' . ($it['id'] ?? '0');
                if (!isset($dedup[$key])) {
                    $dedup[$key] = true;
                    $uniq[] = $it;
                }
            }

            // mischia un po' (oppure ordina come vuoi)
            if (function_exists('shuffle')) {
                shuffle($uniq);
            }

            // rispetta il limit
            $out = array_slice($uniq, 0, $limit);

            // Output coerente: { items: [...] }
            $payload = ['items' => array_values($out)];

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($payload, JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            // Logga lato PHP per capire eventuali fatal/notices ecc.
            error_log('[RelatedController] ERROR: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'server_error', 'message' => 'Internal error'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // ————————————————————————————————————————————————————————————————————————
    // PRODUCTS: publish + stock > 0
    // ————————————————————————————————————————————————————————————————————————
    protected function collectSellableProducts(array $excludeIds): array
    {
        $out = [];
        $all = Prodotto::all(); // oggetti con id, title, prezzo, immagini, ACF ecc.

        foreach ($all as $p) {
            $pid = (int) ($p->id ?? 0);
            if ($pid <= 0) {
                continue;
            }
            if (in_array($pid, $excludeIds, true)) {
                continue;
            }

            // status WP
            $status = function_exists('get_post_status') ? (get_post_status($pid) ?: '') : '';
            if ($status !== 'publish') {
                continue;
            }

            // disponibilità / stock numerico
            $rawStock = $p->disponibilita ?? ($p->stock ?? 0);
            $stock    = is_numeric($rawStock) ? (int) $rawStock : (int) ($p->stock ?? 0);
            if ($stock <= 0) {
                continue;
            }

            // prezzo numerico
            $price = (float) ($p->prezzo ?? 0);

            // immagine (fallback ordinato)
            $img = '';
            if (!empty($p->immagine_1['url'])) {
                $img = $p->immagine_1['url'];
            } elseif (!empty($p->immagine_2['url'])) {
                $img = $p->immagine_2['url'];
            } elseif (!empty($p->immagine_3['url'])) {
                $img = $p->immagine_3['url'];
            } elseif (!empty($p->immagine_4['url'])) {
                $img = $p->immagine_4['url'];
            }

            // permalink
            $permalink = function_exists('get_permalink') ? (get_permalink($pid) ?: '#') : '#';

            $out[] = [
                'id'        => $pid,
                'type'      => 'product',
                'title'     => (string) ($p->title ?? ''),
                'price'     => $price,
                'image'     => (string) $img,
                'permalink' => (string) $permalink,
            ];
        }

        return $out;
    }

    // ————————————————————————————————————————————————————————————————————————
    // KITS: publish + prodotti non vuoti + OGNI prodotto (post_type 'prodotto') con stock > 0
    // ————————————————————————————————————————————————————————————————————————
    protected function collectSellableKits(array $excludeIds): array
    {
        $out = [];
        $all = Kit::all();

        foreach ($all as $k) {
            $kid = (int) ($k->id ?? 0);
            if ($kid <= 0) {
                continue;
            }
            if (in_array($kid, $excludeIds, true)) {
                continue;
            }

            // status WP
            $status = function_exists('get_post_status') ? (get_post_status($kid) ?: '') : '';
            if ($status !== 'publish') {
                continue;
            }

            // prodotti del kit: devono esistere e NON essere vuoti
            if (empty($k->prodotti) || !is_iterable($k->prodotti)) {
                continue;
            }

            // OGNI prodotto del tipo 'prodotto' deve avere disponibilità > 0
            $allChildrenOk = true;
            $containsIds   = [];

            foreach ($k->prodotti as $wpPost) {
                $pid = (int) ($wpPost->ID ?? 0);
                if ($pid <= 0) {
                    continue;
                }

                // Considera il vincolo solo per il post_type 'prodotto'
                $ptype = function_exists('get_post_type') ? (get_post_type($pid) ?: '') : '';
                if ($ptype !== 'prodotto') {
                    continue;
                }

                $prod = Prodotto::find($pid);
                if (!$prod) {
                    $allChildrenOk = false;
                    break;
                }

                $rawStock = $prod->disponibilita ?? ($prod->stock ?? 0);
                $stock    = is_numeric($rawStock) ? (int) $rawStock : (int) ($prod->stock ?? 0);
                if ($stock <= 0) {
                    $allChildrenOk = false;
                    break;
                }

                $containsIds[] = $pid;
            }

            if (!$allChildrenOk) {
                continue;
            }

            // prezzo numerico (sanitizza "€ 12,50")
            $priceNumeric = (float) str_replace(['€', ' ', ','], ['', '', '.'], (string) ($k->prezzo ?? 0));

            // immagine / permalink
            $img = (string) ($k->immagine_kit['url'] ?? '');
            $permalink = function_exists('get_permalink') ? (get_permalink($kid) ?: '#') : '#';

            $out[] = [
                'id'        => $kid,
                'type'      => 'kit',
                'title'     => (string) ($k->nome ?? ''),
                'price'     => $priceNumeric,
                'image'     => $img,
                'permalink' => (string) $permalink,
                'contains'  => $containsIds, // utile al FE per dedup
            ];
        }

        return $out;
    }
}
