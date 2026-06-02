{{-- resources/views/components/modal-tindak-lanjut.blade.php --}}

@php
    $statusValues = ['proses', 'diteruskan', 'ditolak', 'selesai'];

    $statusInfo = [
        'proses' => [
            'label' => 'Diproses',
            'icon' => '⏳',
            'color' => 'blue'
        ],
        'diteruskan' => [
            'label' => 'Diteruskan',
            'icon' => '📤',
            'color' => 'amber'
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'icon' => '❌',
            'color' => 'red'
        ],
        'selesai' => [
            'label' => 'Selesai',
            'icon' => '✅',
            'color' => 'emerald'
        ]
    ];
@endphp

<div
    id="tl-overlay"
    class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    onclick="if(event.target===this) closeModalTL()"
>

    <div
        class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-fade-in"
    >

        {{-- HEADER --}}
        <div class="bg-gradient-to-r from-slate-800 via-slate-700 to-slate-800 px-6 py-5">

            <div class="flex items-start justify-between">

                <div>

                    <div class="text-xs uppercase tracking-widest text-slate-300 font-medium">
                        Tindak Lanjut Aduan
                    </div>

                    <h3
                        id="tl-tiket-label"
                        class="text-white text-xl font-bold mt-1"
                    >
                        #ADU-000001
                    </h3>

                    <div
                        id="tl-nama-label"
                        class="text-slate-300 text-sm mt-1"
                    >
                        Nama Pelapor
                    </div>

                </div>

                <button
                    onclick="closeModalTL()"
                    class="w-10 h-10 rounded-xl hover:bg-white/10 text-slate-300 hover:text-white transition"
                >
                    ✕
                </button>

            </div>

        </div>

        <form
            id="tl-form"
            method="POST"
            action=""
            enctype="multipart/form-data"
            class="flex flex-col"
            x-data="{ statusPilih:'proses' }"
        >
            @csrf

            <input type="hidden" name="_method" id="tl-method" value="POST">
            <input type="hidden" name="pengaduan_id" id="tl-pengaduan-id">

            <div class="p-6 space-y-5">

                {{-- STATUS SAAT INI --}}
                <div
                    class="bg-slate-50 border border-slate-200 rounded-2xl p-4"
                >
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Status Saat Ini
                    </div>

                    <div
                        id="current-status"
                        class="mt-1 font-bold text-slate-800"
                    >
                        -
                    </div>
                </div>

                {{-- STATUS BARU --}}
                <div>

                    <label
                        class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3"
                    >
                        Pilih Status Baru
                    </label>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                        @foreach($statusValues as $status)

                            <label class="cursor-pointer">

                                <input
                                    type="radio"
                                    name="status_baru"
                                    value="{{ $status }}"
                                    class="hidden"
                                    x-model="statusPilih"
                                    required
                                >

                                <div
                                    class="rounded-2xl border-2 p-4 text-center transition-all duration-200"

                                    :class="
                                        statusPilih === '{{ $status }}'
                                        ? 'border-blue-600 bg-blue-50 shadow-md scale-[1.02]'
                                        : 'border-slate-200 hover:border-slate-300'
                                    "
                                >

                                    <div class="text-3xl mb-2">
                                        {{ $statusInfo[$status]['icon'] }}
                                    </div>

                                    <div class="font-semibold text-sm text-slate-800">
                                        {{ $statusInfo[$status]['label'] }}
                                    </div>

                                </div>

                            </label>

                        @endforeach

                    </div>

                </div>

                {{-- CATATAN --}}
                <div>

                    <label
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Catatan Tindak Lanjut
                    </label>

                    <p class="text-xs text-slate-500 mb-2">
                        Jelaskan tindakan yang telah dilakukan terhadap aduan ini.
                    </p>

                    <textarea
                        id="tl-catatan"
                        name="catatan_petugas"
                        rows="5"
                        required
                        placeholder="Tuliskan progres penanganan aduan..."
                        class="w-full rounded-2xl border border-slate-200
                               px-4 py-3 text-sm
                               focus:ring-4 focus:ring-blue-100
                               focus:border-blue-500
                               resize-none"
                    ></textarea>

                </div>

                {{-- FOTO BUKTI --}}
                <div
                    x-show="statusPilih === 'selesai'"
                    x-transition
                >

                    <label
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Foto Bukti Penyelesaian
                    </label>

                    <label
                        class="block border-2 border-dashed border-slate-300
                               rounded-2xl p-6 text-center
                               cursor-pointer
                               hover:border-blue-500
                               hover:bg-blue-50/50
                               transition"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-10 h-10 mx-auto text-slate-400 mb-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>

                        <div class="font-medium text-slate-700">
                            Upload Foto Bukti
                        </div>

                        <div class="text-xs text-slate-500 mt-1">
                            JPG, PNG maksimal 5MB
                        </div>

                        <input
                            type="file"
                            name="bukti_gambar"
                            accept="image/*"
                            class="hidden"
                        >
                    </label>

                </div>

            </div>

            {{-- FOOTER --}}
            <div
                class="border-t bg-slate-50 px-6 py-4 flex gap-3"
            >

                <button
                    type="button"
                    onclick="closeModalTL()"
                    class="flex-1 py-3 rounded-xl border border-slate-300
                           bg-white text-slate-600 font-medium
                           hover:bg-slate-100 transition"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="flex-1 py-3 rounded-xl
                           bg-blue-700 hover:bg-blue-800
                           text-white font-semibold
                           shadow-lg shadow-blue-700/20
                           transition"
                >
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>

<script>

function formatStatusLabel(status) {

    const labels = {
        proses: '⏳ Sedang Diproses',
        diteruskan: '📤 Diteruskan',
        ditolak: '❌ Ditolak',
        selesai: '✅ Selesai'
    };

    return labels[status] || status;
}

function openModalTL(
    id,
    tiket,
    nama,
    status,
    catatan,
    tlId
) {

    const form = document.getElementById('tl-form');

    document.getElementById('tl-pengaduan-id').value = id;

    document.getElementById('tl-tiket-label').textContent =
        tiket;

    document.getElementById('tl-nama-label').textContent =
        nama;

    document.getElementById('tl-catatan').value =
        catatan || '';

    document.getElementById('current-status').textContent =
        formatStatusLabel(status);

    if (tlId) {

        form.action = `/tindak-lanjut/${tlId}`;

        document.getElementById('tl-method').value =
            'PATCH';

    } else {

        form.action = `/tindak-lanjut`;

        document.getElementById('tl-method').value =
            'POST';
    }

    const alpineData = form._x_dataStack?.[0];

    if (alpineData) {

        alpineData.statusPilih =
            status || 'proses';
    }

    document
        .getElementById('tl-overlay')
        .classList
        .remove('hidden');

    document.body.style.overflow = 'hidden';
}

function closeModalTL() {

    document
        .getElementById('tl-overlay')
        .classList
        .add('hidden');

    document.body.style.overflow = '';

    document
        .getElementById('tl-form')
        .reset();
}

</script>