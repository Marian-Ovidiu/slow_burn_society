// function donationFormData(progettoId) {
//     return {
//         progettoId, // 👈 Salvo progettoId nello state
//         step: 1,
//         selectedAmount: null,
//         customAmount: '',
//         loading: false,
//         clientSecret: null,
//         stripe: null,
//         elements: null,
//         formData: {
//             name: '',
//             surname: '',
//             phone: '',
//             email: '',
//             codiceFiscale: '',
//         },

//         createIntent() {
//             this.loading = true;

//             let selectedDonationAmount = (this.customAmount || this.selectedAmount) * 100;

//             if (!selectedDonationAmount || selectedDonationAmount < 100) {
//                 alert("Inserisci un importo valido (minimo 1€).");
//                 this.loading = false;
//                 return;
//             }

//             const call = new window.ApiService();
//             call.post('/create-payment-intent', {
//                 amount: selectedDonationAmount,
//                 progetto_id: this.progettoId
//             }).then(response => {
//                 this.clientSecret = response.data.clientSecret;

//                 if (!this.clientSecret) {
//                     console.error("❌ clientSecret mancante");
//                     this.loading = false;
//                     alert("Errore interno. Riprova più tardi.");
//                     return;
//                 }

//                 this.stripe = Stripe('pk_live_51QQqzmP9ji9EUZt5LkB8kShCP2rhsd195h5SlYAzUb3gGabZ8R8Uinp0TiDGKXqFsBu7oCPVL7of79NbNSGrAr3u00xFyOm6u8'); // 🔐 chiave pubblica reale

//                 this.elements = this.stripe.elements({
//                     clientSecret: this.clientSecret,
//                     paymentMethodCreation: 'manual'
//                 });

//                 const paymentElement = this.elements.create('payment');
//                 paymentElement.mount(`#payment-element-${this.progettoId}`);

//                 this.loading = false;
//                 this.step = 3;
//             }).catch(error => {
//                 console.error('❌ Errore nella richiesta:', error);
//                 this.loading = false;
//             });
//         },

//         async submitForm() {
//             this.loading = true;

//             const thankYouUrl = document.querySelector(`#thank-you-url`)?.value || '/grazie';

//             const { error, paymentIntent } = await this.stripe.confirmPayment({
//                 elements: this.elements,
//                 confirmParams: {
//                     return_url: thankYouUrl,
//                     payment_method_data: {
//                         billing_details: {
//                             name: `${this.formData.name} ${this.formData.surname}`,
//                             email: this.formData.email,
//                         }
//                     }
//                 }
//             });

//             if (error) {
//                 console.error('❌ Errore Stripe:', error.message);
//                 alert("Errore durante il pagamento: " + error.message);
//                 this.loading = false;
//                 return;
//             }

//             const paymentMethodType = paymentIntent.payment_method_types[0];

//             if (paymentMethodType === 'card') {
//                 const elements = this.elements;

//                 elements.submit();

//                 const { error: pmError, paymentMethod } = await this.stripe.createPaymentMethod({
//                     elements,
//                     params: {
//                         billing_details: {
//                             name: `${this.formData.name} ${this.formData.surname}`,
//                             email: this.formData.email
//                         }
//                     }
//                 });

//                 if (pmError) {
//                     console.error('❌ Errore PaymentMethod:', pmError.message);
//                     alert("Errore durante il pagamento: " + pmError.message);
//                     this.loading = false;
//                     return;
//                 }

//                 const amount = this.customAmount || this.selectedAmount;

//                 const call = new window.ApiService();
//                 call.post('/complete-donation', {
//                     name: this.formData.name,
//                     surname: this.formData.surname,
//                     phone: this.formData.phone,
//                     email: this.formData.email,
//                     codiceFiscale: this.formData.codiceFiscale,
//                     paymentMethodId: paymentMethod.id,
//                     progettoId: this.progettoId,
//                     amount: amount
//                 }).then(response => {
//                     if (response.success) {
//                         window.location.href = thankYouUrl;
//                     } else {
//                         alert("Errore nella creazione dell'ordine");
//                     }
//                     this.loading = false;
//                 }).catch(err => {
//                     console.error('❌ Errore creazione ordine:', err);
//                     this.loading = false;
//                 });

//             } else {
//                 console.log('➡️ Redirect a:', paymentIntent.next_action?.redirect_to_url?.url);
//                 // lasciato il codice commentato, se vuoi usare redirect automatico.
//             }
//         }
//     };
// }
