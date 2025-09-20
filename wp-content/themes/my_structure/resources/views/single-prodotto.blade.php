@extends('layouts.singleProductLayout')

{{-- SEO / Title override --}}
@section('title', ($product['title'] ?? 'Prodotto') . ' – Slow Burn Society')
@push('head')
    {{-- JSON-LD Product --}}
    <script type="application/ld+json">
  {!! json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $product['title'] ?? '',
      'image' => $product['gallery'] ?? [$product['image'] ?? ''],
      'description' => strip_tags($product['descrizione'] ?? ($product['description_html'] ?? '')),
      'sku' => $product['id'] ?? null,
      'brand' => $product['brand'] ?? null,
      'offers' => [
          '@type' => 'Offer',
          'price' => number_format($product['price'] ?? 0, 2, '.', ''),
          'priceCurrency' => 'EUR',
          'availability' => ($product['available'] ?? false) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
          'url' => $product['permalink'] ?? request()->fullUrl(),
      ]
  ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
  </script>
@endpush

@section('content')
    <style>
        :root {
            /* base scura per far risaltare i neon */
            --bg1: #0A0014;
            /* indaco quasi nero */
            --bg2: #05080F;
            /* blu-notte profondo */

            /* NEON brand */
            --violet: #BF00FF;
            /* Viola Elettrico */
            --cyan: #00FFFF;
            /* Ciano Brillante */
            --magenta: #FF00CC;
            /* Magenta Acido */
            --lime: #CCFF00;
            /* Verde Lime Fluo */
            --orange: #FF8800;
            /* Arancione Vibrante */

            /* glass */
            --glass: rgba(255, 255, 255, .10);
            --glass-border: rgba(255, 255, 255, .22);
        }

        /* sfondo aurora */
        body {
            background:
                radial-gradient(1200px 800px at 12% 8%, color-mix(in oklab, var(--violet) 18%, transparent), transparent 55%),
                radial-gradient(1100px 700px at 88% 16%, color-mix(in oklab, var(--cyan) 14%, transparent), transparent 50%),
                radial-gradient(1000px 900px at 50% 88%, color-mix(in oklab, var(--orange) 12%, transparent), transparent 50%),
                linear-gradient(180deg, var(--bg1), var(--bg2));
        }

        /* header accent multicolor (tutti i 5 colori) */
        .header-accent {
            background: linear-gradient(90deg,
                    var(--violet) 0%,
                    var(--cyan) 20%,
                    var(--magenta) 45%,
                    var(--lime) 70%,
                    var(--orange) 100%);
            height: 2px;
            opacity: .95;
        }

        /* blobs aurora (sostituisci i vecchi) */
        .blob.blob-a {
            background: radial-gradient(closest-side, color-mix(in oklab, var(--violet) 75%, transparent), transparent 70%);
        }

        .blob.blob-b {
            background: radial-gradient(closest-side, color-mix(in oklab, var(--cyan) 70%, transparent), transparent 70%);
        }

        .blob.blob-c {
            background: radial-gradient(closest-side, color-mix(in oklab, var(--orange) 65%, transparent), transparent 70%);
        }

        /* optional: glow per CTA lime */
        .btn-neon-lime {
            background: var(--lime);
            color: #0A0A0A;
            box-shadow: 0 8px 22px color-mix(in oklab, var(--lime) 35%, transparent);
            transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
        }

        .btn-neon-lime:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 28px color-mix(in oklab, var(--orange) 30%, transparent);
            filter: saturate(1.05);
        }

        .btn-neon-outline {
            background: transparent;
            color: #fff;
            border: 1px solid color-mix(in oklab, var(--violet) 60%, transparent);
            box-shadow: 0 0 0 0 color-mix(in oklab, var(--violet) 0%, transparent), inset 0 0 18px color-mix(in oklab, var(--violet) 18%, transparent);
        }

        .btn-neon-outline:hover {
            border-color: var(--magenta);
            box-shadow: 0 0 24px color-mix(in oklab, var(--magenta) 28%, transparent), inset 0 0 22px color-mix(in oklab, var(--magenta) 24%, transparent);
        }

        /* fumo: scie morbide dietro l'immagine */
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

        /* tre scie che “respirano” a velocità diverse */
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

        /* riduci movimento */
        @media (prefers-reduced-motion: reduce) {

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

    <section x-data="productPage({
        initial: @js($productForJs ?? []),
        maxQty: @js($product['disponibilita'] ?? 0)
    })" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">

        {{-- COLONNA SINISTRA: Media --}}
        <div class="lg:col-span-6">
            {{-- Banner TTL carrello --}}
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

            {{-- Immagine principale --}}
            <div
                class="smoke-wrap aspect-square w-full rounded-2xl border border-white/10 bg-white/5 backdrop-blur grid place-items-center overflow-hidden card-aura">
                <!-- SMOKE TRAILS (dietro) -->
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

                <!-- IMMAGINE PRODOTTO (sopra) -->
                <img :src="selectedImage || product.image" :alt="product.title"
                    class="relative z-[1] max-h-full max-w-full object-contain drop-shadow-[0_12px_30px_rgba(0,0,0,.25)]" />
            </div>


            {{-- Thumbs --}}
            <div class="mt-3 flex gap-2 overflow-x-auto scrollbar-hide" aria-label="Galleria immagini prodotto">
                <template x-for="(img,i) in (product.gallery?.length ? product.gallery : [product.image]).slice(0,8)"
                    :key="i">
                    <button type="button"
                        class="rounded border bg-white p-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#45752c]"
                        @click="selectedImage = img" :aria-label="'Seleziona immagine ' + (i + 1)">
                        <img :src="img" class="h-16 w-16 object-cover rounded" alt="">
                    </button>
                </template>
            </div>
        </div>

        {{-- COLONNA DESTRA: Info + CTA --}}
        <div class="lg:col-span-6 space-y-4">
            {{-- Breadcrumb minimal --}}
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

            <div class="flex items-center gap-3">
                <p class="text-2xl font-semibold text-emerald-300">
                    € <span x-text="priceFormatted()"></span>
                </p>
                <span class="text-xs px-2 py-1 rounded"
                    :class="inStock ? 'bg-emerald-400/15 text-emerald-200' : 'bg-red-400/15 text-red-200'"
                    x-text="inStock ? 'Disponibile' : 'Non disponibile'"></span>
            </div>

            {{-- Descrizione breve / html pulito --}}
            <div class="prose prose-sm max-w-none" x-html="descriptionHtml()"></div>

            {{-- Quantità + CTA --}}
            <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="flex items-center rounded-lg border bg-white text-black w-full sm:w-auto">
                    <button type="button" class="px-3 py-2 text-black hover:bg-black/5" @click="decrement()"
                        aria-label="Diminuisci quantità">−</button>

                    <input type="number"
                        class="w-full sm:w-14 text-center py-2 bg-white text-black placeholder:text-black/60 focus:outline-none focus:ring-2 focus:ring-black/20 [color-scheme:light]"
                        x-model.number="qty" min="1" :max="maxQty" :disabled="!inStock"
                        inputmode="numeric">

                    <button type="button" class="px-3 py-2 text-black hover:bg-black/5" @click="increment()"
                        aria-label="Aumenta quantità">+</button>
                </div>
                <button type="button"
                    class="flex-1 rounded-lg bg-[#45752c] text-white py-3 font-semibold hover:bg-[#386322] disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="$store.cart?.remainingFor(product.id, Number(maxQty)) <= 0"
                    @click.stop="addToCart(product.cart)"
                    :aria-label="$store.cart?.remainingFor(product.id, Number(maxQty)) > 0 ?
                        `Aggiungi al carrello: ${product.title} (qty: ${qty})` :
                        `Non disponibile: ${product.title}`">
                    Aggiungi
                </button>
            </div>

            <p class="text-xs mt-1"
                :class="$store.cart?.remainingFor(product.id, Number(maxQty)) > 0 ? 'text-green-600' : 'text-red-600'">
                Disp.: <span x-text="$store.cart?.remainingFor(product.id, Number(maxQty)) ?? 0"></span>
            </p>
            {{-- Sezione dettagli opzionale --}}
            @if (!empty($product['titolo_descrizione']))
                <h2 class="mt-6 text-sm font-semibold text-white">{{ $product['titolo_descrizione'] }}</h2>
            @endif

            @if (!empty($product['descrizione']))
                <div class="prose prose-sm max-w-none">{!! $product['descrizione'] !!}</div>
            @endif
        </div>
    </section>

    {{-- Prodotti correlati --}}
