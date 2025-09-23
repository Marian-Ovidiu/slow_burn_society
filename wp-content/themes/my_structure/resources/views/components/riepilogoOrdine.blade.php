{{-- DX: riepilogo --}}
<aside class="md:col-span-5" x-data="cartWrapper()" x-init="init()">
    <div class="sticky top-4 space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold tracking-tight">Riepilogo ordine</h2>

                <button
                    type="button"
                    class="text-xs font-medium text-gray-600 underline hover:text-gray-900"
                    @click="
                        editMode = !editMode;
                        if (!editMode) { $store.cart.items = $store.cart.items.slice(); }
                    "
                    x-text="editMode ? 'Chiudi modifica' : 'Modifica'">
                </button>
            </div>

            <template x-if="!$store.cart.items.length">
                <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                    Il carrello è vuoto. <a href="/" class="underline">Torna allo shop</a>
                </div>
            </template>

            <ul class="divide-y divide-gray-200" x-show="$store.cart.items.length">
                <template x-for="it in $store.cart.items" :key="String(it.id)">
                    <li class="py-3">
                        <div class="flex items-start gap-3">
                            <img
                                :src="it.image || 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2264%22 height=%2264%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f3f4f6%22/></svg>'"
                                :alt="it.name"
                                class="h-16 w-16 flex-shrink-0 rounded-lg object-cover ring-1 ring-gray-200"
                                loading="lazy"
                            >

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-sm font-medium text-gray-900 truncate" x-text="it.name"></h3>
                                    <span class="text-sm font-semibold text-gray-900" x-text="formatEuro(lineTotal(it))"></span>
                                </div>

                                <p class="mt-0.5 text-xs text-gray-500" x-show="it.variant" x-text="it.variant"></p>

                                <!-- Riga quantità / azioni -->
                                <div class="mt-2 flex items-center gap-2" x-show="editMode">
                                    <button
                                        type="button"
                                        class="text-xs text-red-600 underline"
                                        @click="$store.cart.remove(it.id)"
                                    >
                                        Rimuovi
                                    </button>
                                </div>

                                <div class="mt-1 text-xs text-gray-600" x-show="!editMode">
                                    Qtà: <span x-text="it.qty || 1"></span>
                                </div>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>

            <div class="mt-4 space-y-2 text-sm" x-show="$store.cart.items.length">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotale</span>
                    <span>€ <span x-text="reactiveTotal.toFixed(2).replace('.', ',')"></span></span>
                </div>

                <div class="flex justify-between text-gray-600">
                    <span>Spedizione</span>
                    <span x-text="shippingLabel()"></span>
                </div>

                <p class="text-[11px] text-gray-500 mt-1" x-show="remainingToFree() > 0">
                    Ti mancano € <span x-text="remainingToFree().toFixed(2).replace('.', ',')"></span> per la spedizione gratuita.
                </p>

                <div class="flex justify-between text-gray-600">
                    <span>IVA</span>
                    <span x-text="vatLabel()"></span>
                </div>

                <div class="border-t pt-2"></div>
                <div class="flex justify-between text-base font-bold text-gray-900">
                    <span>Totale</span>
                    <span>€ <span x-text="reactiveGrandTotal.toFixed(2).replace('.', ',')"></span></span>
                </div>
            </div>
        </div>

        <p class="text-[11px] leading-snug text-gray-500">
            Proseguendo accetti i <a href="/termini" class="underline">Termini</a> e la <a href="/privacy" class="underline">Privacy</a>.
        </p>
    </div>
</aside>

<script>
function cartWrapper() {
    return {
        editMode: false,
        reactiveTotal: 0,
        reactiveGrandTotal: 0,

        init() {
            this.update();
            window.addEventListener('cart:changed', this.update.bind(this));
            window.addEventListener('cart:ready', this.update.bind(this));
        },

        update() {
            const cart = Alpine.store('cart');
            if (!cart) return;
            this.reactiveTotal = Number(cart.total?.() ?? 0);
            this.reactiveGrandTotal = this.reactiveTotal + this.calcShipping();
        },

        lineTotal(it) { return (Number(it.price) || 0) * (Number(it.qty) || 1); },
        formatEuro(v) { return `€ ${Number(v).toFixed(2).replace('.', ',')}`; },

        calcShipping() { return this.reactiveTotal >= 35 ? 0 : 4.90; },
        shippingLabel() { return this.reactiveTotal >= 35 ? 'Gratis' : '€ 4,90'; },
        vatLabel() { const iva = this.reactiveTotal * 0.22; return `€ ${iva.toFixed(2).replace('.', ',')}`; },
        remainingToFree() { return Math.max(0, 35 - this.reactiveTotal); },
    }
}
</script>
