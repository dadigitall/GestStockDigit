<div class="max-w-4xl mx-auto space-y-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="flex gap-1 p-1 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5">
        <button wire:click="switchTab('general')" class="flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ $tab === 'general' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}">
            Général
        </button>
        <button wire:click="switchTab('invoicing')" class="flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ $tab === 'invoicing' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}">
            Facturation
        </button>
        <button wire:click="switchTab('stock_finance')" class="flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ $tab === 'stock_finance' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}">
            Stock & Finances
        </button>
        <button wire:click="switchTab('features')" class="flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ $tab === 'features' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}">
            Fonctionnalités
        </button>
        <button wire:click="switchTab('hardware')" class="flex-1 px-4 py-2.5 text-sm font-medium rounded-lg transition-all {{ $tab === 'hardware' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}">
            Périphériques
        </button>
    </div>

    @if($tab === 'general')
        <form wire:submit="saveGeneral" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Logo</label>
                    <div class="flex items-center gap-4">
                        @if($company->logo)
                            <img src="{{ Storage::url($company->logo) }}" class="w-16 h-16 rounded-lg object-cover border">
                        @endif
                        <input type="file" wire:model="tempLogo" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                    @error('tempLogo') <span class="text-xs text-rose-600 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nom de l'entreprise</label>
                    <input wire:model="name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Raison sociale</label>
                    <input wire:model="legal_name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">N° fiscal / RC</label>
                    <input wire:model="tax_number" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Registre de commerce</label>
                    <input wire:model="registration_number" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Téléphone</label>
                    <input wire:model="phone" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <input wire:model="email" type="email" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Site web</label>
                    <input wire:model="website" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Adresse</label>
                    <textarea wire:model="address" rows="2" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Devise principale</label>
                    <select wire:model="currency" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="XAF">XAF - CFA (BEAC)</option>
                        <option value="XOF">XOF - CFA (BCEAO)</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="USD">USD - Dollar</option>
                        <option value="GBP">GBP - Livre</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Fuseau horaire</label>
                    <select wire:model="timezone" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="Africa/Douala">Africa/Douala (WAT)</option>
                        <option value="Africa/Yaounde">Africa/Yaounde (WAT)</option>
                        <option value="Africa/Lagos">Africa/Lagos (WAT)</option>
                        <option value="Africa/Abidjan">Africa/Abidjan (GMT)</option>
                        <option value="Europe/Paris">Europe/Paris (CET)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Format des dates</label>
                    <select wire:model="date_format" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="d/m/Y">31/12/2026</option>
                        <option value="Y-m-d">2026-12-31</option>
                        <option value="m/d/Y">12/31/2026</option>
                        <option value="d.m.Y">31.12.2026</option>
                        <option value="d F Y">31 décembre 2026</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Langue d'affichage</label>
                    <select wire:model="locale" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="fr">Français</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-white/5">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

    @elseif($tab === 'invoicing')
        <form wire:submit="saveInvoicing" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6 space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Préfixes de numérotation</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Factures</label>
                        <input wire:model="invoice_prefix" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Ventes (reçus)</label>
                        <input wire:model="sale_prefix" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Commandes fournisseurs</label>
                        <input wire:model="purchase_prefix" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Bons de livraison</label>
                        <input wire:model="delivery_prefix" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Devis</label>
                        <input wire:model="quotation_prefix" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Avoirs</label>
                        <input wire:model="credit_note_prefix" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Transferts</label>
                        <input wire:model="transfer_prefix" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm uppercase">
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-white/5 pt-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Pied de ticket de caisse</h3>
                <textarea wire:model="ticket_footer" rows="3" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" placeholder="Merci de votre visite !"></textarea>
            </div>

            <div class="border-t border-slate-200 dark:border-white/5 pt-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Pied de facture</h3>
                <textarea wire:model="invoice_footer" rows="3" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" placeholder="Banque : ... | Compte : ..."></textarea>
            </div>

            <div class="border-t border-slate-200 dark:border-white/5 pt-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Conditions générales de vente (CGV)</h3>
                <textarea wire:model="invoice_terms" rows="5" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" placeholder="Nos conditions générales de vente..."></textarea>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-white/5">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

    @elseif($tab === 'stock_finance')
        <form wire:submit="saveStockFinance" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Taux de taxe par défaut (%)</label>
                    <input wire:model="default_tax_rate" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    @error('default_tax_rate') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Remise maximale autorisée (%)</label>
                    <input wire:model="discount_max_rate" type="number" step="0.01" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    @error('discount_max_rate') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Limite de crédit client par défaut</label>
                    <input wire:model="credit_limit_default" type="number" step="1" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Seuil d'alerte stock global</label>
                    <input wire:model="alert_threshold_global" type="number" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500" placeholder="Laisser vide pour utiliser les seuils par produit">
                </div>

                <div class="flex items-center gap-3">
                    <input wire:model="enable_multi_currency" type="checkbox" id="multi_currency" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="multi_currency" class="text-sm font-medium text-slate-700 dark:text-slate-300">Activer la multi-devise</label>
                </div>

                @if($enable_multi_currency)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Devise secondaire</label>
                        <select wire:model="secondary_currency" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Sélectionner --</option>
                            <option value="XAF">XAF</option>
                            <option value="XOF">XOF</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-white/5">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

    @elseif($tab === 'features')
        <form wire:submit="saveFeatures" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6 space-y-6">
            @foreach ($featureCategories as $catKey => $catLabel)
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">{{ $catLabel }}</h3>
                    <div class="space-y-3">
                        @foreach ($features as $key => $enabled)
                            @php
                                $def = app(\App\Services\SettingsService::class)->getDefinition($key);
                            @endphp
                            @if ($def && $def['category'] === $catKey)
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                                    <div>
                                        <label for="feature_{{ $key }}" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                            {{ $def['label'] }}
                                        </label>
                                        <p class="text-xs text-slate-400">{{ $def['description'] }}</p>
                                    </div>
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input
                                            type="checkbox"
                                            id="feature_{{ $key }}"
                                            wire:model="features.{{ $key }}"
                                            class="peer sr-only"
                                        >
                                        <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                                    </label>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-white/5">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

    @elseif($tab === 'hardware')
        <form wire:submit="saveHardware" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6 space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Périphériques connectés</h3>
                <p class="text-xs text-slate-400 mb-4">Activez les périphériques matériels disponibles sur votre point de vente.</p>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <div>
                            <label for="hw_barcode_scanner" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Lecteur code-barres</label>
                            <p class="text-xs text-slate-400">Scanner les produits par code-barres au point de vente</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="hw_barcode_scanner" wire:model="hw_barcode_scanner" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <div>
                            <label for="hw_receipt_printer" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Imprimante ticket</label>
                            <p class="text-xs text-slate-400">Impression des reçus et tickets de caisse</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="hw_receipt_printer" wire:model="hw_receipt_printer" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <div>
                            <label for="hw_a4_printer" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Imprimante A4</label>
                            <p class="text-xs text-slate-400">Impression des factures, devis, rapports au format A4</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="hw_a4_printer" wire:model="hw_a4_printer" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <div>
                            <label for="hw_cash_drawer" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Tiroir-caisse</label>
                            <p class="text-xs text-slate-400">Ouverture automatique du tiroir-caisse lors des ventes</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="hw_cash_drawer" wire:model="hw_cash_drawer" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <div>
                            <label for="hw_payment_terminal" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Terminal de paiement</label>
                            <p class="text-xs text-slate-400">Intégration TPE pour paiements par carte bancaire</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="hw_payment_terminal" wire:model="hw_payment_terminal" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <div>
                            <label for="hw_electronic_scale" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Balance électronique</label>
                            <p class="text-xs text-slate-400">Pesée automatique des produits en vrac</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="hw_electronic_scale" wire:model="hw_electronic_scale" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <div>
                            <label for="hw_customer_display" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Écran client</label>
                            <p class="text-xs text-slate-400">Afficher le montant dû et les articles au client</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" id="hw_customer_display" wire:model="hw_customer_display" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-indigo-600 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-white/5">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>
    @endif
</div>
