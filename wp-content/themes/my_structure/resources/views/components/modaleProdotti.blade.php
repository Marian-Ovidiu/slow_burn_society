<!-- Modale (max-h 50vh) -->
<div x-show="modalOpen" x-cloak x-transition
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4"
     role="dialog" aria-modal="true" aria-labelledby="modal-title"
     @keydown.escape.window="closeModal()" @click.self="closeModal()">

  <div class="bg-white w-full max-w-md p-6 rounded relative max-h-[50vh] overflow-y-auto"
       @click.away="closeModal()">

    <button class="absolute top-2 right-2 text-gray-500 hover:text-black"
            @click="closeModal()" aria-label="Chiudi modale" type="button">✕</button>

    <template x-if="selected">
      <div>
        <h2 id="modal-title" class="text-xl font-bold mb-2"
            x-text="selected.title ?? selected.name"></h2>

        <!-- Galleria (scroll orizzontale) -->
        <div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide"
             x-show="Array.isArray(selected.gallery) && selected.gallery.length">
          <template x-for="(img,i) in selected.gallery" :key="i">
            <img :src="img"
                 class="w-24 h-24 object-cover rounded border cursor-pointer hover:scale-105 transition"
                 @click="selectImage(img)" :alt="'Anteprima ' + (i + 1)" loading="lazy">
          </template>
        </div>

        <!-- Immagine singola fallback (limitata) -->
        <template x-if="!(Array.isArray(selected.gallery) && selected.gallery.length)">
          <img :src="selected?.image || ''" alt="Immagine prodotto"
               class="w-full max-h-[20vh] object-contain rounded mb-4">
        </template>

        <p class="text-green-600 font-semibold text-lg mb-2">
          €<span x-text="Number(selected.price).toFixed(2)"></span>
        </p>

        <p class="text-sm mb-3 text-gray-700" x-html="selected.description"></p>

        <!-- Info extra -->
        <dl class="text-xs text-gray-500 mb-3 space-y-1">
          <div x-show="selected.availability">
            <dt class="inline font-semibold">Disponibilità:</dt>
            <dd class="inline"
                x-text="'Disponibilità: ' + $store.cart.remainingFor(selected?.id, Number(selected?.stock))"></dd>
          </div>
          <div x-show="selected.category">
            <dt class="inline font-semibold">Categoria:</dt>
            <dd class="inline" x-text="selected.category"></dd>
          </div>
          <div x-show="selected.brand">
            <dt class="inline font-semibold">Brand:</dt>
            <dd class="inline" x-text="selected.brand"></dd>
          </div>
        </dl>

        <button type="button"
                class="mt-4 w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322] transition disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="Number.isFinite(Number(selected?.stock)) &&
                           $store.cart.remainingFor(selected.id, Number(selected.stock)) === 0"
                @click="addSelectedToCart()">
          Aggiungi al carrello
        </button>
      </div>
    </template>
  </div>
</div>

<!-- Lightbox -->
<template x-if="selectedImage">
  <div class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
       @click="selectImage(null)" role="dialog" aria-modal="true">
    <img :src="selectedImage" class="max-w-full max-h-[90vh] rounded shadow-xl"
         alt="Zoom immagine prodotto">
  </div>
</template>
