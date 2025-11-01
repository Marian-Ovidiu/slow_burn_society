@extends('layouts.mainLayout')

@php
  $postId = get_the_ID();
  $acf = function_exists('get_field');
  $trippy = $acf ? (bool) get_field('trippy_enabled', $postId) : true;

  if (!function_exists('sbs_post_cover')) {
      function sbs_post_cover($id, $size = 'sbs-card') {
          $img = get_the_post_thumbnail_url($id, $size);
          if (!$img) {
              $html = get_post_field('post_content', $id);
              if (preg_match('/<img[^>]+src=["\']([^"\']+)/i', $html, $m)) $img = $m[1];
          }
          if (function_exists('get_field') && !$img) {
              $ov = get_field('cover_image_override', $id);
              if (is_array($ov) && !empty($ov['url'])) $img = $ov['url'];
          }
          return $img ?: theme_asset('assets/images/placeholder-16x9.jpg');
      }
  }

  $readingTime = function_exists('sbs_reading_time_minutes') ? sbs_reading_time_minutes($postId) : max(1, ceil(str_word_count(strip_tags(get_the_content(null, false, $postId))) / 220));

  $seoDesc = get_the_excerpt($postId) ?: (function(){
      $content = wp_strip_all_tags(get_post_field('post_content', get_the_ID()));
      $content = preg_replace('/\s+/', ' ', $content);
      return mb_substr($content, 0, 160);
  })();

  $faq      = function_exists('sbs_faq_from_blocks') ? sbs_faq_from_blocks($postId) : [];
  $products = function_exists('sbs_products_from_blocks') ? sbs_products_from_blocks($postId) : [];

  $cover = sbs_post_cover($postId, 'large');
  $title = get_the_title($postId);
  $dateISO = get_post_time('c', true, $postId);
  $dateHuman = get_the_date('', $postId);
  $authorId = get_post_field('post_author', $postId);
  $author   = get_the_author_meta('display_name', $authorId) ?: 'Slow Burn Society';

  $tags = get_the_tags($postId) ?: [];

  // Share data
  $permalinkRaw = get_permalink($postId);
  $permalinkEnc = urlencode($permalinkRaw);
  $shareText    = $title . ' — ' . wp_strip_all_tags($seoDesc);
  $shareTextEnc = urlencode($shareText);
@endphp

@if($trippy) @section('trippy')@endsection @endif

@section('title', $title)
@section('meta_description', wp_strip_all_tags($seoDesc))

@push('head')
  <meta name="robots" content="index, follow">
@endpush

