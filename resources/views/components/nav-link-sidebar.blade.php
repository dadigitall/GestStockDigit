@props(['active' => false, 'href' => '#'])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 rounded-lg transition-colors'
    : 'flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50 rounded-lg transition-colors';
@endphp

<a href="{{ $href }}" {{ $attributes->wire('navigate') }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $icon ?? '' }}
    <span>{{ $slot }}</span>
</a>
