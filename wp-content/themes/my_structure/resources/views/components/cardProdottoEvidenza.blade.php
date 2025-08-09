<section
  x-data="{
    modalOpen:false,
    selected:null,
    selectedImage:null,
    addToCart(item){
      console.log('Aggiunto al carrello:', item);
      alert(`${item.title} è stato aggiunto al carrello!`);
    }
  }"
  class="relative px-4 py-8"
  id="shop"
  aria-labelledby="shop-title"
>
  <!-- Titolo sezione -->
  <header class="mb-8 text-center">
    <h2 class="section-title" id="shop-title">Shop</h2>
  </header>

  <!-- Lista prodotti (semantica) -->
  <ul class="product-grid" role="list">
    @foreach ($products as $product)
      @php
        $productForJs = [
          'title' => $product->title,
          'price' => $product->prezzo,
          'image' => $product->immagine_1['url'] ?? '',
          'description' => $product->descrizione ?? '',
          'gallery' => array_filter([
            $product->immagine_1['url'] ?? null,
            $product->immagine_2['url'] ?? null,
            $product->immagine_3['url'] ?? null,
            $product->immagine_4['url'] ?? null,
          ]),
          'availability' => $product->disponibilita ?? 'Disponibile',
          'category' => $product->categoria ?? null,
          'brand' => $product->brand ?? null,
        ];
      @endphp

      <li>
        <article
          class="product-card"
          @click="modalOpen = true; selected = {{ json_encode($productForJs) }}; selectedImage = null"
        >
          <img
            src="{{ $product->immagine_1['url'] ?? '' }}"
            alt="{{ $product->title }}"
            class="product-image"
            loading="lazy"
          >

          <h3
            class="product-title"
            x-data
            x-init="$el.innerText = '{{ addslashes($product->title) }}'.length > 35 ? '{{ addslashes($product->title) }}'.slice(0,35)+'…' : '{{ addslashes($product->title) }}'"
            title="{{ $product->title }}"
          ></h3>

          <p class="product-card-details text-xs" title="{{ strip_tags($product->descrizione) }}">
            {{ \Illuminate\Support\Str::limit(strip_tags($product->descrizione), 55) }}
          </p>

          <p class="product-price">€{{ $product->prezzo }}</p>

          <button
            type="button"
            class="mt-2 w-full bg-[#45752c] text-white text-xs py-1.5 rounded hover:bg-[#386322] transition"
            @click.stop="addToCart({ title: '{{ addslashes($product->title) }}', price: '{{ $product->prezzo }}' })"
            aria-label="Aggiungi {{ $product->title }} al carrello"
          >
            Aggiungi al carrello
          </button>
        </article>
      </li>
    @endforeach
  </ul>

  <!-- Modale -->
  <div
    x-show="modalOpen"
    x-cloak
    x-transition
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
    @keydown.escape.window="modalOpen=false"
  >
    <div
      class="bg-white w-full max-w-md p-6 rounded relative max-h-[95vh] overflow-y-auto"
      @click.away="modalOpen=false"
    >
      <button
        class="absolute top-2 right-2 text-gray-500"
        @click="modalOpen=false"
        aria-label="Chiudi modale"
        type="button"
      >✕</button>

      <template x-if="selected">
        <div>
          <h2 class="text-xl font-bold mb-2" x-text="selected.title"></h2>

          <!-- Galleria -->
          <div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide">
            <template x-for="(img,i) in selected.gallery" :key="i">
              <img
                :src="img"
                class="w-24 h-24 object-cover rounded border cursor-pointer hover:scale-105 transition"
                @click="selectedImage = img"
                :alt="'Anteprima '+(i+1)"
                loading="lazy"
              >
            </template>
          </div>

          <p class="text-green-600 font-semibold text-lg mb-2">
            €<span x-text="selected.price"></span>
          </p>

          <p class="text-sm mb-3 text-gray-700" x-html="selected.description"></p>

          <!-- Info extra -->
          <dl class="text-xs text-gray-500 mb-3 space-y-1">
            <div x-show="selected.availability">
              <dt class="inline font-semibold">Disponibilità:</dt>
              <dd class="inline" x-text="selected.availability"></dd>
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

          <button
            type="button"
            class="mt-4 w-full bg-[#45752c] text-white py-2 rounded hover:bg-[#386322] transition"
            @click="addToCart({ title: selected.title, price: selected.price })"
          >
            Aggiungi al carrello
          </button>
        </div>
      </template>
    </div>
  </div>

  <!-- Lightbox -->
  <template x-if="selectedImage">
    <div
      class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
      @click="selectedImage=null"
      role="dialog"
      aria-modal="true"
    >
      <img :src="selectedImage" class="max-w-full max-h-[90vh] rounded shadow-xl" alt="Zoom immagine prodotto">
    </div>
  </template>
</section>