@if (!empty($relatedItems))
  @php
    $hasKit = false; $hasProd = false;
    foreach ($relatedItems as $r) { if(($r['type'] ?? '') === 'kit') $hasKit = true; if(($r['type'] ?? '') === 'product') $hasProd = true; }
    $relatedTitle = $hasKit && $hasProd ? 'Consigliati per te' : ($hasKit ? 'Altri kit che potrebbero piacerti' : 'Prodotti correlati');
  @endphp

  <section class="mt-12" aria-labelledby="related-title">
    <h2 id="related-title" class="text-lg font-bold tracking-tight mb-4">{{ $relatedTitle }}</h2>

    <ul class="grid gap-4 sm:gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4" role="list">
      @foreach ($relatedItems as $r)
        <li role="listitem">
          <article class="bg-white/10 border border-white/15 rounded-lg p-3 h-full flex flex-col backdrop-blur">
            <a href="{{ $r['permalink'] }}" class="block">
              <img src="{{ $r['image'] }}" alt="{{ $r['title'] }}" class="w-full h-40 object-contain rounded mb-2 bg-white">
            </a>
            <h3 class="text-sm font-semibold line-clamp-2">
              <a href="{{ $r['permalink'] }}" class="hover:underline">{{ $r['title'] }}</a>
            </h3>
            <p class="mt-1 text-sm text-gray-100">€ {{ $r['price_formatted'] }}</p>
            <span class="text-[11px] mt-0.5 {{ ($r['available'] ?? false) ? 'text-green-300' : 'text-red-300' }}">
              {{ ($r['available'] ?? false) ? 'Disponibile' : 'Non disponibile' }}
            </span>

            {{-- CTA coerente con type --}}
            @if (($r['type'] ?? '') === 'kit')
              <button type="button"
                class="mt-auto w-full text-xs font-semibold py-2 rounded transition {{ ($r['available'] ?? false) ? 'bg-[#45752c] text-white hover:bg-[#386322]' : 'bg-gray-300 text-gray-600 cursor-not-allowed' }}"
                @click.prevent="$store.cart && $store.cart.add(@js($r['cart']))"
                {{ ($r['available'] ?? false) ? '' : 'disabled' }}>
                Aggiungi
              </button>
            @else
              <div class="mt-auto grid grid-cols-2 gap-2">
                <button type="button"
                  class="text-xs font-semibold py-2 rounded transition bg-[#45752c] text-white hover:bg-[#386322] disabled:opacity-50 disabled:cursor-not-allowed"
                  @click.prevent="$store.cart && $store.cart.add(@js($r['cart']))"
                  {{ ($r['available'] ?? false) ? '' : 'disabled' }}>
                  Aggiungi
                </button>
                <a href="{{ $r['permalink'] }}" class="text-black text-center text-xs font-semibold py-2 rounded border border-gray-300 bg-white hover:bg-gray-50">
                  Dettagli
                </a>
              </div>
            @endif
          </article>
        </li>
      @endforeach
    </ul>
    {{-- @include('components.cartIcon') --}}
  </section>
