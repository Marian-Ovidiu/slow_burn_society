// resources/assets/js/checkout.js
document.addEventListener('alpine:init', () => {
  Alpine.data('checkout', () => ({
    editMode: false,
    loading: false,
    error: '',
    stripe: null,
    elements: null,          // <- useremo sempre questa, aggiornata da createOrReplacePI()
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

        // Inizializza Stripe una volta sola
        if (!window.STRIPE_PK) throw new Error('Stripe PK mancante');
        this.stripe = window.Stripe(window.STRIPE_PK);

        // Primo mount basato sul carrello corrente
        const items = this.serializeCartItems();
        if (!items.length) return;
        await this.createOrReplacePI(items);

        await this.refreshInventoryAndClamp();

        window.addEventListener('cart:changed', () => {
          // quando cambiano le qty, aggiorna PI e riallinea stock (debounced)
          this.debouncedRefreshPI();
          this.refreshInventoryAndClamp();
        });

      } catch (e) {
        console.error('[checkout:init]', e);
        this.error = e.message || 'Errore inizializzazione checkout';
      }
    },

    serializeCartItems() {
      const cart = Alpine.store('cart');
      if (!cart || !Array.isArray(cart.items)) return [];
      return cart.items.map(it =>
        (it.isKit || it.type === 'kit' || it.kitId)
          ? { kitId: it.kitId || it.id, qty: Number(it.qty || 1) }
          : { id: it.id, qty: Number(it.qty || 1) }
      );
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
      } catch { }
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
        // valida/chiudi i campi dell’Element (sempre quello attuale)
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
        await this.persistOrder(payload);
        Alpine.store('cart').clear();
        window.location.href = thankYouUrl;
      } catch (e) {
        console.error('[checkout:pay]', e);
        this.error = e.message || 'Errore pagamento';
      } finally {
        this.loading = false;
      }
    },

    // ——— Instances & mount aggiornabili ———
    _elementsInstance: null,   // per gestire smontaggio pulito

    async createOrReplacePI(items) {
      // Opzione semplice: crea sempre un nuovo PI con gli items correnti
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
      this.intentId = json.data.intentId;

      // Smonta eventuali Elements precedenti per evitare conflitti di clientSecret
      try {
        if (this.paymentElement) this.paymentElement.unmount();
        this.paymentElement = null;
        if (this._elementsInstance && this._elementsInstance.destroy) {
          this._elementsInstance.destroy();
        }
      } catch (_) { }

      // Crea nuovo Elements legato al nuovo clientSecret
      this._elementsInstance = this.stripe.elements({
        clientSecret: this.clientSecret,
        appearance: { theme: 'stripe' }
      });

      // *** punto chiave: manteniamo `this.elements` sempre riferito all'istanza attuale ***
      this.elements = this._elementsInstance;

      this.paymentElement = this._elementsInstance.create('payment', { layout: 'tabs' });
      this.paymentElement.on('change', (e) => {
        this.paymentComplete = !!e.complete;
        this.error = e.error?.message || '';
      });
      this.paymentElement.mount('#payment-element');
    },

    _debouncer: null,
    debouncedRefreshPI() {
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

    // Util UI
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
   // --- INVENTARIO / STOCK (checkout) ---
async refreshInventoryAndClamp() {
  try {
    const cart = Alpine.store('cart');
    const items = cart?.items || [];
    if (!items.length) return;

    // Lista ID per l'endpoint (supporto kit:ID)
    const ids = items.map(it => (it.kitId ? `kit:${it.kitId}` : String(it.id)));
    const url = `/wp-json/sbs/v1/inventory?ids=${encodeURIComponent(ids.join(','))}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error('Inventory fetch failed');
    const data = await res.json(); // es: [{id:'123', available: 2}, {id:'kit:5', available: 1}]

    // helper: estrai numero sicuro (ignora boolean)
    const toNum = (v) => {
      if (typeof v === 'boolean') return null;
      if (v === null || v === undefined || v === '') return null;
      const n = Number(v);
      return Number.isFinite(n) && n >= 0 ? n : null;
    };
    const readStock = (row) => {
      return (
        toNum(row.available) ??
        toNum(row.availability) ??
        toNum(row.stock) ??
        toNum(row.qty) ??
        toNum(row.remaining) ??
        null
      );
    };

    // Mappa id -> disponibilità numerica
    const map = new Map(
      data.map(r => [String(r.id), readStock(r)])
    );

    // Aggiorna maxQty e clampa se necessario
    let changed = false;
    cart.items.forEach((it) => {
      const key = it.kitId ? `kit:${it.kitId}` : String(it.id);
      const stock = map.get(key);
      if (stock !== null && stock !== undefined) {
        // imposta lo stock reale
        it.maxQty = stock;

        if (it.qty > stock) {
          cart.setQty(it.id, stock); // clamp + persist + cart:changed
          changed = true;
        }
      }
    });

    // se non abbiamo fatto clamp, persistiamo comunque la nuova maxQty
    if (!changed) cart.save();
  } catch (e) {
    console.warn('[checkout] refreshInventoryAndClamp error', e);
  }
},


  }));
});
