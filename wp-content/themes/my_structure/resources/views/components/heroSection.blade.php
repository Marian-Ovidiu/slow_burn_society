<section class="relative w-screen h-[50vh] left-1/2 -translate-x-1/2" aria-label="Hero sezione introduttiva">
    <!-- Background decorativo -->
    <img src="{{ $dataHero->immagine_hero['url'] }}"
         class="absolute inset-0 w-full h-full object-cover"
         alt=""
         aria-hidden="true" />

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenuto centrato -->
    <div class="absolute inset-0 flex items-center justify-center px-4 text-white text-center">
        <div class="flex flex-col items-center">
            @if ($dataHero->titolo)
                <h1 class="text-3xl md:text-5xl font-bold drop-shadow-lg leading-tight">
                    {!! $dataHero->titolo !!}
                </h1>
            @endif

            @if ($dataHero->sottotitolo)
                <p class="mt-4 text-base md:text-lg drop-shadow max-w-2xl">
                    {!! $dataHero->sottotitolo !!}
                </p>
            @endif

            @php
                $ctaUrl = $dataHero->cta['url'] ?? '/';
                $ctaText = $dataHero->cta['title'] ?? 'Scopri lo shop';
            @endphp

            <a href="#shop"
               class="mt-6 bg-[#45752c] hover:bg-[#386322] text-white font-semibold px-6 py-3 rounded shadow transition">
                {{ $ctaText }}
            </a>
        </div>
    </div>
</section>
