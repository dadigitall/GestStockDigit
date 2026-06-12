<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Enregistrer un paiement</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant</label>
                        <input wire:model="paymentAmount" type="number" step="0.01" min="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showPaymentModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Annuler</button>
                    <button wire:click="savePayment" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Valider</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Detail view -->
    @if($showDetail && $detail)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Facture {{ $detail->reference }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Créée le {{ $detail->created_at->format('d/m/Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('invoices.print', $detail->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimer
                    </a>
                    <button wire:click="resetForm" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Retour</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Client</h4>
                    <p class="text-gray-900 dark:text-white font-medium">{{ $detail->customer?->name ?? '-' }}</p>
                    <p class="text-sm text-gray-500">{{ $detail->customer?->address ?? '' }}</p>
                    <p class="text-sm text-gray-500">{{ $detail->customer?->phone ?? '' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500"><span class="font-medium">Date d'émission :</span> {{ $detail->issue_date->format('d/m/Y') }}</p>
                    <p class="text-sm text-gray-500"><span class="font-medium">Échéance :</span> {{ $detail->due_date?->format('d/m/Y') ?? '-' }}</p>
                    <p class="text-sm text-gray-500"><span class="font-medium">Type :</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $detail->type === 'sale' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : '' }}
                            {{ $detail->type === 'proforma' ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300' : '' }}
                            {{ $detail->type === 'deposit' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : '' }}
                            {{ $detail->type === 'balance' ? 'bg-cyan-100 dark:bg-cyan-900/50 text-cyan-700 dark:text-cyan-300' : '' }}
                            {{ $detail->type === 'credit_note' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}">
                            {{ ['sale' => 'Vente', 'proforma' => 'Proforma', 'deposit' => 'Acompte', 'balance' => 'Solde', 'credit_note' => 'Avoir'][$detail->type] ?? $detail->type }}
                        </span>
                    </p>
                    <p class="text-sm text-gray-500"><span class="font-medium">Statut :</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $detail->status === 'draft' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '' }}
                            {{ $detail->status === 'sent' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : '' }}
                            {{ $detail->status === 'paid' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : '' }}
                            {{ $detail->status === 'partially_paid' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : '' }}
                            {{ $detail->status === 'overdue' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}
                            {{ $detail->status === 'cancelled' ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : '' }}">
                            {{ ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'partially_paid' => 'Partiellement payée', 'overdue' => 'En retard', 'cancelled' => 'Annulée'][$detail->status] ?? $detail->status }}
                        </span>
                    </p>
                </div>
            </div>

            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Qté</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Désignation</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">PU HT</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Remise</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Total HT</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">TVA</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Total TTC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($detail->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                            <td class="px-4 py-2 text-gray-900 dark:text-white">
                                {{ $item->product_name }}
                                @if($item->product_reference)<span class="text-xs text-gray-500">({{ $item->product_reference }})</span>@endif
                            </td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ $item->discount > 0 ? number_format($item->discount, 0, ',', ' ') . ' F' : '-' }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($item->quantity * $item->unit_price, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($item->tax_rate, 0) }}%</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">{{ number_format($item->subtotal, 0, ',', ' ') }} F</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-end">
                <div class="w-72 space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400"><span>Sous-total HT</span><span>{{ number_format($detail->subtotal, 0, ',', ' ') }} F</span></div>
                    @if($detail->discount > 0)<div class="flex justify-between text-gray-600 dark:text-gray-400"><span>Remise</span><span>-{{ number_format($detail->discount, 0, ',', ' ') }} F</span></div>@endif
                    <div class="flex justify-between text-gray-600 dark:text-gray-400"><span>TVA</span><span>{{ number_format($detail->tax_amount, 0, ',', ' ') }} F</span></div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-1"><span>Total TTC</span><span>{{ number_format($detail->total, 0, ',', ' ') }} F</span></div>
                    @if($detail->paid_amount > 0)
                        <div class="flex justify-between text-green-600"><span>Payé</span><span>{{ number_format($detail->paid_amount, 0, ',', ' ') }} F</span></div>
                        <div class="flex justify-between text-red-600 font-medium"><span>Reste dû</span><span>{{ number_format($detail->amount_due, 0, ',', ' ') }} F</span></div>
                    @endif
                </div>
            </div>

            @if($detail->payment_terms || $detail->notes)
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-sm text-gray-600 dark:text-gray-400">
                    @if($detail->payment_terms)<p><span class="font-medium">Conditions de paiement :</span> {{ $detail->payment_terms }}</p>@endif
                    @if($detail->notes)<p class="mt-1"><span class="font-medium">Notes :</span> {{ $detail->notes }}</p>@endif
                </div>
            @endif

            <div class="flex items-center gap-2 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                @if($detail->status === 'draft')
                    <button wire:click="edit({{ $detail->id }})" class="px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg">Modifier</button>
                    <button wire:click="markSent({{ $detail->id }})" class="px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg">Marquer envoyée</button>
                    <button wire:click="cancel({{ $detail->id }})" class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg">Annuler</button>
                @endif
                @if(in_array($detail->status, ['sent', 'partially_paid', 'overdue']))
                    <button wire:click="markPaid({{ $detail->id }})" class="px-3 py-1.5 text-sm font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg">Marquer payée</button>
                    <button wire:click="recordPayment({{ $detail->id }})" class="px-3 py-1.5 text-sm font-medium text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg">Enregistrer paiement</button>
                @endif
                @if($detail->status === 'draft')
                    <button wire:click="delete({{ $detail->id }})" class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg" onclick="return confirm('Supprimer cette facture ?')">Supprimer</button>
                @endif
            </div>
        </div>

    <!-- Form view -->
    @elseif($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ $editId ? 'Modifier' : 'Nouvelle' }} facture</h2>

            <form wire:submit.prevent="{{ $editId ? 'update' : 'create' }}">
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                        <select wire:model="customerId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <option value="">Sélectionner un client</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('customerId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select wire:model="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <option value="sale">Vente</option>
                            <option value="proforma">Proforma</option>
                            <option value="deposit">Acompte</option>
                            <option value="balance">Solde</option>
                            <option value="credit_note">Avoir</option>
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date d'émission</label>
                        <input wire:model="issueDate" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        @error('issueDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date d'échéance</label>
                        <input wire:model="dueDate" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                </div>

                <!-- Product search -->
                <div class="relative mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ajouter un produit</label>
                    <input wire:model.live.debounce.300ms="productSearch" type="text" placeholder="Rechercher un produit..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    @if(strlen($productSearch) >= 2 && count($productResults) > 0)
                        <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            @foreach($productResults as $p)
                                <button type="button" wire:click="addToCart({{ $p['id'] }})" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    {{ $p['name'] }}
                                    @if($p['reference'])<span class="text-gray-500">({{ $p['reference'] }})</span>@endif
                                    <span class="float-right text-gray-400">{{ number_format($p['sale_price'] ?? 0, 0, ',', ' ') }} F</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Cart -->
                @if(count($cart) > 0)
                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Produit</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400" style="width:70px">Qté</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400" style="width:110px">PU HT</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400" style="width:90px">Remise %</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400" style="width:90px">TVA %</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400" style="width:120px">Total TTC</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400" style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($cart as $index => $item)
                                    <tr>
                                        <td class="px-3 py-2 text-gray-900 dark:text-white font-medium">{{ $item['name'] }}</td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'qty', $event.target.value)" type="number" step="1" min="0.01" value="{{ $item['qty'] }}" class="w-full text-center px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'price', $event.target.value)" type="number" step="1" min="0" value="{{ $item['price'] }}" class="w-full text-right px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'discount', $event.target.value)" type="number" step="1" min="0" max="100" value="{{ $item['discount'] }}" class="w-full text-right px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'tax_rate', $event.target.value)" type="number" step="1" min="0" value="{{ $item['tax_rate'] }}" class="w-full text-right px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                                        </td>
                                        <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white">{{ number_format($item['subtotal'] ?? 0, 0, ',', ' ') }} F</td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mb-6">
                        <div class="w-72 space-y-1 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400"><span>Sous-total HT</span><span>{{ number_format($subtotal, 0, ',', ' ') }} F</span></div>
                            @if($discount > 0)<div class="flex justify-between text-gray-600 dark:text-gray-400"><span>Remise</span><span>-{{ number_format($discount, 0, ',', ' ') }} F</span></div>@endif
                            <div class="flex justify-between text-gray-600 dark:text-gray-400"><span>TVA</span><span>{{ number_format($taxAmount, 0, ',', ' ') }} F</span></div>
                            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-1"><span>Total TTC</span><span>{{ number_format($total, 0, ',', ' ') }} F</span></div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm mb-4">
                        Aucun produit ajouté. Utilisez la recherche ci-dessus pour ajouter des produits.
                    </div>
                @endif
                @error('cart') <p class="text-red-500 text-xs mb-4">{{ $message }}</p> @enderror

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conditions de paiement</label>
                        <textarea wire:model="paymentTerms" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <button type="button" wire:click="resetForm" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">{{ $editId ? 'Mettre à jour' : 'Enregistrer' }}</button>
                </div>
            </form>
        </div>

    <!-- List view -->
    @else
        <!-- Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par référence ou client..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="filterStatus" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="draft">Brouillon</option>
                    <option value="sent">Envoyée</option>
                    <option value="paid">Payée</option>
                    <option value="partially_paid">Partiellement payée</option>
                    <option value="overdue">En retard</option>
                    <option value="cancelled">Annulée</option>
                </select>
                <select wire:model.live="filterType" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">Tous les types</option>
                    <option value="sale">Vente</option>
                    <option value="proforma">Proforma</option>
                    <option value="deposit">Acompte</option>
                    <option value="balance">Solde</option>
                    <option value="credit_note">Avoir</option>
                </select>
                <button wire:click="$set('showForm', true)" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle facture
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Client</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Échéance</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Total TTC</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Payé</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Reste</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $inv->reference }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $inv->customer?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $inv->issue_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $inv->due_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($inv->total, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-right text-green-600 font-medium">{{ number_format($inv->paid_amount, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-right text-red-600 font-medium">{{ number_format($inv->amount_due, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $inv->status === 'draft' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '' }}
                                    {{ $inv->status === 'sent' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : '' }}
                                    {{ $inv->status === 'paid' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : '' }}
                                    {{ $inv->status === 'partially_paid' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : '' }}
                                    {{ $inv->status === 'overdue' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}
                                    {{ $inv->status === 'cancelled' ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : '' }}">
                                    {{ ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'partially_paid' => 'Partielle', 'overdue' => 'En retard', 'cancelled' => 'Annulée'][$inv->status] ?? $inv->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $inv->type === 'sale' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : '' }}
                                    {{ $inv->type === 'proforma' ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300' : '' }}
                                    {{ $inv->type === 'deposit' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : '' }}
                                    {{ $inv->type === 'balance' ? 'bg-cyan-100 dark:bg-cyan-900/50 text-cyan-700 dark:text-cyan-300' : '' }}
                                    {{ $inv->type === 'credit_note' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}">
                                    {{ ['sale' => 'Vente', 'proforma' => 'Proforma', 'deposit' => 'Acompte', 'balance' => 'Solde', 'credit_note' => 'Avoir'][$inv->type] ?? $inv->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="view({{ $inv->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs">Voir</button>
                                    @if($inv->status === 'draft')
                                        <button wire:click="edit({{ $inv->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs">Modifier</button>
                                        <button wire:click="markSent({{ $inv->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs">Envoyer</button>
                                        <button wire:click="delete({{ $inv->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs" onclick="return confirm('Supprimer cette facture ?')">Supprimer</button>
                                    @endif
                                    @if(in_array($inv->status, ['sent', 'partially_paid', 'overdue']))
                                        <button wire:click="markPaid({{ $inv->id }})" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-xs">Payée</button>
                                        <button wire:click="recordPayment({{ $inv->id }})" class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 text-xs">Paiement</button>
                                    @endif
                                    @if(in_array($inv->status, ['sent', 'partially_paid']))
                                        <button wire:click="cancel({{ $inv->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs">Annuler</button>
                                    @endif
                                    <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 text-xs">Imprimer</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucune facture trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $invoices->links() }}</div>
        </div>
    @endif
</div>
