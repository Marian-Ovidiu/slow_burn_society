@extends('layouts.singleProductLayout')

@section('title', ($kit['title'] ?? 'Kit') . ' – Slow Burn Society')

@push('head')
  @php
    $jsonLdImages = [];
    if (!empty($kit['image'])) $jsonLdImages[] = $kit['image'];
    if (!empty($kit['gallery']) && is_array($kit['gallery'])) {
      foreach ($kit['gallery'] as $g) $jsonLdImages[] = is_array($g) ? $g['url'] ?? null : $g;
    }
    $jsonLdImages = array_values(array_filter(array_unique($jsonLdImages)));
  @endphp
  <script type="application/ld+json">
  {!! json_encode([
      '@context' => 'https://schema.org',
      '@type'    => 'Product',
      'name'     => $kit['title'] ?? '',
      'image'    => $jsonLdImages ?: ($kit['image'] ?? ''),
      'description' => strip_tags($kit['descrizione'] ?? ($kit['description_html'] ?? '')),
      'sku'      => $kit['id'] ?? null,
      'offers'   => [
          '@type'         => 'Offer',
          'price'         => number_format($kit['price'] ?? 0, 2, '.', ''),
          'priceCurrency' => 'EUR',
          'availability'  => ($kit['available'] ?? false) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
          'url'           => $kit['permalink'] ?? request()->fullUrl(),
      ],
  ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
  </script>
@endpush

@section('content')
  <style>
    .smoke-wrap { position: relative; isolation: isolate }
    .smoke-layer { position: absolute; inset: -8%; z-index: 0; pointer-events: none; opacity:.55 }
    .smoke-svg { width:100%; height:100%; display:block; filter: blur(8px) }
    .smoke-1 { animation: smokeFloat1 22s ease-in-out infinite; transform-origin: 30% 70% }
    .smoke-2 { animation: smokeFloat2 28s ease-in-out infinite; transform-origin: 60% 40%; opacity:.8 }
    .smoke-3 { animation: smokeFloat3 34s ease-in-out infinite; transform-origin: 50% 90%; opacity:.6 }
    .smoke-wrap:hover .smoke-svg { opacity:.65; filter: blur(9px) }
    @keyframes smokeFloat1 { 0%,100%{transform:translate3d(0,0,0) scale(1)} 50%{transform:translate3d(-1%,-2%,0) scale(1.03)} }
    @keyframes smokeFloat2 { 0%,100%{transform:translate3d(0,0,0) scale(1)} 50%{transform:translate3d(1%,-1%,0) scale(1.02)} }
    @keyframes smokeFloat3 { 0%,100%{transform:translate3d(0,0,0) scale(1)} 50%{transform:translate3d(0,-2%,0) scale(1.04)} }
    @media (prefers-reduced-motion:reduce){
      .smoke-1,.smoke-2,.smoke-3{animation:none!important}
      .smoke-svg{filter:blur(6px); opacity:.35}
    }
  </style>

  <section x-data="productPage({
      initial: @js($kitForJs ?? []),
      maxQty: @js(($kitForJs['available'] ?? false) ? 1 : 0),   // kit: max 1 se disponibile
      items:   @js($kitForJs['products'] ?? [])
  })" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">

    {{-- Colonna sinistra: immagine --}}
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
               :style="`width:${Math.max(0, Math.min(100, Math.round(($store.cart.remainingSeconds() / $store.cart.ttlSeconds) * 100)))}%`"></div>
        </div>
      </div>

      <div class="smoke-wrap aspect-square w-full rounded-2xl border border-white/10 bg-white/5 backdrop-blur grid place-items-center overflow-hidden">
        <div class="smoke-layer"></div>
        <img :src="kit.image" :alt="kit.title"
             class="relative z-[1] max-h-full max-w-full object-contain drop-shadow-[0_12px_30px_rgba(0,0,0,.25)]" />
      </div>
    </div>

    {{-- Colonna destra: dettagli + CTA --}}
    <div class="lg:col-span-6 space-y-4">
      <nav class="text-xs text-white/70" aria-label="breadcrumb">
        <a href="/" class="hover:underline">Home</a>
        <span class="mx-1">/</span>
        <a href="/kit" class="hover:underline">Kit</a>
        <span class="mx-1">/</span>
        <span aria-current="page">{{ $kit['title'] ?? 'Kit' }}</span>
      </nav>

      @if (!empty($kit['pretitolo']))
        <p class="text-xs uppercase tracking-wider text-white/60">{{ $kit['pretitolo'] }}</p>
      @endif

      <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">
        {{ $kit['title'] ?? 'Kit' }}
      </h1>

      <div class="flex items-center gap-3">
        <p class="text-2xl font-semibold text-emerald-300">
          € <span x-text="priceFormatted()"></span>
        </p>
        <span class="text-xs px-2 py-1 rounded"
              :class="available ? 'bg-emerald-400/15 text-emerald-200' : 'bg-red-400/15 text-red-200'"
              x-text="available ? 'Disponibile' : 'Non disponibile'"></span>
      </div>

      <div class="prose prose-sm max-w-none" x-html="descriptionHtml()"></div>

      <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <button type="button"
                @click.stop="addToCart(product.cart)"
                :disabled="!available || cartQty() > 0"
                :aria-label="!available
                    ? `Non disponibile: ${product.title}`
                    : (cartQty() > 0 ? `Già nel carrello: ${product.title}` : `Aggiungi al carrello: ${product.title}`)"
                class="flex-1 py-3 font-semibold rounded-lg transition
                       focus:outline-none focus:ring-2 focus:ring-white/30
                       disabled:cursor-not-allowed disabled:opacity-70
                       grid place-items-center gap-2"
                :class="cartQty() > 0
                    ? 'bg-emerald-600 text-white'
                    : (available ? 'bg-[#45752c] text-white hover:bg-[#386322]' : 'bg-white/15 text-white')">

          <span class="inline-flex items-center gap-2">
            <svg x-show="cartQty() > 0" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-2.59a.75.75 0 0 1 0 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47 3.97-3.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
            <span x-text="cartQty() > 0 ? 'Aggiunto' : (available ? 'Aggiungi al carrello' : 'Non disponibile')"></span>
          </span>
        </button>
      </div>

      {{-- Contenuto del kit --}}
      <section class="mt-8">
        <h2 class="text-sm font-semibold mb-3 text-white">Cosa c’è dentro il kit</h2>

        <ul class="divide-y divide-white/10 rounded-lg border border-white/10 bg-transparent">
          <template x-if="!itemsList?.length">
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
                 class="text-xs font-semibold px-3 py-1.5 rounded border border-white/30 bg-white/10 text-white hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/30">
                Dettagli
              </a>
            </li>
          </template>
        </ul>

        <p class="text-[11px] text-white/60 mt-2">
          La disponibilità del kit dipende dalla disponibilità dei singoli prodotti.
        </p>
      </section>
    </div>
  </section>

  @php $relatedItems = $relatedItems ?? ($related ?? []); @endphp
  @include('components.suggestProducts')
@endsection

@include('components.cartIcon')
@include('components.cartSuggestModal')

@push('before_alpine')
<script>
  // Alpine: modulo unico (kit/prodotto) — ora i KIT usano "available" come unica sorgente
  globalThis.productPage = function({ initial, maxQty, items }) {

    const safeCart = () => {
      try { return (globalThis.Alpine && Alpine.store) ? (Alpine.store('cart') || null) : null; }
      catch { return null; }
    };

    const entity = {
      id: Number(initial?.id || 0),
      title: String(initial?.title || ''),
      price: Number(initial?.price || 0),
      image: String(initial?.image || ''),
      gallery: Array.isArray(initial?.gallery) ? initial.gallery : [],
      description: String(initial?.description || ''),
      available: !!initial?.available,          // <-- qui la verità per i KIT
      cart: initial?.cart || null,              // per i kit: { id:'kit:ID', type:'kit', ... }
    };

    return {
      // dati
      entity,
      get product(){ return this.entity; },
      get kit(){ return this.entity; },

      itemsList: Array.isArray(items) ? items : [],

      qty: 1,
      maxQty: Number(maxQty || 0),

      // --- utilità
      isKit() {
        const cid = String(this.entity?.cart?.id ?? '');
        return (this.entity?.cart?.type === 'kit') || cid.startsWith('kit:');
      },
      get available(){
        // KIT: dal payload; PRODOTTO: da stock
        if (this.isKit()) return !!this.entity?.available;
        return Number(this.maxQty || 0) > 0;
      },

      cartKey(){
        const idFromPayload = this.entity?.cart?.id;
        if (idFromPayload != null && idFromPayload !== '') return String(idFromPayload);
        return this.isKit() ? `kit:${this.entity.id}` : String(this.entity.id);
      },
      cartQty(){
        const cart = safeCart(); if (!cart) return 0;
        const key = this.cartKey();
        if (typeof cart.qtyOf === 'function') return Number(cart.qtyOf(key) || 0);
        const it = cart.items?.find(i => String(i.id) === String(key));
        return Number(it?.qty || 0);
      },

      // per prodotti (stock), per kit non serve ma lo teniamo neutro
      remaining(){
        if (this.isKit()) return (this.available && this.cartQty() === 0) ? 1 : 0;
        const cart = safeCart(); const stock = Number(this.maxQty || 0);
        if (!cart) return stock;
        if (typeof cart.remainingFor === 'function') {
          const r = Number(cart.remainingFor(this.cartKey(), stock));
          return Number.isFinite(r) ? Math.max(0, r) : Math.max(0, stock - this.cartQty());
        }
        return Math.max(0, stock - this.cartQty());
      },

      // CTA
      canAdd(){
        if (this.isKit()) return this.available && this.cartQty() === 0; // max 1
        return this.available && this.remaining() > 0;
      },
      disableCta(){ return !this.canAdd(); },

      // testo/format
      descriptionHtml(){ return this.entity.description; },
      priceFormatted(){ return Number(this.entity.price || 0).toFixed(2).replace('.', ','); },
      fmtPrice(n){ return Number(n||0).toFixed(2).replace('.', ','); },

      // azione
      addToCart(payload){
        const cart = safeCart(); if (!cart || typeof cart.add !== 'function') return;
        if (!this.canAdd()) return;

        if (this.isKit()) {
          // una sola unità
          cart.add(payload);
          return;
        }

        // prodotti: quantità
        const want = Math.max(1, Number(this.qty || 1));
        const canAdd = Math.min(want, this.remaining());
        if (this.cartQty() === 0) cart.add(payload);
        const target = Math.min(this.cartQty() + canAdd, Number(this.maxQty || 0));
        if (typeof cart.setQty === 'function') cart.setQty(this.cartKey(), target);
        else {
          const extra = Math.max(0, target - Math.max(this.cartQty(), 1));
          for (let i = 0; i < extra; i++) cart.add(payload);
        }
      }
    };
  };

  // Helpers usati dai correlati (lasciati per compatibilità con il componente)
  window.cartQtyOf = (id) => {
    try {
      const cart = Alpine?.store?.('cart'); if (!cart) return 0;
      if (typeof cart.qtyOf === 'function') return Number(cart.qtyOf(id) || 0);
      return Number((cart.items?.find(i => String(i.id) === String(id))?.qty) || 0);
    } catch { return 0; }
  };
  window.isProductMaxed = (id, max) => {
    const m = Number(max ?? 0); if (m <= 0) return true;
    return window.cartQtyOf(String(id)) >= m;
  };
  window.remainingForUI = (id, max) => {
    try {
      const cart = Alpine?.store?.('cart'); const m = Number(max ?? 0);
      if (!cart) return m;
      if (typeof cart.remainingFor === 'function') {
        const r = Number(cart.remainingFor(String(id), m));
        return Number.isFinite(r) ? r : 0;
      }
      const current = window.cartQtyOf(String(id));
      return Math.max(0, m - current);
    } catch { return Number(max ?? 0); }
  };
  window.addRecommendedToCart = (payload) => {
    try {
      const cart = Alpine?.store?.('cart');
      if (!cart || typeof cart.add !== 'function') return;
      const p = { ...payload };
      if (p.type === 'kit') {
        if (!String(p.id || '').startsWith('kit:')) p.id = `kit:${p.kitId ?? p.id}`;
        if (!('maxQty' in p) || Number(p.maxQty) < 1) p.maxQty = 1; // fail-safe
        p.qty = 1;
      } else {
        p.type = 'product';
        p.id = String(p.id ?? p.productId ?? p.product_id ?? 0);
        p.qty = Number(p.qty ?? 1);
      }
      cart.add(p);
    } catch (e) { console.error('[recommended:add] errore', e); }
  };
</script>
@endpush

@push('after_alpine')
  <script defer src="{{ vite_asset('assets/js/cart.js') }}"></script>
  <script defer src="{{ vite_asset('assets/js/checkout.js') }}"></script>
@endpush
