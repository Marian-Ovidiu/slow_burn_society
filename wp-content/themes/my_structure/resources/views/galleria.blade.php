@extends('layouts.mainLayout')
@section('content')
<div class="bg-white py-6 sm:py-8 lg:py-12">
    <div class="mx-auto max-w-screen-2xl px-4 md:px-8">
        <div class="header flex-col items-center justify-center">
            @include('components.testo-sottotesto',[
                'titolo' => $galleria->titolo,
                'sottotitolo' => '',
                'highlight' => true,
                'text_base_highlight' => $galleria->frase_base,
            ])
            @include('components.testo-sottotesto',[
                'titolo' => null,
                'sottotitolo' => $galleria->descrizione,
                'highlight' => false,
            ])
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:gap-6 xl:gap-8 py-8">
            @foreach($galleria->data as $key_group => $group)
                @foreach($group as $key => $gr)
                    @php
                        $classes = ($key % 4 === 0 || $key % 4 === 3) ? '' : 'md:col-span-2';
                    @endphp
                    <a href="#"
                       class="group relative flex h-48 items-end overflow-hidden rounded-lg bg-gray-100 shadow-lg md:h-80 {{ $classes && !$group['descrizione'] ? $classes : '' }}">
                        <img src="{{$gr['immagine']['url']}}" loading="lazy" alt="Photo by Minh Pham" class="absolute inset-0 h-full w-full object-cover object-center transition duration-200 group-hover:scale-110" />

                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-gray-800 via-transparent to-transparent opacity-50"></div>

                        @if($gr['testo'])
                            <span class="relative ml-4 mb-3 inline-block text-sm text-white md:ml-5 md:text-lg">{{$gr['testo']}}</span>
                        @endif
                    </a>

                    @if($gr['descrizione'])
                        @include('components.testo-sottotesto',[
                            'titolo' => null,
                            'sottotitolo' => $gr['descrizione'],
                            'highlight' => false,
                            'class' => 'col-span-2 md:col-span-3 flex items-center justify-center'
                        ])
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
</div>
@endsection