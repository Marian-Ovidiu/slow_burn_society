@php
    $options = \Models\Options\OpzioniGlobaliFields::get();
@endphp
<header x-data="{ open: false }" class="p-6 bg-white lg:pb-0">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Desktop and above -->
        <nav class="flex items-center justify-between h-16 lg:h-20">
            <a href="{{ get_home_url(null, '/', 'https') }}" title="{{ __('Home', 'text_domain') }}" class="flex">
                <div class="text-center flex flex-col items-center justify-between">
                    <img class="w-auto h-12 lg:h-12" src="{{ $options->logo['url'] }}"
                        alt="Project Africa Conservation logo" />
                    <div class="text-custom-dark-green font-bold text-lg">Project Africa Conservation</div>
                    <!-- Increased font size -->
                </div>
            </a>

            <!-- Mobile menu button -->
            <button @click="open = !open" type="button"
                class="inline-flex p-2 text-black transition-all duration-200 rounded-md lg:hidden focus:bg-gray-100 hover:bg-gray-100">
                <svg x-show="!open" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                </svg>
                <svg x-show="open" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Desktop menu -->
            <div class="hidden lg:flex lg:items-center lg:ml-auto lg:space-x-10 py-6">
                @foreach ($menu as $key => $item)
                    <div class="relative group">
                        <a href="{{ $item->url }}"
                            class="text-lg font-medium text-black transition-all duration-200 hover:text-custom-green focus:text-custom-green font-nunitoSans">
                            <!-- Increased font size -->
                            {{ $item->title }}
                        </a>
                        @if (!empty($item->children))
                            <!-- Dropdown Menu -->
                            <div
                                class="absolute left-0 hidden p-2 bg-white border border-gray-200 rounded-lg shadow-md group-hover:block z-50">
                                <!-- Ho aggiunto z-index qui -->
                                @foreach ($item->children as $subkey => $subitem)
                                    <a href="{{ $subitem->url }}"
                                        class="block px-4 py-1 text-lg font-medium text-black transition-all duration-200 hover:text-custom-green focus:text-custom-green">
                                        <!-- Increased font size -->
                                        {{ $subitem->title }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </nav>

        <!-- Mobile menu -->
        <nav x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute inset-x-0 mt-6 pt-4 pb-6 bg-white border border-gray-200 rounded-md shadow-md lg:hidden z-50">
            <div class="flow-root pl-4">
                <div class="flex flex-col px-3 space-y-4 pt-2">
                    @foreach ($menu as $item)
                        <div class="flex flex-col justify-between w-full">
                            <a href="{{ $item->url }}"
                                class="text-lg font-medium text-black hover:text-custom-green focus:text-custom-green font-nunitoSans">
                                {{ $item->title }}
                            </a>
                            @if (!empty($item->children))
                                <div class="mt-2 space-y-1 pl-4 border-l border-gray-200">
                                    @foreach ($item->children as $subitem)
                                        <a href="{{ $subitem->url }}"
                                            class="block px-2 py-1 text-base font-medium text-black hover:text-custom-green focus:text-custom-green">
                                            {{ $subitem->title }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </nav>
    </div>
</header>
