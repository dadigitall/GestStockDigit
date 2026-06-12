<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Barre d'outils --}}
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
                @foreach(['draft', 'requested', 'approved', 'prepared', 'shipped', 'partially_received', 'fully_received', 'rejected', 'cancelled'] as $s)
                    <option value="{{ $s }}">{{ $this->statusLabel($s) }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            + Nouveau transfert
        </button>
    </div>

    {{-- Formulaire de création --}}
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ $editId ? 'Modifier' : 'Nouveau' }} transfert</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Stock source</label>
                    <select wire:model="sourceStoreId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        <option value="">Sélectionner...</option>
                        @foreach($stores as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('sourceStoreId') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Stock destination</label>
                    <select wire:model="destinationStoreId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        <option value="">Sélectionner...</option>
                        @foreach($stores as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('destinationStoreId') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Titre (optionnel)</label>
                    <input wire:model="transferTitle" type="text" placeholder="Ex: Réapprovisionnement magasin A" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                </div>
            </div>

            {{-- Produits --}}
            <div class="mt-6">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Produits à transférer</label>
                <div class="relative mb-3">
                    <input wire:model.live.debounce.300ms="productSearch" wire:keyup="searchProduct" type="text" placeholder="Rechercher un produit par nom ou référence..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                    @if(!empty($productResults))
                        <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @foreach($productResults as $p)
                                <button wire:click="addItem({{ $p['id'] }})" type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <span class="font-medium">{{ $p['name'] }}</span>
                                    <span class="text-gray-400 text-xs ml-2">{{ $p['reference'] }}</span>
                                    <span class="text-gray-400 text-xs ml-2">Stock: {{ number_format($p['stock_quantity'] ?? 0, 0, ',', ' ') }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(!empty($transferItems))
                    <table class="w-full text-sm mb-3">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Quantité</th>
                                <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($transferItems as $index => $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $item['product_name'] }}</td>
                                    <td class="px-3 py-2">
                                        <input wire:model="transferItems.{{ $index }}.quantity" type="number" step="0.01" min="0.01" class="w-24 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs ml-auto block">
                                        @error("transferItems.{$index}.quantity") <span class="text-xs text-red-500 block text-right">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-xs text-gray-400 italic mb-3">Aucun produit ajouté. Utilisez la recherche ci-dessus pour ajouter des produits.</p>
                @endif
                @error('transferItems') <span class="text-xs text-red-500 block mb-2">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model="transferNotes" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm"></textarea>
            </div>

            <div class="mt-4 flex gap-2">
                <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">{{ $editId ? 'Mettre à jour' : 'Créer le transfert' }}</button>
                <button wire:click="resetForm" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Annuler</button>
            </div>
        </div>
    @endif

    {{-- Expédition --}}
    @if($showShip)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Expédition du transfert</h3>
                <button wire:click="$set('showShip', false)" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Fermer</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Demandé</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Expédié</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($shipItems as $itemId => $data)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $data['product_name'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs font-medium">{{ number_format($data['quantity_requested'], 0, ',', ' ') }}</td>
                                <td class="px-3 py-2">
                                    <input wire:model="shipItems.{{ $itemId }}.quantity_shipped" type="number" step="0.01" min="0" class="w-24 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs ml-auto block">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex gap-2">
                <button wire:click="saveShip" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Confirmer l'expédition</button>
                <button wire:click="$set('showShip', false)" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">Annuler</button>
            </div>
        </div>
    @endif

    {{-- Réception --}}
    @if($showReceive)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Réception du transfert</h3>
                <button wire:click="$set('showReceive', false)" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Fermer</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Expédié</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Reçu</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Perte</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($receiveItems as $itemId => $data)
                            @php $loss = max(0, (float)($data['quantity_shipped'] ?? 0) - (float)($data['quantity_received'] ?? 0)); @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $loss > 0 ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                                <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $data['product_name'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs font-medium">{{ number_format($data['quantity_shipped'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-3 py-2">
                                    <input wire:model="receiveItems.{{ $itemId }}.quantity_received" type="number" step="0.01" min="0" class="w-24 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs ml-auto block">
                                </td>
                                <td class="px-3 py-2 text-right text-xs font-medium {{ $loss > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                    {{ $loss > 0 ? number_format($loss, 0, ',', ' ') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex gap-2">
                <button wire:click="saveReceive" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Confirmer la réception</button>
                <button wire:click="$set('showReceive', false)" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">Annuler</button>
            </div>
        </div>
    @endif

    {{-- Détail --}}
    @if($showDetail && $detail = \App\Models\Transfer::with(['items.product', 'sourceStore', 'destinationStore', 'requestedBy', 'approvedBy', 'preparedBy', 'shippedBy', 'receivedBy'])->find($detailId))
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] flex flex-col">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $detail->reference }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $detail->title ?? 'Sans titre' }}</p>
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
                            <span class="text-gray-500 text-xs">Source</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->sourceStore?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Destination</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->destinationStore?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Créé le</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        @if($detail->requestedBy)
                            <div><span class="text-gray-500 text-xs">Demandé par</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->requestedBy->name }}</p></div>
                        @endif
                        @if($detail->approvedBy)
                            <div><span class="text-gray-500 text-xs">Approuvé par</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->approvedBy->name }}</p></div>
                        @endif
                        @if($detail->shippedBy)
                            <div><span class="text-gray-500 text-xs">Expédié par</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->shippedBy->name }}</p></div>
                        @endif
                        @if($detail->receivedBy)
                            <div><span class="text-gray-500 text-xs">Réceptionné par</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->receivedBy->name }}</p></div>
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
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Demandé</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Expédié</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Reçu</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Perte</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($detail->items as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $item->product?->name ?? '#'.$item->product_id }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ number_format($item->quantity_requested, 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ $item->quantity_shipped !== null ? number_format($item->quantity_shipped, 0, ',', ' ') : '—' }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ $item->quantity_received !== null ? number_format($item->quantity_received, 0, ',', ' ') : '—' }}</td>
                                        <td class="px-3 py-2 text-right text-xs {{ ($item->quantity_lost ?? 0) > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ $item->quantity_lost ? number_format($item->quantity_lost, 0, ',', ' ') : '—' }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ match($item->status) { 'pending' => 'bg-gray-100 dark:bg-gray-700 text-gray-600', 'shipped' => 'bg-purple-100 dark:bg-purple-900/50 text-purple-700', 'received' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700', 'partially_received' => 'bg-orange-100 dark:bg-orange-900/50 text-orange-700', default => 'bg-gray-100 dark:bg-gray-700 text-gray-600' } }}">
                                                {{ $this->itemStatusLabel($item->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Liste des transferts --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Titre</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Source</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Destination</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Statut</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Créé le</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($transfers as $t)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-xs font-mono font-medium text-gray-900 dark:text-white">{{ $t->reference }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ $t->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $t->sourceStore?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $t->destinationStore?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $this->statusBadge($t->status) }}">{{ $this->statusLabel($t->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $t->created_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <button wire:click="viewDetail({{ $t->id }})" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-medium rounded hover:bg-gray-200 dark:hover:bg-gray-600">Détail</button>

                                    @if($t->status === 'draft')
                                        <button wire:click="edit({{ $t->id }})" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[10px] font-medium rounded hover:bg-blue-200 dark:hover:bg-blue-800/50">Modifier</button>
                                        <button wire:click="submit({{ $t->id }})" class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-[10px] font-medium rounded hover:bg-indigo-200 dark:hover:bg-indigo-800/50">Soumettre</button>
                                        <button wire:click="cancel({{ $t->id }})" onclick="return confirm('Annuler ce transfert ?')" class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[10px] font-medium rounded hover:bg-red-200 dark:hover:bg-red-800/50">Annuler</button>
                                    @endif

                                    @if($t->status === 'requested')
                                        <button wire:click="approve({{ $t->id }})" class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[10px] font-medium rounded hover:bg-emerald-200 dark:hover:bg-emerald-800/50">Approuver</button>
                                        <button wire:click="reject({{ $t->id }})" class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[10px] font-medium rounded hover:bg-red-200 dark:hover:bg-red-800/50">Refuser</button>
                                        <button wire:click="cancel({{ $t->id }})" onclick="return confirm('Annuler ce transfert ?')" class="px-2 py-1 bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 text-[10px] font-medium rounded hover:bg-rose-200 dark:hover:bg-rose-800/50">Annuler</button>
                                    @endif

                                    @if($t->status === 'approved')
                                        <button wire:click="prepare({{ $t->id }})" class="px-2 py-1 bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 text-[10px] font-medium rounded hover:bg-amber-200 dark:hover:bg-amber-800/50">Préparer</button>
                                    @endif

                                    @if($t->status === 'prepared')
                                        <button wire:click="openShip({{ $t->id }})" class="px-2 py-1 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-[10px] font-medium rounded hover:bg-purple-200 dark:hover:bg-purple-800/50">Expédier</button>
                                    @endif

                                    @if($t->status === 'shipped')
                                        <button wire:click="openReceive({{ $t->id }})" class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[10px] font-medium rounded hover:bg-emerald-200 dark:hover:bg-emerald-800/50">Réceptionner</button>
                                    @endif

                                    @if($t->status === 'partially_received')
                                        <button wire:click="openReceive({{ $t->id }})" class="px-2 py-1 bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300 text-[10px] font-medium rounded hover:bg-orange-200 dark:hover:bg-orange-800/50">Récep. restante</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">Aucun transfert trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $transfers->links() }}
    </div>
</div>
