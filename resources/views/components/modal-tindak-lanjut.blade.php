{{-- resources/views/components/modal-tindak-lanjut.blade.php --}}
@php
    $statusValues = ['proses', 'diteruskan', 'selesai'];
@endphp

<div id="tl-overlay" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) closeModalTL()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        
        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-white text-base" id="tl-tiket-label">Update Progress Aduan</h3>
                <p class="text-blue-200 text-xs" id="tl-nama-label">—</p>
            </div>
            <button onclick="closeModalTL()" class="text-blue-200 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- FORM (Selalu membuat histori baru) --}}
        <form id="tl-form" method="POST" action="{{ route('admin.pengaduan.updateStatus') }}" enctype="multipart/form-data" class="p-6 space-y-4" x-data="{ statusPilih: 'proses' }">
            @csrf
            @method('PATCH')
            <input type="hidden" name="pengaduan_id" id="tl-pengaduan-id">

            {{-- Pilihan Status --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pilih Status Baru</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($statusValues as $val)
                        @php $meta = \App\Helpers\StatusHelper::modalMeta($val); @endphp
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="{{ $val }}" class="sr-only" x-model="statusPilih" required>
                            <div class="border-2 rounded-xl p-3 text-center transition-all"
                                 :class="statusPilih === '{{ $val }}' ? '{{ $meta['active'] }} border-transparent' : 'border-gray-100 bg-gray-50 text-gray-400'">
                                <div class="text-xl mb-1">{{ $meta['icon'] }}</div>
                                <div class="text-[10px] font-bold leading-tight">{{ $meta['label'] }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Catatan/Progres Petugas <span class="text-red-500">*</span></label>
                <textarea name="keterangan" rows="4" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 resize-none" placeholder="Apa yang sedang dikerjakan?..." required></textarea>
            </div>

            {{-- Bukti foto — muncul jika status = selesai --}}
            <div x-show="statusPilih === 'selesai'" x-transition>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Foto Bukti (Opsional)</label>
                <input type="file" name="bukti_gambar" accept="image/*" class="w-full text-xs border border-dashed border-gray-300 rounded-xl p-2"/>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModalTL()" class="flex-1 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 font-medium transition">Batal</button>
                <button type="submit" class="flex-1 py-2.5 text-sm bg-blue-700 hover:bg-blue-800 text-white rounded-xl font-bold transition">Simpan Progres</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalTL(id, tiket, nama, status) {
    document.getElementById('tl-pengaduan-id').value = id;
    document.getElementById('tl-tiket-label').textContent = 'Update Progres — ' + tiket;
    document.getElementById('tl-nama-label').textContent = nama;
    
    // Set default value ke Alpine
    const alpineEl = document.getElementById('tl-form');
    if (alpineEl.__x) {
        alpineEl.__x.$data.statusPilih = status || 'proses';
    }

    document.getElementById('tl-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModalTL() {
    document.getElementById('tl-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>