<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

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
                @foreach(['draft', 'in_progress', 'frozen', 'completed', 'validated', 'cancelled'] as $s)
                    <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterType" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Tous types</option>
                @foreach(['global' => 'Global', 'partial' => 'Partiel', 'by_store' => 'Par magasin', 'by_category' => 'Par catégorie', 'by_location' => 'Par emplacement', 'tournant' => 'Tournant', 'by_lot' => 'Par lot'] as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            + Nouvel inventaire
        </button>
    </div>

    {{-- Etape 1-2 : Formulaire de création --}}
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Nouvel inventaire</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Titre</label>
                    <input wire:model="title" type="text" placeholder="Ex: Inventaire mensuel juin 2026" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                    @error('title') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        @foreach(['global' => 'Global', 'partial' => 'Partiel (stock > 0)', 'by_store' => 'Par magasin', 'by_category' => 'Par catégorie', 'by_location' => 'Par emplacement', 'tournant' => 'Tournant', 'by_lot' => 'Par lot'] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if(in_array($type, ['by_store', 'by_location']))
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Magasin</label>
                    <select wire:model="storeId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        <option value="">Tous</option>
                        @foreach($stores as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($type === 'by_category')
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
                    <select wire:model="categoryId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        <option value="">Toutes</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="md:col-span-3">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input wire:model="freezeStock" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                        Geler le stock pendant l'inventaire (empêche les modifications)
                    </label>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea wire:model="notes" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm"></textarea>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Créer l'inventaire</button>
                <button wire:click="resetForm" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Annuler</button>
            </div>
        </div>
    @endif

    {{-- Etape 4-5 : Comptage physique --}}
    @if($showCounting)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Saisie du comptage physique</h3>
                <button wire:click="$set('showCounting', false)" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Fermer</button>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Magasin</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Lot</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Théorique</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Physique</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Écart</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($countedItems as $itemId => $data)
                            @php $diff = ($data['physical'] !== null && $data['physical'] !== '') ? (float)$data['physical'] - (float)$data['theoretical'] : null; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $data['physical'] !== null && $data['physical'] !== '' ? ($diff != 0 ? 'bg-amber-50/50 dark:bg-amber-900/10' : 'bg-emerald-50/50 dark:bg-emerald-900/10') : '' }}">
                                <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $data['product_name'] }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-400 text-xs">{{ $data['store_name'] }}</td>
                                <td class="px-3 py-2 text-gray-600 dark:text-gray-400 text-xs">{{ $data['lot_number'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs font-medium">{{ number_format($data['theoretical'], 0, ',', ' ') }}</td>
                                <td class="px-3 py-2">
                                    <input wire:model="countedItems.{{ $itemId }}.physical" type="number" step="0.01" placeholder="{{ number_format($data['theoretical'], 0, ',', ' ') }}" class="w-24 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs {{ $data['physical'] !== null && $data['physical'] !== '' && $diff != 0 ? 'border-amber-400 dark:border-amber-600' : '' }}">
                                </td>
                                <td class="px-3 py-2 text-right text-xs font-medium">
                                    @if($diff !== null)
                                        <span class="{{ $diff > 0 ? 'text-emerald-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', ' ') }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex gap-2">
                <button wire:click="saveCounting" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer le comptage</button>
                <button wire:click="$set('showCounting', false)" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg">Fermer</button>
            </div>
        </div>
    @endif

    {{-- Etape 6-7 : Analyse des écarts avec approbation --}}
    @if($showComparison)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Analyse des écarts</h3>
                <button wire:click="closeComparison" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Fermer</button>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Stock</th>
                            <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Théo.</th>
                            <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Phys.</th>
                            <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Écart</th>
                            <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Valeur</th>
                            <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Justification</th>
                            <th class="px-2 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Décision</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($comparisonItems as $itemId => $data)
                            @php $hasDiff = $data['discrepancy'] != 0; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $hasDiff ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }}">
                                <td class="px-2 py-2 text-gray-900 dark:text-white text-xs">{{ $data['product_name'] }}</td>
                                <td class="px-2 py-2 text-gray-500 text-xs">{{ $data['store_name'] }}</td>
                                <td class="px-2 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ number_format($data['theoretical'], 0, ',', ' ') }}</td>
                                <td class="px-2 py-2 text-right text-xs font-medium {{ $data['physical'] !== null ? 'text-gray-900 dark:text-white' : 'text-gray-400 italic' }}">{{ $data['physical'] !== null ? number_format($data['physical'], 0, ',', ' ') : '—' }}</td>
                                <td class="px-2 py-2 text-right text-xs font-medium {{ $data['discrepancy'] > 0 ? 'text-emerald-600' : ($data['discrepancy'] < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                    {{ $data['discrepancy'] > 0 ? '+' : '' }}{{ number_format($data['discrepancy'], 0, ',', ' ') }}
                                </td>
                                <td class="px-2 py-2 text-right text-xs {{ $data['discrepancy_value'] != 0 ? 'font-medium text-red-600' : 'text-gray-400' }}">{{ number_format($data['discrepancy_value'], 0, ',', ' ') }} F</td>
                                <td class="px-2 py-2">
                                    @if($hasDiff)
                                        <input wire:model="comparisonItems.{{ $itemId }}.justification" type="text" placeholder="Justification..." class="w-32 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-[10px]">
                                    @else
                                        <span class="text-gray-300 text-[10px]">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if($hasDiff)
                                        @if(!$data['decision'])
                                            <div class="flex gap-1 justify-center">
                                                <button wire:click="approveItem({{ $itemId }})" class="px-2 py-0.5 bg-emerald-600 text-white text-[10px] font-medium rounded hover:bg-emerald-700">✓</button>
                                                <button wire:click="rejectItem({{ $itemId }})" class="px-2 py-0.5 bg-red-600 text-white text-[10px] font-medium rounded hover:bg-red-700">✗</button>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $data['decision'] === 'approved' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' }}">
                                                {{ $data['decision'] === 'approved' ? 'Approuvé' : 'Rejeté' }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-300 text-[10px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button wire:click="closeComparison" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Terminer l'analyse</button>
            </div>
        </div>
    @endif

    {{-- Etape 10 : Détail / Rapport --}}
    @if($detailId && $detail = \App\Models\Inventory::with(['items.product', 'items.store', 'items.lot', 'items.counter', 'items.decider', 'creator', 'validator', 'store', 'category'])->find($detailId))
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] flex flex-col">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $detail->reference }} — {{ $detail->title }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Rapport d'inventaire</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('inventories.print', $detail) }}" target="_blank" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700">Imprimer</a>
                        <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl leading-none">&times;</button>
                    </div>
                </div>
                <div class="overflow-y-auto p-6 space-y-4">
                    {{-- En-tête --}}
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                        <div><span class="text-gray-500 text-xs">Type</span><p class="font-medium text-gray-900 dark:text-white">{{ $this->typeLabel($detail->type) }}</p></div>
                        <div><span class="text-gray-500 text-xs">Statut</span><p><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $this->statusBadge($detail->status) }}">{{ str_replace('_', ' ', $detail->status) }}</span></p></div>
                        <div><span class="text-gray-500 text-xs">Créé par</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->creator?->name ?? '-' }}</p></div>
                        <div><span class="text-gray-500 text-xs">Date création</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->created_at->format('d/m/Y H:i') }}</p></div>
                        <div><span class="text-gray-500 text-xs">Gel stock</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->freeze_stock ? 'Oui' : 'Non' }}</p></div>
                        @if($detail->store)<div><span class="text-gray-500 text-xs">Magasin</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->store->name }}</p></div>@endif
                        @if($detail->category)<div><span class="text-gray-500 text-xs">Catégorie</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->category->name }}</p></div>@endif
                        @if($detail->frozen_at)<div><span class="text-gray-500 text-xs">Gelé le</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->frozen_at->format('d/m/Y H:i') }}</p></div>@endif
                        @if($detail->completed_at)<div><span class="text-gray-500 text-xs">Terminé le</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->completed_at->format('d/m/Y H:i') }}</p></div>@endif
                        @if($detail->validated_by)<div><span class="text-gray-500 text-xs">Validé par</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->validator?->name ?? '-' }}</p></div>@endif
                        @if($detail->validated_at)<div><span class="text-gray-500 text-xs">Validé le</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->validated_at->format('d/m/Y H:i') }}</p></div>@endif
                    </div>

                    @if($detail->notes)
                    <div class="text-sm bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg">
                        <span class="text-xs text-gray-500 font-medium">Notes :</span>
                        <p class="mt-0.5 text-gray-900 dark:text-white text-xs">{{ $detail->notes }}</p>
                    </div>
                    @endif

                    {{-- Synthèse --}}
                    <div class="grid grid-cols-4 gap-3 text-center text-sm">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $detail->items->count() }}</div>
                            <div class="text-[10px] text-gray-500">Articles</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <div class="text-2xl font-bold {{ $detail->total_discrepancies > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $detail->total_discrepancies }}</div>
                            <div class="text-[10px] text-gray-500">Écarts</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <div class="text-2xl font-bold {{ $detail->total_discrepancy_value != 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ number_format($detail->total_discrepancy_value, 0, ',', ' ') }} F</div>
                            <div class="text-[10px] text-gray-500">Valeur écart</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $detail->approvedItems->count() }}</div>
                            <div class="text-[10px] text-gray-500">Approuvés</div>
                        </div>
                    </div>

                    {{-- Tableau détaillé --}}
                    @if($detail->items->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Détail des articles</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-[10px]">Produit</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-[10px]">Stock</th>
                                        <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-[10px]">Théorique</th>
                                        <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-[10px]">Physique</th>
                                        <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-[10px]">Écart qté</th>
                                        <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-[10px]">Écart valeur</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-[10px]">Responsable</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-[10px]">Justification</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-[10px]">Décision</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($detail->items as $item)
                                        @php $hasDiff = $item->discrepancy_quantity != 0; @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $hasDiff ? 'bg-amber-50/30 dark:bg-amber-900/5' : '' }}">
                                            <td class="px-2 py-2 text-gray-900 dark:text-white text-xs">{{ $item->product?->name ?? '#' . $item->product_id }}</td>
                                            <td class="px-2 py-2 text-gray-500 text-[10px]">{{ $item->store?->name ?? '-' }}</td>
                                            <td class="px-2 py-2 text-right text-xs text-gray-600 dark:text-gray-400">{{ number_format($item->theoretical_quantity, 0, ',', ' ') }}</td>
                                            <td class="px-2 py-2 text-right text-xs {{ $item->physical_quantity !== null ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400 italic' }}">{{ $item->physical_quantity !== null ? number_format($item->physical_quantity, 0, ',', ' ') : '—' }}</td>
                                            <td class="px-2 py-2 text-right text-xs font-medium {{ $item->discrepancy_quantity > 0 ? 'text-emerald-600' : ($item->discrepancy_quantity < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                                {{ $item->discrepancy_quantity > 0 ? '+' : '' }}{{ number_format($item->discrepancy_quantity, 0, ',', ' ') }}
                                            </td>
                                            <td class="px-2 py-2 text-right text-xs {{ $item->discrepancy_value != 0 ? 'font-medium text-red-600' : 'text-gray-400' }}">{{ number_format($item->discrepancy_value, 0, ',', ' ') }} F</td>
                                            <td class="px-2 py-2 text-[10px] text-gray-500">{{ $item->counter?->name ?? '-' }}</td>
                                            <td class="px-2 py-2 text-[10px] text-gray-600 dark:text-gray-400 max-w-[120px] truncate">{{ $item->justification ?? ($hasDiff ? '—' : '') }}</td>
                                            <td class="px-2 py-2">
                                                @if($item->decision)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $item->decision === 'approved' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700' : 'bg-red-100 dark:bg-red-900/50 text-red-700' }}">
                                                        {{ $item->decision === 'approved' ? '✓ Approuvé' : '✗ Rejeté' }}
                                                    </span>
                                                @elseif($hasDiff)
                                                    <span class="text-[10px] text-amber-600 italic">En attente</span>
                                                @else
                                                    <span class="text-[10px] text-gray-300">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 px-6 py-3 flex justify-between items-center shrink-0">
                    <div class="text-xs text-gray-500">
                        @if($detail->total_discrepancies > 0)
                            <span class="text-amber-600 font-medium">{{ $detail->total_discrepancies }} écart(s)</span> pour un total de <span class="text-red-600 font-medium">{{ number_format($detail->total_discrepancy_value, 0, ',', ' ') }} F</span>
                        @else
                            Aucun écart
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @if($detail->status === 'in_progress' || $detail->status === 'draft')
                            <button wire:click="startCounting({{ $detail->id }})" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">Comptage</button>
                        @endif
                        @if($detail->status === 'in_progress')
                            <button wire:click="openComparison({{ $detail->id }})" class="px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-lg hover:bg-amber-700">Analyser les écarts</button>
                            <button wire:click="completeInventory({{ $detail->id }})" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700">Marquer terminé</button>
                        @endif
                        @if($detail->status === 'completed')
                            <button wire:click="validateInventory({{ $detail->id }})" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700">Valider l'inventaire</button>
                        @endif
                        @if(in_array($detail->status, ['draft', 'in_progress']))
                            <button wire:click="cancelInventory({{ $detail->id }})" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700">Annuler</button>
                        @endif
                        <button wire:click="closeDetail" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg hover:bg-gray-300">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Liste des inventaires --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Titre</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Type</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Statut</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Articles</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Écarts</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Valeur écart</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Créé le</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Par</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($inventories as $inv)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white text-xs">{{ $inv->reference }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">{{ $inv->title }}</td>
                            <td class="px-4 py-3 text-gray-500 text-[10px]">{{ $this->typeLabel($inv->type) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $this->statusBadge($inv->status) }}">{{ str_replace('_', ' ', $inv->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-gray-900 dark:text-white">{{ $inv->total_items }}</td>
                            <td class="px-4 py-3 text-right text-xs {{ $inv->total_discrepancies > 0 ? 'text-amber-600 dark:text-amber-400 font-medium' : 'text-gray-500' }}">{{ $inv->total_discrepancies }}</td>
                            <td class="px-4 py-3 text-right text-xs {{ $inv->total_discrepancy_value != 0 ? 'text-red-600 font-medium' : 'text-gray-500' }}">{{ number_format($inv->total_discrepancy_value, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-gray-500 text-[10px]">{{ $inv->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-500 text-[10px]">{{ $inv->creator?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="viewDetail({{ $inv->id }})" class="p-1 text-gray-400 hover:text-indigo-600 transition-colors" title="Détails">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    @if($inv->status === 'draft')
                                        <button wire:click="startCounting({{ $inv->id }})" class="p-1 text-gray-400 hover:text-emerald-600 transition-colors" title="Démarrer comptage">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    @if($inv->status === 'completed')
                                        <button wire:click="validateInventory({{ $inv->id }})" class="p-1 text-gray-400 hover:text-emerald-600 transition-colors" title="Valider">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    @if(in_array($inv->status, ['draft', 'in_progress']))
                                        <button wire:click="cancelInventory({{ $inv->id }})" class="p-1 text-gray-400 hover:text-red-600 transition-colors" title="Annuler">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucun inventaire trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $inventories->links() }}</div>
    </div>
</div>
