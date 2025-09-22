{{-- DX: riepilogo --}}
<style>
    /* rimuovi spin Chrome/Safari/Edge */
    input[type=number].no-spin::-webkit-outer-spin-button,
    input[type=number].no-spin::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* rimuovi spin Firefox */
    input[type=number].no-spin {
        -moz-appearance: textfield;
    }
</style>
<!-- COMPONENTE BLADE COMPLETO (aggiornato) -->
<aside class="md:col-span-5" x-data="cartWrapper()" x-init="init()">
    <div class="sticky top-4 space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold tracking-tight">Riepilogo ordine</h2>

                <button type="button" class="text-xs font-medium text-gray-600 underline hover:text-gray-900"
                    @click="
                        editMode = !editMode;
                        if (!editMode) {
                            $store.cart.items = $store.cart.items.slice();
                        }
                    "
                    x-text="editMode ? 'Chiudi modifica' : 'Modifica'">
                </button>
            </div>

            <template x-if="!$store.cart.items.length">
                <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                    Il carrello è vuoto. <a href="/" class="underline">Torna allo shop</a>
                </div>
            </template>

            <!-- ...lista articoli identica... -->

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
                    Ti mancano € <span x-text="remainingToFree().toFixed(2).replace('.', ',')"></span> per la spedizione
                    gratuita.
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
            Proseguendo accetti i <a href="/termini" class="underline">Termini</a> e la <a href="/privacy"
                class="underline">Privacy</a>.
        </p>
    </div>
</aside>

<script>
    function cartWrapper() {
        return {
            reactiveTotal: 0,
            reactiveGrandTotal: 0,

            init() {
                this.update();
                window.addEventListener('cart:changed', this.update.bind(this));
            },

            update() {
                const cart = Alpine.store('cart');
                this.reactiveTotal = cart.total();
                this.reactiveGrandTotal = this.reactiveTotal + this.calcShipping();
            },

            calcShipping() {
                // Esempio: gratis sopra 50€, altrimenti 5€
                return this.reactiveTotal >= 50 ? 0 : 5;
            },

            shippingLabel() {
                return this.reactiveTotal >= 50 ? 'Gratis' : '€ 5,00';
            },

            vatLabel() {
                const iva = this.reactiveTotal * 0.22;
                return `€ ${iva.toFixed(2).replace('.', ',')}`;
            },

            remainingToFree() {
                return Math.max(0, 50 - this.reactiveTotal);
            },
        }
    }
</script>
