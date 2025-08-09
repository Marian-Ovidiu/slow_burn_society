@php
    /** @var $dataHero Core\Bases\BaseGroupAcf\OpzioniGlobaliFields */
@endphp

<section class="relative w-screen h-[40vh] left-1/2 -translate-x-1/2 text-center overflow-hidden"
    aria-labelledby="promo-title">
    <!-- Sfondo -->
    <div class="absolute inset-0 bg-cover bg-center z-0"
        style="background-image:url({{ $dataHero->sfondo_banner['url'] }});" aria-hidden="true"></div>

    <!-- Overlay per contrasto -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-black/30 z-10"></div>

    <!-- Contenuto -->
    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center px-6 md:px-12 lg:px-16">
        <h2 id="promo-title" class="banner-title text-white/95 drop-shadow">
            {{ $dataHero->titolo_banner }}
        </h2>

        @if ($dataHero->sottotitolo_banner)
            <p class="banner-subtitle">
                {!! str_replace('<p>', '<p style="color: white;">', $dataHero->sottotitolo_banner) !!}
            </p>
        @endif

        @if (!empty($dataHero->link_banner['url']) && !empty($dataHero->link_banner['title']))
            <a href="{{ $dataHero->link_banner['url'] }}" class="banner-cta"
                aria-label="Vai a: {{ $dataHero->link_banner['title'] }}">
                {{ $dataHero->link_banner['title'] }}
            </a>
        @endif
    </div>

    <!-- Riga info in basso -->
    <div class="absolute bottom-4 w-full text-center z-30 px-4">
        <p class="text-xs md:text-sm text-white/95 drop-shadow font-medium">
            Pss... in ogni pacco tanti sticker e forse un regalino 🎁
        </p>
    </div>
</section>
