@extends('layouts.mainLayout')
@section('trippy', true)
@section('content')
    <script>
        window.STRIPE_PK = window.STRIPE_PK || "{{ my_env('STRIPE_PK') ?? '' }}";
        // Mappa product_id -> stock massimo (solo prodotti singoli)
        window.inventoryMax = @json($inventoryMap ?? []);

        document.addEventListener('alpine:init', () => {
            const cart = Alpine.store('cart');
            const map = window.inventoryMax || {};

            // Precarica maxQty sugli item del carrello (prodotti)
            if (cart && Array.isArray(cart.items)) {
                cart.items.forEach(it => {
                    if (it.kitId) return; // i kit hanno logica separata via API
                    const mx = map[it.id];
                    if (Number.isFinite(mx)) it.maxQty = mx;
                });
                cart.save?.();
                cart.emitChanged?.();
            }
        });
    </script>
    <script src="https://js.stripe.com/v3"></script>

    <section x-data="checkout()" class="mx-auto w-full max-w-6xl px-4 py-6 md:py-10">

        {{-- Banner countdown carrello --}}
        <div x-show="$store.cartReady && $store.cart.items.length"
            class="mb-4 rounded-xl border border-yellow-200 bg-yellow-50 p-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm text-yellow-900">
                    Carrello attivo — scade tra
                    <span class="font-semibold" x-text="$store.cart.remainingFormatted()"></span>
                </p>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded bg-gray-200">
                <div class="h-full bg-[#45752c] transition-all"
                    :style="`width:${Math.max(0, Math.min(100, Math.round(($store.cart.remainingSeconds() / $store.cart.ttlSeconds) * 100)))}%`">
                </div>
            </div>
        </div>

        {{-- Alert scadenza --}}
        <div x-show="expired" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            Il carrello è scaduto. Puoi comunque completare il pagamento già avviato,
            oppure <a href="/#shop" class="underline">torna allo shop</a>.
        </div>

        {{-- Stepper --}}
        <nav class="mb-6 md:mb-8">
            <ol class="flex items-center gap-2 text-xs md:text-sm">
                <li class="flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-[#45752c] text-white">1</span>
                    <span class="font-semibold">Carrello</span>
                </li>
                <span class="h-px w-6 bg-gray-300 md:w-12"></span>
                <li class="flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-black text-white">2</span>
                    <span class="font-semibold">Checkout</span>
                </li>
                <span class="h-px w-6 bg-gray-300 md:w-12"></span>
                <li class="flex items-center gap-2 opacity-50">
                    <span class="grid h-6 w-6 place-items-center rounded-full border border-gray-300">3</span>
                    <span class="font-semibold">Conferma</span>
                </li>
            </ol>
        </nav>

        {{-- Stato store --}}
        <template x-if="!$store.cartReady">
            <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600">Caricamento carrello…</div>
        </template>

        <div x-show="$store.cartReady" class="grid grid-cols-1 gap-6 md:grid-cols-12 md:gap-8">
            {{-- SX: dati + pagamento --}}
            <div class="md:col-span-7 space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold tracking-tight">Dati cliente</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm text-gray-700">Nome</label>
                            <input type="text" x-model="form.firstName"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black bg-white text-gray-900 placeholder-gray-400"
                                placeholder="Mario">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-gray-700">Cognome</label>
                            <input type="text" x-model="form.lastName"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black bg-white text-gray-900 placeholder-gray-400"
                                placeholder="Rossi">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm text-gray-700">Email</label>
                            <input type="email" x-model="form.email"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black bg-white text-gray-900 placeholder-gray-400"
                                placeholder="mario@esempio.it">
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold tracking-tight">Indirizzo di spedizione</h2>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
                        <div class="md:col-span-4">
                            <label class="mb-1 block text-sm text-gray-700">Via</label>
                            <input type="text" x-model.lazy="form.street"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black bg-white text-gray-900 placeholder-gray-400"
                                placeholder="Via Roma">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm text-gray-700">N. civico</label>
                            <input type="text" x-model.lazy="form.streetNo"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black bg-white text-gray-900 placeholder-gray-400"
                                placeholder="12/A">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm text-gray-700">CAP</label>
                            <input type="text" x-model.lazy="form.cap" inputmode="numeric" maxlength="5" pattern="\d{5}"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black bg-white text-gray-900 placeholder-gray-400"
                                placeholder="00100">
                        </div>

                        <div class="md:col-span-3">
                            <label class="mb-1 block text-sm text-gray-700">Città</label>
                            <input type="text" x-model.lazy="form.city"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black"
                                placeholder="Roma">
                        </div>

                        <div class="md:col-span-1">
                            <label class="mb-1 block text-sm text-gray-700">Prov.</label>
                            <input type="text" x-model.lazy="form.province" maxlength="2"
                                @input="form.province = (form.province || '').toUpperCase().replace(/[^A-Z]/g,'')"
                                class="w-full uppercase rounded-lg border-gray-300 focus:border-black focus:ring-black text-center"
                                placeholder="RM">
                        </div>
                    </div>

                    <p class="mt-2 text-[11px] text-gray-500">
                        Spediamo solo in Italia. Inserisci CAP a 5 cifre e provincia a 2 lettere.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-bold tracking-tight">Pagamento</h2>
                    <div id="payment-element" class="rounded-xl border border-gray-200 p-4"></div>

                    <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                        <p>Transazione sicura • SSL</p>
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 2l7 4v6c0 5-3.5 9-7 10-3.5-1-7-5-7-10V6l7-4z" stroke="currentColor" />
                            </svg>
                            <span>Stripe</span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <button
                            class="w-full rounded-xl bg-[#45752c] py-3 font-semibold text-white shadow hover:bg-[#386322] disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!canPay()" @click="pay()">
                            <span x-show="!loading">Paga ora</span>
                            <span x-show="loading">Elaboro…</span>
                        </button>
                        <p class="text-sm text-red-600" x-text="error"></p>
                    </div>
                </div>
            </div>

            @include('components.riepilogoOrdine')
        </div>
    </section>
@endsection
