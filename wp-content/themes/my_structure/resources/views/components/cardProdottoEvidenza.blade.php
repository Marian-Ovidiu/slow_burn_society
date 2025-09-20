<section x-data="shop()" class="relative px-4 py-8" id="shop" aria-labelledby="shop-title">
    <!-- Titolo sezione -->
    <header class="mb-8 text-center">
        <h2 class="section-title" id="shop-title">Oppure componi il tuo  kit da solo</h2>
        <!-- Countdown TTL carrello -->
        <p class="text-xs text-gray-500" x-show="$store.cartReady && $store.cart.items.length">
            <span
                x-text="(() => {
    const ms = $store.cart.remainingMs();
    if (ms <= 0) return 'Carrello scaduto';
    const m = Math.floor(ms / 60000);
    const s = Math.floor((ms % 60000) / 1000);
    return `Carrello attivo — scade in ${m}m ${String(s).padStart(2,'0')}s`;
  })()">
            </span>
        </p>
    </header>

    <!-- Lista prodotti (semantica) -->
    <ul class="product-grid" role="list">
        @foreach ($products as $product)
            <li>
                <article class="product-card" @click="openModal(@js($productsForJs[$product->id]))">
                    <img src="{{ $product->immagine_1['url'] ?? '' }}" alt="{{ $product->title }}" class="product-image" loading="lazy">

                    <h3 class="product-title" x-text="truncate(@js($product->title), 35)" title="{{ $product->title }}"></h3>

                    <p class="product-card-details text-xs" title="{{ strip_tags($product->descrizione) }}">
                        {{ \Illuminate\Support\Str::limit(strip_tags($product->descrizione), 55) }}
                    </p>

                    <div class="flex justify-between">
                        <div>
                        <p class="product-price">€{{ number_format((float) $product->prezzo, 2, ',', '') }}</p>
                        </div>
                        <div>
                        <p class="product-price"
                            x-text="'Disponibilità: ' + $store.cart.remainingFor({{ $product->id }}, Number(@js($productsForJs[$product->id]['stock'] ?? 0)))">
                        </p>
                        </div>
                    </div>

                    <button type="button"
                        :disabled="$store.cart.remainingFor({{ $product->id }}, Number(@js($productsForJs[$product->id]['stock'] ?? 0))) == 0"
                        class="mt-4 w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322] transition disabled:opacity-50 disabled:cursor-not-allowed"
                        @click.stop="addToCart(@js($productsForJs[$product->id]['cart'] ?? (object) []))">
                        Aggiungi al carrello
                    </button>
                </article>
            </li>

        @endforeach
    </ul>

    @include('components.modaleProdotti')

</section>
