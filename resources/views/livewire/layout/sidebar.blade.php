<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<aside class="h-full w-72 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-white/5 flex flex-col">
    <!-- Logo -->
    <div class="px-6 py-5 border-b border-slate-100 dark:border-white/5">
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 via-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l3 1.8a2 2 0 0 0 2.06 0L12 19v-5.5l-5-3-4.03 2.42Z" />
                    <path d="m7 16.5-4.74-2.85" />
                    <path d="m7 16.5 5-3" />
                    <path d="M7 16.5v5.17" />
                    <path d="M12 13.5V19l3.97 2.38a2 2 0 0 0 2.06 0l3-1.8a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L17 10.5l-5 3Z" />
                    <path d="m17 16.5-5-3" />
                    <path d="m17 16.5 4.74-2.85" />
                    <path d="M17 16.5v5.17" />
                    <path d="M7.97 4.42A2 2 0 0 0 7 6.13v4.37l5 3 5-3V6.13a2 2 0 0 0-.97-1.71l-3-1.8a2 2 0 0 0-2.06 0l-3 1.8Z" />
                    <path d="M12 8 7.26 5.15" />
                    <path d="m12 8 4.74-2.85" />
                    <path d="M12 13.5V8" />
                </svg>
            </div>
            <div>
                <div class="font-bold text-slate-900 dark:text-white text-lg leading-tight">GestStock <span class="text-indigo-500">Digit</span></div>
                <div class="text-[10px] uppercase tracking-widest text-slate-400">Pro · v2.4</div>
            </div>
        </a>
    </div>

    <!-- Organization Switcher -->
    <div class="px-4 py-4 border-b border-slate-100 dark:border-white/5">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer transition">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ auth()->user()->company?->name ?? auth()->user()->name }}</div>
                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    en ligne
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
        <x-nav-link-sidebar :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-layout-dashboard w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="7" height="9" x="3" y="3" rx="1" />
                    <rect width="7" height="5" x="14" y="3" rx="1" />
                    <rect width="7" height="9" x="14" y="12" rx="1" />
                    <rect width="7" height="5" x="3" y="16" rx="1" />
                </svg>
            </x-slot>
            Vue d'ensemble
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('pos.index')" :active="request()->routeIs('pos.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-shopping-cart w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1" />
                    <circle cx="19" cy="21" r="1" />
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                </svg>
            </x-slot>
            Ventes
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('stock.index')" :active="request()->routeIs('stock.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-package w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" />
                    <path d="M12 22V12" />
                    <polyline points="3.29 7 12 12 20.71 7" />
                    <path d="m7.5 4.27 9 5.15" />
                </svg>
            </x-slot>
            Inventaire
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('magasins.index')" :active="request()->routeIs('magasins.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-store w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
            </x-slot>
            Magasins
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('entrepots.index')" :active="request()->routeIs('entrepots.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-warehouse w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 21V10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v11" />
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 1.132-1.803l7.95-3.974a2 2 0 0 1 1.837 0l7.948 3.974A2 2 0 0 1 22 8z" />
                    <path d="M6 13h12" />
                    <path d="M6 17h12" />
                </svg>
            </x-slot>
            Entrepôts
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-truck w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                    <path d="M15 18H9" />
                    <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
                    <circle cx="17" cy="18" r="2" />
                    <circle cx="7" cy="18" r="2" />
                </svg>
            </x-slot>
            Fournisseurs
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('customers.index')" :active="request()->routeIs('customers.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-users w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <path d="M16 3.128a4 4 0 0 1 0 7.744" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <circle cx="9" cy="7" r="4" />
                </svg>
            </x-slot>
            Clients
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-file-text w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z" />
                    <path d="M14 2v5a1 1 0 0 0 1 1h5" />
                    <path d="M10 9H8" />
                    <path d="M16 13H8" />
                    <path d="M16 17H8" />
                </svg>
            </x-slot>
            Facturation
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('reports.index')" :active="request()->routeIs('reports.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-chart-column w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v16a2 2 0 0 0 2 2h16" />
                    <path d="M18 17V9" />
                    <path d="M13 17V5" />
                    <path d="M8 17v-3" />
                </svg>
            </x-slot>
            Rapports
        </x-nav-link-sidebar>

        <div class="text-[10px] uppercase tracking-widest text-slate-400 px-3 mb-2 mt-6 font-semibold">Système</div>

        @can('manage companies')
            <x-nav-link-sidebar :href="route('companies.index')" :active="request()->routeIs('companies.*')" wire:navigate>
                <x-slot:icon>
                    <svg class="lucide lucide-building2 w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" />
                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" />
                        <path d="M10 6h4" />
                        <path d="M10 10h4" />
                        <path d="M10 14h4" />
                        <path d="M10 18h4" />
                    </svg>
                </x-slot>
                Entreprises
            </x-nav-link-sidebar>
        @endcan

        <x-nav-link-sidebar :href="route('settings.index')" :active="request()->routeIs('settings.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-settings w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </x-slot>
            Paramètres
        </x-nav-link-sidebar>

        <x-nav-link-sidebar :href="route('support.index')" :active="request()->routeIs('support.*')" wire:navigate>
            <x-slot:icon>
                <svg class="lucide lucide-life-buoy w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m4.93 4.93 4.24 4.24" />
                    <path d="m14.83 9.17 4.24-4.24" />
                    <path d="m14.83 14.83 4.24 4.24" />
                    <path d="m9.17 14.83-4.24 4.24" />
                    <circle cx="12" cy="12" r="4" />
                </svg>
            </x-slot>
            Support
        </x-nav-link-sidebar>
    </nav>

    <!-- Bottom: Upgrade + Logout -->
    <div class="p-4 border-t border-slate-100 dark:border-white/5">
        <div class="relative overflow-hidden p-4 rounded-2xl bg-gradient-to-br from-violet-600 via-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-500/30">
            <div class="absolute -right-6 -top-6 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative">
                <div class="text-xs uppercase tracking-widest opacity-80">Plan Pro</div>
                <div class="text-sm font-bold mt-0.5 mb-2">Débloquez l'IA prédictive</div>
                <button class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white text-indigo-700 hover:bg-white/90 transition">Upgrader →</button>
            </div>
        </div>

        <button wire:click="logout" class="w-full flex items-center gap-3 px-3 py-2.5 mt-3 rounded-xl text-sm font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition">
            <svg class="lucide lucide-log-out w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m16 17 5-5-5-5" />
                <path d="M21 12H9" />
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            </svg>
            Déconnexion
        </button>
    </div>
</aside>
