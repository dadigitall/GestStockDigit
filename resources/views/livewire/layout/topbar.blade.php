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

<header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 lg:fixed lg:top-0 lg:right-0 lg:left-64 z-20">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6">
        <!-- Mobile menu button -->
        <button x-data @click="$store.sidebar.toggle()" class="lg:hidden p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Search -->
        <div class="flex-1 max-w-lg mx-4">
            <div class="relative">
                <input type="text" placeholder="Rechercher..." class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Right actions -->
        <div class="flex items-center gap-4">
            <!-- Notifications -->
            <button class="relative p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User dropdown -->
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-300">{{ substr(auth()->user()->name, 0, 2) }}</span>
                        </div>
                        <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <x-dropdown-link :href="route('profile')" wire:navigate>
                        {{ __('Profil') }}
                    </x-dropdown-link>
                    <button wire:click="logout" class="w-full text-start">
                        <x-dropdown-link>
                            {{ __('Déconnexion') }}
                        </x-dropdown-link>
                    </button>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>

<!-- Spacer for fixed header -->
<div class="h-16 hidden lg:block"></div>
