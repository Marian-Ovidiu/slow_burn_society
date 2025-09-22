{{-- Full-bleed senza hack di margini --}}
<section id="chi-siamo"
    class="relative w-screen left-1/2 -translate-x-1/2 overflow-hidden
         px-4 md:px-8 lg:px-16 py-12 text-center text-white"
    aria-labelledby="chi-siamo-title" role="region" itemscope itemtype="https://schema.org/Organization">
    <meta itemprop="name" content="Slow Burn Society" />

    {{-- Sfondo: <img> (non LCP → lazy), dimensioni dichiarate per evitare CLS --}}
    <img src="https://slow-burn-society.shop/wp-content/uploads/2025/06/Sigaretta_accesa_nella_notte_con_luci_neon_blu_sullo_sfondo.jpg"
        alt="" class="absolute inset-0 w-full h-full object-cover z-0" loading="lazy" decoding="async"
        width="1920" height="1080" sizes="100vw" aria-hidden="true" />

    {{-- Overlay per contrasto --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-transparent z-10"></div>

    {{-- Contenuto --}}
    <div class="relative z-20 max-w-2xl mx-auto">
        <h2 id="chi-siamo-title" class="text-xl md:text-2xl font-extrabold mb-2 drop-shadow-lg">
            🧡 Slow Burn Society: un progetto indipendente, fatto con passione
        </h2>

        <p class="text-sm md:text-base text-white/95 drop-shadow max-w-xl mx-auto" itemprop="description">
            Mi chiamo <span itemprop="founder" itemscope itemtype="https://schema.org/Person">
                <span itemprop="name">Mariano</span>
            </span>, vivo a Torino e ho creato Slow Burn Society:
            una selezione di accessori curati e utili, con attenzione a qualità ed esperienza.
            Ogni giorno ci metto tutto me stesso per offrire qualcosa di onesto e fatto bene.
            <br />
            Se noti un bug o qualcosa che non funziona, fai uno screenshot e scrivimi a
            <a href="mailto:h.marian914@gmail.com"
                class="underline underline-offset-2 decoration-white/70 hover:decoration-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                aria-label="Invia una email a Mariano all'indirizzo h.marian914@gmail.com">
                h.marian914@gmail.com
            </a>.
            Grazie di cuore! 🙏
            <br />
        </p>

        {{-- Micro-usp opzionali (leggeri) --}}
        <ul class="mt-4 flex flex-wrap gap-2 justify-center text-[12px] md:text-[13px] text-white/90">
            <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Piccolo brand indipendente</li>
            <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Assistenza diretta</li>
            <li class="px-2 py-1 rounded-full bg-white/10 backdrop-blur">Feedback benvenuti</li>
        </ul>
    </div>
</section>
