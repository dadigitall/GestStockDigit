<div>
    <!-- Tabs -->
    <div class="flex items-center gap-1 mb-6 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-1 w-fit">
        <button wire:click="$set('tab', 'units')" class="px-4 py-2 text-sm font-medium rounded-lg transition {{ $tab === 'units' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Unités
            </div>
        </button>
        <button wire:click="$set('tab', 'conversions')" class="px-4 py-2 text-sm font-medium rounded-lg transition {{ $tab === 'conversions' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                Conversions
            </div>
        </button>
    </div>

    @if($tab === 'units')
        <!-- Units Tab -->
        <div class="flex justify-end mb-6">
            <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                Nouvelle unité
            </button>
        </div>

        <!-- Form -->
        @if($showForm)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6 mb-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">{{ $editingUnit ? 'Modifier' : 'Nouvelle' }} unité</h3>
                <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom *</label>
                        <input wire:model="name" class="w-full border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        @error('name') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug *</label>
                        <input wire:model="slug" class="w-full border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="ex: kg, piece">
                        @error('slug') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Unité de base</label>
                        <label class="flex items-center gap-2 mt-2 cursor-pointer">
                            <input wire:model="base_unit" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Unité fondamentale (non décomposable)</span>
                        </label>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                        <button type="button" wire:click="cancel" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Annuler</button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @forelse($units as $unit)
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $unit->name }}</span>
                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/5 text-slate-500">{{ $unit->slug }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] px-1.5 py-0.5 rounded {{ $unit->type === 'standard' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' }}">
                                    {{ $unit->type === 'standard' ? 'Standard' : 'Personnalisée' }}
                                </span>
                                @if($unit->base_unit)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Base</span>
                                @endif
                            </div>
                        </div>
                        @if($unit->type !== 'standard')
                            <div class="flex items-center gap-1">
                                <button wire:click="edit({{ $unit->id }})" class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="delete({{ $unit->id }})" wire:confirm="Supprimer cette unité ?" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="lg:col-span-4 text-center py-12 text-slate-500 dark:text-slate-400">
                    <p class="text-lg font-medium mb-1">Aucune unité</p>
                    <p class="text-sm">Créez votre première unité personnalisée.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $units->links() }}</div>

    @else
        <!-- Conversions Tab -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-white/5">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Définissez les facteurs de conversion entre unités. 
                    <span class="font-medium">Exemple :</span> 1 Carton = 24 Pièces → facteur <code class="px-1 py-0.5 bg-slate-100 dark:bg-slate-800 rounded text-xs">24</code>.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-white/5">
                            <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400 w-40">De ↓ → Vers →</th>
                            @foreach($allUnits as $to)
                                <th class="px-3 py-3 text-center font-medium text-slate-500 dark:text-slate-400 min-w-[100px]">{{ $to->slug }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach($allUnits as $from)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ $from->name }}
                                    <span class="text-[10px] text-slate-400 ml-1">({{ $from->slug }})</span>
                                </td>
                                @foreach($allUnits as $to)
                                    <td class="px-3 py-3 text-center">
                                        @if($from->id === $to->id)
                                            <span class="text-xs text-slate-400">1</span>
                                        @else
                                            @php
                                                $key = $from->id.'-'.$to->id;
                                                $conv = $this->conversions[$key] ?? null;
                                            @endphp
                                            <input
                                                wire:model.blur="conversions.{{ $key }}.factor"
                                                wire:change="setConversion({{ $from->id }}, {{ $to->id }}, $event.target.value)"
                                                type="number"
                                                step="0.000001"
                                                min="0"
                                                placeholder="—"
                                                class="w-20 text-center text-xs border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 px-2 py-1 focus:ring-1 focus:ring-indigo-500 {{ $conv ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}"
                                            >
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
