<div class="mb-1">
    @php
        $isActive = $active == $store->id;
        $hasChildren = $store->children->count() > 0;
        $typeClass = match($store->type) {
            'filiale', 'agence' => 'from-violet-500 to-purple-600',
            'boutique', 'point_vente', 'magasin' => 'from-emerald-500 to-teal-600',
            'depot', 'entrepot' => 'from-amber-500 to-orange-600',
            'rayon', 'zone_stockage', 'emplacement' => 'from-sky-500 to-blue-600',
            default => 'from-slate-500 to-slate-600',
        };
    @endphp
    <div class="flex items-center gap-1.5 {{ !$store->is_active ? 'opacity-50' : '' }}">
        @if(isset($depth) && $depth > 0)
            <div class="flex items-center shrink-0" style="width: {{ $depth * 20 }}px">
                @for($i = 0; $i < $depth; $i++)
                    <div class="w-5 flex items-center justify-center">
                        <div class="w-px h-full bg-slate-200 dark:bg-white/10"></div>
                    </div>
                @endfor
            </div>
            <div class="w-4 shrink-0 flex items-center">
                <div class="w-3 h-px bg-slate-300 dark:bg-white/20"></div>
            </div>
        @endif
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition w-full {{ $isActive ? 'bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-200 dark:ring-indigo-800' : 'hover:bg-slate-50 dark:hover:bg-white/5' }}">
            <div class="w-6 h-6 rounded {{ $depth > 0 ? 'md:w-6 md:h-6' : '' }} bg-gradient-to-br {{ $typeClass }} flex items-center justify-center text-white shrink-0">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            <a href="{{ route('stores.show', $store) }}" wire:navigate class="text-sm font-medium truncate {{ $isActive ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                {{ $store->name }}
            </a>
            <span class="text-[10px] font-mono text-slate-400 uppercase">{{ $store->code }}</span>
            <span class="text-[10px] text-slate-400 capitalize hidden md:inline">{{ str_replace('_', ' ', $store->type) }}</span>
            @if($store->manager)
                <span class="text-[10px] text-slate-400 hidden lg:inline">· {{ $store->manager->name }}</span>
            @endif
            @if(!$store->is_active)
                <span class="text-[10px] text-slate-400 italic">inactif</span>
            @endif
            @if($hasChildren)
                <span class="text-[10px] text-slate-400 ml-auto">{{ $store->children->count() }} enfant(s)</span>
            @endif
        </div>
    </div>
    @if($hasChildren)
        <div class="space-y-0.5 mt-0.5">
            @foreach($store->children as $child)
                @include('components.store-tree-item', ['store' => $child, 'active' => $active, 'depth' => ($depth ?? 0) + 1])
            @endforeach
        </div>
    @endif
</div>
