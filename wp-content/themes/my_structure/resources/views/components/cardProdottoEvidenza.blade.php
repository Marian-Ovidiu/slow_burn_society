@php
 /***
  *  @var $data[] Core\Bases\BaseGroupAcf\OpzioniProdottoFields
  */   
@endphp
<section class="px-4 md:px-8 lg:px-16 py-6">
    @if(isset($data))
        <h2 class="text-xl md:text-2xl font-bold mb-4">
            {!! $prodottoFields->titolo ?? 'Prodotti in evidenza 🔥' !!}
        </h2>
    @endif

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
        @foreach ($products as $product)
            <div class="bg-white rounded-xl shadow p-4 text-center cursor-pointer transition hover:shadow-md"
                @click="modalOpen = true; selected = {{ json_encode($product) }}">
                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                    class="w-full h-32 md:h-40 lg:h-48 object-cover rounded" />
                <h3 class="mt-2 font-medium text-sm">{{ $product['name'] }}</h3>
                <p class="text-green-600 font-semibold">€{{ $product['price'] }}</p>
                
                @if (!empty($product['description']))
                    <p class="text-xs text-gray-500 mt-1">
                        {{ \Illuminate\Support\Str::limit($product['description'], 40, '...') }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>
</section>
