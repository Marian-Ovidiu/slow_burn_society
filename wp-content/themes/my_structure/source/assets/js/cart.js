// resources/assets/js/cart.js
(function () {
  if (window.__cartStoreInitialized) return;
  window.__cartStoreInitialized = true;

  const LS_KEY = 'cart_v2';
  const TTL_MINUTES = 10;
  const TTL_MS = TTL_MINUTES * 60 * 1000;

  const now = () => Date.now();
  const pad2 = (n) => String(n).padStart(2, '0');

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
    return Number(n || 0).toFixed(2);
  }

  // Helpers stock
  const toNum = (v) => {
    const n = Number(v);
    return Number.isFinite(n) && n >= 0 ? n : null;
  };
  const pickMaxFromItem = (item) => {
    // supporta più nomi: maxQty | stock | available | availability
    return toNum(item?.maxQty) ?? toNum(item?.stock) ?? toNum(item?.available) ?? toNum(item?.availability);
  };

  const init = () => {
    if (Alpine.store('cart')) return;

    Alpine.store('cartReady', false);

    const data = loadFromStorage();
    if (data.expiresAt && data.expiresAt <= now()) {
      saveToStorage([], 0);
      data.items = [];
      data.expiresAt = 0;
    }

    Alpine.store('cart', {
      // item shape: { id, name, image, price, qty, maxQty? }  (maxQty solo se finito)
      items: data.items.map(i => ({
        ...i,
        qty: Number(i.qty || 1),
        price: Number(i.price),
        maxQty: toNum(i.maxQty) ?? undefined
      })),

      expiresAt: data.expiresAt || (data.items.length ? now() + TTL_MS : 0),

      // --- TTL ---
      remainingMs() {
        void this._heartbeat; // dipendenza reattiva: forza ricalcolo ogni tick
        if (!this.expiresAt) return 0;
        return Math.max(0, this.expiresAt - Date.now());
      },
      remainingMinutes() {
        return Math.ceil(this.remainingMs() / 60000); // <- FIX: 60.000 ms = 1 min
      },
      remainingSeconds() {
        return Math.max(0, Math.ceil(this.remainingMs() / 1000));
      },
      remainingFormatted() { // "m:ss"
        const s = this.remainingSeconds();
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return `${m}:${pad2(sec)}`;
      },
      isExpired() {
        return this.expiresAt && this.expiresAt <= now();
      },
      touchExpiry() {
        this.expiresAt = now() + TTL_MS;
      },

      // --- Stock Helpers (per UI dinamica) ---
      qtyFor(id) {
        const it = this.items.find(i => i.id === id);
        return it ? Number(it.qty) : 0;
      },
      maxFor(id) {
        const it = this.items.find(i => i.id === id);
        return toNum(it?.maxQty) ?? null; // null = illimitato / non noto
      },
      /**
       * Ritorna quanta disponibilità resta per un prodotto.
       * - Se passi totalStock (numero), usa quello come cap totale.
       * - Altrimenti tenta l'item.maxQty salvato nel carrello.
       * Ritorna un numero >= 0, oppure null se illimitato/non noto.
       */
      remainingFor(id, totalStock) {
        const cap = toNum(totalStock) ?? this.maxFor(id);
        if (cap == null) return null; // illimitato
        const inCart = this.qtyFor(id);
        return Math.max(0, cap - inCart);
      },

      // --- CRUD con limiti di stock ---
      add(item) {
        // item atteso: { id, name, image, price, [stock|maxQty|available|availability] }
        const price = Number(item.price);
        if (!Number.isFinite(price)) {
          console.warn('[cart] price non valido per item', item);
          return;
        }

        const capFromInput = pickMaxFromItem(item);   // cap totale (se fornito)
        const found = this.items.find(i => i.id === item.id);

        // cap effettivo:
        // 1) se l'item in cart ha già un maxQty finito → usa quello
        // 2) altrimenti, se add() riceve un cap finito → usalo e salvalo
        // 3) altrimenti cap = illimitato (null)
        const capExisting = toNum(found?.maxQty);
        const cap = capExisting ?? capFromInput ?? null;

        if (found) {
          const next = found.qty + 1;
          if (cap != null && next > cap) {
            window.dispatchEvent(new CustomEvent('cart:stock_exceeded', {
              detail: { id: item.id, name: found.name, max: cap }
            }));
            return;
          }
          found.qty = next;
          if (capExisting == null && capFromInput != null) found.maxQty = capFromInput;
        } else {
          const initialQty = 1;
          if (cap != null && initialQty > cap) {
            window.dispatchEvent(new CustomEvent('cart:stock_exceeded', {
              detail: { id: item.id, name: item.name, max: cap }
            }));
            return;
          }
          const payload = {
            id: item.id,
            name: item.name,
            image: item.image,
            price,
            qty: initialQty
          };
          if (cap != null) payload.maxQty = cap;
          this.items.push(payload);
        }

        this.touchExpiry();
        this.save();
      },

      setQty(id, qty) {
        qty = Math.max(0, Number(qty || 0));
        const it = this.items.find(i => i.id === id);
        if (!it) return;

        const cap = toNum(it.maxQty);
        if (cap != null && qty > cap) {
          qty = cap; // clamp
          window.dispatchEvent(new CustomEvent('cart:stock_exceeded', {
            detail: { id, name: it.name, max: cap }
          }));
        }

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

      // --- Totali ---
      lineSubtotal(item) {
        return Number(item.qty) * Number(item.price);
      },
      total() {
        return this.items.reduce((sum, i) => sum + (Number(i.qty) * Number(i.price)), 0);
      },
      lineSubtotalFormatted(item) { return formatMoney(this.lineSubtotal(item)); },
      totalFormatted() { return formatMoney(this.total()); },

      // --- Persistenza ---
      save() {
        const exp = this.items.length ? this.expiresAt : 0;
        const itemsToSave = this.items.map(i => {
          const out = { ...i };
          if (toNum(out.maxQty) == null) delete out.maxQty; // non salvare null/undefined
          return out;
        });
        saveToStorage(itemsToSave, exp);

        // Notifica cambiamenti del carrello (checkout ricalcola PI)
        window.dispatchEvent(new CustomEvent('cart:changed', {
          detail: {
            items: itemsToSave,
            expiresAt: exp,
            total: this.total()
          }
        }));
      },

      // --- Timer scadenza ---
      _expiryTimerId: null,
      _startExpiryWatcher() {
        if (this._expiryTimerId) clearInterval(this._expiryTimerId);
        this._expiryTimerId = setInterval(() => {
          if (this.isExpired()) {
            this.clear();
            window.dispatchEvent(new CustomEvent('cart:expired'));
          }
        }, 15000);
      },
      _heartbeat: 0,            // proprietà reattiva "vuota"
      _countdownTimerId: null,
      _startCountdownTicker() {
        if (this._countdownTimerId) clearInterval(this._countdownTimerId);
        this._countdownTimerId = setInterval(() => {
          // aggiorno una prop reattiva così Alpine ricalcola i binding
          this._heartbeat = Date.now();
        }, 1000);
      },
    });

    Alpine.store('cart')._startExpiryWatcher();
    Alpine.store('cart')._startCountdownTicker();
    Alpine.store('cartReady', true);
    window.dispatchEvent(new CustomEvent('cart:ready'));
  };

  if (window.Alpine) init();
  else document.addEventListener('alpine:init', init);
})();

// Esempio toast su superamento stock (customizza come vuoi)
window.addEventListener('cart:stock_exceeded', (e) => {
  const { name, max } = e.detail;
  alert(`${name}: limite raggiunto (${max})`);
});
