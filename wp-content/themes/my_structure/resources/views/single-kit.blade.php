@extends('layouts.singleProductLayout')

{{-- SEO / Title --}}
@section('title', ($kit['title'] ?? 'Kit') . ' – Slow Burn Society')

@push('head')
    {{-- JSON-LD del Kit (usa SOLO image) --}}
    <script type="application/ld+json">
  {!! json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $kit['title'] ?? '',
      'image' => $kit['image'] ?? '',
      'description' => strip_tags($kit['descrizione'] ?? ($kit['description_html'] ?? '')),
      'sku' => $kit['id'] ?? null,
      'brand' => $kit['brand'] ?? null,
      'offers' => [
          '@type' => 'Offer',
          'price' => number_format($kit['price'] ?? 0, 2, '.', ''),
          'priceCurrency' => 'EUR',
          'availability' => ($kit['available'] ?? false) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
          'url' => $kit['permalink'] ?? request()->fullUrl(),
      ]
  ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
  </script>
@endpush

@section('content')
    {{-- Effetto “smoke” locale alla pagina --}}
    <style>
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
                transform: translate3d(0, 0, 0) scale(1)
            }

            50% {
                transform: translate3d(-1%, -2%, 0) scale(1.03)
            }
        }

        @keyframes smokeFloat2 {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1)
            }

            50% {
                transform: translate3d(1%, -1%, 0) scale(1.02)
            }
        }

        @keyframes smokeFloat3 {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1)
            }

            50% {
                transform: translate3d(0, -2%, 0) scale(1.04)
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .smoke-1,
            .smoke-2,
            .smoke-3 {
                animation: none !important
            }

            .smoke-svg {
                filter: blur(6px);
                opacity: .35
            }
        }
    </style>

    <section x-data="kitPage({
        initial: @js($kitForJs ?? []),
        maxQty: @js($kit['disponibilita'] ?? 0),
        items: @js($items ?? [])
    })" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">

        {{-- COLONNA SX --}}
        <div class="lg:col-span-6">
            {{-- Banner TTL carrello --}}
            <div x-show="$store.cart && $store.cart.items.length"
                class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3" role="status">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-amber-900">
                        Carrello attivo — scade tra
                        <span class="font-semibold" x-text="$store.cart.remainingFormatted()"></span>
                    </p>
                </div>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded bg-gray-200" aria-hidden="true">
                    <div class="h-full bg-[#45752c] transition-all"
                        :style="`width:${Math.max(0, Math.min(100, Math.round(($store.cart.remainingSeconds() / $store.cart.ttlSeconds) * 100)))}%`">
                    </div>
                </div>
            </div>

            {{-- UNA SOLA IMMAGINE --}}
            <div
                class="smoke-wrap w-full rounded-2xl border border-white/10 bg-white/5 backdrop-blur overflow-hidden grid place-items-center">
                <div class="smoke-layer"><!-- smoke svgs opzionali --></div>
                <div class="relative z-[1] w-full max-w-[520px] md:max-w-[600px] aspect-[2/3] md:aspect-[3/4] p-4">
                    <img :src="kit.image" :alt="kit.title"
                        class="absolute inset-0 h-full w-full object-contain drop-shadow-[0_12px_30px_rgba(0,0,0,.25)]"
                        width="800" height="1200" loading="eager" decoding="async" />
                </div>
            </div>
        </div>

        {{-- COLONNA DX --}}
        <div class="lg:col-span-6 space-y-4">
            <nav class="text-xs text-gray-500" aria-label="breadcrumb">
                <a href="/" class="hover:underline">Home</a>
                <span class="mx-1" aria-hidden="true">/</span>
                <a href="/kit" class="hover:underline">Kit</a>
                <span class="mx-1" aria-hidden="true">/</span>
                <span aria-current="page">{{ $kit['title'] ?? 'Kit' }}</span>
            </nav>

            @if (!empty($kit['pretitolo']))
                <p class="text-xs uppercase tracking-wider text-gray-500">{{ $kit['pretitolo'] }}</p>
            @endif

            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                {{ $kit['title'] ?? 'Kit' }}
            </h1>

            <div class="flex items-center gap-3">
                <p class="text-2xl font-semibold text-green-700">
                    € <span x-text="priceFormatted()"></span>
                </p>
                <span class="text-xs px-2 py-1 rounded"
                    :class="inStock ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    x-text="inStock ? 'Disponibile' : 'Non disponibile'"></span>
            </div>

            <div class="prose prose-sm max-w-none" x-html="descriptionHtml()"></div>

            {{-- Qty + CTA --}}
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
                    :disabled="!inStock" @click.stop="addToCart(kit.cart)"
                    :aria-label="inStock ? `Aggiungi al carrello: ${kit.title} (qty: ${qty})` : `Non disponibile: ${kit.title}`">
                    Aggiungi
                </button>
            </div>

            <p class="text-xs mt-1" :class="inStock ? 'text-green-600' : 'text-red-600'">
                Disp.: <span x-text="maxQty"></span>
            </p>

            {{-- CONTENUTO DEL KIT --}}
            <section class="mt-8">
                <h2 class="text-sm font-semibold mb-3 text-white">Cosa c’è dentro il kit</h2>

                <ul class="divide-y divide-white/10 rounded-lg border border-white/10 bg-transparent">
                    <template x-if="!itemsList.length">
                        <li class="p-4 text-sm text-white/70">Nessun contenuto specificato.</li>
                    </template>

                    <template x-for="p in itemsList" :key="p.id">
                        <li class="p-3 flex items-center gap-3">
                            <img :src="(p.immagine_1 && p.immagine_1.url) ? p.immagine_1.url: (p.image || '')"
                                alt="" class="h-14 w-14 object-contain rounded bg-white border border-white/20">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-white truncate">
                                    <a :href="p.url || '#'" class="hover:underline" x-text="p.title"></a>
                                </h3>
                                <p class="text-xs text-white/70 mt-0.5" x-text="p.short || ''"></p>
                                <div class="mt-1 flex items-center gap-3">
                                    <span class="text-sm text-white/90">€ <span x-text="fmtPrice(p.price)"></span></span>
                                    <span class="text-[11px]"
                                        :class="(Number(p.disponibilita || 0) > 0) ? 'text-green-400' : 'text-red-400'"
                                        x-text="(Number(p.disponibilita||0)>0) ? 'Disponibile' : 'Non disponibile'"></span>
                                </div>
                            </div>
                            <a :href="p.url || '#'"
                                class="text-xs font-semibold px-3 py-1.5 rounded border border-white/30 bg-white/10 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/30">Dettagli</a>
                        </li>
                    </template>
                </ul>

                <p class="text-[11px] text-gray-500 mt-2">La disponibilità del kit dipende dalla disponibilità dei singoli
                    prodotti.</p>
            </section>
        </div>
    </section>

    {{-- KIT correlati --}}
    @if (!empty($relatedKits))
        <section class="mt-8" x-data> {{-- <= importante: abilita Alpine nella sezione --}}
            <h2 id="related-title" class="text-lg font-bold tracking-tight my-4">Prodotti correlati</h2>
            <ul class="grid gap-4 sm:gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4" role="list">
                @foreach ($relatedKits as $r)
                    @php
                        $entity = $r['entity'] ?? ($r['type'] ?? 'kit');
                        $isProduct = $entity === 'product';
                        $rid = (int) ($r['id'] ?? 0);
                        $href = (string) ($r['permalink'] ?? '#');
                        $img = $isProduct ? $r['immagine_1']['url'] ?? ($r['image'] ?? '') : $r['image'] ?? '';
                        $price = (float) ($r['price'] ?? 0);
                        $priceFormatted = $r['price_formatted'] ?? number_format($price, 2, ',', '.');
                        $stock = (int) ($r['disponibilita'] ?? 0);
                        $available = (bool) ($r['available'] ?? $stock > 0);

                        // Payload: se già presente nel dato, usalo; altrimenti costruiscilo ora
                        $payload = $r['cart'] ?? [
                            'id' => $isProduct ? (string) $rid : 'kit:' . $rid,
                            $isProduct ? 'productId' : 'kitId' => $rid,
                            'type' => $isProduct ? 'product' : 'kit',
                            'name' => (string) ($r['title'] ?? ''),
                            'image' => (string) $img,
                            'price' => (float) $price,
                            'qty' => 1,
                            'maxQty' => $stock,
                        ];
                    @endphp

                    <li role="listitem">
                        <article
                            class="rounded-lg border border-white/10 bg-white/10 backdrop-blur p-3 h-full flex flex-col text-white transition hover:bg-white/15">
                            <a href="{{ $href }}" class="block">
                                <img src="{{ $img }}" alt="{{ $r['title'] ?? '' }}"
                                    class="w-full h-40 object-contain rounded mb-2 bg-white" loading="lazy" width="560"
                                    height="320" sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw">
                            </a>

                            <h3 class="text-sm font-semibold line-clamp-2">
                                <a href="{{ $href }}" class="hover:underline">{{ $r['title'] ?? '' }}</a>
                            </h3>

                            <p class="mt-1 text-sm text-white/90">€ {{ $priceFormatted }}</p>

                            <span class="text-[11px] mt-0.5 {{ $available ? 'text-green-300' : 'text-red-300' }}">
                                {{ $available ? 'Disponibile' : 'Non disponibile' }}
                            </span>

                            <button type="button"
                                class="mt-auto w-full text-xs font-semibold py-2 rounded transition
                                       {{ $available ? 'bg-[#45752c] text-white hover:bg-[#386322] focus:outline-none focus:ring-2 focus:ring-white/30' : 'bg-white/15 text-white cursor-not-allowed disabled:opacity-60' }}"
                                @if ($available) :disabled="!$store.cartReady"
                                    @click.prevent="if ($store.cartReady && $store.cart && typeof $store.cart.add==='function') { $store.cart.add(@js($payload)); navigator.vibrate?.(8); }"
                                @else
                                    disabled @endif>
                                Aggiungi
                            </button>
                        </article>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection

