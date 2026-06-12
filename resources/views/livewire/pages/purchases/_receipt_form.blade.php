<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Nouvelle réception</h3>
    <form wire:submit="saveReceipt" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commande fournisseur *</label>
                <select wire:model="receipt_purchase_order_id" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Sélectionner une commande...</option>
                    @foreach(\App\Models\PurchaseOrder::where('company_id', auth()->user()->company_id)->whereIn('status', ['sent', 'partially_received'])->get() as $po)
                        <option value="{{ $po->id }}">{{ $po->reference }} — {{ $po->supplier?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <input wire:model="receipt_notes" type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        @if(count($receipt_items) > 0)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Articles reçus</h4>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Produit</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Commandé</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Accepté *</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Rejeté</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">P.U.</th>
                            <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Lot</th>
                            <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Exp.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipt_items as $i => $item)
                            <tr wire:key="ritem-{{ $i }}">
                                <td class="px-3 py-1 text-xs text-gray-900 dark:text-white">{{ $item['product_name'] ?? 'Produit #'.$item['product_id'] }}</td>
                                <td class="px-3 py-1 text-right text-xs text-gray-500 dark:text-gray-400">{{ $item['quantity_ordered'] }}</td>
                                <td class="px-3 py-1"><input wire:model="receipt_items.{{ $i }}.quantity_accepted" type="number" step="0.01" min="0" class="w-20 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs"></td>
                                <td class="px-3 py-1"><input wire:model="receipt_items.{{ $i }}.quantity_rejected" type="number" step="0.01" min="0" class="w-20 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs"></td>
                                <td class="px-3 py-1"><input wire:model="receipt_items.{{ $i }}.unit_cost" type="number" step="1" min="0" class="w-24 text-right border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs"></td>
                                <td class="px-3 py-1"><input wire:model="receipt_items.{{ $i }}.lot_number" type="text" class="w-24 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs"></td>
                                <td class="px-3 py-1"><input wire:model="receipt_items.{{ $i }}.expiry_date" type="date" class="w-28 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1 text-xs"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex items-center gap-2 pt-2">
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Valider la réception</button>
            <button type="button" wire:click="resetReceiptForm" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Annuler</button>
        </div>
    </form>
</div>
