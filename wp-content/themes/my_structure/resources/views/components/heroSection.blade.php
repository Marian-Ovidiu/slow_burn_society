     @php
         /***
          *  @var $dataHero[] Core\Bases\BaseGroupAcf\OpzioniGlobaliFields
          */
     @endphp
     <!-- HERO -->
     <section class="relative w-full h-48 md:h-64 lg:h-80">
         
         <img src="{{$dataHero->immagine_hero['url'] }}"
             class="w-full h-full object-cover" alt="Banner accendini">

         <!-- Overlay -->
         <div class="absolute inset-0 bg-black/40"></div>
         <!-- Testo -->
         <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-4">

             @if ($dataHero->titolo)
                 <h1 class="text-2xl md:text-4xl font-bold drop-shadow-lg"> {!! $dataHero->titolo !!} </h1>
             @endif

             @if ($dataHero)
                 <p class="mt-2 text-sm md:text-base drop-shadow"> {!! $dataHero->sottotitolo !!}</p>
             @endif
             @php
                 $ctaUrl = $dataHero->cta['url'] ?? '/shop';
                 $ctaText = $dataHero->cta['title'] ?? 'Scopri lo shop';
             @endphp

             <a href="{{ $ctaUrl }}"
                 class="mt-4 bg-[#45752c] hover:bg-[#386322] text-white font-semibold px-6 py-2 rounded shadow transition">
                 {{ $ctaText }}
             </a>
         </div>
     </section>
