<section x-data="{
    modalOpen2: false,
    selected2: null,
    selectedImage2: null,

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
}" class="px-4 md:px-8 lg:px-16 py-10 bg-transparent border-t border-white/10"
    aria-labelledby="kit-section-title" id="kit-title">

    <header class="text-center mb-6">
        <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white" id="kit-section-title">
            🆕 Non sai cosa prendere? Ci abbiamo già pensato noi!
        </h2>
        <p class="mt-2 text-white/70 text-sm" id="kit-section-desc">
            Scopri i nostri Kit SlowBurn: selezionati con cura, pronti per il tuo rituale.
        </p>
        <span class="mt-2 inline-block text-white/80 text-[12px] md:text-[13px]">
            Nota: in questa fase alcuni contenuti sono brandizzati; i materiali ufficiali
            <span itemprop="brand">Slow Burn Society</span> arriveranno a breve.
        </span>
        <p class="mt-1 text-xs text-white/60" x-show="$store.cartReady && $store.cart.items.length" aria-live="polite">
            <span
                x-text="$store.cart.remainingMinutes() > 0
        ? 'Carrello attivo — scade in ' + $store.cart.remainingMinutes() + ' min'
        : 'Carrello scaduto'"></span>
        </p>
    </header>

    <ul class="grid gap-2 grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4" role="list"
        aria-describedby="kit-section-desc">
        @foreach ($latest as $kit)
            @php
                $available = true;
                $kitJs = $kitsForJs[$kit->id] ?? null;
                if($kitJs && !empty($kitJs['products'])){
                    $products = json_decode(json_encode($kitJs['products']));
                     foreach ($products as $product){
                        if(!$product->disponibilita){
                            $available = false;
                            break;
                        }
                     }
                }

                $kitUrl = $kit->url ?? (get_permalink($kit->id) ?? null);
                $priceNum = number_format(
                    (float) str_replace(['€', ' ', ','], ['', '', '.'], $kit->prezzo),
                    2,
                    ',',
                    '.',
                );
            @endphp

            <li role="listitem" class="h-full">
                <article
                    class="group relative rounded-lg p-3 h-full flex flex-col transition
                 border border-white/15 bg-white/10 backdrop-blur hover:bg-white/15 text-white"
                    itemscope itemtype="https://schema.org/Product" aria-labelledby="kit-ttl-{{ $kit->id }}">

                    @if (!$available)
                        <span
                            class="absolute top-2 right-2 text-[10px] font-semibold px-2 py-0.5 rounded
                        bg-red-600 text-red    "
                            aria-label="Non disponibile">
                            Non disponibile
                        </span>
                    @endif

                    <!-- Immagine -->
                    <div class="overflow-hidden rounded-md mb-2">
                        @if (!empty($kitUrl))
                            <a href="{{ $kitUrl }}" class="block" itemprop="url">
                                <img src="{{ $kit->immagine_kit['url'] ?? '' }}" alt="{{ $kit->nome }}"
                                    class="w-full h-44 object-contain mx-auto transition duration-300 rounded bg-white"
                                    loading="lazy" width="560" height="320"
                                    sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw" itemprop="image" />
                            </a>
                        @else
                            <button type="button" class="block w-full text-left"
                                @click="modalOpen2 = true; selected2 = @js($kitJs ?? (object) [])"
                                aria-haspopup="dialog" aria-controls="kit-modal"
                                aria-label="Apri dettagli {{ $kit->nome }}">
                                <img src="{{ $kit->immagine_kit['url'] ?? '' }}" alt="{{ $kit->nome }}"
                                    class="w-full h-44 object-contain mx-auto transition duration-300 rounded bg-white"
                                    loading="lazy" width="560" height="320"
                                    sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw" itemprop="image" />
                            </button>
                        @endif
                    </div>

                    <!-- Dettagli -->
                    <div class="text-left space-y-1 flex-1">
                        <h3 class="text-sm font-semibold text-white truncate" id="kit-ttl-{{ $kit->id }}"
                            itemprop="name">
                            @if (!empty($kitUrl))
                                <a href="{{ $kitUrl }}" class="hover:underline" title="{{ $kit->nome }}">
                                    {{ \Illuminate\Support\Str::limit($kit->nome, 40) }}
                                </a>
                            @else
                                {{ \Illuminate\Support\Str::limit($kit->nome, 40) }}
                            @endif
                        </h3>

                        @if ($kit->mini_descrizione)
                            <p class="text-xs text-white/70 leading-snug" itemprop="description">
                                {{ \Illuminate\Support\Str::limit($kit->mini_descrizione, 60) }}
                            </p>
                        @endif

                        <p class="text-sm text-white/90" itemprop="offers" itemscope
                            itemtype="https://schema.org/Offer">
                            <span aria-hidden="true">€{{ $priceNum }}</span>
                            <meta itemprop="priceCurrency" content="EUR" />
                            <meta itemprop="price"
                                content="{{ number_format((float) str_replace(['€', ' ', ','], ['', '', '.'], $kit->prezzo), 2, '.', '') }}" />
                            <link itemprop="availability"
                                href="https://schema.org/{{ $available ? 'InStock' : 'OutOfStock' }}" />
                        </p>

                        <p class="text-[11px] {{ $available ? 'text-green-300' : 'text-red-300' }}">
                            {{ $available ? 'Disponibile' : 'Non disponibile' }}
                        </p>
                    </div>

                    <!-- CTA: Aggiungi + Dettagli -->
                    <!-- CTA: Aggiungi + Dettagli -->
                    <div class="mt-2 grid gap-2 grid-cols-1 md:grid-cols-2">
                        <button type="button"
                            :disabled="!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))"
                            @click.stop="addToCart(@js($kitJs['cart'] ?? (object) []))"
                            class="w-full text-xs font-semibold py-1.5 px-3 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="(!@js($available) || isInCart(@js($kitJs['cart'] ?? (object) []))) ?
                            'bg-white/20 text-white cursor-not-allowed' :
                            'bg-[#45752c] text-white hover:bg-[#386322]'"
                            :aria-label="isInCart(@js($kitJs['cart'] ?? (object) [])) ?
                                'Nel carrello: {{ $kit->nome }}' :
                                'Aggiungi al carrello: {{ $kit->nome }}'">
                            <span x-text="isInCart(@js($kitJs['cart'] ?? (object) [])) ? 'Nel carrello' : 'Aggiungi'"></span>
                        </button>

                        @if (!empty($kitUrl))
                            <a href="{{ $kitUrl }}"
                                class="w-full text-center text-xs font-semibold py-1.5 px-3 rounded border border-white/30 bg-white/10 text-white hover:bg-white/20">
                                Dettagli
                            </a>
                        @else
                            <button type="button"
                                class="w-full text-center text-xs font-semibold py-1.5 px-3 rounded border border-white/30 bg-white/10 text-white hover:bg-white/20"
                                @click="modalOpen2 = true; selected2 = @js($kitJs ?? (object) [])"
                                aria-haspopup="dialog" aria-controls="kit-modal"
                                aria-label="Dettagli rapidi: {{ $kit->nome }}">
                                Dettagli
                            </button>
                        @endif
                    </div>
                </article>
            </li>
        @endforeach
    </ul>
</section>
