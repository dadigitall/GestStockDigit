<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingRequisition ? 'Modifier' : 'Nouvelle' }} demande d'approvisionnement</h3>
    <form wire:submit="saveRequisition" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin *</label>
                <select wire:model="requisition_store_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorité</label>
                <select wire:model="requisition_priority" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="low">Basse</option>
                    <option value="medium">Moyenne</option>
                    <option value="high">Haute</option>
                    <option value="urgent">Urgente</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date souhaitée</label>
                <input wire:model="requisition_desired_date" type="date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Justification</label>
            <textarea wire:model="requisition_justification" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Produits demandés</h4>
                <button type="button" wire:click="addRequisitionItem" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">+ Ajouter un produit</button>
            </div>
            @foreach($requisition_items as $i => $item)
                <div class="flex items-center gap-3 mb-2" wire:key="reqitem-{{ $i }}">
                    <div class="relative flex-1">
                        <input wire:model.live="productSearch" placeholder="Rechercher un produit..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                               wire:keydown.escape="$set('productResults', [])" x-on:click="$wire.set('contextItemIndex', {{ $i }})">
                        @if(strlen($productSearch) >= 2 && count($productResults) > 0)
                            <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-40 overflow-y-auto">
                                @foreach($productResults as $p)
                                    <button type="button" wire:click="selectProduct({{ $p['id'] }}, 'requisition', {{ $i }})"
                                            class="w-full text-left px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        {{ $p['name'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <input wire:model="requisition_items.{{ $i }}.quantity" type="number" step="0.01" min="0.01" placeholder="Qté" class="w-24 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                    <textarea wire:model="requisition_items.{{ $i }}.notes" placeholder="Notes" rows="1" class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm"></textarea>
                    <button type="button" wire:click="removeRequisitionItem({{ $i }})" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
            <button type="button" wire:click="resetRequisitionForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
        </div>
    </form>
</div>
