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

    {{-- Title --}}
    <title>@yield('title', 'Slow Burn Society')</title>

    <link rel="stylesheet" href="<?php echo vite_asset('assets/css/styles.css'); ?>">

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

    {{-- Tailwind & Alpine --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style>
        /*@use 'fonts.scss';*/
        @tailwind base;
        @tailwind components;
        @tailwind utilities;

        .l .material-symbols-rounded {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        [x-cloak] {
            display: none !important;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .product-card {
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            min-height: 260px;
        }

        .product-card:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .product-title {
            margin-top: 0.5rem;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .product-price {
            color: #2e7d32;
            font-weight: bold;
            font-size: 0.9rem;
        }

        [x-cloak] {
            display: none !important
        }

        .hero-title {
            position: relative;
            font-size: clamp(1.875rem, 2vw + 1rem, 3rem);
            font-weight: 800;
            letter-spacing: .05em;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, .4);
            text-align: center;
        }

        .hero-title::after {
            content: "";
            display: block;
            width: 5rem;
            height: .25rem;
            background-color: #45752c;
            margin: .75rem auto 0;
            border-radius: .25rem;
        }

        .section-title {
            position: relative;
            font-size: clamp(1.5rem, 1.2vw + 1rem, 2rem);
            /* ~ text-2xl -> text-3xl */
            font-weight: 800;
            letter-spacing: .05em;
            line-height: 1.2;
            text-align: center;
            color: #1f2937;
            /* text-gray-800 */
        }

        .section-title::after {
            content: "";
            display: block;
            width: 4rem;
            /* un filo più corta dell'H1 */
            height: .25rem;
            background-color: #45752c;
            margin: .5rem auto 0;
            border-radius: .25rem;
        }

        /* opzionale: nascondi scrollbar thumbnails */
        .scrollbar-hide::-webkit-scrollbar {
            display: none
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none
        }

        .banner-title {
            position: relative;
            font-size: clamp(1.75rem, 1.2vw + 1.1rem, 2.25rem);
            /* ~ text-3xl -> text-4xl */
            font-weight: 800;
            letter-spacing: .01em;
            /* tracking-tight */
            line-height: 1.15;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, .4);
            color: #fff;
            margin: 0;
        }

        .banner-title::after {
            content: "";
            display: block;
            width: 4rem;
            height: .25rem;
            background-color: #45752c;
            margin: .6rem auto 0;
            border-radius: .25rem;
        }

        .banner-title>p {
            color: white !important;
        }

        .banner-subtitle {
            margin-top: .75rem;
            font-size: clamp(1rem, .5vw + .9rem, 1.125rem);
            max-width: 42rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, .35);
            color: #fff;
        }

        .banner-subtitle > p {
            color: white;
        }
        .banner-cta {
            display: inline-block;
            margin-top: 1.25rem;
            font-weight: 600;
            padding: .6rem 1.25rem;
            border-radius: .5rem;
            background: #fff;
            color: #45752c;
            transition: filter .2s ease, transform .2s ease;
            text-decoration: none;
        }

        .banner-cta:hover {
            filter: brightness(0.97);
            transform: translateY(-1px);
        }

        .banner-cta:focus {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }

        /* riduci motion per chi ha preferenze */
        @media (prefers-reduced-motion: reduce) {
            .banner-cta {
                transition: none;
            }
        }

        /* Responsive: 3 colonne da md (768px), 4 colonne da lg (1024px) */
        @media (min-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .product-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
    {{-- Extra head (per-pagina) --}}
    @yield('head')
</head>

<body class="min-h-screen bg-[#fefcf7] text-gray-800 font-sans flex flex-col antialiased">
    {!! wp_head() !!}

    {{-- HEADER --}}
    <header class="w-full" role="banner">
        @widget('HeaderMenu')
    </header>

    {{-- MAIN CONTENT --}}
    <main id="main-content" class="flex-1 container mx-auto" role="main">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-gray-800 text-white text-sm py-6 mt-auto" role="contentinfo">
        @widget('FooterMenu')
    </footer>

    {{-- Scripts specifici alla pagina --}}
    @yield('scripts')
    {!! wp_footer() !!}
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>

</html>
