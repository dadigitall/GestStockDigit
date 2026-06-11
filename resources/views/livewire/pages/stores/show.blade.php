<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('stores.index') }}" wire:navigate class="hover:text-indigo-600 transition">Entités</a>
        @foreach($ancestors as $ancestor)
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            <a href="{{ route('stores.show', $ancestor) }}" wire:navigate class="hover:text-indigo-600 transition">{{ $ancestor->name }}</a>
        @endforeach
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        <span class="text-slate-900 dark:text-white font-medium">{{ $store->name }}</span>
    </nav>

    <!-- Header -->
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ match($store->type) {
                'filiale', 'agence' => 'from-violet-500 to-purple-600',
                'boutique', 'point_vente', 'magasin' => 'from-emerald-500 to-teal-600',
                'depot', 'entrepot' => 'from-amber-500 to-orange-600',
                'rayon', 'zone_stockage', 'emplacement' => 'from-sky-500 to-blue-600',
                default => 'from-slate-500 to-slate-600',
            } }} flex items-center justify-center text-white shadow-lg">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $store->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $store->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                        {{ $store->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span class="text-xs text-slate-400">{{ strtoupper($store->code) }}</span>
                    <span class="text-xs text-slate-400">·</span>
                    <span class="text-xs text-slate-400 capitalize">{{ str_replace('_', ' ', $store->type) }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stores.index') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300 dark:border-slate-600 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- General Info -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                Informations générales
            </h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Nom</label>
                    <p class="text-slate-900 dark:text-white font-medium">{{ $store->name }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Code interne</label>
                    <p class="text-slate-900 dark:text-white font-medium font-mono">{{ strtoupper($store->code) }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Type</label>
                    <p class="text-slate-900 dark:text-white font-medium capitalize">{{ str_replace('_', ' ', $store->type) }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Statut</label>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $store->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                        {{ $store->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Adresse</label>
                    <p class="text-slate-900 dark:text-white">{{ $store->address ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Téléphone</label>
                    <p class="text-slate-900 dark:text-white">{{ $store->phone ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Email</label>
                    <p class="text-slate-900 dark:text-white">{{ $store->email ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Horaires</label>
                    <p class="text-slate-900 dark:text-white">{{ $store->opening_hours ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Responsable</label>
                    <p class="text-slate-900 dark:text-white">{{ $store->manager?->name ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-0.5">Rattachement</label>
                    <p class="text-slate-900 dark:text-white">
                        @if($store->parent)
                            <a href="{{ route('stores.show', $store->parent) }}" wire:navigate class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $store->parent->name }}</a>
                        @else
                            <span class="text-slate-400">Racine (aucun parent)</span>
                        @endif
                    </p>
                </div>
            </div>
            @if($store->notes)
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-white/5">
                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-1">Notes</label>
                    <p class="text-sm text-slate-700 dark:text-slate-300">{{ $store->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Capabilities & Meta -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    Capacités
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Stock</span>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $store->allows_stock ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                            {{ $store->allows_stock ? 'Activé' : 'Désactivé' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-slate-100 dark:border-white/5">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Ventes</span>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $store->allows_sales ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                            {{ $store->allows_sales ? 'Activé' : 'Désactivé' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-slate-100 dark:border-white/5">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Caisse</span>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $store->allows_cash_register ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                            {{ $store->allows_cash_register ? 'Activé' : 'Désactivé' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h2"/></svg>
                    Métadonnées
                </h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Créé le</span>
                        <span class="text-slate-900 dark:text-white">{{ $store->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 dark:border-white/5 pt-3">
                        <span class="text-slate-500">Modifié le</span>
                        <span class="text-slate-900 dark:text-white">{{ $store->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hierarchy Tree: Children -->
    @if($children->count() > 0)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                Entités rattachées ({{ $children->count() }})
            </h2>
            <div class="space-y-2">
                @foreach($children as $child)
                    <a href="{{ route('stores.show', $child) }}" wire:navigate
                       class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition group">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ match($child->type) {
                            'filiale', 'agence' => 'from-violet-500 to-purple-600',
                            'boutique', 'point_vente', 'magasin' => 'from-emerald-500 to-teal-600',
                            'depot', 'entrepot' => 'from-amber-500 to-orange-600',
                            'rayon', 'zone_stockage', 'emplacement' => 'from-sky-500 to-blue-600',
                            default => 'from-slate-500 to-slate-600',
                        } }} flex items-center justify-center text-white shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-slate-900 dark:text-white group-hover:text-indigo-600 transition">{{ $child->name }}</div>
                            <div class="text-xs text-slate-400 flex items-center gap-1">
                                <span class="uppercase">{{ $child->code }}</span>
                                <span>·</span>
                                <span class="capitalize">{{ str_replace('_', ' ', $child->type) }}</span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-600 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                Hiérarchie
            </h2>
            <p class="text-sm text-slate-400">Aucune entité rattachée.</p>
        </div>
    @endif

    <!-- Full Hierarchy Tree -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6h4"/><path d="M8 6v12"/><path d="M14 14h4"/><path d="M16 14v4"/><path d="M3 3v18h18"/></svg>
            Arbre hiérarchique complet
        </h2>
        <div class="text-sm">
            @php
                $rootList = \App\Models\Store::where('company_id', $store->company_id)
                    ->whereNull('parent_id')
                    ->with('children')
                    ->orderBy('name')
                    ->get();
            @endphp
            @foreach($rootList as $root)
                @include('components.store-tree-item', ['store' => $root, 'active' => $store->id])
            @endforeach
        </div>
    </div>
</div>
