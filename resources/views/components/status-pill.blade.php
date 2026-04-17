@props(['status'])

@php
    $classes = match($status) {
        'selesai'     => 'bg-green-100 text-green-700',
        'proses'      => 'bg-blue-100 text-blue-700',
        'pending'     => 'bg-amber-100 text-amber-700',
        'diteruskan'  => 'bg-purple-100 text-purple-700',
        default       => 'bg-gray-100 text-gray-700',
    };
@endphp

<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $classes }}">
    {{ $status }}
</span>