@extends('layouts.mainLayout')

{{-- Attiva lo sfondo trippy --}}
@section('trippy')@endsection

@section('title', 'Chi siamo • Slow Burn Society')
@section('meta_description', 'Slow Burn Society: la cultura del fumo lento. Scopri la nostra storia, i valori e la visione per un e-commerce di accessori di qualità.')

@push('head')
  <meta name="robots" content="index, follow">
@endpush

@section('content')
<section class="relative py-12 md:py-16">
  {{-- HERO --}}
  <div class="mx-auto max-w-[92vw] md:max-w-[58ch] text-center">
    <div class="prose prose-base md:prose-lg prose-amber dark:prose-invert max-w-none">
      <p class="!mb-2 inline-flex items-center gap-2 rounded-full glass px-3 py-1 text-sm">
        <i class="material-symbols-rounded text-base align-[-2px]">local_fire_department</i>
        <strong class="tracking-wide">Chi siamo</strong>
      </p>
      <h1 class="!mt-3 !mb-2 text-3xl md:text-5xl font-extrabold leading-tight">
        Slow Burn Society
      </h1>
      <p class="!mt-0 opacity-90">
        Fumare lentamente, godersi il momento, scegliere con cura gli accessori. Questa è la nostra cultura.
      </p>
    </div>
  </div>

  {{-- STORY --}}
  <article class="mx-auto max-w-[92vw] md:max-w-[58ch] mt-10">
    <div class="prose prose-base md:prose-lg prose-amber dark:prose-invert max-w-none">
      <h2>La nostra storia</h2>
      <p>
        Mi chiamo <strong>Marian</strong>. Fumo da <strong>5 anni</strong> e, in questo tempo, ho imparato
        cosa significa <em>fumare lentamente</em>: rallentare, rispettare il rito, gustare ogni tiro.
      </p>
      <p>
        Ho <strong>26 anni</strong> e la mia passione è costruire un <strong>e-commerce di accessori per tabacchi</strong>
        firmati <strong>Slow Burn Society</strong>: accendini, filtri, cartine e tool pensati per chi ama la qualità
        e il vibe del fumo slow.
      </p>
      <p>
        Non è solo shop: è una community che condivide cultura, consigli e cura del dettaglio. Meno hype, più sostanza.
      </p>
    </div>
  </article>

  {{-- VALUES (cards in glass) --}}
  <div class="mx-auto max-w-[92vw] md:max-w-[110ch] mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="glass rounded-3xl p-6">
      <div class="flex items-center gap-2">
        <i class="material-symbols-rounded">hourglass_empty</i>
        <h3 class="text-lg font-bold">Lentezza consapevole</h3>
      </div>
      <p class="mt-2 opacity-90 text-sm">
        Prenditi il tuo tempo. Il rito viene prima del consumo. Ogni sessione è un’esperienza.
      </p>
    </div>

    <div class="glass rounded-3xl p-6">
      <div class="flex items-center gap-2">
        <i class="material-symbols-rounded">trophy</i>
        <h3 class="text-lg font-bold">Qualità prima di tutto</h3>
      </div>
      <p class="mt-2 opacity-90 text-sm">
        Accessori selezionati, materiali curati, design funzionale. Niente fronzoli, solo roba valida.
      </p>
    </div>

    <div class="glass rounded-3xl p-6">
      <div class="flex items-center gap-2">
        <i class="material-symbols-rounded">groups</i>
        <h3 class="text-lg font-bold">Community & cultura</h3>
      </div>
      <p class="mt-2 opacity-90 text-sm">
        Guide, articoli e consigli pratici per chi vuole migliorare il proprio rituale slow burn.
      </p>
    </div>
  </div>

  {{-- TIMELINE SEMPLICE --}}
  <div class="mx-auto max-w-[92vw] md:max-w-[72ch] mt-12">
    <div class="glass rounded-3xl p-6 md:p-8">
      <h2 class="text-xl md:text-2xl font-bold">La roadmap</h2>
      <ol class="mt-4 space-y-4">
        <li class="flex items-start gap-3">
          <i class="material-symbols-rounded mt-[2px]">flag</i>
          <div>
            <div class="font-semibold">Ricerca & selezione prodotti</div>
            <p class="opacity-90 text-sm">Catalogo essenziale di accessori davvero utili, testati in prima persona.</p>
          </div>
        </li>
        <li class="flex items-start gap-3">
          <i class="material-symbols-rounded mt-[2px]">storefront</i>
          <div>
            <div class="font-semibold">E-commerce Slow Burn Society</div>
            <p class="opacity-90 text-sm">Shop chiaro, mobile-first, checkout semplice e veloce.</p>
          </div>
        </li>
        <li class="flex items-start gap-3">
          <i class="material-symbols-rounded mt-[2px]">auto_stories</i>
          <div>
            <div class="font-semibold">Blog & guide</div>
            <p class="opacity-90 text-sm">Contenuti pratici: manutenzione, scelta degli accessori, rituali slow.</p>
          </div>
        </li>
      </ol>
    </div>
  </div>

  {{-- CONTATTO VELOCE --}}
    <div class="mx-auto max-w-[92vw] md:max-w-[72ch] mt-16 md:mt-24">
      <div class="rounded-3xl border border-white/10 bg-white/5 p-6 md:p-8 backdrop-blur-md">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div>
            <h3 class="text-xl md:text-2xl font-bold">Hai bisogno di una mano?</h3>
            <p class="opacity-90 mt-1">Scrivici o chiamaci, ti rispondiamo in poco tempo.</p>

            <ul class="mt-4 space-y-2 text-sm">
              <li class="flex items-center gap-2">
                <i class="material-symbols-rounded text-base">call</i>
                <a href="tel:+393298579699" class="underline underline-offset-4">+39 329 857 9699</a>
              </li>
              <li class="flex items-center gap-2">
                <i class="material-symbols-rounded text-base">alternate_email</i>
                <a href="mailto:h.marian914@gmail.com" class="underline underline-offset-4">h.marian914@gmail.com</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
</section>

{{-- JSON-LD Organization --}}
<script type="application/ld+json">
{!! json_encode([
  "@context" => "https://schema.org",
  "@type" => "Organization",
  "name" => "Slow Burn Society",
  "url" => home_url('/'),
  "brand" => "Slow Burn Society",
  "founder" => "Marian",
  "sameAs" => [],
  "description" => "Slow Burn Society: la cultura del fumo lento e accessori selezionati."
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection
