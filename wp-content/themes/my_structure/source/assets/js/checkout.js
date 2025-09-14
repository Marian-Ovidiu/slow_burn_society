document.addEventListener('alpine:init', () => {
    Alpine.data('checkout', () => ({
        loading: false,
        error: '',
        stripe: null,
        elements: null,
        paymentElement: null,   // <— tieni il riferimento
        clientSecret: null,
        intentId: null,
        expired: false,
        paymentComplete: false,
        form: { firstName: '', lastName: '', email: '' },

        async init() {
            try {
                // aspetta lo store cart
                if (!Alpine.store('cartReady')) {
                    await new Promise(r => { window.addEventListener('cart:ready', r, { once: true }); setTimeout(r, 800); });
                }
                Alpine.store('cart')?.touchExpiry?.();
                window.addEventListener('cart:expired', () => { this.expired = true; });

                if (!Alpine.store('cart').items.length) return;

                // payload items
                const items = Alpine.store('cart').items.map(it =>
                    (it.isKit || it.type === 'kit' || it.kitId)
                        ? { kitId: it.kitId || it.id, qty: it.qty }
                        : { id: it.id, qty: it.qty }
                );

                // crea PaymentIntent
                const res = await fetch('/create-payment-intent', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items })
                });
                const json = await res.json();
                if (!json.success) throw new Error(json.data?.message || 'create-intent failed');

                this.clientSecret = json.data.clientSecret;
                this.intentId = json.data.intentId;

                if (!window.STRIPE_PK) throw new Error('Stripe PK mancante');
                this.stripe = window.Stripe(window.STRIPE_PK);
                this.elements = this.stripe.elements({
                    clientSecret: this.clientSecret,
                    appearance: { theme: 'stripe' }
                });

                // CREA e MONTA il Payment Element UNA sola volta
                this.paymentElement = this.elements.create('payment', {
                    layout: 'tabs' // opzionale
                });
                this.paymentElement.on('change', (e) => {
                    this.paymentComplete = !!e.complete;
                    this.error = e.error?.message || '';
                });
                this.paymentElement.mount('#payment-element');
            } catch (e) {
                console.error('[checkout:init]', e);
                this.error = e.message || 'Errore inizializzazione checkout';
            }
        },

        // validazione base
        isFormValid() {
            const nameOk = this.form.firstName.trim().length >= 2;
            const lastOk = this.form.lastName.trim().length >= 2;
            const mailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email.trim());
            return nameOk && lastOk && mailOk;
        },

        canPay() {
            return !this.loading
                && !!this.intentId
                && (Alpine.store('cart')?.items?.length > 0)
                && this.isFormValid()
                && this.paymentComplete;
        },

        async pay() {
            if (!this.canPay()) return;
            this.loading = true; this.error = '';

            try {
                // IMPORTANTISSIMO: chiudi/valida l’Element prima di qualsiasi I/O
                const { error: submitError } = await this.elements.submit();
                if (submitError) {
                    this.error = submitError.message || 'Dati di pagamento incompleti.';
                    this.loading = false;
                    return;
                }

                const { error } = await this.stripe.confirmPayment({
                    elements: this.elements,
                    confirmParams: {
                        payment_method_data: {
                            billing_details: {
                                name: `${this.form.firstName} ${this.form.lastName}`.trim() || undefined,
                                email: this.form.email || undefined,
                            }
                        },
                        return_url: window.location.origin + '/grazie'
                    },
                    redirect: 'if_required'
                });

                if (error) throw error;

                // success
                Alpine.store('cart').clear();
                window.location.href = '/grazie';
            } catch (e) {
                console.error('[checkout:pay]', e);
                this.error = e.message || 'Errore pagamento';
            } finally {
                this.loading = false;
            }
        },

        // label riepilogo
        shippingLabel() { return 'Calcolata al checkout'; },
        vatLabel() { return 'IVA inclusa'; },
        grandTotal() { return Alpine.store('cart').total(); }
    }));
});
