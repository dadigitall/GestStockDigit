<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Promotions</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Promotions, remises, coupons et offres spéciales</p>
        </div>
        <button wire:click="$set('showForm', true)" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouvelle promotion
        </button>
    </div>

    @if($showForm)
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $editId ? 'Modifier' : 'Nouvelle' }} promotion</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                    <input wire:model="name" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model="promotionType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @foreach(\App\Models\Promotion::types() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de remise</label>
                    <select wire:model="discountType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="percentage">Pourcentage (%)</option>
                        <option value="fixed">Montant fixe (F)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valeur de la remise</label>
                    <input wire:model="discountValue" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Ex: 20 (%) ou 5000 (F)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Achat minimum (F)</label>
                    <input wire:model="minPurchase" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Qté min</label>
                    <input wire:model="minQuantity" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Qté max</label>
                    <input wire:model="maxQuantity" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                @if(in_array($promotionType, ['buy_x_get_y']))
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acheter X</label>
                    <input wire:model="buyQuantity" type="number" step="1" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Obtenir Y</label>
                    <input wire:model="getQuantity" type="number" step="1" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date début</label>
                    <input wire:model="startsAt" type="datetime-local" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date fin</label>
                    <input wire:model="endsAt" type="datetime-local" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorité</label>
                    <input wire:model="priority" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                </div>
                <div class="md:col-span-2 border-t border-gray-100 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Cibles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produits</label>
                            <input wire:model.live.debounce="productSearch" type="text" placeholder="Rechercher..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-2">
                            @if(count($this->productResults) > 0)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden mb-2">
                                    @foreach($productResults as $p)
                                        <button wire:click="selectProduct({{ $p['id'] }})" type="button" class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm border-b border-gray-100 dark:border-gray-600 last:border-0">
                                            {{ $p['name'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @if(count($selectedProducts) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($selectedProducts as $pid)
                                        @php $p = \App\Models\Product::find($pid); @endphp
                                        @if($p)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded text-xs">
                                                {{ $p->name }}
                                                <button wire:click="removeProduct({{ $pid }})" class="text-indigo-500 hover:text-indigo-700">&times;</button>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégories</label>
                            <select wire:model="selectedCategories" multiple class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm h-24">
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clients</label>
                            <select wire:model="selectedCustomers" multiple class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm h-24">
                                @foreach($customers as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Magasins</label>
                            <select wire:model="selectedStores" multiple class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm h-24">
                                @foreach($stores as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
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

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
        <div class="flex items-center gap-4 flex-wrap">
            <input wire:model.live.debounce="search" type="text" placeholder="Rechercher..." class="flex-1 min-w-[200px] rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <select wire:model.live="filterType" class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm">
                <option value="">Tous les types</option>
                @foreach(\App\Models\Promotion::types() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm text-sm">
                <option value="">Tous les statuts</option>
                <option value="active">Actives</option>
                <option value="inactive">Inactives</option>
            </select>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Remise</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Période</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Actif</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $promo)
                    <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">{{ $promo->name }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                {{ \App\Models\Promotion::types()[$promo->type] ?? $promo->type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">
                            @if($promo->discount_type === 'percentage')
                                {{ $promo->discount_value }}%
                            @else
                                {{ number_format($promo->discount_value, 0, ',', ' ') }} F
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500">
                            @if($promo->starts_at && $promo->ends_at)
                                {{ $promo->starts_at->format('d/m/Y') }} - {{ $promo->ends_at->format('d/m/Y') }}
                            @elseif($promo->starts_at)
                                À partir du {{ $promo->starts_at->format('d/m/Y') }}
                            @elseif($promo->ends_at)
                                Jusqu'au {{ $promo->ends_at->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">Permanente</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button wire:click="toggleActive({{ $promo->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition
                                {{ $promo->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $promo->is_active ? 'Oui' : 'Non' }}
                            </button>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="edit({{ $promo->id }})" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-indigo-600 transition" title="Modifier">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button wire:click="delete({{ $promo->id }})" wire:confirm="Supprimer cette promotion ?" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 transition" title="Supprimer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center text-gray-500 dark:text-gray-400">Aucune promotion</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $promotions->links() }}</div>
    </div>
</div>
