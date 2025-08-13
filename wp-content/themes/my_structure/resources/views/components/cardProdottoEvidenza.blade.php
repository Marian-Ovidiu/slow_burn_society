<section x-data="{
    modalOpen: false,
    selected: null,
    selectedImage: null,
    addToCart(item) {
        // Normalizzo i campi per il nuovo store: { id, name, image, price }
        this.$store.cart.add({
            id: item.id,
            name: item.name ?? item.title,   // fallback se arriva 'title'
            image: item.image,
            price: Number(item.price)
        });
    }
}" class="relative px-4 py-8" id="shop" aria-labelledby="shop-title">
    <!-- Titolo sezione -->
    <header class="mb-8 text-center">
        <h2 class="section-title" id="shop-title">Shop</h2>
        <!-- Countdown TTL carrello -->
        <p class="text-xs text-gray-500" x-show="$store.cartReady && $store.cart.items.length">
            <span x-text="$store.cart.remainingMinutes() > 0
                ? 'Carrello attivo — scade in ' + $store.cart.remainingMinutes() + ' min'
                : 'Carrello scaduto'"></span>
        </p>
    </header>

    <!-- Lista prodotti (semantica) -->
    <ul class="product-grid" role="list">
        @foreach ($products as $product)
            @php
                $productForJs = [
                    'id' => $product->id, // <-- serve per il modal
                    'title' => $product->title,
                    'name' => $product->title, // per compatibilità con lo store
                    'price' => (float) $product->prezzo,
                    'image' => $product->immagine_1['url'] ?? '',
                    'description' => $product->descrizione ?? '',
                    'gallery' => array_values(array_filter([
                        $product->immagine_1['url'] ?? null,
                        $product->immagine_2['url'] ?? null,
                        $product->immagine_3['url'] ?? null,
                        $product->immagine_4['url'] ?? null,
                    ])),
                    'availability' => $product->disponibilita ?? 'Disponibile',
                    'category' => $product->categoria ?? null,
                    'brand' => $product->brand ?? null,
                ];
            @endphp

            <li>
                <article class="product-card"
                    @click="modalOpen = true; selected = {{ json_encode($productForJs) }}; selectedImage = null">
                    <img src="{{ $product->immagine_1['url'] ?? '' }}" alt="{{ $product->title }}" class="product-image"
                         loading="lazy">

                    <h3 class="product-title" x-data
                        x-init="$el.innerText = '{{ addslashes($product->title) }}'.length > 35 ? '{{ addslashes($product->title) }}'.slice(0, 35) + '…' : '{{ addslashes($product->title) }}'"
                        title="{{ $product->title }}">
                    </h3>

                    <p class="product-card-details text-xs" title="{{ strip_tags($product->descrizione) }}">
                        {{ \Illuminate\Support\Str::limit(strip_tags($product->descrizione), 55) }}
                    </p>

                    <p class="product-price">€{{ number_format((float) $product->prezzo, 2, ',', '') }}</p>

                    <button type="button"
                        class="mt-2 w-full bg-[#45752c] text-white text-xs py-1.5 rounded hover:bg-[#386322] transition"
                        @click.stop="addToCart({
                            id: {{ $product->id }},
                            name: '{{ addslashes($product->title) }}',
                            price: {{ (float) $product->prezzo }},
                            image: '{{ $product->immagine_1['url'] ?? '' }}'
                        })"
                        aria-label="Aggiungi {{ $product->title }} al carrello">
                        Aggiungi al carrello
                    </button>
                </article>
            </li>
        @endforeach
    </ul>

    <!-- Modale -->
    <div x-show="modalOpen" x-cloak x-transition
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4"
         role="dialog" aria-modal="true" aria-labelledby="modal-title"
         @keydown.escape.window="modalOpen=false">
        <div class="bg-white w-full max-w-md p-6 rounded relative max-h-[95vh] overflow-y-auto"
             @click.away="modalOpen=false">
            <button class="absolute top-2 right-2 text-gray-500" @click="modalOpen=false"
                    aria-label="Chiudi modale" type="button">✕</button>

            <template x-if="selected">
                <div>
                    <h2 class="text-xl font-bold mb-2" x-text="selected.title ?? selected.name"></h2>

                    <!-- Galleria -->
                    <div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide">
                        <template x-for="(img,i) in selected.gallery" :key="i">
                            <img :src="img"
                                 class="w-24 h-24 object-cover rounded border cursor-pointer hover:scale-105 transition"
                                 @click="selectedImage = img" :alt="'Anteprima ' + (i + 1)" loading="lazy">
                        </template>
                    </div>

                    <p class="text-green-600 font-semibold text-lg mb-2">
                        €<span x-text="Number(selected.price).toFixed(2)"></span>
                    </p>

                    <p class="text-sm mb-3 text-gray-700" x-html="selected.description"></p>

                    <!-- Info extra -->
                    <dl class="text-xs text-gray-500 mb-3 space-y-1">
                        <div x-show="selected.availability">
                            <dt class="inline font-semibold">Disponibilità:</dt>
                            <dd class="inline" x-text="selected.availability"></dd>
                        </div>
                        <div x-show="selected.category">
                            <dt class="inline font-semibold">Categoria:</dt>
                            <dd class="inline" x-text="selected.category"></dd>
                        </div>
                        <div x-show="selected.brand">
                            <dt class="inline font-semibold">Brand:</dt>
                            <dd class="inline" x-text="selected.brand"></dd>
                        </div>
                    </dl>

                    <button type="button"
                        class="mt-4 w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322] transition"
                        @click="addToCart({
                            id: selected.id,
                            name: selected.name ?? selected.title,
                            price: Number(selected.price),
                            image: selected.image || (selected.gallery?.[0] ?? '')
                        })">
                        Aggiungi al carrello
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Lightbox -->
    <template x-if="selectedImage">
        <div class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
             @click="selectedImage=null" role="dialog" aria-modal="true">
            <img :src="selectedImage" class="max-w-full max-h-[90vh] rounded shadow-xl"
                 alt="Zoom immagine prodotto">
        </div>
    </template>
</section>
