<div class="flex flex-col lg:flex-row gap-6 h-full">
    <!-- Products Panel -->
    <div class="flex-1">
        <div class="mb-4">
            <input wire:model.live.debounce.200ms="search" type="text" placeholder="Rechercher un produit ou scanner un code-barres..." class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-lg focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($products as $product)
                <button wire:click="addToCart({{ $product->id }})" class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 hover:shadow-md transition-all text-left">
                    <p class="font-medium text-gray-900 dark:text-white text-sm line-clamp-2">{{ $product->name }}</p>
                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-2">{{ number_format($product->sale_price, 0, ',', ' ') }} F</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Stock: {{ $product->stock_quantity ?? 0 }}</p>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="lg:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Panier</h3>

        <div class="flex-1 overflow-y-auto space-y-3 mb-4">
            @forelse($cart as $index => $item)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['name'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($item['price'], 0, ',', ' ') }} F</p>
                    </div>
                    <div class="flex items-center gap-2 ml-2">
                        <button wire:click="updateQty({{ $index }}, {{ $item['qty'] - 1 }})" class="w-7 h-7 flex items-center justify-center rounded bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-300">-</button>
                        <span class="w-8 text-center font-medium text-gray-900 dark:text-white">{{ $item['qty'] }}</span>
                        <button wire:click="updateQty({{ $index }}, {{ $item['qty'] + 1 }})" class="w-7 h-7 flex items-center justify-center rounded bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-300">+</button>
                    </div>
                    <div class="text-right ml-3 min-w-[80px]">
                        <p class="font-medium text-gray-900 dark:text-white">{{ number_format($item['subtotal'], 0, ',', ' ') }} F</p>
                        <button wire:click="removeItem({{ $index }})" class="text-xs text-red-600 hover:text-red-800">Retirer</button>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-8">Panier vide</p>
            @endforelse
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                <span>Total</span>
                <span>{{ number_format($total, 0, ',', ' ') }} F</span>
            </div>
            <button class="w-full py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" {{ empty($cart) ? 'disabled' : '' }}>
                Payer ({{ count($cart) }} article(s))
            </button>
        </div>
    </div>
</div>
