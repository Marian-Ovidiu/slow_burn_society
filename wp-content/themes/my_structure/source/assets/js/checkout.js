// resources/assets/js/checkout.js
document.addEventListener('alpine:init', () => {
  Alpine.data('checkout', () => ({
    loading: false,
    error: '',
    stripe: null,
    elements: null,
    paymentElement: null,
    clientSecret: null,
    intentId: null,
    expired: false,
    paymentComplete: false,
    form: { firstName: '', lastName: '', email: '' },

    // Attende che lo store cart sia pronto
    waitForCartReady() {
      return new Promise((resolve) => {
        const tick = () => {
          if (Alpine.store('cartReady')) return resolve();
          setTimeout(tick, 50);
        };
        tick();
      });
    },

    async init() {
      try {
        await this.waitForCartReady();

        // mantieni vivo il TTL finché sei nella pagina
        Alpine.store('cart')?.touchExpiry?.();
        window.addEventListener('cart:expired', () => { this.expired = true; });

        const cart = Alpine.store('cart');
        if (!cart || !Array.isArray(cart.items) || cart.items.length === 0) return;

        // payload minimale per creare il PaymentIntent
        const items = cart.items.map(it =>
          (it.isKit || it.type === 'kit' || it.kitId)
            ? { kitId: it.kitId || it.id, qty: Number(it.qty || 1) }
            : { id: it.id, qty: Number(it.qty || 1) }
        );

        // crea PaymentIntent sul backend
        const res = await fetch('/create-payment-intent', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ items })
        });
        const json = await res.json();
        if (!json || !json.success) {
          throw new Error(json?.data?.message || 'Impossibile creare il pagamento');
        }

        this.clientSecret = json.data.clientSecret;
        this.intentId     = json.data.intentId;

        if (!window.STRIPE_PK) throw new Error('Stripe PK mancante');

        this.stripe   = window.Stripe(window.STRIPE_PK);
        this.elements = this.stripe.elements({
          clientSecret: this.clientSecret,
          appearance: { theme: 'stripe' }
        });

        // Crea e monta UNA volta il Payment Element
        this.paymentElement = this.elements.create('payment', { layout: 'tabs' });
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

    // Validazione form
    isFormValid() {
      const nameOk = this.form.firstName.trim().length >= 2;
      const lastOk = this.form.lastName.trim().length >= 2;
      const mailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email.trim());
      return nameOk && lastOk && mailOk;
    },

    // Abilitazione bottone "Paga"
    canPay() {
      return !this.loading
        && !!this.intentId
        && (Alpine.store('cart')?.items?.length > 0)
        && this.isFormValid()
        && this.paymentComplete;
    },

    // Snapshot ordine per la thank-you
    buildOrderPayload() {
      const cart = Alpine.store('cart');
      const items = cart.items.map(it => ({
        name: it.name,
        qty:  Number(it.qty || 1),
        price: Number(it.price || 0),
        subtotal: Number((it.qty || 1) * (it.price || 0)),
        image: it.image || null,
      }));
      const subtotal = items.reduce((s, i) => s + i.subtotal, 0);

      return {
        id: null,
        number: null,
        created_at: new Date().toISOString(),
        email: this.form.email || null,
        payment_method: 'Carta',
        items,
        subtotal: Number(subtotal.toFixed(2)),
        shipping: 0,
        discount: 0,
        total: Number(cart.total().toFixed(2)),
        invoice_url: null,
        view_url: null,
      };
    },

    // Salvataggio best-effort su sessione server
    async persistOrder(payload) {
      try {
        if (navigator.sendBeacon) {
          const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
          navigator.sendBeacon('/checkout/store-order', blob);
          return;
        }
      } catch {}
      try {
        await fetch('/checkout/store-order', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } catch (e) {
        console.warn('[persistOrder] fallita', e);
      }
    },

    // Azione "Paga"
    async pay() {
      if (!this.canPay()) return;
      this.loading = true; this.error = '';

      // URL con PI per la thank-you (serve anche come return_url per 3DS)
      const thankYouUrl = `${window.location.origin}/grazie?pi=${encodeURIComponent(this.intentId)}`;

      // snapshot PRIMA di toccare il carrello
      const payload = this.buildOrderPayload();

      try {
        // valida/chiudi i campi dell’Element
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
            return_url: thankYouUrl
          },
          redirect: 'if_required'
        });

        if (error) throw error;

        // Caso “no redirect”: pagamento già riuscito
        await this.persistOrder(payload);  // salva ordine in sessione (se hai l’endpoint)
        Alpine.store('cart').clear();
        window.location.href = thankYouUrl;
      } catch (e) {
        console.error('[checkout:pay]', e);
        this.error = e.message || 'Errore pagamento';
      } finally {
        this.loading = false;
      }
    },

    // Util UI (coerenti col checkout)
    shippingLabel() { return 'Calcolata al checkout'; },
    vatLabel() { return 'IVA inclusa'; },
    grandTotal() { return Alpine.store('cart')?.total?.() || 0; },
    remainingSeconds() {
      const ms = Alpine.store('cart')?.remainingMs?.() ?? 0;
      return Math.max(0, Math.round(ms / 1000));
    },
    remainingFormatted() {
      const s = this.remainingSeconds();
      const mm = String(Math.floor(s / 60)).padStart(2, '0');
      const ss = String(s % 60).padStart(2, '0');
      return `${mm}:${ss}`;
    },
  }));
});
