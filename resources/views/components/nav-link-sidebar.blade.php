@props(['active' => false, 'href' => '#'])

@php
$classes = ($active ?? false)
    ? 'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl mb-1 text-sm font-medium transition-all group relative bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-lg shadow-indigo-500/30'
    : 'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl mb-1 text-sm font-medium transition-all group relative text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white';
@endphp

<a href="{{ $href }}" {{ $attributes->wire('navigate') }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $icon ?? '' }}
    <span class="flex-1 text-left">{{ $slot }}</span>
</a>
