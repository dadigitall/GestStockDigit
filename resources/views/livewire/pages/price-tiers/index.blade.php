<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Grilles de prix</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Remises par produit, catégorie, client ou quantité</p>
        </div>
        <button wire:click="openCreateForm" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouveau palier
        </button>
    </div>

    @if($showForm)
        <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $editId ? 'Modifier' : 'Nouveau' }} palier de prix</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produit</label>
                    @if($productId)
                        <div class="flex items-center justify-between px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                            <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">{{ $productName }}</span>
                            <button wire:click="removeProduct" class="text-indigo-500 hover:text-indigo-700"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
                        </div>
                    @else
                        <input wire:model.live.debounce="productSearch" type="text" placeholder="Rechercher un produit (laisser vide pour tous)" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @if(count($productResults) > 0)
                            <div class="mt-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                @foreach($productResults as $p)
                                    <button wire:click="selectProduct({{ $p['id'] }})" type="button" class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm border-b border-gray-100 dark:border-gray-600 last:border-0">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $p['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
                    <select wire:model="categoryId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie client</label>
                    <select wire:model="customerCategoryId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Toutes les catégories client</option>
                        @foreach($customerCategories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                    <select wire:model="customerId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Tous les clients</option>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin</label>
                    <select wire:model="storeId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Tous les magasins</option>
                        @foreach($stores as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Qté min</label>
                        <input wire:model="minQuantity" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Qté max</label>
                        <input wire:model="maxQuantity" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prix *</label>
                    <input wire:model="price" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Libellé (optionnel)</label>
                    <input wire:model="priceLabel" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Ex: Prix fidélité">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorité</label>
                    <input wire:model="priority" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="flex items-center gap-2 mt-6">
                        <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Actif</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date début</label>
                    <input wire:model="startDate" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date fin</label>
                    <input wire:model="endDate" type="date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
                <button wire:click="{{ $editId ? 'update' : 'save' }}" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
                    {{ $editId ? 'Mettre à jour' : 'Enregistrer' }}
                </button>
                <button wire:click="$set('showForm', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Annuler</button>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Produit / Cible</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Qté min</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Qté max</th>
                    <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase">Prix</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Priorité</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Actif</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tiers as $tier)
                    <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $tier->product?->name ?? 'Tous les produits' }}
                            </div>
                            <div class="text-xs text-gray-500 space-x-2">
                                @if($tier->category)<span>Cat: {{ $tier->category->name }}</span>@endif
                                @if($tier->customerCategory)<span>Client cat: {{ $tier->customerCategory->name }}</span>@endif
                                @if($tier->customer)<span>Client: {{ $tier->customer->name }}</span>@endif
                                @if($tier->store)<span>Mag: {{ $tier->store->name }}</span>@endif
                                @if($tier->price_label)<span class="text-indigo-600">{{ $tier->price_label }}</span>@endif
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-400">{{ $tier->min_quantity ?: '-' }}</td>
                        <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-400">{{ $tier->max_quantity ?? '-' }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($tier->price, 0, ',', ' ') }} F</td>
                        <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-400">{{ $tier->priority }}</td>
                        <td class="py-3 px-4 text-center">
                            <button wire:click="toggleActive({{ $tier->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition
                                {{ $tier->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $tier->is_active ? 'Oui' : 'Non' }}
                            </button>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="edit({{ $tier->id }})" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-indigo-600 transition" title="Modifier">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button wire:click="delete({{ $tier->id }})" wire:confirm="Supprimer ce palier ?" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 transition" title="Supprimer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">Aucun palier de prix</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $tiers->links() }}</div>
    </div>
</div>
