<!DOCTYPE html>
<html lang="it" class="scroll-smooth">

<head>
    …
    {{-- CONFIG opzionale prima del CDN (se vuoi future estensioni) --}}
    <script>
        window.tailwind = {
            config: {
                theme: {
                    extend: {}
                },
            }
        };
    </script>

    {{-- Tailwind CDN: abilita tutte le utility, comprese quelle arbitrarie tipo bg-[#fefcf7] --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Il tuo CSS personalizzato (se serve) DOPO Tailwind, così può sovrascrivere --}}
    <link rel="stylesheet" href="{{ vite_asset('assets/css/styles.css') }}">

    {{-- Toastify CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24..48,100..700,0..1,-50..200"
        rel="stylesheet" />

    <style>
        /* Assicura le impostazioni della font variabile */
        .material-symbols-rounded {
            font-variation-settings:
                'FILL' 0,
                /* 0 outline, 1 filled */
                'wght' 400,
                /* 100..700 */
                'GRAD' 0,
                'opsz' 24;
            /* 24, 28, 40, 48 o range */
            line-height: 1;
            /* evita salti verticali */
        }
    </style>
    @stack('head')
    {!! wp_head() !!}
    <script>
        window.deferAlpineInit = true;
    </script>
</head>

<body class="min-h-screen bg-[#fefcf7] text-gray-800 font-sans flex flex-col antialiased">

    {{-- HEADER --}}
    <header class="w-full border-b border-gray-200 bg-white/80 backdrop-blur" role="banner">
        @widget('HeaderMenu')
    </header>

    {{-- MAIN --}}
    <main id="main-content" class="flex-1 container mx-auto px-4 py-6 md:py-10" role="main">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-gray-800 text-white text-sm py-6 mt-auto" role="contentinfo">
        @widget('FooterMenu')
        <div class="container mx-auto px-4 mt-4 opacity-70">
            © {{ date('Y') }} Slow Burn Society — Tutti i diritti riservati.
        </div>
    </footer>

    {{-- Scripts: definizioni componenti PRIMA di Alpine --}}
    @stack('before_alpine')

    {{-- Alpine core (una sola volta, nel layout) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Scripts che dipendono da Alpine (store carrello, ecc.) --}}
    @stack('after_alpine')

    {{-- Toastify JS --}}
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    {{-- Hook WP --}}
    {!! wp_footer() !!}

    {{-- Avvio Alpine DOPO che è tutto nel DOM --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Alpine) Alpine.start();
        });
    </script>
</body>

</html>
