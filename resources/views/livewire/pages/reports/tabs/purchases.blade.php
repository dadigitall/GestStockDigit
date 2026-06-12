<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Achats par fournisseur -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Achats par fournisseur</h3>
            @if(count($purchaseBySupplier) > 0)
                <div class="space-y-3">
                    @foreach($purchaseBySupplier as $s)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $s['supplier']['name'] ?? 'N/A' }}</span>
                            <span class="font-medium">{{ number_format($s['total_amount'], 0, ',', ' ') }} F <span class="text-gray-400 text-xs">({{ $s['total_orders'] }})</span></span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun achat</p>
            @endif
        </div>

        <!-- Commandes en attente -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Commandes en attente</h3>
            @if(count($pendingOrders) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Réf.</th><th class="pb-2 font-medium">Fournisseur</th><th class="pb-2 font-medium">Statut</th><th class="pb-2 font-medium text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($pendingOrders as $o)
                                @if(!isset($o['_summary']))
                                    <tr>
                                        <td class="py-1 text-gray-900 dark:text-white">{{ $o['reference'] ?? $o->reference ?? 'N/A' }}</td>
                                        <td class="py-1 text-gray-600 dark:text-gray-400">{{ $o['supplier']['name'] ?? $o->supplier->name ?? 'N/A' }}</td>
                                        <td class="py-1"><span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700">{{ $o['status'] ?? $o->status ?? 'N/A' }}</span></td>
                                        <td class="py-1 text-right font-medium">{{ number_format($o['total'] ?? $o->total ?? 0, 0, ',', ' ') }} F</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune commande en attente</p>
            @endif
        </div>

        <!-- Dettes fournisseurs -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Dettes fournisseurs</h3>
            @if(count($supplierDebts) > 0)
                <div class="space-y-2">
                    @foreach($supplierDebts as $d)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $d['name'] ?? 'N/A' }}</span>
                            <span class="font-medium text-red-600">{{ number_format($d['balance'] ?? 0, 0, ',', ' ') }} F</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune dette</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Achats par période -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Achats par période</h3>
            @if(count($purchaseByPeriod) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Période</th><th class="pb-2 font-medium text-right">Commandes</th><th class="pb-2 font-medium text-right">Montant</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($purchaseByPeriod as $p)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $p['period'] ?? 'N/A' }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ $p['total_orders'] }}</td>
                                    <td class="py-1 text-right font-medium">{{ number_format($p['total_amount'], 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Évolution des coûts d'achat -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Évolution des coûts d'achat (12 mois)</h3>
            @if(count($purchaseCostEvolution) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Période</th><th class="pb-2 font-medium text-right">Commandes</th><th class="pb-2 font-medium text-right">Montant</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($purchaseCostEvolution as $p)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $p['period'] ?? 'N/A' }}</td>
                                    <td class="py-1 text-right text-gray-700 dark:text-gray-300">{{ $p['total_orders'] ?? 0 }}</td>
                                    <td class="py-1 text-right font-medium">{{ number_format($p['total_amount'] ?? 0, 0, ',', ' ') }} F</td>
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

    <!-- Performance fournisseur -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Performance fournisseur</h3>
        @if(count($supplierPerformance) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="pb-2 font-medium">Fournisseur</th>
                            <th class="pb-2 font-medium text-right">Respect délais</th>
                            <th class="pb-2 font-medium text-right">Qualité</th>
                            <th class="pb-2 font-medium text-right">Note globale</th>
                            <th class="pb-2 font-medium text-right">Évaluations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($supplierPerformance as $s)
                            <tr>
                                <td class="py-1.5 text-gray-900 dark:text-white">{{ $s->name ?? 'N/A' }}</td>
                                <td class="py-1.5 text-right">{{ number_format($s->avg_delay_score ?? 0, 1) }}/5</td>
                                <td class="py-1.5 text-right">{{ number_format($s->avg_quality ?? 0, 1) }}/5</td>
                                <td class="py-1.5 text-right font-medium">{{ number_format($s->avg_rating ?? 0, 1) }}/5</td>
                                <td class="py-1.5 text-right text-gray-500">{{ $s->eval_count ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-8">Aucune évaluation fournisseur</p>
        @endif
    </div>
</div>
