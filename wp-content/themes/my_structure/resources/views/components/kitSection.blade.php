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
    <div class="text-center mb-6">
        <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-gray-900" id="text-left">
            🆕 Non sai cosa prendere? Ci abbiamo già pensato noi!
        </h2>
        <p class="mt-2 text-gray-600 text-sm">
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

    <!-- 2 colonne compatte -->
    <div class="grid gap-6 grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($latest as $kit)
            @php
                $kitJs = $kitsForJs[$kit->id] ?? null;
                $available = !empty(($kitsForJs[$kit->id] ?? [])['disponibilita']);
            @endphp

            <div class="group relative bg-white rounded-lg shadow-sm p-3 hover:shadow-md transition cursor-pointer"
                @click="modalOpen2 = true; selected2 = @js($kitJs ?? (object) [])">

                @if (!$available)
                    <span
                        class="absolute top-2 right-2 text-[10px] font-semibold px-2 py-0.5 rounded bg-red-100 text-red-700">
                        Non disponibile
                    </span>
                @endif

                <!-- Immagine compatta -->
                <div class="overflow-hidden rounded-md mb-2">
                    <img src="{{ $kit->immagine_kit['url'] ?? '' }}" alt="{{ $kit->nome }}"
                        class="w-full h-44 object-contain mx-auto transition duration-300 rounded" />
                </div>

                <!-- Dettagli compatti -->
                <div class="text-left space-y-1">
                    <h3 class="text-sm font-semibold text-gray-900 truncate" title="{{ $kit->nome }}">
                        {{ \Illuminate\Support\Str::limit($kit->nome, 40) }}
                    </h3>
                    @if ($kit->mini_descrizione)
                        <p class="text-xs text-gray-500 leading-snug">
                            {{ \Illuminate\Support\Str::limit($kit->mini_descrizione, 60) }}
                        </p>
                    @endif
                    <p class="text-sm text-gray-700">
                        €{{ number_format((float) str_replace(['€', ' ', ','], ['', '', '.'], $kit->prezzo), 2, ',', '.') }}
                    </p>

                    <p class="text-[11px] {{ $available ? 'text-green-600' : 'text-red-600' }}">
                        {{ $available ? 'Disponibile' : 'Non disponibile' }}
                    </p>
                </div>

                <!-- Aggiungi al carrello (CARD) compatto -->
                <button :disabled="!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))"
                    @click.stop="addToCart(@js($kitJs['cart'] ?? (object) []))"
                    class="mt-2 w-full text-xs font-semibold py-1.5 px-3 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="(!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))) ?
                    'bg-gray-300 text-gray-600 cursor-not-allowed' :
                    'bg-[#45752c] text-white hover:bg-[#386322]'">
                    <span
                        x-text="isInCart(@js($kitJs['cart'] ?? (object) [])) ? 'Nel carrello' : 'Aggiungi al carrello'"></span>
                </button>
            </div>
        @endforeach
    </div>

   @include('components.modaleKit')
</section>
