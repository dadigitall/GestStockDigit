<div class="mb-1">
    @php
        $isActive = $active == $store->id;
        $hasChildren = $store->children->count() > 0;
    @endphp
    <a href="{{ route('stores.show', $store) }}" wire:navigate
       class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ $isActive ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 font-medium' : 'hover:bg-slate-50 dark:hover:bg-white/5 text-slate-700 dark:text-slate-300' }}">
        <div class="w-6 h-6 rounded flex items-center justify-center text-white shrink-0" style="background: {{ match($store->type) {
            'filiale', 'agence' => 'linear-gradient(135deg, #7c3aed, #9333ea)',
            'boutique', 'point_vente', 'magasin' => 'linear-gradient(135deg, #10b981, #0d9488)',
            'depot', 'entrepot' => 'linear-gradient(135deg, #f59e0b, #ea580c)',
            'rayon', 'zone_stockage', 'emplacement' => 'linear-gradient(135deg, #0ea5e9, #2563eb)',
            default => 'linear-gradient(135deg, #64748b, #475569)',
        } }};">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
            </svg>
        </div>
        <span class="text-sm truncate">{{ $store->name }}</span>
        <span class="text-xs text-slate-400 uppercase ml-auto">{{ $store->code }}</span>
        @if(!$store->is_active)
            <span class="text-xs text-slate-400">(inactif)</span>
        @endif
    </a>
    @if($hasChildren)
        <div class="ml-6 pl-3 border-l border-slate-200 dark:border-white/10 space-y-1 mt-1">
            @foreach($store->children as $child)
                @include('components.store-tree-item', ['store' => $child, 'active' => $active])
            @endforeach
        </div>
    @endif
</div>
