<section x-data="{
    modalOpen2: false,
    selected2: null,

    // true se l'item (kit) è già nel carrello
    isInCart(item) {
        const raw = String(item?.id ?? '');
        if (!raw) return false;
        const kid = raw.replace(/^kit:/, '');
        const key = `kit:${kid}`;
        return ($store.cart.items || []).some(i => i.id === key);
    },

    addToCart(item) {
        // blocca se già in cart
        if (this.isInCart(item)) return;

        const toNumber = (v) => {
            if (typeof v === 'number') return v;
            if (!v) return 0;
            return Number(String(v).replace(/[€\s]/g, '').replace(',', '.')) || 0;
        };

        // normalizza sempre come kit
        const raw = String(item.id ?? '');
        const kid = raw.replace(/^kit:/, '');
        const id = `kit:${kid}`;

        $store.cart.add({
            id,
            kitId: kid,
            type: 'kit',
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
                $available = !empty(($kitsForJs[$kit->id] ?? [])['disponibilita']);
            @endphp

            <div class="group relative bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition cursor-pointer"
                @click="modalOpen2 = true; selected2 = @js($kitJs ?? (object) [])">

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

                <!-- Aggiungi al carrello (CARD) -->
                <button :disabled="!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))"
                    @click.stop="addToCart(@js($kitJs['cart'] ?? (object) []))"
                    class="w-full text-sm font-semibold py-2 px-4 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="(!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))) ?
                    'bg-gray-300 text-gray-600 cursor-not-allowed' :
                    'bg-[#45752c] text-white hover:bg-[#386322]'">
                    <span x-text="isInCart(@js($kitJs['cart'] ?? (object) [])) ? 'Nel carrello' : 'Aggiungi al carrello'">
                    </span>
                </button>
            </div>
        @endforeach
    </div>

    <!-- Overlay / Modal -->
    <div x-show="modalOpen2" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center px-4 py-8"
        @keydown.escape.window="modalOpen2 = false; selected2 = null" @click.self="modalOpen2 = false; selected2 = null"
        x-transition>

        <div class="bg-white w-full max-w-lg rounded-lg shadow-lg overflow-y-auto max-h-[95vh] relative p-6"
            @click.away="modalOpen2 = false; selected2 = null">

            <button class="absolute top-2 right-2 text-gray-400 hover:text-black text-xl"
                @click="modalOpen2 = false; selected2 = null">✕</button>

            <img :src="selected2?.image || ''" alt="" class="w-full max-h-[400px] object-contain rounded mb-4">

            <h2 class="text-2xl font-bold mb-2 text-gray-900" x-text="selected2?.title ?? selected2?.name"></h2>

            <p class="text-lg font-semibold text-[#45752c] mb-2">
                €<span x-text="Number(selected2?.price || 0).toFixed(2).replace('.', ',')"></span>
            </p>

            <div class="text-sm text-gray-700 mb-4" x-html="selected2?.description || ''"></div>

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
                                    :class="(product.available ?? (Number(product.disponibilita) > 0)) ? 'text-green-600' :
                                    'text-red-600'">
                                    <span
                                        x-text="(product.available ?? (Number(product.disponibilita) > 0)) ? 'Disponibile' : 'Non disponibile'"></span>
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Bottone Aggiungi al carrello (MODAL) -->
            <div class="text-left mt-3">
                <button type="button" :disabled="!Boolean(selected2?.disponibilita) || isInCart(selected2?.cart || {})"
                    @click.stop="addToCart(selected2?.cart || {})"
                    class="w-full text-sm font-semibold py-2 px-4 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="(!Boolean(selected2?.disponibilita) || isInCart(selected2?.cart || {})) ?
                    'bg-gray-300 text-gray-600 cursor-not-allowed' :
                    'bg-[#45752c] text-white hover:bg-[#386322]'">
                    <span x-text="isInCart(selected2?.cart || {}) ? 'Nel carrello' : 'Aggiungi al carrello'">
                    </span>
                </button>
            </div>

        </div>
    </div>
</section>