@section('content')
<article class="w-full relative py-8 md:py-12">

  {{-- HEADER --}}
  <header class="w-full mx-auto max-w-[92vw] md:max-w-[70ch] px-4">
    <div class="mt-4 md:mt-6 prose prose-amber dark:prose-invert max-w-none">
      <h1 class="!mt-2 !mb-2 text-2xl md:text-4xl font-extrabold leading-tight">{{ $title }}</h1>
      <div class="not-prose flex flex-wrap items-center gap-2 text-xs md:text-sm opacity-90">
        <span class="inline-flex items-center gap-1 rounded-full glass px-2 py-0.5">
          <i class="material-symbols-rounded text-sm">person</i>{{ esc_html($author) }}
        </span>
        <time class="inline-flex items-center gap-1 rounded-full glass px-2 py-0.5" datetime="{{ esc_attr($dateISO) }}">
          <i class="material-symbols-rounded text-sm">today</i>{{ esc_html($dateHuman) }}
        </time>
        <span class="inline-flex items-center gap-1 rounded-full glass px-2 py-0.5">
          <i class="material-symbols-rounded text-sm">schedule</i>{{ (int)$readingTime }} min
        </span>
      </div>
    </div>
  </header>

  {{-- CONTENT --}}
  <div class="w-full mx-auto max-w-[92vw] md:max-w-[58ch] px-4 mt-6">
    <div class="prose prose-base md:prose-lg prose-amber dark:prose-invert max-w-none trippy-content">
      {!! apply_filters('the_content', get_the_content(null, false, $postId)) !!}
    </div>
  </div>

  {{-- PRODUCTS --}}
  @if (!empty($products))
    <section class="w-full mx-auto max-w-[92vw] md:max-w-[58ch] px-4 mt-10">
      <h2 class="text-lg md:text-2xl font-bold mb-3">Prodotti consigliati</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ($products as $p)
          <a href="{{ esc_url($p['url']) }}" target="_blank" rel="nofollow noopener"
             class="glass rounded-2xl p-4 flex items-center gap-4 hover:brightness-110 transition">
            @if (!empty($p['image']))
              <img src="{{ esc_url($p['image']) }}" alt="{{ esc_attr($p['name'] ?: 'Prodotto') }}"
                   class="w-16 h-16 object-cover rounded-xl" loading="lazy" decoding="async">
            @endif
            <div class="flex-1">
              <div class="font-semibold text-sm md:text-base">{{ $p['name'] ?: 'Prodotto' }}</div>
              <div class="text-xs md:text-sm opacity-80">Consigliato da Slow Burn</div>
            </div>
            <i class="material-symbols-rounded">open_in_new</i>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  {{-- TAGS --}}
  @if (!empty($tags))
    <div class="w-full mx-auto max-w-[92vw] md:max-w-[58ch] px-4 mt-8">
      <div class="flex flex-wrap gap-2">
        @foreach ($tags as $t)
          <a href="{{ esc_url(get_tag_link($t)) }}" class="inline-flex items-center gap-1 rounded-full glass px-3 py-1 text-xs md:text-sm">
            <i class="material-symbols-rounded text-sm">tag</i>{{ esc_html($t->name) }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- SHARE (Web Share API + fallback link + copy URL) --}}
  <div class="w-full mx-auto max-w-[92vw] md:max-w-[58ch] px-4 mt-8">
    <div class="flex flex-col sm:flex-row gap-3">
      {{-- BTN: Web Share (scegli app) --}}
      <button
        id="share-btn"
        type="button"
        class="glass rounded-xl px-4 py-3 text-sm font-semibold inline-flex items-center justify-center gap-2 hover:brightness-110"
        data-share-title="{{ esc_attr($title) }}"
        data-share-text="{{ esc_attr($shareText) }}"
        data-share-url="{{ esc_url($permalinkRaw) }}"
        aria-controls="share-fallback"
        aria-expanded="false">
        <i class="material-symbols-rounded">ios_share</i>
        Condividi
      </button>

      {{-- BTN: Copia link --}}
      <button
        id="copy-btn"
        type="button"
        class="glass rounded-xl px-4 py-3 text-sm font-semibold inline-flex items-center justify-center gap-2 hover:brightness-110"
        data-copy="{{ esc_url($permalinkRaw) }}">
        <i class="material-symbols-rounded">content_copy</i>
        Copia link
      </button>
    </div>

    {{-- Fallback menu share (mostrato solo se Web Share non disponibile) --}}
    <div id="share-fallback" class="mt-3 hidden">
      <div class="flex flex-wrap gap-2">
        <a href="https://api.whatsapp.com/send?text={{ $shareTextEnc }}%20{{ $permalinkEnc }}"
           class="glass rounded-lg px-3 py-2 text-sm inline-flex items-center gap-2 hover:brightness-110" target="_blank" rel="noopener nofollow">
          <i class="material-symbols-rounded text-base">chat</i> WhatsApp
        </a>
        <a href="https://t.me/share/url?url={{ $permalinkEnc }}&text={{ $shareTextEnc }}"
           class="glass rounded-lg px-3 py-2 text-sm inline-flex items-center gap-2 hover:brightness-110" target="_blank" rel="noopener nofollow">
          <i class="material-symbols-rounded text-base">send</i> Telegram
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $permalinkEnc }}"
           class="glass rounded-lg px-3 py-2 text-sm inline-flex items-center gap-2 hover:brightness-110" target="_blank" rel="noopener nofollow">
          <i class="material-symbols-rounded text-base">share</i> Facebook
        </a>
      </div>
    </div>
  </div>

  {{-- PREV / NEXT --}}
  @php
    $prev = get_previous_post();
    $next = get_next_post();
  @endphp
  @if($prev || $next)
    <nav class="w-full mx-auto max-w-[92vw] md:max-w-[58ch] px-4 mt-10">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if($prev)
          <a href="{{ esc_url(get_permalink($prev)) }}" class="glass rounded-2xl p-4 hover:brightness-110 transition">
            <div class="text-xs opacity-80 mb-1 inline-flex items-center gap-1">
              <i class="material-symbols-rounded text-sm">west</i> Articolo precedente
            </div>
            <div class="font-semibold">{{ esc_html(get_the_title($prev)) }}</div>
          </a>
        @endif
        @if($next)
          <a href="{{ esc_url(get_permalink($next)) }}" class="glass rounded-2xl p-4 hover:brightness-110 transition text-right sm:text-left">
            <div class="text-xs opacity-80 mb-1 inline-flex items-center gap-1 sm:justify-start justify-end">
              <span>Articolo successivo</span><i class="material-symbols-rounded text-sm">east</i>
            </div>
            <div class="font-semibold">{{ esc_html(get_the_title($next)) }}</div>
          </a>
        @endif
      </div>
    </nav>
  @endif

  {{-- RELATED --}}
  @php
    $relArgs = [
      'post_type' => 'post',
      'posts_per_page' => 3,
      'post__not_in' => [$postId],
      'ignore_sticky_posts' => true,
      'orderby' => 'date',
      'order' => 'DESC'
    ];
    $related = get_posts($relArgs);
  @endphp
  @if(!empty($related))
    <section class="w-full mx-auto max-w-[92vw] md:max-w-[110ch] px-4 mt-12">
      <h2 class="text-lg md:text-2xl font-bold mb-4">Potrebbe interessarti anche</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        @foreach($related as $rp)
          @php
            $rpTitle = get_the_title($rp);
            $rpUrl   = get_permalink($rp);
            $rpImg   = sbs_post_cover($rp->ID, 'sbs-card');
            $rpDate  = get_the_date(get_option('date_format'), $rp);
          @endphp
          <article class="group relative overflow-hidden rounded-2xl md:rounded-3xl glass hover:brightness-110 transition">
            <a href="{{ esc_url($rpUrl) }}" class="absolute inset-0" aria-label="Leggi: {{ esc_attr($rpTitle) }}"></a>
            <div class="aspect-[16/9] overflow-hidden">
              <img src="{{ esc_url($rpImg) }}" alt="" loading="lazy" decoding="async"
                   class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
            </div>
            <div class="p-4 md:p-5">
              <div class="text-xs opacity-80">{{ esc_html($rpDate) }}</div>
              <h3 class="mt-1 text-base md:text-lg font-bold leading-snug group-hover:underline underline-offset-4">
                {{ esc_html($rpTitle) }}
              </h3>
            </div>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  {{-- JSON-LD --}}
  <script type="application/ld+json">
    {!! json_encode([
      "@context" => "https://schema.org",
      "@type" => "Article",
      "headline" => $title,
      "author" => ["@type" => "Person", "name" => $author],
      "datePublished" => get_post_time('c', true, $postId),
      "dateModified" => get_post_modified_time('c', true, $postId),
      "image" => [$cover],
      "mainEntityOfPage" => get_permalink($postId),
      "publisher" => [
        "@type" => "Organization",
        "name"  => "Slow Burn Society",
        "logo"  => [
          "@type" => "ImageObject",
          "url"   => theme_asset('assets/images/social-cover.jpg')
        ]
      ],
      "keywords" => implode(', ', array_map(fn($t)=> $t->name, $tags ?: []))
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
  </script>

  {{-- CTA CONTATTI --}}
  <section class="w-full mx-auto max-w-[92vw] md:max-w-[72ch] px-4 mt-12 md:mt-16">
    <div class="rounded-2xl md:rounded-3xl glass p-5 md:p-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <div>
          <h3 class="text-lg md:text-2xl font-bold">Domande o richieste?</h3>
          <p class="opacity-90 mt-1 text-sm md:text-base leading-relaxed">Scrivici pure: ti rispondiamo in tempi rapidi.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
          <a href="tel:+393298579699"
             class="inline-flex items-center justify-center gap-2 rounded-xl md:rounded-2xl glass px-4 py-3 font-semibold hover:brightness-110 min-h-[44px]">
            <i class="material-symbols-rounded text-xl">phone_iphone</i> Chiama
          </a>
          <a href="mailto:h.marian914@gmail.com"
             class="inline-flex items-center justify-center gap-2 rounded-xl md:rounded-2xl glass px-4 py-3 font-semibold hover:brightness-110 min-h-[44px]">
            <i class="material-symbols-rounded text-xl">alternate_email</i> Email
          </a>
          <a href="{{ esc_url(home_url('/contatti')) }}"
             class="inline-flex items-center justify-center gap-2 rounded-xl md:rounded-2xl bg-white text-gray-900 px-4 py-3 font-semibold shadow hover:shadow-md min-h-[44px]">
            <i class="material-symbols-rounded text-xl">forum</i> Pagina contatti
          </a>
        </div>
      </div>
    </div>
  </section>

  <div class="mt-10 text-center opacity-80 text-xs md:text-sm px-4">
    <p>Stay slow. Stay chill. 🌀</p>
  </div>
</article>

{{-- Script share/copy (inline, zero dipendenze) --}}
<script>
  (function () {
    const shareBtn = document.getElementById('share-btn');
    const copyBtn  = document.getElementById('copy-btn');
    const fallback = document.getElementById('share-fallback');

    if (shareBtn) {
      const title = shareBtn.getAttribute('data-share-title') || document.title;
      const text  = shareBtn.getAttribute('data-share-text')  || '';
      const url   = shareBtn.getAttribute('data-share-url')   || window.location.href;

      // Se Web Share non è supportata, mostra il fallback
      if (!('share' in navigator)) {
        if (fallback) {
          fallback.classList.remove('hidden');
          shareBtn.setAttribute('aria-expanded', 'true');
        }
      }

      shareBtn.addEventListener('click', async () => {
        if ('share' in navigator) {
          try {
            await navigator.share({ title, text, url });
          } catch (err) {
            // utente ha annullato o errore → non fare nulla
          }
        } else if (fallback) {
          // toggle fallback
          const hidden = fallback.classList.contains('hidden');
          fallback.classList.toggle('hidden', !hidden);
          shareBtn.setAttribute('aria-expanded', hidden ? 'true' : 'false');
        }
      });
    }

    if (copyBtn) {
      const toCopy = copyBtn.getAttribute('data-copy') || window.location.href;

      copyBtn.addEventListener('click', async () => {
        const showOk = () => {
          copyBtn.innerHTML = '<i class="material-symbols-rounded">check</i> Copiato!';
          setTimeout(() => {
            copyBtn.innerHTML = '<i class="material-symbols-rounded">content_copy</i> Copia link';
          }, 1500);
        };

        try {
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(toCopy);
            showOk();
            return;
          }
        } catch (e) { /* continua su fallback */ }

        // Fallback legacy
        const ta = document.createElement('textarea');
        ta.value = toCopy;
        ta.setAttribute('readonly', '');
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        showOk();
      });
    }
  })();
</script>
@endsection
