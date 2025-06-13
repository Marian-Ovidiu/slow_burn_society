@extends('layouts.mainLayout')
@section('content')
    @php
        // $thankYouUrl = get_permalink(pll_get_post(412, pll_current_language()));
        $thankYouUrl = '/grazie';
        $img = $opzioniArchivio->immagine_hero ?? [];

        // Separati i due gruppi di frasi per animazioni diverse
        $typingTextHero = array_filter(['una missione', 'una passione', 'una dedizione']);

        $typingTitoliH3 = array_filter([
            $opzioniArchivio->highlights_frase_1 ?? null,
            $opzioniArchivio->highlights_frase_2 ?? null,
            $opzioniArchivio->highlights_frase_3 ?? null,
        ]);
    @endphp

    {{-- Hero con sfondo e doppio typing --}}
    <section class="relative bg-black">
        @if (!empty($img['url']))
            <div class="absolute inset-0 z-0">
                <img src="{{ $img['url'] }}" alt="{{ $img['alt'] ?? '' }}" title="{{ $img['title'] ?? '' }}"
                    width="{{ $img['width'] ?? '' }}" height="{{ $img['height'] ?? '' }}"
                    aria-label="{{ $img['description'] ?? ($img['alt'] ?? '') }}" loading="lazy"
                    class="w-full h-full object-cover brightness-[.7] transition-all duration-300 ease-in-out" />
            </div>
        @endif

        <div class="absolute inset-0 bg-black/10 sm:bg-black/20 z-10"></div>

        <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-36">
            <div class="text-center sm:text-left max-w-3xl">
                {{-- H1 principale --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white drop-shadow-md mb-4 leading-tight">
                    {{ $opzioniArchivio->titolo_hero }}
                </h1>

                {{-- Frasi emozionali animate sotto l'H1 --}}
                <div x-data="typingEffect({{ json_encode($typingTextHero) }})" class="relative text-white">
                    {{-- Frase attiva --}}
                    <p x-text="displayText"
                        class="text-xl sm:text-2xl lg:text-3xl font-medium transition-opacity duration-300"></p>

                    {{-- Frase placeholder invisibile ma con altezza visibile (per evitare jump) --}}
                    <span class="absolute opacity-0 pointer-events-none text-xl sm:text-2xl lg:text-3xl font-medium">
                        {{ collect($typingTextHero)->sortByDesc(fn($t) => strlen($t))->first() }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <div class="container flex justify-center pt-6">
        {{-- H3 semantico statico per SEO --}}
        <h3 class="sr-only">{{ $typingTitoliH3[0] ?? '' }}</h3> {{-- screen-reader only --}}

        {{-- Effetto typing visuale --}}
        <div x-data="typingEffect({{ json_encode($typingTitoliH3) }})" class="mb-2 h-8 text-center">
            <template x-for="(text, index) in texts" :key="index">
                <span x-show="currentText === index" x-text="displayText"
                    class="text-lg sm:text-xl lg:text-2xl font-semibold text-custom-dark-green" x-transition.opacity></span>
            </template>
            <span class="text-lg sm:text-xl lg:text-2xl text-custom-dark-green animate-blink">|</span>
        </div>
    </div>


    {{-- inizio testo sottotesto --}}
    @include('components.testo-sottotesto', [
        'titolo' => '',
        'sottotitolo' => $opzioniArchivio->testo_sotto_hero,
        'immagine_url' => $opzioniArchivio->immagine_sotto_hero['url'] ?? null,
        'immagine_alt' => $opzioniArchivio->immagine_sotto_hero['alt'] ?? null,
        'immagine_title' => $opzioniArchivio->immagine_sotto_hero['title'] ?? null,
        'immagine_caption' => $opzioniArchivio->immagine_sotto_hero['caption'] ?? null,
        'immagine_description' => $opzioniArchivio->immagine_sotto_hero['description'] ?? null,
    ])
    {{-- fine testo sottotesto --}}

    {{-- <input type="hidden"a id="thank-you-url" value="{{ $thankYouUrl }}"> --}}
    @foreach ($progetti as $index => $progetto)
        <section class="py-10 sm:py-16 lg:py-24">
            <div class="max-w-5xl px-4 mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 md:items-stretch gap-y-5">
                    <figure class="relative py-4">
                        {{-- Immagine di sfondo --}}
                        <img src="{{ $progetto->featured_image }}" alt="{{ $progetto->titolo_card }}"
                            title="{{ $progetto->titolo_card }}"
                            class="object-cover w-full h-full md:object-left md:origin-top-left" loading="lazy"
                            decoding="async" />

                        {{-- Overlay per leggibilità testo --}}
                        <div class="absolute inset-0 bg-black/10 sm:bg-black/20 z-10"></div>

                        {{-- Caption visibile (se presente) --}}
                        @if (!empty($progetto->immagine_card['caption']))
                            <figcaption class="absolute bottom-0 left-0 bg-black/60 text-white text-xs italic p-2 z-20">
                                {{ $progetto->immagine_card['caption'] }}
                            </figcaption>
                        @endif

                        {{-- Caption SEO invisibile ma letta dai reader --}}
                        @if (!empty($progetto->immagine_card['description']))
                            <div class="sr-only">{{ $progetto->immagine_card['description'] }}</div>
                        @endif

                        {{-- Contenuto sovrapposto (titolo e testo) --}}
                        <div
                            class="absolute inset-0 z-20 flex items-center justify-center md:justify-start px-4 sm:px-6 lg:px-8">
                            <div class="max-w-3xl text-center md:text-left">
                                <h4 class="font-bold text-white text-3xl lg:text-4xl leading-tight">
                                    {{ $progetto->titolo_card }}
                                </h4>
                                <p class="mt-4 text-sm text-gray-200">{!! $progetto->content !!}</p>
                            </div>
                        </div>
                    </figure>


                    {{-- Form Donazione --}}
                    <div x-data="donationFormData" x-init="init({{ $progetto->id }}, '{{ $thankYouUrl }}')"
                        class="w-full max-w-xl mx-auto bg-white rounded-xl shadow-xl py-3 px-6">
                        {{-- Stepper header --}}
                        <div class="flex justify-between mb-6 text-sm font-semibold text-custom-dark-green">
                            <template x-for="(label, i) in ['Importo', 'Dati', 'Pagamento']" :key="i">
                                <button class="flex-1 text-center focus:outline-none" @click="goToStep(i+1)"
                                    :class="{
                                        'border-b-4 border-custom-dark-green pb-2 text-custom-dark-green': step ===
                                            i +
                                            1,
                                        'text-gray-400 cursor-not-allowed': (i + 1 > step),
                                        'hover:text-custom-dark-green': (i + 1 <= step)
                                    }"
                                    type="button">
                                    <span x-text="label"></span>
                                </button>
                            </template>
                        </div>

                        <h2 class="text-2xl font-bold text-custom-dark-green mb-4">
                            {{ load_static_strings('Fai una donazione al nostro progetto') }}
                        </h2>

                        {{-- STEP 1 – Importo --}}
                        <template x-if="step === 1">
                            <div>
                                <p class="text-xl font-bold text-custom-dark-green mb-4">
                                    {{ load_static_strings('Quanto vuoi donare?') }}</p>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <template x-for="amount in [5, 25, 50, 100]" :key="amount">
                                        <button @click="selectedAmount = amount; customAmount = ''; showAmountError = false"
                                            :class="selectedAmount === amount && !customAmount ?
                                                'bg-custom-dark-green text-white' :
                                                'bg-custom-light-green text-custom-dark-green'"
                                            class="rounded-lg px-4 py-2 font-medium text-center transition duration-200 active:scale-95"
                                            x-text="amount + '€'">
                                        </button>
                                    </template>
                                </div>

                                <p class="mt-6 text-custom-dark-green font-semibold">
                                    {{ load_static_strings('Oppure inserisci un importo personalizzato') }}</p>

                                <div class="flex items-center mt-3">
                                    <input x-model="customAmount" @input="selectedAmount = null; showAmountError = false"
                                        type="number" min="1"
                                        placeholder="{{ load_static_strings('Inserisci importo') }}"
                                        class="flex-1 rounded-l px-4 py-2 border border-r-0 border-gray-300 focus:outline-none focus:ring-2 focus:ring-custom-dark-green">
                                    <span class="bg-gray-100 px-3 py-2 rounded-r border border-gray-300 text-gray-700">,00
                                        €</span>
                                </div>

                                <template x-if="showAmountError">
                                    <p class="text-red-600 mt-2">
                                        {{ load_static_strings('Seleziona o inserisci un importo valido') }}</p>
                                </template>

                                <div class="mt-6 flex justify-end">
                                    <button @click.prevent="goToStep(2)" :disabled="!isAmountValid()"
                                        class="rounded-full px-6 py-3 font-bold text-white transition"
                                        :class="isAmountValid() ? 'bg-custom-dark-green hover:translate-y-1' :
                                            'bg-gray-400 cursor-not-allowed'">
                                        {{ load_static_strings('Avanti') }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- STEP 2 – Dati Utente --}}
                        <template x-if="step === 2">
                            <div>
                                <p class="text-xl font-bold text-custom-dark-green mb-4">
                                    {{ load_static_strings('I tuoi dati') }}
                                </p>

                                <div class="grid gap-4 text-left" @submit.prevent>
                                    {{-- Nome --}}
                                    <div>
                                        <input x-model="formData.name" @blur="touched.name = true" type="text"
                                            autocomplete="given-name" required
                                            placeholder="{{ load_static_strings('Nome') }}" class="input-field w-full"
                                            aria-label="Nome">
                                        <template x-if="touched.name && formData.name === ''">
                                            <p class="text-red-500 text-sm mt-1">
                                                {{ load_static_strings('Il nome è obbligatorio') }}
                                            </p>
                                        </template>
                                    </div>

                                    {{-- Cognome --}}
                                    <div>
                                        <input x-model="formData.surname" @blur="touched.surname = true" type="text"
                                            autocomplete="family-name" required
                                            placeholder="{{ load_static_strings('Cognome') }}" class="input-field w-full"
                                            aria-label="Cognome">
                                        <template x-if="touched.surname && formData.surname === ''">
                                            <p class="text-red-500 text-sm mt-1">
                                                {{ load_static_strings('Il cognome è obbligatorio') }}
                                            </p>
                                        </template>
                                    </div>

                                    {{-- Email --}}
                                    <div>
                                        <input x-model="formData.email" @blur="touched.email = true" type="email"
                                            autocomplete="email" required
                                            placeholder="{{ load_static_strings('Email') }}" class="input-field w-full"
                                            aria-label="Email">
                                        <template
                                            x-if="touched.email && (formData.email === '' || !formData.email.includes('@'))">
                                            <p class="text-red-500 text-sm mt-1">
                                                {{ load_static_strings('Inserisci un’email valida') }}
                                            </p>
                                        </template>
                                    </div>

                                    {{-- Telefono --}}
                                    <div>
                                        <input x-model="formData.phone" @blur="touched.phone = true" type="tel"
                                            autocomplete="tel" required
                                            placeholder="{{ load_static_strings('Telefono') }}"
                                            class="input-field w-full" aria-label="Telefono">
                                        <template x-if="touched.phone && formData.phone === ''">
                                            <p class="text-red-500 text-sm mt-1">
                                                {{ load_static_strings('Il telefono è obbligatorio') }}
                                            </p>
                                        </template>
                                    </div>

                                    {{-- Codice Fiscale (opzionale) --}}
                                    <div>
                                        <label for="cf" class="block text-sm text-gray-600 mb-1">
                                            {{ load_static_strings('Codice Fiscale') }}
                                            <span class="text-xs text-gray-400 ml-1">
                                                ({{ load_static_strings('opzionale') }})
                                            </span>
                                        </label>
                                        <input id="cf" x-model="formData.codiceFiscale" type="text"
                                            placeholder="{{ load_static_strings('Codice Fiscale') }}"
                                            class="input-field w-full" aria-label="Codice Fiscale">
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-between">
                                    <button @click="step = 1"
                                        class="rounded-full px-6 py-3 bg-gray-200 text-custom-dark-green font-semibold hover:bg-gray-300">
                                        {{ load_static_strings('Indietro') }}
                                    </button>

                                    <button
                                        @click="touched.name = true; touched.surname = true; touched.email = true; touched.phone = true; goToStep(3)"
                                        :disabled="!isUserDataValid()"
                                        class="rounded-full px-6 py-3 font-bold text-white transition"
                                        :class="isUserDataValid() ? 'bg-custom-dark-green hover:translate-y-1' :
                                            'bg-gray-400 cursor-not-allowed'">
                                        {{ load_static_strings('Procedi al pagamento') }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- STEP 3 – Pagamento --}}
                        <template x-if="step === 3">
                            <div>
                                <p class="text-xl font-bold text-custom-dark-green mb-4">
                                    {{ load_static_strings('Metodo di pagamento') }}</p>

                                <div class="mb-6" :id="'google-pay-button-' + progettoId" style="display: none;">
                                </div>
                                <div :id="'card-element-container-' + progettoId">
                                    <form :id="'payment-form-' + progettoId" @submit.prevent="submitForm">
                                        <div :id="'payment-element-' + progettoId"></div>
                                    </form>
                                </div>

                                <div class="mt-6 flex justify-between">
                                    <button @click="step = 2"
                                        class="rounded-full px-6 py-3 bg-gray-200 text-custom-dark-green font-semibold hover:bg-gray-300">
                                        {{ load_static_strings('Indietro') }}
                                    </button>
                                    <button @click="submitForm()"
                                        class="rounded-full px-6 py-3 bg-custom-dark-green text-white font-bold hover:translate-y-1 transition">
                                        {{ load_static_strings('Dona ora') }}
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>
    @endforeach
@stop
