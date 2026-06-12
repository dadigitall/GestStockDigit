<div class="space-y-6">
    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Encaissements</p>
            <p class="text-lg font-bold text-green-600 mt-1">{{ number_format($cashSummary['total_in'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Décaissements</p>
            <p class="text-lg font-bold text-red-600 mt-1">{{ number_format($cashSummary['total_out'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Solde</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ number_format($cashSummary['balance'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Bénéfice brut</p>
            <p class="text-lg font-bold text-green-600 mt-1">{{ number_format($cashSummary['gross_profit'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Marge brute</p>
            <p class="text-lg font-bold text-indigo-600 mt-1">{{ $cashSummary['gross_margin_pct'] ?? 0 }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Revenu</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ number_format($cashSummary['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Synthèse de caisse -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Synthèse de caisse</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700 dark:text-gray-300">Espèces</span>
                    <span class="font-medium">{{ number_format($cashSummary['cash_count'] ?? 0, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700 dark:text-gray-300">Mobile Money</span>
                    <span class="font-medium">{{ number_format($cashSummary['mobile_count'] ?? 0, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700 dark:text-gray-300">Carte</span>
                    <span class="font-medium">{{ number_format($cashSummary['card_count'] ?? 0, 0, ',', ' ') }} F</span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between text-sm font-bold">
                    <span class="text-gray-900 dark:text-white">Total encaissements</span>
                    <span class="text-green-600">{{ number_format($cashSummary['total_in'] ?? 0, 0, ',', ' ') }} F</span>
                </div>
            </div>
        </div>

        <!-- Dépenses -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Dépenses opérationnelles</h3>
            @if(count($expenses) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Date</th><th class="pb-2 font-medium">Type</th><th class="pb-2 font-medium text-right">Montant</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($expenses as $e)
                                <tr>
                                    <td class="py-1 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($e['movement_date'] ?? $e->movement_date)->format('d/m/Y') }}</td>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $e['type'] ?? $e->type ?? 'N/A' }}</td>
                                    <td class="py-1 text-right font-medium text-red-600">{{ number_format($e['amount'] ?? $e->amount ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune dépense</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Créances clients -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Créances clients</h3>
            @if(count($customerReceivables) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Client</th><th class="pb-2 font-medium text-right">Solde</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($customerReceivables as $c)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $c['name'] ?? 'N/A' }}</td>
                                    <td class="py-1 text-right font-medium text-red-600">{{ number_format($c['balance'] ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune créance</p>
            @endif
        </div>

        <!-- Ventes à crédit -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ventes à crédit</h3>
            @if(count($creditSales) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Réf.</th><th class="pb-2 font-medium">Client</th><th class="pb-2 font-medium text-right">Restant dû</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($creditSales as $s)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $s['reference'] ?? $s->reference ?? 'N/A' }}</td>
                                    <td class="py-1 text-gray-600 dark:text-gray-400">{{ $s['customer']['name'] ?? $s->customer->name ?? 'N/A' }}</td>
                                    <td class="py-1 text-right font-medium text-amber-600">{{ number_format($s['due'] ?? $s->total - $s->paid_amount ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucune vente à crédit</p>
            @endif
        </div>

        <!-- Paiements en retard -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Paiements en retard</h3>
            @if(count($latePayments) > 0)
                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                            <tr><th class="pb-2 font-medium">Client</th><th class="pb-2 font-medium text-right">Montant</th><th class="pb-2 font-medium text-right">Échu le</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($latePayments as $p)
                                <tr>
                                    <td class="py-1 text-gray-900 dark:text-white">{{ $p['customer']['name'] ?? $p->customer->name ?? 'N/A' }}</td>
                                    <td class="py-1 text-right font-medium text-red-600">{{ number_format($p['amount'] ?? $p->amount ?? 0, 0, ',', ' ') }} F</td>
                                    <td class="py-1 text-right text-gray-500 text-xs">{{ \Carbon\Carbon::parse($p['due_date'] ?? $p->due_date)->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500 text-center py-8">Aucun paiement en retard</p>
            @endif
        </div>
    </div>

    <!-- Paiements reçus -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Paiements reçus</h3>
        @if(count($paymentsReceived) > 0)
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase sticky top-0 bg-white dark:bg-gray-800">
                        <tr><th class="pb-2 font-medium">Date</th><th class="pb-2 font-medium">Client</th><th class="pb-2 font-medium">Méthode</th><th class="pb-2 font-medium text-right">Montant</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($paymentsReceived as $pmt)
                            <tr>
                                <td class="py-1 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($pmt['payment_date'] ?? $pmt->payment_date)->format('d/m/Y') }}</td>
                                <td class="py-1 text-gray-900 dark:text-white">{{ $pmt['customer']['name'] ?? $pmt->customer->name ?? 'N/A' }}</td>
                                <td class="py-1 text-gray-600 dark:text-gray-400">{{ $pmt['payment_method'] ?? $pmt->payment_method ?? 'N/A' }}</td>
                                <td class="py-1 text-right font-medium text-green-600">{{ number_format($pmt['amount'] ?? $pmt->amount ?? 0, 0, ',', ' ') }} F</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-8">Aucun paiement reçu</p>
        @endif
    </div>
</div>
