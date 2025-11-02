@extends('layouts.mainLayout')

{{-- Abilita sfondo trippy --}}
@section('trippy')@endsection

@section('title', 'Work in progress • Slow Burn Society')
@section('meta_description', 'La pagina è in costruzione, ma nel frattempo esplora gli ultimi articoli dal nostro blog.')

@push('head')
  <meta name="robots" content="noindex, follow">
@endpush

@section('content')
@php
  // Helper cover (usa il tuo se già definito altrove)
  if (!function_exists('sbs_post_cover')) {
      function sbs_post_cover($id, $size = 'large')
      {
          $img = get_the_post_thumbnail_url($id, $size);
          if (!$img) {
              $html = get_post_field('post_content', $id);
              if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $html, $m)) {
                  $img = $m[1];
              }
          }
          if (!$img && function_exists('get_field')) {
              $ov = get_field('cover_image_override', $id);
              if (is_array($ov) && !empty($ov['url'])) {
                  $img = $ov['url'];
              }
          }
          return $img ?: theme_asset('assets/images/placeholder-16x9.jpg');
      }
  }

  $posts = get_posts([
      'post_type'      => 'post',
      'posts_per_page' => 6,
      'post_status'    => 'publish',
      'orderby'        => 'date',
      'order'          => 'DESC',
  ]);
@endphp

<section class="relative py-8 md:py-12">
  {{-- HERO (mobile-first) --}}
  <div class="w-full mx-auto max-w-[92vw] md:max-w-[58ch] px-4">
    <div class="prose prose-base md:prose-lg prose-amber dark:prose-invert max-w-none text-center">
      <p class="!mb-2 inline-flex items-center gap-2 rounded-full glass px-3 py-1 text-sm">
        <i class="material-symbols-rounded text-base align-[-2px]">construction</i>
        <strong class="tracking-wide">Work in progress</strong>
      </p>
      <h1 class="!mt-2 !mb-2 text-2xl md:text-5xl font-extrabold leading-tight">
        Stiamo sistemando gli ultimi dettagli ✨
      </h1>
      <p class="!mt-0 opacity-90 leading-relaxed">
        Questa pagina è quasi pronta. Nel frattempo, dai un’occhiata al nostro blog:
        troverai guide, curiosità e consigli firmati Slow Burn Society.
      </p>
    </div>
  </div>

  {{-- ARTICOLI CONSIGLIATI (mobile: 1 colonna, poi 2/3) --}}
  <div class="mt-10 md:mt-16">
    <div class="w-full mx-auto max-w-[92vw] md:max-w-[72ch] px-4">
      <div class="flex items-end justify-between gap-3 mb-4 md:mb-6">
        <h2 class="text-xl md:text-3xl font-bold">Articoli che potrebbero piacerti</h2>
      </div>
    </div>

    @if (!empty($posts))
      <div class="w-full mx-auto max-w-[92vw] md:max-w-[110ch] px-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
        @foreach ($posts as $post)
          @php
            $permalink = get_permalink($post);
            $title     = get_the_title($post);
            $thumb     = sbs_post_cover($post->ID, 'sbs-card');
            $date      = get_the_date(get_option('date_format'), $post);
            $excerpt   = wp_strip_all_tags(
              wp_trim_words(get_the_excerpt($post) ?: strip_shortcodes($post->post_content), 18)
            );
          @endphp

          <article class="group relative overflow-hidden rounded-2xl md:rounded-3xl glass hover:brightness-110 transition">
            <a href="{{ esc_url($permalink) }}" class="absolute inset-0" aria-label="Leggi: {{ esc_attr($title) }}"></a>

            <div class="aspect-[1/1] overflow-hidden">
              <img
                src="{{ esc_url($thumb) }}"
                alt=""
                loading="lazy"
                decoding="async"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            </div>

            <div class="p-4 md:p-6">
              <div class="flex flex-wrap items-center gap-2 text-xs opacity-90">
                <time datetime="{{ esc_attr(get_the_date('c', $post)) }}">{{ esc_html($date) }}</time>
              </div>

              <h3 class="mt-2 md:mt-3 text-base md:text-xl font-bold leading-snug group-hover:underline underline-offset-4">
                {{ esc_html($title) }}
              </h3>

              <p class="mt-1 md:mt-2 text-sm opacity-90 leading-relaxed">{{ esc_html($excerpt) }}</p>

              <div class="mt-3 md:mt-4 inline-flex items-center gap-1 text-sm font-semibold">
                Leggi l'articolo
                <i class="material-symbols-rounded text-base">trending_flat</i>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    @else
      <div class="w-full mx-auto max-w-[92vw] md:max-w-[58ch] px-4 mt-8 md:mt-10 text-center opacity-90">
        Al momento non ci sono articoli disponibili.
        <a class="underline underline-offset-4" href="{{ esc_url(home_url('/')) }}">Torna alla home</a>.
      </div>
    @endif
  </div>

  {{-- CONTATTO VELOCE (mobile: colonna + bottoni full width) --}}
  <div class="w-full mx-auto max-w-[92vw] md:max-w-[72ch] px-4 mt-14 md:mt-20">
    <div class="rounded-2xl md:rounded-3xl glass p-5 md:p-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <div>
          <h3 class="text-lg md:text-2xl font-bold">Domande o richieste?</h3>
          <p class="opacity-90 mt-1 text-sm md:text-base leading-relaxed">
            Scrivici pure: ti rispondiamo in tempi rapidi.
          </p>
        </div>

        <div class="flex flex-col sm:flex-row w-full md:w-auto gap-3">
          <a href="{{ esc_url(home_url('/contatti')) }}"
             class="inline-flex items-center justify-center gap-2 rounded-xl md:rounded-2xl bg-white text-gray-900 px-4 py-3 md:px-5 md:py-3 text-base font-semibold shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-white/30 min-h-[44px] w-full md:w-auto">
            <i class="material-symbols-rounded text-xl">mail</i>
            Contattaci
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Signature piccola --}}
  <div class="mt-10 text-center opacity-80 text-xs md:text-sm px-4">
    <p>Stay slow. Stay chill. 🌀</p>
  </div>
</section>
@endsection
