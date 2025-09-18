@extends('layouts.mainLayout')

@section('content')
    <div x-data="{ modalOpen: false, selected: null, modalOpen2: false, selected2: null }">

        @if ($latest)
            @include('components.kitSection', [
                'latest' => $latest,
                'kitsForJs' => $kitsForJs,
            ])
        @endif
        @if ($dataHero)
            @include('components.bannerPromozionale', [
                'dataHero' => $dataHero,
            ])
        @endif
        @if ($products)
            @include('components.cardProdottoEvidenza', [
                'subdata' => $subdata,
                'products' => $products,
            ])
        @endif

        <div class="relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-[100vw] max-w-[100vw] overflow-x-clip">
            @include('components.chiSono')
        </div>

        @include('components.cartIcon')
    @endsection
