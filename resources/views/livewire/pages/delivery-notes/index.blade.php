<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <select wire:model.live="filterStatus" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Tous statuts</option>
                @foreach(['draft', 'delivered', 'partially_delivered', 'cancelled'] as $s)
                    <option value="{{ $s }}">{{ $this->statusLabel($s) }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            + Nouveau bon de livraison
        </button>
    </div>

    {{-- Formulaire --}}
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ $editId ? 'Modifier' : 'Nouveau' }} bon de livraison</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Client <span class="text-red-500">*</span></label>
                    <select wire:model="customerId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        <option value="">Sélectionner un client...</option>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('customerId') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date de livraison <span class="text-red-500">*</span></label>
                    <input wire:model="deliveryDate" type="date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                    @error('deliveryDate') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Produits --}}
            <div class="mt-4">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Produits <span class="text-red-500">*</span></label>
                <div class="relative mb-3">
                    <input wire:model.live.debounce.300ms="productSearch" type="text" placeholder="Rechercher un produit par nom, référence ou code-barres..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                    @if(!empty($productResults))
                        <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @foreach($productResults as $p)
                                <button wire:click="addToCart({{ $p['id'] }})" type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <span class="font-medium">{{ $p['name'] }}</span>
                                    <span class="text-gray-400 text-xs ml-2">{{ $p['reference'] }}</span>
                                    <span class="text-gray-400 text-xs ml-2">Stock: {{ number_format($p['stock_quantity'] ?? 0, 0, ',', ' ') }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(!empty($cart))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm mb-3">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs w-20">Unité</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs w-20">Qté demandée</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs w-20">Qté livrée</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Notes</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($cart as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $item['product_name'] }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400 text-xs">{{ $item['unit'] }}</td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'quantity_requested', $event.target.value)" type="number" step="0.01" min="0" value="{{ $item['quantity_requested'] }}" class="w-20 text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-1 py-1 text-xs mx-auto block">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'quantity_delivered', $event.target.value)" type="number" step="0.01" min="0" value="{{ $item['quantity_delivered'] }}" class="w-20 text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-1 py-1 text-xs mx-auto block">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'notes', $event.target.value)" type="text" value="{{ $item['notes'] }}" class="w-full border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs" placeholder="Notes...">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic mb-3">Aucun produit ajouté. Utilisez la recherche ci-dessus pour ajouter des produits.</p>
                @endif
                @error('cart') <span class="text-xs text-red-500 block mb-2">{{ $message }}</span> @enderror
            </div>

            {{-- Notes --}}
            <div class="mt-4">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model="notes" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm"></textarea>
            </div>

            <div class="mt-4 flex gap-2">
                <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">{{ $editId ? 'Mettre à jour' : 'Créer le bon de livraison' }}</button>
                <button wire:click="resetForm" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Annuler</button>
            </div>
        </div>
    @endif

    {{-- Détail --}}
    @if($showDetail && $detail)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] flex flex-col">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $detail->reference }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $detail->customer?->name ?? 'Client inconnu' }}</p>
                    </div>
                    <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl leading-none">&times;</button>
                </div>
                <div class="overflow-y-auto p-6 space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 text-xs">Statut</span>
                            <p><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $this->statusBadge($detail->status) }}">{{ $this->statusLabel($detail->status) }}</span></p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Client</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->customer?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Date livraison</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->delivery_date?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Créé le</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        @if($detail->received_date)
                            <div><span class="text-gray-500 text-xs">Reçu le</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->received_date->format('d/m/Y') }}</p></div>
                        @endif
                    </div>

                    @if($detail->notes)
                        <div class="text-sm"><span class="text-gray-500 text-xs">Notes</span><p class="text-gray-900 dark:text-white mt-1">{{ $detail->notes }}</p></div>
                    @endif

                    <div>
                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Articles</h4>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Unité</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Qté demandée</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Qté livrée</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($detail->items as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $item->product_name ?? ($item->product?->name ?? '#'.$item->product_id) }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400 text-xs">{{ $item->unit ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ number_format($item->quantity_requested, 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ number_format($item->quantity_delivered, 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400 text-xs">{{ $item->notes ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-xs text-gray-400">Aucun article.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <a href="{{ route('delivery-notes.print', $detail->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            Imprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Liste --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Client</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Date livraison</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Statut</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Créé le</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($deliveryNotes as $dn)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-xs font-mono font-medium text-gray-900 dark:text-white">{{ $dn->reference }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ $dn->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $dn->delivery_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $this->statusBadge($dn->status) }}">{{ $this->statusLabel($dn->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $dn->created_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <button wire:click="view({{ $dn->id }})" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-medium rounded hover:bg-gray-200 dark:hover:bg-gray-600">Détail</button>

                                    @if($dn->status === 'draft')
                                        <button wire:click="edit({{ $dn->id }})" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[10px] font-medium rounded hover:bg-blue-200 dark:hover:bg-blue-800/50">Modifier</button>
                                        <button wire:click="markDelivered({{ $dn->id }})" class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[10px] font-medium rounded hover:bg-emerald-200 dark:hover:bg-emerald-800/50">Livrer</button>
                                        <button wire:click="markPartial({{ $dn->id }})" class="px-2 py-1 bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 text-[10px] font-medium rounded hover:bg-amber-200 dark:hover:bg-amber-800/50">Partiel</button>
                                        <button wire:click="cancel({{ $dn->id }})" onclick="return confirm('Annuler ce bon de livraison ?')" class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[10px] font-medium rounded hover:bg-red-200 dark:hover:bg-red-800/50">Annuler</button>
                                    @endif

                                    @if($dn->status === 'partially_delivered')
                                        <button wire:click="markDelivered({{ $dn->id }})" class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[10px] font-medium rounded hover:bg-emerald-200 dark:hover:bg-emerald-800/50">Livrer</button>
                                        <button wire:click="cancel({{ $dn->id }})" onclick="return confirm('Annuler ce bon de livraison ?')" class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[10px] font-medium rounded hover:bg-red-200 dark:hover:bg-red-800/50">Annuler</button>
                                    @endif

                                    <a href="{{ route('delivery-notes.print', $dn->id) }}" target="_blank" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-medium rounded hover:bg-gray-200 dark:hover:bg-gray-600">Imprimer</a>

                                    <button wire:click="delete({{ $dn->id }})" onclick="return confirm('Supprimer ce bon de livraison ?')" class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[10px] font-medium rounded hover:bg-red-200 dark:hover:bg-red-800/50">Supprimer</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Aucun bon de livraison trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $deliveryNotes->links() }}
    </div>
</div>
