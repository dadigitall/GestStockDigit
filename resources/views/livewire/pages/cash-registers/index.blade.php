<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-300 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Caisses</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Gestion des caisses et mouvements financiers
                @if($this->openRegistersCount > 0)
                    · <span class="text-emerald-600 font-medium">{{ $this->openRegistersCount }} caisse(s) ouverte(s)</span>
                @endif
            </p>
        </div>
        <button wire:click="openCreateForm" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouvelle caisse
        </button>
    </div>

    @if($showRapport && $rapportId)
        <!-- Rapport de caisse -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            @php $rd = $this->rapportData; @endphp
            @if($rd)
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Rapport de caisse</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $rd['register']->name }} · {{ $rd['register']->store?->name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select wire:model.live="rapportPeriod" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm">
                            <option value="shift">Poste</option>
                            <option value="today">Aujourd'hui</option>
                            <option value="week">Cette semaine</option>
                            <option value="month">Ce mois</option>
                            <option value="custom">Personnalisé</option>
                        </select>
                        @if($rapportPeriod === 'custom')
                            <input wire:model="rapportDateFrom" type="date" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm">
                            <input wire:model="rapportDateTo" type="date" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm">
                        @endif
                        <a href="{{ route('cash-registers.print', ['cashRegister' => $rd['register']->id, 'period' => $rapportPeriod, 'from' => $rapportDateFrom, 'to' => $rapportDateTo]) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                            Imprimer
                        </a>
                        <button wire:click="closeRapport" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Retour</button>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6 border-b border-gray-100 dark:border-gray-700">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total entrées</p>
                        <p class="text-xl font-bold text-emerald-600 mt-1">{{ number_format($rd['summary']['total_in'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total sorties</p>
                        <p class="text-xl font-bold text-rose-600 mt-1">{{ number_format($rd['summary']['total_out'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Solde théorique</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($rd['summary']['expected_balance'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Nb mouvements</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $rd['movements']->count() }}</p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Par méthode de paiement</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Espèces</span><span class="font-medium">{{ number_format($rd['summary']['by_cash'], 0, ',', ' ') }} F</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Mobile Money</span><span class="font-medium">{{ number_format($rd['summary']['by_mobile_money'], 0, ',', ' ') }} F</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Carte</span><span class="font-medium">{{ number_format($rd['summary']['by_card'], 0, ',', ' ') }} F</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Chèque</span><span class="font-medium">{{ number_format($rd['summary']['by_check'], 0, ',', ' ') }} F</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Virement</span><span class="font-medium">{{ number_format($rd['summary']['by_bank_transfer'], 0, ',', ' ') }} F</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Crédit</span><span class="font-medium">{{ number_format($rd['summary']['by_credit'], 0, ',', ' ') }} F</span></div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Par type de mouvement</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Ventes encaissées</span><span class="font-medium text-emerald-600">+{{ number_format($rd['summary']['cash_sales'], 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Paiements clients</span><span class="font-medium text-emerald-600">+{{ number_format($rd['summary']['customer_payments'], 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Remboursements</span><span class="font-medium text-rose-600">-{{ number_format($rd['summary']['customer_refunds'], 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Dépenses internes</span><span class="font-medium text-rose-600">-{{ number_format($rd['summary']['internal_expenses'], 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Paiements fournisseurs</span><span class="font-medium text-rose-600">-{{ number_format($rd['summary']['supplier_payments'], 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Dépôts bancaires</span><span class="font-medium text-rose-600">-{{ number_format($rd['summary']['bank_deposits'], 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Retraits propriétaire</span><span class="font-medium text-rose-600">-{{ number_format($rd['summary']['owner_withdrawals'], 0, ',', ' ') }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Mouvements</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Par</th>
                                <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="text-right py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rd['movements'] as $m)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50">
                                    <td class="py-3 px-2 text-gray-600 dark:text-gray-400">{{ $m->movement_date->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-2">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium
                                            @if($m->direction === 'in') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                                            @else bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $m->type)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-gray-600 dark:text-gray-400">{{ $m->payment_method }}</td>
                                    <td class="py-3 px-2 text-gray-600 dark:text-gray-400">{{ $m->user?->name }}</td>
                                    <td class="py-3 px-2 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $m->description }}</td>
                                    <td class="py-3 px-2 text-right font-medium {{ $m->direction === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $m->direction === 'in' ? '+' : '-' }}{{ number_format($m->amount, 0, ',', ' ') }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-gray-500">Aucun mouvement</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @elseif($showDetail && $detailId)
        <!-- Détail caisse -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $detail->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $detail->store?->name }} · {{ $detail->code }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                        @if($detail->status === 'open') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                        @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                        {{ $detail->status === 'open' ? 'Ouverte' : 'Fermée' }}
                    </span>
                    <button wire:click="showRapport({{ $detail->id }})" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Rapport</button>
                    <button wire:click="closeDetail" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Retour</button>
                </div>
            </div>

            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Solde attendu</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($detail->expected_balance, 0, ',', ' ') }} F</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Solde actuel</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($detail->current_balance, 0, ',', ' ') }} F</p>
                </div>
                @if($detail->counted_amount)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Montant compté</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($detail->counted_amount, 0, ',', ' ') }} F</p>
                    </div>
                @endif
                @if($detail->difference !== null)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Écart</p>
                        <p class="text-xl font-bold {{ $detail->difference >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                            {{ $detail->difference >= 0 ? '+' : '' }}{{ number_format($detail->difference, 0, ',', ' ') }} F
                        </p>
                    </div>
                @endif
            </div>

            <!-- Infos ouverture/fermeture + caissiers -->
            <div class="px-6 pb-4 flex flex-wrap items-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                @if($detail->opened_at)
                    <span>Ouvert le {{ $detail->opened_at->format('d/m/Y H:i') }} par {{ $detail->openedBy?->name }}</span>
                @endif
                @if($detail->closed_at)
                    <span>Fermé le {{ $detail->closed_at->format('d/m/Y H:i') }} par {{ $detail->closedBy?->name }}</span>
                @endif
                @if($detail->cashiers->isNotEmpty())
                    <span>Caissiers: {{ $detail->cashiers->pluck('name')->implode(', ') }}</span>
                @endif
            </div>

            <!-- Signatures & Validation -->
            @if($detail->cashier_signature || $detail->validator_signature)
                <div class="px-6 pb-4 flex flex-wrap items-center gap-6">
                    @if($detail->cashier_signature)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Signature caissier</p>
                            <p class="text-sm font-mono text-gray-900 dark:text-white italic">{{ $detail->cashier_signature }}</p>
                        </div>
                    @endif
                    @if($detail->validator_signature)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Signature validateur</p>
                            <p class="text-sm font-mono text-gray-900 dark:text-white italic">{{ $detail->validator_signature }}</p>
                            <p class="text-xs text-gray-400 mt-1">Validé par {{ $detail->validatedBy?->name }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Validation action -->
            @if($detail->status === 'closed' && !$detail->validated_by)
                <div class="px-6 pb-4">
                    <button wire:click="confirmValidate({{ $detail->id }})" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                        Valider la clôture
                    </button>
                </div>
            @endif

            <!-- Mouvements -->
            <div class="border-t border-gray-100 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Mouvements</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                    <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="text-right py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements ?? [] as $movement)
                                    <tr class="border-b border-gray-50 dark:border-gray-700/50">
                                        <td class="py-3 px-2 text-gray-600 dark:text-gray-400">{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                                        <td class="py-3 px-2">
                                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                                @if($movement->direction === 'in') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                                                @else bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $movement->type)) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-gray-600 dark:text-gray-400">{{ $movement->payment_method }}</td>
                                        <td class="py-3 px-2 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $movement->description }}</td>
                                        <td class="py-3 px-2 text-right font-medium {{ $movement->direction === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $movement->direction === 'in' ? '+' : '-' }}{{ number_format($movement->amount, 0, ',', ' ') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400">Aucun mouvement</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($movements ?? collect(), 'links'))
                        <div class="mt-4">{{ $movements->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($showForm)
        <!-- Formulaire création/édition -->
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $editId ? 'Modifier la caisse' : 'Nouvelle caisse' }}</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                    <input wire:model="name" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                    <input wire:model="code" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin *</label>
                    <select wire:model="storeId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Sélectionner un magasin</option>
                        @foreach($stores as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('storeId') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Caissiers assignés</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($users as $id => $name)
                            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <input type="checkbox" wire:model="selectedCashiers" value="{{ $id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-4">
                    <button wire:click="save" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
                        {{ $editId ? 'Enregistrer' : 'Créer' }}
                    </button>
                    <button wire:click="$set('showForm', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Liste -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <input wire:model.live.debounce="search" type="text" placeholder="Rechercher une caisse..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Tous</option>
                    <option value="open">Ouverte</option>
                    <option value="closed">Fermée</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Caisse</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Magasin</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Caissiers</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Solde</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registers as $register)
                        <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="py-4 px-4">
                                <button wire:click="viewDetail({{ $register->id }})" class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">{{ $register->name }}</button>
                                @if($register->code)
                                    <span class="text-xs text-gray-400 ml-2">({{ $register->code }})</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-400">{{ $register->store?->name }}</td>
                            <td class="py-4 px-4">
                                <span class="text-xs text-gray-500">{{ $register->cashiers->pluck('name')->take(2)->implode(', ') }}{{ $register->cashiers->count() > 2 ? '...' : '' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                                    @if($register->status === 'open') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                                    @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ $register->status === 'open' ? 'Ouverte' : 'Fermée' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($register->expected_balance, 0, ',', ' ') }} F
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-end gap-1">
                                    @if($register->status === 'open')
                                        <button wire:click="confirmMovement({{ $register->id }}, 'in')" class="p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-emerald-600 transition" title="Entrée">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                        <button wire:click="confirmMovement({{ $register->id }}, 'out')" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 transition" title="Sortie">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/></svg>
                                        </button>
                                        <button wire:click="confirmClose({{ $register->id }})" class="p-2 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 text-amber-600 transition" title="Clôturer">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20M12 2v20"/></svg>
                                        </button>
                                    @else
                                        <button wire:click="confirmOpen({{ $register->id }})" class="p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-emerald-600 transition" title="Ouvrir">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13 12H3"/></svg>
                                        </button>
                                    @endif
                                    <button wire:click="edit({{ $register->id }})" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-indigo-600 transition" title="Modifier">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">Aucune caisse trouvée</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $registers->links() }}</div>
        </div>
    @endif

    <!-- Modal Ouverture -->
    <div wire:ignore.self class="fixed inset-0 z-50 flex items-center justify-center {{ $showOpenModal ? '' : 'hidden' }}">
        <div class="fixed inset-0 bg-black/50" wire:click="$set('showOpenModal', false)"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Ouverture de caisse</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fond de caisse initial (FCFA)</label>
                    <input wire:model="initialBalance" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('initialBalance') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button wire:click="openRegister" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition">Ouvrir la caisse</button>
                    <button wire:click="$set('showOpenModal', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Mouvement -->
    <div wire:ignore.self class="fixed inset-0 z-50 flex items-center justify-center {{ $showMovementModal ? '' : 'hidden' }}">
        <div class="fixed inset-0 bg-black/50" wire:click="$set('showMovementModal', false)"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ $movementDirection === 'in' ? 'Entrée' : 'Sortie' }} de caisse</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model.live="movementType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @if($movementDirection === 'in')
                            <option value="cash_sale">Vente encaissée</option>
                            <option value="customer_payment">Paiement client</option>
                            <option value="correction">Correction</option>
                        @else
                            <option value="internal_expense">Dépense interne</option>
                            <option value="customer_refund">Remboursement client</option>
                            <option value="supplier_payment">Paiement fournisseur</option>
                            <option value="owner_withdrawal">Retrait propriétaire</option>
                            <option value="bank_deposit">Dépôt bancaire</option>
                            <option value="correction">Correction</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant (FCFA) *</label>
                    <input wire:model="movementAmount" type="number" step="0.01" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('movementAmount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Méthode de paiement</label>
                    <select wire:model="movementPaymentMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="cash">Espèces</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="card">Carte</option>
                        <option value="check">Chèque</option>
                        <option value="bank_transfer">Virement</option>
                        <option value="credit">Crédit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea wire:model="movementDescription" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button wire:click="addMovement" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">Ajouter</button>
                    <button wire:click="$set('showMovementModal', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Clôture avec signature -->
    <div wire:ignore.self class="fixed inset-0 z-50 flex items-center justify-center {{ $showCloseModal ? '' : 'hidden' }}">
        <div class="fixed inset-0 bg-black/50" wire:click="$set('showCloseModal', false)"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Clôture de caisse</h3>

            @php
                $reg = \App\Models\CashRegister::with('movements')->find($closeRegisterId);
                $summary = $reg?->closingSummary();
                $liveDifference = (float) $countedAmount - (float) ($summary['expected'] ?? 0);
            @endphp
            @if($summary)
                <div class="space-y-2 mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Fond initial</span><span class="font-medium">{{ number_format($summary['initial_balance'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Ventes espèces (cash)</span><span class="font-medium text-emerald-600">+{{ number_format($summary['cash_sales_cash'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Mobile Money</span><span class="font-medium text-emerald-600">+{{ number_format($summary['mobile_money'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Carte</span><span class="font-medium text-emerald-600">+{{ number_format($summary['card'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Crédits</span><span class="font-medium text-emerald-600">+{{ number_format($summary['credits'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Remboursements</span><span class="font-medium text-rose-600">-{{ number_format($summary['refunds'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Dépenses</span><span class="font-medium text-rose-600">-{{ number_format($summary['expenses'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Retraits propriétaire</span><span class="font-medium text-rose-600">-{{ number_format($summary['owner_withdrawals'], 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Dépôts bancaires</span><span class="font-medium text-rose-600">-{{ number_format($summary['bank_deposits'], 0, ',', ' ') }} F</span></div>
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-2 flex justify-between font-bold">
                        <span>Montant théorique</span>
                        <span class="text-gray-900 dark:text-white">{{ number_format($summary['expected'], 0, ',', ' ') }} F</span>
                    </div>
                </div>
            @endif

            <!-- Live difference preview -->
            <div class="px-4 mb-4">
                <div class="flex justify-between p-3 rounded-lg {{ $liveDifference == 0 ? 'bg-gray-50 dark:bg-gray-700/50' : ($liveDifference > 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-rose-50 dark:bg-rose-900/20') }}">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Écart estimé</span>
                    <span class="text-sm font-bold {{ $liveDifference == 0 ? 'text-gray-900 dark:text-white' : ($liveDifference > 0 ? 'text-emerald-600' : 'text-rose-600') }}">
                        {{ $liveDifference >= 0 ? '+' : '' }}{{ number_format($liveDifference, 0, ',', ' ') }} F
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant compté (FCFA) *</label>
                    <input wire:model="countedAmount" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('countedAmount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commentaire</label>
                    <textarea wire:model="closingNote" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Signature du caissier *</label>
                    <input wire:model="cashierSignature" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tapez votre nom ou signature">
                    @error('cashierSignature') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button wire:click="closeRegister" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl transition">Clôturer</button>
                    <button wire:click="$set('showCloseModal', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Validation responsable -->
    <div wire:ignore.self class="fixed inset-0 z-50 flex items-center justify-center {{ $showValidateModal ? '' : 'hidden' }}">
        <div class="fixed inset-0 bg-black/50" wire:click="$set('showValidateModal', false)"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Validation responsable</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Validez la clôture de caisse avec votre signature.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Signature du validateur *</label>
                    <input wire:model="validatorSignature" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tapez votre nom ou signature">
                    @error('validatorSignature') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button wire:click="validateClosing" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">Valider</button>
                    <button wire:click="$set('showValidateModal', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Annuler</button>
                </div>
            </div>
        </div>
    </div>
</div>
