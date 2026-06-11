<div>
    <!-- Header actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un produit..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        <a href="{{ route('products.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau produit
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th wire:click="sortBy('name')" class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            <div class="flex items-center gap-1">
                                Nom
                                @if($sortField === 'name')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Catégorie</th>
                        <th wire:click="sortBy('sale_price')" class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400 cursor-pointer">
                            <div class="flex items-center gap-1">Prix vente @if($sortField === 'sale_price')<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>@endif</div>
                        </th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Stock</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Statut</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                                @if($product->barcode)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Code: {{ $product->barcode }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $product->reference ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $product->category?->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ number_format($product->sale_price, 0, ',', ' ') }} F</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $product->stock_quantity <= 0 ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' :
                                       ($product->stock_quantity <= $product->min_stock ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300' :
                                        'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300') }}">
                                    {{ $product->stock_quantity ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($product->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">Actif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('products.edit', $product) }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="text-lg font-medium mb-1">Aucun produit</p>
                                <p class="text-sm">Commencez par créer un nouveau produit.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $products->links() }}
        </div>
    </div>
</div>
