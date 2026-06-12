<div class="space-y-6">
    <!-- Alerts -->
    @if(count($alerts) > 0)
        <div class="space-y-2">
            @foreach($alerts as $alert)
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium
                    @if($alert['type'] === 'danger') bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800
                    @elseif($alert['type'] === 'warning') bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                    @else bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 @endif">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($alert['type'] === 'danger')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        @elseif($alert['type'] === 'warning')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                    <span>{{ $alert['message'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

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
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bénéfice brut</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($stats['gross_profit'], 0, ',', ' ') }} F</p>
            <p class="text-[10px] text-gray-400">Marge {{ $stats['margin_rate'] }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ventes (mois)</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_sales'] }}</p>
            <p class="text-[10px] text-gray-400">Panier {{ number_format($stats['avg_basket'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valeur stock</p>
            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($stats['stock_value'], 0, ',', ' ') }} F</p>
            <p class="text-[10px] text-gray-400">{{ $stats['total_products'] }} produits</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Taux retour</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['return_rate'] }}%</p>
            <p class="text-[10px] text-gray-400">{{ $stats['out_of_stock_count'] }} ruptures</p>
        </div>
    </div>

    <!-- Secondary KPI Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Factures impayées</p>
            <p class="text-lg font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($stats['unpaid_invoices'], 0, ',', ' ') }} F</p>
            @if($stats['overdue_invoices'] > 0)
                <p class="text-[10px] text-red-500">dont {{ number_format($stats['overdue_invoices'], 0, ',', ' ') }} F en retard</p>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dettes fournisseurs</p>
            <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($stats['supplier_debts'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Commandes en attente</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $stats['pending_orders'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ruptures / Stock bas</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                <span class="text-red-600">{{ $stats['out_of_stock_count'] }}</span>
                /
                <span class="text-amber-600">{{ $stats['low_stock_count'] }}</span>
            </p>
        </div>
    </div>

    <!-- Chart + Sales by store/user -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Évolution des ventes (14 jours)</h3>
            @php $max = max(array_column($salesChart, 'total')) ?: 1; @endphp
            <div class="flex items-end gap-1.5 h-40">
                @foreach($salesChart as $date => $data)
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

        <!-- Sales by Store -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par magasin (mois)</h3>
            @if(count($salesByStore) > 0)
                <div class="space-y-3">
                    @foreach($salesByStore as $s)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ $s['store']['name'] ?? 'N/A' }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($s['amount'], 0, ',', ' ') }} F</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                @php $pct = max($salesByStore, fn($a) => $a['amount'])['amount'] ?? 1; @endphp
                                <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $pct > 0 ? ($s['amount'] / $pct) * 100 : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $s['total'] }} vente(s)</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <!-- Products + Customers + Alerts grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Products -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top 10 produits (CA)</h3>
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
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucune vente</p>
            @endif
        </div>

        <!-- Top Customers -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Clients les plus rentables</h3>
            @if(count($topCustomers) > 0)
                <div class="space-y-3">
                    @foreach($topCustomers as $c)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $c['customer']['name'] ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $c['total_orders'] }} commande(s)</p>
                            </div>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ number_format($c['total_spent'], 0, ',', ' ') }} F</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucun client</p>
            @endif
        </div>

        <!-- Flop Products -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Produits les moins vendus</h3>
            @if(count($flopProducts) > 0)
                <div class="space-y-2">
                    @foreach($flopProducts as $p)
                        <div class="flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $p['product_name'] }}</p>
                            </div>
                            <span class="text-xs text-gray-500">{{ (int) $p['total_qty'] }} vendu(s)</span>
                            <span class="text-sm text-gray-400">{{ number_format($p['total_revenue'], 0, ',', ' ') }} F</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <!-- Stock + Expiry -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">En rupture de stock</h3>
            @if(count($outOfStockList) > 0)
                <ul class="space-y-1.5">
                    @foreach($outOfStockList as $p)
                        <li class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 truncate">{{ $p['name'] }}</span>
                            <span class="font-medium text-red-600 dark:text-red-400 ml-2 shrink-0">0</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucun produit en rupture</p>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Stock bas (≤ min)</h3>
            @if(count($lowStockList) > 0)
                <ul class="space-y-1.5">
                    @foreach($lowStockList as $p)
                        <li class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 truncate">{{ $p['name'] }}</span>
                            <span class="font-medium text-amber-600 dark:text-amber-400 ml-2 shrink-0">{{ $p['stock_quantity'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucun stock bas</p>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Expiration proche (30 jours)</h3>
            @if(count($expiringSoon) > 0)
                <ul class="space-y-1.5">
                    @foreach($expiringSoon as $lot)
                        <li class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 truncate">{{ $lot->product_name }} ({{ $lot->lot_number }})</span>
                            <span class="font-medium text-red-600 dark:text-red-400 ml-2 shrink-0">{{ \Carbon\Carbon::parse($lot->expiry_date)->format('d/m') }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('lots.index') }}" wire:navigate class="block text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline pt-2">Voir tous les lots →</a>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucune expiration imminente</p>
            @endif
        </div>
    </div>

    <!-- Sales by User -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par vendeur (mois)</h3>
            @if(count($salesByUser) > 0)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase">
                            <th class="pb-2 font-medium">Vendeur</th>
                            <th class="pb-2 font-medium text-right">Ventes</th>
                            <th class="pb-2 font-medium text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($salesByUser as $u)
                            <tr>
                                <td class="py-1.5 text-gray-900 dark:text-white">{{ $u['user']['first_name'] ?? '' }} {{ $u['user']['last_name'] ?? $u['user']['name'] ?? 'N/A' }}</td>
                                <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ $u['total'] }}</td>
                                <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($u['amount'], 0, ',', ' ') }} F</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">Aucune donnée</p>
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
                <a href="{{ route('reports.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors border border-purple-200 dark:border-purple-800">
                    <svg class="w-5 h-5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Rapports</span>
                </a>
                <a href="{{ route('stock.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors border border-amber-200 dark:border-amber-800">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Voir le stock</span>
                </a>
                <a href="{{ route('suppliers.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors border border-blue-200 dark:border-blue-800">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseurs</span>
                </a>
                <a href="{{ route('invoices.index') }}" wire:navigate class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg hover:bg-teal-100 dark:hover:bg-teal-900/40 transition-colors border border-teal-200 dark:border-teal-800">
                    <svg class="w-5 h-5 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Factures</span>
                </a>
            </div>
        </div>
    </div>
</div>
