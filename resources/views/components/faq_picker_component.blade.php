{{--
    PATCH: Tambahkan komponen ini ke KEDUA form pengaduan:
    - resources/views/pengaduan/create.blade.php (dashboard petugas)
    - resources/views/pengaduan/create-public.blade.php (pemohon publik)

    CARA PASANG:
    1. Tambahkan x-data="{ ..., topik: '', ...faqPicker() }" ke tag <form> (merge dengan x-data yang sudah ada)
       atau bungkus section ini dengan div x-data="faqPicker()" tersendiri
    2. Letakkan blok HTML di bawah ini SEBELUM textarea aduan
    3. Tambahkan script faqPicker() sebelum </body> atau di @push('scripts')
--}}

{{-- ── SECTION: Topik / Template Jawaban ──────────────────────── --}}
<div x-data="faqPicker()" class="flex flex-col gap-1.5">

    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
        Topik Pertanyaan / Aduan
        <span class="font-normal text-slate-400 normal-case">(opsional — pilih jika tersedia)</span>
    </label>

    {{-- Dropdown topik --}}
    <select x-model="topik"
            @change="applyTemplate()"
            class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50
                   focus:outline-none focus:border-blue-400 focus:bg-white focus:ring-2
                   focus:ring-blue-100 transition">
        <option value="">— Pilih topik (atau kosongkan untuk isi manual) —</option>
        <optgroup label="Permohonan Paspor">
            <option value="paspor_baru_dewasa">Persyaratan paspor baru (dewasa)</option>
            <option value="paspor_anak">Persyaratan paspor anak</option>
            <option value="paspor_umroh_haji">Paspor untuk umroh / haji</option>
            <option value="paspor_cpmi">Paspor untuk bekerja ke luar negeri (CPMI)</option>
        </optgroup>
        <optgroup label="Masalah Paspor">
            <option value="paspor_rusak">Penggantian paspor rusak</option>
            <option value="paspor_hilang">Penggantian paspor hilang</option>
        </optgroup>
        <optgroup label="Layanan Lain">
            <option value="pengambilan_diwakilkan">Pengambilan paspor diwakilkan</option>
            <option value="pembatalan_paspor">Pembatalan permohonan paspor</option>
            <option value="kekurangan_berkas">Kekurangan berkas / catatan petugas</option>
        </optgroup>
        <option value="lainnya">Lainnya (isi manual)</option>
    </select>

    {{-- Info badge saat template terisi --}}
    <div x-show="topik !== '' && topik !== 'lainnya'"
         class="flex items-center gap-2 text-xs text-blue-700 bg-blue-50 border border-blue-200
                rounded-lg px-3 py-2">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Template jawaban otomatis diisi. Anda tetap bisa mengedit sesuai kebutuhan.</span>
    </div>
</div>

