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

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

    {{-- Tailwind & Alpine --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
</body>

</html>
