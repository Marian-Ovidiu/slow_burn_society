<div x-data="{ modalOpen: false, selected: null }" class="relative px-4 py-8">

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($products as $product)
            @php
                $productForJs = [
                    'title' => $product->title,
                    'price' => $product->prezzo,
                    'image' => $product->immagine_1['url'] ?? '',
                    'description' => $product->description ?? '',
                    'gallery' => $product->gallery ?? [$product->immagine_1['url'], $product->immagine_2['url'], $product->immagine_3['url'], $product->immagine_4['url']],
                    'details' => $product->details ?? [],
                ];
            @endphp

            <div class="cursor-pointer bg-white p-4 rounded shadow hover:shadow-md transition"
                @click="modalOpen = true; selected = {{ json_encode($productForJs) }}">
                <img src="{{ $product->immagine_1['url'] ?? '' }}" class="w-full h-40 object-cover rounded">
                <h3 class="mt-2 font-semibold text-sm">{{ $product->title }}</h3>
                <p class="text-green-600 text-sm font-bold">€{{ $product->prezzo }}</p>
            </div>
        @endforeach
    </div>

    <!-- Modale -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4"
        x-transition>
        <div class="bg-white w-full max-w-md p-6 rounded relative" @click.away="modalOpen = false">
            <button class="absolute top-2 right-2 text-gray-500" @click="modalOpen = false">✕</button>

            <template x-if="selected">
                <div>
                    <h2 class="text-xl font-bold mb-2" x-text="selected.title"></h2>

                    <div class="flex gap-2 mb-4 overflow-x-auto">
                        <template x-for="(img, i) in selected.gallery" :key="i">
                            <img :src="img" class="w-24 h-24 object-cover rounded border">
                        </template>
                    </div>

                    <p class="text-green-600 font-semibold text-lg mb-2">€<span x-text="selected.price"></span></p>
                    <p class="text-sm mb-3 text-gray-700" x-text="selected.description"></p>

                    <ul class="list-disc pl-5 text-sm text-gray-600" x-show="selected.details.length">
                        <template x-for="(d, i) in selected.details" :key="i">
                            <li x-text="d"></li>
                        </template>
                    </ul>

                    <button class="mt-4 w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322]">
                        Aggiungi al carrello
                    </button>
                </div>
            </template>
        </div>
    </div>

</div>