{{--
    SCRIPT — letakkan sebelum </body> atau di @push('scripts') / <x-slot name="scripts">
    Ganti nama field textarea sesuai form Anda: #aduan
--}}
<script>
function faqPicker() {
    const templates = {
        paspor_baru_dewasa: `Persyaratan permohonan paspor baru (dewasa) dengan membawa dokumen ASLI:
            1. e-KTP
            2. Kartu Keluarga (KK)
            3. Akte Lahir / Buku Nikah / Ijazah SD-SMA (pilih salah satu; nama, tempat tanggal lahir, dan nama ayah harus sama dengan e-KTP dan KK)
            4. Paspor lama (jika memiliki)

            Pendaftaran:
            - Pemohon usia di bawah 60 tahun wajib mendaftar online melalui aplikasi M-Paspor (Playstore/Appstore)
            - Setelah mendaftar dan melakukan pembayaran, datang langsung ke kantor sesuai lokasi dan waktu yang dipilih
            - Bawa semua dokumen ASLI untuk proses foto dan wawancara

            Biaya paspor:
            - Elektronik masa berlaku 5 tahun: Rp 650.000
            - Elektronik masa berlaku 10 tahun: Rp 950.000

            Layanan:
            - Reguler: paspor jadi 3 hari kerja setelah pembayaran, foto, dan wawancara
            - Percepatan: paspor jadi 4 jam (berkas diterima sebelum 10.00 WIB), biaya tambahan Rp 1.000.000`,

                    paspor_anak: `Persyaratan permohonan paspor anak (belum memiliki e-KTP) dengan dokumen ASLI:
            1. e-KTP kedua orang tua kandung
            2. Kartu Keluarga (KK)
            3. Akta Lahir anak
            4. Buku / Surat Nikah orang tua; jika bercerai lampirkan surat perceraian
            5. Paspor kedua orang tua (jika memiliki)
            6. Paspor lama anak (jika memiliki)

            Ketentuan kehadiran orang tua:
            - Kedua orang tua wajib hadir saat proses permohonan
            - Jika salah satu tidak bisa hadir, wajib melampirkan surat kuasa bermaterai beserta alasan ketidakhadirannya
            - Jika orang tua bercerai dan memiliki hak asuh tertulis dari pengadilan, salah satu orang tua dapat mengurus tanpa kehadiran yang lain

            Pendaftaran:
            - Anak usia di atas 3 tahun wajib daftar online melalui M-Paspor
            - Datang sesuai lokasi dan waktu yang dipilih, bawa semua dokumen ASLI

            Biaya paspor anak:
            - Elektronik masa berlaku 5 tahun: Rp 650.000

            Layanan:
            - Reguler: paspor jadi 3 hari kerja setelah pembayaran, foto, dan wawancara
            - Percepatan: paspor jadi 4 jam (berkas diterima sebelum 10.00 WIB), biaya tambahan Rp 1.000.000`,

                    paspor_umroh_haji: `Persyaratan permohonan paspor untuk umroh / haji dengan dokumen ASLI:
            1. e-KTP
            2. Kartu Keluarga (KK)
            3. Akte Lahir / Buku Nikah / Ijazah SD-SMA (pilih salah satu)
            4. Paspor lama (jika memiliki)

            Catatan khusus nama satu kata:
            Pemohon yang hanya memiliki 1 kata pada nama wajib membawa dokumen tambahan:
            - Surat rekomendasi dari travel umroh / haji
            - Izin operasional travel umroh
            - BPIH bagi calon jamaah haji

            Pendaftaran:
            - Wajib daftar online melalui M-Paspor untuk pemohon usia di bawah 60 tahun
            - Datang sesuai lokasi dan waktu yang dipilih dengan membawa semua dokumen ASLI

            Biaya:
            - Elektronik masa berlaku 5 tahun: Rp 650.000
            - Elektronik masa berlaku 10 tahun: Rp 950.000

            Layanan:
            - Reguler: paspor jadi 3 hari kerja
            - Percepatan: paspor jadi 4 jam (sebelum 10.00 WIB), tambahan Rp 1.000.000`,

                    paspor_cpmi: `Persyaratan permohonan paspor untuk bekerja ke luar negeri (CPMI) dengan dokumen ASLI:
            1. e-KTP
            2. Kartu Keluarga (KK)
            3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
            4. Paspor lama (jika memiliki)

            Kebijakan paspor gratis bagi CPMI:
            Berlaku bagi CPMI yang baru pertama kali membuat paspor, datang langsung ke kantor dengan tambahan dokumen:
            - ID CPMI yang dikeluarkan oleh BP2MI, ATAU
            - Kontrak kerja yang telah ditandatangani secara sah / sertifikat kelulusan program G to G

            Pendaftaran (bagi yang tidak termasuk paspor gratis):
            - Wajib daftar online melalui M-Paspor untuk pemohon usia di bawah 60 tahun
            - Datang sesuai lokasi dan waktu yang dipilih dengan membawa semua dokumen ASLI

            Biaya:
            - Elektronik masa berlaku 5 tahun: Rp 650.000
            - Elektronik masa berlaku 10 tahun: Rp 950.000

            Layanan:
            - Reguler: paspor jadi 3 hari kerja
            - Percepatan: paspor jadi 4 jam (sebelum 10.00 WIB), tambahan Rp 1.000.000`,

                    paspor_rusak: `Prosedur penggantian paspor RUSAK:

            Datang langsung ke Kantor Imigrasi tanpa mendaftar melalui M-Paspor untuk proses BAP (Berita Acara Pemeriksaan).

            Dokumen ASLI yang harus dibawa:
            1. e-KTP
            2. Kartu Keluarga (KK)
            3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
            4. Paspor yang rusak

            Biaya:
            - Denda paspor rusak: Rp 500.000
            - Ditambah biaya jenis paspor yang dipilih:
            - Elektronik masa berlaku 5 tahun: Rp 650.000
            - Elektronik masa berlaku 10 tahun: Rp 950.000`,

                    paspor_hilang: `Prosedur penggantian paspor HILANG:

            Langkah pertama: urus Surat Keterangan Kehilangan di kantor kepolisian terdekat.

            Setelah memiliki surat keterangan kehilangan, datang langsung ke Kantor Imigrasi mulai pukul 08.00 WIB TANPA mendaftar melalui M-Paspor untuk proses BAP.

            Dokumen ASLI yang harus dibawa:
            1. e-KTP
            2. Kartu Keluarga (KK)
            3. Akta Lahir / Ijazah SD-SMA / Buku Nikah
            4. Surat Keterangan Kehilangan dari kepolisian

            Biaya:
            - Denda paspor hilang: Rp 1.000.000
            - Ditambah biaya jenis paspor yang dipilih:
            - Elektronik masa berlaku 5 tahun: Rp 650.000
            - Elektronik masa berlaku 10 tahun: Rp 950.000`,

                    pengambilan_diwakilkan: `Ketentuan pengambilan paspor yang DIWAKILKAN:

            A. Diwakilkan kepada orang yang BERBEDA Kartu Keluarga:
            1. Surat kuasa pengambilan paspor (ditandatangani pemilik paspor di atas materai Rp 10.000)
            2. Lembar pengambilan paspor dari petugas foto/wawancara
            3. Struk bukti pembayaran paspor
            4. e-KTP asli pengambil paspor
            5. Fotokopi e-KTP pemilik paspor

            B. Diwakilkan kepada keluarga dalam SATU Kartu Keluarga:
            1. Lembar pengambilan paspor dari petugas foto/wawancara
            2. Struk bukti pembayaran paspor
            3. Kartu Keluarga asli
            4. e-KTP asli pengambil paspor`,

                    pembatalan_paspor: `Permohonan pembatalan paspor.

            Mohon lengkapi data berikut agar dapat kami teruskan kepada petugas:

            - Nama Lengkap Pemohon Paspor    :
            - Alamat                          :
            - Nomor WhatsApp                  :
            - Tanggal Permohonan Paspor       :
            - Lokasi Foto & Wawancara         :
            - Alasan Pembatalan               :`,

                    kekurangan_berkas: `Perihal kekurangan berkas / catatan dari petugas.

            Mohon informasikan:
            - Nama lengkap pemohon            :
            - Tanggal kunjungan ke kantor     :
            - Jenis layanan yang diajukan     :
            - Catatan / berkas yang kurang    :

            Jika ada lembar catatan kekurangan berkas dari petugas, mohon lampirkan foto lembar tersebut pada kolom bukti di bawah.`,

                    lainnya: '',
                };

                return {
                    topik: '',
                    applyTemplate() {
                        const textarea = document.getElementById('aduan');
                        if (!textarea) return;
                        textarea.value = templates[this.topik] ?? '';
                        // Trigger event supaya Alpine/Livewire/dsb ikut update jika perlu
                        textarea.dispatchEvent(new Event('input'));
                    }
                };
}
</script>