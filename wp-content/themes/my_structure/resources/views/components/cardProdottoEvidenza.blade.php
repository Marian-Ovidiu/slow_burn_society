<section x-data="shop()" class="px-4 md:px-8 lg:px-16 py-10 bg-[#fefcf7] border-t border-gray-200" id="shop"
    aria-labelledby="shop-title">
    <header class="mb-6 text-center">
        <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-gray-900" id="shop-title">
            Oppure componi il tuo kit da solo
        </h2>
        <p class="mt-1 text-xs text-gray-500" x-show="$store.cartReady && $store.cart.items.length" aria-live="polite">
            <span
                x-text="(() => { const ms = $store.cart.remainingMs(); if (ms <= 0) return 'Carrello scaduto'; const m = Math.floor(ms / 60000); const s = Math.floor((ms % 60000) / 1000); return `Carrello attivo — scade in ${m}m ${String(s).padStart(2,'0')}s`; })()"></span>
        </p>
    </header>

    <ul class="grid gap-6 grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4" role="list"
        aria-describedby="shop-title">
        @foreach ($products as $product)
            @php
                $pJs = $productsForJs[$product->id] ?? null;
                $stock = (int) ($productsForJs[$product->id]['stock'] ?? 0);
                $priceNum = number_format((float) $product->prezzo, 2, ',', '');
            @endphp

            <li>
                <article
                    class="group relative bg-white rounded-lg shadow-sm p-3 hover:shadow-md transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#386322]"
                    @click="openModal(@js($pJs ?? (object) []))"
                    @keydown.enter.prevent="openModal(@js($pJs ?? (object) []))"
                    @keydown.space.prevent="openModal(@js($pJs ?? (object) []))" tabindex="0"
                    aria-labelledby="p-ttl-{{ $product->id }}"
                    aria-describedby="p-desc-{{ $product->id }} p-meta-{{ $product->id }}" itemscope
                    itemtype="https://schema.org/Product" title="{{ $product->title }}">
                    <div class="overflow-hidden rounded-md mb-2">
                        <img src="{{ $product->immagine_1['url'] ?? '' }}" alt="{{ $product->title }}"
                            class="w-full h-44 object-contain mx-auto transition duration-300 rounded" loading="lazy"
                            decoding="async" width="560" height="320"
                            sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw" itemprop="image" />
                    </div>

                    <h3 id="p-ttl-{{ $product->id }}" class="text-sm font-semibold text-gray-900 truncate"
                        itemprop="name" x-text="truncate(@js($product->title), 35)"></h3>

                    <p id="p-desc-{{ $product->id }}" class="text-xs text-gray-500 leading-snug mt-1"
                        itemprop="description" title="{{ strip_tags($product->descrizione) }}">
                        {{ \Illuminate\Support\Str::limit(strip_tags($product->descrizione), 55) }}
                    </p>

                    <div id="p-meta-{{ $product->id }}" class="flex items-center justify-between mt-2"
                        itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                        <p class="text-sm text-gray-700">
                            €{{ $priceNum }}
                            <meta itemprop="priceCurrency" content="EUR" />
                            <meta itemprop="price"
                                content="{{ number_format((float) $product->prezzo, 2, '.', '') }}" />
                        </p>

                        <p class="text-[11px]"
                            :class="$store.cart.remainingFor({{ $product->id }}, Number(@js($stock))) > 0 ?
                                'text-green-600' : 'text-red-600'">
                            <span
                                x-text="'Disponibilità: ' + $store.cart.remainingFor({{ $product->id }}, Number(@js($stock)))"></span>
                            <link
                                :href="'https://schema.org/' + ($store.cart.remainingFor({{ $product->id }}, Number(
                                    @js($stock))) > 0 ? 'InStock' : 'OutOfStock')"
                                itemprop="availability" />
                        </p>
                    </div>

                    <button type="button"
                        :disabled="$store.cart.remainingFor({{ $product->id }}, Number(@js($stock))) <= 0"
                        :aria-disabled="$store.cart.remainingFor({{ $product->id }}, Number(@js($stock))) <= 0"
                        class="mt-2 w-full text-xs font-semibold py-1.5 px-3 rounded transition disabled:opacity-50 disabled:cursor-not-allowed bg-[#45752c] text-white hover:bg-[#386322] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#386322]"
                        @click.stop="(function () {
              const rem = $store.cart.remainingFor({{ $product->id }}, Number(@js($stock)));
              if (rem <= 0) return;
              addToCart(@js($productsForJs[$product->id]['cart'] ?? (object) []));
            })()"
                        :aria-label="'Aggiungi al carrello: ' + (@js($product->title))">
                        Aggiungi al carrello
                    </button>
                </article>
            </li>
        @endforeach
    </ul>

    @include('components.modaleProdotti')
</section>
