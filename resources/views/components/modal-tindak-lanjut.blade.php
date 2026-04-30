{{-- resources/views/components/modal-tindak-lanjut.blade.php --}}
@php
    // Urutan baru: Sedang Ditindaklanjuti → Disposisi Kasi → Selesai
    $statusValues = ['diteruskan', 'proses', 'selesai'];
@endphp

<div id="tl-overlay"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    onclick="if(event.target===this) closeModalTL()">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-white text-base" id="tl-tiket-label">Tindak Lanjut</h3>
                <p class="text-blue-200 text-xs" id="tl-nama-label">—</p>
            </div>
            <button onclick="closeModalTL()" class="text-blue-200 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- ══ FORM STORE (TL baru) — status langsung diteruskan, cukup catatan ══ --}}
        <form id="tl-form-store" method="POST" action="{{ route('tindak-lanjut.store') }}"
              class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="pengaduan_id" id="tl-pengaduan-id">
            {{-- Status dikunci ke 'diteruskan' (Sedang Ditindaklanjuti) --}}
            <input type="hidden" name="status_baru" value="diteruskan">

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Catatan Tindak Lanjut <span class="text-red-500">*</span>
                </label>
                <textarea name="catatan_petugas" id="tl-catatan" rows="4"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 resize-none"
                    placeholder="Tuliskan langkah yang sudah/sedang dilakukan..."
                    required></textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModalTL()"
                    class="flex-1 py-2.5 text-sm border border-gray-200 rounded-xl
                           text-gray-500 hover:bg-gray-50 font-medium transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-700 hover:bg-blue-800
                           text-white rounded-xl font-bold transition">
                    Simpan Tindak Lanjut
                </button>
            </div>
        </form>

        {{-- ══ FORM UPDATE (Edit TL) — ada pilihan status + catatan + bukti jika selesai ══ --}}
        <form id="tl-form-update" method="POST" action=""
              enctype="multipart/form-data" class="hidden p-6 space-y-4"
              x-data="{ statusPilih: '' }">
            @csrf
            @method('PUT')

            {{-- Pilihan Status — urutan baru: Sedang Ditindaklanjuti → Disposisi Kasi → Selesai --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    Ubah Status Menjadi
                </label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($statusValues as $val)
                        @php $meta = \App\Helpers\StatusHelper::modalMeta($val); @endphp
                        <label class="status-option-update cursor-pointer">
                            <input type="radio" name="status_baru" value="{{ $val }}" class="sr-only"
                                   x-model="statusPilih">
                            <div class="status-card-update border-2 border-gray-200 rounded-xl p-3 text-center transition-all"
                                 data-active="{{ $meta['active'] }}"
                                 data-value="{{ $val }}">
                                <div class="text-xl mb-1">{{ $meta['icon'] }}</div>
                                <div class="text-xs font-bold text-gray-600 leading-tight">
                                    {{ $meta['label'] }}
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Catatan Tindak Lanjut <span class="text-red-500">*</span>
                </label>
                <textarea name="catatan_petugas" id="tl-catatan-update" rows="4"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 resize-none"
                    required></textarea>
            </div>

            {{-- Bukti foto — hanya muncul jika status = selesai --}}
            <div x-show="statusPilih === 'selesai'" x-transition>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">
                    Foto Bukti Penyelesaian <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <input type="file" name="bukti_gambar" accept="image/*"
                    class="w-full text-xs border border-dashed border-gray-300 rounded-xl p-2
                           text-gray-500 file:mr-2 file:text-xs file:border-0 file:rounded-lg
                           file:bg-slate-100 file:text-slate-600 file:px-2 file:py-1"/>
                <p class="text-[10px] text-gray-400 mt-1">Upload bukti bahwa pengaduan telah diselesaikan.</p>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModalTL()"
                    class="flex-1 py-2.5 text-sm border border-gray-200 rounded-xl
                           text-gray-500 hover:bg-gray-50 font-medium transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-700 hover:bg-blue-800
                           text-white rounded-xl font-bold transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function openModalTL(pengaduanId, tiket, nama, statusSaat, catatanLama, tindakLanjutId) {
    document.getElementById('tl-tiket-label').textContent = 'Tindak Lanjut — ' + tiket;
    document.getElementById('tl-nama-label').textContent  = nama;

    const hasExisting = tindakLanjutId && tindakLanjutId !== 'null' && tindakLanjutId !== '';

    if (hasExisting) {
        document.getElementById('tl-form-store').classList.add('hidden');
        document.getElementById('tl-form-update').classList.remove('hidden');
        document.getElementById('tl-form-update').action = '/tindak-lanjut/' + tindakLanjutId;
        document.getElementById('tl-catatan-update').value = catatanLama || '';

        document.querySelectorAll('#tl-form-update input[type="radio"]').forEach(r => {
            r.checked = (r.value === statusSaat);
            updateStatusCardUpdate(r);
        });

        // Sync Alpine x-model supaya x-show bukti foto ikut terupdate
        const alpineEl = document.getElementById('tl-form-update');
        if (alpineEl._x_dataStack) {
            alpineEl._x_dataStack[0].statusPilih = statusSaat;
        }

    } else {
        document.getElementById('tl-form-update').classList.add('hidden');
        document.getElementById('tl-form-store').classList.remove('hidden');
        document.getElementById('tl-pengaduan-id').value = pengaduanId;
        document.getElementById('tl-catatan').value = '';
    }

    document.getElementById('tl-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModalTL() {
    document.getElementById('tl-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}

function updateStatusCardUpdate(radio) {
    const card = radio.nextElementSibling;
    if (!card) return;
    const activeClasses  = (card.dataset.active || '').split(' ').filter(Boolean);
    const defaultClasses = ['border-gray-200'];

    if (radio.checked) {
        defaultClasses.forEach(c => card.classList.remove(c));
        activeClasses.forEach(c => card.classList.add(c));
    } else {
        activeClasses.forEach(c => card.classList.remove(c));
        defaultClasses.forEach(c => card.classList.add(c));
    }
}

document.querySelectorAll('.status-option-update input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.status-option-update input[type="radio"]')
            .forEach(r => updateStatusCardUpdate(r));
    });
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModalTL(); });
</script>