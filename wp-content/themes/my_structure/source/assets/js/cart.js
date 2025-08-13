(function () {
  if (window.__cartStoreInitialized) return;
  window.__cartStoreInitialized = true;

  const LS_KEY = 'cart_v2';
  const TTL_MINUTES = 30;
  const TTL_MS = TTL_MINUTES * 60 * 1000;

  const now = () => Date.now();

  function safeParse(json, fallback) {
    try { return JSON.parse(json); } catch { return fallback; }
  }

  function loadFromStorage() {
    const raw = localStorage.getItem(LS_KEY);
    const data = safeParse(raw, null);
    if (!data || !Array.isArray(data.items)) {
      return { items: [], expiresAt: 0 };
    }
    return data;
  }

  function saveToStorage(items, expiresAt) {
    localStorage.setItem(LS_KEY, JSON.stringify({ items, expiresAt }));
  }

  function formatMoney(n) {
    // toFixed per UI; internamente usiamo Number
    return Number(n || 0).toFixed(2);
  }

  const init = () => {
    if (Alpine.store('cart')) return;

    Alpine.store('cartReady', false);

    const data = loadFromStorage();
    // se scaduto al load → svuota
    if (data.expiresAt && data.expiresAt <= now()) {
      saveToStorage([], 0);
      data.items = [];
      data.expiresAt = 0;
    }

    Alpine.store('cart', {
      // ogni item dovrebbe avere: { id, name, image, price } ; price come numero o stringa numerica
      items: data.items.map(i => ({ ...i, qty: Number(i.qty || 1) })),

      // timestamp di scadenza assoluta
      expiresAt: data.expiresAt || (data.items.length ? now() + TTL_MS : 0),

      // --- Helpers tempo / TTL ---
      remainingMs() {
        if (!this.expiresAt) return 0;
        return Math.max(0, this.expiresAt - now());
      },
      remainingMinutes() {
        return Math.ceil(this.remainingMs() / 60000);
      },
      isExpired() {
        return this.expiresAt && this.expiresAt <= now();
      },
      touchExpiry() {
        this.expiresAt = now() + TTL_MS;
      },

      // --- CRUD ---
      add(item) {
        // item atteso: { id, name, image, price }
        const price = Number(item.price);
        if (Number.isNaN(price)) {
          console.warn('[cart] price non valido per item', item);
          return;
        }
        const found = this.items.find(i => i.id === item.id);
        if (found) {
          found.qty++;
        } else {
          this.items.push({ id: item.id, name: item.name, image: item.image, price: price, qty: 1 });
        }
        this.touchExpiry();
        this.save();
      },

      setQty(id, qty) {
        qty = Math.max(0, Number(qty || 0));
        const it = this.items.find(i => i.id === id);
        if (!it) return;
        if (qty === 0) {
          this.remove(id);
          return;
        }
        it.qty = qty;
        this.touchExpiry();
        this.save();
      },

      remove(id) {
        this.items = this.items.filter(i => i.id !== id);
        if (this.items.length) this.touchExpiry();
        this.save();
      },

      clear() {
        this.items = [];
        this.expiresAt = 0;
        this.save();
      },

      // --- Computed ---
      lineSubtotal(item) {
        return Number(item.qty) * Number(item.price);
      },

      total() {
        return this.items.reduce((sum, i) => sum + (Number(i.qty) * Number(i.price)), 0);
      },

      // versioni formattate per UI
      lineSubtotalFormatted(item) { return formatMoney(this.lineSubtotal(item)); },
      totalFormatted() { return formatMoney(this.total()); },

      // --- Persistenza ---
      save() {
        // se vuoto, niente scadenza
        const exp = this.items.length ? this.expiresAt : 0;
        saveToStorage(this.items, exp);
      },

      // --- Timer scadenza ---
      _expiryTimerId: null,
      _startExpiryWatcher() {
        if (this._expiryTimerId) clearInterval(this._expiryTimerId);
        // check ogni 15s per essere leggeri
        this._expiryTimerId = setInterval(() => {
          if (this.isExpired()) {
            this.clear();
            window.dispatchEvent(new CustomEvent('cart:expired'));
          }
        }, 15000);
      }
    });

    // avvia watcher scadenza
    Alpine.store('cart')._startExpiryWatcher();
    Alpine.store('cartReady', true);
  };

  if (window.Alpine) init();
  else document.addEventListener('alpine:init', init);
})();

window.addEventListener('cart:expired', () => alert('Carrello scaduto 😴'));
