@props(['href' => '#', 'active' => false, 'icon' => null])

@php
$baseClasses = 'flex items-center w-full py-2.5 px-4 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out mb-1';
$activeClasses = 'bg-green-50 text-green-700 font-semibold';
$inactiveClasses = 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';

$classes = $active ? $activeClasses : $inactiveClasses;
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses . ' ' . $classes]) }}>
    @if ($icon)
        <i class="{{ $icon }} mr-3"></i>
    @endif
    {{ $slot }}
</a>
