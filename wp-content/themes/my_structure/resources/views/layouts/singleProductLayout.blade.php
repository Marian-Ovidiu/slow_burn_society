<!DOCTYPE html>
<html lang="it" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- Tailwind config (opzionale) --}}
    <script>
        window.tailwind = {
            config: {
                theme: {
                    extend: {
                        colors: {
                            sbs: {
                                green: '#45752c',
                                greenDark: '#386322',
                                sand: '#fefcf7'
                            }
                        },
                        boxShadow: {
                            glass: '0 8px 28px rgba(0,0,0,.08), inset 0 1px 0 rgba(255,255,255,.15)'
                        }
                    }
                }
            }
        }
    </script>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- CSS tuo (sovrascrive Tailwind se serve) --}}
    <link rel="stylesheet" href="{{ vite_asset('assets/css/styles.css') }}">

    {{-- Toastify + Material Symbols --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24..48,100..700,0..1,-50..200"
        rel="stylesheet" />

    <style>
        /* Material Symbols */
        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1
        }

        /* === TRIPPY BACKGROUND === */
        :root {
            --bg1: #0f172a;
            /* slate-900 */
            --bg2: #0b1221;
            /* blu notte */
            --blobA: #34d399;
            /* emerald-400 */
            --blobB: #a78bfa;
            /* violet-400 */
            --blobC: #f59e0b;
            /* amber-500 */
            --glass: rgba(255, 255, 255, .12);
            --glass-border: rgba(255, 255, 255, .22);
            --motion: 1;
            /* scala dell'animazione */
        }

        body {
            background: radial-gradient(1200px 800px at 10% 10%, rgba(52, 211, 153, .18), transparent 55%),
                radial-gradient(1100px 700px at 90% 20%, rgba(167, 139, 250, .14), transparent 50%),
                radial-gradient(1000px 900px at 50% 90%, rgba(245, 158, 11, .10), transparent 50%),
                linear-gradient(180deg, var(--bg1), var(--bg2));
            position: relative;
            overflow-x: hidden;
        }

        /* grain leggero */
        .noise::before {
            content: '';
            position: fixed;
            inset: -20px;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/feComponentTransfer%3E%3CfeComponentTransfer%3E%3CfeFuncA type='table' tableValues='0 0 .015 0'/%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            opacity: .45;
            mix-blend-mode: soft-light;
        }

        /* blobs aurora */
        .aurora .blob {
            position: absolute;
            border-radius: 9999px;
            filter: blur(40px);
            opacity: .55;
            will-change: transform, opacity;
            transition: opacity .6s ease;
        }

        .blob.blob-a {
            background: radial-gradient(closest-side, var(--blobA), transparent 70%);
            width: 56vw;
            height: 56vw;
            left: -10vw;
            top: -8vw;
            animation: floatA calc(26s/var(--motion)) ease-in-out infinite;
        }

        .blob.blob-b {
            background: radial-gradient(closest-side, var(--blobB), transparent 70%);
            width: 48vw;
            height: 48vw;
            right: -12vw;
            top: 12vh;
            animation: floatB calc(32s/var(--motion)) ease-in-out infinite;
        }

        .blob.blob-c {
            background: radial-gradient(closest-side, var(--blobC), transparent 70%);
            width: 60vw;
            height: 60vw;
            left: 10vw;
            bottom: -20vh;
            animation: floatC calc(38s/var(--motion)) ease-in-out infinite;
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

        @media (prefers-reduced-motion: reduce) {
            .aurora .blob {
                animation: none !important;
                opacity: .28
            }
        }

        /* glass panels */
        .glass {
            background: linear-gradient(180deg, rgba(255, 255, 255, .14), rgba(255, 255, 255, .06));
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow, 0 10px 30px rgba(0, 0, 0, .15));
        }

        /* micro-decor all’header */
        .header-accent {
            background: linear-gradient(90deg, #34d399 0%, #84cc16 20%, #a78bfa 55%, #f59e0b 100%);
            height: 2px;
            opacity: .8;
        }

        /* card hover aura */
        .card-aura {
            position: relative;
        }

        .card-aura::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 16px;
            background: conic-gradient(from 180deg at 50% 50%, rgba(52, 211, 153, .25), rgba(167, 139, 250, .25), rgba(245, 158, 11, .25), rgba(52, 211, 153, .25));
            filter: blur(18px);
            opacity: 0;
            transition: opacity .35s ease;
            z-index: -1;
        }

        .card-aura:hover::after {
            opacity: 1
        }

        /* opzionale: clamp 2 righe senza plugin */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    @stack('head')
    {!! wp_head() !!}
    <script>
        window.deferAlpineInit = true;
    </script>
</head>

<body class="noise min-h-screen text-gray-100 font-sans flex flex-col antialiased">

    {{-- aurora layer --}}
    <div class="aurora fixed inset-0 -z-10 pointer-events-none">
        <i class="blob blob-a"></i>
        <i class="blob blob-b"></i>
        <i class="blob blob-c"></i>
    </div>

    {{-- HEADER glass --}}
    <header class="w-full relative z-10" role="banner">
        @widget('HeaderMenu')
    </header>

    {{-- MAIN: container glass con soft-shadow --}}
    <main id="main-content" class="flex-1 container mx-auto px-4 py-8 md:py-12">
        <div class="glass rounded-2xl p-4 md:p-6 lg:p-8 shadow-glass card-aura">
            @yield('content')
        </div>
    </main>

    {{-- FOOTER glass --}}
    <footer class="mt-auto glass border-t border-white/20">
        <div class="container mx-auto px-4 py-8 text-sm text-white/80">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <p>© {{ date('Y') }} Slow Burn Society — Tutti i diritti riservati.</p>
                <div class="flex items-center gap-3">
                    <span class="material-symbols-rounded text-base">eco</span>
                    <span class="text-white/70">Be kind. Stay chill. 🔥</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- STACK: componenti PRIMA di Alpine --}}
    @stack('before_alpine')

    {{-- Alpine core --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- STACK: script che dipendono da Alpine --}}
    @stack('after_alpine')

    {{-- Toastify --}}
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    {!! wp_footer() !!}

    {{-- Start Alpine --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Alpine) Alpine.start();
        });
    </script>
</body>

</html>
