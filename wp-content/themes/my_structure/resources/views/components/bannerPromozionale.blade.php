@php
  // Fallback puliti: priorità ai campi di $dataHero
  $heroTitle   = trim($dataHero->titolo_banner ?? '') ?: ($promoTitle ?? '');
  $heroSub     = trim($dataHero->sottotitolo_banner ?? '') ?: ($promoSubtitle ?? '');
  $heroBg      = $dataHero->sfondo_banner['url'] ?? '';
  $heroPill    = $promoPill ?? ''; // opzionale
@endphp

<section id="promo"
  class="relative isolate w-screen left-1/2 -translate-x-1/2 overflow-hidden text-center
         min-h-[62svh] sm:min-h-[56svh] md:min-h-[48svh]
         pt-[calc(56px+env(safe-area-inset-top))] pb-[calc(40px+env(safe-area-inset-bottom))]"
  aria-labelledby="promo-title" role="region">

  @if ($heroBg)
    <img src="{{ $heroBg }}" alt=""
         class="absolute inset-0 size-full object-cover z-0 pointer-events-none"
         loading="lazy" decoding="async" width="1920" height="840" sizes="100vw" />
  @endif

  <div class="absolute inset-0 z-10 pointer-events-none">
    <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/45 to-black/25"></div>
    <div class="absolute inset-0 [mask-image:radial-gradient(140%_100%_at_50%_0%,#000_40%,transparent_75%)]"></div>
  </div>

  @if (!empty($heroPill))
    <div class="absolute top-3 left-1/2 -translate-x-1/2 z-20">
      <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-[12px] font-semibold
                  bg-white/90 text-gray-900 shadow">
        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4"><path fill="currentColor" d="M12 2 2 7l10 5 10-5-10-5Zm0 7L2 4v13l10 5 10-5V4l-10 5Z"/></svg>
        <span>{{ $heroPill }}</span>
      </div>
    </div>
  @endif

  <div class="relative z-20 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 h-full
              flex flex-col items-center justify-center gap-3 sm:gap-4">

    @if ($heroTitle)
      <h2 id="promo-title"
          class="text-white drop-shadow-xl font-extrabold tracking-tight
                 text-[clamp(1.6rem,6.2vw,2.6rem)] leading-tight">
        {{ $heroTitle }}
      </h2>
    @endif

    @if ($heroSub)
      <p class="max-w-[65ch] text-white/95 text-[0.95rem] sm:text-base leading-relaxed">
        {!! $heroSub !!}
      </p>
    @endif

    <ul class="mt-1 flex flex-wrap items-center justify-center gap-2 text-[12px] sm:text-[13px] text-white/95"
        aria-label="Vantaggi principali">
      <li class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur">
        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4"><path fill="currentColor" d="M20 8h-3.17l-2-2H9.17l-2 2H4a2 2 0 0 0-2 2v7h2a3 3 0 1 0 6 0h6a3 3 0 1 0 6 0h2v-5a4 4 0 0 0-4-4Zm-14 11a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm12 0a1 1 0 1 1 .001-1.999A1 1 0 0 1 18 19Z"/></svg>
        Spedizione 24/48h*
      </li>
      <li class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur">
        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4"><path fill="currentColor" d="M12 2a5 5 0 0 1 5 5v2h1a2 2 0 0 1 2 2v9H4V11a2 2 0 0 1 2-2h1V7a5 5 0 0 1 5-5Zm-3 7h6V7a3 3 0 0 0-6 0v2Z"/></svg>
        Reso 30 giorni
      </li>
      <li class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur">
        <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4"><path fill="currentColor" d="M12 2 1 7l11 5 9-4.09V17h2V7L12 2Zm0 13L1 10v7l11 5 11-5v-7l-11 5Z"/></svg>
        Pagamenti sicuri
      </li>
    </ul>

    <div class="mt-3 w-full max-w-md grid grid-cols-1 sm:grid-cols-2 gap-3">
      <a href="/#kit-title"
         class="inline-flex items-center justify-center px-5 py-3 rounded-xl
                bg-[#45752c] text-white font-semibold shadow
                hover:bg-[#386322] focus:outline-none
                focus-visible:ring-2 focus-visible:ring-white/70">
        Vai allo shop
      </a>

      <a href="#shop"
         class="inline-flex items-center justify-center px-5 py-3 rounded-xl
                bg-white/95 text-gray-900 font-semibold shadow
                hover:bg-white focus:outline-none
                focus-visible:ring-2 focus-visible:ring-white/70">
        Componi il tuo kit
      </a>
    </div>

    <p class="mt-2 text-[12px] sm:text-sm text-white/95 drop-shadow font-medium">
      Pss… in ogni pacco sticker e, a volte, un piccolo regalo 🎁
      <span class="sr-only">Promozione soggetta a disponibilità.</span>
    </p>
  </div>

  <button type="button" aria-label="Scopri di più"
          class="group absolute bottom-3 left-1/2 -translate-x-1/2 z-20
                 hidden sm:inline-flex items-center gap-2 text-white/80 hover:text-white">
    <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true"><path fill="currentColor" d="M12 16 6 10h12l-6 6Z"/></svg>
    <span class="text-xs font-semibold tracking-wide">Scorri</span>
  </button>

  <style>
    @media (prefers-reduced-motion: reduce) {
      #promo * { animation: none !important; transition: none !important }
    }
  </style>
</section>
