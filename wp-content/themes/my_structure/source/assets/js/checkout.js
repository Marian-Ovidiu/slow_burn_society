document.addEventListener('alpine:init', () => {
  Alpine.data('checkout', () => ({
    loading: false,
    error: '',
    stripe: null,
    elements: null,
    clientSecret: null,
    intentId: null,
    expired: false,

    // ⚠️ tienilo in sync con cart.js (TTL_MINUTES = 5)
    TTL_SECONDS: 300,

    progressWidth() {
      const rem = (Alpine.store('cart')?.remainingSeconds?.() ?? 0);
      const pct = Math.max(0, Math.min(100, Math.round((rem / this.TTL_SECONDS) * 100)));
      return pct;
    },

    // ... il tuo form + methods già presenti ...

    async init() {
      // aspetta lo store
      if (!Alpine.store('cartReady')) {
        await new Promise(r => {
          window.addEventListener('cart:ready', r, { once: true });
          setTimeout(r, 800);
        });
      }

      // dai tutto il tempo in checkout (resetta TTL all’ingresso)
      if (Alpine.store('cart')?.touchExpiry) {
        Alpine.store('cart').touchExpiry();
      }

      // se scade mentre sei qui, mostriamo alert e NON blocchiamo il pay se l’intent è già creato
      window.addEventListener('cart:expired', () => {
        this.expired = true;
      });

      // se il carrello è vuoto e non hai ancora creato un intent, fermati
      if (!Alpine.store('cart').items.length) return;

      // Stripe init + createIntent come già avevi
      if (!window.STRIPE_PK) { this.error = 'Stripe PK mancante'; return; }
      this.stripe = window.Stripe(window.STRIPE_PK);
      await this._createIntentAndMount();
    },

    async pay() {
      this.loading = true; this.error = '';
      try {
        const { error, paymentIntent } = await this.stripe.confirmPayment({
          elements: this.elements,
          clientSecret: this.clientSecret,
          confirmParams: {
            payment_method_data: {
              billing_details: {
                name : `${this.form.firstName} ${this.form.lastName}`.trim() || undefined,
                email: this.form.email || undefined,
              }
            }
          },
          redirect: 'if_required'
        });
        if (error) throw error;
        if (!paymentIntent || paymentIntent.status !== 'succeeded') throw new Error('Pagamento non riuscito');

        // Finalize: scala stock lato server
        const rf = await fetch('/checkout/finalize', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ intentId: this.intentId })
        });
        const jf = await rf.json();
        if (!jf.success) throw new Error(jf.data?.message || 'Errore finalizzazione');

        Alpine.store('cart').clear();
        window.location = '/grazie';
      } catch (e) {
        this.error = e.message || 'Errore imprevisto';
      } finally {
        this.loading = false;
      }
    }
  }));
});
