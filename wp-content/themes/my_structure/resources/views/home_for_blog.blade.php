{{-- Home Blog --}}
@extends('layouts.mainLayout')
@section('trippy', true)

@section('title', 'Blog | Slow Burn Society')
@section('meta_description',
    'Articoli su cultura, CBD, accessori da fumo, leggi e lifestyle. Recensioni, guide pratiche
    e news.')
    {{-- facoltativo: sfondo trippy sulla home --}}
    {{-- @section('trippy', true) --}}

    @push('head')
        {{-- FAQ Schema (aggiorna le domande in base ai tuoi articoli pillar) --}}
        <script type="application/ld+json">
    {
      "@context":"https://schema.org",
      "@type":"FAQPage",
      "mainEntity":[
        {"@type":"Question","name":"CBD è legale in Italia?","acceptedAnswer":{"@type":"Answer","text":"Sì, i prodotti CBD senza THC oltre i limiti di legge sono commercializzabili. Consulta sempre le normative aggiornate."}},
        {"@type":"Question","name":"Qual è la differenza tra CBD e THC?","acceptedAnswer":{"@type":"Answer","text":"Il THC è psicoattivo, il CBD no. Il CBD è usato per benessere e relax senza sballo."}}
      ]
    }
    </script>
    @endpush

@section('content')
    <section class="py-10" x-data="blogFilters()">

        {{-- HERO + SEARCH --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight">
                    Slow Burn <span class="text-amber-600">Blog</span>
                </h1>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-300">
                    Cultura, CBD, accessori, leggi e lifestyle. Zero fuffa, solo roba utile.
                </p>
            </div>

            <div class="w-full md:w-1/2">
                <label class="sr-only" for="search">Cerca</label>
                <div class="flex gap-2">
                    <input id="search" type="search" x-model="q"
                        placeholder="Cerca articoli (es. vaporizzatori, CBD)…"
                        class="flex-1 rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white/80 dark:bg-zinc-900/60 backdrop-blur">
                    <button @click="apply()"
                        class="px-4 py-3 rounded-xl bg-amber-600 text-white font-semibold hover:bg-amber-700">
                        Cerca
                    </button>
                </div>
                <div class="mt-2 flex items-center gap-2 text-sm">
                    <select x-model="cat"
                        class="rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 bg-white/80 dark:bg-zinc-900/60">
                        <option value="">Tutte le categorie</option>
                        @foreach ($categories ?? [] as $c)
                            <option value="{{ $c->slug }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <select x-model="sort"
                        class="rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 bg-white/80 dark:bg-zinc-900/60">
                        <option value="new">Più recenti</option>
                        <option value="pop">Più letti</option>
                    </select>
                    <button @click="reset()"
                        class="ml-auto text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">Reset</button>
                </div>
            </div>
        </div>

        {{-- FEATURED --}}
        @if (!empty($featured))
            <article class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-zinc-800 mb-10">
                <div class="grid md:grid-cols-2">
                    <a href="{{ get_permalink($featured->ID) }}" class="block group">
                        @php $img = get_the_post_thumbnail_url($featured->ID, 'large') ?: theme_asset('assets/images/placeholder-16x9.jpg'); @endphp
                        <img src="{{ $img }}" alt="{{ esc_attr(get_the_title($featured->ID)) }}"
                            class="h-full w-full object-cover aspect-video md:aspect-auto group-hover:opacity-90 transition">
                    </a>
                    <div class="p-6 md:p-8">
                        <div class="text-xs uppercase tracking-wider text-amber-700 font-semibold">
                            In evidenza
                        </div>
                        <h2 class="mt-2 text-2xl md:text-3xl font-bold leading-tight">
                            <a href="{{ get_permalink($featured->ID) }}" class="hover:underline">
                                {{ get_the_title($featured->ID) }}
                            </a>
                        </h2>
                        <p class="mt-3 text-gray-600 dark:text-gray-300 line-clamp-3">
                            {{ wp_strip_all_tags(get_the_excerpt($featured->ID)) }}
                        </p>
                        <div class="mt-4 flex items-center gap-3 text-sm text-gray-500">
                            <time datetime="{{ get_post_time('c', true, $featured->ID) }}">
                                {{ get_the_date('', $featured->ID) }}
                            </time>
                            <span>•</span>
                            <span>{{ get_the_category($featured->ID)[0]->name ?? 'Articoli' }}</span>
                        </div>
                        <a href="{{ get_permalink($featured->ID) }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl bg-amber-600 text-white px-4 py-2 font-semibold hover:bg-amber-700">
                            Leggi ora <span class="material-symbols-rounded text-base">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </article>
        @endif

        <div class="grid lg:grid-cols-12 gap-8">
            {{-- MAIN LIST --}}
            <div class="lg:col-span-8">
                <div class="grid sm:grid-cols-2 gap-6">
                    @forelse(($latest ?? []) as $post)
                        <article class="rounded-2xl border border-gray-200 dark:border-zinc-800 overflow-hidden">
                            <a href="{{ get_permalink($post->ID) }}" class="block group">
                                @php $img = get_the_post_thumbnail_url($post->ID, 'medium_large') ?: theme_asset('assets/images/placeholder-4x3.jpg'); @endphp
                                <img src="{{ $img }}" alt="{{ esc_attr(get_the_title($post->ID)) }}"
                                    class="w-full aspect-video object-cover group-hover:opacity-90 transition">
                            </a>
                            <div class="p-5">
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span
                                        class="uppercase tracking-wider">{{ get_the_category($post->ID)[0]->name ?? 'Articoli' }}</span>
                                    <span>•</span>
                                    <time
                                        datetime="{{ get_post_time('c', true, $post->ID) }}">{{ get_the_date('', $post->ID) }}</time>
                                </div>
                                <h3 class="mt-2 text-xl font-bold leading-tight">
                                    <a href="{{ get_permalink($post->ID) }}" class="hover:underline">
                                        {{ get_the_title($post->ID) }}
                                    </a>
                                </h3>
                                <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-3">
                                    {{ wp_strip_all_tags(get_the_excerpt($post->ID)) }}
                                </p>
                                <a href="{{ get_permalink($post->ID) }}"
                                    class="mt-4 inline-flex items-center gap-1 text-amber-700 font-semibold">
                                    Continua <span class="material-symbols-rounded text-base">chevron_right</span>
                                </a>
                            </div>
                        </article>
                    @empty
                        <p class="text-gray-500">Nessun articolo (ancora) — scrivine uno subito bro 😎</p>
                    @endforelse
                </div>

                {{-- PAGINAZIONE SAFE --}}
                @php
                    // true se è un paginator di Laravel (LengthAwarePaginator o simili)
                    $isPaginator =
                        isset($latest) &&
                        is_object($latest) &&
                        $latest instanceof \Illuminate\Contracts\Pagination\Paginator;
                @endphp

                @if ($isPaginator)
                    <div class="mt-8">
                        {!! $latest->links('pagination::tailwind') !!}
                    </div>
                @else
                    {{-- Fallback WordPress, solo se ci sono le funzioni e la query globale --}}
                    @php global $wp_query; @endphp
                    @if (function_exists('get_next_posts_link') || function_exists('get_previous_posts_link'))
                        <div class="mt-8 flex items-center gap-2">
                            {!! get_previous_posts_link('Più recenti')
                                ? '<span class="px-3 py-2 rounded border">' . get_previous_posts_link('Più recenti') . '</span>'
                                : '' !!}
                            {!! get_next_posts_link('Articoli più vecchi', $wp_query->max_num_pages ?? 1)
                                ? '<span class="px-3 py-2 rounded border">' .
                                    get_next_posts_link('Articoli più vecchi', $wp_query->max_num_pages ?? 1) .
                                    '</span>'
                                : '' !!}
                        </div>
                    @endif
                @endif

            </div>

            {{-- SIDEBAR --}}
            <aside class="lg:col-span-4 space-y-6">
                {{-- Categorie --}}
                <div class="rounded-2xl border border-gray-200 dark:border-zinc-800 p-5">
                    <h4 class="font-bold mb-3">Categorie</h4>
                    <ul class="space-y-2">
                        @foreach ($categories ?? [] as $c)
                            <li>
                                <a href="{{ get_category_link($c->term_id) }}"
                                    class="flex items-center justify-between hover:underline">
                                    <span>{{ $c->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $c->count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Tag popolari --}}
                <div class="rounded-2xl border border-gray-200 dark:border-zinc-800 p-5">
                    <h4 class="font-bold mb-3">Tag popolari</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($popularTags ?? [] as $t)
                            <a href="{{ get_tag_link($t->term_id) }}"
                                class="px-3 py-1 rounded-full border border-gray-300 dark:border-zinc-700 text-sm hover:bg-amber-50 dark:hover:bg-zinc-800">
                                #{{ $t->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Newsletter --}}
                <div class="rounded-2xl border border-gray-200 dark:border-zinc-800 p-5">
                    <h4 class="font-bold mb-1">Iscriviti alla newsletter</h4>
                    <p class="text-sm text-gray-500 mb-4">Tips, guide e novità (zero spam, promesso).</p>
                    <form action="{{ admin_url('admin-post.php') }}" method="post" class="space-y-3">
                        {{-- endpoint admin-post --}}
                        <input type="hidden" name="action" value="sbs_newsletter_subscribe">
                        {{-- nonce WP (anti-CSRF) --}}
                        <?php wp_nonce_field('sbs_newsletter_subscribe', 'sbs_newsletter_nonce'); ?>

                        <input type="email" name="email" required placeholder="la-tua@email.it"
                            class="w-full rounded-xl border border-gray-300 dark:border-zinc-700 px-4 py-3 bg-white/80 dark:bg-zinc-900/60">
                        <button
                            class="w-full rounded-xl bg-amber-600 text-white px-4 py-3 font-semibold hover:bg-amber-700">
                            Iscrivimi
                        </button>
                    </form>

                </div>
            </aside>
        </div>
    </section>
@endsection

@push('after_alpine')
    <script>
        function blogFilters() {
            return {
                q: new URLSearchParams(location.search).get('q') || '',
                cat: new URLSearchParams(location.search).get('cat') || '',
                sort: new URLSearchParams(location.search).get('sort') || 'new',
                apply() {
                    const p = new URLSearchParams();
                    if (this.q) p.set('q', this.q);
                    if (this.cat) p.set('cat', this.cat);
                    if (this.sort && this.sort !== 'new') p.set('sort', this.sort);
                    window.location.search = p.toString();
                },
                reset() {
                    this.q = '';
                    this.cat = '';
                    this.sort = 'new';
                    this.apply();
                }
            }
        }
    </script>
@endpush
