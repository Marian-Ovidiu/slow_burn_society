@extends('layouts.mainLayout')
@section('content')

 {{--   <div class="container" x-data="donationFormData()" x-ref="donationForm">
        <div class="w-screen">
            <div class="mx-auto flex justify-center flex-col items-center max-w-screen-lg px-6 pb-20">
                <!-- Step 1: Selezione dell'importo -->
                <div class="w-full text-center" x-show="step === 1">
                    <p class="mt-8 font-serif text-xl font-bold text-blue-900">Scegli quanto donare</p>
                    <div class="mt-4 mx-auto grid grid-cols-2 gap-2 lg:max-w-xl">
                        <button
                                @click="selectedAmount = 20; customAmount = ''"
                                :class="selectedAmount === 20 && !customAmount ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-900'"
                                class="rounded-lg px-4 py-2 font-medium active:scale-95"
                        >20€</button>
                        <button
                                @click="selectedAmount = 50; customAmount = ''"
                                :class="selectedAmount === 50 && !customAmount ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-900'"
                                class="rounded-lg px-4 py-2 font-medium active:scale-95"
                        >50€</button>
                        <button
                                @click="selectedAmount = 80; customAmount = ''"
                                :class="selectedAmount === 80 && !customAmount ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-900'"
                                class="rounded-lg px-4 py-2 font-medium active:scale-95"
                        >80€</button>
                        <button
                                @click="selectedAmount = 150; customAmount = ''"
                                :class="selectedAmount === 150 && !customAmount ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-900'"
                                class="rounded-lg px-4 py-2 font-medium active:scale-95"
                        >150€</button>
                    </div>
                </div>

                <!-- Step 1: Importo personalizzato -->
                <div class="w-full text-center" x-show="step === 1">
                    <p class="mt-8 font-serif text-xl font-bold text-blue-900">Oppure scegli tu l'importo</p>
                    <div class="w-full mx-auto md:w-1/2 px-3 mb-2 md:mb-0 flex flex-row justify-center items-center mt-4">
                        <input x-model="customAmount" @input="selectedAmount = null" class="appearance-none block w-full rounded py-3 px-4 mb-3 leading-tight" type="number" placeholder="Scegli importo">
                        <div class="decimals h-full px-4">,00</div>
                    </div>
                </div>

                <!-- Pulsante per avanzare allo step 2 dal primo step -->
                <button
                        x-show="step === 1"
                        @click="step = 2"
                        :disabled="!(selectedAmount || customAmount)"
                        :class="(selectedAmount || customAmount) ? 'bg-emerald-600 hover:translate-y-1' : 'bg-gray-400 cursor-not-allowed'"
                        class="mt-4 w-56 rounded-full border-emerald-500 px-10 py-4 text-lg font-bold text-white transition"
                >
                    Avanti
                </button>

                <!-- Step 2: Dettagli di fatturazione -->
                <div x-show="step === 2" class="w-full text-center">
                    <p class="mt-8 font-serif text-xl font-bold text-blue-900">Dettagli di fatturazione</p>
                    <div class="mt-4 mx-auto grid grid-cols-1 gap-6 lg:max-w-xl">
                        <input x-model="formData.name" type="text" placeholder="Nome" name="name" required class="w-full rounded-lg border-gray-300 px-4 py-2"/>
                        <input x-model="formData.surname" type="text" placeholder="Cognome" name="surname" required class="w-full rounded-lg border-gray-300 px-4 py-2"/>
                        <input x-model="formData.phone" type="number" placeholder="Numero di telefono" name="phone" class="w-full rounded-lg border-gray-300 px-4 py-2"/>
                        <input x-model="formData.email" type="email" placeholder="Email" name="email" required class="w-full rounded-lg border-gray-300 px-4 py-2"/>
                        <input x-model="formData.codiceFiscale" type="text" placeholder="Codice Fiscale" name="codiceFiscale" required class="w-full rounded-lg border-gray-300 px-4 py-2"/>
                    </div>
                    <div class="buttons flex justify-between flex-row">
                        <button @click="step = 1" class="mt-4 min-w-32 rounded-full border-emerald-500 bg-emerald-600 px-5 py-4 text-lg font-bold text-white transition hover:translate-y-1">
                            Indietro
                        </button>
                        <button
                                @click="createIntent()"
                                id="call-intent"
                                :disabled="!(formData.name && formData.surname && formData.email && formData.codiceFiscale)"
                                :class="(formData.name && formData.surname && formData.email && formData.codiceFiscale) ? 'bg-emerald-600 hover:translate-y-1' : 'bg-gray-400 cursor-not-allowed'"
                                class="mt-4 min-w-32 rounded-full border-emerald-500 px-5 py-4 text-lg font-bold text-white transition">
                            <span x-show="!loading">Avanti</span>
                            <span x-show="loading" class="loader"></span>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Dati della carta di credito -->
                <div x-show="step === 3" class="w-full text-center">
                        <p class="mt-8 font-serif text-xl font-bold text-blue-900">Dati della carta di credito</p>
                        <div class="mt-4 mx-auto grid grid-cols-1 gap-6 lg:max-w-xl">
                            @foreach($pagamenti_disponibili as $p)
                                @if($p->id === 'stripe')
                                    <div id="card-element-container">
                                        <!-- Div per il form di Stripe Elements -->
                                        <form id="payment-form">
                                            <div id="payment-element">
                                                <!-- Elemento di Stripe per la carta di credito -->
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    {!! '<button data-gateway-id="' . esc_attr( $p->id ) . '">' . esc_html( $p->get_title() ) . '</button>' !!}
                                @endif
                            @endforeach
                        </div>
                        <div class="buttons flex justify-between flex-row">
                            <button @click="step = 2" class="mt-4 min-w-32 rounded-full border-emerald-500 bg-emerald-600 px-5 py-4 text-lg font-bold text-white transition hover:translate-y-1">
                                Indietro
                            </button>
                            <button @click="submitForm()" class="mt-4 min-w-32 rounded-full border-emerald-500 px-5 py-4 text-lg font-bold text-white transition bg-emerald-600 hover:translate-y-1">
                                Dona ora
                            </button>
                        </div>
                    </div>
            </div>
        </div>
    </div>--}}


@stop