<div class="space-y-6">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $companies->total() }} entreprise(s)</p>
        <button wire:click="$toggle('showCreateForm')" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            + Nouvelle entreprise
        </button>
    </div>

    <!-- Create Form -->
    @if($showCreateForm)
        <form wire:submit="createCompany" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-6 space-y-6">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Nouvelle entreprise</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom</label>
                    <input wire:model="name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Raison sociale</label>
                    <input wire:model="legal_name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Téléphone</label>
                    <input wire:model="phone" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Devise</label>
                    <select wire:model="currency" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        <option value="XAF">XAF</option>
                        <option value="XOF">XOF</option>
                        <option value="EUR">EUR</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-white/5 pt-4">
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Administrateur par défaut</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom</label>
                        <input wire:model="admin_name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        @error('admin_name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input wire:model="admin_email" type="email" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        @error('admin_email') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mot de passe</label>
                        <input wire:model="admin_password" type="password" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-sm">
                        @error('admin_password') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-white/5">
                <button type="button" wire:click="$set('showCreateForm', false)" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition">Annuler</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Créer</button>
            </div>
        </form>
    @endif

    <!-- Companies List -->
    <div class="space-y-3">
        @foreach($companies as $company)
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 overflow-hidden">
                <button wire:click="toggleDetails({{ $company->id }})" class="w-full flex items-center gap-4 px-5 py-4 hover:bg-slate-50 dark:hover:bg-white/5 transition text-left">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                        {{ substr($company->name, 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $company->name }}</p>
                        <p class="text-xs text-slate-500">{{ $company->email ?? 'Aucun email' }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span>{{ $company->stores_count }} magasin(s)</span>
                        <span>{{ $company->users_count }} utilisateur(s)</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 transition {{ $showDetails === $company->id ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                @if($showDetails === $company->id)
                    <div class="px-5 pb-4 border-t border-slate-100 dark:border-white/5 pt-3">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div><span class="text-slate-500">Téléphone</span><p class="font-medium text-slate-900 dark:text-white">{{ $company->phone ?? '—' }}</p></div>
                            <div><span class="text-slate-500">Email</span><p class="font-medium text-slate-900 dark:text-white">{{ $company->email ?? '—' }}</p></div>
                            <div><span class="text-slate-500">Devise</span><p class="font-medium text-slate-900 dark:text-white">{{ $company->currency }}</p></div>
                            <div><span class="text-slate-500">N° fiscal</span><p class="font-medium text-slate-900 dark:text-white">{{ $company->tax_number ?? '—' }}</p></div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('settings') }}?company={{ $company->id }}" wire:navigate class="text-xs font-medium text-indigo-600 hover:underline">Paramètres</a>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $companies->links() }}</div>
</div>
