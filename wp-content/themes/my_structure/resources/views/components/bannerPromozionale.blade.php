@php
    /** @var $dataHero Core\Bases\BaseGroupAcf\OpzioniGlobaliFields */
    // Fallback copy se ACF vuoto
    $promoTitle = trim($dataHero->titolo_banner ?? '') ?: 'Bundle pronti, prezzo speciale';
    $promoSubtitle =
        trim(strip_tags($dataHero->sottotitolo_banner ?? '')) ?:
        'Selezione curata di accessori: risparmia con i kit consigliati o componi il tuo.';
    $ctaUrl = $dataHero->link_banner['url'] ?? '#kits';
    $ctaText = $dataHero->link_banner['title'] ?? 'Scopri i Kit';
@endphp

<section id="promo"
    class="relative w-screen left-1/2 -translate-x-1/2 text-center overflow-hidden
         aspect-[16/7] md:h-[42vh] lg:h-[48vh]"
    aria-labelledby="promo-title" role="region">
    <!-- Sfondo come immagine (non LCP → lazy) -->
    @if (!empty($dataHero->sfondo_banner['url']))
        <img src="{{ $dataHero->sfondo_banner['url'] }}" alt="" aria-hidden="true"
            class="absolute inset-0 w-full h-full object-cover z-0" loading="lazy" decoding="async" width="1920"
            height="840" sizes="100vw" />
    @endif

    <!-- Overlay per contrasto -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/40 to-black/20 z-10"></div>

    <!-- Contenuto -->
    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center px-6 md:px-12 lg:px-16">
        <h2 id="promo-title"
            class="text-white/95 drop-shadow font-extrabold tracking-tight
                               text-[clamp(1.5rem,4vw,2rem)]">
            {{-- se vuoi forzare da ACF, resta tutto uguale --}}
            {{ $promoTitle }}
        </h2>

        <p class="mt-2 text-white/90 text-sm md:text-base max-w-[60ch]">
            {{ $promoSubtitle }}
        </p>

        <!-- USP / trust boosters -->
        <ul class="mt-3 flex flex-wrap gap-2 text-[12px] md:text-[13px] text-white/90" aria-label="Vantaggi principali">
            <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Spedizione 24/48h*</li>
            <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Reso 30 giorni</li>
            <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Pagamenti sicuri</li>
        </ul>

        <!-- CTA primaria + secondaria -->
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ $ctaUrl }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg
                bg-[#45752c] text-white font-semibold shadow hover:bg-[#386322]
                focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                aria-label="Vai a: {{ $ctaText }}">
                {{ $ctaText }}
            </a>

            <a href="#shop"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg
                bg-white/90 text-gray-900 font-semibold shadow hover:bg-white
                focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                aria-label="Componi il tuo kit">
                Componi il tuo kit
            </a>
        </div>

        <!-- Riga info in basso -->
        <p class="mt-4 text-xs md:text-sm text-white/95 drop-shadow font-medium">
            Pss… in ogni pacco sticker e, a volte, un piccolo regalo 🎁
            <span class="sr-only">Promozione soggetta a disponibilità.</span>
        </p>
    </div>
</section>
