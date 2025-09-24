   {{-- Prodotti correlati --}}
 @if (!empty($relatedItems))
        <section class="mt-8" x-data>
            <h2 id="related-title" class="text-lg font-bold tracking-tight my-4">Prodotti correlati</h2>
            <ul class="grid gap-4 sm:gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4" role="list">
                @foreach ($relatedItems as $r)
                    @php
                        $isProduct = ($r['entity'] ?? ($r['type'] ?? 'kit')) === 'product';
                        $rid = (int) ($r['id'] ?? 0);
                        $href = (string) ($r['permalink'] ?? '#');
                        $img = $isProduct ? $r['immagine_1']['url'] ?? ($r['image'] ?? '') : $r['image'] ?? '';
                        $price = (float) ($r['price'] ?? 0);
                        $priceFormatted = $r['price_formatted'] ?? number_format($price, 2, ',', '.');
                        $stock = (int) ($r['disponibilita'] ?? 0);
                        $available = (bool) ($r['available'] ?? $stock > 0);
                        $payload = $r['cart'] ?? [
                            'id' => $isProduct ? $rid : 'kit:' . $rid,
                            $isProduct ? 'productId' : 'kitId' => $rid,
                            'type' => $isProduct ? 'product' : 'kit',
                            'name' => (string) ($r['title'] ?? ''),
                            'image' => (string) $img,
                            'price' => (float) $price,
                            'qty' => 1,
                            'maxQty' => $stock,
                        ];
                    @endphp

                    <li>
                        <article
                            class="rounded-lg border border-white/10 bg-white/10 backdrop-blur p-3 h-full flex flex-col text-white transition hover:bg-white/15">
                            <a href="{{ $href }}" class="block">
                                <img src="{{ $img }}" alt="{{ $r['title'] ?? '' }}"
                                    class="w-full h-40 object-contain rounded mb-2 bg-white" loading="lazy"
                                    width="560" height="320"
                                    sizes="(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw">
                            </a>

                            <h3 class="text-sm font-semibold line-clamp-2">
                                <a href="{{ $href }}" class="hover:underline">{{ $r['title'] ?? '' }}</a>
                            </h3>

                            <p class="mt-1 text-sm text-white/90">€ {{ $priceFormatted }}</p>

                            <span class="text-[11px] mt-0.5 {{ $available ? 'text-green-300' : 'text-red-300' }}">
                                {{ $available ? 'Disponibile' : 'Non disponibile' }}
                                <span class="opacity-80">— Qtà: {{ $stock }}</span>
                                @if ($isProduct)
                                    <span class="opacity-70"> (in carrello: <span
                                            x-text="cartQtyOf({{ $rid }})"></span>)</span>
                                @endif
                            </span>

                            <button type="button"
                                class="mt-auto w-full text-xs font-semibold py-2 rounded transition
         {{ $available ? 'bg-[#45752c] text-white hover:bg-[#386322] focus:outline-none focus:ring-2 focus:ring-white/30' : 'bg-white/15 text-white cursor-not-allowed disabled:opacity-60' }}"
                                @if ($available) :disabled="{{ $isProduct ? 'isProductMaxed(' . $rid . ', ' . $stock . ')' : 'false' }}"
    @click.stop.prevent="addRecommendedToCart(@js($payload))"
  @else
    disabled @endif>
                                Aggiungi
                            </button>
                        </article>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

