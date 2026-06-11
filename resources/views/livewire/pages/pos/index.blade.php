<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-300 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-300 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    @if($saleCompleted && $lastSale)
        <!-- Receipt -->
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Vente validée</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $lastSale->reference }}</p>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2 text-sm">
                @foreach($lastSale->items as $item)
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">{{ $item->product_name }} x{{ $item->quantity }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($item->subtotal, 0, ',', ' ') }} F</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4 space-y-1 text-sm">
                <div class="flex justify-between font-bold text-lg text-gray-900 dark:text-white">
                    <span>Total</span>
                    <span>{{ number_format($lastSale->total, 0, ',', ' ') }} F</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Payé</span>
                    <span>{{ number_format($lastSale->paid_amount, 0, ',', ' ') }} F</span>
                </div>
                @if($lastSale->change_amount > 0)
                    <div class="flex justify-between text-gray-500">
                        <span>Monnaie</span>
                        <span class="text-green-600">{{ number_format($lastSale->change_amount, 0, ',', ' ') }} F</span>
                    </div>
                @endif
                <div class="flex justify-between text-gray-500">
                    <span>Paiement</span>
                    <span class="capitalize">{{ str_replace('_', ' ', $lastSale->payment_method) }}</span>
                </div>
            </div>

            <button wire:click="newSale" class="mt-6 w-full py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                Nouvelle vente
            </button>
        </div>

    @else
        <!-- POS Interface -->
        <div class="flex flex-col lg:flex-row gap-6" style="min-height: calc(100vh - 12rem);">
            <!-- Products Panel -->
            <div class="flex-1 flex flex-col">
                <div class="mb-4">
                    <input wire:model.live.debounce.200ms="search" type="text" placeholder="Rechercher un produit (nom, réf., code-barres)..." class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex-1 overflow-y-auto">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                        @foreach($products as $product)
                            <button wire:click="addToCart({{ $product->id }})" class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 hover:shadow-md transition-all text-left {{ $product->is_stockable && $product->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $product->is_stockable && $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                                <p class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2">{{ $product->name }}</p>
                                @if($product->reference)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $product->reference }}</p>
                                @endif
                                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-2">{{ number_format($product->sale_price, 0, ',', ' ') }} F</p>
                                @if($product->is_stockable)
                                    <p class="text-xs mt-1 {{ $product->stock_quantity <= 0 ? 'text-red-500' : ($product->stock_quantity <= $product->min_stock ? 'text-amber-500' : 'text-gray-500') }}">
                                        Stock: {{ $product->stock_quantity }}
                                    </p>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Cart Panel -->
            <div class="lg:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Panier</h3>
                    <span class="text-sm text-gray-500">{{ count($this->cart) }} article(s)</span>
                </div>

                <!-- Customer selection -->
                <div class="mb-4">
                    <select wire:model.live="customerId" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2">
                        <option value="">Client comptoir</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cart items -->
                <div class="flex-1 overflow-y-auto space-y-2 mb-4">
                    @forelse($this->cart as $index => $item)
                        <div class="flex items-center gap-2 p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($item['price'], 0, ',', ' ') }} F</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="updateQty({{ $index }}, {{ $item['qty'] - 1 }})" class="w-7 h-7 flex items-center justify-center rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 text-sm font-bold">−</button>
                                <input wire:change="updateQty({{ $index }}, $event.target.value)" value="{{ $item['qty'] }}" type="number" min="1" class="w-10 text-center text-sm font-medium bg-transparent border-none text-gray-900 dark:text-white [appearance:textfield]">
                                <button wire:click="updateQty({{ $index }}, {{ $item['qty'] + 1 }})" class="w-7 h-7 flex items-center justify-center rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 text-sm font-bold">+</button>
                            </div>
                            <div class="text-right min-w-[70px]">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($item['subtotal'], 0, ',', ' ') }} F</p>
                                <button wire:click="removeItem({{ $index }})" class="text-xs text-red-500 hover:text-red-700">× retirer</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <p class="text-sm">Panier vide</p>
                            <p class="text-xs mt-1">Cliquez sur un produit</p>
                        </div>
                    @endforelse
                </div>

                <!-- Totals -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Sous-total</span>
                        <span>{{ number_format($subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Taxes</span>
                        <span>{{ number_format($taxAmount, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Remise</span>
                        <div class="flex items-center gap-1">
                            <input wire:change="updatedDiscount" wire:model="discount" type="number" min="0" class="w-20 text-right text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-2 py-1" placeholder="0">
                            <span class="text-gray-400 text-xs">F</span>
                        </div>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>Total</span>
                        <span>{{ number_format($total, 0, ',', ' ') }} F</span>
                    </div>

                    <button wire:click="openPayment" class="w-full py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors mt-2" {{ empty($this->cart) ? 'disabled' : '' }}>
                        Payer ({{ number_format($total, 0, ',', ' ') }} F)
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        @if($showPaymentModal)
            <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:key="payment-modal">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6" @click.away="closePayment">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Finaliser la vente</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mode de paiement</label>
                            <select wire:model="paymentMethod" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2.5">
                                <option value="cash">Espèces</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="card">Carte bancaire</option>
                                <option value="transfer">Virement</option>
                                <option value="check">Chèque</option>
                                <option value="credit">Crédit client</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total à payer</label>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($total, 0, ',', ' ') }} F</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant reçu</label>
                            <input wire:model.live="paidAmount" type="number" step="1" class="w-full text-2xl font-bold border-2 border-indigo-300 dark:border-indigo-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                            @error('paidAmount') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        @if($changeAmount > 0)
                            <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                <p class="text-sm text-green-700 dark:text-green-300">Monnaie à rendre</p>
                                <p class="text-xl font-bold text-green-600">{{ number_format($changeAmount, 0, ',', ' ') }} F</p>
                            </div>
                        @endif

                        @if((float) $paidAmount < $total && $paidAmount > 0)
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-lg">
                                <p class="text-sm text-amber-700 dark:text-amber-300">Reste à payer : {{ number_format($total - (float) $paidAmount, 0, ',', ' ') }} F</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea wire:model="notes" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2" placeholder="Optionnel"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="closePayment" class="flex-1 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Annuler
                        </button>
                        <button wire:click="confirmSale" class="flex-1 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors" {{ (float) $paidAmount < $total ? 'disabled' : '' }}>
                            Confirmer la vente
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
