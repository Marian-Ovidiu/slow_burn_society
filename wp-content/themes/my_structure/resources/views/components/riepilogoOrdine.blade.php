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

<aside class="md:col-span-5">
    <div class="sticky top-4 space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold tracking-tight">Riepilogo ordine</h2>

                <button type="button" class="text-xs font-medium text-gray-600 underline hover:text-gray-900"
                    @click="editMode = !editMode" x-text="editMode ? 'Chiudi modifica' : 'Modifica'">
                </button>
            </div>

            <template x-if="!$store.cart.items.length">
                <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                    Il carrello è vuoto. <a href="/" class="underline">Torna allo shop</a>
                </div>
            </template>

            <ul class="divide-y divide-gray-200" x-show="$store.cart.items.length">
                <template x-for="it in $store.cart.items" :key="it.id + (it.kitId || '')">
                    <li class="py-3">
                        <div class="flex items-start gap-3">
                            <img :src="it.image ||
                                'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2264%22 height=%2264%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f3f4f6%22/></svg>'"
                                :alt="it.name"
                                class="h-16 w-16 flex-shrink-0 rounded-lg object-cover ring-1 ring-gray-200"
                                loading="lazy">

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate text-sm font-medium text-gray-900" x-text="it.name">
                                    </p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        € <span
                                            x-text="$store.cart.lineSubtotal(it).toFixed(2).replace('.', ',')"></span>
                                    </p>
                                </div>

                                <!-- Riga info / controlli -->
                                <div class="mt-2 flex items-center justify-between">
                                    <!-- Qty solo testo -->
                                    <div class="text-xs text-gray-500" x-show="!editMode">
                                        Qtà: <strong x-text="it.qty"></strong>
                                        <template x-if="(it.maxQty ?? $store.cart.maxFor(it.id)) !== null">
                                            <span> — Max:
                                                <span x-text="(it.maxQty ?? $store.cart.maxFor(it.id))"></span>
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Label max anche in MODIFICA -->
                                    <template x-if="(it.maxQty ?? $store.cart.maxFor(it.id)) !== null">
                                        <span class="ml-2 text-[11px] text-gray-500">
                                            Max:
                                            <span x-text="(it.maxQty ?? $store.cart.maxFor(it.id))"></span>
                                        </span>
                                    </template>


                                    <!-- Controlli di modifica -->
                                    <div class="flex items-center gap-2" x-show="editMode">
                                        <!-- − -->
                                        <button type="button"
                                            class="h-8 w-8 rounded-lg ring-1 ring-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                                            :disabled="it.qty <= 1"
                                            @click="
    const max = (it.maxQty ?? $store.cart.maxFor(it.id));
    const next = Math.max(1, (Number(it.qty) || 1) - 1);
    $store.cart.setQty(it.id, max != null ? Math.min(next, max) : next);
  "
                                            aria-label="Diminuisci quantità">
                                            −
                                        </button>

                                        <!-- input numerico -->
                                        <div class="flex items-center gap-1">
                                            <input type="number"
                                                class="no-spin w-14 h-8 rounded-lg border-gray-300 text-center text-sm focus:border-black focus:ring-black"
                                                :value="it.qty" min="1" readonly @keydown.prevent
                                                @paste.prevent
                                                :title="(() => {
                                                    const max = (it.maxQty ?? $store.cart.maxFor(it.id));
                                                    return max != null ? `Massimo ${max} disponibili` : 'Quantità';
                                                })()">
                                            <template
                                                x-if="(() => {
            return $store.cart.maxFor(it.id) !== null;
          })()">
                                                <span class="text-[11px] text-gray-500 whitespace-nowrap">
                                                    / <span
                                                        x-text="$store.cart.maxFor(it.kitId ? `kit:${it.kitId}` : it.id)"></span>
                                                    disp.
                                                </span>
                                            </template>
                                        </div>

                                        <!-- + -->
                                        <button type="button"
                                            x-show="(() => {
  
    const max = (it.maxQty ?? $store.cart.maxFor(it.id));
    return !(max != null && (Number(it.qty) || 1) >= max);
  })()"
                                            class="h-8 w-8 rounded-lg ring-1 ring-gray-300 text-gray-700 hover:bg-gray-50"
                                            @click="
    const max = (it.maxQty ?? $store.cart.maxFor(it.id));
    const next = (Number(it.qty) || 1) + 1;
    $store.cart.setQty(it.id, max != null ? Math.min(next, max) : next);
  "
                                            aria-label="Aumenta quantità">
                                            +
                                        </button>

                                        <!-- Rimuovi -->
                                        <button type="button"
                                            class="ml-2 h-8 px-2 rounded-lg text-xs text-red-600 hover:bg-red-50"
                                            @click="$store.cart.remove(it.id)">
                                            Rimuovi
                                        </button>
                                    </div>


                                    <!-- Prezzo unitario -->
                                    <div class="text-[11px] text-gray-500">
                                        Prezzo: € <span x-text="Number(it.price).toFixed(2).replace('.', ',')"></span>
                                    </div>
                                </div>

                                <!-- Hint stock quando clampa -->
                                <p class="mt-1 text-[11px] text-amber-700"
                                    x-show="editMode && ($store.cart.maxFor(it.id) !== null) && (it.qty > $store.cart.maxFor(it.id))">
                                    Quantità ridotta alla disponibilità massima (<span
                                        x-text="$store.cart.maxFor(it.id)"></span>).
                                </p>
                            </div>
                        </div>
                    </li>

                </template>
            </ul>

            <div class="mt-4 space-y-2 text-sm" x-show="$store.cart.items.length">
                <div class="flex justify-between text-gray-600"><span>Subtotale</span><span>€
                        <span x-text="$store.cart.total().toFixed(2).replace('.', ',')"></span></span></div>
                <div class="flex justify-between text-gray-600">
                    <span>Spedizione</span>
                    <span x-text="shippingLabel()"></span>
                </div>

                <!-- Hint separato, in grigio piccolo -->
                <p class="text-[11px] text-gray-500 mt-1" x-show="remainingToFree() > 0">
                    Ti mancano € <span x-text="remainingToFree().toFixed(2).replace('.', ',')"></span> per la
                    spedizione gratuita.
                </p>
                <div class="flex justify-between text-gray-600"><span>IVA</span><span x-text="vatLabel()"></span></div>
                <div class="border-t pt-2"></div>
                <div class="flex justify-between text-base font-bold text-gray-900"><span>Totale</span><span>€
                        <span x-text="grandTotal().toFixed(2).replace('.', ',')"></span></span></div>
            </div>
        </div>
        <p class="text-[11px] leading-snug text-gray-500">Proseguendo accetti i <a href="/termini"
                class="underline">Termini</a> e la <a href="/privacy" class="underline">Privacy</a>.</p>
    </div>
</aside>
