<?php
/**
 * @var \Models\Progetto $progetto
 */
?>
@php
    // $thankYouPage = pll_get_post(412, pll_current_language());
    // $thankYouUrl = $thankYouPage ? get_permalink($thankYouPage) : home_url('/grazie');
    $thankYouUrl = home_url('/grazie');

    $img = $progetto->immagine_hero ?? [];
@endphp
@extends('layouts.mainLayout')
@section('content')
    {{-- Hero --}}

    <section class="relative">
        <div class="absolute inset-0 -z-10">
            <img src="{!! $img['url'] !!}" alt="{{ $img['alt'] ?? $progetto->titolo_hero }}"
                class="w-full h-full object-cover object-top" loading="eager" decoding="async" width="1920" height="1080">
            <div class="absolute inset-0 bg-black/25"></div>
        </div>

        <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 sm:py-40">
            <div class="max-w-3xl mx-auto text-center sm:text-left rounded-xl px-6 py-8 shadow-2xl">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight drop-shadow-xl">
                    {{ $progetto->titolo_hero }}
                </h1>
                <div class="mt-6 text-base sm:text-lg text-gray-200 prose prose-invert max-w-none">
                    {!! $progetto->testo_hero !!}
                </div>
            </div>
        </div>
    </section>
    {{-- Fine Hero --}}
    @if (function_exists('pll_get_the_languages'))
        @php
            $languages = pll_get_the_languages(['raw' => 1]);
        @endphp

        <ul class="flex gap-2">
            @foreach ($languages as $lang)
                <li>
                    <a href="{{ $lang['url'] }}" class="{{ $lang['current_lang'] ? 'font-bold underline' : '' }}">
                        {{ strtoupper($lang['slug']) }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <section class="container mx-auto">
        {{-- Sezione Problemi --}}
        @component('components.section', [
            'titolo' => $progetto->problemi_titolo_1,
            'items' => $progetto->getProblemi(),
        ])
        @endcomponent

        {{-- Sezione Soluzioni --}}
        @component('components.section', [
            'titolo' => $progetto->soluzioni_titolo_1,
            'items' => $progetto->getSoluzioni(),
        ])
        @endcomponent
    </section>


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
                                        autocomplete="given-name" required placeholder="{{ load_static_strings('Nome') }}"
                                        class="input-field w-full" aria-label="Nome">
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
                                        autocomplete="email" required placeholder="{{ load_static_strings('Email') }}"
                                        class="input-field w-full" aria-label="Email">
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
                                        autocomplete="tel" required placeholder="{{ load_static_strings('Telefono') }}"
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
                                {{ load_static_strings('Metodo di pagamento') }}
                            </p>

                            <!-- Google Pay / Apple Pay Button -->
                            <div class="mb-6">
                                <div :id="'google-pay-button-' + progettoId" style="display: none;"></div>
                            </div>

                            <!-- Stripe Payment Element -->
                            <form :id="'payment-form-' + progettoId" @submit.prevent="submitForm">
                                <div :id="'payment-element-' + progettoId" class="mb-4"></div>
                            </form>

                            <!-- PayPal Button -->
                            <div :id="'paypal-button-container-' + progettoId" class="mb-6"></div>

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
@stop
