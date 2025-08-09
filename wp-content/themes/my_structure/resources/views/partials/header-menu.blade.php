 @php
    /***
     *  @var $dataHero[] Core\Bases\BaseGroupAcf\OpzioniGlobaliFields
    */
    use Models\Options\OpzioniGlobaliFields;
    $dataHero = OpzioniGlobaliFields::get();
@endphp
@if (!empty($menu))
    <div x-data="{ open: false }" class="bg-[#21231E] text-white shadow-lg px-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">

            <!-- Logo simbolico -->
            <div class="flex items-center gap-3" style="max-height: 100px">
                <img src="{{ $dataHero->logo['url'] }}" class="text-3xl" alt="">
                <span class="text-lg font-bold uppercase">Slow Burn Society</span>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="open = !open" class="lg:hidden focus:outline-none">
                <span x-show="!open" class="material-symbols-rounded text-3xl">menu</span>
                <span x-show="open" class="material-symbols-rounded text-3xl">close</span>
            </button>

            <!-- Desktop Menu -->
            @if (!empty($menu))
                <nav class="hidden lg:flex gap-6">
                    @foreach ($menu as $item)
                        <a href="{{ $item->url }}"
                            class="hover:text-yellow-200 font-semibold">{{ $item->title }}</a>
                    @endforeach
                </nav>
            @endif

        </div>

        <!-- Mobile Menu -->
        @if (!empty($menu))
            <nav x-show="open" x-transition class="lg:hidden mt-4">
                <div class="flex flex-col gap-3 px-4 pb-4">
                    @foreach ($menu as $item)
                        <a href="{{ $item->url }}"
                            class="text-white font-medium hover:underline">{{ $item->title }}</a>
                    @endforeach
                </div>
            </nav>
        @endif
    </div>
      @if ($dataHero)
        @include('components.heroSection', [
            'dataHero' => $dataHero,
        ])
    @endif
@endif
