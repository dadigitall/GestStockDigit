<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un fournisseur..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau fournisseur
        </button>
    </div>

    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingSupplier ? 'Modifier' : 'Nouveau' }} fournisseur</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact</label>
                    <input wire:model="contact_name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sélectionner...</option>
                        <option value="local">Local</option>
                        <option value="international">International</option>
                        <option value="manufacturer">Fabricant</option>
                        <option value="distributor">Distributeur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conditions de paiement</label>
                    <input wire:model="payment_terms" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end gap-2 md:col-span-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Nom</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Contact</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Email</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Téléphone</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Statut</th>
                    <th class="px-6 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $supplier->name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $supplier->contact_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $supplier->email ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $supplier->phone ?? '-' }}</td>
                        <td class="px-6 py-4"><span class="capitalize">{{ $supplier->type ?? '-' }}</span></td>
                        <td class="px-6 py-4">
                            @if($supplier->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">Actif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">Inactif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $supplier->id }})" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Modifier
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <p class="text-lg font-medium mb-1">Aucun fournisseur</p>
                            <p class="text-sm">Ajoutez votre premier fournisseur.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $suppliers->links() }}
        </div>
    </div>
</div>
