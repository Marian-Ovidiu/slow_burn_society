// resources/assets/js/cart.js
(function () {
  if (window.__cartStoreInitialized) return;
  window.__cartStoreInitialized = true;

  const LS_KEY = 'cart_v2';
  const TTL_MINUTES = 10;
  const TTL_MS = TTL_MINUTES * 60 * 1000;

  const now = () => Date.now();
  const pad2 = (n) => String(n).padStart(2, '0');

  const safeParse = (json, fallback) => { try { return JSON.parse(json); } catch { return fallback; } };

  function loadFromStorage() {
    const raw = localStorage.getItem(LS_KEY);
    const data = safeParse(raw, null);
    if (!data || !Array.isArray(data.items)) return { items: [], expiresAt: 0 };
    return data;
  }

  function saveToStorage(items, expiresAt) {
    localStorage.setItem(LS_KEY, JSON.stringify({ items, expiresAt }));
  }

  const formatMoney = (n) => Number(n || 0).toFixed(2);

  // Helpers numerici / stock
  const toNum = (v) => {
    if (typeof v === 'boolean') return null;
    if (v === null || v === undefined || v === '') return null;
    const n = Number(v);
    return Number.isFinite(n) && n >= 0 ? n : null;
  };

  const isKitId = (id) => (typeof id === 'string' && id.startsWith('kit:'));

  const pickMaxFromItem = (item) => (
    toNum(item?.maxQty) ??
    toNum(item?.stock) ??
    toNum(item?.availability) ??
    toNum(item?.available)
  );

  function init() {
    if (Alpine.store('cart')) return;

    // Flag globale "ready" separata
    Alpine.store('cartReady', false);

    const data = loadFromStorage();
    if (data.expiresAt && data.expiresAt <= now()) {
      saveToStorage([], 0);
      data.items = [];
      data.expiresAt = 0;
    }

    const canonicalId = (id) => {
      const s = String(id ?? '');
      // Per i kit DEVE già essere in forma 'kit:123'
      return s;
    };

    Alpine.store('cart', {
      // Stato
      items: (data.items || []).map(i => ({
        ...i,
        id: canonicalId(i.id),
        qty: Number(i.qty || 1),
        price: Number(i.price),
        basePrice: Number(i.basePrice ?? i.price),
        maxQty: toNum(i.maxQty) ?? undefined,
        _refreshTick: 0,
      })),
      token: null,

      // Interni
      _expiredHandled: false,
      _expiryTimerId: null,
      _countdownTimerId: null,
      _heartbeat: 0,

      // Token anonimo carrello
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

      // Scadenza
      expiresAt: data.expiresAt || ((data.items || []).length ? now() + TTL_MS : 0),

      remainingMs() { void this._heartbeat; return this.expiresAt ? Math.max(0, this.expiresAt - Date.now()) : 0; },
      remainingSeconds() { return Math.max(0, Math.ceil(this.remainingMs() / 1000)); },
      remainingMinutes() { return Math.ceil(this.remainingMs() / 60000); },
      remainingFormatted() {
        const s = this.remainingSeconds();
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return `${m}:${pad2(sec)}`;
      },
      isExpired() { return this.expiresAt && this.expiresAt <= now(); },
      touchExpiry() { this.expiresAt = now() + TTL_MS; this._expiredHandled = false; },

      // Helpers stock
      qtyFor(id) { const it = this.items.find(i => String(i.id) === String(id)); return it ? Number(it.qty) : 0; },
      maxFor(id) { const it = this.items.find(i => String(i.id) === String(id)); return toNum(it?.maxQty) ?? null; },
      remainingFor(id, totalStock) {
        const cap = toNum(totalStock) ?? this.maxFor(id);
        if (cap == null) return null;
        const inCart = this.qtyFor(id);
        return Math.max(0, cap - inCart);
      },

      // CRUD
      add(item) {
        const incomingId = canonicalId(item.id);
        const price = Number(item.price);
        if (!Number.isFinite(price)) { console.warn('[cart] price non valido', item); return; }

        this._hydrateFromStorageSafely();

        const capFromInput = pickMaxFromItem(item);
        const found = this.items.find(i => String(i.id) === String(incomingId));

        const capExisting = toNum(found?.maxQty);
        const cap = capExisting ?? capFromInput ?? null;

        if (found) {
          const next = found.qty + 1;
          if (cap != null && next > cap) {
            window.dispatchEvent(new CustomEvent('cart:stock_exceeded', {
              detail: { id: incomingId, name: found.name, max: cap }
            }));
            return;
          }
          found.qty = next;
          if (capExisting == null && capFromInput != null) found.maxQty = capFromInput;
        } else {
          const initialQty = 1;
          if (cap != null && initialQty > cap) {
            window.dispatchEvent(new CustomEvent('cart:stock_exceeded', {
              detail: { id: incomingId, name: item.name, max: cap }
            }));
            return;
          }
          const payload = {
            id: incomingId,
            name: item.name,
            image: item.image,
            price,
            basePrice: price,
            qty: initialQty
          };
          if (cap != null) payload.maxQty = cap;
          this.items.push(payload);
        }

        this.touchExpiry();
        this.items = this.items.slice();
        this.save();
        this._refreshTick = Date.now();
      },
      remove(id) {
        this.items = this.items.filter(i => String(i.id) !== String(id));
        if (this.items.length) this.touchExpiry(); else this.expiresAt = 0;
        this._expiredHandled = false;
        this.items = this.items.slice();
        this.save();
        this._refreshTick = Date.now();
      },

      clear() {
        this.items = [];
        this.expiresAt = 0;
        this._expiredHandled = true; // evita doppio fire
        this.items = this.items.slice();
        this.save();
        this._refreshTick = Date.now();
      },

      // Totali
      lineSubtotal(item) { return Number(item.qty) * Number(item.price); },
      total() { return this.items.reduce((sum, i) => sum + (Number(i.qty) * Number(i.price)), 0); },
      lineSubtotalFormatted(item) { return formatMoney(this.lineSubtotal(item)); },
      totalFormatted() { return formatMoney(this.total()); },

      // Persistenza + eventi
      save() {
        // Protezione KIT
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

        const exp = this.items.length ? this.expiresAt : 0;

        const itemsToSave = this.items.map(i => {
          const out = { ...i };
          out.price = Number(out.price);
          out.basePrice = Number.isFinite(Number(out.basePrice)) ? Number(out.basePrice) : Number(out.price);
          if (toNum(out.maxQty) == null) delete out.maxQty;
          return out;
        });

        saveToStorage(itemsToSave, exp);
        this.emitChanged();
      },

      emitChanged() {
        window.dispatchEvent(new CustomEvent('cart:changed', {
          detail: { items: this.items, expiresAt: this.expiresAt, total: this.total() }
        }));
      },

      _hydrateFromStorageSafely() {
        const data = (function () {
          try { return JSON.parse(localStorage.getItem(LS_KEY) || ''); } catch { return null; }
        })();
        const lsItems = Array.isArray(data?.items) ? data.items : [];
        if (!lsItems.length) return;

        const byId = new Map(this.items.map(it => [String(it.id), it]));
        lsItems.forEach(raw => {
          const key = String(raw.id);
          if (!byId.has(key)) {
            byId.set(key, {
              ...raw,
              id: canonicalId(raw.id),
              qty: Number(raw.qty || 1),
              price: Number(raw.price || 0),
              basePrice: Number(raw.basePrice ?? raw.price ?? 0),
              maxQty: toNum(raw.maxQty) ?? undefined
            });
          }
        });

        this.items = Array.from(byId.values());
      },

      // Timer scadenza
      _startExpiryWatcher() {
        if (this._expiryTimerId) clearInterval(this._expiryTimerId);
        this._expiryTimerId = setInterval(() => {
          if (!this._expiredHandled && this.isExpired()) {
            this.clear();
            window.dispatchEvent(new CustomEvent('cart:expired'));
            this._refreshTick = Date.now();
          }
        }, 15000);
      },

      _startCountdownTicker() {
        if (this._countdownTimerId) clearInterval(this._countdownTimerId);
        this._countdownTimerId = setInterval(() => {
          this._heartbeat = Date.now(); // aggiorna UI timer
          if (!this._expiredHandled && this.isExpired()) {
            this.clear();
            window.dispatchEvent(new CustomEvent('cart:expired'));
            this._refreshTick = Date.now();
          }
        }, 1000);
      },
    });

    const store = Alpine.store('cart');
    store.ensureToken();
    store._startExpiryWatcher();
    store._startCountdownTicker();

    Alpine.store('cartReady', true);
    window.dispatchEvent(new CustomEvent('cart:ready'));
    // Primo “changed” per allineare tutte le UI che ascoltano
    store.emitChanged();
  }

  if (window.Alpine) init();
  else document.addEventListener('alpine:init', init);
})();

// Esempio toast su superamento stock (customizza come vuoi)
window.addEventListener('cart:stock_exceeded', (e) => {
  const { name, max } = e.detail || {};
  Toastify({
    text: `${name}: limite raggiunto (${max})`,
    duration: 3500,
    gravity: "top",
    position: "right",
    close: true,
    stopOnFocus: true,
    style: {
      background: "rgba(245, 158, 11, .95)", // amber
      color: "#fff",
      borderRadius: "10px",
      backdropFilter: "blur(6px)"
    }
  }).showToast();
});
