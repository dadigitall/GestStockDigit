<div>
    <button wire:click="closeDetail" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Retour à la liste
    </button>

    {{-- En-tête client --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $detailCustomer->name }}</h2>
                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($detailCustomer->category)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background: {{ $detailCustomer->category->color }}20; color: {{ $detailCustomer->category->color }}">{{ $detailCustomer->category->name }}</span>
                    @endif
                    <span>{{ ['particular' => 'Particulier', 'professional' => 'Professionnel', 'reseller' => 'Revendeur', 'wholesaler' => 'Grossiste'][$detailCustomer->type] ?? $detailCustomer->type }}</span>
                    @if($detailCustomer->tax_number)<span>NIF: {{ $detailCustomer->tax_number }}</span>@endif
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400">Solde actuel</div>
                <div class="text-2xl font-bold {{ $detailCustomer->balance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ number_format($detailCustomer->balance, 0, ',', ' ') }} F
                </div>
                @if($detailCustomer->isCreditBlocked())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 mt-1">Crédit bloqué</span>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Téléphone :</span>
                <span class="text-gray-900 dark:text-white ml-1">{{ $detailCustomer->phone ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Email :</span>
                <span class="text-gray-900 dark:text-white ml-1">{{ $detailCustomer->email ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Adresse :</span>
                <span class="text-gray-900 dark:text-white ml-1">{{ $detailCustomer->address ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Plafond crédit :</span>
                <span class="text-gray-900 dark:text-white ml-1">{{ $detailCustomer->credit_limit ? number_format($detailCustomer->credit_limit, 0, ',', ' ') . ' F' : '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Délai paiement :</span>
                <span class="text-gray-900 dark:text-white ml-1">{{ $detailCustomer->payment_terms ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Total payé :</span>
                <span class="text-gray-900 dark:text-white ml-1">{{ number_format($detailCustomer->totalPaid(), 0, ',', ' ') }} F</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Historique des achats --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-white flex items-center justify-between">
                <span>Historique des achats</span>
                @if($detailCustomer->sales->count() > 0)
                    <span class="text-xs text-gray-500">{{ $detailCustomer->sales->count() }} vente(s)</span>
                @endif
            </div>
            <div class="p-4">
                @forelse($detailCustomer->sales as $sale)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0 text-sm">
                        <div>
                            <div class="text-gray-900 dark:text-white font-medium">{{ $sale->reference }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-gray-900 dark:text-white">{{ number_format($sale->total, 0, ',', ' ') }} F</div>
                            <div class="text-xs {{ $sale->paid_amount >= $sale->total ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $sale->paid_amount >= $sale->total ? 'Payée' : 'Solde: ' . number_format(max(0, $sale->total - $sale->paid_amount), 0, ',', ' ') . ' F' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Aucun achat</p>
                @endforelse
            </div>
        </div>

        {{-- Historique des règlements --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-white flex items-center justify-between">
                <span>Historique des règlements</span>
                <span class="text-xs text-gray-500">{{ $detailCustomer->payments->count() }} paiement(s)</span>
            </div>
            <div class="p-4">
                @forelse($detailCustomer->payments as $payment)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0 text-sm">
                        <div>
                            <div class="text-emerald-600 dark:text-emerald-400 font-medium">+ {{ number_format($payment->amount, 0, ',', ' ') }} F</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('d/m/Y') }} · {{ ['cash' => 'Espèces', 'mobile_money' => 'Mobile Money', 'card' => 'Carte', 'transfer' => 'Virement', 'check' => 'Chèque'][$payment->payment_method] ?? $payment->payment_method }}</div>
                        </div>
                        <div class="text-xs text-gray-400">Réf: {{ $payment->reference ?? $payment->id }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Aucun règlement</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Échéancier --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-white flex items-center justify-between">
            <span>Échéancier</span>
            @if($detailCustomer->balance > 0)
                <button wire:click="openScheduleForm({{ $detailCustomer->id }})" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">+ Planifier une échéance</button>
            @endif
        </div>
        <div class="p-4">
            @forelse($detailCustomer->paymentSchedules as $schedule)
                @php $remaining = $schedule->remaining(); @endphp
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700/50 last:border-0 text-sm">
                    <div>
                        <div class="text-gray-900 dark:text-white flex items-center gap-2">
                            <span>{{ number_format($schedule->amount, 0, ',', ' ') }} F</span>
                            @if($schedule->isOverdue())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">En retard</span>
                            @elseif($schedule->status === 'paid')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">Payée</span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">À venir</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">Échéance: {{ $schedule->due_date->format('d/m/Y') }} · {{ $remaining > 0 ? 'Reste: ' . number_format($remaining, 0, ',', ' ') . ' F' : '' }}</div>
                    </div>
                    @if($remaining > 0)
                        <button wire:click="openPaymentForm({{ $detailCustomer->id }}, {{ $schedule->sale_id }}, {{ $schedule->id }})" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-800">Payer</button>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Aucune échéance planifiée</p>
            @endforelse
        </div>
    </div>

    {{-- Relevé de compte synthétique --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h3 class="font-medium text-gray-900 dark:text-white mb-3">Relevé de compte</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="py-2 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                    <th class="py-2 text-left font-medium text-gray-500 dark:text-gray-400">Libellé</th>
                    <th class="py-2 text-right font-medium text-gray-500 dark:text-gray-400">Débit</th>
                    <th class="py-2 text-right font-medium text-gray-500 dark:text-gray-400">Crédit</th>
                    <th class="py-2 text-right font-medium text-gray-500 dark:text-gray-400">Solde</th>
                </tr>
            </thead>
            <tbody>
                @php $runningBalance = 0; @endphp
                @foreach($detailCustomer->sales->merge($detailCustomer->payments)->sortByDesc('created_at')->take(30) as $entry)
                    @php
                        if ($entry instanceof \App\Models\Sale) {
                            $debit = $entry->total;
                            $credit = 0;
                            $label = 'Vente ' . $entry->reference;
                            $date = $entry->created_at->format('d/m/Y');
                        } else {
                            $debit = 0;
                            $credit = $entry->amount;
                            $label = 'Paiement ' . ($entry->reference ?? '#' . $entry->id);
                            $date = $entry->payment_date->format('d/m/Y');
                        }
                        $runningBalance += $debit - $credit;
                    @endphp
                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                        <td class="py-1.5 text-gray-600 dark:text-gray-400">{{ $date }}</td>
                        <td class="py-1.5 text-gray-900 dark:text-white">{{ $label }}</td>
                        <td class="py-1.5 text-right text-red-600 dark:text-red-400">{{ $debit > 0 ? number_format($debit, 0, ',', ' ') : '—' }}</td>
                        <td class="py-1.5 text-right text-emerald-600 dark:text-emerald-400">{{ $credit > 0 ? number_format($credit, 0, ',', ' ') : '—' }}</td>
                        <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($runningBalance, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
