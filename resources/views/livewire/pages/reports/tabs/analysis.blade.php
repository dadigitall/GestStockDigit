<div class="space-y-6">
    <!-- Comparaison période à période -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Comparaison période à période</h3>
            @php
                $revChange = $currentPeriod && $previousPeriod && ($previousPeriod['revenue'] ?? 0) > 0
                    ? round((($currentPeriod['revenue'] - $previousPeriod['revenue']) / $previousPeriod['revenue']) * 100, 1)
                    : 0;
                $countChange = $currentPeriod && $previousPeriod && ($previousPeriod['count'] ?? 0) > 0
                    ? round((($currentPeriod['count'] - $previousPeriod['count']) / $previousPeriod['count']) * 100, 1)
                    : 0;
            @endphp
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Période précédente</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($previousPeriod['revenue'] ?? 0, 0, ',', ' ') }} F</p>
                    <p class="text-xs text-gray-400">{{ $previousPeriod['count'] ?? 0 }} vente(s)</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Période actuelle</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($currentPeriod['revenue'] ?? 0, 0, ',', ' ') }} F</p>
                    <p class="text-xs text-gray-400">{{ $currentPeriod['count'] ?? 0 }} vente(s)</p>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <p class="text-sm">CA : <span class="font-bold {{ $revChange >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $revChange >= 0 ? '+' : '' }}{{ $revChange }}%</span></p>
                <p class="text-sm">Ventes : <span class="font-bold {{ $countChange >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $countChange >= 0 ? '+' : '' }}{{ $countChange }}%</span></p>
            </div>
        </div>

        <!-- Taux de rupture -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Taux de rupture</h3>
            <p class="text-3xl font-bold {{ ($stockoutRate['rate'] ?? 0) > 5 ? 'text-red-600' : 'text-green-600' }}">{{ $stockoutRate['rate'] ?? 0 }}%</p>
            <p class="text-sm text-gray-500 mt-1">{{ $stockoutRate['out_of_stock'] ?? 0 }} / {{ $stockoutRate['total_products'] ?? 0 }} produits</p>
        </div>

        <!-- Taux de retour -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Taux de retour par magasin</h3>
            @if(count($returnRate) > 0)
                <div class="space-y-2">
                    @foreach($returnRate as $r)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $r['store_name'] }}</span>
                            <span class="font-medium {{ ($r['rate'] ?? 0) > 5 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">{{ $r['rate'] ?? 0 }}%</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-4">Aucune donnée</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Analyse ABC -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Analyse ABC</h3>
            <div class="mb-3 flex gap-2 text-xs">
                <span class="px-2 py-1 rounded bg-green-100 text-green-700 font-medium">A : 70%</span>
                <span class="px-2 py-1 rounded bg-amber-100 text-amber-700 font-medium">B : 90%</span>
                <span class="px-2 py-1 rounded bg-red-100 text-red-700 font-medium">C : 100%</span>
            </div>
            @if(count($abcAnalysis) > 0)
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr>
                                <th class="pb-2 font-medium">Classe</th>
                                <th class="pb-2 font-medium">Produit</th>
                                <th class="pb-2 font-medium text-right">%</th>
                                <th class="pb-2 font-medium text-right">Cumul</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($abcAnalysis as $a)
                                <tr>
                                    <td class="py-1">
                                        <span class="px-2 py-0.5 text-xs rounded-full font-medium
                                            {{ $a['class'] === 'A' ? 'bg-green-100 text-green-700' : ($a['class'] === 'B' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $a['class'] }}
                                        </span>
                                    </td>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $a['product_name'] }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ $a['percent'] }}%</td>
                                    <td class="py-1 text-right font-medium">{{ $a['cumulative'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Saisonnalité -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Saisonnalité des ventes (12 mois)</h3>
            @if(count($seasonality) > 0)
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Mois</th><th class="pb-2 font-medium text-right">Ventes</th><th class="pb-2 font-medium text-right">Montant</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($seasonality as $s)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $s['period'] ?? 'N/A' }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ $s['count'] ?? 0 }}</td>
                                    <td class="py-1 text-right font-medium">{{ number_format($s['total'] ?? 0, 0, ',', ' ') }} F</td>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Marge par famille -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Marge par famille (catégorie)</h3>
            @if(count($marginByFamily) > 0)
                <div class="space-y-3">
                    @foreach($marginByFamily as $m)
                        @php $mPct = $m['revenue'] > 0 ? round(($m['margin'] / $m['revenue']) * 100, 1) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ $m['name'] ?? 'N/A' }}</span>
                                <span class="font-medium text-green-600">{{ number_format($m['margin'], 0, ',', ' ') }} F <span class="text-gray-400 text-xs">({{ $mPct }}%)</span></span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                @php $maxMargin = max(array_column($marginByFamily, 'margin')) ?: 1; @endphp
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ max(1, ($m['margin'] / $maxMargin) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Contribution par magasin -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Contribution par magasin</h3>
            @if(count($contributionByStore ?? []) > 0)
                @php
                    $totalContrib = collect($contributionByStore)->sum('amount');
                @endphp
                <div class="space-y-3">
                    @foreach($contributionByStore as $s)
                        @php $contribPct = $totalContrib > 0 ? round(($s['amount'] / $totalContrib) * 100, 1) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ $s['store']['name'] ?? 'N/A' }}</span>
                                <span class="font-medium">{{ $contribPct }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $contribPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <!-- Top / Flop -->
    @if(isset($abcAnalysis) && count($abcAnalysis) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top 5 produits (classe A)</h3>
                @php $topA = array_filter($abcAnalysis, fn($a) => $a['class'] === 'A'); @endphp
                @if(count($topA) > 0)
                    <div class="space-y-2">
                        @foreach(array_slice(array_values($topA), 0, 5) as $i => $a)
                            <div class="flex items-center gap-2">
                                <span class="w-5 text-sm font-bold text-gray-400">{{ $i + 1 }}</span>
                                <span class="flex-1 text-sm text-gray-900 dark:text-white truncate">{{ $a['product_name'] }}</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $a['total_revenue'] > 0 ? number_format($a['total_revenue'], 0, ',', ' ') : '0' }} F</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">Aucun produit classe A</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top 5 produits (classe C)</h3>
                @php $topC = array_filter($abcAnalysis, fn($a) => $a['class'] === 'C'); @endphp
                @if(count($topC) > 0)
                    <div class="space-y-2">
                        @foreach(array_slice(array_values($topC), 0, 5) as $i => $a)
                            <div class="flex items-center gap-2">
                                <span class="w-5 text-sm font-bold text-gray-400">{{ $i + 1 }}</span>
                                <span class="flex-1 text-sm text-gray-900 dark:text-white truncate">{{ $a['product_name'] }}</span>
                                <span class="text-sm font-medium text-gray-500">{{ $a['total_revenue'] > 0 ? number_format($a['total_revenue'], 0, ',', ' ') : '0' }} F</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">Aucun produit classe C</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Répartition ABC</h3>
                @php
                    $countA = count(array_filter($abcAnalysis, fn($a) => $a['class'] === 'A'));
                    $countB = count(array_filter($abcAnalysis, fn($a) => $a['class'] === 'B'));
                    $countC = count(array_filter($abcAnalysis, fn($a) => $a['class'] === 'C'));
                    $total = $countA + $countB + $countC ?: 1;
                @endphp
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-green-600 font-medium">A — Haut potentiel</span>
                            <span>{{ round(($countA / $total) * 100) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-green-500 h-3 rounded-full" style="width: {{ ($countA / $total) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-amber-600 font-medium">B — Potentiel moyen</span>
                            <span>{{ round(($countB / $total) * 100) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-amber-500 h-3 rounded-full" style="width: {{ ($countB / $total) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-red-600 font-medium">C — Faible potentiel</span>
                            <span>{{ round(($countC / $total) * 100) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-red-500 h-3 rounded-full" style="width: {{ ($countC / $total) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Prévision des ruptures -->
    @if(count($stockoutForecast) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Prévision des ruptures</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Basé sur la vitesse de vente des 30 derniers jours. Produits triés par urgence.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="pb-2 font-medium">Produit</th>
                            <th class="pb-2 font-medium text-right">Stock</th>
                            <th class="pb-2 font-medium text-right">Ventes/j (30j)</th>
                            <th class="pb-2 font-medium text-right">Jours restants</th>
                            <th class="pb-2 font-medium text-right">Jours avant min</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($stockoutForecast as $f)
                            <tr class="{{ $f['critical'] ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                <td class="py-1.5 text-gray-900 dark:text-white">{{ $f['name'] }}</td>
                                <td class="py-1.5 text-right">{{ number_format($f['stock_quantity'], 2, ',', ' ') }}</td>
                                <td class="py-1.5 text-right">{{ number_format($f['daily_rate'], 2, ',', ' ') }}</td>
                                <td class="py-1.5 text-right font-medium {{ $f['days_until_out'] <= 7 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                    {{ $f['days_until_out'] >= 999 ? '—' : $f['days_until_out'] . ' j' }}
                                </td>
                                <td class="py-1.5 text-right font-medium {{ $f['days_until_min'] <= 7 ? 'text-amber-600' : 'text-gray-900 dark:text-white' }}">
                                    {{ $f['days_until_min'] >= 999 ? '—' : $f['days_until_min'] . ' j' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
