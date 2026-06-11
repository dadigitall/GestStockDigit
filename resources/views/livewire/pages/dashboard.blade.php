<div class="space-y-6">
    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CA Aujourd'hui</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['ca_today'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">CA Ce mois</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['ca_month'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ventes</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_sales'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Panier moyen</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['avg_basket'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valeur stock</p>
            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($stats['stock_value'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Produits</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_products'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Chart (14 days) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes des 14 derniers jours</h3>
            @php $max = max(array_column($this->salesChart, 'total')) ?: 1; @endphp
            <div class="flex items-end gap-1.5 h-40">
                @foreach($this->salesChart as $date => $data)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[10px] font-medium text-gray-500 {{ $data['total'] > 0 ? '' : 'opacity-0' }}">
                            {{ $data['total'] > 0 ? number_format($data['total'], 0, ',', ' ') : '0' }}
                        </span>
                        <div class="w-full bg-indigo-100 dark:bg-indigo-900/40 rounded-t relative" style="height: 100%;">
                            <div class="absolute bottom-0 w-full bg-indigo-500 dark:bg-indigo-400 rounded-t transition-all duration-500" style="height: {{ max(1, ($data['total'] / $max) * 100) }}%;"></div>
                        </div>
                        <span class="text-[10px] text-gray-400 truncate w-full text-center">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Stock Alerts -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Alertes stock</h3>
            <div class="space-y-4">
                @if(count($outOfStockList) > 0)
                    <div>
                        <p class="text-xs font-medium text-red-600 dark:text-red-400 mb-2">En rupture ({{ count($outOfStockList) }})</p>
                        <ul class="space-y-1">
                            @foreach($outOfStockList as $p)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300 truncate">{{ $p['name'] }}</span>
                                    <span class="font-medium text-red-600 dark:text-red-400 ml-2">0</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(count($lowStockList) > 0)
                    <div>
                        <p class="text-xs font-medium text-amber-600 dark:text-amber-400 mb-2">Stock bas ({{ count($lowStockList) }})</p>
                        <ul class="space-y-1">
                            @foreach($lowStockList as $p)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300 truncate">{{ $p['name'] }}</span>
                                    <span class="font-medium text-amber-600 dark:text-amber-400 ml-2">{{ $p['stock_quantity'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(count($outOfStockList) === 0 && count($lowStockList) === 0)
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucune alerte stock</p>
                @endif
                <a href="{{ route('stock.index') }}" wire:navigate class="block text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline pt-2">Voir tout le stock →</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 10 Products -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top 10 produits les plus vendus</h3>
            @if(count($topProducts) > 0)
                <div class="space-y-2">
                    @foreach($topProducts as $i => $p)
                        <div class="flex items-center gap-3">
                            <span class="w-5 text-sm font-bold text-gray-400">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $p['product_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ (int) $p['total_qty'] }} vendu(s)</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($p['total_revenue'], 0, ',', ' ') }} F</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucune vente pour le moment</p>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Actions rapides</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('pos.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors border border-green-200 dark:border-green-800">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nouvelle vente</span>
                </a>
                <a href="{{ route('products.create') }}" wire:navigate class="flex items-center gap-3 p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors border border-indigo-200 dark:border-indigo-800">
                    <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nouveau produit</span>
                </a>
                <a href="{{ route('stock.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors border border-amber-200 dark:border-amber-800">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Voir le stock</span>
                </a>
                <a href="{{ route('suppliers.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors border border-blue-200 dark:border-blue-800">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseurs</span>
                </a>
                <a href="{{ route('categories.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors border border-purple-200 dark:border-purple-800">
                    <svg class="w-5 h-5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Catégories</span>
                </a>
                <a href="{{ route('customers.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg hover:bg-teal-100 dark:hover:bg-teal-900/40 transition-colors border border-teal-200 dark:border-teal-800">
                    <svg class="w-5 h-5 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Clients</span>
                </a>
            </div>
        </div>
    </div>
</div>
