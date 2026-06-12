<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Chiffre d'affaires</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ number_format($salesSummary['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nb ventes</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $salesSummary['total_sales'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Panier moyen</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ number_format($salesSummary['avg_basket'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Remises</p>
            <p class="text-lg font-bold text-red-600 mt-1">{{ number_format($salesSummary['total_discount'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Marge brute</p>
            <p class="text-lg font-bold text-green-600 mt-1">{{ number_format($salesSummary['gross_margin'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Taux marge</p>
            <p class="text-lg font-bold text-indigo-600 mt-1">{{ $salesSummary['margin_rate'] ?? 0 }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fréquence achat</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $salesSummary['purchase_frequency'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ventes par produit -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par produit</h3>
            @if(count($salesByProduct) > 0)
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr>
                                <th class="pb-2 font-medium">Produit</th>
                                <th class="pb-2 font-medium text-right">Qté</th>
                                <th class="pb-2 font-medium text-right">Revenu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($salesByProduct as $p)
                                <tr class="text-sm">
                                    <td class="py-1.5 text-gray-900 dark:text-white">{{ $p['product_name'] }}</td>
                                    <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ (int) $p['total_qty'] }}</td>
                                    <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($p['total_revenue'], 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Ventes par catégorie -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par catégorie</h3>
            @if(count($salesByCategory) > 0)
                <div class="space-y-3">
                    @foreach($salesByCategory as $c)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ $c['name'] ?? 'N/A' }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($c['total_revenue'], 0, ',', ' ') }} F</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                @php $maxCat = max(array_column($salesByCategory, 'total_revenue')) ?: 1; @endphp
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($c['total_revenue'] / $maxCat) * 100 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ (int) $c['total_qty'] }} vendu(s)</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Ventes par magasin -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par magasin</h3>
            @if(count($salesByStore) > 0)
                <div class="space-y-3">
                    @foreach($salesByStore as $s)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $s['store']['name'] ?? 'N/A' }}</span>
                            <span class="font-medium">{{ number_format($s['amount'], 0, ',', ' ') }} F <span class="text-gray-400 text-xs">({{ $s['total'] }})</span></span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Ventes par vendeur -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par vendeur</h3>
            @if(count($salesByUser) > 0)
                <div class="space-y-3">
                    @foreach($salesByUser as $u)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $u['user']['first_name'] ?? '' }} {{ $u['user']['last_name'] ?? $u['user']['name'] ?? 'N/A' }}</span>
                            <span class="font-medium">{{ number_format($u['amount'], 0, ',', ' ') }} F <span class="text-gray-400 text-xs">({{ $u['total'] }})</span></span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Ventes par client -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par client (top 20)</h3>
            @if(count($salesByCustomer) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Client</th><th class="pb-2 font-medium text-right">Montant</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($salesByCustomer as $c)
                                <tr><td class="py-1 text-gray-900 dark:text-white">{{ $c['customer']['name'] ?? 'N/A' }}</td><td class="py-1 text-right font-medium">{{ number_format($c['amount'], 0, ',', ' ') }} F</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Ventes par type -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes par type</h3>
            @if(count($salesByType) > 0)
                <div class="space-y-2">
                    @foreach($salesByType as $t)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 capitalize">{{ $t['type'] ?? 'N/A' }}</span>
                            <span class="font-medium">{{ number_format($t['amount'], 0, ',', ' ') }} F ({{ $t['total'] }})</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Ventes annulées -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes annulées</h3>
            <p class="text-2xl font-bold text-red-600">{{ $cancelledSales['total'] ?? 0 }} vente(s)</p>
            <p class="text-lg text-red-500 mt-1">{{ number_format($cancelledSales['amount'] ?? 0, 0, ',', ' ') }} F</p>
        </div>

        <!-- Retours clients -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Retours clients</h3>
            @if(count($returns) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Réf.</th><th class="pb-2 font-medium">Client</th><th class="pb-2 font-medium text-right">Montant</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($returns as $r)
                                <tr><td class="py-1 text-gray-900 dark:text-white">{{ $r['reference'] ?? 'N/A' }}</td><td class="py-1 text-gray-600 dark:text-gray-400">{{ $r['customer']['name'] ?? 'N/A' }}</td><td class="py-1 text-right font-medium text-red-600">{{ number_format($r['refund_amount'] ?? 0, 0, ',', ' ') }} F</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun retour</p>
            @endif
        </div>
    </div>

    <!-- Marges par produit -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Marges par produit</h3>
        @if(count($marginByProduct) > 0)
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                        <tr>
                            <th class="pb-2 font-medium">Produit</th>
                            <th class="pb-2 font-medium text-right">Revenu</th>
                            <th class="pb-2 font-medium text-right">Coût</th>
                            <th class="pb-2 font-medium text-right">Marge</th>
                            <th class="pb-2 font-medium text-right">Taux</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($marginByProduct as $m)
                            @php $mRate = $m['revenue'] > 0 ? round(($m['margin'] / $m['revenue']) * 100, 1) : 0; @endphp
                            <tr>
                                <td class="py-1.5 text-gray-900 dark:text-white">{{ $m['product_name'] }}</td>
                                <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ number_format($m['revenue'], 0, ',', ' ') }} F</td>
                                <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ number_format($m['cost'], 0, ',', ' ') }} F</td>
                                <td class="py-1.5 text-right font-medium {{ $m['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($m['margin'], 0, ',', ' ') }} F</td>
                                <td class="py-1.5 text-right font-medium {{ $mRate >= 30 ? 'text-green-600' : 'text-amber-600' }}">{{ $mRate }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
        @endif
    </div>
</div>
