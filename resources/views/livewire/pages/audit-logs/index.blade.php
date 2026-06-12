<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Journal d'audit</h1>
        <p class="mt-1 text-sm text-gray-500">
            Traçabilité complète des actions effectuées dans la plateforme.
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 p-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher (utilisateur, action, module...)"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <select wire:model.live="filterAction" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Toutes les actions</option>
                    @foreach ($actions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterModule" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Tous les modules</option>
                    @foreach ($modules as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                <input
                    type="date"
                    wire:model.live="dateFrom"
                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    title="Date début"
                >
                <input
                    type="date"
                    wire:model.live="dateTo"
                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    title="Date fin"
                >
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('created_at')" class="cursor-pointer px-4 py-3 text-left font-medium text-gray-500 hover:text-gray-700">
                            <span class="inline-flex items-center gap-1">
                                Date
                                @if ($sortField === 'created_at')
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        @if ($sortDirection === 'asc')
                                            <path d="m5 15 7-7 7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        @else
                                            <path d="m19 9-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Utilisateur</th>
                        <th wire:click="sortBy('action')" class="cursor-pointer px-4 py-3 text-left font-medium text-gray-500 hover:text-gray-700">
                            <span class="inline-flex items-center gap-1">
                                Action
                                @if ($sortField === 'action')
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        @if ($sortDirection === 'asc')
                                            <path d="m5 15 7-7 7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        @else
                                            <path d="m19 9-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('module')" class="cursor-pointer px-4 py-3 text-left font-medium text-gray-500 hover:text-gray-700">
                            <span class="inline-flex items-center gap-1">
                                Module
                                @if ($sortField === 'module')
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        @if ($sortDirection === 'asc')
                                            <path d="m5 15 7-7 7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        @else
                                            <path d="m19 9-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Détails</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                <span class="text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                {{ $log->user_name ?? 'Système' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $log->action === 'created' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $log->action === 'updated' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $log->action === 'deleted' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $log->action === 'login' || $log->action === 'logout' ? 'bg-gray-100 text-gray-700' : '' }}
                                    {{ $log->action === 'failed_login' ? 'bg-rose-100 text-rose-700' : '' }}
                                    {{ $log->action === 'price_changed' || $log->action === 'stock_adjusted' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $log->action === 'exported' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $log->action === 'validated' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $log->action === 'cancelled' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $log->action === 'restored' ? 'bg-teal-100 text-teal-700' : '' }}
                                ">
                                    {{ $actions[$log->action] ?? $log->action }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                <span class="text-xs">{{ $modules[$log->module] ?? $log->module }}</span>
                            </td>
                            <td class="max-w-xs truncate px-4 py-3 text-gray-600">
                                @if ($log->reason)
                                    <span class="text-xs italic text-gray-500">{{ $log->reason }}</span>
                                @elseif ($log->entity_type)
                                    <span class="text-xs text-gray-400">{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                Aucune entrée de journal trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
