<?php

namespace Classes;

class OrderMailer
{
    /**
     * Invia la ricevuta ordine per un PaymentIntent riuscito.
     * Ritorna true se inviata, false se saltata (es. già inviata o dati mancanti).
     */
    public function sendReceiptForIntent(string $intentId): bool
    {
        global $wpdb;

        // 0) non inviare due volte: controlla eventi
        if ($this->wasAlreadySent($intentId)) {
            return false;
        }

        $table = $wpdb->prefix . 'sbs_payment_intents';
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE intent_id = %s LIMIT 1",
            $intentId
        ), ARRAY_A);

        if (!$row) return false;

        $toEmail = $row['email'] ?? '';
        if (!is_email($toEmail)) return false;

        // Dati principali
        $cartToken  = $row['cart_token'] ?? '';
        $currency   = strtoupper($row['currency'] ?? 'EUR');
        $subtotal   = (int)($row['amount_subtotal'] ?? 0);
        $shipping   = (int)($row['amount_shipping'] ?? 0);
        $discount   = (int)($row['amount_discount'] ?? 0);
        $tax        = (int)($row['amount_tax'] ?? 0);
        $total      = (int)($row['amount_total'] ?? 0);
        $itemsJson  = $row['items_json'] ?? '[]';
        $items      = json_decode($itemsJson, true) ?: [];

        // Espandi gli items in righe "umane" (nome prodotto, qty)
        $expanded = $this->expandItemsForEmail($items);

        // Subject + contenuto
        $orderNo = $this->humanOrderNumber($intentId);
        $subject = sprintf('Conferma ordine #%s', $orderNo);
        $html    = $this->renderHtmlReceipt($orderNo, $toEmail, $expanded, compact('subtotal','shipping','discount','tax','total','currency'));

        // intestazioni HTML + BCC admin opzionale
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $fromName = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $fromEmail = get_option('admin_email');
        $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';

        // (opzionale) BCC amministratore
        $adminBcc = apply_filters('sbs_order_mail_admin_bcc', get_option('admin_email'));
        if ($adminBcc && is_email($adminBcc)) {
            $headers[] = 'Bcc: ' . $adminBcc;
        }

        $sent = wp_mail($toEmail, $subject, $html, $headers);

        // log evento per non ripetere
        if ($sent) {
            $this->logEvent($cartToken, 'receipt_sent', ['intent_id' => $intentId], $intentId);
        }

        return $sent;
    }

    private function wasAlreadySent(string $intentId): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . 'sbs_cart_events';
        $found = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE intent_id = %s AND type = 'receipt_sent' LIMIT 1",
            $intentId
        ));
        return ((int)$found) > 0;
    }

    /**
     * Trasforma items normalizzati [{id|null, kitId|null, qty}] in righe per email
     * con nome, qty, (facoltativo: immagine/permalink).
     */
    private function expandItemsForEmail(array $items): array
    {
        $rows = [];

        foreach ($items as $it) {
            $qty = max(1, (int)($it['qty'] ?? 1));

            // Prodotto singolo
            if (!empty($it['id'])) {
                $pid = (int)$it['id'];
                if ($pid > 0) {
                    $rows[] = [
                        'name'  => get_the_title($pid) ?: ('Prodotto #' . $pid),
                        'qty'   => $qty,
                        'url'   => get_permalink($pid),
                        'image' => get_the_post_thumbnail_url($pid, 'thumbnail') ?: null,
                    ];
                }
                continue;
            }

            // KIT: elenco componenti (quantità per kit * qty kit)
            if (!empty($it['kitId'])) {
                $kitId = (int)$it['kitId'];
                $kitName = get_the_title($kitId) ?: ('Kit #' . $kitId);
                $rows[] = [
                    'name'  => $kitName . ' (Kit)',
                    'qty'   => $qty,
                    'url'   => get_permalink($kitId),
                    'image' => get_the_post_thumbnail_url($kitId, 'thumbnail') ?: null,
                ];

                $components = $this->getKitComposition($kitId);
                foreach ($components as $pid => $perKitQty) {
                    $pid = (int)$pid;
                    $perKitQty = max(1, (int)$perKitQty);
                    $rows[] = [
                        'name'  => '&nbsp;&nbsp;↳ ' . (get_the_title($pid) ?: ('Prodotto #' . $pid)),
                        'qty'   => $perKitQty * $qty,
                        'url'   => get_permalink($pid),
                        'image' => null,
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * Stessa logica usata nel WebhookController per espandere kit.
     */
    private function getKitComposition(int $kitId): array
    {
        // 1) JSON
        $json = get_post_meta($kitId, 'kit_items_json', true);
        if (!empty($json)) {
            $arr = json_decode($json, true);
            if (is_array($arr)) {
                $out = [];
                foreach ($arr as $row) {
                    $pid = (int)($row['id'] ?? 0);
                    $q   = max(1, (int)($row['qty'] ?? 1));
                    if ($pid > 0) $out[$pid] = ($out[$pid] ?? 0) + $q;
                }
                if ($out) return $out;
            }
        }
        // 2) ACF repeater
        $acfRows = get_post_meta($kitId, 'kit_items', true);
        if (is_array($acfRows) && !empty($acfRows)) {
            $out = [];
            foreach ($acfRows as $row) {
                $pid = (int)($row['product'] ?? $row['id'] ?? 0);
                $q   = max(1, (int)($row['qty'] ?? 1));
                if ($pid > 0) $out[$pid] = ($out[$pid] ?? 0) + $q;
            }
            if ($out) return $out;
        }
        // 3) ACF lista semplice
        if (function_exists('get_field')) {
            $list = (array)get_field('prodotti', $kitId) ?: [];
            $out = [];
            foreach ($list as $p) {
                $pid = is_object($p) ? (int)($p->ID ?? 0) : (int)$p;
                if ($pid > 0) $out[$pid] = ($out[$pid] ?? 0) + 1;
            }
            if ($out) return $out;
        }
        return [];
    }

    /**
     * Rendering HTML semplice. Se preferisci, spostalo in una Blade view.
     */
    private function renderHtmlReceipt(string $orderNo, string $toEmail, array $rows, array $totals): string
    {
        $fmt = function(int $cents) {
            return '€ ' . number_format($cents / 100, 2, ',', '.');
        };

        ob_start(); ?>
        <div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;line-height:1.5;color:#111">
            <h2 style="margin:0 0 12px">Grazie per il tuo ordine <span style="font-weight:700">#<?= esc_html($orderNo) ?></span></h2>
            <p style="margin:0 0 16px">Abbiamo ricevuto il pagamento. Ti invieremo una notifica quando l'ordine sarà spedito.</p>

            <h3 style="margin:16px 0 8px;font-size:16px">Riepilogo</h3>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse">
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td style="padding:6px 0; font-size:14px;">
                            <?= $r['image'] ? '<img src="'.esc_url($r['image']).'" alt="" style="height:38px;width:38px;object-fit:cover;border-radius:6px;margin-right:8px;vertical-align:middle">' : '' ?>
                            <?php if ($r['url']): ?>
                                <a href="<?= esc_url($r['url']) ?>" style="color:#111;text-decoration:none"><?= wp_kses_post($r['name']) ?></a>
                            <?php else: ?>
                                <?= wp_kses_post($r['name']) ?>
                            <?php endif; ?>
                        </td>
                        <td align="right" style="padding:6px 0; font-size:14px; white-space:nowrap">
                            x <?= (int)$r['qty'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <hr style="border:none;border-top:1px solid #eee;margin:12px 0">

            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:14px;color:#444">
                <tr><td>Subtotale</td><td align="right"><?= $fmt($totals['subtotal']) ?></td></tr>
                <tr><td>Spedizione</td><td align="right"><?= $fmt($totals['shipping']) ?></td></tr>
                <?php if (($totals['discount'] ?? 0) > 0): ?>
                    <tr><td>Sconto</td><td align="right">− <?= $fmt($totals['discount']) ?></td></tr>
                <?php endif; ?>
                <?php if (($totals['tax'] ?? 0) > 0): ?>
                    <tr><td>Imposte</td><td align="right"><?= $fmt($totals['tax']) ?></td></tr>
                <?php endif; ?>
                <tr><td colspan="2"><div style="height:8px"></div></td></tr>
                <tr>
                    <td style="font-weight:700">Totale</td>
                    <td align="right" style="font-weight:700"><?= $fmt($totals['total']) ?></td>
                </tr>
            </table>

            <p style="margin:16px 0 0; font-size:12px; color:#666">
                Ordine inviato a: <?= esc_html($toEmail) ?><br>
                Se non sei stato tu a effettuare questo ordine, contatta il supporto.
            </p>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function humanOrderNumber(string $intentId): string
    {
        // es. pi_3Sxxxx → O-3SXXXX-xxxx
        $suffix = substr($intentId, -6);
        return strtoupper('O-' . substr(md5($intentId), 0, 6) . '-' . $suffix);
    }

    private function logEvent(string $cartToken, string $type, array $meta = [], ?string $intentId = null): void
    {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'sbs_cart_events',
            [
                'cart_token' => substr($cartToken ?? '', 0, 36),
                'intent_id'  => $intentId ? substr($intentId, 0, 64) : null,
                'type'       => substr($type, 0, 32),
                'item_id'    => null,
                'qty'        => null,
                'meta_json'  => $meta ? wp_json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                'client_ip'  => $this->clientIp(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );
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
