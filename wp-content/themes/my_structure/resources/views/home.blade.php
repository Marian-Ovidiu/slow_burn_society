@extends('layouts.mainLayout')

@section('content')
    <div x-data="{ modalOpen: false, selected: null, modalOpen2: false, selected2: null }">
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

        <div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-[100vw] max-w-[100vw] overflow-x-clip">
            @include('components.chiSono')
        </div>
        <div x-data x-init="$store.cartReady = true" x-show="$store.cartReady" class="fixed bottom-4 right-4 z-[9999]">
            <a href="/checkout"
                class="relative w-14 h-14 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg">
                <span class="material-symbols-rounded text-black text-3xl">shopping_cart</span>
            </a>
        </div>


    </div>
@endsection
