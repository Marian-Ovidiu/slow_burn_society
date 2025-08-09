@extends('layouts.mainLayout')

@section('content')
<div x-data="{
    items: Alpine.store('cart').items,
    remove(id) {
        Alpine.store('cart').remove(id);
    },
    clear() {
        Alpine.store('cart').clear();
    },
    total() {
        return Alpine.store('cart').total().toFixed(2).replace('.', ',');
    }
}" class="px-4 py-8 max-w-3xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">🛒 Il tuo carrello</h1>

    <template x-if="items.length === 0">
        <p class="text-gray-600">Il carrello è vuoto.</p>
    </template>

    <template x-if="items.length > 0">
        <div>
            <div class="divide-y divide-gray-200 mb-6">
                <template x-for="item in items" :key="item.id">
                    <div class="py-4 flex gap-4 items-center">
                        <img :src="item.image" alt="" class="w-16 h-16 rounded object-cover">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold" x-text="item.name"></h3>
                            <p class="text-xs text-gray-600">Quantità: <span x-text="item.qty"></span></p>
                            <p class="text-sm text-gray-800 font-medium">€<span x-text="(item.qty * parseFloat(item.price)).toFixed(2).replace('.', ',')"></span></p>
                        </div>
                        <button class="text-red-500 text-sm hover:underline" @click="remove(item.id)">Rimuovi</button>
                    </div>
                </template>
            </div>

            <div class="text-right border-t pt-4">
                <p class="text-lg font-bold">Totale: €<span x-text="total()"></span></p>
                <button class="mt-4 bg-[#45752c] hover:bg-[#386322] text-white px-6 py-2 rounded font-semibold transition">
                    Procedi all'acquisto
                </button>
                <button class="ml-4 text-sm text-gray-500 hover:underline" @click="clear()">Svuota carrello</button>
            </div>
        </div>
    </template>
</div>
@endsection
