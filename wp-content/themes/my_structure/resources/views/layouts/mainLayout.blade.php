<!DOCTYPE html>
<html lang="it" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- SEO Basics --}}
    <meta name="description" content="@yield('meta_description', 'Slow Burn Society: accendini, filtri, tabacco e accessori da fumo.')" />
    <meta name="keywords" content="@yield('meta_keywords', 'accendini, filtri, tabacco, accessori, cannabis, slow burn, shop')" />
    <meta name="author" content="Slow Burn Society" />
    <meta name="robots" content="index, follow" />

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og_title', 'Slow Burn Society')" />
    <meta property="og:description" content="@yield('og_description', 'Accendini, filtri, tabacco e accessori da fumo.')" />
    <meta property="og:image" content="@yield('og_image', theme_asset('assets/images/social-cover.jpg'))" />
    <meta property="og:url" content="{{ home_url($_SERVER['REQUEST_URI']) }}" />
    <meta property="og:type" content="website" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('twitter_title', 'Slow Burn Society')" />
    <meta name="twitter:description" content="@yield('twitter_description', 'Accendini, filtri, tabacco e accessori da fumo.')" />
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/social-cover.jpg'))" />

    <title>@yield('title', 'Slow Burn Society')</title>

    {{-- Tailwind (CDN) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- App CSS (può sovrascrivere Tailwind) --}}
    <link rel="stylesheet" href="{{ vite_asset('assets/css/styles.css') }}">

    {{-- Toastify + Fonts --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Nunito+Sans:wght@300;400;700&display=swap"
        rel="stylesheet">

    <style>
        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1
        }
    </style>

    <link rel="canonical" href="{{ home_url($_SERVER['REQUEST_URI']) }}">
    <meta name="color-scheme" content="light dark">

    {{-- Head extra dalle pagine --}}
    @stack('head')

    {{-- ====== BACKGROUND TRIPPY (abilitato solo se la pagina dichiara @section('trippy')) ====== --}}
    @hasSection('trippy')
        <style>
            .trippy-aurora {
                --violet: #BF00FF;
                --cyan: #00FFFF;
                --magenta: #FF00CC;
                --lime: #CCFF00;
                --orange: #FF8800;
                --bg1: #0A0014;
                --bg2: #05080F;
                --motion: 1;
                position: fixed;
                inset: 0;
                z-index: 0;
                pointer-events: none;
                background: linear-gradient(180deg, var(--bg1), var(--bg2));
            }

            .trippy-aurora .vignette {
                position: absolute;
                inset: -10%;
                /* fallback */
                background:
                    radial-gradient(1200px 800px at 12% 10%, rgba(191, 0, 255, .20), transparent 55%),
                    radial-gradient(1100px 700px at 88% 16%, rgba(0, 255, 255, .16), transparent 50%),
                    radial-gradient(1000px 900px at 50% 90%, rgba(255, 136, 0, .14), transparent 50%);
                /* override modern */
                background:
                    radial-gradient(1200px 800px at 12% 10%, color-mix(in srgb, var(--violet) 20%, transparent), transparent 55%),
                    radial-gradient(1100px 700px at 88% 16%, color-mix(in srgb, var(--cyan) 16%, transparent), transparent 50%),
                    radial-gradient(1000px 900px at 50% 90%, color-mix(in srgb, var(--orange) 14%, transparent), transparent 50%);
            }

            .trippy-aurora .blob {
                position: absolute;
                border-radius: 9999px;
                filter: blur(40px);
                opacity: .6;
                will-change: transform;
            }

            .blob.violet {
                background: radial-gradient(closest-side, color-mix(in srgb, var(--violet) 70%, transparent), transparent 70%);
                width: 58vw;
                height: 58vw;
                left: -12vw;
                top: -8vh;
                animation: floatA calc(28s/var(--motion)) ease-in-out infinite;
            }

            .blob.cyan {
                background: radial-gradient(closest-side, color-mix(in srgb, var(--cyan) 65%, transparent), transparent 70%);
                width: 50vw;
                height: 50vw;
                right: -14vw;
                top: 10vh;
                animation: floatB calc(34s/var(--motion)) ease-in-out infinite;
            }

            .blob.orange {
                background: radial-gradient(closest-side, color-mix(in srgb, var(--orange) 60%, transparent), transparent 70%);
                width: 62vw;
                height: 62vw;
                left: 8vw;
                bottom: -20vh;
                animation: floatC calc(40s/var(--motion)) ease-in-out infinite;
            }

            @keyframes floatA {

                0%,
                100% {
                    transform: translate3d(0, 0, 0)
                }

                50% {
                    transform: translate3d(4vw, 2vh, 0)
                }
            }

            @keyframes floatB {

                0%,
                100% {
                    transform: translate3d(0, 0, 0)
                }

                50% {
                    transform: translate3d(-3vw, 3vh, 0)
                }
            }

            @keyframes floatC {

                0%,
                100% {
                    transform: translate3d(0, 0, 0)
                }

                50% {
                    transform: translate3d(2vw, -2vh, 0)
                }
            }

            .trippy-aurora .noise {
                position: absolute;
                inset: -20px;
                mix-blend-mode: soft-light;
                opacity: .45;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/feComponentTransfer%3E%3CfeComponentTransfer%3E%3CfeFuncA type='table' tableValues='0 0 .012 0'/%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            }

            .trippy-aurora .shade {
                position: absolute;
                inset: 0;
                background: radial-gradient(ellipse at 50% 40%, transparent 55%, rgba(0, 0, 0, .35) 100%);
            }

            @media (prefers-reduced-motion: reduce) {
                .blob {
                    animation: none !important;
                    opacity: .35
                }
            }

            h1,
            h2,
            h3 {
                font-family: 'Bebas Neue', sans-serif;
                letter-spacing: .5px;
                text-transform: uppercase;
            }

            body {
                font-family: 'Nunito Sans', sans-serif;
                color: #1a1a1a;
                background-color: #f9f7f4;
            }

            strong,
            a {
                color: #ff5a00;
            }
        </style>
    @endif

    {!! wp_head() !!}
</head>

{{-- Se trippy è attivo → testo bianco, altrimenti testo scuro --}}

<body
    class="min-h-screen bg-transparent font-sans flex flex-col antialiased @hasSection('trippy')
text-white
@else
text-gray-600
@endif">

    {{-- BACKGROUND TRIPPY (render solo se @section('trippy') è presente nella pagina) --}}
    @hasSection('trippy')
        <div class="trippy-aurora" aria-hidden="true">
            <div class="vignette"></div>
            <i class="blob violet"></i>
            <i class="blob cyan"></i>
            <i class="blob orange"></i>
            <div class="noise"></div>
            <div class="shade"></div>
        </div>
    @endif

    {{-- HEADER --}}
    <header class="w-full relative z-10" role="banner">
        @widget('HeaderMenu')
    </header>

    {{-- MAIN --}}
    <main id="main-content" class="flex-1 container mx-auto relative z-10" role="main">
        @yield('content')

    </main>

    {{-- FOOTER --}}
    <footer class="bg-[#292524] text-white text-sm py-6 mt-auto relative z-10" role="contentinfo">
        @widget('FooterMenu')
    </footer>

    {{-- Script BEFORE Alpine --}}
    @stack('before_alpine')

    {{-- Alpine core --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Script AFTER Alpine (store carrello, ecc.) --}}
    @stack('after_alpine')

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    {!! wp_footer() !!}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Alpine) Alpine.start();
        });
    </script>
</body>

</html>