@push('before_alpine')
<script>
  // Factory unica per product e kit
  globalThis.productPage = function({ initial, maxQty, items }) {
    const safeCart = () => {
      try { return (globalThis.Alpine && Alpine.store) ? (Alpine.store('cart') || null) : null; }
      catch { return null; }
    };

    // Normalizza l'input (sia per product che per kit)
    const entity = {
      id: Number(initial?.id || 0),
      title: String(initial?.title || ''),
      price: Number(initial?.price || 0),
      image: String(initial?.image || ''),
      gallery: Array.isArray(initial?.gallery) ? initial.gallery : [],
      description: String(initial?.description || ''),
      cart: initial?.cart || null,
    };

    return {
      // alias per compatibilità con entrambi i template
      entity,
      get product(){ return this.entity; },
      get kit(){ return this.entity; },

      itemsList: Array.isArray(items) ? items : [],

      qty: 1,
      maxQty: Number(maxQty || 0),

      get inStock(){ return Number.isFinite(this.maxQty) && this.maxQty > 0; },

      descriptionHtml(){ return this.entity.description; },
      priceFormatted(){ return Number(this.entity.price || 0).toFixed(2).replace('.', ','); },
      fmtPrice(n){ return Number(n||0).toFixed(2).replace('.', ','); },

      increment(){ if (this.inStock) this.qty = this.maxQty ? Math.min(this.qty + 1, this.maxQty) : this.qty + 1; },
      decrement(){ this.qty = Math.max(1, this.qty - 1); },

      addToCart(payload){
        const cart = safeCart();
        if (!cart || typeof cart.add !== 'function') return;

        const stock = Number(this.maxQty || 0);
        const rem = (typeof cart.remainingFor === 'function')
          ? Number(cart.remainingFor(this.entity.id, stock) ?? stock)
          : Math.max(0, stock);
        if (rem <= 0) return;

        const want = Math.max(1, Number(this.qty || 1));
        const canAdd = Math.min(want, rem);

        const current = (typeof cart.qtyOf === 'function')
          ? Number(cart.qtyOf(this.entity.id) || 0)
          : Number((cart.items?.find(i => Number(i.id) === Number(this.entity.id))?.qty) || 0);

        if (current === 0) cart.add(payload);

        const target = Math.min(current + canAdd, stock);
        if (typeof cart.setQty === 'function') {
          cart.setQty(this.entity.id, target);
        } else {
          const extra = Math.max(0, target - Math.max(current, 1));
          for (let i = 0; i < extra; i++) cart.add(payload);
        }
      }
    };
  };

  // Helpers globali usati nei correlati
  window.cartQtyOf = (id) => {
    try {
      const cart = Alpine?.store?.('cart'); if (!cart) return 0;
      if (typeof cart.qtyOf === 'function') return Number(cart.qtyOf(id) || 0);
      return Number((cart.items?.find(i => Number(i.id) === Number(id))?.qty) || 0);
    } catch { return 0; }
  };

  window.isProductMaxed = (id, max) => {
    const m = Number(max ?? 0);
    if (m <= 0) return true;
    return window.cartQtyOf(id) >= m;
  };

  window.remainingForUI = (id, max) => {
    try {
      const cart = Alpine?.store?.('cart'); const m = Number(max ?? 0);
      if (!cart) return m;
      if (typeof cart.remainingFor === 'function') {
        const r = Number(cart.remainingFor(id, m)); return Number.isFinite(r) ? r : 0;
      }
      const current = (typeof cart.qtyOf === 'function')
        ? Number(cart.qtyOf(id) || 0)
        : Number((cart.items?.find(i => Number(i.id) === Number(id))?.qty) || 0);
      return Math.max(0, m - current);
    } catch { return Number(max ?? 0); }
  };

  window.addRecommendedToCart = (payload) => {
    try {
      const cart = Alpine?.store?.('cart');
      if (!cart || typeof cart.add !== 'function') return;
      const p = { ...payload };
      if (p.type === 'kit') {
        if (!String(p.id || '').startsWith('kit:')) p.id = `kit:${p.kitId ?? p.id}`;
      } else {
        p.type = 'product';
        p.id = Number(p.id ?? p.productId ?? p.product_id ?? 0);
      }
      p.qty = Number(p.qty ?? 1);
      cart.add(p);
    } catch (e) { console.error('[recommended:add] errore', e); }
  };
</script>
@endpush
