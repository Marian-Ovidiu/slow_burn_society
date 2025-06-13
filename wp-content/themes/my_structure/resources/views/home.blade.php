@extends('layouts.mainLayout')

@section('content')
    {{-- AlpineJS scope globale --}}
    <div x-data="{ modalOpen: false, selected: null, modalOpen2: false, selected2: null }">
        @if ($dataHero)
            @include('components.heroSection', [
                'dataHero' => $dataHero,
            ])
        @endif
        @if ($products)
            @include('components.cardProdottoEvidenza', [
                'subdata' => $subdata, 
                'products' => $products
            ])
        @endif

        <!-- Banner promozionale -->
        <section class="px-4 md:px-8 lg:px-16 py-6 bg-yellow-100 text-center">
            <h2 class="text-xl font-semibold md:text-2xl">🎁 Spedizione gratuita sopra i 30€!</h2>
            <p class="text-sm">Solo per pochi giorni, approfittane ora.</p>
        </section>

        <!-- Sezione Novità -->
        <section class="px-4 md:px-8 lg:px-16 py-6">
            <h2 class="text-xl md:text-2xl font-bold mb-4">Novità dal bancone 🆕</h2>
            <div class="space-y-4 md:grid md:grid-cols-3 md:gap-6 md:space-y-0">
                @foreach ($latest as $item)
                    <div class="flex items-center gap-4 bg-white rounded shadow p-3 cursor-pointer transition hover:shadow-md"
                        @click="modalOpen2 = true; selected2 = {{ json_encode($item) }}">
                        <img src="{{ $item['image'] }}" class="w-16 h-16 object-cover rounded" alt="{{ $item['name'] }}">
                        <div>
                            <h3 class="text-sm font-semibold">{{ $item['name'] }}</h3>
                            <p class="text-xs text-gray-500">€{{ $item['price'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Modale novità -->
        <div x-show="modalOpen2" x-cloak
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center px-4" x-transition>
            <div class="bg-white w-full max-w-md md:max-w-xl lg:max-w-2xl rounded-lg p-6 md:p-8 relative"
                @click.away="modalOpen2 = false">
                <button class="absolute top-2 right-2 text-gray-500" @click="modalOpen2 = false">
                    <span class="material-symbols-rounded text-2xl">close</span>
                </button>

                <template x-if="selected2">
                    <div>
                        <h2 class="text-lg md:text-xl font-bold mb-2" x-text="selected2.name"></h2>
                        <div class="flex gap-2 overflow-x-auto mb-4">
                            <template x-for="(img, i) in selected2.gallery || [selected2.image]" :key="i">
                                <img :src="img" class="w-24 h-24 md:w-32 md:h-32 object-cover rounded border" />
                            </template>
                        </div>
                        <p class="text-green-600 font-semibold text-lg mb-2">€<span x-text="selected2.price"></span></p>
                        <p class="text-sm text-gray-700 mb-3" x-text="selected2.description"></p>
                        <ul class="text-sm list-disc pl-5 mb-4 space-y-1 text-gray-600" x-show="selected2.details?.length">
                            <template x-for="(d, i) in selected2.details" :key="i">
                                <li x-text="d"></li>
                            </template>
                        </ul>
                        <button class="w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322] transition mb-3">
                            Aggiungi al carrello
                        </button>
                        <button @click="modalOpen2 = false"
                            class="w-full bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300 transition">
                            Torna indietro
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Recensioni -->
        <section class="px-4 md:px-8 lg:px-16 py-6 bg-gray-50">
            <h2 class="text-xl md:text-2xl font-bold mb-4">Cosa dicono i clienti 🗣️</h2>
            <div class="space-y-4 md:grid md:grid-cols-3 md:gap-6 md:space-y-0">
                @foreach ([['name' => 'Luca R.', 'text' => 'Servizio rapido e prodotti top, come da Slow Burn Society!'], ['name' => 'Giulia S.', 'text' => 'Ho trovato tutto per le mie sigarette rollate, super!'], ['name' => 'Franco P.', 'text' => 'Comodo, veloce, economico. Consigliato!']] as $review)
                    <div class="bg-white p-4 rounded shadow">
                        <p class="text-sm italic">“{{ $review['text'] }}”</p>
                        <p class="text-xs text-right text-gray-500 mt-2">— {{ $review['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Chi siamo -->
        <section class="px-4 md:px-8 lg:px-16 py-6 text-center bg-[#f5e8d2]">
            <h2 class="text-xl md:text-2xl font-bold mb-2">🧡 Slow Burn Society: il tuo tabacchino di fiducia online</h2>
            <p class="text-sm text-gray-700 max-w-xl mx-auto">
                Siamo un piccolo team italiano appassionato di articoli da fumo. Consegniamo a casa tua tutto ciò che
                troveresti sotto casa — ma senza uscire!
            </p>
        </section>

        <!-- CTA -->
        <section class="px-4 md:px-16 py-8 text-center bg-[#45752c] text-white">
            <h2 class="text-2xl font-bold mb-2">Ordina ora da Slow Burn Society 🚚</h2>
            <p class="text-sm mb-4">Hai bisogno di qualcosa di urgente? Spedizioni rapide in tutta Italia.</p>
            <a href="/shop"
                class="inline-block bg-white text-[#45752c] px-6 py-2 rounded font-semibold shadow hover:bg-gray-100">
                Vai allo shop
            </a>
        </section>

    </div>
@endsection
