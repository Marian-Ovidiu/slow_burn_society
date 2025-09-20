<!-- MODALE con max-h 50vh -->
<div x-show="modalOpen2" x-cloak x-transition
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4"
     role="dialog" aria-modal="true" aria-labelledby="modal2-title"
     @keydown.escape.window="modalOpen2 = false; selected2 = null"
     @click.self="modalOpen2 = false; selected2 = null">

  <div class="bg-white w-full max-w-md p-6 rounded relative max-h-[50vh] overflow-y-auto"
       @click.away="modalOpen2 = false; selected2 = null">

    <button class="absolute top-2 right-2 text-gray-500 hover:text-black"
            @click="modalOpen2 = false; selected2 = null"
            aria-label="Chiudi modale" type="button">✕</button>

    <template x-if="selected2">
      <div>
        <h2 id="modal2-title" class="text-xl font-bold mb-2"
            x-text="selected2.title ?? selected2.name"></h2>

        <!-- Galleria (se presente) -->
        <template x-if="Array.isArray(selected2.gallery) && selected2.gallery.length">
          <div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide">
            <template x-for="(img,i) in selected2.gallery" :key="i">
              <img :src="img"
                   class="w-24 h-24 object-cover rounded border cursor-pointer hover:scale-105 transition"
                   @click="selectedImage2 = img"
                   :alt="'Anteprima ' + (i + 1)" loading="lazy">
            </template>
          </div>
        </template>

        <!-- Altrimenti immagine singola (limitata a ~20vh) -->
        <template x-if="!(Array.isArray(selected2.gallery) && selected2.gallery.length)">
          <img :src="selected2?.image || ''" alt="Immagine prodotto"
               class="w-full max-h-[20vh] object-contain rounded mb-4">
        </template>

        <p class="text-green-600 font-semibold text-lg mb-2">
          €<span x-text="Number(selected2?.price || 0).toFixed(2).replace('.', ',')"></span>
        </p>

        <p class="text-sm mb-3 text-gray-700" x-html="selected2?.description || ''"></p>

        <dl class="text-xs text-gray-500 mb-3 space-y-1">
          <div x-show="Number.isFinite(Number(selected2?.stock))">
            <dt class="inline font-semibold">Disponibilità:</dt>
            <dd class="inline ml-1"
                x-text="$store?.cart?.remainingFor
                         ? $store.cart.remainingFor(selected2?.id ?? selected2?.cart?.id, Number(selected2?.stock))
                         : (Number(selected2?.disponibilita) || 0)">
            </dd>
          </div>
          <div x-show="selected2?.category">
            <dt class="inline font-semibold">Categoria:</dt>
            <dd class="inline ml-1" x-text="selected2.category"></dd>
          </div>
          <div x-show="selected2?.brand">
            <dt class="inline font-semibold">Brand:</dt>
            <dd class="inline ml-1" x-text="selected2.brand"></dd>
          </div>
        </dl>

        <template x-if="selected2?.products && selected2.products.length">
          <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-800 mb-2">Contenuto del Kit</h3>
            <div class="grid gap-2 sm:grid-cols-2">
              <template x-for="(product, index) in selected2.products" :key="index">
                <div class="flex items-start gap-2">
                  <img :src="product.image ?? ''" alt=""
                       class="w-10 h-10 object-cover rounded border bg-gray-100">
                  <div class="flex-1">
                    <span class="block text-[12px] text-gray-700 leading-snug"
                          x-text="product.title"></span>
                    <p class="mt-0.5 text-[11px]"
                       :class="(product.available ?? (Number(product.disponibilita) > 0))
                                ? 'text-green-600' : 'text-red-600'">
                      <span x-text="(product.available ?? (Number(product.disponibilita) > 0))
                                      ? 'Disponibile' : 'Non disponibile'"></span>
                    </p>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </template>

        <button type="button"
                class="mt-2 w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322] transition
                       disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="!Boolean(selected2?.disponibilita) || isInCart(selected2?.cart || {})"
                @click.stop="addToCart(selected2?.cart || {})">
          <span x-text="isInCart(selected2?.cart || {}) ? 'Nel carrello' : 'Aggiungi al carrello'"></span>
        </button>
      </div>
    </template>
  </div>
</div>

<!-- LIGHTBOX (opzionale) -->
<template x-if="selectedImage2">
  <div class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
       @click="selectedImage2 = null" role="dialog" aria-modal="true">
    <img :src="selectedImage2" class="max-w-full max-h-[90vh] rounded shadow-xl"
         alt="Zoom immagine prodotto">
  </div>
</template>
