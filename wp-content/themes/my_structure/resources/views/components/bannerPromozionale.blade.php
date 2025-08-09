     @php
         /***
          *  @var $dataHero[] Core\Bases\BaseGroupAcf\OpzioniGlobaliFields
          */
     @endphp
     <!-- Banner promozionale -->
<section class="relative h-[40vh] w-full text-center text-white overflow-hidden">
    <!-- Immagine di sfondo -->
    <div class="absolute inset-0 bg-cover bg-center z-0"
         style="background-image: url({{ $dataHero->sfondo_banner['url'] }});">
    </div>

    <!-- Gradient overlay per profondità -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-black/30 z-10"></div>

    <!-- Contenuto centrato -->
    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center px-6 md:px-12 lg:px-16 text-white">
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow-lg">
            {{ $dataHero->titolo_banner }}
        </h2>
        <p class="mt-3 text-base md:text-lg max-w-md drop-shadow">
            {!! $dataHero->sottotitolo_banner !!}
        </p>

        <a href="{{ $dataHero->link_banner['url'] }}"
           class="mt-6 inline-block bg-white text-[#45752c] hover:bg-gray-100 font-semibold px-6 py-2 rounded shadow transition-all duration-200">
            {{ $dataHero->link_banner['title'] }}
        </a>
    </div>

    <!-- Extra info fissa in basso -->
    <div class="absolute bottom-4 w-full text-center z-30">
        <p class="text-xs md:text-sm text-white drop-shadow font-medium">
            Pss... in ogni pacco tanti sticker e forse un regalino 🎁
        </p>
    </div>
</section>


