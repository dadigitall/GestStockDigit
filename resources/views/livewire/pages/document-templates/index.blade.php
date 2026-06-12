<div>
    <div class="flex justify-end mb-6">
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau modèle
        </button>
    </div>

    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editId ? 'Modifier' : 'Nouveau' }} modèle de document</h3>
            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                        <input wire:model="name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                        <select wire:model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="quotation">Devis</option>
                            <option value="invoice">Facture</option>
                            <option value="delivery_note">Bon de livraison</option>
                            <option value="customer_order">Commande client</option>
                        </select>
                        @error('type') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Format papier *</label>
                        <select wire:model="paperFormat" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="A4">A4</option>
                            <option value="ticket">Ticket</option>
                        </select>
                        @error('paperFormat') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model="isDefault" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Modèle par défaut</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur primaire (hex)</label>
                        <input wire:model="colors.primary" type="color" class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur secondaire (hex)</label>
                        <input wire:model="colors.secondary" type="color" class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur d'accent (hex)</label>
                        <input wire:model="colors.accent" type="color" class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur de fond (hex)</label>
                        <input wire:model="colors.background" type="color" class="w-full h-10 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo</label>
                    <input wire:model="logo" type="file" accept="image/*" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50">
                    @error('logo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Entête HTML</label>
                    <textarea wire:model="headerHtml" rows="4" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500 font-mono text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pied de page HTML</label>
                    <textarea wire:model="footerHtml" rows="4" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500 font-mono text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mentions légales</label>
                    <textarea wire:model="legalMentions" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conditions générales</label>
                    <textarea wire:model="terms" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    <button type="button" wire:click="resetForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                </div>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($templates as $template)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $template->name }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ match($template->type) { 'quotation' => 'Devis', 'invoice' => 'Facture', 'delivery_note' => 'Bon de livraison', 'customer_order' => 'Commande client', default => $template->type } }}
                            · {{ $template->paper_format }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        @if($template->is_default)
                            <span class="text-xs px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-medium">Défaut</span>
                        @endif
                    </div>
                </div>

                @if($template->colors && count(array_filter($template->colors)))
                    <div class="flex items-center gap-1.5 mt-3">
                        @foreach(['primary', 'secondary', 'accent', 'background'] as $key)
                            @if(!empty($template->colors[$key]))
                                <div class="w-5 h-5 rounded-full border border-gray-300 dark:border-gray-600" style="background: {{ $template->colors[$key] }}" title="{{ $key }}"></div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="flex items-center gap-2 mt-4">
                    <button wire:click="edit({{ $template->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    @if(!$template->is_default)
                        <button wire:click="setDefault({{ $template->id }})" class="p-1.5 text-gray-400 hover:text-emerald-600 transition-colors" title="Définir par défaut">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                    @endif
                    <button wire:click="delete({{ $template->id }})" wire:confirm="Supprimer ce modèle ?" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="lg:col-span-3 text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="text-lg font-medium mb-1">Aucun modèle</p>
                <p class="text-sm">Créez votre premier modèle de document.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $templates->links() }}
    </div>
</div>
