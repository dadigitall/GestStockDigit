<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Coupons et codes promo</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Codes promo, coupons de réduction et offres spéciales</p>
        </div>
        <button wire:click="$set('showForm', true)" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Nouveau coupon
        </button>
    </div>

    @if($showForm)
        <div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">{{ $editId ? 'Modifier' : 'Nouveau' }} coupon</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                    <div class="flex gap-2">
                        <input wire:model="code" type="text" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm uppercase">
                        <button wire:click="generateCode" type="button" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg transition">Générer</button>
                    </div>
                    @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select wire:model="couponType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="fixed">Montant fixe (F)</option>
                        <option value="percentage">Pourcentage (%)</option>
                        <option value="free_shipping">Livraison gratuite</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Valeur *</label>
                    <input wire:model="value" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('value') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Promotion liée</label>
                    <select wire:model="promotionId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Aucune</option>
                        @foreach($promotions as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant min de commande</label>
                    <input wire:model="minOrderAmount" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remise max (plafond)</label>
                    <input wire:model="maxDiscount" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Limite d'utilisation (0 = illimité)</label>
                    <input wire:model="usageLimit" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Utilisations par client</label>
                    <input wire:model="usagePerCustomer" type="number" step="1" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date début</label>
                    <input wire:model="startsAt" type="datetime-local" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date fin</label>
                    <input wire:model="endsAt" type="datetime-local" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div class="flex items-center gap-3 mt-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Actif</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-6 border-t border-gray-100 dark:border-gray-700 mt-6">
                <button wire:click="{{ $editId ? 'update' : 'save' }}" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">
                    {{ $editId ? 'Mettre à jour' : 'Enregistrer' }}
                </button>
                <button wire:click="$set('showForm', false)" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition">Annuler</button>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4">
        <input wire:model.live.debounce="search" type="text" placeholder="Rechercher par code..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase">Valeur</th>
                    <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase">Utilisé</th>
                    <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase">Limite</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Expire</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Actif</th>
                    <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                        <td class="py-3 px-4">
                            <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $coupon->code }}</span>
                            @if($coupon->promotion)
                                <div class="text-xs text-gray-500">{{ $coupon->promotion->name }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">
                            {{ ['fixed' => 'Montant fixe', 'percentage' => 'Pourcentage', 'free_shipping' => 'Livraison'][$coupon->type] ?? $coupon->type }}
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-gray-900 dark:text-white">
                            @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                            @else
                                {{ number_format($coupon->value, 0, ',', ' ') }} F
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400">{{ $coupon->times_used }}</td>
                        <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-400">{{ $coupon->usage_limit ?: '∞' }}</td>
                        <td class="py-3 px-4 text-xs text-gray-500">
                            @if($coupon->ends_at)
                                {{ $coupon->ends_at->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button wire:click="toggleActive({{ $coupon->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition
                                {{ $coupon->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                {{ $coupon->is_active ? 'Oui' : 'Non' }}
                            </button>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="edit({{ $coupon->id }})" class="p-2 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-indigo-600 transition" title="Modifier">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button wire:click="delete({{ $coupon->id }})" wire:confirm="Supprimer ce coupon ?" class="p-2 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 text-rose-600 transition" title="Supprimer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-12 text-center text-gray-500 dark:text-gray-400">Aucun coupon</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $coupons->links() }}</div>
    </div>
</div>
