<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $users->total() }} utilisateur(s)</p>
        <div class="flex items-center gap-3">
            <input wire:model.live="search" placeholder="Rechercher..." class="w-64 px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
            @can('manage users')
                <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                    Nouvel utilisateur
                </button>
            @endcan
        </div>
    </div>

    @if($showForm)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ $editingUser ? 'Modifier' : 'Nouvel' }} utilisateur</h3>
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom complet *</label>
                    <input wire:model="name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Prénom</label>
                    <input wire:model="first_name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom de famille</label>
                    <input wire:model="last_name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Identifiant</label>
                    <input wire:model="username" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email *</label>
                    <input wire:model="email" type="email" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                    @error('email') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Téléphone</label>
                    <input wire:model="phone" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mot de passe {{ $editingUser ? '(laisser vide pour conserver)' : '*' }}</label>
                    <input wire:model="password" type="password" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                    @error('password') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Confirmer le mot de passe</label>
                    <input wire:model="password_confirmation" type="password" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Magasin / Entité</label>
                    <select wire:model="store_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="">— Aucun —</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Statut</label>
                    <select wire:model="status" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                        <option value="suspended">Suspendu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Rôles</label>
                    <div class="space-y-1 mt-1">
                        @foreach($roles as $role)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" value="{{ $role->name }}" wire:model="selectedRoles"
                                    {{ $role->name === 'Super Admin' && !auth()->user()->hasRole('Super Admin') ? 'disabled' : '' }}
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="lg:col-span-3 flex gap-2 pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Enregistrer</button>
                    <button type="button" wire:click="cancel" class="px-4 py-2 border border-slate-300 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50">Annuler</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5 text-xs uppercase tracking-wider text-slate-500">
                        <th class="text-left px-4 py-3 font-medium">Utilisateur</th>
                        <th class="text-left px-4 py-3 font-medium">Contact</th>
                        <th class="text-left px-4 py-3 font-medium">Magasin</th>
                        <th class="text-left px-4 py-3 font-medium">Rôles</th>
                        <th class="text-left px-4 py-3 font-medium">Statut</th>
                        <th class="text-left px-4 py-3 font-medium">Dernière connexion</th>
                        <th class="text-right px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition {{ $user->status !== 'active' ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-400">
                                            @if($user->first_name || $user->last_name)
                                                {{ $user->first_name }} {{ $user->last_name }} ·
                                            @endif
                                            @if($user->username)
                                                {{ '@'.$user->username }} ·
                                            @endif
                                            Créé le {{ $user->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                <div>{{ $user->email }}</div>
                                @if($user->phone)<div class="text-xs">{{ $user->phone }}</div>@endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $user->store?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full
                                            {{ $role->name === 'Super Admin' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full
                                    {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                                    {{ $user->status === 'inactive' ? 'bg-slate-100 text-slate-500' : '' }}
                                    {{ $user->status === 'suspended' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : '' }}">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        {{ $user->status === 'active' ? 'bg-emerald-500' : '' }}
                                        {{ $user->status === 'inactive' ? 'bg-slate-400' : '' }}
                                        {{ $user->status === 'suspended' ? 'bg-rose-500' : 'bg-slate-400' }}"></span>
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-400">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @can('manage users')
                                    <button wire:click="edit({{ $user->id }})" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400 hover:text-indigo-600 transition">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <p class="text-base font-medium">Aucun utilisateur</p>
                                <p class="text-sm mt-1">Créez votre premier utilisateur.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
