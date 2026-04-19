

{{-- BACKDROP --}}
<div id="tl-overlay"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    onclick="if(event.target===this) closeModalTL()">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

        {{-- HEADER MODAL --}}
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

            {{-- FORM: STORE (new) --}}
            <form id="tl-form-store" method="POST" action="{{ route('tindak-lanjut.store') }}" enctype="multipart/form-data"
                class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="pengaduan_id" id="tl-pengaduan-id">

                {{-- Status Baru --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Ubah Status Menjadi
                    </label>
                    <div class="grid grid-cols-3 gap-2" id="tl-status-picker">
                        @foreach([
                            ['proses',     '🔄', 'Proses',    'border-blue-400 bg-blue-50 text-blue-700'],
                            ['diteruskan', '📤', 'Diteruskan','border-purple-400 bg-purple-50 text-purple-700'],
                            ['selesai',    '✅', 'Selesai',   'border-emerald-400 bg-emerald-50 text-emerald-700'],
                        ] as [$val, $icon, $label, $activeClass])
                            <label class="status-option cursor-pointer">
                                <input type="radio" name="status_baru" value="{{ $val }}" class="sr-only" required>
                                <div class="status-card border-2 border-gray-200 rounded-xl p-3 text-center transition-all"
                                    data-active="{{ $activeClass }}">
                                    <div class="text-xl mb-1">{{ $icon }}</div>
                                    <div class="text-xs font-bold text-gray-600 status-label">{{ $label }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Catatan Petugas --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Catatan Tindak Lanjut <span class="text-red-500">*</span>
                    </label>
                    <textarea name="catatan_petugas" id="tl-catatan" rows="4"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 resize-none"
                        placeholder="Tuliskan langkah yang sudah/sedang dilakukan, hasil koordinasi, atau alasan penerusan..."
                        required></textarea>
                    <p class="text-xs text-gray-400 mt-1">Catatan ini akan terlihat oleh pemohon saat mengecek status aduan.</p>
                </div>

                {{-- INPUT GAMBAR BUKTI (Tambahan Baru) --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">
                        Foto Bukti (Opsional)
                    </label>
                    <input type="file" name="bukti_gambar"
                        class="w-full text-xs border border-dashed border-gray-300 rounded-xl p-2" />
                </div>

                {{-- RIWAYAT (muncul jika sudah ada tindak lanjut sebelumnya) --}}
                <div id="tl-riwayat-wrap" class="hidden">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                        <p class="text-xs font-semibold text-amber-700 mb-1">⚠️ Tindak lanjut sebelumnya:</p>
                        <p class="text-xs text-amber-600 italic" id="tl-riwayat-text">—</p>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="flex gap-3 pt-1">
                    <button type="button" onclick="closeModalTL()"
                        class="flex-1 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 font-medium transition">
                        Batal
                    </button>
                    <button type="submit" id="tl-submit-btn"
                        class="flex-1 py-2.5 text-sm bg-blue-700 hover:bg-blue-800 text-white rounded-xl font-bold transition">
                        Simpan Tindak Lanjut
                    </button>
                </div>
            </form>

        {{-- FORM: UPDATE (existing — hidden by default, swap via JS) --}}
        <form id="tl-form-update" method="POST" action="" enctype="multipart/form-data" class="hidden p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    Ubah Status Menjadi
                </label>
                <div class="grid grid-cols-3 gap-2" id="tl-status-picker-update">
                    @foreach([
                        ['proses',     '🔄', 'Proses'],
                        ['diteruskan', '📤', 'Diteruskan'],
                        ['selesai',    '✅', 'Selesai'],
                    ] as [$val, $icon, $label])
                        <label class="status-option cursor-pointer">
                            <input type="radio" name="status_baru" value="{{ $val }}" class="sr-only">
                            <div class="status-card border-2 border-gray-200 rounded-xl p-3 text-center transition-all">
                                <div class="text-xl mb-1">{{ $icon }}</div>
                                <div class="text-xs font-bold text-gray-600">{{ $label }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Revisi Catatan <span class="text-red-500">*</span>
                </label>
                <textarea name="catatan_petugas" id="tl-catatan-update" rows="4"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400 resize-none"
                    required></textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeModalTL()"
                    class="flex-1 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 font-medium transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-700 hover:bg-blue-800 text-white rounded-xl font-bold transition">
                    Update Catatan
                </button>
            </div>
        </form>

    </div>
</div>

<script>
/**
 * openModalTL(pengaduanId, tiket, nama, statusSaat, catatanLama, tindakLanjutId)
 *
 * - tindakLanjutId: ID dari tindak_lanjuts jika sudah ada record, null jika belum
 * - catatanLama: catatan_petugas yang sudah ada sebelumnya
 */
function openModalTL(pengaduanId, tiket, nama, statusSaat, catatanLama, tindakLanjutId) {
    document.getElementById('tl-tiket-label').textContent = 'Tindak Lanjut — ' + tiket;
    document.getElementById('tl-nama-label').textContent  = nama;

    const hasExisting = tindakLanjutId && tindakLanjutId !== 'null' && tindakLanjutId !== '';

    if (hasExisting) {
        // MODE UPDATE
        document.getElementById('tl-form-store').classList.add('hidden');
        document.getElementById('tl-form-update').classList.remove('hidden');

        const updateAction = '/tindak-lanjut/' + tindakLanjutId;
        document.getElementById('tl-form-update').action = updateAction;
        document.getElementById('tl-catatan-update').value = catatanLama || '';

        // Pre-select status saat ini
        const radios = document.querySelectorAll('#tl-form-update input[type="radio"]');
        radios.forEach(r => {
            r.checked = (r.value === statusSaat);
            updateStatusCard(r);
        });
    } else {
        // MODE STORE
        document.getElementById('tl-form-update').classList.add('hidden');
        document.getElementById('tl-form-store').classList.remove('hidden');

        document.getElementById('tl-pengaduan-id').value = pengaduanId;
        document.getElementById('tl-catatan').value      = '';

        // Tampilkan riwayat jika ada catatan sebelumnya tapi belum ada record tindak lanjut
        if (catatanLama && catatanLama.trim() !== '') {
            document.getElementById('tl-riwayat-text').textContent = catatanLama;
            document.getElementById('tl-riwayat-wrap').classList.remove('hidden');
        } else {
            document.getElementById('tl-riwayat-wrap').classList.add('hidden');
        }

        // Reset semua status card
        document.querySelectorAll('#tl-form-store input[type="radio"]').forEach(r => {
            r.checked = false;
            updateStatusCard(r);
        });
    }

    document.getElementById('tl-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModalTL() {
    document.getElementById('tl-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}

// Update visual card saat radio diklik
function updateStatusCard(radio) {
    const card = radio.nextElementSibling;
    if (!card) return;
    const activeClasses = (card.dataset.active || 'border-blue-400 bg-blue-50 text-blue-700').split(' ');
    const defaultClasses = ['border-gray-200'];

    if (radio.checked) {
        defaultClasses.forEach(c => card.classList.remove(c));
        activeClasses.forEach(c => card.classList.add(c));
        card.querySelector('.status-label') && (card.querySelector('.status-label').style.color = '');
    } else {
        activeClasses.forEach(c => card.classList.remove(c));
        defaultClasses.forEach(c => card.classList.add(c));
    }
}

// Pasang event listener ke semua status-option
document.querySelectorAll('.status-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Reset semua dalam container yang sama
        const picker = this.closest('[id^="tl-status-picker"]');
        if (picker) {
            picker.querySelectorAll('input[type="radio"]').forEach(r => updateStatusCard(r));
        }
    });
});

// ESC untuk tutup modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModalTL();
});
</script>