  <section x-data="shop()" id="shop" aria-labelledby="shop-title"
    class="px-4 md:px-8 lg:px-16 py-10 bg-transparent border-t border-white/10">

    <header class="mb-6 text-center">
      <h2 id="shop-title" class="text-xl md:text-2xl font-extrabold tracking-tight text-white">
        Oppure componi il tuo kit da solo
      </h2>
      <p class="mt-1 text-xs text-white/60"
        x-show="$store.cartReady && $store.cart.items.length"
        aria-live="polite">
        <span x-text="(() => {
          const ms = $store.cart.remainingMs();
          if (ms <= 0) return 'Carrello scaduto';
          const m = Math.floor(ms / 60000);
          const s = Math.floor((ms % 60000) / 1000);
          return `Carrello attivo — scade in ${m}m ${String(s).padStart(2,'0')}s`;
        })()"></span>
      </p>
    </header>

  <ul class="grid gap-6 grid-cols-1 min-[480px]:grid-cols-2 min-[768px]:grid-cols-3 min-[1024px]:grid-cols-4" role="list"
        aria-describedby="shop-title">
      @foreach ($products as $product)
        @php
          $pJs = $productsForJs[$product->id] ?? null;
          $stock = (int) ($productsForJs[$product->id]['stock'] ?? 0);
          $priceNum = number_format((float) $product->prezzo, 2, ',', '');
          $remBind = "\$store.cart.remainingFor({$product->id}, Number({$stock}))";
        @endphp

        <li>
          <article class="group relative rounded-lg p-3 transition
                          border border-white/15 bg-white/10 backdrop-blur
                          hover:bg-white/15"
                  aria-labelledby="p-ttl-{{ $product->id }}"
                  aria-describedby="p-desc-{{ $product->id }} p-meta-{{ $product->id }}"
                  itemscope itemtype="https://schema.org/Product"
                  title="{{ $product->title }}">

            <!-- Immagine → link alla pagina prodotto (niente popup) -->
            <div class="overflow-hidden rounded-md mb-2">
              <a href="{{ $product->url }}" class="block w-full text-left"
                aria-label="Vai al prodotto: {{ $product->title }}">
                <img src="{{ $product->immagine_1['url'] ?? '' }}" alt="{{ $product->title }}"
                    class="w-full h-44 object-contain mx-auto transition duration-300 rounded"
                    loading="lazy" decoding="async" width="560" height="320"
                    sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw"
                    itemprop="image" />
              </a>
            </div>

            <h3 id="p-ttl-{{ $product->id }}" class="text-sm font-semibold text-white truncate" itemprop="name">
              <a href="{{ $product->url }}" class="hover:underline"
                :aria-label="'Vai a: ' + @js($product->title)">
                <span x-text="truncate(@js($product->title), 35)"></span>
              </a>
            </h3>

            <p id="p-desc-{{ $product->id }}" class="text-xs text-white/70 leading-snug mt-1"
              itemprop="description" title="{{ strip_tags($product->descrizione) }}">
              {{ \Illuminate\Support\Str::limit(strip_tags($product->descrizione), 55) }}
            </p>

            <div id="p-meta-{{ $product->id }}" class="flex items-center justify-between mt-2"
                itemprop="offers" itemscope itemtype="https://schema.org/Offer">
              <p class="text-sm text-white/90">
                €{{ $priceNum }}
                <meta itemprop="priceCurrency" content="EUR" />
                <meta itemprop="price" content="{{ number_format((float) $product->prezzo, 2, '.', '') }}" />
              </p>

              <p class="text-[11px]" :class="{{ $remBind }} > 0 ? 'text-green-300' : 'text-red-300'">
                <span x-text="'Disp.: ' + {{ $remBind }}"></span>
                <link :href="'https://schema.org/' + ({{ $remBind }} > 0 ? 'InStock' : 'OutOfStock')"
                      itemprop="availability" />
              </p>
            </div>

            <div class="mt-2 grid grid-cols-2 gap-2">
              <button type="button"
                      class="text-xs font-semibold py-1.5 px-3 rounded transition
                            bg-[#45752c] text-white hover:bg-[#386322]
                            disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="{{ $remBind }} <= 0" :aria-disabled="{{ $remBind }} <= 0"
                      @click.stop="(function () {
                        const rem = {{ $remBind }}; if (rem <= 0) return;
                        addToCart(@js($productsForJs[$product->id]['cart'] ?? (object) []));
                      })()"
                      :aria-label="'Aggiungi al carrello: ' + (@js($product->title))">
                Aggiungi
              </button>

              <a href="{{ $product->url }}"
                class="text-center text-xs font-semibold py-1.5 px-3 rounded
                        border border-white/30 bg-white/10 text-white
                        hover:bg-white/20">
                Dettagli
              </a>
            </div>
          </article>
        </li>
      @endforeach
    </ul>
  </section>
