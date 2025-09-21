<div x-data="cartSuggest" x-init="init()" @suggest:open.window="open()">
    <!-- Overlay (full screen) -->
    <div x-cloak x-show="openModal" x-transition.opacity class="fixed inset-0 z-[10000] bg-black/50 backdrop-blur-sm"
        @click="close()"></div>

    <!-- Contenitore centrato sulla visual viewport -->
    <div x-cloak x-show="openModal" x-transition
        class="fixed inset-0 z-[10001] grid place-items-center p-4 vvh pointer-events-none">

        <!-- WRAPPER modale (box) -->
        <div class="relative w-full max-w-xl rounded-2xl overflow-hidden trippy-bg pointer-events-auto"
            @keydown.escape.window="close()" role="dialog" aria-modal="true" aria-labelledby="cs-title">

            <!-- PANEL a griglia: header / body (scroll) / footer -->
            <div class="trippy-panel text-white grid grid-rows-[auto,1fr,auto] overflow-hidden">

                <!-- Header -->
                <div
                    class="px-5 py-4 border-b border-white/10 flex items-center justify-between bg-transparent/60 backdrop-blur-sm">
                    <h3 id="cs-title" class="text-lg font-semibold">Consigliati per te</h3>
                    <button class="p-2 rounded hover:bg-white/10" @click.stop="close()" aria-label="Chiudi">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <!-- Body: SOLO qui scroll verticale -->
                <div class="modal-body px-4 py-4 space-y-4 pr-2 -mr-2">

                    <!-- Loader (scheletri compatti) -->
                    <div x-show="typeof loading !== 'undefined' && loading"
                        class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <template x-for="n in 6" :key="n">
                            <div class="border border-white/10 rounded-lg overflow-hidden animate-pulse bg-white/5">
                                <div class="aspect-[1/1] bg-white/10"></div>
                                <div class="p-2 space-y-1.5">
                                    <div class="h-2 bg-white/10 rounded"></div>
                                    <div class="h-2 w-2/3 bg-white/10 rounded"></div>
                                    <div class="h-6 bg-white/10 rounded mt-2"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Errore -->
                    <div x-show="typeof loading !== 'undefined' && !loading && typeof error !== 'undefined' && error"
                        class="text-sm text-red-300" x-text="error"></div>
                    <!-- Suggeriti (grid 2/3 col + card compatte) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2"
                        x-show="typeof loading !== 'undefined' && !loading && Array.isArray(items) && items.length">
                        <template x-for="p in items" :key="p.id">
                            <article class="border border-white/10 rounded-lg overflow-hidden flex flex-col bg-white/5">
                                <a :href="p.permalink" class="block aspect-[1/1] bg-black/20">
                                    <img :src="p.image" :alt="p.title"
                                        class="w-full h-full object-cover" loading="lazy">
                                </a>
                                <div class="p-2 flex-1 flex flex-col">
                                    <h4 class="text-[12px] leading-tight font-semibold line-clamp-2" x-text="p.title">
                                    </h4>
                                    <span class="text-[10px] uppercase opacity-70"
                                        x-text="p.type === 'kit' ? 'Kit' : 'Prodotto'"></span>


                                    <button
                                        class="mt-auto inline-flex items-center justify-center rounded-md border border-white/20 px-2 py-1 text-[11px] font-medium hover:bg-white/10 disabled:opacity-60 disabled:cursor-not-allowed"
                                        :disabled="wasAdded(p)" @click.stop="add(p)">
                                        <span
                                            class="material-symbols-rounded mr-1 text-[16px] leading-none">add_shopping_cart</span>
                                        <span x-text="wasAdded(p) ? 'Aggiunto' : 'Aggiungi'"></span>
                                    </button>
                                </div>
                            </article>
                        </template>
                    </div>

                    <!-- Nessun suggerito -->
                    <p x-show="typeof loading !== 'undefined' && !loading && Array.isArray(items) && items.length === 0 && !error"
                        class="text-sm text-white/80">
                        Nessun suggerimento disponibile in questo momento.
                    </p>
                </div>

                <!-- Footer: FISSO in basso alla modale -->
                <div
                    class="px-5 py-4 border-t border-white/10 flex gap-2 justify-end bg-transparent/60 backdrop-blur-sm pb-safe">
                    <button
                        class="inline-flex items-center px-4 py-2 rounded-lg border border-white/20 hover:bg-white/10"
                        @click.stop="close()">Continua lo shopping</button>
                    <a href="/checkout"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-[#45752c] text-white hover:bg-[#3b6426]">
                        Vai al checkout
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .modal-body {
        overflow-y: auto;
        /* abilita scroll verticale */
        overscroll-behavior: contain;
        /* evita scroll della pagina dietro */
        -webkit-overflow-scrolling: touch;
        /* momentum scroll iOS */
        touch-action: pan-y;
        /* consenti gesture verticali */
        max-height: 100%;
        /* non supera l'altezza assegnata dal grid */
    }

    /* il panel è una grid: header (auto) / body (1fr) / footer (auto) */
    .trippy-panel {
        display: grid;
        grid-template-rows: auto 1fr auto;
        overflow: hidden;
        /* nasconde overflow dei layer interni */
    }

    /* ===== Visual viewport helper ===== */
    .vvh {
        height: 100svh;
    }

    /* Safe area padding per footer su iOS */
    .pb-safe {
        padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
    }

    /* Momentum scroll iOS */
    .ios-scroll {
        -webkit-overflow-scrolling: touch;
    }

    /* ===== Trippy background (non blocca i click) ===== */
    .trippy-bg {
        position: relative;
    }

    .trippy-bg::before,
    .trippy-bg::after {
        content: '';
        position: absolute;
        pointer-events: none;
        z-index: 0;
    }

    .trippy-bg::before {
        inset: -40%;
        background: conic-gradient(from 0deg at 50% 50%, #ff00ea, #00eaff, #ffe600, #ff00ea);
        animation: trippySpin 14s linear infinite;
        filter: blur(36px) saturate(1.2);
        transform: scale(1.1);
        will-change: transform, filter;
    }

    .trippy-bg::after {
        inset: 0;
        background: radial-gradient(100% 100% at 50% 50%, rgba(0, 0, 0, .35), rgba(0, 0, 0, .55));
    }

    /* ===== Panel sopra ai layer grafici ===== */
    .trippy-panel {
        position: relative;
        z-index: 1;
        background: rgba(8, 10, 12, 0.78);
        backdrop-filter: blur(10px);
    }

    /* Motion reduce */
    @keyframes trippySpin {
        to {
            transform: rotate(360deg) scale(1.1);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .trippy-bg::before {
            animation: none;
        }
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartSuggest', () => ({
            openModal: false,
            loading: false,
            error: '',
            items: [],
            addedIds: new Set(),

            // ——— UTIL ———
            async waitForCartReady() {
                const ready = () => !!(Alpine.store('cart') && Alpine.store('cartReady'));
                if (ready()) return;
                await new Promise((resolve) => {
                    const start = Date.now();
                    const t = setInterval(() => {
                        if (ready() || Date.now() - start > 5000) {
                            clearInterval(t);
                            resolve();
                        }
                    }, 30);
                });
            },

            fmt(val) {
                try {
                    return new Intl.NumberFormat('it-IT', {
                        style: 'currency',
                        currency: 'EUR'
                    }).format(Number(val || 0));
                } catch (e) {
                    return '€' + (Number(val || 0)).toFixed(2).replace('.', ',');
                }
            },
            inCart(p) {
                const items = (Alpine.store('cart')?.items || []);
                console.log('inCart');
                console.log(items);
                return items.some(i => String(i.id) === String(p.id));
            },
            wasAdded(p) {
                return this.inCart(p) || this.addedIds.has(String(p.id));
            },

            _collectCartIds() {
                const raw = (Alpine.store('cart')?.items || []);
                const ids = [];
                raw.forEach(i => {
                    const sid = String(i.id || '');
                    if (sid.startsWith('kit:')) {
                        const kitNum = parseInt(sid.replace('kit:', ''), 10);
                        if (!isNaN(kitNum)) ids.push(String(kitNum));
                        if (Array.isArray(i.contains)) i.contains.forEach(pid => ids.push(
                            String(pid)));
                    } else {
                        ids.push(sid);
                    }
                });
                return ids.filter(Boolean);
            },

            async fetchRelated() {
                this.loading = true;
                this.error = '';
                this.items = [];
                this.addedIds.clear();
                try {
                    await this.waitForCartReady();

                    const excludeIds = this._collectCartIds()
                        .map(x => parseInt(String(x), 10))
                        .filter(n => Number.isFinite(n) && n > 0);

                    const url = new URL('/related', window.location.origin);
                    if (excludeIds.length) url.searchParams.set('in_cart_ids', excludeIds.join(
                        ','));
                    url.searchParams.set('limit', '3');

                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    this.items = Array.isArray(data) ? data : (data.items || []);
                    console.log('data');
                    console.log(data);

                } catch (e) {
                    console.error(e);
                    this.error = 'Impossibile caricare i suggerimenti.';
                } finally {
                    this.loading = false;
                }
            },

            async add(p) {
                try {
                    await this.waitForCartReady();
                    const cart = Alpine.store('cart');

                    // p.type arriva dal backend: 'product' | 'kit'
                    const idStr = (p.type === 'kit') ? `kit:${p.id}` : String(p.id);

                    cart.add({
                        id: idStr,
                        type: p.type || 'product',
                        name: p.title,
                        image: p.image || null,
                        price: Number(p.price || 0),
                        qty: 1,
                    });

                    this.addedIds.add(String(p.id));
                    if (typeof cart._heartbeat !== 'undefined') cart._heartbeat = Date.now();
                    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches)
                        navigator.vibrate?.(8);
                } catch (e) {
                    console.error(e);
                }
            },

            async open() {
                this.openModal = true;
                await this.waitForCartReady();
                this.fetchRelated();
                if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) navigator
                    .vibrate?.(12);
            },
            close() {
                this.openModal = false;
            },

            init() {
                /* hook futuri */
            }
        }));
    });
</script>
