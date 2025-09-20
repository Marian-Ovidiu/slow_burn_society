@php
    // Fallback sicuri
    $heroImg = $dataHero->immagine_hero['url'] ?? '';
    $heroTitle = trim($dataHero->titolo ?? '') ?: 'Accessori selezionati, esperienza migliore';
    $heroSub =
        trim($dataHero->sottotitolo ?? '') ?:
        'Kit consigliati e prodotti singoli: qualunque sia il tuo rituale, qui trovi qualità e praticità.';
    $ctaUrl = $dataHero->cta['url'] ?? '#shop-title';
    $ctaText = $dataHero->cta['title'] ?? 'Scopri lo shop';
@endphp

<section class="relative w-screen left-1/2 -translate-x-1/2 overflow-hidden
         h-[56vh] md:h-[62vh] lg:h-[68vh]"
    aria-labelledby="hero-title" role="banner">
    <!-- LCP image: <picture> + fetchpriority alto, dimensioni dichiarate -->
    @if ($heroImg)
        <picture>
            {{-- Se usi ACF per una versione WebP, aggiungi uno <source> dedicato --}}
            <source srcset="{{ $heroImg }}" type="image/webp" />
            <img src="{{ $heroImg }}" alt=""
                class="absolute inset-0 w-full h-full object-cover object-center z-0" width="1920" height="1080"
                sizes="100vw" fetchpriority="high" decoding="async" aria-hidden="true" />
        </picture>
    @endif

    <!-- Overlay per contrasto e leggibilità -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/35 to-black/25 z-10"></div>

    <!-- Contenuto -->
    <div class="absolute inset-0 z-20 flex items-center justify-center px-4 md:px-8 lg:px-12">
        <div class="text-center text-white max-w-3xl mx-auto">
            <h1 id="hero-title"
                class="font-extrabold tracking-tight drop-shadow
                 text-[clamp(1.8rem,4.5vw,3rem)] text-white leading-tight">
                {!! $heroTitle !!}
            </h1>

            @if ($heroSub)
                <p class="mt-3 md:mt-4 text-base md:text-lg text-white/95 drop-shadow-sm">
                    {!! $heroSub !!}
                </p>
            @endif

            <!-- CTA primaria + secondaria (chiaro percorso: acquista OR componi) -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $ctaUrl }}"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-lg
                  bg-[#45752c] text-white font-semibold shadow hover:bg-[#386322]
                  focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                    aria-label="Vai a: {{ strip_tags($ctaText) }}">
                    {{ $ctaText }}
                </a>

                <a href="#shop-title"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-lg
                  bg-white/95 text-gray-900 font-semibold shadow hover:bg-white
                  focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                    aria-label="Componi il tuo kit">
                    Componi il tuo kit
                </a>
            </div>

            <!-- Micro-USP opzionali (non pesanti per LCP) -->
            <ul class="mt-4 flex flex-wrap gap-2 justify-center text-[12px] md:text-[13px] text-white/90">
                <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Spedizione 24/48h*</li>
                <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Reso 30 giorni</li>
                <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Pagamenti sicuri</li>
            </ul>

            <span class="sr-only">Le condizioni di spedizione e reso possono variare in base al carrello.</span>
        </div>
    </div>
</section>
