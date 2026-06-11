<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $roles->count() }} rôle(s)</p>
        <div class="flex items-center gap-3">
            @can('manage roles')
                <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                    Nouveau rôle
                </button>
            @endcan
        </div>
    </div>

    @if($showForm)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ $editingRole ? 'Modifier' : 'Nouveau' }} rôle</h3>
            <form wire:submit="save" class="space-y-4">
                <div class="max-w-md">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom du rôle *</label>
                    <input wire:model="name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Permissions</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($grouped as $group => $perms)
                            <div class="border border-slate-200 dark:border-white/5 rounded-lg p-3">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">{{ $group }}</h4>
                                <div class="space-y-1">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5 rounded px-1 py-0.5">
                                            <input type="checkbox" value="{{ $perm->name }}" wire:model="rolePermissions"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-slate-700 dark:text-slate-300">{{ $perm->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-slate-300 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50">Annuler</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($roles as $role)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br
                            {{ $role->name === 'Super Admin' ? 'from-violet-500 to-purple-600' : '' }}
                            {{ $role->name === 'Admin' ? 'from-indigo-500 to-blue-600' : '' }}
                            {{ !in_array($role->name, ['Super Admin', 'Admin']) ? 'from-slate-500 to-slate-600' : '' }}
                            flex items-center justify-center text-white text-sm font-bold">
                            {{ substr($role->name, 0, 2) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $role->name }}</h4>
                            <p class="text-xs text-slate-400">{{ $role->permissions->count() }} permission(s)</p>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        @can('manage roles')
                            <button wire:click="edit({{ $role->id }})" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400 hover:text-indigo-600 transition">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if($role->name !== 'Super Admin')
                                <button wire:click="delete({{ $role->id }})" wire:confirm="Supprimer le rôle {{ $role->name }} ?" class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-500/10 text-slate-400 hover:text-rose-600 transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>
                <div class="flex flex-wrap gap-1">
                    @foreach($role->permissions->take(8) as $perm)
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/5 text-slate-500">{{ $perm->name }}</span>
                    @endforeach
                    @if($role->permissions->count() > 8)
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/5 text-slate-400">+{{ $role->permissions->count() - 8 }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 text-center py-16 text-slate-400">
                <p class="text-base font-medium">Aucun rôle</p>
            </div>
        @endforelse
    </div>
</div>
