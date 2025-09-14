@extends('layouts.mainLayout')

@section('content')
    <script>
        window.STRIPE_PK = window.STRIPE_PK || "{{ my_env('STRIPE_PK') ?? '' }}";
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
                {{-- usa inline calc o la tua progressWidth() se l’hai nel componente --}}
                <div class="h-full bg-[#45752c] transition-all"
                    :style="`width: ${Math.max(0, Math.min(100, Math.round(($store.cart.remainingSeconds() / 300) * 100)))}%`">
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
                <li class="flex items-center gap-2"><span
                        class="grid h-6 w-6 place-items-center rounded-full bg-[#45752c] text-white">1</span><span
                        class="font-semibold">Carrello</span></li>
                <span class="h-px w-6 bg-gray-300 md:w-12"></span>
                <li class="flex items-center gap-2"><span
                        class="grid h-6 w-6 place-items-center rounded-full bg-black text-white">2</span><span
                        class="font-semibold">Checkout</span></li>
                <span class="h-px w-6 bg-gray-300 md:w-12"></span>
                <li class="flex items-center gap-2 opacity-50"><span
                        class="grid h-6 w-6 place-items-center rounded-full border border-gray-300">3</span><span
                        class="font-semibold">Conferma</span></li>
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
                        <div><label class="mb-1 block text-sm text-gray-700">Nome</label>
                            <input type="text" x-model="form.firstName"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black"
                                placeholder="Mario">
                        </div>
                        <div><label class="mb-1 block text-sm text-gray-700">Cognome</label>
                            <input type="text" x-model="form.lastName"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black"
                                placeholder="Rossi">
                        </div>
                        <div class="md:col-span-2"><label class="mb-1 block text-sm text-gray-700">Email</label>
                            <input type="email" x-model="form.email"
                                class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black"
                                placeholder="mario@esempio.it">
                        </div>
                    </div>
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
                            :disabled="!canPay()" @click="pay">
                            <span x-show="!loading">Paga ora</span>
                            <span x-show="loading">Elaboro…</span>
                        </button>
                        <p class="text-sm text-red-600" x-text="error"></p>
                    </div>
                </div>
            </div>

            {{-- DX: riepilogo --}}
            <aside class="md:col-span-5">
                <div class="sticky top-4 space-y-4">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 md:p-5 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-lg font-bold tracking-tight">Riepilogo ordine</h2>
                            <a href="/#shop" class="text-xs font-medium text-gray-500 underline">Modifica</a>
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
                                                <p class="truncate text-sm font-medium text-gray-900" x-text="it.name"></p>
                                                <p class="text-sm font-semibold text-gray-900">€ <span
                                                        x-text="$store.cart.lineSubtotal(it).toFixed(2).replace('.', ',')"></span>
                                                </p>
                                            </div>
                                            <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                                                <span>Qtà: <strong x-text="it.qty"></strong></span>
                                                <span>Prezzo: € <span
                                                        x-text="Number(it.price).toFixed(2).replace('.', ',')"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <div class="mt-4 space-y-2 text-sm" x-show="$store.cart.items.length">
                            <div class="flex justify-between text-gray-600"><span>Subtotale</span><span>€ <span
                                        x-text="$store.cart.total().toFixed(2).replace('.', ',')"></span></span></div>
                            <div class="flex justify-between text-gray-600"><span>Spedizione</span><span
                                    x-text="shippingLabel()"></span></div>
                            <div class="flex justify-between text-gray-600"><span>IVA</span><span
                                    x-text="vatLabel()"></span></div>
                            <div class="border-t pt-2"></div>
                            <div class="flex justify-between text-base font-bold text-gray-900"><span>Totale</span><span>€
                                    <span x-text="grandTotal().toFixed(2).replace('.', ',')"></span></span></div>
                        </div>
                    </div>
                    <p class="text-[11px] leading-snug text-gray-500">Proseguendo accetti i <a href="/termini"
                            class="underline">Termini</a> e la <a href="/privacy" class="underline">Privacy</a>.</p>
                </div>
            </aside>
        </div>
    </section>
@endsection
