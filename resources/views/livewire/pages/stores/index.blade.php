<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h4"/><path d="M8 6v12"/><path d="M14 14h4"/><path d="M16 14v4"/><path d="M3 3v18h18"/></svg>
            <span>Hiérarchie organisationnelle</span>
            <span class="text-slate-300">·</span>
            <span>{{ $stores->total() }} entité(s)</span>
        </div>
        <div class="flex items-center gap-3">
            <!-- View Toggle -->
            <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-lg p-0.5">
                <button wire:click="toggleView('list')"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $viewMode === 'list' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    <svg class="w-3.5 h-3.5 inline mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Liste
                </button>
                <button wire:click="toggleView('tree')"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $viewMode === 'tree' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                    <svg class="w-3.5 h-3.5 inline mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h4"/><path d="M8 6v12"/><path d="M14 14h4"/><path d="M16 14v4"/><path d="M3 3v18h18"/></svg>
                    Arbre
                </button>
            </div>
            <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                Nouvelle entité
            </button>
        </div>
    </div>

    @if($showForm)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ $editingStore ? 'Modifier' : 'Nouvelle' }} entité</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom *</label>
                    <input wire:model="name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500">
                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Code interne *</label>
                    <input wire:model="code" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm uppercase focus:ring-2 focus:ring-indigo-500">
                    @error('code') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type</label>
                    <select wire:model="type" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="filiale">Filiale</option>
                        <option value="agence">Agence</option>
                        <option value="boutique">Boutique</option>
                        <option value="point_vente">Point de vente</option>
                        <option value="magasin">Magasin</option>
                        <option value="depot">Dépôt</option>
                        <option value="entrepot">Entrepôt</option>
                        <option value="rayon">Rayon</option>
                        <option value="zone_stockage">Zone de stockage</option>
                        <option value="emplacement">Emplacement</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Rattaché à</label>
                    <select wire:model="parent_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="">— Aucun (racine) —</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Responsable</label>
                    <select wire:model="manager_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="">— Non défini —</option>
                        @foreach($managers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Horaires</label>
                    <input wire:model="opening_hours" placeholder="ex: Lun-Ven 8h-18h" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Téléphone</label>
                    <input wire:model="phone" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Adresse</label>
                    <input wire:model="address" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                    <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm"></textarea>
                </div>
                <div class="lg:col-span-3 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <input wire:model="allows_stock" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Stock
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <input wire:model="allows_sales" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Ventes
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <input wire:model="allows_cash_register" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Caisse
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="cancel" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    @if($viewMode === 'tree')
        <!-- Tree View - CDC 8.5 Hiérarchie organisationnelle -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-1">
                        <div class="w-7 h-7 rounded-md bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white text-[10px] font-bold">F</div>
                        <div class="w-7 h-7 rounded-md bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-[10px] font-bold">B</div>
                        <div class="w-7 h-7 rounded-md bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-[10px] font-bold">E</div>
                        <div class="w-7 h-7 rounded-md bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white text-[10px] font-bold">R</div>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Hiérarchie organisationnelle</h2>
                        <p class="text-xs text-slate-400">
                            entreprise → filiales/agences → boutiques/magasins → dépôts/entrepôts → rayons/emplacements
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-violet-500"></span>Filiale/Agence</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Boutique/Magasin</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Dépôt/Entrepôt</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-500"></span>Rayon/Emplacement</span>
                </div>
            </div>
            <div class="p-6">
                @if($tree->count() > 0)
                    @foreach($tree as $root)
                        @include('components.store-tree-item', ['store' => $root, 'active' => null, 'depth' => 0])
                    @endforeach
                @else
                    <div class="text-center py-12 text-slate-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <p class="text-base font-medium">Aucune entité</p>
                        <p class="text-sm mt-1">Créez votre première entité racine pour construire la hiérarchie.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Legend : Hierarchy patterns possibles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4">
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                    <svg class="w-4 h-4 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    <span>Entreprise → Filiales → Agences → Boutiques</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px]">
                    <span class="px-1.5 py-0.5 rounded bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">Siège</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="px-1.5 py-0.5 rounded bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">Filiale</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="px-1.5 py-0.5 rounded bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">Agence</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Boutique</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4">
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                    <svg class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 21V10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v11"/><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 1.132-1.803l7.95-3.974a2 2 0 0 1 1.837 0l7.948 3.974A2 2 0 0 1 22 8z"/></svg>
                    <span>Entrepôt central → Dépôts régionaux → Points de vente</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px]">
                    <span class="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Central</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Dépôt</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">PdV</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4">
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                    <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <span>Magasins indépendants</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px]">
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Siège</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <div class="flex gap-0.5">
                        <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">M1</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">M2</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">M3</span>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4">
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                    <svg class="w-4 h-4 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/></svg>
                    <span>Supermarché → Rayons → Emplacements</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px]">
                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Supermarché</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="px-1.5 py-0.5 rounded bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300">Rayon</span>
                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="px-1.5 py-0.5 rounded bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300">Emplacement</span>
                </div>
            </div>
        </div>
    @else
        <!-- List View -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($stores as $store)
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-5 hover:shadow-md transition group {{ $store->is_active ? '' : 'opacity-60' }}">
                    <!-- Hiérarchie path -->
                    @if($store->parent)
                        <div class="flex items-center gap-1 text-[10px] text-slate-400 mb-2">
                            @php
                                $path = [];
                                $current = $store;
                                while ($current->parent) {
                                    $path[] = $current->parent;
                                    $current = $current->parent;
                                }
                                $path = array_reverse($path);
                            @endphp
                            @foreach($path as $p)
                                <a href="{{ route('stores.show', $p) }}" wire:navigate class="hover:text-indigo-600 transition truncate max-w-[80px]">{{ $p->name }}</a>
                                <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center gap-1 text-[10px] text-slate-400 mb-2">
                            <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>Racine</span>
                        </div>
                    @endif

                    <div class="flex items-start gap-3">
                        <a href="{{ route('stores.show', $store) }}" wire:navigate class="w-10 h-10 rounded-xl bg-gradient-to-br {{ match($store->type) {
                            'filiale', 'agence' => 'from-violet-500 to-purple-600',
                            'boutique', 'point_vente', 'magasin' => 'from-emerald-500 to-teal-600',
                            'depot', 'entrepot' => 'from-amber-500 to-orange-600',
                            'rayon', 'zone_stockage', 'emplacement' => 'from-sky-500 to-blue-600',
                            default => 'from-slate-500 to-slate-600',
                        } }} flex items-center justify-center text-white shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                @switch($store->type)
                                    @case('filiale')
                                    @case('agence')
                                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>
                                    @break
                                    @case('depot')
                                    @case('entrepot')
                                        <path d="M18 21V10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v11"/><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 1.132-1.803l7.95-3.974a2 2 0 0 1 1.837 0l7.948 3.974A2 2 0 0 1 22 8z"/><path d="M6 13h12"/><path d="M6 17h12"/>
                                    @break
                                    @case('rayon')
                                    @case('zone_stockage')
                                    @case('emplacement')
                                        <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/>
                                    @break
                                    @default
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                                @endswitch
                            </svg>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('stores.show', $store) }}" wire:navigate class="text-sm font-semibold text-slate-900 dark:text-white truncate hover:text-indigo-600 transition">{{ $store->name }}</a>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wider {{ $store->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                                    {{ $store->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ strtoupper($store->code) }}
                                @if($store->type)
                                    · {{ ucfirst(str_replace('_', ' ', $store->type)) }}
                                @endif
                            </p>
                        </div>
                        <button wire:click="edit({{ $store->id }})" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400 hover:text-indigo-600 transition shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>

                    @if($store->address || $store->phone)
                        <div class="mt-2 space-y-0.5 text-xs text-slate-500">
                            @if($store->address)<p>📍 {{ $store->address }}</p>@endif
                            @if($store->phone)<p>📞 {{ $store->phone }}</p>@endif
                        </div>
                    @endif

                    @if($store->manager)
                        <p class="text-xs text-slate-500 mt-2">Responsable : {{ $store->manager->name }}</p>
                    @endif

                    @if($store->opening_hours)
                        <p class="text-xs text-slate-500 mt-0.5">🕐 {{ $store->opening_hours }}</p>
                    @endif

                    <div class="flex gap-2 mt-3">
                        @if($store->allows_stock)
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Stock</span>
                        @endif
                        @if($store->allows_sales)
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">Ventes</span>
                        @endif
                        @if($store->allows_cash_register)
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Caisse</span>
                        @endif
                    </div>

                    <!-- Children count -->
                    @php
                        $childCount = \App\Models\Store::where('parent_id', $store->id)->count();
                    @endphp
                    @if($childCount > 0)
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-white/5">
                            <a href="{{ route('stores.show', $store) }}#children" wire:navigate class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-indigo-600 transition">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h4"/><path d="M8 6v12"/><path d="M14 14h4"/><path d="M16 14v4"/><path d="M3 3v18h18"/></svg>
                                {{ $childCount }} entité(s) rattachée(s)
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 text-center py-16 text-slate-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-300 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <p class="text-base font-medium">Aucune entité</p>
                    <p class="text-sm mt-1">Créez votre première filiale, boutique ou entrepôt.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $stores->links() }}</div>
    @endif
</div>
