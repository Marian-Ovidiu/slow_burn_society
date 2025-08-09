<section class="px-4 md:px-8 lg:px-16 py-10 bg-[#fefcf7] border-t border-gray-200">
    <div class="text-center mb-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900">
            🆕 Non sai cosa prendere? Ci abbiamo già pensato noi!
        </h2>
        <p class="mt-2 text-gray-600 text-sm md:text-base">
            Scopri i nostri Kit SlowBurn: selezionati con cura, pronti per il tuo rituale.
        </p>
    </div>

    <div class="grid gap-6 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($latest as $kit)
            <div class="group relative bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition cursor-pointer"
                @click="modalOpen2 = true; selected2 = {{ json_encode([
                    'title' => $kit->nome,
                    'description' => $kit->descrizione,
                    'image' => $kit->immagine_kit['url'] ?? '',
                    'price' => $kit->prezzo,
                    'products' => collect($kit->prodotti)->map(function ($product) {
                        return [
                            'title' => $product->post_title ?? '',
                            'image' => get_field('immagine_1', $product->ID)['url'] ?? '',
                        ];
                    }),
                ]) }}">

                <!-- Immagine -->
                <div class="overflow-hidden rounded-md mb-2">
                    <img src="{{ $kit->immagine_kit['url'] ?? '' }}" alt="{{ $kit->nome }}"
                        class="w-full max-h-[600px] object-contain mx-auto transition duration-300 rounded">
                </div>

                <!-- Dettagli -->
                <div class="text-left">
                    <h3 class="text-base font-semibold text-gray-900 truncate" title="{{ $kit->nome }}">
                        {{ \Illuminate\Support\Str::limit($kit->nome, 40) }}
                    </h3>
                    @if ($kit->mini_descrizione)
                        <p class="text-sm text-gray-500 mt-1 leading-snug">
                            {{ \Illuminate\Support\Str::limit($kit->mini_descrizione, 60) }}
                        </p>
                    @endif
                    <p class="text-base text-gray-600 mt-1">
                        €{{ number_format((float) str_replace(['€', ','], ['', '.'], $kit->prezzo), 2, ',', '.') }}
                    </p>
                </div>
                <div class="text-left mt-3">
                    <button
                        class="w-full text-sm font-semibold bg-[#45752c] text-white py-2 px-4 rounded hover:bg-[#386322] transition"
                        @click.stop="addToCart({ id: {{ $kit->id }}, name: '{{ $kit->nome }}', price: '{{ $kit->prezzo }}' })">
                        Aggiungi al carrello
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modale Kit -->
    <!-- Modale Kit -->
    <div x-show="modalOpen2" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center px-4 py-8"
        @keydown.escape.window="modalOpen2 = false" @click.away="modalOpen2 = false" x-transition>

        <template x-if="selected2">
            <div class="bg-white w-full max-w-lg rounded-lg shadow-lg overflow-y-auto max-h-[95vh] relative p-6">
                <!-- Chiudi -->
                <button class="absolute top-2 right-2 text-gray-400 hover:text-black text-xl"
                    @click="modalOpen2 = false">✕</button>

                <!-- Immagine -->
                <img :src="selected2.image" alt="" class="w-full max-h-[400px] object-contain rounded mb-4">

                <!-- Titolo -->
                <h2 class="text-2xl font-bold mb-2 text-gray-900" x-text="selected2.title"></h2>

                <!-- Prezzo -->
                <p class="text-lg font-semibold text-[#45752c] mb-2">
                    €<span
                        x-text="Number(parseFloat((selected2.price || '').toString().replace('€','').replace(',','.'))).toFixed(2).replace('.', ',')">
                    </span>
                </p>

                <!-- Descrizione -->
                <div class="text-sm text-gray-700 mb-4" x-html="selected2.description"></div>

                <!-- Lista Prodotti con immagini -->
                <template x-if="selected2.products && selected2.products.length">
                    <div class="mt-4">
                        <h3 class="text-base font-semibold text-gray-800 mb-3">Contenuto del Kit:</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <template x-for="(product, index) in selected2.products" :key="index">
                                <div class="flex items-start gap-3">
                                    <img :src="product.image ?? product.immagine_1?.url ?? ''" alt=""
                                        class="w-16 h-16 object-cover rounded border bg-gray-100">
                                    <span class="text-sm text-gray-700 leading-snug"
                                        x-text="product.title ?? product.post_title"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Bottone -->
                <div class="mt-6">
                    <button
                        class="w-full text-sm font-semibold bg-[#45752c] text-white py-3 rounded hover:bg-[#386322] transition"
                        @click="addToCart({ id: selected2.id ?? null, name: selected2.title, price: selected2.price })">
                        Aggiungi al carrello
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- 🔽 Bottone Aggiungi al carrello -->
    <div class="mt-6">
        <button class="w-full text-sm font-semibold bg-[#45752c] text-white py-3 rounded hover:bg-[#386322] transition"
            @click="addToCart({ id: selected2.id ?? null, name: selected2.title, price: selected2.price })">
            Aggiungi al carrello
        </button>
    </div>

    </div>
    </div>
