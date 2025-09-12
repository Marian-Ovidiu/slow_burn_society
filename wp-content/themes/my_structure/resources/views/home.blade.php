@extends('layouts.mainLayout')

@section('content')
    <div x-data="{ modalOpen: false, selected: null, modalOpen2: false, selected2: null }">
        @if ($products)
            @include('components.cardProdottoEvidenza', [
                'subdata' => $subdata,
                'products' => $products,
            ])
        @endif

        @if ($dataHero)
            @include('components.bannerPromozionale', [
                'dataHero' => $dataHero,
            ])
        @endif

        @if ($latest)
            @include('components.kitSection', [
                'latest' => $latest
            ])
        @endif

        <div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-[100vw] max-w-[100vw] overflow-x-clip">
            @include('components.chiSono')
        </div>
        <style>
/* glow verde morbido */
@keyframes glowPulse {
  0%   { box-shadow: 0 0 0 0 rgba(69,117,44,0.55), 0 0 0 6px rgba(69,117,44,0.20), 0 0 0 12px rgba(69,117,44,0.10); }
  70%  { box-shadow: 0 0 0 8px rgba(69,117,44,0.00), 0 0 0 16px rgba(69,117,44,0.00), 0 0 0 24px rgba(69,117,44,0.00); }
  100% { box-shadow: 0 0 0 0 rgba(69,117,44,0.00), 0 0 0 0 rgba(69,117,44,0.00), 0 0 0 0 rgba(69,117,44,0.00); }
}
.glow-pulse { animation: glowPulse 1.6s ease-out infinite; }
</style>

<!-- BOLLA CARRELLO – stile ripristinato + 50px margin right/bottom -->
<div
  x-data="{
    threshold: 49,
    crossed: false,
    glowActive: false,
    get qty(){ return ($store.cart?.items || []).reduce((a,i)=> a + Number(i.qty||0), 0) },
    get total(){ return (typeof $store.cart?.total === 'function') ? $store.cart.total() : 0 },
    get progress(){ return this.threshold ? Math.min(100, Math.round((this.total / this.threshold) * 100)) : 0 },
    get leftToThreshold(){ const left = this.threshold - this.total; return left > 0 ? left.toFixed(2) : '0.00' },
    init(){
      this.$watch(()=>this.qty, () => {
        const btn = this.$root.querySelector('[data-cart-btn]');
        btn?.classList.remove('scale-100'); btn?.classList.add('scale-110');
        setTimeout(()=>{ btn?.classList.remove('scale-110'); btn?.classList.add('scale-100'); }, 150);
      });
      this.$watch(()=>this.total, (val, old) => {
        if (old < this.threshold && val >= this.threshold && !this.crossed) {
          this.crossed = true; this.glowActive = true; navigator.vibrate?.(60);
          setTimeout(()=> this.glowActive = false, 2000);
          window.dispatchEvent(new CustomEvent('cart:perk', { detail: { type: 'free-shipping' } }));
        }
        if (val < this.threshold) this.crossed = false;
      });
    }
  }"
  x-cloak
  x-show="$store.cart ? $store.cartReady : true"
  class="fixed z-[9999]"
  style="right:50px; bottom:calc(50px + env(safe-area-inset-bottom));"
>
  <div class="relative w-10 h-10">
    <!-- Glow layer -->
    <div class="absolute inset-0 rounded-full pointer-events-none transition-opacity duration-300"
         :class="glowActive ? 'opacity-100 glow-pulse' : 'opacity-0'"></div>

    <a href="/checkout" class="absolute inset-0 block" aria-label="Vai al carrello/checkout">
      <!-- Ghiera progressiva (template string con backtick) -->
      <div class="absolute -inset-[5px] rounded-full pointer-events-none"
           :style="`background: conic-gradient(#45752c ${progress}%, #e5e7eb ${progress}% 100%);
                    -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 5px), black 0);
                    mask: radial-gradient(farthest-side, transparent calc(100% - 5px), black 0);
                    transition: background 140ms linear;`">
      </div>

      <!-- Pulsante giallo -->
      <div data-cart-btn
           class="absolute inset-0 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg transition-transform scale-100">
        <span class="material-symbols-rounded text-black text-2xl leading-none">shopping_cart</span>

        <!-- Badge quantità -->
        <span x-show="qty > 0" x-transition
              class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-[4px] rounded-full bg-[#45752c] text-white text-[10px] font-bold flex items-center justify-center leading-none">
          <span x-text="qty"></span>
        </span>
      </div>
    </a>

    <!-- Tooltip -->
    <div x-show="qty > 0" x-transition
         class="absolute top-full left-1/2 -translate-x-1/2 mt-1 max-w-[240px] whitespace-nowrap bg-white border border-gray-200 rounded px-2 py-1 shadow text-gray-700 text-[10px]">
      <template x-if="total < threshold">
        <span>Ancora €<span x-text="leftToThreshold"></span> <br> per <b>sped. gratis</b> 🚀</span>
      </template>
      <template x-if="total >= threshold">
        <span>🎉 <b>Spedizione gratis</b> sbloccata!</span>
      </template>
    </div>
  </div>
</div>
@endsection
@section('scripts')
  <script src="<?php echo vite_asset('assets/js/main.js'); ?>"></script>
@endsection
