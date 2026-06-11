<div>
    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom du produit *</label>
                            <input wire:model="name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            @error('name') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Référence</label>
                            <input wire:model="reference" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code-barres</label>
                            <input wire:model="barcode" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marque</label>
                            <input wire:model="brand" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
                            <select wire:model="category_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                                <option value="">Sélectionner...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea wire:model="description" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Prix</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prix d'achat *</label>
                            <input wire:model="purchase_price" type="number" step="0.01" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            @error('purchase_price') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prix de vente *</label>
                            <input wire:model="sale_price" type="number" step="0.01" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            @error('sale_price') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prix de gros</label>
                            <input wire:model="wholesale_price" type="number" step="0.01" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">TVA (%)</label>
                            <input wire:model="tax_rate" type="number" step="0.01" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unité vente</label>
                            <select wire:model="unit_sale" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                                <option value="piece">Pièce</option>
                                <option value="kg">Kg</option>
                                <option value="litre">Litre</option>
                                <option value="carton">Carton</option>
                                <option value="palette">Palette</option>
                                <option value="sac">Sac</option>
                                <option value="boite">Boîte</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unité achat</label>
                            <select wire:model="unit_purchase" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                                <option value="piece">Pièce</option>
                                <option value="kg">Kg</option>
                                <option value="litre">Litre</option>
                                <option value="carton">Carton</option>
                                <option value="palette">Palette</option>
                                <option value="sac">Sac</option>
                                <option value="boite">Boîte</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Stock -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stock</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock minimum *</label>
                            <input wire:model="min_stock" type="number" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            @error('min_stock') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock maximum</label>
                            <input wire:model="max_stock" type="number" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seuil d'alerte</label>
                            <input wire:model="alert_threshold" type="number" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Options</h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3">
                            <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Produit actif</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input wire:model="is_sellable" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Vendable</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input wire:model="is_stockable" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Stockable</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input wire:model="track_lot" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Suivi par lot</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input wire:model="track_serial" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Suivi par N° série</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input wire:model="track_expiry" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Suivi date péremption</span>
                        </label>
                    </div>
                </div>

                <!-- Supplier -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fournisseur</h3>
                    <select wire:model="supplier_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sélectionner...</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center gap-4">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                {{ $product ? 'Mettre à jour' : 'Créer le produit' }}
            </button>
            <a href="{{ route('products.index') }}" wire:navigate class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Annuler
            </a>
        </div>
    </form>
</div>
