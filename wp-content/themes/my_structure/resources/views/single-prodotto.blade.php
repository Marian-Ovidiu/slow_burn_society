@extends('layouts.singleProductLayout')

{{-- SEO / Title override --}}
@section('title', ($product['title'] ?? 'Prodotto') . ' – Slow Burn Society')

@push('head')
    @php
        // immagini per JSON-LD (array di stringhe)
        $jsonLdImages = $product['gallery'] ?? [];
        if (!is_array($jsonLdImages) || empty($jsonLdImages)) {
            $jsonLdImages = [$product['image'] ?? ''];
        }
        // flatten in caso di array ACF tipo ['url' => ...]
        $jsonLdImages = array_values(
            array_filter(
                array_map(function ($i) {
                    return is_array($i) ? $i['url'] ?? null : $i;
                }, $jsonLdImages),
            ),
        );
    @endphp
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['title'] ?? '',
        'image' => $jsonLdImages,
        'description' => strip_tags($product['descrizione'] ?? ($product['description_html'] ?? '')),
        'sku' => $product['id'] ?? null,
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($product['price'] ?? 0, 2, '.', ''),
            'priceCurrency' => 'EUR',
            'availability' => ($product['available'] ?? false) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => $product['permalink'] ?? request()->fullUrl(),
        ],
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <style>
        :root {
            --bg1: #0A0014;
            --bg2: #05080F;
            --violet: #BF00FF;
            --cyan: #00FFFF;
            --orange: #FF8800;
        }

        body {
            background:
                radial-gradient(1200px 800px at 12% 8%, color-mix(in oklab, var(--violet) 18%, transparent), transparent 55%),
                radial-gradient(1100px 700px at 88% 16%, color-mix(in oklab, var(--cyan) 14%, transparent), transparent 50%),
                radial-gradient(1000px 900px at 50% 88%, color-mix(in oklab, var(--orange) 12%, transparent), transparent 50%),
                linear-gradient(180deg, var(--bg1), var(--bg2));
        }

        .smoke-wrap {
            position: relative;
            isolation: isolate;
        }

        .smoke-layer {
            position: absolute;
            inset: -8%;
            z-index: 0;
            pointer-events: none;
            opacity: .55;
        }

        .smoke-svg {
            width: 100%;
            height: 100%;
            display: block;
            filter: blur(8px);
        }

        .smoke-1 {
            animation: smokeFloat1 22s ease-in-out infinite;
            transform-origin: 30% 70%;
        }

        .smoke-2 {
            animation: smokeFloat2 28s ease-in-out infinite;
            transform-origin: 60% 40%;
            opacity: .8;
        }

        .smoke-3 {
            animation: smokeFloat3 34s ease-in-out infinite;
            transform-origin: 50% 90%;
            opacity: .6;
        }

        .smoke-wrap:hover .smoke-svg {
            opacity: .65;
            filter: blur(9px);
        }

        @keyframes smokeFloat1 {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(-1%, -2%, 0) scale(1.03);
            }
        }

        @keyframes smokeFloat2 {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(1%, -1%, 0) scale(1.02);
            }
        }

        @keyframes smokeFloat3 {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(0, -2%, 0) scale(1.04);
            }
        }

        @media (prefers-reduced-motion:reduce) {

            .smoke-1,
            .smoke-2,
            .smoke-3 {
                animation: none !important;
            }

            .smoke-svg {
                filter: blur(6px);
                opacity: .35;
            }
        }
    </style>

    <section x-data="(() => {
        const s = productPage({
            initial: @js($productForJs ?? []),
            maxQty: @js($product['disponibilita'] ?? 0)
        });
    
        // helper robusto: accetta stringhe o oggetti {url|src}
        s.imgUrl = (v) => (typeof v === 'string') ? v : (v?.url ?? v?.src ?? '');
    
        // selectedImage vive nello stato Alpine restituito da productPage
        const g = Array.isArray(s.product?.gallery) ? s.product.gallery : [];
        s.selectedImage = s.product?.image || (g.length ? s.imgUrl(g[0]) : '');
    
        return s;
    })()" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">

        {{-- Colonna sinistra: media --}}
        <div class="lg:col-span-6">
            <div x-show="$store.cart && $store.cart.items.length"
                class="mb-4 rounded-xl border border-amber-400/40 bg-amber-400/10 p-3 text-amber-100" role="status">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm">
                        Carrello attivo — scade tra
                        <span class="font-semibold" x-text="$store.cart.remainingFormatted()"></span>
                    </p>
                </div>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded bg-white/20" aria-hidden="true">
                    <div class="h-full bg-[#45752c] transition-all"
                        :style="`width:${Math.max(0, Math.min(100, Math.round(($store.cart.remainingSeconds() / $store.cart.ttlSeconds) * 100)))}%`">
                    </div>
                </div>
            </div>

            <div
                class="smoke-wrap aspect-square w-full rounded-2xl border border-white/10 bg-white/5 backdrop-blur grid place-items-center overflow-hidden">
                <div class="smoke-layer">
                    <svg class="smoke-svg smoke-1" viewBox="0 0 1200 1200" aria-hidden="true">
                        <defs>
                            <linearGradient id="smokeGrad1" x1="0" x2="1" y1="0" y2="1">
                                <stop offset="0%" stop-color="#BF00FF" stop-opacity=".24" />
                                <stop offset="50%" stop-color="#FF00CC" stop-opacity=".18" />
                                <stop offset="100%" stop-color="#00FFFF" stop-opacity=".16" />
                            </linearGradient>
                        </defs>
                        <path
                            d="M200,950 C260,780 420,760 520,610 C640,430 460,330 560,210 C650,110 820,150 900,260
                                                             C980,370 900,520 820,610 C690,750 600,740 520,860 C470,940 360,1020 260,1000 Z"
                            fill="url(#smokeGrad1)" />
                    </svg>
                    <svg class="smoke-svg smoke-2" viewBox="0 0 1200 1200" aria-hidden="true">
                        <defs>
                            <linearGradient id="smokeGrad2" x1="1" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#00FFFF" stop-opacity=".20" />
                                <stop offset="60%" stop-color="#CCFF00" stop-opacity=".18" />
                                <stop offset="100%" stop-color="#FF8800" stop-opacity=".16" />
                            </linearGradient>
                        </defs>
                        <path
                            d="M950,980 C880,900 820,840 780,740 C720,590 850,520 820,420 C790,320 670,300 580,350
                                                             C500,390 520,480 520,560 C520,700 420,760 380,840 C350,900 340,980 420,1020 Z"
                            fill="url(#smokeGrad2)" />
                    </svg>
                    <svg class="smoke-svg smoke-3" viewBox="0 0 1200 1200" aria-hidden="true">
                        <defs>
                            <linearGradient id="smokeGrad3" x1="0" x2="1" y1="1" y2="0">
                                <stop offset="0%" stop-color="#FF8800" stop-opacity=".15" />
                                <stop offset="50%" stop-color="#BF00FF" stop-opacity=".16" />
                                <stop offset="100%" stop-color="#CCFF00" stop-opacity=".18" />
                            </linearGradient>
                        </defs>
                        <path
                            d="M240,240 C340,220 460,260 540,340 C600,400 600,480 560,560 C520,640 440,700 420,800
                                                             C400,900 470,980 560,1000 C650,1020 760,980 800,900 C840,820 780,760 760,680
                                                             C740,600 800,520 820,440 C840,360 800,300 740,260 C660,210 540,200 460,220 Z"
                            fill="url(#smokeGrad3)" />
                    </svg>
                </div>

                <img :src="selectedImage || imgUrl(product.image)" :alt="product.title"
                    class="relative z-[1] max-h-full max-w-full object-contain drop-shadow-[0_12px_30px_rgba(0,0,0,.25)]" />

            </div>

            <div class="mt-3 flex gap-2 overflow-x-auto scrollbar-hide" aria-label="Galleria immagini prodotto">
                <template x-for="(img,i) in (product.gallery?.length ? product.gallery : [product.image]).slice(0,8)"
                    :key="i">
                    <button type="button"
                        class="rounded border bg-white p-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#45752c]"
                        @click="selectedImage = imgUrl(img)" :aria-label="'Seleziona immagine ' + (i + 1)">
                        <img :src="imgUrl(img)" class="h-16 w-16 object-cover rounded" alt="">
                    </button>
                </template>
            </div>
        </div>

        {{-- Colonna destra: info + CTA --}}
        <div class="lg:col-span-6 space-y-4">
            <nav class="text-xs text-white/70" aria-label="breadcrumb">
                <a href="/" class="hover:underline">Home</a>
                <span class="mx-1" aria-hidden="true">/</span>
                <a href="/prodotti" class="hover:underline">Prodotti</a>
                <span class="mx-1" aria-hidden="true">/</span>
                <span aria-current="page">{{ $product['title'] ?? 'Prodotto' }}</span>
            </nav>

            @if (!empty($product['pretitolo']))
                <p class="text-xs uppercase tracking-wider text-white/60">{{ $product['pretitolo'] }}</p>
            @endif

            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
                {{ $product['title'] ?? 'Prodotto' }}
            </h1>

            @if (!empty($product['titolo_descrizione']))
                <h2 class="mt-6 text-sm font-semibold text-white">{{ $product['titolo_descrizione'] }}</h2>
            @endif

            <div class="flex items-center gap-3">
                <p class="text-2xl font-semibold text-emerald-300">
                    € <span x-text="priceFormatted()"></span>
                </p>
                <span class="text-xs px-2 py-1 rounded"
                    :class="inStock ? 'bg-emerald-400/15 text-emerald-200' : 'bg-red-400/15 text-red-200'"
                    x-text="inStock ? 'Disponibile' : 'Non disponibile'"></span>
            </div>

            <div class="prose prose-sm max-w-none" x-html="descriptionHtml()"></div>

            <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button type="button" @click.stop="addToCart(product.cart)"
                    :disabled="!inStock || (($store.cart?.remainingFor(product.id, Number(maxQty)) ?? Number(maxQty)) <= 0)"
                    :aria-label="(() => {
                        const qty = (typeof $store.cart?.qtyOf === 'function') ?
                            Number($store.cart.qtyOf(product.id) || 0) :
                            Number((($store.cart?.items) || []).find(i => Number(i.id) === Number(product.id))
                                ?.qty || 0);
                        if (!inStock) return `Non disponibile: ${product.title}`;
                        if (qty > 0) return `Già nel carrello: ${product.title}`;
                        return `Aggiungi al carrello: ${product.title} (qty: ${qty || 1})`;
                    })()"
                    class="flex-1 py-3 font-semibold rounded-lg transition
         focus:outline-none focus:ring-2 focus:ring-white/30 disabled:cursor-not-allowed disabled:opacity-70
         grid place-items-center gap-2"
                    :class="(() => {
                        const rem = ($store.cart?.remainingFor(product.id, Number(maxQty)) ?? Number(maxQty));
                        const qty = (typeof $store.cart?.qtyOf === 'function') ?
                            Number($store.cart.qtyOf(product.id) || 0) :
                            Number((($store.cart?.items) || []).find(i => Number(i.id) === Number(product.id))
                                ?.qty || 0);
                        if (qty > 0) return 'bg-emerald-600 text-white hover:bg-emerald-600';
                        if (inStock && rem > 0) return 'bg-[#45752c] text-white hover:bg-[#386322]';
                        return 'bg-white/15 text-white';
                    })()">

                    <span class="inline-flex items-center gap-2">
                        <!-- Spunta quando è già nel carrello -->
                        <svg x-show="(typeof $store.cart?.qtyOf === 'function'
                  ? Number($store.cart.qtyOf(product.id) || 0)
                  : Number((($store.cart?.items)||[]).find(i => Number(i.id)===Number(product.id))?.qty || 0)) > 0"
                            xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-2.59a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47 3.97-3.97a.75.75 0 0 1 1.06 0Z"
                                clip-rule="evenodd" />
                        </svg>

                        <span
                            x-text="(() => {
        const rem = ($store.cart?.remainingFor(product.id, Number(maxQty)) ?? Number(maxQty));
        const qty = (typeof $store.cart?.qtyOf === 'function')
          ? Number($store.cart.qtyOf(product.id) || 0)
          : Number((($store.cart?.items)||[]).find(i => Number(i.id)===Number(product.id))?.qty || 0);
        if (!inStock || rem <= 0) return 'Non disponibile';
        return qty > 0 ? 'Aggiunto' : 'Aggiungi al carrello';
      })()">
                        </span>
                    </span>
                </button>

            </div>

            <p class="text-xs mt-1"
                :class="($store.cart?.remainingFor(product.id, Number(maxQty)) ?? Number(maxQty)) > 0 ? 'text-green-600' :
                    'text-red-600'">
                Disp.: <span x-text="$store.cart?.remainingFor(product.id, Number(maxQty)) ?? Number(maxQty)"></span>
                <span class="opacity-70">/ {{ $product['disponibilita'] }}</span>
            </p>
        </div>
    </section>

    @include('components.suggestProducts')
@endsection

@include('components.cartIcon')
@include('components.cartSuggestModal')

@push('after_alpine')
    <script defer src="{{ vite_asset('assets/js/cart.js') }}"></script>
    <script defer src="{{ vite_asset('assets/js/checkout.js') }}"></script>
@endpush
