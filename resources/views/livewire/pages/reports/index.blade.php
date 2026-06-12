<div>
    <!-- Filters bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Période</label>
                <select wire:model.live="period" class="form-select rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                    <option value="today">Aujourd'hui</option>
                    <option value="yesterday">Hier</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                    <option value="quarter">Ce trimestre</option>
                    <option value="year">Cette année</option>
                    <option value="custom">Personnalisée</option>
                </select>
            </div>
            @if($period === 'custom')
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Du</label>
                    <input type="date" wire:model.live="dateFrom" class="form-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Au</label>
                    <input type="date" wire:model.live="dateTo" class="form-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                </div>
            @endif
            @if(count($stores) > 0)
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Magasin</label>
                    <select wire:model.live="storeId" class="form-select rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="">Tous</option>
                        @foreach($stores as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if($tab === 'sales' && count($users) > 0)
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Vendeur</label>
                    <select wire:model.live="userId" class="form-select rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                        <option value="">Tous</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex gap-2 ml-auto">
                <button wire:click="exportCsv('{{ $tab }}')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    CSV
                </button>
                <button wire:click="exportExcel('{{ $tab }}')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </button>
                <button wire:click="exportPdf('{{ $tab }}')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF
                </button>
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex gap-6 -mb-px">
            <button wire:click="changeTab('sales')" class="pb-3 text-sm font-medium border-b-2 transition-colors
                {{ $tab === 'sales' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Ventes
            </button>
            <button wire:click="changeTab('stock')" class="pb-3 text-sm font-medium border-b-2 transition-colors
                {{ $tab === 'stock' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Stock
            </button>
            <button wire:click="changeTab('purchases')" class="pb-3 text-sm font-medium border-b-2 transition-colors
                {{ $tab === 'purchases' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Achats
            </button>
            <button wire:click="changeTab('financial')" class="pb-3 text-sm font-medium border-b-2 transition-colors
                {{ $tab === 'financial' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Financier
            </button>
            <button wire:click="changeTab('analysis')" class="pb-3 text-sm font-medium border-b-2 transition-colors
                {{ $tab === 'analysis' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Analyses
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    @if($tab === 'sales')
        @include('livewire.pages.reports.tabs.sales')
    @elseif($tab === 'stock')
        @include('livewire.pages.reports.tabs.stock')
    @elseif($tab === 'purchases')
        @include('livewire.pages.reports.tabs.purchases')
    @elseif($tab === 'financial')
        @include('livewire.pages.reports.tabs.financial')
    @elseif($tab === 'analysis')
        @include('livewire.pages.reports.tabs.analysis')
    @endif
</div>
