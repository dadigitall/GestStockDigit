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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pertes et casses</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pertes, casses, expirations, vols et consommation interne</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openCreateForm" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nouvelle perte
            </button>
        </div>
    </div>

    @if($showDetail && $detailId)
        <!-- Detail View -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $detail->reference }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $detail->product?->name }} · {{ $detail->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                        @if($detail->status === 'approved') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                        @elseif($detail->status === 'rejected') bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300
                        @else bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 @endif">
                        {{ ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'][$detail->status] ?? $detail->status }}
                    </span>
                    <button wire:click="closeDetail" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Retour</button>
                </div>
            </div>

            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Produit</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ $detail->product?->name }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ \App\Models\StockLoss::lossTypes()[$detail->loss_type] ?? $detail->loss_type }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantité</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($detail->quantity, 2, ',', ' ') }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valeur</p>
                    <p class="text-lg font-semibold text-rose-600 dark:text-rose-400 mt-1">{{ number_format($detail->total_value, 0, ',', ' ') }} F</p>
                </div>
            </div>

            @if($detail->reason)
                <div class="px-6 pb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium text-gray-700 dark:text-gray-300">Motif:</span> {{ $detail->reason }}</p>
                </div>
            @endif

            @if($detail->justification)
                <div class="px-6 pb-4">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Justificatif</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $detail->justification }}</p>
                    </div>
                </div>
            @endif

            @if($detail->notes)
                <div class="px-6 pb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium text-gray-700 dark:text-gray-300">Notes:</span> {{ $detail->notes }}</p>
                </div>
            @endif

            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
                @if($detail->status === 'pending')
                    <button wire:click="approve({{ $detail->id }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition">Approuver</button>
                    <button wire:click="reject({{ $detail->id }})" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Rejeter</button>
                @endif
            </div>
        </div>
    @elseif($showForm)
        <!-- Create Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Nouvelle perte</h2>

                <div class="space-y-4">
                    <!-- Product Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produit *</label>
                        @if($productId)
                            <div class="flex items-center justify-between px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                                <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">{{ $productName }}</span>
                                <button wire:click="removeProduct" class="text-indigo-500 hover:text-indigo-700">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @else
                            <input wire:model.live.debounce="productSearch" type="text" placeholder="Rechercher un produit..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @if(count($productResults) > 0)
                                <div class="mt-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                    @foreach($productResults as $p)
                                        <button wire:click="selectProduct({{ $p['id'] }})" type="button" class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm border-b border-gray-100 dark:border-gray-600 last:border-0">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $p['name'] }}</span>
                                            @if($p['reference'])<span class="text-gray-500 ml-2">Réf: {{ $p['reference'] }}</span>@endif
                                            <span class="text-gray-400 ml-2">· Stock: {{ $p['current_stock'] ?? 'N/A' }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                        @error('productId') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Loss Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de perte *</label>
                        <select wire:model="lossType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach(\App\Models\StockLoss::lossTypes() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quantity & Price -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantité *</label>
                            <input wire:model.live="quantity" type="number" step="0.01" min="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('quantity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prix unitaire *</label>
                            <input wire:model.live="unitPrice" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('unitPrice') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Total -->
                    @if($quantity && $unitPrice)
                        <div class="p-3 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-xl flex items-center justify-between">
                            <span class="text-sm font-medium text-rose-800 dark:text-rose-300">Valeur totale</span>
                            <span class="text-lg font-bold text-rose-600 dark:text-rose-400">{{ number_format((float)$quantity * (float)$unitPrice, 0, ',', ' ') }} F</span>
                        </div>
                    @endif

                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif</label>
                        <textarea wire:model="reason" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Circonstances de la perte..."></textarea>
                    </div>

                    <!-- Justification -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Justificatif</label>
                        <textarea wire:model="justification" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Pièce justificative, rapport, constat..."></textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button wire:click="save" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30" @if(!$productId) disabled @endif>
                            Enregistrer
                        </button>
                        <button wire:click="$set('showForm', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <input wire:model.live.debounce="search" type="text" placeholder="Rechercher une perte..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <select wire:model.live="filterType" class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Tous les types</option>
                    @foreach(\App\Models\StockLoss::lossTypes() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="approved">Approuvé</option>
                    <option value="rejected">Rejeté</option>
                </select>
            </div>
        </div>

        <!-- List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Qté</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Valeur</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($losses as $loss)
                        <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="py-4 px-4">
                                <button wire:click="viewDetail({{ $loss->id }})" class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">{{ $loss->reference }}</button>
                                <span class="text-xs text-gray-400 ml-2">{{ $loss->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-400">{{ $loss->product?->name }}</td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if(in_array($loss->loss_type, ['breakage', 'damaged'])) bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300
                                    @elseif(in_array($loss->loss_type, ['theft', 'unknown_loss'])) bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300
                                    @elseif($loss->loss_type === 'expired') bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300
                                    @else bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 @endif">
                                    {{ \App\Models\StockLoss::lossTypes()[$loss->loss_type] ?? $loss->loss_type }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right text-gray-600 dark:text-gray-400">{{ number_format($loss->quantity, 2, ',', ' ') }}</td>
                            <td class="py-4 px-4 text-right font-medium text-rose-600">-{{ number_format($loss->total_value, 0, ',', ' ') }} F</td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                                    @if($loss->status === 'approved') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                                    @elseif($loss->status === 'rejected') bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300
                                    @else bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 @endif">
                                    {{ ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'][$loss->status] ?? $loss->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="viewDetail({{ $loss->id }})" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-indigo-600 transition" title="Détails">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    @if($loss->status === 'pending')
                                        <button wire:click="approve({{ $loss->id }})" class="p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-emerald-600 transition" title="Approuver">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button wire:click="reject({{ $loss->id }})" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 transition" title="Rejeter">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">Aucune perte enregistrée</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $losses->links() }}
            </div>
        </div>
    @endif
</div>
