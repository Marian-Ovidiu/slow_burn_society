{{-- resources/views/optionPages/ordini.blade.php --}}

@php
    // ========= Helpers SOLO di presentazione =========
    function sbs_money_eur($cents): string {
        $eur = is_numeric($cents) ? ((int)$cents)/100 : 0;
        return number_format($eur, 2, ',', '.');
    }
    function sbs_json($v) {
        if (empty($v)) return [];
        if (is_array($v)) return $v;
        $arr = json_decode((string)$v, true);
        return is_array($arr) ? $arr : [];
    }
    function sbs_payment_label(?string $status): string {
        return match ((string)$status) {
            'paid', 'succeeded' => 'Pagato',
            'failed', 'payment_failed' => 'Fallito',
            'processing' => 'In lavorazione',
            default => 'In attesa',
        };
    }
    function sbs_payment_badge_class(?string $status): string {
        return match ((string)$status) {
            'paid', 'succeeded' => 'bg-green-100 text-green-800',
            'failed', 'payment_failed' => 'bg-red-100 text-red-800',
            'processing' => 'bg-slate-100 text-slate-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    function sbs_ship_label(?string $code): string {
        return match ($code) {
            'to_ship'   => 'Da spedire',
            'shipping'  => 'In spedizione',
            'shipped'   => 'Spedito',
            'canceled'  => 'Annullato',
            default     => '—',
        };
    }
    function sbs_ship_badge_class(?string $code): string {
        return match ($code) {
            'to_ship'   => 'bg-amber-100 text-amber-800',
            'shipping'  => 'bg-blue-100 text-blue-800',
            'shipped'   => 'bg-green-100 text-green-800',
            'canceled'  => 'bg-red-100 text-red-800',
            default     => 'bg-gray-100 text-gray-800',
        };
    }
    function sbs_short($text, $len=80) {
        $s = (string)($text ?? '');
        return mb_strlen($s) > $len ? (mb_substr($s,0,$len).'…') : $s;
    }

    // ========= Dati attesi =========
    // Passa qui l'array di righe "grezze" dalla tabella wp_sbs_payment_intents (ARRAY_A).
    // Esempio nel tuo PHP:  echo App::blade()->make('optionPages.ordini', ['orders' => $intents])->render();
    /** @var array $orders */
    $orders = $orders ?? ($intents ?? []); // fallback se l'hai chiamato $intents
@endphp

<style>
    .sbs-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(820px,1fr)); gap: 14px; }
    @media (max-width: 960px){ .sbs-grid { grid-template-columns: 1fr; } }
    .sbs-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; }
    .sbs-row { display:flex; gap:14px; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; }
    .sbs-badge { display:inline-block; padding:.25rem .55rem; border-radius:999px; font-size:12px; line-height:1; }
    .bg-amber-100{ background:#FEF3C7 } .text-amber-800{ color:#92400E }
    .bg-blue-100{ background:#DBEAFE } .text-blue-800{ color:#1E3A8A }
    .bg-green-100{ background:#D1FAE5 } .text-green-800{ color:#065F46 }
    .bg-red-100{ background:#FEE2E2 } .text-red-800{ color:#991B1B }
    .bg-slate-100{ background:#F1F5F9 } .text-slate-800{ color:#1F2937 }
    .bg-gray-100{ background:#F5F5F5 } .text-gray-800{ color:#1F2937 }
    .sbs-kv { display:grid; grid-template-columns: 140px 1fr; gap:8px 12px; }
    .sbs-kv .k { color:#6b7280; font-size:12px; }
    .sbs-kv .v { font-size:13px; }
    .sbs-items { width:100%; border-collapse: collapse; }
    .sbs-items th, .sbs-items td { border-bottom:1px solid #eef2f7; padding:8px 6px; font-size:13px; vertical-align: top; }
    .sbs-items th { color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing: .02em; font-size:11px; }
    .sbs-thumb { width:48px; height:48px; border-radius:6px; object-fit:cover; background:#f8fafc; border:1px solid #e5e7eb; }
    .sbs-section-title { font-weight:700; margin-bottom:6px; }
    .sbs-amounts { display:grid; grid-template-columns: repeat(2,minmax(160px,1fr)); gap:8px 14px; }
    @media (max-width: 640px){ .sbs-amounts { grid-template-columns: 1fr; } }
    .sbs-code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; background:#f8fafc; border:1px solid #e5e7eb; border-radius:6px; padding:2px 6px; }
    .sbs-muted { color:#6b7280; }
</style>

<div class="wrap">
    <h1 class="wp-heading-inline">Ordini</h1>
    <p class="description">Riepilogo ordini da <code>wp_sbs_payment_intents</code> con dettaglio articoli, importi e spedizione.</p>

    @if (empty($orders))
        <div class="notice notice-info"><p>Nessun ordine presente.</p></div>
    @else
        <div class="sbs-grid" style="margin-top:12px">
            @foreach ($orders as $o)
                @php
                    // Campi base dall'intent
                    $intentId   = $o['intent_id'] ?? '';
                    $status     = $o['status'] ?? '';
                    $email      = $o['email'] ?? '';
                    $firstName  = $o['first_name'] ?? '';
                    $lastName   = $o['last_name'] ?? '';
                    $createdAt  = $o['created_at'] ?? '';
                    $currency   = $o['currency'] ?? 'EUR';

                    // Importi (cents)
                    $amountSubtotal = (int)($o['amount_subtotal'] ?? 0);
                    $amountShipping = (int)($o['amount_shipping'] ?? 0);
                    $amountDiscount = (int)($o['amount_discount'] ?? 0);
                    $amountTax      = (int)($o['amount_tax'] ?? 0);
                    $amountTotal    = (int)($o['amount_total'] ?? 0);

                    // JSON
                    $items     = sbs_json($o['items_json'] ?? '[]');
                    $shipJson  = sbs_json($o['shipping_json'] ?? '{}');

                    // Spedizione: prova metadati/JSON
                    $shipName = trim(($firstName ? $firstName.' ' : '').$lastName);
                    if (!$shipName) $shipName = $o['shipping_name'] ?? '';
                    $shipLine1   = $shipJson['line1']       ?? '';
                    $shipCity    = $shipJson['city']        ?? '';
                    $shipPostal  = $shipJson['postal_code'] ?? '';
                    $shipState   = $shipJson['state']       ?? '';
                    $shipCountry = $shipJson['country']     ?? 'IT';

                    // Spedizione: stato (colonna opzionale)
                    $shipStatus  = $o['shipping_status'] ?? null;
                    $shipLabel   = sbs_ship_label($shipStatus);
                    $shipBadge   = sbs_ship_badge_class($shipStatus);

                    $payLabel = sbs_payment_label($status);
                    $payBadge = sbs_payment_badge_class($status);

                    // Meta facoltativi
                    $clientIp  = $o['client_ip']  ?? '';
                    $userAgent = $o['user_agent'] ?? '';
                    $referrer  = $o['referrer']   ?? '';
                    $actionUrl = $actionUrl ?? admin_url('admin-post.php');
                @endphp

                <div class="sbs-card">
                    <div class="sbs-row">
                        <div>
                            <div class="sbs-section-title" style="font-size:16px;">
                                Ordine <span class="sbs-code">{{ esc_html($intentId) }}</span>
                            </div>
                            <div class="sbs-muted" style="font-size:12px;">
                                Creato il {{ esc_html(mysql2date('d/m/Y H:i', $createdAt)) }}
                            </div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <span class="sbs-badge {{ $payBadge }}">{{ esc_html($payLabel) }}</span>
                            @if ($shipLabel !== '—')
                                <span class="sbs-badge {{ $shipBadge }}">{{ esc_html($shipLabel) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- ================== ARTICOLI ================== --}}
                    <div style="margin-top:12px;">
                        <div class="sbs-section-title">Articoli</div>
                        @if (empty($items))
                            <div class="sbs-muted">Nessun articolo salvato.</div>
                        @else
                            <table class="sbs-items">
                                <thead>
                                    <tr>
                                        <th>Prodotto</th>
                                        <th style="width:90px;text-align:center;">Qtà</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $it)
                                        @php
                                            $pid   = (int)($it['id'] ?? 0);
                                            $kitId = (int)($it['kitId'] ?? 0);
                                            $qty   = max(1, (int)($it['qty'] ?? 1));

                                            $isKit = $kitId && !$pid;
                                            $postId = $pid ?: $kitId;

                                            $title = $postId ? (get_the_title($postId) ?: ($isKit ? 'Kit' : 'Prodotto')) : 'Articolo';
                                            $thumb = $postId ? (get_the_post_thumbnail_url($postId,'thumbnail') ?: '') : '';
                                            $edit  = $postId ? get_edit_post_link($postId,'') : '';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div style="display:flex; gap:10px; align-items:center;">
                                                    @if ($thumb)
                                                        <img src="{{ esc_url($thumb) }}" alt="" class="sbs-thumb">
                                                    @else
                                                        <div class="sbs-thumb"></div>
                                                    @endif
                                                    <div>
                                                        <div style="font-weight:600;">
                                                            @if ($edit)
                                                                <a href="{{ esc_url($edit) }}">{{ esc_html($title) }}</a>
                                                            @else
                                                                {{ esc_html($title) }}
                                                            @endif
                                                        </div>
                                                        <div class="sbs-muted" style="font-size:12px;">
                                                            {{ $isKit ? 'KIT' : 'PRODOTTO' }} • ID: {{ (int)$postId }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align:center;">{{ (int)$qty }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>

                    {{-- ================== IMPORTI ================== --}}
                    <div style="margin-top:12px;">
                        <div class="sbs-section-title">Importi ({{ esc_html($currency) }})</div>
                        <div class="sbs-amounts">
                            <div><span class="sbs-muted">Subtotale</span><br><strong>€ {{ sbs_money_eur($amountSubtotal) }}</strong></div>
                            <div><span class="sbs-muted">Spedizione</span><br><strong>{{ $amountShipping ? '€ '.sbs_money_eur($amountShipping) : 'Gratis' }}</strong></div>
                            <div><span class="sbs-muted">Sconto</span><br><strong>{{ $amountDiscount ? '-€ '.sbs_money_eur($amountDiscount) : '—' }}</strong></div>
                            <div><span class="sbs-muted">Imposte</span><br><strong>{{ $amountTax ? '€ '.sbs_money_eur($amountTax) : '—' }}</strong></div>
                        </div>
                        <div style="margin-top:8px; font-size:16px;">
                            <span class="sbs-muted">Totale</span>
                            <span style="font-weight:700; margin-left:8px;">€ {{ sbs_money_eur($amountTotal) }}</span>
                        </div>
                    </div>

                    {{-- ================== CLIENTE / SPEDIZIONE ================== --}}
                    <div style="margin-top:12px;">
                        <div class="sbs-section-title">Cliente & Spedizione</div>
                        <div class="sbs-kv">
                            <div class="k">Cliente</div>
                            <div class="v">
                                @if ($firstName || $lastName)
                                    <strong>{{ esc_html(trim($firstName.' '.$lastName)) }}</strong>
                                @else
                                    —
                                @endif
                            </div>

                            <div class="k">Email</div>
                            <div class="v">
                                @if (!empty($email))
                                    <a href="mailto:{{ esc_attr($email) }}">{{ esc_html($email) }}</a>
                                @else
                                    —
                                @endif
                            </div>

                            <div class="k">Indirizzo</div>
                            <div class="v">
                                @if ($shipLine1 || $shipCity || $shipPostal || $shipState || $shipCountry)
                                    {{ esc_html($shipLine1) }}<br>
                                    {{ esc_html(trim($shipPostal.' '.$shipCity)) }} ({{ esc_html($shipState) }}) — {{ esc_html($shipCountry) }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ================== STATO SPEDIZIONE (opzionale) ================== --}}
                    <div style="margin-top:12px;">
                        <div class="sbs-section-title">Stato spedizione</div>
                        <form method="post" action="{{ esc_url($actionUrl) }}" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            @php wp_nonce_field('sbs_update_shipping'); @endphp
                            <input type="hidden" name="action" value="sbs_update_shipping">
                            <input type="hidden" name="intent_id" value="{{ esc_attr($intentId) }}">

                            @php $opts = ['to_ship'=>'Da spedire','shipping'=>'In spedizione','shipped'=>'Spedito','canceled'=>'Annullato']; @endphp
                            <select name="shipping_status">
                                @foreach ($opts as $val => $label)
                                    <option value="{{ $val }}" {{ ($shipStatus ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="button button-small">Salva</button>

                            @if ($shipLabel !== '—')
                                <span class="sbs-badge {{ $shipBadge }}">{{ esc_html($shipLabel) }}</span>
                            @endif
                        </form>
                    </div>

                    {{-- ================== META TECNICI ================== --}}
                    <div style="margin-top:12px;">
                        <div class="sbs-section-title">Meta</div>
                        <div class="sbs-kv">
                            <div class="k">IP</div>
                            <div class="v"><span class="sbs-code">{{ esc_html($clientIp ?: '—') }}</span></div>

                            <div class="k">User-Agent</div>
                            <div class="v" title="{{ esc_attr($userAgent) }}">{{ esc_html(sbs_short($userAgent, 120) ?: '—') }}</div>

                            <div class="k">Referrer</div>
                            <div class="v">
                                @if ($referrer)
                                    <a href="{{ esc_url($referrer) }}" target="_blank" rel="noreferrer">{{ esc_html(sbs_short($referrer, 80)) }}</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
