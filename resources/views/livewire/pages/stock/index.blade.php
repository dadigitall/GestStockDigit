<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <!-- KPI -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Valeur totale</div>
            <div class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ number_format($valuation['total_value'], 0, ',', ' ') }} F</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Marge potentielle</div>
            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($valuation['total_margin'], 0, ',', ' ') }} F</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Produits en stock</div>
            <div class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $valuation['product_count'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Pertes valorisées</div>
            <div class="text-lg font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($valuation['losses_value'] ?? 0, 0, ',', ' ') }} F</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Alertes</div>
            <div class="text-lg font-bold {{ $this->alertCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }} mt-1">{{ $this->alertCount }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Méthode</div>
            <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-1 uppercase">{{ strtoupper($valuationMethod) }}</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <button wire:click="$set('tab', 'stock')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap {{ $tab === 'stock' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Stock en temps réel
        </button>
        <button wire:click="$set('tab', 'movements')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap {{ $tab === 'movements' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Mouvements
        </button>
        <button wire:click="$set('tab', 'valuation')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap {{ $tab === 'valuation' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Valorisation
        </button>
        <button wire:click="$set('tab', 'alerts')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap {{ $tab === 'alerts' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Alertes @if($this->alertCount > 0)<span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold">{{ $this->alertCount }}</span>@endif
        </button>
    </div>

    <!-- Common Filters -->
    <div class="flex flex-wrap gap-3 mb-6">
        <div class="relative flex-1 min-w-[200px] max-w-md">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
        </div>
        @if($tab === 'stock')
            <select wire:model.live="filterStore" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Tous magasins/entrepôts</option>
                @foreach($stores as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterCategory" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Toutes catégories</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Tous statuts</option>
                <option value="available">En stock</option>
                <option value="low">Stock bas</option>
                <option value="out">Rupture</option>
                <option value="overstock">Surstock</option>
                <option value="expired">Périmé</option>
                <option value="expiring_soon">Expiration proche</option>
            </select>
        @elseif($tab === 'movements')
            <select wire:model.live="filterType" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Tous types</option>
                <option value="purchase_entry">Entrée achat</option>
                <option value="sale">Sortie vente</option>
                <option value="transfer_out">Transfert sortant</option>
                <option value="transfer_in">Transfert entrant</option>
                <option value="customer_return">Retour client</option>
                <option value="supplier_return">Retour fournisseur</option>
                <option value="adjustment_positive">Ajustement +</option>
                <option value="adjustment_negative">Ajustement -</option>
                <option value="inventory">Inventaire</option>
                <option value="breakage">Casse</option>
                <option value="loss">Perte</option>
                <option value="expiry">Expiration</option>
                <option value="donation">Don</option>
                <option value="sample">Échantillon</option>
                <option value="internal_consumption">Conso. interne</option>
            </select>
            <input wire:model.live="filterDateFrom" type="date" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
            <input wire:model.live="filterDateTo" type="date" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
            <button wire:click="openMovementForm('adjustment_positive')" class="px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 whitespace-nowrap">+ Nouveau mouvement</button>
        @endif
    </div>

    <!-- Tab: Stock -->
    @if($tab === 'stock')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Produit</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Catégorie</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Disponible</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Réservé</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Endommagé</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Bloqué</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-500 dark:text-gray-400">En transit</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Total</th>
                            <th class="px-3 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Valeur</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Statut</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Magasins</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($products as $product)
                            @php
                                $available = ($product->stock_quantity ?? 0) - ($product->reserved_stock ?? 0) - ($product->damaged_stock ?? 0) - ($product->blocked_stock ?? 0);
                                $total = $product->stock_quantity ?? 0;
                                $value = $total * ($product->purchase_price ?? 0);
                                $status = $available > 0 ? ($product->min_stock > 0 && $total <= $product->min_stock ? 'low' : 'ok') : 'out';
                                if ($product->max_stock > 0 && $total >= $product->max_stock) $status = 'overstock';
                                $activeLots = $product->lots->filter(fn($l) => ($l->remaining_quantity ?? 0) > 0);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $product->name }}</div>
                                    @if($product->supplier)<div class="text-[10px] text-gray-400">{{ $product->supplier->name }}</div>@endif
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $product->reference ?? $product->sku ?? '-' }}</td>
                                <td class="px-3 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $product->category?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-center text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ number_format($available, $product->unit_sale === 'unit' ? 0 : 2) }}</td>
                                <td class="px-3 py-3 text-center text-xs {{ ($product->reserved_stock ?? 0) > 0 ? 'text-amber-600 font-medium' : 'text-gray-400' }}">{{ $product->reserved_stock ? number_format($product->reserved_stock, 0) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-xs {{ ($product->damaged_stock ?? 0) > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ $product->damaged_stock ? number_format($product->damaged_stock, 0) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-xs {{ ($product->blocked_stock ?? 0) > 0 ? 'text-purple-600 font-medium' : 'text-gray-400' }}">{{ $product->blocked_stock ? number_format($product->blocked_stock, 0) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-xs {{ ($product->transit_stock ?? 0) > 0 ? 'text-blue-600 font-medium' : 'text-gray-400' }}">{{ $product->transit_stock ? number_format($product->transit_stock, 0) : '-' }}</td>
                                <td class="px-3 py-3 text-center text-xs font-medium text-gray-900 dark:text-white">{{ number_format($total, $product->unit_sale === 'unit' ? 0 : 2) }}</td>
                                <td class="px-3 py-3 text-right text-xs text-gray-900 dark:text-white">{{ number_format($value, 0, ',', ' ') }} F</td>
                                <td class="px-3 py-3 text-center">
                                    @php $s = $status; @endphp
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $s === 'out' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : ($s === 'low' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : ($s === 'overstock' ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300' : 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300')) }}">
                                        {{ $s === 'out' ? 'Rupture' : ($s === 'low' ? 'Stock bas' : ($s === 'overstock' ? 'Surstock' : 'OK')) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($product->stores as $s)
                                            <span class="inline-block text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400" title="{{ $s->name }}">
                                                {{ \Illuminate\Support\Str::limit($s->name, 6) }}
                                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ number_format($s->pivot->stock_quantity ?? 0) }}</span>
                                            </span>
                                        @empty
                                            <span class="text-[10px] text-gray-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucun produit trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $products->links() }}</div>
        </div>

    <!-- Tab: Mouvements -->
    @elseif($tab === 'movements')
        @if($showMovementForm)
            @include('livewire.pages.stock._movement_form')
        @endif
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Produit</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-3 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Qté</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Magasin</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Document</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Par</th>
                            <th class="px-3 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Avant</th>
                            <th class="px-3 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Après</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($movements as $mvt)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-3 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-3 text-xs text-gray-900 dark:text-white">{{ $mvt->product?->name ?? '#' . $mvt->product_id }}</td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ in_array($mvt->type, ['purchase_entry', 'transfer_in', 'customer_return', 'adjustment_positive']) ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300' : 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' }}">
                                        {{ $this->movementTypeLabel($mvt->type) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right text-xs font-medium {{ $mvt->quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $mvt->quantity > 0 ? '+' : '' }}{{ number_format($mvt->quantity, 0, ',', ' ') }}
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-600 dark:text-gray-400">
                                    @if($mvt->store){{ $mvt->store->name }}@elseif($mvt->sourceStore){{ $mvt->sourceStore->name }}→{{ $mvt->destinationStore?->name ?? '?' }}@else — @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-500">
                                    @if($mvt->reference_type && $mvt->reference_id)
                                        <span class="text-indigo-500">{{ class_basename($mvt->reference_type) }} #{{ $mvt->reference_id }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $mvt->user?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-right text-xs text-gray-500">{{ number_format($mvt->stock_before) }}</td>
                                <td class="px-3 py-3 text-right text-xs font-medium text-gray-900 dark:text-white">{{ number_format($mvt->stock_after) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucun mouvement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $movements->links() }}</div>
        </div>

    <!-- Tab: Valorisation -->
    @elseif($tab === 'valuation')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Méthodes de valorisation</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">CMP (Coût Moyen Pondéré)</div>
                            <div class="text-xs text-gray-500">Prix d'achat moyen × quantité</div>
                        </div>
                        <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($valuation['methods']['cmp'], 0, ',', ' ') }} F</div>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">FIFO (First In, First Out)</div>
                            <div class="text-xs text-gray-500">Basé sur le prix des lots les plus anciens</div>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($valuation['methods']['fifo'], 0, ',', ' ') }} F</div>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">FEFO (First Expired, First Out)</div>
                            <div class="text-xs text-gray-500">Basé sur les lots proches de l'expiration</div>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($valuation['methods']['fefo'], 0, ',', ' ') }} F</div>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Dernier prix d'achat</div>
                            <div class="text-xs text-gray-500">Basé sur le prix d'achat le plus récent</div>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($valuation['methods']['last_price'], 0, ',', ' ') }} F</div>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Coût standard</div>
                            <div class="text-xs text-gray-500">Basé sur le coût standard défini</div>
                        </div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($valuation['methods']['standard'], 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Valeur par magasin</h3>
                <div class="space-y-2">
                    @forelse($valuation['by_store'] as $sv)
                        <div class="flex items-center justify-between text-sm py-1.5">
                            <span class="text-gray-600 dark:text-gray-400">{{ $sv['name'] }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($sv['value'], 0, ',', ' ') }} F</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Aucune donnée.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Valeur par catégorie</h3>
                <div class="space-y-2">
                    @forelse($valuation['by_category'] as $catName => $catValue)
                        <div class="flex items-center justify-between text-sm py-1.5">
                            <span class="text-gray-600 dark:text-gray-400">{{ $catName }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($catValue, 0, ',', ' ') }} F</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Aucune donnée.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Valeur par fournisseur</h3>
                <div class="space-y-2">
                    @forelse($valuation['by_supplier'] as $sv)
                        @if($sv['value'] > 0)
                            <div class="flex items-center justify-between text-sm py-1.5">
                                <span class="text-gray-600 dark:text-gray-400">{{ $sv['name'] }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($sv['value'], 0, ',', ' ') }} F</span>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-400">Aucune donnée.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-medium text-gray-900 dark:text-white mb-4">Synthèse</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Valeur totale du stock (CMP)</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($valuation['total_value'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Marge potentielle</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($valuation['total_margin'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">Pertes valorisées</span>
                        <span class="font-bold text-red-600 dark:text-red-400">{{ number_format($valuation['losses_value'] ?? 0, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">Nombre de produits</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $valuation['product_count'] }}</span>
                    </div>
                </div>
            </div>
        </div>

    <!-- Tab: Alertes -->
    @elseif($tab === 'alerts')
        <div class="space-y-4">
            @forelse($alerts as $alert)
                <div class="rounded-xl border p-4 {{ $alert['severity'] === 'danger' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : ($alert['severity'] === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800' : 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800') }}">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5">
                            @if($alert['severity'] === 'danger')
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            @elseif($alert['severity'] === 'warning')
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium {{ $alert['severity'] === 'danger' ? 'text-red-800 dark:text-red-300' : ($alert['severity'] === 'warning' ? 'text-amber-800 dark:text-amber-300' : 'text-blue-800 dark:text-blue-300') }}">
                                {{ $alert['message'] }}
                            </div>
                            <div class="text-xs {{ $alert['severity'] === 'danger' ? 'text-red-600 dark:text-red-400' : ($alert['severity'] === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400') }} mt-1">
                                {{ $alert['count'] }} élément(s) concerné(s) · {{ ['danger' => 'Critique', 'warning' => 'Attention', 'info' => 'Information'][$alert['severity']] }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-lg font-medium">Aucune alerte</p>
                    <p class="text-sm">Tous les stocks sont en ordre.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
