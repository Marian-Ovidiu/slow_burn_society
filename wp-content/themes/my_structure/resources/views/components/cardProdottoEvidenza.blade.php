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
                <article class="group relative bg-white rounded-lg shadow-sm p-3 hover:shadow-md transition"
                    aria-labelledby="p-ttl-{{ $product->id }}"
                    aria-describedby="p-desc-{{ $product->id }} p-meta-{{ $product->id }}" itemscope
                    itemtype="https://schema.org/Product" title="{{ $product->title }}">
                    {{-- Immagine: apre la modale (dettagli rapidi) --}}
                    <div class="overflow-hidden rounded-md mb-2">
                        <button type="button" class="block w-full text-left"
                            @click="openModal(@js($pJs ?? (object) []))" aria-haspopup="dialog"
                            aria-controls="product-modal-quick-{{ $product->id }}"
                            aria-label="Dettagli rapidi: {{ $product->title }}">
                            <img src="{{ $product->immagine_1['url'] ?? '' }}" alt="{{ $product->title }}"
                                class="w-full h-44 object-contain mx-auto transition duration-300 rounded"
                                loading="lazy" decoding="async" width="560" height="320"
                                sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw" itemprop="image" />
                        </button>
                    </div>

                    {{-- Titolo: porta alla pagina prodotto --}}
                    <h3 id="p-ttl-{{ $product->id }}" class="text-sm font-semibold text-gray-900 truncate"
                        itemprop="name">
                        <a href="{{ $product->url }}" class="hover:underline"
                            :aria-label="'Vai a: ' + @js($product->title)">
                            <span x-text="truncate(@js($product->title), 35)"></span>
                        </a>
                    </h3>

                    {{-- Descrizione breve --}}
                    <p id="p-desc-{{ $product->id }}" class="text-xs text-gray-500 leading-snug mt-1"
                        itemprop="description" title="{{ strip_tags($product->descrizione) }}">
                        {{ \Illuminate\Support\Str::limit(strip_tags($product->descrizione), 55) }}
                    </p>

                    {{-- Prezzo + Disponibilità --}}
                    <div id="p-meta-{{ $product->id }}" class="flex items-center justify-between mt-2"
                        itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                        <p class="text-sm text-gray-700">
                            €{{ $priceNum }}
                            <meta itemprop="priceCurrency" content="EUR" />
                            <meta itemprop="price"
                                content="{{ number_format((float) $product->prezzo, 2, '.', '') }}" />
                        </p>

                        @php $remBind = "\$store.cart.remainingFor({$product->id}, Number({$stock}))"; @endphp
                        <p class="text-[11px]" :class="{{ $remBind }} > 0 ? 'text-green-600' : 'text-red-600'">
                            <span x-text="'Disp.: ' + {{ $remBind }}"></span>
                            <link :href="'https://schema.org/' + ({{ $remBind }} > 0 ? 'InStock' : 'OutOfStock')"
                                itemprop="availability" />
                        </p>
                    </div>

                    {{-- CTA sdoppiata: Aggiungi + Dettagli --}}
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        {{-- Aggiungi al carrello --}}
                        <button type="button"
                            class="text-xs font-semibold py-1.5 px-3 rounded transition
                   bg-[#45752c] text-white hover:bg-[#386322]
                   disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="{{ $remBind }} <= 0" :aria-disabled="{{ $remBind }} <= 0"
                            @click.stop="(function () {
              const rem = {{ $remBind }};
              if (rem <= 0) return;
              addToCart(@js($productsForJs[$product->id]['cart'] ?? (object) []));
            })()"
                            :aria-label="'Aggiungi al carrello: ' + (@js($product->title))">
                            Aggiungi
                        </button>

                        {{-- Dettagli (pagina prodotto) --}}
                        <a href="{{ $product->url }}"
                            class="text-center text-xs font-semibold py-1.5 px-3 rounded border border-gray-300 bg-white hover:bg-gray-50">
                            Dettagli
                        </a>
                    </div>

                    {{-- (Opzionale) link testuale ai dettagli rapidi sotto i bottoni --}}
                    {{-- <button type="button" class="mt-2 w-full text-[11px] underline text-gray-600 hover:text-gray-900"
          @click="openModal(@js($pJs ?? (object) []))">
      Dettagli rapidi
  </button> --}}
                </article>
            </li>
        @endforeach
    </ul>

    @include('components.modaleProdotti')
</section>
