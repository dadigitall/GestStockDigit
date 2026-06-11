<div>
    <div class="flex justify-end mb-6">
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau magasin
        </button>
    </div>

    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingStore ? 'Modifier' : 'Nouveau' }} magasin</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                    <input wire:model="code" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @error('code') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="boutique">Boutique</option>
                        <option value="magasin">Magasin</option>
                        <option value="entrepot">Entrepôt</option>
                        <option value="depot">Dépôt</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                    <input wire:model="phone" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                    <input wire:model="address" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                </div>
                <div class="flex items-end gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="allows_stock" type="checkbox" class="rounded border-gray-300 text-indigo-600"> Stock
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="allows_sales" type="checkbox" class="rounded border-gray-300 text-indigo-600"> Ventes
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="allows_cash_register" type="checkbox" class="rounded border-gray-300 text-indigo-600"> Caisse
                    </label>
                </div>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($stores as $store)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $store->name }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ strtoupper($store->code) }} · {{ ucfirst($store->type) }}</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $store->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        <button wire:click="edit({{ $store->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>
                </div>
                @if($store->address)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $store->address }}</p>
                @endif
                <div class="flex gap-3 mt-3 text-xs text-gray-500">
                    @if($store->allows_stock) <span class="text-green-600">Stock</span> @endif
                    @if($store->allows_sales) <span class="text-blue-600">Ventes</span> @endif
                    @if($store->allows_cash_register) <span class="text-amber-600">Caisse</span> @endif
                </div>
            </div>
        @empty
            <div class="lg:col-span-3 text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="text-lg font-medium">Aucun magasin</p>
                <p class="text-sm">Créez votre premier magasin ou entrepôt.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $stores->links() }}</div>
</div>
