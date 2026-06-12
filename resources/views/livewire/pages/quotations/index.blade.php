<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Toolbar --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <select wire:model.live="filterStatus" class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                <option value="">Tous statuts</option>
                @foreach(['draft', 'sent', 'accepted', 'refused', 'expired', 'converted', 'cancelled'] as $s)
                    <option value="{{ $s }}">{{ $this->statusLabel($s) }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            + Nouveau devis
        </button>
    </div>

    {{-- Formulaire --}}
    @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">{{ $editId ? 'Modifier' : 'Nouveau' }} devis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Client <span class="text-red-500">*</span></label>
                    <select wire:model="customerId" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                        <option value="">Sélectionner un client...</option>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('customerId') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date de validité</label>
                    <input wire:model="validityDate" type="date" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                    @error('validityDate') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Produits --}}
            <div class="mt-4">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Produits <span class="text-red-500">*</span></label>
                <div class="relative mb-3">
                    <input wire:model.live.debounce.300ms="productSearch" type="text" placeholder="Rechercher un produit par nom, référence ou code-barres..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm">
                    @if(!empty($productResults))
                        <div class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @foreach($productResults as $p)
                                <button wire:click="addToCart({{ $p['id'] }})" type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <span class="font-medium">{{ $p['name'] }}</span>
                                    <span class="text-gray-400 text-xs ml-2">{{ $p['reference'] }}</span>
                                    <span class="text-gray-400 text-xs ml-2">{{ number_format($p['sale_price'] ?? 0, 0, ',', ' ') }} F</span>
                                    <span class="text-gray-400 text-xs ml-2">Stock: {{ number_format($p['stock_quantity'] ?? 0, 0, ',', ' ') }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(!empty($cart))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm mb-3">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs w-20">Qté</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs w-28">Prix unitaire</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs w-20">Remise %</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs w-20">TVA %</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs w-28">Sous-total</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500 dark:text-gray-400 text-xs w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($cart as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">
                                            {{ $item['name'] }}
                                            <span class="text-gray-400 ml-1">({{ $item['reference'] }})</span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'qty', $event.target.value)" type="number" step="0.01" min="1" value="{{ $item['qty'] }}" class="w-16 text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-1 py-1 text-xs mx-auto block">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'price', $event.target.value)" type="number" step="0.01" min="0" value="{{ $item['price'] }}" class="w-24 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs ml-auto block">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'discount', $event.target.value)" type="number" step="0.01" min="0" max="100" value="{{ $item['discount'] }}" class="w-16 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-1 py-1 text-xs ml-auto block">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input wire:change="updateItem({{ $index }}, 'tax_rate', $event.target.value)" type="number" step="0.01" min="0" max="100" value="{{ $item['tax_rate'] }}" class="w-16 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-1 py-1 text-xs ml-auto block">
                                        </td>
                                        <td class="px-3 py-2 text-right text-gray-900 dark:text-white text-xs font-medium">{{ number_format($item['subtotal'], 0, ',', ' ') }} F</td>
                                        <td class="px-3 py-2 text-center">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic mb-3">Aucun produit ajouté. Utilisez la recherche ci-dessus pour ajouter des produits.</p>
                @endif
                @error('cart') <span class="text-xs text-red-500 block mb-2">{{ $message }}</span> @enderror
            </div>

            {{-- Totaux --}}
            <div class="flex justify-end mt-4">
                <div class="w-72 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Sous-total</span>
                        <span>{{ number_format($subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>TVA</span>
                        <span>{{ number_format($taxAmount, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Remise globale</span>
                        <div class="flex items-center gap-1">
                            <input wire:model.live="discount" type="number" step="0.01" min="0" class="w-20 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs">
                            <span>F</span>
                        </div>
                    </div>
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-2">
                        <span>Total</span>
                        <span>{{ number_format($total, 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>

            {{-- Conditions et notes --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Conditions commerciales</label>
                    <textarea wire:model="commercialTerms" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm" placeholder="Ex: Paiement à 30 jours, livraison gratuite..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Notes internes</label>
                    <textarea wire:model="notes" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm"></textarea>
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">{{ $editId ? 'Mettre à jour' : 'Créer le devis' }}</button>
                <button wire:click="resetForm" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Annuler</button>
            </div>
        </div>
    @endif

    {{-- Détail --}}
    @if($showDetail && $detail)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] flex flex-col">
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $detail->reference }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $detail->customer?->name ?? 'Client inconnu' }}</p>
                    </div>
                    <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl leading-none">&times;</button>
                </div>
                <div class="overflow-y-auto p-6 space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 text-xs">Statut</span>
                            <p><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $this->statusBadge($detail->status) }}">{{ $this->statusLabel($detail->status) }}</span></p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Client</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->customer?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Validité</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->validity_date?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Créé le</span>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $detail->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        @if($detail->sent_at)
                            <div><span class="text-gray-500 text-xs">Envoyé le</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->sent_at->format('d/m/Y H:i') }}</p></div>
                        @endif
                        @if($detail->accepted_at)
                            <div><span class="text-gray-500 text-xs">Accepté le</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->accepted_at->format('d/m/Y H:i') }}</p></div>
                        @endif
                        @if($detail->refused_at)
                            <div><span class="text-gray-500 text-xs">Refusé le</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->refused_at->format('d/m/Y H:i') }}</p></div>
                        @endif
                        @if($detail->convertedInvoice)
                            <div><span class="text-gray-500 text-xs">Facture générée</span><p class="font-medium text-gray-900 dark:text-white">{{ $detail->convertedInvoice->reference }}</p></div>
                        @endif
                    </div>

                    @if($detail->commercial_terms)
                        <div class="text-sm"><span class="text-gray-500 text-xs">Conditions commerciales</span><p class="text-gray-900 dark:text-white mt-1">{{ $detail->commercial_terms }}</p></div>
                    @endif

                    @if($detail->notes)
                        <div class="text-sm"><span class="text-gray-500 text-xs">Notes</span><p class="text-gray-900 dark:text-white mt-1">{{ $detail->notes }}</p></div>
                    @endif

                    <div>
                        <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Articles</h4>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Produit</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Qté</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Prix U.</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Remise</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">TVA</th>
                                    <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($detail->items as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-3 py-2 text-gray-900 dark:text-white text-xs">{{ $item->product_name ?? ($item->product?->name ?? '#'.$item->product_id) }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ $item->discount > 0 ? $item->discount.'%' : '—' }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400 text-xs">{{ $item->tax_rate > 0 ? $item->tax_rate.'%' : '—' }}</td>
                                        <td class="px-3 py-2 text-right text-gray-900 dark:text-white text-xs font-medium">{{ number_format($item->subtotal, 0, ',', ' ') }} F</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-gray-200 dark:border-gray-700">
                                <tr>
                                    <td colspan="5" class="px-3 py-2 text-right text-xs text-gray-600 dark:text-gray-400">Sous-total</td>
                                    <td class="px-3 py-2 text-right text-xs text-gray-900 dark:text-white font-medium">{{ number_format($detail->subtotal, 0, ',', ' ') }} F</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="px-3 py-2 text-right text-xs text-gray-600 dark:text-gray-400">TVA</td>
                                    <td class="px-3 py-2 text-right text-xs text-gray-900 dark:text-white font-medium">{{ number_format($detail->tax_amount, 0, ',', ' ') }} F</td>
                                </tr>
                                @if($detail->discount > 0)
                                    <tr>
                                        <td colspan="5" class="px-3 py-2 text-right text-xs text-gray-600 dark:text-gray-400">Remise</td>
                                        <td class="px-3 py-2 text-right text-xs text-red-600 font-medium">-{{ number_format($detail->discount, 0, ',', ' ') }} F</td>
                                    </tr>
                                @endif
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td colspan="5" class="px-3 py-2 text-right text-sm font-semibold text-gray-900 dark:text-white">Total</td>
                                    <td class="px-3 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">{{ number_format($detail->total, 0, ',', ' ') }} F</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <a href="{{ route('quotations.print', $detail->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            Imprimer
                        </a>
                        @if($detail->status === 'draft')
                            <button wire:click="send({{ $detail->id }})" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-xs font-medium rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800/50">Envoyer</button>
                        @endif
                        @if($detail->status === 'sent')
                            <button wire:click="accept({{ $detail->id }})" class="px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs font-medium rounded-lg hover:bg-emerald-200 dark:hover:bg-emerald-800/50">Accepter</button>
                            <button wire:click="refuse({{ $detail->id }})" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-xs font-medium rounded-lg hover:bg-red-200 dark:hover:bg-red-800/50">Refuser</button>
                        @endif
                        @if($detail->status === 'accepted')
                            <button wire:click="convertToInvoice({{ $detail->id }})" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-800/50">Transformer en facture</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Liste --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Client</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400 text-xs">Total</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Statut</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400 text-xs">Validité</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($quotations as $q)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 text-xs font-mono font-medium text-gray-900 dark:text-white">{{ $q->reference }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-300">{{ $q->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $q->created_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-right font-medium text-gray-900 dark:text-white">{{ number_format($q->total, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $this->statusBadge($q->status) }}">{{ $this->statusLabel($q->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $q->validity_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <button wire:click="view({{ $q->id }})" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-medium rounded hover:bg-gray-200 dark:hover:bg-gray-600">Détail</button>

                                    @if($q->status === 'draft')
                                        <button wire:click="edit({{ $q->id }})" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[10px] font-medium rounded hover:bg-blue-200 dark:hover:bg-blue-800/50">Modifier</button>
                                        <button wire:click="send({{ $q->id }})" class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-[10px] font-medium rounded hover:bg-indigo-200 dark:hover:bg-indigo-800/50">Envoyer</button>
                                        <button wire:click="cancel({{ $q->id }})" onclick="return confirm('Annuler ce devis ?')" class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[10px] font-medium rounded hover:bg-red-200 dark:hover:bg-red-800/50">Annuler</button>
                                    @endif

                                    @if($q->status === 'sent')
                                        <button wire:click="accept({{ $q->id }})" class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[10px] font-medium rounded hover:bg-emerald-200 dark:hover:bg-emerald-800/50">Accepter</button>
                                        <button wire:click="refuse({{ $q->id }})" class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 text-[10px] font-medium rounded hover:bg-red-200 dark:hover:bg-red-800/50">Refuser</button>
                                        <button wire:click="cancel({{ $q->id }})" onclick="return confirm('Annuler ce devis ?')" class="px-2 py-1 bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 text-[10px] font-medium rounded hover:bg-rose-200 dark:hover:bg-rose-800/50">Annuler</button>
                                    @endif

                                    @if($q->status === 'accepted')
                                        <button wire:click="convertToInvoice({{ $q->id }})" class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-[10px] font-medium rounded hover:bg-indigo-200 dark:hover:bg-indigo-800/50">Transformer en facture</button>
                                    @endif

                                    <a href="{{ route('quotations.print', $q->id) }}" target="_blank" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-medium rounded hover:bg-gray-200 dark:hover:bg-gray-600">Imprimer</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">Aucun devis trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $quotations->links() }}
    </div>
</div>
