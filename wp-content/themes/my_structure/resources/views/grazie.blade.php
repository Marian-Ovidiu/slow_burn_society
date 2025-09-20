@extends('layouts.mainLayout')
@section('trippy', true)
@section('content')
    <section x-data x-init="(() => {
        try {
            const k = 'thankyou_cleared';
            if (!sessionStorage.getItem(k)) {
                if ($store?.cart) $store.cart.clear();
                sessionStorage.setItem(k, '1');
                window.toast?.('Pagamento completato ✅', 'success');
            }
        } catch (e) {}
    })()" class="mx-auto w-full max-w-6xl px-4 py-6 md:py-10">

        {{-- Stepper --}}
        <nav class="mb-6 md:mb-8">
            <ol class="flex items-center gap-2 text-xs md:text-sm">
                <li class="flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-[#45752c] text-white">1</span>
                    <span class="font-semibold">Carrello</span>
                </li>
                <span class="h-px w-6 bg-gray-300 md:w-12"></span>
                <li class="flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-[#45752c] text-white">2</span>
                    <span class="font-semibold">Checkout</span>
                </li>
                <span class="h-px w-6 bg-gray-300 md:w-12"></span>
                <li class="flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-black text-white">3</span>
                    <span class="font-semibold">Conferma</span>
                </li>
            </ol>
        </nav>

        @php
            // $o è array di prodotti (ogni prodotto porta anche info ordine)
            $hasItems = isset($o) && is_array($o) && count($o) > 0;
            $first = $hasItems ? $o[0] : null;

            $when = null;
            if ($first && !empty($first->created_at)) {
                $ts = is_numeric($first->created_at) ? (int) $first->created_at : @strtotime((string) $first->created_at);
                $when = $ts ? date('d/m/Y H:i', $ts) : (string) $first->created_at;
            }

            $thanksTitle = isset($fields->title) ? $fields->title : 'Grazie per l’ordine!';
            $thanksSubtitle = isset($fields->subtitle)
                ? $fields->subtitle
                : 'Ti abbiamo inviato una mail di conferma con tutti i dettagli.';
        @endphp


        <div class="grid grid-cols-1 gap-6 md:grid-cols-12 md:gap-8">
            {{-- SX: conferma + info --}}
            <div class="md:col-span-7 space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
                    <div class="text-center">
                        <div
                            class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                            ✓</div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ $thanksTitle }}</h1>
                        <p class="mt-2 text-sm text-gray-600">{{ $thanksSubtitle }}</p>
                    </div>

                    @if ($first)
                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @if (!empty($first->id))
                                <div>
                                    <p class="text-sm text-gray-500">Ordine</p>
                                    <p class="font-semibold text-black">#{{ $first->id }}</p>
                                </div>
                            @endif

                            @if ($when)
                                <div>
                                    <p class="text-sm text-gray-500">Data</p>
                                    <p class="font-semibold text-black">{{ $when }}</p>
                                </div>
                            @endif

                            @if (!empty($first->email))
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="text-sm font-medium text-black">{{ $first->email }}</p>
                                </div>
                            @endif

                            @if (!empty($first->payment_method))
                                <div>
                                    <p class="text-sm text-gray-500">Metodo di pagamento</p>
                                    <p class="text-sm font-medium text-black">{{ $first->payment_method }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
                    <h2 class="mb-2 text-lg font-bold tracking-tight text-black">Serve aiuto?</h2>
                    <p class="text-sm text-black">
                        Scrivici a <a href="mailto:info@slowburnsociety.it" class="underline">info@slowburnsociety.it</a>.
                    </p>
                </div>
            </div>

            {{-- DX: riepilogo --}}
            <aside class="md:col-span-5">
                <div class="sticky top-4 space-y-4">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-lg font-bold tracking-tight">Riepilogo ordine</h2>
                            <a href="/" class="text-xs font-medium text-gray-500 underline">Torna allo shop</a>
                        </div>

                        @if (!$hasItems)
                            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                                Non troviamo i dettagli dei prodotti. Se hai dubbi, contattaci pure.
                            </div>
                        @else
                            <ul class="divide-y divide-gray-200">
                                @foreach ($o as $item)
                                    @php
                                        $name = $item->name ?? 'Prodotto';
                                        $qty = (int) ($item->qty ?? 1);
                                        $price = (float) ($item->price ?? 0);
                                        $sub = (float) ($item->subtotal ?? $qty * $price);
                                        $img = $item->image ?? null;
                                    @endphp
                                    <li class="py-3">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ $img ?: 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2264%22 height=%2264%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f3f4f6%22/></svg>' }}"
                                                alt="{{ $name }}"
                                                class="h-16 w-16 flex-shrink-0 rounded-lg object-cover ring-1 ring-gray-200"
                                                loading="lazy">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-2">
                                                    <p class="truncate text-sm font-medium text-gray-900">
                                                        {{ $name }}</p>
                                                    <p class="text-sm font-semibold text-gray-900">€
                                                        {{ number_format($item->subtotal_item, 2, ',', '') }}
                                                    </p>
                                                </div>
                                                <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                                                    <span>Qtà: <strong>{{ $qty }}</strong></span>
                                                    <span>Prezzo: € {{ number_format($price, 2, ',', '') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex justify-between text-gray-600">
                                    @if (isset($first->subtotal_order))
                                        <div class="flex justify-between text-gray-600">
                                            <span>Subtotale € 
                                                {{ number_format((float) $first->subtotal_order, 2, ',', '') }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if (isset($first->shipping))
                                    <div class="flex justify-between text-gray-600">
                                        <span>Spedizione</span>
                                        @if ((float) $first->shipping == 0)
                                            <span class="text-emerald-700 font-semibold">Spedizione gratuita</span>
                                        @else
                                            <span>€ {{ number_format((float) $first->shipping, 2, ',', '') }}</span>
                                        @endif
                                    </div>
                                @endif

                                @if (!empty($first->discount))
                                    <div class="flex justify-between text-gray-600">
                                        <span>Sconto</span>
                                        <span>-€ {{ number_format((float) $first->discount, 2, ',', '') }}</span>
                                    </div>
                                @endif
                                @if (isset($first->total))
                                    <div class="border-t pt-2"></div>
                                    <div class="flex justify-between text-base font-bold text-gray-900">
                                        <span>Totale</span>
                                        <span>€ {{ number_format((float) $first->total, 2, ',', '') }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </div>

                    <p class="text-[11px] leading-snug text-gray-500">
                        Grazie per aver scelto Slow Burn Society. Trovi termini e privacy sul sito.
                    </p>
                </div>
            </aside>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const pi = new URLSearchParams(location.search).get('pi');
            if (!pi) return;
            try {
                await fetch('/checkout/finalize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        pi
                    })
                });
            } catch (e) {
                // opzionale: console.warn('finalize failed', e);
            }
        });
    </script>
@endsection
