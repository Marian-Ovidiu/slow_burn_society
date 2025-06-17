<div x-data="{ modalOpen: false, selected: null, selectedImage: null }" class="relative px-4 py-8">
    <!-- Lista prodotti -->
    <div class="product-grid">
        @foreach ($products as $product)
            @php
                $productForJs = [
                    'title' => $product->title,
                    'price' => $product->prezzo,
                    'image' => $product->immagine_1['url'] ?? '',
                    'description' => $product->descrizione ?? '',
                    'gallery' => array_filter([
                        $product->immagine_1['url'] ?? null,
                        $product->immagine_2['url'] ?? null,
                        $product->immagine_3['url'] ?? null,
                        $product->immagine_4['url'] ?? null,
                    ]),
                    'details' => $product->descrizione ?? [],
                    'availability' => $product->disponibilita ?? 'Disponibile',
                    'category' => $product->categoria ?? null,
                    'brand' => $product->brand ?? null,
                ];
            @endphp

            <div class="product-card"
                @click="modalOpen = true; selected = {{ json_encode($productForJs) }}; selectedImage = null">
                <img src="{{ $product->immagine_1['url'] ?? '' }}" class="product-image">
                <h3 class="product-title" x-data x-init="$el.innerText = '{{ addslashes($product->title) }}'.length > 35 ? '{{ addslashes($product->title) }}'.slice(0, 35) + '…' : '{{ addslashes($product->title) }}'" title="{{ $product->title }}">
                </h3>
                <div class="product-card-details" title="{{ strip_tags($product->descrizione) }}" style="font-size: 0.75rem">
                    {{ \Illuminate\Support\Str::limit(strip_tags($product->descrizione), 55) }}
                </div>
                <p class="product-price">€{{ $product->prezzo }}</p>
            </div>
        @endforeach
    </div>

    <!-- Modale -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4"
        x-transition>
        <div class="bg-white w-full max-w-md p-6 rounded relative max-h-[95vh] overflow-y-auto"
            @click.away="modalOpen = false">
            <button class="absolute top-2 right-2 text-gray-500" @click="modalOpen = false">✕</button>

            <template x-if="selected">
                <div>
                    <h2 class="text-xl font-bold mb-2" x-text="selected.title"></h2>

                    <!-- Galleria -->
                    <div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide">
                        <template x-for="(img, i) in selected.gallery" :key="i">
                            <img :src="img"
                                class="w-24 h-24 object-cover rounded border cursor-pointer hover:scale-105 transition"
                                @click="selectedImage = img">
                        </template>
                    </div>

                    <p class="text-green-600 font-semibold text-lg mb-2">€<span x-text="selected.price"></span></p>

                    <p class="text-sm mb-3 text-gray-700" x-html="selected.description"></p>

                    <!-- Info extra -->
                    <div class="text-xs text-gray-500 mb-3 space-y-1">
                        <div x-show="selected.availability"><strong>Disponibilità:</strong> <span
                                x-text="selected.availability"></span></div>
                        <div x-show="selected.category"><strong>Categoria:</strong> <span
                                x-text="selected.category"></span></div>
                        <div x-show="selected.brand"><strong>Brand:</strong> <span x-text="selected.brand"></span></div>
                    </div>
                    <button class="mt-4 w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322]">
                        Aggiungi al carrello
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Lightbox -->
    <template x-if="selectedImage">
        <div class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center" @click="selectedImage = null">
            <img :src="selectedImage" class="max-w-full max-h-[90vh] rounded shadow-xl">
        </div>
    </template>

</div>