@endif
@endsection

{{-- === SCRIPT: definizione componente PRIMA di Alpine === --}}
@push('before_alpine')
    <script>
        // Definizione robusta del componente usato in x-data
        globalThis.productPage = function({
            initial,
            maxQty
        }) {
            const safeCart = () => {
                try {
                    return (globalThis.Alpine && Alpine.store) ? (Alpine.store('cart') || null) : null;
                } catch {
                    return null;
                }
            };

            return {
                product: {
                    id: Number(initial?.id || 0),
                    title: String(initial?.title || ''),
                    price: Number(initial?.price || 0),
                    image: String(initial?.image || ''),
                    gallery: Array.isArray(initial?.gallery) ? initial.gallery : [],
                    description: String(initial?.description || ''),
                    cart: initial?.cart || null,
                },

                selectedImage: null,
                qty: 1,
                maxQty: Number(maxQty || 0),

                get inStock() {
                    return Number.isFinite(this.maxQty) && this.maxQty > 0;
                },
                get alreadyInCart() {
                    const items = safeCart()?.items || [];
                    return items.some(i => Number(i.id) === Number(this.product.id));
                },

                descriptionHtml() {
                    return this.product.description;
                },
                priceFormatted() {
                    const n = Number(this.product.price || 0);
                    return n.toFixed(2).replace('.', ',');
                },

                increment() {
                    if (this.inStock) this.qty = this.maxQty ? Math.min(this.qty + 1, this.maxQty) : this.qty + 1;
                },
                decrement() {
                    this.qty = Math.max(1, this.qty - 1);
                },

                // >>> UNICA funzione addToCart, compatibile con lo shop e con qty>1
                addToCart(payload) {
                    const cart = safeCart();
                    if (!cart || typeof cart.add !== 'function') return;

                    const stock = Number(this.maxQty || 0);
                    const rem = (typeof cart.remainingFor === 'function') ?
                        Number(cart.remainingFor(this.product.id, stock) ?? stock) :
                        Math.max(0, stock);

                    if (rem <= 0) return;

                    const want = Math.max(1, Number(this.qty || 1));
                    const canAdd = Math.min(want, rem);

                    const current = (typeof cart.qtyOf === 'function') ?
                        Number(cart.qtyOf(this.product.id) || 0) :
                        Number((cart.items?.find(i => Number(i.id) === Number(this.product.id))?.qty) || 0);

                    if (current === 0) cart.add(payload);

                    const target = Math.min(current + canAdd, stock);

                    if (typeof cart.setQty === 'function') {
                        cart.setQty(this.product.id, target);
                    } else {
                        const extra = Math.max(0, target - Math.max(current, 1));
                        for (let i = 0; i < extra; i++) cart.add(payload);
                    }
                }
            };
        };
    </script>
@endpush


{{-- === SCRIPT: dipendono da Alpine (store, checkout, ecc.) === --}}
@push('after_alpine')
    {{-- Se li gestisci dal controller con addJs(), puoi togliere queste due righe --}}
    <script defer src="{{ vite_asset('assets/js/cart.js') }}"></script>
    <script defer src="{{ vite_asset('assets/js/checkout.js') }}"></script>
@endpush
