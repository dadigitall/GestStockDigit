<div>
    @if(session('message'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">{{ session('error') }}</div>
    @endif

    @if($detailCustomer)
        {{-- ======== DÉTAIL CLIENT / RELEVÉ DE COMPTE ======== --}}
        @include('livewire.pages.customers._detail')
    @else
        {{-- ======== STATS ======== --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Solde total clients</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ number_format(\App\Models\Customer::where('company_id', auth()->user()->company_id)->sum('balance'), 0, ',', ' ') }} F
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Clients à crédit</div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $overdueCustomers->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">En retard</div>
                <div class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $overdueCustomers->filter(fn($c) => $c->isOverdue())->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total clients</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ \App\Models\Customer::where('company_id', auth()->user()->company_id)->count() }}</div>
            </div>
        </div>

        {{-- ======== TOP BAR ======== --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un client..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500">
            </div>
            <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau client
            </button>
        </div>

        {{-- ======== FORMULAIRE ======== --}}
        @if($showForm)
            @include('livewire.pages.customers._form')
        @endif

        {{-- ======== TABLEAU PRINCIPAL ======== --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Client</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Contact</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Catégorie</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Solde</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Crédit</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Statut</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer" wire:click="showDetail({{ $customer->id }})">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $customer->name }}</div>
                                @if($customer->tax_number)
                                    <div class="text-[10px] text-gray-400">NIF: {{ $customer->tax_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ $customer->phone ?? '-' }}</div>
                                @if($customer->email)
                                    <div class="text-[10px] text-gray-400">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($customer->category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium" style="background: {{ $customer->category->color }}20; color: {{ $customer->category->color }}">
                                        {{ $customer->category->name }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-medium text-sm {{ $customer->balance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">
                                    {{ number_format($customer->balance, 0, ',', ' ') }} F
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs">
                                @if($customer->credit_limit)
                                    <span class="text-gray-400">{{ number_format($customer->credit_limit, 0, ',', ' ') }} F</span>
                                    @if($customer->isCreditBlocked())
                                        <div class="text-[10px] text-red-500 font-medium mt-0.5">Bloqué</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($customer->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">Actif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                                    <button wire:click="edit({{ $customer->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-xs">Modifier</button>
                                    @if($customer->balance > 0)
                                        <button wire:click="openPaymentForm({{ $customer->id }})" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 text-xs">Encaisser</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">Aucun client.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $customers->links() }}</div>
        </div>

        {{-- ======== SECTION RELANCES ======== --}}
        @if($pendingCreditCustomers->count() > 0)
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Clients avec soldes impayés — Relances
                    </span>
                </h3>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Client</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Solde</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Plafond</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400">Statut</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($pendingCreditCustomers as $customer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 {{ $customer->isOverdue() ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $customer->name }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-amber-600 dark:text-amber-400">{{ number_format($customer->balance, 0, ',', ' ') }} F</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $customer->credit_limit ? number_format($customer->credit_limit, 0, ',', ' ') . ' F' : '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($customer->isOverdue())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">En retard</span>
                                        @elseif($customer->isCreditBlocked())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">Bloqué</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">À suivre</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="openPaymentForm({{ $customer->id }})" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-800">Encaisser</button>
                                            <button wire:click="sendReminder({{ $customer->id }})" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">Relancer</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    {{-- ======== PAIEMENT MODAL ======== --}}
    @if($showPaymentForm)
        @include('livewire.pages.customers._payment_form')
    @endif

    {{-- ======== ÉCHÉANCIER MODAL ======== --}}
    @if($showScheduleForm)
        @include('livewire.pages.customers._schedule_form')
    @endif
</div>
