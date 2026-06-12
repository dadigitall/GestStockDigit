<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un fournisseur..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau fournisseur
        </button>
    </div>

    @if($showForm && $editingSupplier)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Info form -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $editingSupplier ? 'Modifier' : 'Nouveau' }} fournisseur</h3>
                <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                        <input wire:model="name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact principal</label>
                        <input wire:model="contact_name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input wire:model="email" type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                        <input wire:model="phone" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                        <input wire:model="address" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select wire:model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Sélectionner...</option>
                            <option value="local">Local</option>
                            <option value="international">International</option>
                            <option value="manufacturer">Fabricant</option>
                            <option value="distributor">Distributeur</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devise</label>
                        <select wire:model="currency" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="">Par défaut</option>
                            <option value="XAF">XAF (F CFA)</option>
                            <option value="EUR">EUR (Euro)</option>
                            <option value="USD">USD (Dollar US)</option>
                            <option value="GBP">GBP (Livre sterling)</option>
                            <option value="CNY">CNY (Yuan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conditions de paiement</label>
                        <input wire:model="payment_terms" type="text" placeholder="ex: 30 jours fin de mois" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Délai livraison (jours)</label>
                        <input wire:model="delivery_delay_days" type="number" min="0" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea wire:model="notes" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                        <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                    </div>
                </form>
            </div>

            <!-- Evaluation sidebar -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Évaluation</h3>

                @if($evaluation)
                    <div class="mb-4 text-center">
                        <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $evaluation->overall_rating }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Note globale / 5</div>
                        <div class="text-[10px] text-gray-400 mt-1">Évalué le {{ $evaluation->evaluated_at->format('d/m/Y') }}</div>
                    </div>
                @endif

                <div class="space-y-3">
                    @php
                        $criteria = [
                            'respectDelays' => ['label' => 'Respect des délais', 'value' => $respectDelays],
                            'productQuality' => ['label' => 'Qualité des produits', 'value' => $productQuality],
                            'returnRate' => ['label' => 'Taux de retour', 'value' => $returnRate],
                            'averagePrice' => ['label' => 'Prix moyen', 'value' => $averagePrice],
                            'reliability' => ['label' => 'Fiabilité', 'value' => $reliability],
                            'purchaseVolume' => ['label' => "Volume d'achat", 'value' => $purchaseVolume],
                        ];
                    @endphp

                    @foreach($criteria as $field => $criterion)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $criterion['label'] }}</span>
                                <span class="text-xs text-gray-500">{{ $criterion['value'] }}/5</span>
                            </div>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" wire:click="setScore('{{ $field }}', {{ $i }})"
                                        class="w-6 h-6 rounded-full text-xs font-medium transition
                                        {{ $i <= ${$field} ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-400' }}">
                                        {{ $i }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Commentaire</label>
                        <textarea wire:model="evaluationComment" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <button type="button" wire:click="saveEvaluation" class="w-full px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        {{ $evaluation ? 'Mettre à jour' : 'Enregistrer' }} l'évaluation
                    </button>
                </div>
            </div>
        </div>
    @elseif($showForm && !$editingSupplier)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Nouveau fournisseur</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact principal</label>
                    <input wire:model="contact_name" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                    <input wire:model="phone" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                    <input wire:model="address" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model="type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sélectionner...</option>
                        <option value="local">Local</option>
                        <option value="international">International</option>
                        <option value="manufacturer">Fabricant</option>
                        <option value="distributor">Distributeur</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devise</label>
                    <select wire:model="currency" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Par défaut</option>
                        <option value="XAF">XAF (F CFA)</option>
                        <option value="EUR">EUR (Euro)</option>
                        <option value="USD">USD (Dollar US)</option>
                        <option value="GBP">GBP (Livre sterling)</option>
                        <option value="CNY">CNY (Yuan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conditions de paiement</label>
                    <input wire:model="payment_terms" type="text" placeholder="ex: 30 jours fin de mois" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Délai livraison (jours)</label>
                    <input wire:model="delivery_delay_days" type="number" min="0" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea wire:model="notes" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Nom</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Contact</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Email</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Téléphone</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Délai</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Note</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Statut</th>
                    <th class="px-6 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $supplier->name }}</div>
                            @if($supplier->contact_name)
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->contact_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $supplier->contact_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $supplier->email ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $supplier->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            @if($supplier->delivery_delay_days)
                                <span class="text-xs">{{ $supplier->delivery_delay_days }} j</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($supplier->latestEvaluation)
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $supplier->latestEvaluation->overall_rating }}</span>
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3 h-3 {{ $i <= round($supplier->latestEvaluation->overall_rating) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        @endfor
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($supplier->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">Actif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">Inactif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="edit({{ $supplier->id }})" class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Modifier
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <p class="text-lg font-medium mb-1">Aucun fournisseur</p>
                            <p class="text-sm">Ajoutez votre premier fournisseur.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $suppliers->links() }}
        </div>
    </div>
</div>
