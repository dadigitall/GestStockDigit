<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 21V10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v11"/><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 1.132-1.803l7.95-3.974a2 2 0 0 1 1.837 0l7.948 3.974A2 2 0 0 1 22 8z"/></svg>
            <span>{{ $stores->total() }} entrepôt(s) / dépôt(s)</span>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
            Nouvel entrepôt
        </button>
    </div>

    @if($showForm)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ $editingStore ? 'Modifier' : 'Nouvel' }} entrepôt / dépôt</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom *</label>
                    <input wire:model="name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500">
                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Code *</label>
                    <input wire:model="code" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm uppercase focus:ring-2 focus:ring-indigo-500">
                    @error('code') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type</label>
                    <select wire:model="type" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="entrepot">Entrepôt</option>
                        <option value="depot">Dépôt</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Rattaché à</label>
                    <select wire:model="parent_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="">— Aucun —</option>
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
                        <label class="flex items-center gap-2 text-sm"><input wire:model="allows_stock" type="checkbox" class="rounded border-slate-300 text-indigo-600"> Stock</label>
                        <label class="flex items-center gap-2 text-sm"><input wire:model="allows_sales" type="checkbox" class="rounded border-slate-300 text-indigo-600"> Ventes</label>
                        <label class="flex items-center gap-2 text-sm"><input wire:model="allows_cash_register" type="checkbox" class="rounded border-slate-300 text-indigo-600"> Caisse</label>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="cancel" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- Warehouse Locations Panel -->
    @if($managingStore)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                    Zones & emplacements — {{ $managingStore->name }}
                </h3>
                <button wire:click="closeLocations" class="text-sm text-slate-500 hover:text-slate-700">× Fermer</button>
            </div>

            <form wire:submit="addLocation" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                <div>
                    <input wire:model="locationName" placeholder="Nom" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <input wire:model="locationCode" placeholder="Code" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm uppercase">
                </div>
                <div>
                    <select wire:model="locationType" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="rayon">Rayon</option>
                        <option value="casier">Casier</option>
                        <option value="allee">Allée</option>
                        <option value="etagere">Étagère</option>
                        <option value="zone_froide">Zone froide</option>
                        <option value="quarantaine">Quarantaine</option>
                        <option value="retour">Retour</option>
                        <option value="expire">Produits expirés</option>
                        <option value="preparation">Préparation</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Ajouter</button>
            </form>

            @php
                $groupedLocations = $managingStore->locations->groupBy('type');
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($groupedLocations as $type => $locations)
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                            <span class="text-slate-400">({{ $locations->count() }})</span>
                        </h4>
                        <div class="space-y-1">
                            @foreach($locations as $loc)
                                <div class="flex items-center justify-between px-3 py-1.5 bg-slate-50 dark:bg-white/5 rounded-lg text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-900 dark:text-white">{{ $loc->name }}</span>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $loc->code }}</span>
                                    </div>
                                    <button wire:click="deleteLocation({{ $loc->id }})" class="text-slate-400 hover:text-rose-600 p-0.5">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if($managingStore->locations->count() === 0)
                <p class="text-sm text-slate-400 text-center py-4">Aucune zone définie. Ajoutez votre premier rayon, casier ou étagère.</p>
            @endif
        </div>
    @endif

    <!-- Warehouse Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($stores as $store)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-5 hover:shadow-md transition group">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 21V10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v11"/><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 1.132-1.803l7.95-3.974a2 2 0 0 1 1.837 0l7.948 3.974A2 2 0 0 1 22 8z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $store->name }}</h4>
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded uppercase {{ $store->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $store->is_active ? 'Actif' : 'Inactif' }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ strtoupper($store->code) }} · {{ ucfirst($store->type) }}</p>
                    </div>
                    <button wire:click="edit({{ $store->id }})" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400 hover:text-indigo-600 transition shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                </div>
                @if($store->parent)<p class="text-xs text-slate-400 mt-2">Rattachement : {{ $store->parent->name }}</p>@endif
                @if($store->address || $store->phone)
                    <div class="mt-2 text-xs text-slate-500">
                        @if($store->address)<p>{{ $store->address }}</p>@endif
                        @if($store->phone)<p>{{ $store->phone }}</p>@endif
                    </div>
                @endif
                @if($store->manager)<p class="text-xs text-slate-500 mt-1">Responsable : {{ $store->manager->name }}</p>@endif

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 dark:border-white/5">
                    <div class="flex gap-1.5 text-xs text-slate-400">
                        <span class="font-medium text-slate-600">{{ $store->locations->count() }}</span> zone(s)
                    </div>
                    <button wire:click="manageLocations({{ $store->id }})" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                        Gérer les zones
                    </button>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 text-center py-16 text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-slate-300 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 21V10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v11"/><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 1.132-1.803l7.95-3.974a2 2 0 0 1 1.837 0l7.948 3.974A2 2 0 0 1 22 8z"/></svg>
                <p class="text-base font-medium">Aucun entrepôt</p>
                <p class="text-sm mt-1">Créez votre premier entrepôt ou dépôt.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $stores->links() }}</div>
</div>
