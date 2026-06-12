<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->pendingRequisitions }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Demandes en cours</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->pendingPOs }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Commandes en cours</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 border-b border-gray-200 dark:border-gray-700">
        <button wire:click="$set('tab', 'requisitions')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors {{ $tab === 'requisitions' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Demandes d'approvisionnement
        </button>
        <button wire:click="$set('tab', 'orders')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors {{ $tab === 'orders' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Commandes fournisseurs
        </button>
        <button wire:click="$set('tab', 'receipts')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors {{ $tab === 'receipts' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Réceptions
        </button>
        <button wire:click="$set('tab', 'returns')" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors {{ $tab === 'returns' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Retours fournisseurs
        </button>
    </div>

    <!-- Top bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
        </div>
        @if($tab === 'requisitions')
            <button wire:click="createRequisition" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle demande
            </button>
        @elseif($tab === 'orders')
            <button wire:click="createPO" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Nouvelle commande
            </button>
        @elseif($tab === 'receipts')
            <button wire:click="createReceipt" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Nouvelle réception
            </button>
        @elseif($tab === 'returns')
            <button wire:click="createReturn" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Nouveau retour
            </button>
        @endif
    </div>

    <!-- Product search dropdown -->
    @if(strlen($productSearch) >= 2 && count($productResults) > 0)
        <div class="absolute z-50 mt-1 w-96 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
            @foreach($productResults as $p)
                <button type="button" class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 last:border-0">
                    {{ $p['name'] }} @if($p['sku'])<span class="text-gray-500">({{ $p['sku'] }})</span>@endif
                    @if($p['purchase_price'])<span class="float-right text-gray-400">{{ number_format($p['purchase_price'], 0, ',', ' ') }} F</span>@endif
                </button>
            @endforeach
        </div>
    @endif

    <!-- ========== REQUISITIONS ========== -->
    @if($tab === 'requisitions')
        @if($showRequisitionForm)
            @include('livewire.pages.purchases._requisition_form')
        @endif
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Magasin</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Demandeur</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Priorité</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date souh.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($requisitions as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $r->reference }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $r->store?->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $r->requester?->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $r->priority === 'urgent' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}
                                    {{ $r->priority === 'high' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : '' }}
                                    {{ $r->priority === 'medium' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : '' }}
                                    {{ $r->priority === 'low' ? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' : '' }}">
                                    {{ ucfirst($r->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $r->desired_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $r->status === 'draft' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '' }}
                                    {{ $r->status === 'submitted' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : '' }}
                                    {{ $r->status === 'approved' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : '' }}
                                    {{ $r->status === 'rejected' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}
                                    {{ $r->status === 'in_progress' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : '' }}
                                    {{ str_contains($r->status, 'delivered') ? 'bg-teal-100 dark:bg-teal-900/50 text-teal-700 dark:text-teal-300' : '' }}
                                    {{ $r->status === 'cancelled' ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : '' }}">
                                    {{ str_replace('_', ' ', ucfirst($r->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($r->status === 'draft')
                                        <button wire:click="editRequisition({{ $r->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs">Modifier</button>
                                        <button wire:click="submitRequisition({{ $r->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs">Soumettre</button>
                                        <button wire:click="cancelRequisition({{ $r->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs">Annuler</button>
                                    @endif
                                    @if($r->status === 'submitted')
                                        <button wire:click="approveRequisition({{ $r->id }})" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-xs">Approuver</button>
                                        <button wire:click="rejectRequisition({{ $r->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs">Rejeter</button>
                                    @endif
                                    @if($r->status === 'approved')
                                        <button wire:click="createPOFromRequisition({{ $r->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs">Créer commande</button>
                                    @endif
                                    @if(in_array($r->status, ['submitted', 'approved', 'in_progress']))
                                        <button wire:click="cancelRequisition({{ $r->id }})" class="text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 text-xs">Annuler</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucune demande d'approvisionnement.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $requisitions->links() }}</div>
        </div>

    <!-- ========== PURCHASE ORDERS ========== -->
    @elseif($tab === 'orders')
        @if($showPOForm)
            @include('livewire.pages.purchases._po_form')
        @endif
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Fournisseur</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Montant</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date livr.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $po->reference }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $po->supplier?->name }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ number_format($po->total, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $po->delivery_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $po->status === 'draft' ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : '' }}
                                    {{ $po->status === 'sent' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : '' }}
                                    {{ $po->status === 'partially_received' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : '' }}
                                    {{ $po->status === 'received' ? 'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300' : '' }}
                                    {{ $po->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300' : '' }}">
                                    {{ str_replace('_', ' ', ucfirst($po->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($po->status === 'draft')
                                        <button wire:click="editPO({{ $po->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs">Modifier</button>
                                        <button wire:click="sendPO({{ $po->id }})" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs">Envoyer</button>
                                        <button wire:click="cancelPO({{ $po->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs">Annuler</button>
                                    @endif
                                    @if(in_array($po->status, ['sent', 'partially_received']))
                                        <button wire:click="createReceiptFromPO({{ $po->id }})" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-xs">Réceptionner</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucune commande fournisseur.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $purchaseOrders->links() }}</div>
        </div>

    <!-- ========== RECEIPTS ========== -->
    @elseif($tab === 'receipts')
        @if($showReceiptForm)
            @include('livewire.pages.purchases._receipt_form')
        @endif
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Commande</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Fournisseur</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($receipts as $rc)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $rc->reference }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $rc->purchaseOrder?->reference }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $rc->supplier?->name }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $rc->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('purchases.receipt.print', $rc->id) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">Imprimer</a>
                                <span class="text-xs text-gray-400 ml-2">{{ $rc->items->count() }} art.</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucune réception.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $receipts->links() }}</div>
        </div>

    <!-- ========== RETURNS ========== -->
    @elseif($tab === 'returns')
        @if($showReturnForm)
            @include('livewire.pages.purchases._return_form')
        @endif
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Réf.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Fournisseur</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Raison</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($returns as $sr)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $sr->reference }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $sr->supplier?->name }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs {{ $sr->return_type === 'total' ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ $sr->return_type === 'total' ? 'Total' : 'Partiel' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ ['defective' => 'Défectueux', 'error' => 'Erreur livraison', 'expired' => 'Expiré'][$sr->reason_type] ?? $sr->reason_type }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $sr->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucun retour fournisseur.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $returns->links() }}</div>
        </div>
    @endif
</div>
