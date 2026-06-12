<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingPO ? 'Modifier' : 'Nouvelle' }} commande fournisseur</h3>
    <form wire:submit="savePO" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fournisseur *</label>
                <select wire:model="po_supplier_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Sélectionner...</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin *</label>
                <select wire:model="po_store_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Source</label>
                <select wire:model="po_source" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="manual">Manuelle</option>
                    <option value="requisition">Demande d'approvisionnement</option>
                    <option value="low_stock">Seuil bas</option>
                    <option value="sales_forecast">Prévisions de vente</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date livraison prévue</label>
                <input wire:model="po_delivery_date" type="date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conditions de paiement</label>
                <input wire:model="po_payment_terms" type="text" placeholder="ex: 30 jours" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frais de transport</label>
                <input wire:model="po_shipping_cost" type="number" step="1" min="0" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <input wire:model="po_notes" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Lignes de commande</h4>
                <button type="button" wire:click="addPOItem" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">+ Ajouter une ligne</button>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Produit</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Qté</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">P.U.</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Remise %</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">TVA %</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Total</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po_items as $i => $item)
                        <tr wire:key="poitem-{{ $i }}">
                            <td class="px-3 py-1">
                                <div class="relative">
                                    <input wire:model.live="productSearch" placeholder="Produit..." class="w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500"
                                           wire:keydown.escape="$set('productResults', [])">
                                    @if(strlen($productSearch) >= 2 && count($productResults) > 0)
                                        <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                            @foreach($productResults as $p)
                                                <button type="button" wire:click="selectProduct({{ $p['id'] }}, 'po', {{ $i }})"
                                                        class="w-full text-left px-2 py-1 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs text-gray-900 dark:text-white">{{ $p['name'] }}</button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-1"><input wire:model="po_items.{{ $i }}.quantity" type="number" step="0.01" min="0.01" class="w-20 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1.5 text-xs"></td>
                            <td class="px-3 py-1"><input wire:model="po_items.{{ $i }}.unit_price" type="number" step="1" min="0" class="w-24 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1.5 text-xs"></td>
                            <td class="px-3 py-1"><input wire:model="po_items.{{ $i }}.discount" type="number" step="0.01" min="0" max="100" class="w-16 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1.5 text-xs"></td>
                            <td class="px-3 py-1"><input wire:model="po_items.{{ $i }}.tax_rate" type="number" step="0.01" min="0" max="100" class="w-16 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1.5 text-xs"></td>
                            <td class="px-3 py-1 text-right text-xs text-gray-900 dark:text-white font-medium">
                                @php $lineTotal = ($item['quantity'] * $item['unit_price']) * (1 - ($item['discount']/100)) * (1 + ($item['tax_rate']/100)); @endphp
                                {{ number_format($lineTotal, 0, ',', ' ') }}
                            </td>
                            <td class="px-3 py-1">
                                <button type="button" wire:click="removePOItem({{ $i }})" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
            <button type="button" wire:click="resetPOForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
        </div>
    </form>
</div>
