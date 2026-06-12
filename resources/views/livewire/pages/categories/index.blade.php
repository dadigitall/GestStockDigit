<div>
    <div class="flex justify-end mb-6">
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle catégorie
        </button>
    </div>

    <!-- Form -->
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingCategory ? 'Modifier' : 'Nouvelle' }} catégorie</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie parente</label>
                    <select wire:model="parent_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Aucune</option>
                        @foreach($parentCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur</label>
                    <input wire:model="color" type="color" class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marge (%)</label>
                    <input wire:model="margin_rate" type="number" step="0.01" min="0" max="100" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marge min. (%)</label>
                    <input wire:model="min_margin" type="number" step="0.01" min="0" max="100" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seuil stock</label>
                    <input wire:model="stock_threshold" type="number" min="0" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($categories as $category)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full" style="background: {{ $category->color ?? '#6366f1' }}"></div>
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $category->name }}</h4>
                            @if($category->parent)
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sous-catégorie de {{ $category->parent->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="edit({{ $category->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button wire:click="toggleActive({{ $category->id }})" class="p-1.5 transition-colors {{ $category->is_active ? 'text-gray-400 hover:text-amber-600' : 'text-gray-400 hover:text-green-600' }}">
                            @if($category->is_active)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </button>
                    </div>
                </div>
                @if($category->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $category->description }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="text-gray-400">{{ $category->products_count ?? 0 }} produit(s)</span>
                    @if($category->margin_rate)
                        <span class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">Marge {{ $category->margin_rate }}%</span>
                    @endif
                    @if($category->stock_threshold)
                        <span class="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Seuil {{ $category->stock_threshold }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="lg:col-span-3 text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="text-lg font-medium mb-1">Aucune catégorie</p>
                <p class="text-sm">Créez votre première catégorie.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $categories->links() }}
    </div>
</div>
