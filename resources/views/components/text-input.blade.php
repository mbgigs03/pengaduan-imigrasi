@props(['disabled' => false])

{{-- Update style agar lebih modern --}}
<input {{ $attributes->merge(['class' => 'border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm bg-slate-50']) }}>