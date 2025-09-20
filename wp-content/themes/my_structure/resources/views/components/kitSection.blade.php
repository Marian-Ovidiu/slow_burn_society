<section x-data="{
    modalOpen2: false,
    selected2: null,
    selectedImage2: null, // <-- AGGIUNTA

    // true se l'item (kit) è già nel carrello
    isInCart(item) {
        const raw = String(item?.id ?? '');
        if (!raw || !$store?.cart?.items) return false;
        const kid = raw.replace(/^kit:/, '');
        const key = `kit:${kid}`;
        return ($store.cart.items || []).some(i => i.id === key);
    },
    addToCart(item) {
        if (this.isInCart(item)) return;
        const toNumber = (v) => {
            if (typeof v === 'number') return v;
            if (!v) return 0;
            return Number(String(v).replace(/[€\s]/g, '').replace(',', '.')) || 0;
        };
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
}" class="px-4 md:px-8 lg:px-16 py-10 bg-[#fefcf7] border-t border-gray-200"
    aria-labelledby="kit-section-title" id="kit-title">
    <header class="text-center mb-6">
        <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-gray-900" id="kit-section-title">
            🆕 Non sai cosa prendere? Ci abbiamo già pensato noi!
        </h2>
        <p class="mt-2 text-gray-600 text-sm" id="kit-section-desc">
            Scopri i nostri Kit SlowBurn: selezionati con cura, pronti per il tuo rituale.
        </p>

        <!-- Countdown TTL carrello (annunciato ai lettori di schermo) -->
        <p class="mt-1 text-xs text-gray-500" x-show="$store.cartReady && $store.cart.items.length" aria-live="polite">
            <span
                x-text="$store.cart.remainingMinutes() > 0
          ? 'Carrello attivo — scade in ' + $store.cart.remainingMinutes() + ' min'
          : 'Carrello scaduto'">
            </span>
        </p>
    </header>

    <!-- Lista semantica (migliora a11y e SEO: crawler capiscono l'elenco prodotti) -->
    <ul class="grid gap-6 grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4" role="list"
        aria-describedby="kit-section-desc">
        @foreach ($latest as $kit)
            @php
                $kitJs = $kitsForJs[$kit->id] ?? null;
                $available = !empty(($kitsForJs[$kit->id] ?? [])['disponibilita']);
                // opzionale: URL del dettaglio kit, se presente (aiuta SEO)
                $kitUrl = $kit->permalink ?? null;
                $priceNum = number_format(
                    (float) str_replace(['€', ' ', ','], ['', '', '.'], $kit->prezzo),
                    2,
                    ',',
                    '.',
                );
            @endphp

            <li role="listitem" class="h-full">
                <article
                    class="group relative bg-white rounded-lg shadow-sm p-3 hover:shadow-md transition h-full flex flex-col"
                    itemscope itemtype="https://schema.org/Product" aria-labelledby="kit-ttl-{{ $kit->id }}">
                    @if (!$available)
                        <span
                            class="absolute top-2 right-2 text-[10px] font-semibold px-2 py-0.5 rounded bg-red-100 text-red-700"
                            aria-label="Non disponibile">
                            Non disponibile
                        </span>
                    @endif

                    <!-- Immagine: usa width/height + sizes per CLS e performance -->
                    <div class="overflow-hidden rounded-md mb-2">
                        @if (!empty($kitUrl))
                            <a href="{{ $kitUrl }}" class="block" itemprop="url">
                                <img src="{{ $kit->immagine_kit['url'] ?? '' }}" alt="{{ $kit->nome }}"
                                    class="w-full h-44 object-contain mx-auto transition duration-300 rounded"
                                    loading="lazy" width="560" height="320"
                                    sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw" itemprop="image" />
                            </a>
                        @else
                            <button type="button" class="block w-full text-left"
                                @click="modalOpen2 = true; selected2 = @js($kitJs ?? (object) [])"
                                aria-haspopup="dialog" aria-controls="kit-modal"
                                aria-label="Apri dettagli {{ $kit->nome }}">
                                <img src="{{ $kit->immagine_kit['url'] ?? '' }}" alt="{{ $kit->nome }}"
                                    class="w-full h-44 object-contain mx-auto transition duration-300 rounded"
                                    loading="lazy" width="560" height="320"
                                    sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw" itemprop="image" />
                            </button>
                        @endif
                    </div>

                    <!-- Dettagli -->
                    <div class="text-left space-y-1 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 truncate" id="kit-ttl-{{ $kit->id }}"
                            itemprop="name">
                            @if (!empty($kitUrl))
                                <a href="{{ $kitUrl }}" class="hover:underline"
                                    title="{{ $kit->nome }}">{{ \Illuminate\Support\Str::limit($kit->nome, 40) }}</a>
                            @else
                                {{ \Illuminate\Support\Str::limit($kit->nome, 40) }}
                            @endif
                        </h3>

                        @if ($kit->mini_descrizione)
                            <p class="text-xs text-gray-500 leading-snug" itemprop="description">
                                {{ \Illuminate\Support\Str::limit($kit->mini_descrizione, 60) }}
                            </p>
                        @endif

                        <p class="text-sm text-gray-700" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span aria-hidden="true">€{{ $priceNum }}</span>
                            <meta itemprop="priceCurrency" content="EUR" />
                            <meta itemprop="price"
                                content="{{ number_format((float) str_replace(['€', ' ', ','], ['', '', '.'], $kit->prezzo), 2, '.', '') }}" />
                            <link itemprop="availability"
                                href="https://schema.org/{{ $available ? 'InStock' : 'OutOfStock' }}" />
                        </p>

                        <p class="text-[11px] {{ $available ? 'text-green-600' : 'text-red-600' }}">
                            {{ $available ? 'Disponibile' : 'Non disponibile' }}
                        </p>
                    </div>

                    <!-- CTA accessibile -->
                    <div class="mt-2">
                        <button type="button"
                            :disabled="!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))"
                            @click.stop="addToCart(@js($kitJs['cart'] ?? (object) []))"
                            class="w-full text-xs font-semibold py-1.5 px-3 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="(!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))) ?
                            'bg-gray-300 text-gray-600 cursor-not-allowed' :
                            'bg-[#45752c] text-white hover:bg-[#386322]'"
                            :aria-label="isInCart(@js($kitJs['cart'] ?? (object) [])) ?
                                'Nel carrello: {{ $kit->nome }}' :
                                'Aggiungi al carrello: {{ $kit->nome }}'">
                            <span
                                x-text="isInCart(@js($kitJs['cart'] ?? (object) [])) ? 'Nel carrello' : 'Aggiungi al carrello'">
                            </span>
                        </button>

                        <!-- Pulsante per aprire modal dettagli se hai anche il link SEO sopra -->
                        @if (!empty($kitUrl))
                            <button type="button"
                                class="mt-2 w-full text-xs underline text-gray-700 hover:text-gray-900"
                                @click="modalOpen2 = true; selected2 = @js($kitJs ?? (object) [])"
                                aria-haspopup="dialog" aria-controls="kit-modal"
                                aria-label="Apri dettagli {{ $kit->nome }}">
                                Dettagli rapidi
                            </button>
                        @endif
                    </div>
                </article>
            </li>
        @endforeach
    </ul>

    <!-- Modal (mantieni l’ID per aria-controls) -->
    <div id="kit-modal">
        @include('components.modaleKit')
    </div>
</section>
