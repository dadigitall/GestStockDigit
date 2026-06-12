<div>
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- General info -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Informations générales
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Image -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Image du produit</label>
                            <div class="flex items-start gap-4">
                                @if($image || $existingImage)
                                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-white/5 shrink-0 bg-slate-100 dark:bg-slate-800">
                                        @if($image)
                                            <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                        @else
                                            <img src="{{ asset('storage/'.$existingImage) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100">
                                    @error('image') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom du produit *</label>
                            <input wire:model="name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                            @error('name') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Référence interne</label>
                            <input wire:model="reference" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Code-barres</label>
                            <input wire:model="barcode" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Marque</label>
                            <input wire:model="brand" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Famille</label>
                            <input wire:model="family" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Catégorie</label>
                            <select wire:model="category_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                                <option value="">Sélectionner...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Fournisseur principal</label>
                            <select wire:model="supplier_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                                <option value="">Sélectionner...</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                            <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Prix et taxe
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prix d'achat *</label>
                            <input wire:model="purchase_price" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                            @error('purchase_price') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prix vente détail *</label>
                            <input wire:model="sale_price" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                            @error('sale_price') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prix de gros</label>
                            <input wire:model="wholesale_price" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prix revendeur</label>
                            <input wire:model="reseller_price" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prix promo</label>
                            <input wire:model="promo_price" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">TVA (%)</label>
                            <input wire:model="tax_rate" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Unité vente</label>
                            <select wire:model="unit_sale" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                                @foreach($units as $u)
                                    <option value="{{ $u->slug }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Unité achat</label>
                            <select wire:model="unit_purchase" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                                @foreach($units as $u)
                                    <option value="{{ $u->slug }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Conditionnement</label>
                            <input wire:model="packaging" placeholder="ex: 24 pièces/carton" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Caractéristiques physiques -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        Caractéristiques physiques
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Poids</label>
                            <input wire:model="weight" type="number" step="0.01" placeholder="kg" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Volume</label>
                            <input wire:model="volume" type="number" step="0.01" placeholder="m³" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dimensions</label>
                            <input wire:model="dimensions" placeholder="LxlxH cm" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Variants (8.14) -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="8" height="8" rx="1"/><rect x="14" y="2" width="8" height="8" rx="1"/><rect x="2" y="14" width="8" height="8" rx="1"/><rect x="14" y="14" width="8" height="8" rx="1"/></svg>
                        Variantes
                    </h3>
                    @if($product)
                        <!-- Quick add -->
                        <div class="flex flex-wrap gap-2 mb-3">
                            <input wire:model="variantName" placeholder="Nom (ex: Rouge, XL)" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm flex-1 min-w-[120px]">
                            <input wire:model="variantSku" placeholder="SKU" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-24">
                            <input wire:model="variantBarcode" placeholder="Code-barres" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-28">
                            <input wire:model="variantPrice" type="number" step="0.01" placeholder="Prix" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-20">
                            <input wire:model="variantWholesalePrice" type="number" step="0.01" placeholder="Gros" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-20">
                            <input wire:model="variantPurchasePrice" type="number" step="0.01" placeholder="Achat" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-20">
                            <input wire:model="variantStock" type="number" placeholder="Stock" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-16">
                            <label class="flex items-center gap-1.5 text-xs text-slate-500">
                                <input wire:model="variantIsActive" type="checkbox" class="rounded border-slate-300 text-indigo-600">
                                Actif
                            </label>
                            <button type="button" wire:click="addVariant" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">+</button>
                        </div>
                        @if($variants->count() > 0)
                            <div class="space-y-1 mb-4">
                                @foreach($variants as $v)
                                    <div class="flex items-center justify-between px-3 py-1.5 bg-slate-50 dark:bg-white/5 rounded-lg text-sm">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <span class="text-slate-700 dark:text-slate-300 truncate">{{ $v->name }}</span>
                                            @if($v->sku)
                                                <span class="text-xs text-slate-400">SKU: {{ $v->sku }}</span>
                                            @endif
                                            @if($v->barcode)
                                                <span class="text-xs text-slate-400">CB: {{ $v->barcode }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-xs text-slate-500">{{ $v->price ? number_format($v->price, 0).' XAF' : '—' }}</span>
                                            <span class="text-xs text-slate-400">{{ $v->purchase_price ? 'A:'.number_format($v->purchase_price, 0) : '' }}</span>
                                            <span class="text-xs font-medium {{ $v->stock_quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $v->stock_quantity }}</span>
                                            <span class="text-[10px] {{ $v->is_active ? 'text-emerald-500' : 'text-slate-400' }}">{{ $v->is_active ? 'Actif' : 'Inactif' }}</span>
                                            <button type="button" wire:click="deleteVariant({{ $v->id }})" class="text-slate-400 hover:text-rose-600 p-0.5">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 mb-4">Aucune variante.</p>
                        @endif

                        <!-- Auto-generation -->
                        <hr class="border-slate-200 dark:border-white/5 my-3">
                        <p class="text-xs font-medium text-slate-500 mb-2">Génération automatique de combinaisons</p>
                        @foreach($attributeGroups as $i => $group)
                            <div class="flex gap-2 mb-2">
                                <input wire:model="attributeGroups.{{ $i }}.name" placeholder="Attribut (ex: Couleur)" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-40">
                                <input wire:model="attributeGroups.{{ $i }}.values" placeholder="Valeurs séparées par virgules (ex: Rouge, Bleu)" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm flex-1">
                                <button type="button" wire:click="removeAttributeGroup({{ $i }})" class="px-2 py-1.5 text-slate-400 hover:text-rose-600 border border-slate-300 dark:border-slate-600 rounded-lg">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </div>
                        @endforeach
                        <div class="flex gap-2">
                            <button type="button" wire:click="addAttributeGroup" class="px-3 py-1.5 border border-indigo-300 dark:border-indigo-700 text-indigo-600 dark:text-indigo-400 text-sm rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">+ Ajouter un attribut</button>
                            @if(count($attributeGroups) > 0)
                                <button type="button" wire:click="generateCombinations" class="px-3 py-1.5 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700">Générer les combinaisons</button>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-slate-400">Sauvegardez d'abord le produit pour ajouter des variantes.</p>
                    @endif
                </div>

                <!-- Lots & Expiry (8.15) -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><path d="M2 7h20"/><path d="M10 12h4"/><path d="M10 17h4"/><path d="M7 12v5"/><path d="M17 12v5"/></svg>
                        Lots & dates d'expiration
                    </h3>
                    @if($product)
                        <div class="flex flex-wrap gap-2 mb-3">
                            <input wire:model="lotNumber" placeholder="N° de lot" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm flex-1 min-w-[120px]">
                            <input wire:model="lotManufacturingDate" type="date" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-36">
                            <input wire:model="lotExpiry" type="date" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-36">
                            <input wire:model="lotQuantity" type="number" step="0.01" placeholder="Qté" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-20">
                            <select wire:model="lotSupplierId" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-36">
                                <option value="">Fournisseur</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="addLot" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">+</button>
                        </div>
                        @if($lots->count() > 0)
                            <div class="space-y-1">
                                @foreach($lots as $lot)
                                    <div class="flex items-center justify-between px-3 py-1.5 bg-slate-50 dark:bg-white/5 rounded-lg text-sm">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $lot->lot_number }}</span>
                                            @if($lot->manufacturing_date)
                                                <span class="text-xs text-slate-400">Fab: {{ $lot->manufacturing_date->format('d/m/Y') }}</span>
                                            @endif
                                            @if($lot->expiry_date)
                                                <span class="text-xs {{ $lot->expiry_date->isPast() ? 'text-rose-600' : 'text-amber-600' }}">Exp: {{ $lot->expiry_date->format('d/m/Y') }}</span>
                                            @endif
                                            @if($lot->supplier)
                                                <span class="text-xs text-slate-400">{{ $lot->supplier->name }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-xs text-slate-500">{{ $lot->remaining_quantity }} / {{ $lot->initial_quantity }}</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded {{ $lot->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $lot->status }}</span>
                                            <button type="button" wire:click="deleteLot({{ $lot->id }})" class="text-slate-400 hover:text-rose-600">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400">Aucun lot enregistré.</p>
                        @endif
                    @else
                        <p class="text-xs text-slate-400">Sauvegardez d'abord le produit pour ajouter des lots.</p>
                    @endif
                </div>

                <!-- Serial Numbers (8.16) -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg>
                        Numéros de série
                    </h3>
                    @if($product)
                        <div class="mb-3 space-y-2">
                            <textarea wire:model="serialInput" rows="2" placeholder="Saisissez un N° de série par ligne&#10;ex : SN-001&#10;SN-002" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm"></textarea>
                            <div class="flex flex-wrap gap-2">
                                <input wire:model="serialEntryDate" type="date" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-36">
                                <input wire:model="serialWarrantyExpiry" type="date" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-36">
                                <select wire:model="serialCustomerId" class="px-3 py-1.5 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm w-36">
                                    <option value="">Client</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="addSerial" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">Ajouter</button>
                            </div>
                        </div>
                        @if($serials->count() > 0)
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                @foreach($serials as $s)
                                    <div class="flex items-center justify-between px-3 py-1.5 bg-slate-50 dark:bg-white/5 rounded-lg text-sm">
                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                            <span class="font-mono text-slate-700 dark:text-slate-300 truncate">{{ $s->serial_number }}</span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded {{ $s->status === 'available' ? 'bg-emerald-100 text-emerald-700' : '' }}{{ $s->status === 'sold' ? 'bg-blue-100 text-blue-700' : '' }}{{ $s->status === 'returned' ? 'bg-amber-100 text-amber-700' : '' }}">{{ $s->status }}</span>
                                            @if($s->warranty_expiry)
                                                <span class="text-[10px] {{ $s->warranty_expiry->isPast() ? 'text-rose-500' : 'text-slate-400' }}">Garantie: {{ $s->warranty_expiry->format('d/m/Y') }}</span>
                                            @endif
                                            @if($s->customer)
                                                <span class="text-[10px] text-slate-400">{{ $s->customer->name }}</span>
                                            @endif
                                        </div>
                                        <button type="button" wire:click="deleteSerial({{ $s->id }})" class="text-slate-400 hover:text-rose-600 shrink-0">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400">Aucun numéro de série.</p>
                        @endif
                    @else
                        <p class="text-xs text-slate-400">Sauvegardez d'abord le produit pour ajouter des N° de série.</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Stock -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/></svg>
                        Stock & Seuils
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Stock minimum *</label>
                            <input wire:model="min_stock" type="number" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                            @error('min_stock') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Stock maximum</label>
                            <input wire:model="max_stock" type="number" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Seuil d'alerte</label>
                            <input wire:model="alert_threshold" type="number" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        Options
                    </h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Produit actif</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="is_sellable" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Vendable</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="is_stockable" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Stockable</span>
                        </label>
                        <hr class="border-slate-200 dark:border-white/5">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="track_lot" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Suivi par lot</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="track_serial" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Suivi N° de série</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input wire:model="track_expiry" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Suivi date péremption</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center gap-4">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                {{ $product ? 'Mettre à jour' : 'Créer le produit' }}
            </button>
            <a href="{{ route('products.index') }}" wire:navigate class="px-6 py-2.5 border border-slate-300 dark:border-slate-600 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
