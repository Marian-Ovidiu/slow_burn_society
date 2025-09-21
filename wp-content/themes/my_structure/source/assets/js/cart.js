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
    if (typeof v === 'boolean') return null;
    if (v === null || v === undefined || v === '') return null;
    const n = Number(v);
    return Number.isFinite(n) && n >= 0 ? n : null;
  };

  const isKitId = (id) => (typeof id === 'string' && id.startsWith('kit:'));

  // ---- NEW: normalizzazione item per merge/rehydrate
  const normalizeItem = (i) => {
    return {
      id: i.id,
      name: i.name,
      image: i.image,
      price: Number(i.price),
      basePrice: Number(i.basePrice ?? i.price ?? 0),
      qty: Number(i.qty || 1),
      ...(toNum(i.maxQty) != null ? { maxQty: toNum(i.maxQty) } : {})
    };
  };

  const pickMaxFromItem = (item) => {
    return (
      toNum(item?.maxQty) ??
      toNum(item?.stock) ??
      toNum(item?.availability) ??
      toNum(item?.available)
    );
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
      // item shape: { id, name, image, price, qty, maxQty? }
      items: data.items.map(i => normalizeItem(i)),
      token: null,

      ensureToken() {
        if (this.token) return this.token;
        let t = window.localStorage.getItem('cart_token');
        if (!t) {
          t = (crypto?.randomUUID?.()) || ('ct_' + Math.random().toString(36).slice(2) + '_' + Date.now());
          try { window.localStorage.setItem('cart_token', t); } catch { }
        }
        this.token = t;
        return t;
      },

      expiresAt: data.expiresAt || (data.items.length ? now() + TTL_MS : 0),

      // ---- NEW: rehydrate/merge con localStorage (anti-clobber multi-istanza)
      rehydrateFromStorage(merge = true) {
        const ls = loadFromStorage();
        if (!ls) return;
        const lsItems = Array.isArray(ls.items) ? ls.items : [];
        if (!merge) {
          this.items = lsItems.map(normalizeItem);
        } else {
          const map = new Map(this.items.map(i => [String(i.id), i]));
          lsItems.forEach(lsi => {
            const key = String(lsi.id);
            if (!map.has(key)) {
              this.items.push(normalizeItem(lsi));
            }
          });
        }
        // tieni il TTL più “lungo”
        if (ls.expiresAt) {
          this.expiresAt = Math.max(this.expiresAt || 0, ls.expiresAt || 0);
        }
        // Forza tick reattivo
        this.items = this.items.slice();
      },

      // --- TTL ---
      remainingMs() {
        void this._heartbeat;
        if (!this.expiresAt) return 0;
        return Math.max(0, this.expiresAt - Date.now());
      },
      remainingMinutes() { return Math.ceil(this.remainingMs() / 60000); },
      remainingSeconds() { return Math.max(0, Math.ceil(this.remainingMs() / 1000)); },
      remainingFormatted() {
        const s = this.remainingSeconds();
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return `${m}:${pad2(sec)}`;
      },
      isExpired() { return this.expiresAt && this.expiresAt <= now(); },
      touchExpiry() { this.expiresAt = now() + TTL_MS; },

      // --- Stock Helpers ---
      qtyFor(id) { const it = this.items.find(i => i.id === id); return it ? Number(it.qty) : 0; },
      maxFor(id) { const it = this.items.find(i => i.id === id); return toNum(it?.maxQty) ?? null; },
      remainingFor(id, totalStock) {
        const cap = toNum(totalStock) ?? this.maxFor(id);
        if (cap == null) return null;
        const inCart = this.qtyFor(id);
        return Math.max(0, cap - inCart);
      },

      // --- CRUD con limiti di stock ---
      add(item) {
        // 🔒 Merge difensivo con LS PRIMA di modificare (anti overwrite)
        this.rehydrateFromStorage(true);

        const price = Number(item.price);
        if (!Number.isFinite(price)) {
          console.warn('[cart] price non valido per item', item);
          return;
        }

        const capFromInput = pickMaxFromItem(item);
        const found = this.items.find(i => i.id === item.id);

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
          const payload = normalizeItem({ ...item, basePrice: item.basePrice ?? item.price, qty: initialQty });
          if (cap != null) payload.maxQty = cap;
          this.items.push(payload);
        }

        this.touchExpiry();

        // Forza reattività Alpine (array copy)
        this.items = this.items.slice();

        this.save();
      },

      setQty(id, qty) {
        const it = this.items.find(i => i.id === id);
        if (!it) return;

        if (isKitId(it.id)) {
          if (it.qty !== 1) it.qty = 1;
          this.items = this.items.slice();
          this.save();
          this.emitChanged?.();
          return;
        }

        let v = Math.max(1, Math.floor(Number(qty) || 1));
        const max = Number.isFinite(it.maxQty) ? it.maxQty : (Number.isFinite(it.stock) ? it.stock : null);
        if (max != null) v = Math.min(v, max);
        it.qty = v;

        this.items = this.items.slice();
        this.save();
        this.emitChanged?.();
      },

      remove(id) {
        this.items = this.items.filter(i => i.id !== id);
        if (this.items.length) this.touchExpiry();
        this.items = this.items.slice();
        this.save();
      },

      clear() {
        this.items = [];
        this.expiresAt = 0;
        this.items = this.items.slice();
        this.save();
      },

      // --- Totali ---
      lineSubtotal(item) { return Number(item.qty) * Number(item.price); },
      total() { return this.items.reduce((sum, i) => sum + (Number(i.qty) * Number(i.price)), 0); },
      lineSubtotalFormatted(item) { return formatMoney(this.lineSubtotal(item)); },
      totalFormatted() { return formatMoney(this.total()); },

      // --- Persistenza ---
      save() {
        // 🔒 Merge con LS anche prima di salvare (anti clobber)
        const existing = loadFromStorage();
        const existingItems = Array.isArray(existing?.items) ? existing.items : [];
        const map = new Map(this.items.map(i => [String(i.id), i]));
        existingItems.forEach(ei => {
          const key = String(ei.id);
          if (!map.has(key)) {
            this.items.push(normalizeItem(ei));
          }
        });

        // Protezione KIT: prezzo e qty fissi
        this.items.forEach(it => {
          if (isKitId(it.id)) {
            const p = Number(it.price);
            const bp = Number(it.basePrice);
            if ((!Number.isFinite(p) || p <= 0) && Number.isFinite(bp) && bp > 0) {
              it.price = bp;
            }
            if (it.qty !== 1) it.qty = 1;
          }
        });

        // TTL più lungo
        const exp = this.items.length ? Math.max(this.expiresAt || 0, existing?.expiresAt || 0) : 0;
        this.expiresAt = exp;

        const itemsToSave = this.items.map(i => {
          const out = { ...i };
          out.price = Number(out.price);
          out.basePrice = Number(Number.isFinite(Number(out.basePrice)) ? out.basePrice : out.price);
          if (toNum(out.maxQty) == null) delete out.maxQty;
          return out;
        });

        // ✅ persist
        saveToStorage(itemsToSave, exp);

        // 🔔 Notify
        window.dispatchEvent(new CustomEvent('cart:changed', {
          detail: { items: itemsToSave, expiresAt: exp, total: this.total() }
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
      _heartbeat: 0,
      _countdownTimerId: null,
      _startCountdownTicker() {
        if (this._countdownTimerId) clearInterval(this._countdownTimerId);
        this._countdownTimerId = setInterval(() => {
          this._heartbeat = Date.now();
        }, 1000);
      },
    });

    const store = Alpine.store('cart');
    store.ensureToken();
    store._startExpiryWatcher();
    store._startCountdownTicker();

    Alpine.store('cartReady', true);
    window.dispatchEvent(new CustomEvent('cart:ready'));

    // ---- opzionale: sincronizza quando cambia il LS da altre istanze (stessa tab)
    window.addEventListener('cart:changed', () => {
      // ricarica (merge) in memoria: evita discrepanze grafica/checkout
      store.rehydrateFromStorage(true);
    });
  };

  if (window.Alpine) init();
  else document.addEventListener('alpine:init', init);
})();

// Esempio toast su superamento stock
window.addEventListener('cart:stock_exceeded', (e) => {
  const { name, max } = e.detail;
  alert(`${name}: limite raggiunto (${max})`);
});
