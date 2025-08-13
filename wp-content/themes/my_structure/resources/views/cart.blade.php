<div x-data x-init="$watch(() => $store.cart.items, () => {})">
  <template x-if="$store.cartReady">
    <div>
      <div class="text-sm">
        <span x-text="$store.cart.remainingMinutes() > 0 ? 'Scade in ' + $store.cart.remainingMinutes() + ' min' : 'Nessun carrello attivo'"></span>
      </div>

      <template x-for="item in $store.cart.items" :key="item.id">
        <div class="flex items-center gap-3 py-2 border-b">
          <img :src="item.image" alt="" class="w-12 h-12 object-cover rounded" />
          <div class="flex-1">
            <div class="font-medium" x-text="item.name"></div>
            <div class="text-xs opacity-70">€ <span x-text="Number(item.price).toFixed(2)"></span> cad.</div>
          </div>
          <input type="number" min="1" class="w-16 border rounded px-2 py-1"
                 :value="item.qty"
                 @input="$store.cart.setQty(item.id, $event.target.value)" />
          <div class="w-20 text-right font-semibold">
            € <span x-text="$store.cart.lineSubtotalFormatted(item)"></span>
          </div>
          <button class="ml-2 text-red-600" @click="$store.cart.remove(item.id)">✕</button>
        </div>
      </template>

      <div class="flex justify-between py-3 text-lg font-bold">
        <span>Totale</span>
        <span>€ <span x-text="$store.cart.totalFormatted()"></span></span>
      </div>
    </div>
  </template>
</div>
