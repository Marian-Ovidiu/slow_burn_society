{{-- resources/views/optionPages/ordini.blade.php --}}

@php
    // Helpers SOLO di presentazione
    function sbs_ship_label(?string $code): string
    {
        return match ($code) {
            'to_ship' => 'Da spedire',
            'shipping' => 'In spedizione',
            'shipped' => 'Spedito',
            'canceled' => 'Annullato',
            default => '—',
        };
    }
    function sbs_ship_badge_class(?string $code): string
    {
        return match ($code) {
            'to_ship' => 'bg-amber-100 text-amber-800',
            'shipping' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-green-100 text-green-800',
            'canceled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    function sbs_payment_label(?string $status): string
    {
        return match ((string) $status) {
            'paid', 'succeeded' => 'Pagato',
            'failed', 'payment_failed' => 'Fallito',
            'processing' => 'In lavorazione',
            default => 'In attesa',
        };
    }
    function sbs_payment_badge_class(?string $status): string
    {
        return match ((string) $status) {
            'paid', 'succeeded' => 'bg-green-100 text-green-800',
            'failed', 'payment_failed' => 'bg-red-100 text-red-800',
            'processing' => 'bg-slate-100 text-slate-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
@endphp

<style>
    @media (max-width: 1024px) {

        .col-product-id,
        .col-email {
            display: none;
        }
    }

    @media (max-width: 768px) {

        .col-qty,
        .col-date {
            display: none;
        }

        .wp-list-table .column-title {
            max-width: 220px;
        }
    }

    .bg-amber-100 {
        background: #FEF3C7;
    }

    .text-amber-800 {
        color: #92400E;
    }

    .bg-blue-100 {
        background: #DBEAFE;
    }

    .text-blue-800 {
        color: #1E3A8A;
    }

    .bg-green-100 {
        background: #D1FAE5;
    }

    .text-green-800 {
        color: #065F46;
    }

    .bg-red-100 {
        background: #FEE2E2;
    }

    .text-red-800 {
        color: #991B1B;
    }

    .bg-slate-100 {
        background: #F1F5F9;
    }

    .text-slate-800 {
        color: #1F2937;
    }

    .bg-gray-100 {
        background: #F5F5F5;
    }

    .text-gray-800 {
        color: #1F2937;
    }

    .col-img {
        width: 72px;
        text-align: center;
    }

    .order-thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
    }
</style>

<div class="wrap">
    <h1 class="wp-heading-inline">Ordini</h1>

    <form method="get" class="mt-4 mb-2" style="margin-top: 1rem; margin-bottom: .5rem;">
        <input type="hidden" name="page" value="{{ esc_attr($_GET['page'] ?? 'opzioni-ordini') }}">
        <p class="search-box">
            <label class="screen-reader-text" for="order-search-input">Cerca ordini:</label>
            <input type="search" id="order-search-input" name="s" value="{{ esc_attr($search ?? '') }}" />
            <input type="submit" id="search-submit" class="button" value="Cerca">
        </p>
    </form>

    <div class="card" style="padding: 12px;">
        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <thead>
                <tr>
                    <th style="width:100px;">ID Ordine</th>
                    <th class="col-product-id" style="width:90px; text-align:center;">Prodotto ID</th>
                    <th class="col-img">Immagine</th>
                    <th style="width:150px; text-align:center;">Prodotto</th>
                    <th class="col-qty" style="width:100px; text-align:center;">Quantità</th>
                    <th style="width:100px;">Pagamento</th>
                    <th style="width:170px;">Spedizione</th>
                    <th class="col-date" style="width:100px;">Data</th>
                    <th class="col-email" style="width:100px;">Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    @php
                        $prod = $r['product'];
                        $thumb = $prod['thumb'];
                        $payLabel = sbs_payment_label($r['pay_status']);
                        $payBadge = sbs_payment_badge_class($r['pay_status']);
                        $shipLabel = sbs_ship_label($r['ship_status']);
                        $shipBadge = sbs_ship_badge_class($r['ship_status']);
                        $actionUrl = $actionUrl ?? admin_url('admin-post.php');
                    @endphp
                    <tr>
                        <td><code>{{ esc_html($r['intent_id']) }}</code></td>

                        <td class="col-product-id" style="text-align:center;">
                            @if ($r['product_id'])
                                <a href="{{ esc_url($prod['editUrl']) }}">{{ (int) $r['product_id'] }}</a>
                            @else
                                —
                            @endif
                        </td>

                        <td class="col-img">
                            @if ($thumb)
                                <img src="{{ esc_url($thumb) }}" alt="" class="order-thumb">
                            @else
                                <div class="order-thumb"></div>
                            @endif
                        </td>

                        <td>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <div>
                                    <div style="font-weight:600;" class="column-title">
                                        @if ($prod['editUrl'])
                                            <a href="{{ esc_url($prod['editUrl']) }}">{{ esc_html($prod['title']) }}</a>
                                        @else
                                            {{ esc_html($prod['title']) }}
                                        @endif
                                    </div>
                                    @if ($prod['isKit'])
                                        <small
                                            style="background:#eef;border:1px solid #dde;padding:2px 6px;border-radius:3px;">KIT</small>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="col-qty" style="text-align:center;">{{ $r['qty'] ?? '—' }}</td>

                        <td>
                            <span class="{{ $payBadge }}"
                                style="display:inline-block;padding:.2rem .5rem;border-radius:999px;font-size:12px;">
                                {{ esc_html($payLabel) }}
                            </span>
                        </td>

                        <td>
                            <form method="post" action="{{ esc_url($actionUrl) }}"
                                style="display:flex; gap:8px; align-items:center; flex-wrap: wrap;">
                                @php wp_nonce_field('sbs_update_shipping'); @endphp
                                <input type="hidden" name="action" value="sbs_update_shipping">
                                <input type="hidden" name="intent_id" value="{{ esc_attr($r['intent_id']) }}">

                                <select name="shipping_status">
                                    @php $opts = ['to_ship'=>'Da spedire','shipping'=>'In spedizione','shipped'=>'Spedito','canceled'=>'Annullato']; @endphp
                                    @foreach ($opts as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ ($r['ship_status'] ?? '') === $val ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="button button-small">Salva</button>

                                @if ($shipLabel !== '—')
                                    <span class="{{ $shipBadge }}"
                                        style="display:inline-block;padding:.2rem .5rem;border-radius:999px;font-size:12px;">
                                        {{ esc_html($shipLabel) }}
                                    </span>
                                @endif
                            </form>
                        </td>

                        <td class="col-date">{{ esc_html(mysql2date('d/m/Y H:i', $r['created_at'])) }}</td>
                        <td class="col-email">
                            @if (!empty($r['email']))
                                <a href="mailto:{{ esc_attr($r['email']) }}">{{ esc_html($r['email']) }}</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Nessun ordine trovato.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
