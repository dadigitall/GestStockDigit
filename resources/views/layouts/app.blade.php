<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
        dark: localStorage.getItem('dark') === 'true' || (!localStorage.getItem('dark') && window.matchMedia('(prefers-color-scheme: dark)').matches),
        sidebarOpen: false,
        init() {
            if (this.dark) document.documentElement.classList.add('dark');
            this.$watch('dark', val => {
                document.documentElement.classList.toggle('dark', val);
                localStorage.setItem('dark', val);
            });
        }
      }"
      :class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'GestStockDigit') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <script>
            if (localStorage.getItem('dark') === 'true' || (!localStorage.getItem('dark') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-slate-50 dark:bg-[#0a0e1a] text-slate-900 dark:text-slate-100 flex">

            <!-- Mobile overlay -->
            <template x-teleport="body">
                <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 lg:hidden" @@click="sidebarOpen = false"></div>
            </template>

            <!-- Sidebar wrapper -->
            <div class="fixed lg:sticky top-0 left-0 z-50 h-screen w-72 flex-shrink-0 transition-transform duration-300 -translate-x-full lg:translate-x-0"
                 :class="{ 'translate-x-0': sidebarOpen }">
                <livewire:layout.sidebar />
            </div>

            <!-- Main Content -->
            <div class="flex-1 min-w-0 flex flex-col">
                <!-- Top Navbar -->
                <livewire:layout.topbar :pageTitle="$header ?? 'Tableau de bord'" />

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
