<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Detail Pengaduan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4">

            <div class="bg-white p-6 rounded-xl border border-gray-100 space-y-4">

                <div>
                    <p class="text-xs text-gray-500">Nomor Tiket</p>
                    <p class="font-mono text-sm">{{ $pengaduan->nomor_tiket }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Nama</p>
                    <p class="font-medium">{{ $pengaduan->nama }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">WhatsApp</p>
                    <p>{{ $pengaduan->whatsapp }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Kanal</p>
                    <p>{{ $pengaduan->kanal_pengaduan }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Seksi Tujuan</p>
                    <p>{{ $pengaduan->seksi_tujuan }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Status</p>
                    <x-status-pill :status="$pengaduan->status" />
                </div>

                <div>
                    <p class="text-xs text-gray-500">Tanggal Pengaduan</p>
                    <p>{{ \Carbon\Carbon::parse($pengaduan->tgl_pengaduan)->format('d M Y') }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Deadline</p>
                    <p>{{ \Carbon\Carbon::parse($pengaduan->deadline_tindak_lanjut)->format('d M Y') }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Isi Aduan</p>
                    <div class="p-3 bg-gray-50 rounded-lg text-sm">
                        {{ $pengaduan->aduan }}
                    </div>
                </div>

                @if ($pengaduan->bukti)
                    <div>
                        <p class="text-xs text-gray-500">Bukti</p>
                        <img src="{{ asset('storage/' . $pengaduan->bukti) }}"
                             class="mt-2 rounded-lg border w-64">
                    </div>
                @endif

                @if ($pengaduan->tindakLanjut)
                    <div>
                        <p class="text-xs text-gray-500">Tindak Lanjut</p>
                        <div class="p-3 bg-green-50 rounded-lg text-sm">
                            {{ $pengaduan->tindakLanjut->catatan_petugas }}
                        </div>
                    </div>
                @endif

                <div class="pt-4">
                    <a href="{{ url()->previous() }}"
                       class="text-sm text-gray-500 hover:underline">
                        ← Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>