@include('components.cartIcon')
@include('components.cartSuggestModal')

@push('before_alpine')
    <script>
        // Componente pagina KIT (SOLO image, niente gallery)
        globalThis.kitPage = function({
            initial,
            maxQty,
            items
        }) {
            const safeCart = () => {
                try {
                    return (globalThis.Alpine && Alpine.store) ? (Alpine.store('cart') || null) : null;
                } catch {
                    return null;
                }
            };
            const toNum = (v) => {
                if (typeof v === 'number') return v;
                if (v == null) return 0;
                return Number(String(v).replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
            };
            const fmt = (n) => {
                const val = Number(n || 0);
                try {
                    return new Intl.NumberFormat('it-IT', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(val);
                } catch {
                    return val.toFixed(2).replace('.', ',');
                }
            };

            return {
                kit: {
                    id: Number(initial?.id || 0),
                    title: String(initial?.title || initial?.name || ''),
                    price: Number(initial?.price || 0),
                    image: String(initial?.image || ''),
                    description: String(initial?.description || ''),
                    cart: (() => {
                        const kid = Number(initial?.id || 0);
                        const base = initial?.cart || {};
                        return Object.assign({
                            id: `kit:${kid}`,
                            kitId: kid,
                            type: 'kit',
                            name: String(initial?.title || ''),
                            image: String(initial?.image || ''),
                            price: Number(initial?.price || 0)
                        }, base);
                    })(),
                },

                qty: 1,
                maxQty: Number(maxQty || 0),

                itemsList: (Array.isArray(items) ? items : []).map(p => ({
                    id: p.id,
                    title: String(p.title || ''),
                    url: String(p.url || ''),
                    immagine_1: (p.immagine_1 && typeof p.immagine_1 === 'object') ? p.immagine_1 : {},
                    image: String(p.image || ''),
                    short: String(p.short || ''),
                    price: toNum(p.price || 0),
                    disponibilita: Number(p.disponibilita || 0)
                })),

                get inStock() {
                    return Number.isFinite(this.maxQty) && this.maxQty > 0;
                },

                descriptionHtml() {
                    return this.kit.description;
                },
                priceFormatted() {
                    return fmt(this.kit.price);
                },
                fmtPrice(n) {
                    return fmt(n);
                },

                increment() {
                    if (this.inStock) this.qty = this.maxQty ? Math.min(this.qty + 1, this.maxQty) : this.qty + 1;
                },
                decrement() {
                    this.qty = Math.max(1, this.qty - 1);
                },

                addToCart(payload) {
                    if (!this.inStock) return;
                    const cart = safeCart();
                    if (!cart || typeof cart.add !== 'function') return;

                    const id = String(payload?.id || this.kit.cart?.id || `kit:${this.kit.id}`);
                    const stock = Number(this.maxQty || 0);
                    const want = Math.max(1, Number(this.qty || 1));
                    const current = (Array.isArray(cart.items) ? cart.items : []).find(i => String(i.id) === id)?.qty ||
                        0;
                    const target = Math.min(stock, Number(current) + want);

                    if (!current) cart.add(payload);
                    if (typeof cart.setQty === 'function') {
                        cart.setQty(id, target);
                    } else {
                        let extra = Math.max(0, target - Math.max(1, Number(current) || 0));
                        while (extra-- > 0) cart.add(payload);
                    }
                }
            }
        };
    </script>
@endpush
