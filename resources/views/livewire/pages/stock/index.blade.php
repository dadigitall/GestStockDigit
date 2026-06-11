<div>
    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un produit..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
        </div>
        <select wire:model.live="filterStatus" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            <option value="">Tous les statuts</option>
            <option value="available">En stock</option>
            <option value="low">Stock bas</option>
            <option value="out">En rupture</option>
        </select>
    </div>

    <!-- Stock Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Produit</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Catégorie</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Stock</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Min</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Valeur</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($products as $product)
                    @php
                        $stock = $product->stock_quantity ?? 0;
                        $value = $stock * $product->purchase_price;
                        $status = $stock <= 0 ? 'out' : ($stock <= $product->min_stock ? 'low' : 'ok');
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $product->reference ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $product->category?->name ?? '-' }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ number_format($stock) }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $product->min_stock }}</td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">{{ number_format($value, 0, ',', ' ') }} F</td>
                        <td class="px-6 py-4">
                            @if($status === 'out')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300">Rupture</span>
                            @elseif($status === 'low')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300">Stock bas</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <p class="text-lg font-medium mb-1">Aucun produit trouvé</p>
                            <p class="text-sm">Ajustez vos filtres ou créez des produits.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $products->links() }}
        </div>
    </div>
</div>
