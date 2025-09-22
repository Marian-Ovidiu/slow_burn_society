<style>
    @keyframes glowPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(69, 117, 44, .55), 0 0 0 6px rgba(69, 117, 44, .20), 0 0 0 12px rgba(69, 117, 44, .10);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(69, 117, 44, 0), 0 0 0 16px rgba(69, 117, 44, 0), 0 0 0 24px rgba(69, 117, 44, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(69, 117, 44, 0), 0 0 0 0 rgba(69, 117, 44, 0), 0 0 0 0 rgba(69, 117, 44, 0);
        }
    }

    .glow-pulse {
        animation: glowPulse 1.6s ease-out infinite;
    }

    @media (prefers-reduced-motion: reduce) {
        .glow-pulse {
            animation: none !important;
        }

        [data-cart-btn] {
            transition: none !important;
        }
    }

    @media print {
        .cart-floating {
            display: none !important;
        }
    }

    @media (max-width: 768px) {
        .cart-floating {
            right: calc(clamp(12px, 3vw, 50px) * 3) !important;
            bottom: calc((clamp(12px, 3vw, 50px) + env(safe-area-inset-bottom)) * 4) !important;
        }
    }
</style>

<div x-data="{
    threshold: 35,
    crossed: false,
    glowActive: false,
    fmt(val) { try { return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(Number(val || 0)); } catch (e) { return '€' + (Number(val || 0)).toFixed(2).replace('.', ','); } },
    get qty() { return ($store.cart?.items || []).reduce((a, i) => a + Number(i.qty || 0), 0) },
    get total() { return (typeof $store.cart?.total === 'function') ? $store.cart.total() : 0 },
    get progress() { return this.threshold ? Math.min(100, Math.round((this.total / this.threshold) * 100)) : 0 },
    get leftToThreshold() { const left = this.threshold - this.total; return left > 0 ? left : 0 },
    announce(msg) {
        const n = this.$root.querySelector('[data-live]');
        if (n) {
            n.textContent = '';
            requestAnimationFrame(() => n.textContent = msg);
        }
    },
    init() {
        this.$watch(() => this.qty, (nv, ov) => {
            const btn = this.$root.querySelector('[data-cart-btn]');
            if (!btn) return;
            btn.classList.remove('scale-100');
            btn.classList.add('scale-110');
            setTimeout(() => {
                btn.classList.remove('scale-110');
                btn.classList.add('scale-100');
            }, 150);
            this.announce(`Carrello aggiornato: ${nv} articoli`);
        });
        this.$watch(() => this.total, (val, old) => {
            if (old < this.threshold && val >= this.threshold && !this.crossed) {
                this.crossed = true;
                this.glowActive = true;
                if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) navigator.vibrate?.(60);
                setTimeout(() => this.glowActive = false, 2000);
                this.announce('Spedizione gratuita sbloccata');
                window.dispatchEvent(new CustomEvent('cart:perk', { detail: { type: 'free-shipping' } }));
            }
            if (val < this.threshold) this.crossed = false;
        });
    }
}" x-cloak x-show="$store.cart ? $store.cartReady : true"
    class="cart-floating fixed z-[9999] print:hidden"
    style="right:clamp(12px,4vw,50px); bottom:calc(clamp(12px,6vw,50px) + env(safe-area-inset-bottom));"
    @keydown.enter.window.prevent="$dispatch('suggest:open')">

    <span data-live class="sr-only" aria-live="polite"></span>

    <div class="relative w-10 h-10">
        <div class="absolute inset-0 rounded-full pointer-events-none transition-opacity duration-300"
            :class="glowActive ? 'opacity-100 glow-pulse' : 'opacity-0'"></div>

        <!-- APRE IL MODAL -->
        <button type="button" @click.prevent="$dispatch('suggest:open')"
            class="absolute inset-0 block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#386322] rounded-full"
            :aria-label="qty > 0 ? `Apri suggerimenti. Articoli: ${qty}. Totale: ${fmt(total)}` : 'Apri suggerimenti. Carrello vuoto'">

            <div class="absolute -inset-[5px] rounded-full pointer-events-none"
                :style="`background: conic-gradient(#45752c ${progress}%, #e5e7eb ${progress}% 100%);
                                                                          -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 5px), black 0);
                                                                          mask: radial-gradient(farthest-side, transparent calc(100% - 5px), black 0);
                                                                          transition: background 140ms linear;`">
            </div>

            <div data-cart-btn
                class="absolute inset-0 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg transition-transform scale-100 will-change-transform">
                <span class="material-symbols-rounded text-black text-2xl leading-none"
                    aria-hidden="true">shopping_cart</span>
                <span x-show="qty > 0" x-transition
                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-[4px] rounded-full bg-[#45752c] text-white text-[10px] font-bold flex items-center justify-center leading-none">
                    <span x-text="qty"></span>
                </span>
            </div>
        </button>

        <div x-show="qty > 0" x-transition role="status" aria-live="polite"
            class="absolute top-full left-1/2 -translate-x-1/2 mt-1 max-w-[240px] whitespace-nowrap bg-white border border-gray-200 rounded px-2 py-1 shadow text-gray-700 text-[10px]">
            <template x-if="total < threshold">
                <span>Ancora <b x-text="fmt(leftToThreshold)"></b><br> per <b>sped. gratis</b> 🚀</span>
            </template>
            <template x-if="total >= threshold">
                <span>🎉 <b>Spedizione gratis</b> sbloccata!</span>
            </template>
        </div>
    </div>
</div>
