@php
    /** @var $dataHero \Models\Options\OpzioniGlobaliFields */
    use Models\Options\OpzioniGlobaliFields;
    $dataHero = OpzioniGlobaliFields::get();
@endphp

@if (!empty($menu))
<header x-data="{ open:false }"
        class="sticky top-0 z-50 w-full glass border-b border-white/20 bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/5 text-white"
        role="banner">

    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        {{-- Logo + brand --}}
        <a href="/" class="flex items-center gap-2 shrink-0">
            @if(!empty($dataHero->logo['url']))
                <img src="{{ $dataHero->logo['url'] }}" alt="Slow Burn Society" class="h-8 w-auto" />
            @else
                <span class="material-symbols-rounded text-2xl">local_fire_department</span>
            @endif
            <span class="font-bold tracking-tight hidden sm:inline">Slow Burn Society</span>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden lg:flex items-center gap-6">
            @foreach ($menu as $item)
                <a href="{{ $item->url }}"
                   class="relative font-semibold text-white/90 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50 link-underline">
                    {{ $item->title }}
                </a>
            @endforeach
        </nav>

        {{-- Mobile toggle --}}
        <button @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-nav"
                class="lg:hidden inline-flex items-center justify-center rounded-md p-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
            <span x-show="!open" class="material-symbols-rounded text-3xl">menu</span>
            <span x-show="open" class="material-symbols-rounded text-3xl">close</span>
        </button>
    </div>

    {{-- Mobile nav --}}
    <nav id="mobile-nav" x-show="open" x-transition
         class="lg:hidden border-t border-white/10 bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/5">
        <div class="container mx-auto px-4 py-3 flex flex-col gap-2">
            @foreach ($menu as $item)
                <a href="{{ $item->url }}" class="font-semibold text-white/90 hover:text-white py-2">
                    {{ $item->title }}
                </a>
            @endforeach
        </div>
    </nav>

    {{-- Accent neon bar --}}
    <div class="header-accent" aria-hidden="true"></div>
</header>
@endif
