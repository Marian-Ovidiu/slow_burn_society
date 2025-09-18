// resources/assets/js/checkout.js
document.addEventListener('alpine:init', () => {
  Alpine.data('checkout', () => ({
    editMode: false,
    loading: false,
    error: '',
    isPaying: false,
    stripe: null,
    elements: null,          // <- useremo sempre questa, aggiornata da createOrReplacePI()
    paymentElement: null,
    clientSecret: null,
    intentId: null,
    expired: false,
    paymentComplete: false,
    form: {
      firstName: '',
      lastName: '',
      email: '',
      street: '',
      streetNo: '',
      city: '',
      cap: '',
      province: '',
    },
    parseKit(it) {
      // Rileva kit da id o da property kitId/type
      const rawId = String(it?.id ?? '');
      if (rawId.startsWith('kit:')) {
        const num = rawId.slice(4);
        return { isKit: true, kitId: num, kitKey: `kit:${num}` };
      }
      if (it?.kitId != null) {
        const raw = String(it.kitId);
        const num = raw.replace(/^kit:/, '');
        return { isKit: true, kitId: num, kitKey: `kit:${num}` };
      }
      return { isKit: false, kitId: null, kitKey: null };
    },

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
        const cartToken = await this.waitForCartToken();
        Alpine.store('cart')?.touchExpiry?.();
        window.addEventListener('cart:expired', () => { this.expired = true; });

        if (!window.STRIPE_PK) throw new Error('Stripe PK mancante');
        this.stripe = window.Stripe(window.STRIPE_PK);

        const items = this.serializeCartItems();
        if (!items.length) return;
        await this.createOrReplacePI(items, cartToken);

        await this.refreshInventoryAndClamp();

        window.addEventListener('cart:changed', () => {
          this.debouncedRefreshPI();
          this.refreshInventoryAndClamp();
        });

        // 👇 AGGANCI I WATCHER QUI
        ['firstName', 'lastName', 'email', 'street', 'streetNo', 'city', 'cap', 'province']
          .forEach((k) => {
            this.$watch(`form.${k}`, () => this.debouncedSyncIntent());
          });

      } catch (e) {
        console.error('[checkout:init]', e);
        this.error = e.message || 'Errore inizializzazione checkout';
      }
    },
    async finalizePI(pi) {
      const ctrl = new AbortController();
      const t = setTimeout(() => ctrl.abort(), 8000); // timeout difensivo
      try {
        await fetch('/checkout/finalize', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
          body: new URLSearchParams({ pi }),
          signal: ctrl.signal
        });
      } catch (e) {
        console.warn('[finalizePI] failed (proseguo lo stesso)', e);
      } finally {
        clearTimeout(t);
      }
    },

    async finalizeAndRedirect() {
      const pi = this.intentId;
      await this.finalizePI(pi);              
      Alpine.store('cart').clear();
      window.location.href = `${window.location.origin}/grazie?pi=${encodeURIComponent(pi)}`;
    },

    serializeCartItems() {
      const cart = Alpine.store('cart');
      if (!cart || !Array.isArray(cart.items)) return [];
      return cart.items.map(it => {
        const k = this.parseKit(it);
        console.table(Alpine.store('cart').items)
        console.table(k)
        if (k.isKit) {
          // verso il backend invia "kitId" SENZA prefisso
          return { kitId: k.kitId, qty: Number(it.qty || 1) };
        }
        return { id: it.id, qty: Number(it.qty || 1) };
      });
    },

    // Validazione form
    isFormValid() {
      const nameOk = this.form.firstName.trim().length >= 2;
      const lastOk = this.form.lastName.trim().length >= 2;
      const mailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email.trim());

      const streetOk = this.form.street.trim().length >= 3;
      const streetNoOk = this.form.streetNo.trim().length >= 1; // es. "12/A"
      const cityOk = this.form.city.trim().length >= 2;
      const capOk = /^\d{5}$/.test(this.form.cap.trim());
      const provOk = /^[A-Za-z]{2}$/.test(this.form.province.trim());

      return nameOk && lastOk && mailOk && streetOk && streetNoOk && cityOk && capOk && provOk;
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
        qty: Number(it.qty || 1),
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
        shipping_address: {
          name: `${this.form.firstName} ${this.form.lastName}`.trim(),
          line1: `${this.form.street} ${this.form.streetNo}`.trim(),
          city: this.form.city,
          postal_code: this.form.cap,
          state: (this.form.province || '').toUpperCase(),
          country: 'IT',
        },
      };
    },

    // Azione "Paga"
    async pay() {
      if (!this.canPay()) return;
      this.loading = true; this.error = '';
      this.isPaying = true; // <- blocca refresh PI mentre paghi (vedi patch sotto)

      const thankYouUrl = `${window.location.origin}/grazie?pi=${encodeURIComponent(this.intentId)}`;
      const payload = this.buildOrderPayload();

      try {
        // 0) Preflight: stato attuale del PI
        const { paymentIntent } = await this.stripe.retrievePaymentIntent(this.clientSecret);
        const st = paymentIntent?.status;

        if (st === 'succeeded') {
          Alpine.store('cart').clear();
          await this.finalizeAndRedirect();
          window.location.href = thankYouUrl;
          return;
        }
        if (st === 'processing') {
          this.error = 'Pagamento in elaborazione: attendi qualche secondo e aggiorna la pagina.';
          return;
        }
        if (st === 'canceled') {
          // PI non confermabile: rigenera e fai riprovare
          const items = this.serializeCartItems();
          await this.createOrReplacePI(items);
          this.error = 'Sessione di pagamento scaduta: riprova.';
          return;
        }

        // 1) Valida i campi dell’Element
        const { error: submitError } = await this.elements.submit();
        if (submitError) {
          this.error = submitError.message || 'Dati di pagamento incompleti.';
          return;
        }

        // 2) Conferma
        const fullName = `${this.form.firstName} ${this.form.lastName}`.trim();
        const line1 = `${this.form.street} ${this.form.streetNo}`.trim();

        const { error } = await this.stripe.confirmPayment({
          elements: this.elements,
          confirmParams: {
            payment_method_data: {
              billing_details: {
                name: fullName || undefined,
                email: this.form.email || undefined,
                address: {
                  line1: line1 || undefined,
                  city: this.form.city || undefined,
                  postal_code: this.form.cap || undefined,
                  state: (this.form.province || '').toUpperCase() || undefined,
                  country: 'IT',
                }
              }
            },
            return_url: thankYouUrl
          },
          redirect: 'if_required'
        });

        if (error) {
          // Gestione specifica stato inatteso
          if (error.code === 'payment_intent_unexpected_state') {
            const status = error.payment_intent?.status;
            if (status === 'succeeded') {
              Alpine.store('cart').clear();
              await this.finalizeAndRedirect();
              window.location.href = thankYouUrl;
              return;
            }
            if (status === 'processing') {
              this.error = 'Pagamento in elaborazione: attendi qualche secondo.';
              return;
            }
            if (status === 'canceled') {
              const items = this.serializeCartItems();
              await this.createOrReplacePI(items);
              this.error = 'Sessione di pagamento scaduta: riprova.';
              return;
            }
          }
          throw error;
        }

        Alpine.store('cart').clear();
        await this.finalizeAndRedirect();
        window.location.href = thankYouUrl;

      } catch (e) {
        console.error('[checkout:pay]', e);
        this.error = e.message || 'Errore pagamento';
      } finally {
        this.loading = false;
        this.isPaying = false; // riabilita eventuali refresh
      }
    },


    // ——— Instances & mount aggiornabili ———
    _elementsInstance: null,   // per gestire smontaggio pulito

    async createOrReplacePI(items, forcedToken = null) {
      const cart = Alpine.store('cart');
      const cartToken = forcedToken || this.getCartToken();
      if (!cartToken) throw new Error('cart_token mancante');

      const totalCents = Math.round((cart?.total?.() || 0) * 100);

      const res = await fetch('/create-payment-intent', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          items,
          cart_token: cartToken,
          total_cents: totalCents,
          customer: {
            first_name: this.form.firstName,
            last_name: this.form.lastName,
            email: this.form.email
          },
          shipping: {
            street: this.form.street,
            street_no: this.form.streetNo,
            city: this.form.city,
            cap: this.form.cap,
            province: (this.form.province || '').toUpperCase(),
            country: 'IT'
          }
        })
      });

      const json = await res.json();
      if (!json || !json.success) {
        throw new Error(json?.data?.message || 'Impossibile creare il pagamento');
      }
      this.clientSecret = json.data.clientSecret;
      this.intentId = json.data.intentId;
      this.paymentComplete = false;
      try {
        if (this.paymentElement) this.paymentElement.unmount();
        this.paymentElement = null;
        if (this._elementsInstance?.destroy) this._elementsInstance.destroy();
      } catch { }

      this._elementsInstance = this.stripe.elements({
        clientSecret: this.clientSecret,
        appearance: { theme: 'stripe' }
      });
      this.elements = this._elementsInstance;

      this.paymentElement = this._elementsInstance.create('payment', { layout: 'tabs' });
      this.paymentElement.on('change', (e) => {
        this.paymentComplete = !!e.complete;
        this.error = e.error?.message || '';
      });
      this.paymentElement.mount('#payment-element');
    },


    _debouncer: null,
    // patch a debouncedRefreshPI
    debouncedRefreshPI() {
      if (this.isPaying) return; // evita replacement del PI durante il pay
      clearTimeout(this._debouncer);
      this._debouncer = setTimeout(async () => {
        try {
          const items = this.serializeCartItems();
          if (!items.length) return;
          await this.createOrReplacePI(items);
        } catch (e) {
          console.error('[checkout:refreshPI]', e);
          this.error = e.message || 'Errore aggiornamento pagamento';
        }
      }, 400);
    },
    async syncIntentDetails() {
      if (!this.intentId) return; // serve un PI esistente
      try {
        const payload = {
          intent_id: this.intentId,
          first_name: this.form.firstName,
          last_name: this.form.lastName,
          email: this.form.email,
          street: this.form.street,
          street_no: this.form.streetNo,
          city: this.form.city,
          cap: this.form.cap,
          province: (this.form.province || '').toUpperCase(),
          country: 'IT'
        };
        await fetch('/update-intent-details', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });
      } catch (e) {
        console.warn('[syncIntentDetails] failed', e);
      }
    },
    _debounceSync: null,
    debouncedSyncIntent() {
      clearTimeout(this._debounceSync);
      this._debounceSync = setTimeout(() => this.syncIntentDetails(), 500);
    },

    // Util UI
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
    FREE_SHIP_THRESHOLD: 35.00,
    PAID_SHIP_AMOUNT: 4.99,

    // Calcolo importi
    shippingAmount() {
      const subtotal = Alpine.store('cart')?.total?.() || 0;
      return subtotal >= this.FREE_SHIP_THRESHOLD ? 0 : this.PAID_SHIP_AMOUNT;
    },
    shippingLabel() {
      const ship = this.shippingAmount();
      if (ship === 0) return 'Gratis (sopra 35,00 €)';
      const missing = this.remainingToFree();
      return `€ ${ship.toFixed(2).replace('.', ',')} ${missing > 0 ? `(aggiungi ancora € ${missing.toFixed(2).replace('.', ',')} per spedizione gratis)` : ''}`;
    },
    remainingToFree() {
      const subtotal = Alpine.store('cart')?.total?.() || 0;
      return Math.max(0, this.FREE_SHIP_THRESHOLD - subtotal);
    },
    vatLabel() {
      return 'IVA inclusa';
    },
    grandTotal() {
      const subtotal = Alpine.store('cart')?.total?.() || 0;
      return subtotal + this.shippingAmount();
    },
    // --- INVENTARIO / STOCK (checkout) ---
    _inClamp: false,
    async refreshInventoryAndClamp() {
      if (this._inClamp) return;      // evita re-entrancy
      this._inClamp = true;

      try {
        const cart = Alpine.store('cart');
        const items = cart?.items || [];
        if (!items.length) return;

        // ✅ FIX: supporto kit:ID
        const ids = items.map(it => (it.kitId ? `kit:${it.kitId}` : String(it.id)));
        const url = `/wp-json/sbs/v1/inventory?ids=${encodeURIComponent(ids.join(','))}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Inventory fetch failed');
        const data = await res.json();

        const toNum = (v) => {
          if (typeof v === 'boolean') return null;
          if (v === null || v === undefined || v === '') return null;
          const n = Number(v);
          return Number.isFinite(n) && n >= 0 ? n : null;
        };
        const readStock = (row) => (
          toNum(row.available) ??
          toNum(row.availability) ??
          toNum(row.stock) ??
          toNum(row.qty) ??
          toNum(row.remaining) ??
          null
        );

        const map = new Map(data.map(r => [String(r.id), readStock(r)]));

        let changed = false;
        cart.items.forEach((it) => {
          // ✅ chiave coerente con la chiamata
          const key = it.kitId ? `kit:${it.kitId}` : String(it.id);
          const stock = map.get(key);

          if (stock !== null && stock !== undefined) {
            it.maxQty = stock;

            if (it.qty > stock) {
              // clamp (rimuove se 0)
              cart.setQty(it.id, stock);
              changed = true;
            }
          }
        });

        // evita loop inutili
        if (changed) cart.save();
      } catch (e) {
        console.warn('[checkout] refreshInventoryAndClamp error', e);
      } finally {
        this._inClamp = false;
      }
    },
    waitForCartToken() {
      return new Promise((resolve, reject) => {
        const started = Date.now();

        const tryGet = () => {
          const cart = Alpine.store('cart');

          // 1) Se lo store ha ensureToken, usalo
          if (cart?.ensureToken) {
            const t = cart.ensureToken();
            if (t) return resolve(t);
          }

          // 2) Token già esistente?
          const t2 = cart?.token || window.localStorage.getItem('cart_token');
          if (t2) return resolve(t2);

          // 3) Dopo 1s, crealo noi (proattivo)
          if (Date.now() - started > 1000) {
            const newTok = (window.crypto?.randomUUID?.())
              || ('ct_' + Math.random().toString(36).slice(2) + '_' + Date.now());
            try { window.localStorage.setItem('cart_token', newTok); } catch { }
            if (cart) cart.token = newTok;
            return resolve(newTok);
          }

          // 4) Continua a riprovare fino a 10s
          if (Date.now() - started > 10000)
            return reject(new Error('cart_token non disponibile'));

          setTimeout(tryGet, 50);
        };

        tryGet();
      });
    },
    getCartToken() {
      return Alpine.store('cart')?.token || window.localStorage.getItem('cart_token') || null;
    },


  }));
});
