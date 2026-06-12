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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Retours clients</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Retours, avoirs, échanges et annulations de vente</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openCreateForm" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nouveau retour
            </button>
        </div>
    </div>

    @if($showDetail && $detailId)
        <!-- Detail View -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Retour {{ $detail->reference }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $detail->customer?->name }} · {{ $detail->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                        @if($detail->status === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                        @elseif($detail->status === 'approved') bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300
                        @elseif($detail->status === 'rejected') bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300
                        @else bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 @endif">
                        {{ $detail->status }}
                    </span>
                    <button wire:click="closeDetail" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Retour</button>
                </div>
            </div>

            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ ucfirst($detail->return_type) }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Motif</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ str_replace('_', ' ', ucfirst($detail->reason)) }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remboursement</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($detail->refund_amount, 0, ',', ' ') }} F</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Méthode</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ $detail->refund_method }}</p>
                </div>
            </div>

            @if($detail->margin_impact)
            <div class="px-6 pb-4">
                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl flex items-center justify-between">
                    <span class="text-sm font-medium text-amber-800 dark:text-amber-300">Impact sur la marge</span>
                    <span class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ number_format($detail->margin_impact, 0, ',', ' ') }} F</span>
                </div>
            </div>
            @endif

            @if($detail->creditNote)
            <div class="px-6 pb-4">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center justify-between">
                    <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Avoir client</span>
                    <a href="{{ route('invoices.print', $detail->creditNote) }}" target="_blank" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 hover:underline">{{ $detail->creditNote->reference }}</a>
                </div>
            </div>
            @endif

            @if($detail->reason_description)
                <div class="px-6 pb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium text-gray-700 dark:text-gray-300">Description:</span> {{ $detail->reason_description }}</p>
                </div>
            @endif

            <!-- Items -->
            <div class="border-t border-gray-100 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Articles retournés</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="text-right py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Qté</th>
                                <th class="text-right py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">PU</th>
                                <th class="text-right py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="text-center py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">État</th>
                                <th class="text-center py-3 px-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Réintégré</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detail->items as $item)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50">
                                    <td class="py-3 px-2 font-medium text-gray-900 dark:text-white">{{ $item->product?->name }}</td>
                                    <td class="py-3 px-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($item->quantity, 0) }}</td>
                                    <td class="py-3 px-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                    <td class="py-3 px-2 text-right font-medium text-gray-900 dark:text-white">{{ number_format($item->total, 0, ',', ' ') }}</td>
                                    <td class="py-3 px-2 text-center">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium
                                            @if($item->product_condition === 'good') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                                            @else bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 @endif">
                                            {{ $item->product_condition }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        @if($item->restock)
                                            <span class="text-emerald-600 text-lg">✓</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
                @if($detail->status === 'pending')
                    <button wire:click="approve({{ $detail->id }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition">Approuver</button>
                    <button wire:click="reject({{ $detail->id }})" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Rejeter</button>
                @endif
            </div>
        </div>
    @elseif($showForm)
        <!-- Create Form -->
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Nouveau retour client</h2>

                <!-- Sale Search -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rechercher une vente (ou saisir manuellement)</label>
                    <input wire:model.live.debounce="saleSearch" type="text" placeholder="Numéro de vente..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @if(strlen($saleSearch) >= 2 && count($saleResults) > 0)
                        <div class="mt-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                            @foreach($saleResults as $sale)
                                <button wire:click="selectSale({{ $sale->id }})" class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm border-b border-gray-100 dark:border-gray-600 last:border-0">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $sale->reference }}</span>
                                    <span class="text-gray-500 ml-2">{{ $sale->customer?->name }}</span>
                                    <span class="text-gray-500 ml-2">· {{ number_format($sale->total, 0, ',', ' ') }} F</span>
                                    <span class="text-gray-400 ml-2">· {{ $sale->created_at->format('d/m/Y') }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client *</label>
                        <select wire:model="customerId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sélectionner un client</option>
                            @foreach($customers as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('customerId') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de retour</label>
                        <select wire:model="returnType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="partial">Partiel</option>
                            <option value="total">Total</option>
                            <option value="exchange">Échange</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif *</label>
                        <select wire:model="reason" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="defective">Défectueux</option>
                            <option value="wrong_product">Produit incorrect</option>
                            <option value="changed_mind">Changé d'avis</option>
                            <option value="expired">Périmé</option>
                            <option value="damaged">Endommagé</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mode de remboursement</label>
                        <select wire:model="refundMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cash">Espèces</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="card">Carte</option>
                            <option value="credit_note">Avoir</option>
                            <option value="exchange">Échange</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description du motif</label>
                    <textarea wire:model="reasonDescription" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center gap-3 mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="globalRestock" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Réintégrer tous les articles en stock</span>
                    </label>
                </div>

                <!-- Cart -->
                @if(count($cart) > 0)
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Articles retournés</h3>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <th class="text-left py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                    <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Qté vendue</th>
                                    <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Qté retour</th>
                                    <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">PU</th>
                                    <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="text-center py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">État</th>
                                    <th class="text-center py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="text-center py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $index => $item)
                                    <tr class="border-b border-gray-50 dark:border-gray-700/50">
                                        <td class="py-2 font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                                        <td class="py-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($item['qty'], 0) }}</td>
                                        <td class="py-2 text-right">
                                            <input wire:model="cart.{{ $index }}.return_qty" type="number" min="0" max="{{ $item['qty'] }}" class="w-20 text-right rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="py-2 text-right text-gray-600 dark:text-gray-400">{{ number_format($item['price'], 0, ',', ' ') }}</td>
                                        <td class="py-2 text-right font-medium text-gray-900 dark:text-white">{{ number_format($item['return_qty'] * $item['price'], 0, ',', ' ') }}</td>
                                        <td class="py-2 text-center">
                                            <select wire:model="cart.{{ $index }}.condition" class="text-xs rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm">
                                                <option value="good">Bon</option>
                                                <option value="damaged">Abîmé</option>
                                                <option value="defective">Défectueux</option>
                                                <option value="expired">Périmé</option>
                                            </select>
                                        </td>
                                        <td class="py-2 text-center">
                                            <input type="checkbox" wire:model="cart.{{ $index }}.restock" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        </td>
                                        <td class="py-2 text-center">
                                            <button wire:click="removeFromCart({{ $index }})" class="text-rose-500 hover:text-rose-700 transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td colspan="4" class="py-3 text-right font-bold text-gray-900 dark:text-white">Total remboursement</td>
                                    <td class="py-3 text-right font-bold text-rose-600">{{ number_format(collect($cart)->sum(fn($i) => $i['return_qty'] * $i['price']), 0, ',', ' ') }} F</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @elseif($saleId)
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">Aucun article dans cette vente</div>
                @endif

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
                    <button wire:click="save" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30" @if(count($cart) === 0) disabled @endif>
                        Enregistrer le retour
                    </button>
                    <button wire:click="$set('showForm', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    @else
        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <input wire:model.live.debounce="search" type="text" placeholder="Rechercher un retour..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="approved">Approuvé</option>
                    <option value="completed">Terminé</option>
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
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Motif</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="py-4 px-4">
                                <button wire:click="viewDetail({{ $return->id }})" class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">{{ $return->reference }}</button>
                                <span class="text-xs text-gray-400 ml-2">{{ $return->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-400">{{ $return->customer?->name }}</td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($return->return_type === 'exchange') bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300
                                    @elseif($return->return_type === 'total') bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300
                                    @else bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 @endif">
                                    {{ ucfirst($return->return_type) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-400">{{ str_replace('_', ' ', ucfirst($return->reason)) }}</td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium
                                    @if($return->status === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300
                                    @elseif($return->status === 'approved') bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300
                                    @elseif($return->status === 'rejected') bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300
                                    @else bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 @endif">
                                    {{ $return->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right font-medium text-rose-600">
                                -{{ number_format($return->refund_amount, 0, ',', ' ') }} F
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="viewDetail({{ $return->id }})" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-indigo-600 transition" title="Détails">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    @if($return->status === 'pending')
                                        <button wire:click="approve({{ $return->id }})" class="p-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-emerald-600 transition" title="Approuver">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button wire:click="reject({{ $return->id }})" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 transition" title="Rejeter">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">Aucun retour trouvé</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $returns->links() }}
            </div>
        </div>

        <!-- Cancel Sale Modal (8.52) -->
        <div wire:ignore.self class="fixed inset-0 z-50 flex items-center justify-center {{ $showCancelModal ? '' : 'hidden' }}">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showCancelModal', false)"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Annulation de vente</h3>
                <p class="text-sm text-rose-600 dark:text-rose-400 mb-4">Cette action est irréversible. Le stock sera restauré et la caisse impactée.</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motif d'annulation *</label>
                        <textarea wire:model="cancelReason" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Expliquez le motif..."></textarea>
                        @error('cancelReason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button wire:click="cancelSale" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-xl transition">Confirmer l'annulation</button>
                        <button wire:click="$set('showCancelModal', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Annuler</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
