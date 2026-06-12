<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Nouveau mouvement de stock</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select wire:model="movementType" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="adjustment_positive">Ajustement +</option>
                <option value="adjustment_negative">Ajustement -</option>
                <option value="inventory">Inventaire</option>
                <option value="breakage">Casse</option>
                <option value="loss">Perte</option>
                <option value="expiry">Expiration</option>
                <option value="donation">Don</option>
                <option value="sample">Échantillon</option>
                <option value="internal_consumption">Consommation interne</option>
                <option value="transfer_out">Transfert sortant</option>
                <option value="transfer_in">Transfert entrant</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Produit</label>
            <div class="relative">
                <input wire:model.live.debounce.300ms="productSearch" type="text" placeholder="Rechercher un produit..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                @if(!empty($productResults))
                    <div class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        @foreach($productResults as $p)
                            <button wire:click="selectProduct({{ $p['id'] }})" type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <span class="font-medium">{{ $p['name'] }}</span>
                                @if($p['sku'])<span class="text-gray-400 ml-1">({{ $p['sku'] }})</span>@endif
                                <span class="text-gray-400 ml-2">Stock: {{ number_format($p['stock_quantity'] ?? 0) }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
                @if($movementProductId)
                    @php $selected = \App\Models\Product::find($movementProductId); @endphp
                    @if($selected)
                        <div class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">
                            {{ $selected->name }} (stock: {{ number_format($selected->stock_quantity ?? 0) }})
                            <button wire:click="$set('movementProductId', null)" type="button" class="ml-1 text-red-500 hover:text-red-700">&times;</button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quantité</label>
            <input wire:model="movementQuantity" type="number" step="0.01" min="0.01" placeholder="0" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
        </div>
        @if(in_array($movementType, ['adjustment_positive', 'adjustment_negative', 'breakage', 'loss', 'expiry', 'donation', 'sample', 'internal_consumption']))
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin</label>
            <select wire:model="movementStoreId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Sélectionner</option>
                @foreach($stores as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if(in_array($movementType, ['transfer_out', 'transfer_in']))
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin source</label>
            <select wire:model="movementSourceStoreId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Sélectionner</option>
                @foreach($stores as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin destination</label>
            <select wire:model="movementDestinationStoreId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Sélectionner</option>
                @foreach($stores as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="md:col-span-3">
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Motif (optionnel)</label>
            <input wire:model="movementNotes" type="text" placeholder="Raison du mouvement..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
        </div>
    </div>
    <div class="mt-4 flex gap-2">
        <button wire:click="saveMovement" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
        <button wire:click="resetMovementForm" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Annuler</button>
    </div>
</div>
