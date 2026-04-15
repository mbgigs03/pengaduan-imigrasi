{{-- resources/views/components/status-pill.blade.php --}}
{{--
    Penggunaan: <x-status-pill :status="$pengaduan->status" />
--}}
@props(['status'])

@php
    $classes = match($status) {
        'pending'    => 'bg-amber-50 text-amber-700 border border-amber-200',
        'proses'     => 'bg-blue-50 text-blue-700 border border-blue-200',
        'diteruskan' => 'bg-purple-50 text-purple-700 border border-purple-200',
        'selesai'    => 'bg-green-50 text-green-700 border border-green-200',
        default      => 'bg-gray-50 text-gray-600 border border-gray-200',
    };

    $labels = [
        'pending'    => 'Pending',
        'proses'     => 'Proses',
        'diteruskan' => 'Diteruskan',
        'selesai'    => 'Selesai',
    ];
@endphp

<span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full {{ $classes }}">
    {{ $labels[$status] ?? ucfirst($status) }}
</span>