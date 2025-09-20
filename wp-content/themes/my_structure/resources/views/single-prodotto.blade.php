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
    <section x-data="productPage({
        initial: @js($productForJs ?? []),
        maxQty: @js($product['disponibilita'] ?? 0)
    })" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">

        {{-- COLONNA SINISTRA: Media --}}
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

            {{-- Immagine principale --}}
            <div class="aspect-square w-full rounded-xl border bg-white grid place-items-center overflow-hidden">
                <img :src="selectedImage || product.image" :alt="product.title"
                    class="max-h-full max-w-full object-contain">
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
            <nav class="text-xs text-gray-500" aria-label="breadcrumb">
                <a href="/" class="hover:underline">Home</a>
                <span class="mx-1" aria-hidden="true">/</span>
                <a href="/prodotti" class="hover:underline">Prodotti</a>
                <span class="mx-1" aria-hidden="true">/</span>
                <span aria-current="page">{{ $product['title'] ?? 'Prodotto' }}</span>
            </nav>

            @if (!empty($product['pretitolo']))
                <p class="text-xs uppercase tracking-wider text-gray-500">{{ $product['pretitolo'] }}</p>
            @endif

            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                {{ $product['title'] ?? 'Prodotto' }}
            </h1>

            <div class="flex items-center gap-3">
                <p class="text-2xl font-semibold text-green-700">
                    € <span x-text="priceFormatted()"></span>
                </p>
                <span class="text-xs px-2 py-1 rounded"
                    :class="inStock ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    x-text="inStock ? 'Disponibile' : 'Non disponibile'"></span>
            </div>

            {{-- Descrizione breve / html pulito --}}
            <div class="prose prose-sm max-w-none" x-html="descriptionHtml()"></div>

            {{-- Quantità + CTA --}}
            <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="flex items-center rounded-lg border bg-white w-full sm:w-auto">
                    <button type="button" class="px-3 py-2" @click="decrement()"
                        aria-label="Diminuisci quantità">−</button>
                    <input type="number" class="w-full sm:w-14 text-center py-2 focus:outline-none" x-model.number="qty"
                        min="1" :max="maxQty" :disabled="!inStock" inputmode="numeric">
                    <button type="button" class="px-3 py-2" @click="increment()" aria-label="Aumenta quantità">+</button>
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
                <h2 class="mt-6 text-sm font-semibold text-gray-900">{{ $product['titolo_descrizione'] }}</h2>
            @endif

            @if (!empty($product['descrizione']))
                <div class="prose prose-sm max-w-none">{!! $product['descrizione'] !!}</div>
            @endif
        </div>
    </section>

    {{-- Prodotti correlati --}}
    @if (!empty($related))
        <section class="mt-12">
            <h2 class="text-lg font-bold tracking-tight mb-4">Potrebbe interessarti anche</h2>
            <ul class="grid gap-4 sm:gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4" role="list">
                @foreach ($related as $r)
                    <li role="listitem">
                        <article class="bg-white rounded-lg border p-3 h-full flex flex-col">
                            <a href="{{ $r['permalink'] }}" class="block">
                                <img src="{{ $r['image'] }}" alt="{{ $r['title'] }}"
                                    class="w-full h-40 object-contain rounded mb-2 bg-white">
                            </a>
                            <h3 class="text-sm font-semibold line-clamp-2">
                                <a href="{{ $r['permalink'] }}" class="hover:underline">{{ $r['title'] }}</a>
                            </h3>
                            <p class="mt-1 text-sm text-gray-700">€ {{ $r['price_formatted'] }}</p>
                            <span class="text-[11px] mt-0.5 {{ $r['available'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $r['available'] ? 'Disponibile' : 'Non disponibile' }}
                            </span>
                            <button type="button"
                                class="mt-auto w-full text-xs font-semibold py-2 rounded transition
                             {{ $r['available'] ? 'bg-[#45752c] text-white hover:bg-[#386322]' : 'bg-gray-300 text-gray-600 cursor-not-allowed' }}"
                                @click.prevent="$store.cart && $store.cart.add({
                        id: {{ $r['id'] }},
                        name: @js($r['title']),
                        image: @js($r['image']),
                        price: {{ number_format($r['price'], 2, '.', '') }},
                        maxQty: {{ (int) ($r['disponibilita'] ?? 0) }}
                      })"
                                {{ $r['available'] ? '' : 'disabled' }}>
                                Aggiungi
                            </button>
                        </article>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @include('components.cartIcon')
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
