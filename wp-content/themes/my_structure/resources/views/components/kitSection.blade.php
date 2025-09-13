<section x-data="{
    modalOpen2: false,
    selected2: null,
    addToCart(item) {
        // Normalizza per il nuovo store
        const toNumber = (v) => {
            if (typeof v === 'number') return v;
            if (!v) return 0;
            // gestisce '12,50' e '€12,50'
            return Number(String(v).replace(/[€\s]/g, '').replace(',', '.')) || 0;
        };
        $store.cart.add({
            id: item.id,
            name: item.name ?? item.title,
            image: item.image ?? '',
            price: toNumber(item.price)
        });
    }
}" class="px-4 md:px-8 lg:px-16 py-10 bg-[#fefcf7] border-t border-gray-200">
    <div class="text-center mb-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900" id="text-left">
            🆕 Non sai cosa prendere? Ci abbiamo già pensato noi!
        </h2>
        <p class="mt-2 text-gray-600 text-sm md:text-base">
            Scopri i nostri Kit SlowBurn: selezionati con cura, pronti per il tuo rituale.
        </p>

        <!-- Countdown TTL carrello -->
        <p class="mt-1 text-xs text-gray-500" x-show="$store.cartReady && $store.cart.items.length">
            <span
                x-text="$store.cart.remainingMinutes() > 0
                ? 'Carrello attivo — scade in ' + $store.cart.remainingMinutes() + ' min'
                : 'Carrello scaduto'"></span>
        </p>
    </div>

    <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($latest as $kit)
            @php
                $kitJs = $kitsForJs[$kit->id] ?? null;
                $available = $kitJs['disponibilita'];
            @endphp

            <div class="group relative bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition cursor-pointer"
                @click="modalOpen2 = true; selected2 = @js($kitJs ?? (object) [])">

                {{-- Badge stato (opzionale ma utile) --}}
                @if (!$available)
                    <span
                        class="absolute top-2 right-2 text-[11px] font-semibold px-2 py-1 rounded bg-red-100 text-red-700">
                        Non disponibile
                    </span>
                @endif

                <!-- Immagine -->
                <div class="overflow-hidden rounded-md mb-2">
                    <img src="{{ $kit->immagine_kit['url'] ?? '' }}" alt="{{ $kit->nome }}"
                        class="w-full max-h-[600px] object-contain mx-auto transition duration-300 rounded">
                </div>

                <!-- Dettagli -->
                <div class="text-left">
                    <h3 class="text-base font-semibold text-gray-900 truncate" title="{{ $kit->nome }}">
                        {{ \Illuminate\Support\Str::limit($kit->nome, 40) }}
                    </h3>
                    @if ($kit->mini_descrizione)
                        <p class="text-sm text-gray-500 mt-1 leading-snug">
                            {{ \Illuminate\Support\Str::limit($kit->mini_descrizione, 60) }}
                        </p>
                    @endif
                    <p class="text-base text-gray-600 mt-1">
                        €{{ number_format((float) str_replace(['€', ' ', ','], ['', '', '.'], $kit->prezzo), 2, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs {{ $available ? 'text-green-600' : 'text-red-600' }}">
                        {{ $available ? 'Disponibile' : 'Non disponibile' }}
                    </p>
                </div>

                <!-- Aggiungi al carrello -->
                @php $available = !empty($kitJs['disponibilita']); @endphp

                <button @disabled(!$available)
                    class="w-full text-sm font-semibold py-2 px-4 rounded transition
         {{ $available ? 'bg-[#45752c] text-white hover:bg-[#386322]' : 'bg-gray-300 text-gray-600 cursor-not-allowed' }}
         disabled:opacity-50 disabled:cursor-not-allowed" @click.stop="addToCart(@js($kitJs['cart'] ?? (object) []))">
                    Aggiungi al carrello
                </button>
            </div>
        @endforeach
    </div>




    <!-- Overlay -->
    <div x-show="modalOpen2" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center px-4 py-8"
        @keydown.escape.window="modalOpen2 = false; selected2 = null" @click.self="modalOpen2 = false; selected2 = null"
        x-transition>

        <!-- Pannello -->
        <div class="bg-white w-full max-w-lg rounded-lg shadow-lg overflow-y-auto max-h-[95vh] relative p-6"
            @click.away="modalOpen2 = false; selected2 = null">

            <!-- Chiudi -->
            <button class="absolute top-2 right-2 text-gray-400 hover:text-black text-xl"
                @click="modalOpen2 = false; selected2 = null">✕</button>

            <!-- Immagine -->
            <img :src="selected2?.image || ''" alt="" class="w-full max-h-[400px] object-contain rounded mb-4">

            <!-- Titolo -->
            <h2 class="text-2xl font-bold mb-2 text-gray-900" x-text="selected2?.title ?? selected2?.name"></h2>

            <!-- Prezzo -->
            <p class="text-lg font-semibold text-[#45752c] mb-2">
                €<span x-text="Number(selected2?.price || 0).toFixed(2).replace('.', ',')"></span>
            </p>

            <!-- Descrizione -->
            <div class="text-sm text-gray-700 mb-4" x-html="selected2?.description || ''"></div>

            <!-- Lista Prodotti -->
            <template x-if="selected2?.products && selected2.products.length">
                <div class="mt-4">
                    <h3 class="text-base font-semibold text-gray-800 mb-3">Contenuto del Kit:</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <template x-for="(product, index) in selected2.products" :key="index">
                            <div class="flex items-start gap-3">
                                <img :src="product.image ?? ''" alt=""
                                    class="w-16 h-16 object-cover rounded border bg-gray-100">
                                <span class="text-sm text-gray-700 leading-snug" x-text="product.title"></span>
                                <p class="mt-1 text-xs"
                                    :class="(product.available ?? (Number(product.disponibilita) > 0)) ?
                                    'text-green-600' : 'text-red-600'">
                                    <span
                                        x-text="(product.available ?? (Number(product.disponibilita) > 0))
           ? 'Disponibile' : 'Non disponibile'"></span>
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Bottone Aggiungi al carrello -->
            <div class="text-left mt-3">
                <!-- Bottone nel modal -->
                <button type="button" :disabled="!Boolean(selected2?.disponibilita)"
                    class="w-full text-sm font-semibold bg-[#45752c] text-white py-2 px-4 rounded
               hover:bg-[#386322] transition disabled:opacity-50 disabled:cursor-not-allowed"
                    @click.stop="addToCart(selected2?.cart || {})">
                    <span
                        x-text="Boolean(selected2?.disponibilita) ? 'Aggiungi al carrello' : 'Non disponibile'"></span>
                </button>

            </div>

        </div>
    </div>

</section>
