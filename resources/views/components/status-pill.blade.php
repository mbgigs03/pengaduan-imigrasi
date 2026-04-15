@props(['status'])

@php
    $classes = [
        'ok' => 'bg-green-100 text-green-800',
        'warn' => 'bg-yellow-100 text-yellow-800',
        'over' => 'bg-red-100 text-red-800',
    ][$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-1 rounded-full text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>