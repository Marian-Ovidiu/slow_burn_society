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
                'products' => $products,
            ])
        @endif

        @if ($dataHero)
            @include('components.bannerPromozionale', [
                'dataHero' => $dataHero,
            ])
        @endif

        @if ($latest)
            @include('components.kitSection', [
                'latest' => $latest,
            ])
        @endif

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
