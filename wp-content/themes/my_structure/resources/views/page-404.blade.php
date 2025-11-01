{{-- resources/views/errors/404.blade.php (o dove preferisci) --}}
@extends('layouts.mainLayout')

{{-- Sfondo trippy ON --}}
@section('trippy')@endsection

@section('title', '404 • Slow Burn Society')
@section('meta_description', 'Pagina non trovata — Slow Burn Society')

@push('head')
  <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
@php
  // Post recenti per suggerimenti
  $recent = get_posts([
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);

  // Helper cover (fallback robusto)
  if (!function_exists('sbs_post_cover')) {
    function sbs_post_cover($id, $size = 'sbs-card') {
        $img = get_the_post_thumbnail_url($id, $size);
        if (!$img) {
            $html = get_post_field('post_content', $id);
            if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $html, $m)) $img = $m[1];
        }
        if (!$img && function_exists('get_field')) {
            $ov = get_field('cover_image_override', $id);
            if (is_array($ov) && !empty($ov['url'])) $img = $ov['url'];
        }
        return $img ?: theme_asset('assets/images/placeholder-16x9.jpg');
    }
  }
@endphp

<section class="relative py-10 md:py-16">
  {{-- HERO 404 --}}
  <div class="w-full mx-auto max-w-[92vw] md:max-w-[70ch] px-4 text-center">
    <div class="prose prose-base md:prose-lg prose-amber dark:prose-invert max-w-none">
      <p class="!mb-3 inline-flex items-center gap-2 rounded-full glass px-3 py-1 text-sm">
        <i class="material-symbols-rounded text-base align-[-2px]">sentiment_dissatisfied</i>
        <strong class="tracking-wide">Ops! Qualcosa è andato in fumo</strong>
      </p>

      <h1 class="!my-2 text-6xl md:text-8xl font-black leading-none tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 via-cyan-300 to-amber-300">
        404
      </h1>

      <p class="!mt-2 opacity-90">
        La pagina che cerchi non esiste (più). Ma tranquillo:  
        facciamo un tiro profondo e ti rimettiamo sulla strada giusta. 🌀
      </p>
    </div>

    {{-- CTA primarie --}}
    <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
      <a href="{{ esc_url(home_url('/')) }}"
         class="inline-flex items-center justify-center gap-2 rounded-2xl glass px-5 py-3 font-semibold hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-white/30 min-h-[44px]">
        <i class="material-symbols-rounded text-xl">home</i>
        Torna alla home
      </a>
      <a href="{{ esc_url(home_url('/contatti')) }}"
         class="inline-flex items-center justify-center gap-2 rounded-2xl glass px-5 py-3 font-semibold hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-white/30 min-h-[44px]">
        <i class="material-symbols-rounded text-xl">forum</i>
        Contattaci
      </a>
    </div>

  
  </div>

  {{-- LINK UTILI --}}
  <div class="w-full mx-auto max-w-[92vw] md:max-w-[72ch] px-4 mt-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <a href="{{ esc_url(home_url('/chi-siamo')) }}"
         class="glass rounded-2xl p-4 hover:brightness-110 transition inline-flex items-center justify-between">
        <span class="font-semibold">Conosci Slow Burn Society</span>
        <i class="material-symbols-rounded">local_fire_department</i>
      </a>
    </div>
  </div>

  {{-- CONTATTO DIRETTO --}}
  <div class="w-full mx-auto max-w-[92vw] md:max-w-[72ch] px-4 mt-12 md:mt-16">
    <div class="rounded-2xl md:rounded-3xl glass p-5 md:p-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <div>
          <h3 class="text-lg md:text-2xl font-bold">Ti serve una mano?</h3>
          <p class="opacity-90 mt-1 text-sm md:text-base leading-relaxed">Scrivici o chiamaci, rispondiamo in fretta.</p>
          <ul class="mt-3 space-y-2 text-sm">
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
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
          <a href="{{ esc_url(home_url('/contatti')) }}"
             class="inline-flex items-center justify-center gap-2 rounded-xl md:rounded-2xl bg-white text-gray-900 px-4 py-3 font-semibold shadow hover:shadow-md min-h-[44px]">
            <i class="material-symbols-rounded text-xl">mail</i> Pagina contatti
          </a>
          <a href="{{ esc_url(home_url('/')) }}"
             class="inline-flex items-center justify-center gap-2 rounded-xl md:rounded-2xl glass px-4 py-3 font-semibold hover:brightness-110 min-h-[44px]">
            <i class="material-symbols-rounded text-xl">home</i> Home
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Firma --}}
  <div class="mt-10 text-center opacity-80 text-xs md:text-sm px-4">
    <p>Stay slow. Stay chill. 🌀</p>
  </div>
</section>
@endsection
