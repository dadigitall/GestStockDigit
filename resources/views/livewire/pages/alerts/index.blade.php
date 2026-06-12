<div>
    <!-- Actions bar -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-bold text-gray-900 dark:text-white">{{ $unreadCount }}</span> non lue(s)
                </span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="text-sm text-gray-500"><span class="font-bold">{{ $alerts->total() }}</span> totale(s)</span>
            </div>

            <div class="flex gap-2 ml-auto">
                <select wire:model.live="severity" class="form-select rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                    <option value="">Toutes priorités</option>
                    <option value="danger">Critique</option>
                    <option value="warning">Important</option>
                    <option value="info">Information</option>
                </select>

                <select wire:model.live="filter" class="form-select rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                    <option value="all">Toutes</option>
                    <option value="unread">Non lues</option>
                </select>

                <button wire:click="markAllAsRead" class="px-3 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors border border-transparent">
                    Tout marquer lu
                </button>
            </div>
        </div>
    </div>

    <!-- Type filters -->
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('typeFilter', null)" class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors {{ is_null($typeFilter) ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 hover:bg-gray-200' }}">
            Tous ({{ $alerts->total() }})
        </button>
        @foreach($typeCounts as $type => $count)
            <button wire:click="$set('typeFilter', '{{ $type }}')" class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors {{ $typeFilter === $type ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 hover:bg-gray-200' }}">
                {{ \App\Models\Alert::typeLabel($type) }} ({{ $count }})
            </button>
        @endforeach
    </div>

    <!-- Alerts list -->
    <div class="space-y-3">
        @forelse($alerts as $alert)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 transition-colors {{ is_null($alert->read_at) ? 'border-l-4' : 'opacity-80' }}"
                style="{{ is_null($alert->read_at) ? 'border-left-color: ' . ($alert->severity === 'danger' ? '#dc2626' : ($alert->severity === 'warning' ? '#d97706' : '#3b82f6')) : '' }}">

                <div class="flex items-start gap-4">
                    <!-- Severity icon -->
                    <div class="shrink-0 mt-0.5">
                        @if($alert->severity === 'danger')
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        @elseif($alert->severity === 'warning')
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full
                                {{ $alert->severity === 'danger' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : ($alert->severity === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300') }}">
                                {{ \App\Models\Alert::typeLabel($alert->type) }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</span>
                            @if(is_null($alert->read_at))
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            @endif
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $alert->title }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $alert->message }}</p>

                        <!-- Action buttons -->
                        <div class="flex items-center gap-3 mt-3">
                            @if($alert->action_url)
                                <a href="{{ $alert->action_url }}" wire:navigate class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Voir les détails →
                                </a>
                            @endif
                            @if(is_null($alert->read_at))
                                <button wire:click="markAsRead({{ $alert->id }})" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                    Marquer comme lu
                                </button>
                            @endif
                            <button wire:click="delete({{ $alert->id }})" wire:confirm="Supprimer cette alerte ?" class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors ml-auto">
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Aucune alerte</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Toutes les alertes apparaîtront ici.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $alerts->links() }}
    </div>
</div>
