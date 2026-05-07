<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;
use Dompdf\Options;

class PengaduanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengaduan::query();
        $kanalList = Pengaduan::distinct()->pluck('kanal_pengaduan')->filter()->toArray();
        $user = Auth::user();

        // 1. FILTER OTOMATIS BERDASARKAN ROLE ADMIN (Scope Keamanan)
        // Ini harus di atas agar data yang tidak berhak tidak pernah "terpanggil"
        if ($user->role === 'admin') {
            if ($user->seksi === 'Doklanintalkim') {
                $query->whereIn('seksi_tujuan', ['Doklanintalkim', 'Doklan_Paspor', 'Doklan_Izin']);
            } elseif ($user->seksi === 'Inteldakim') {
                $query->whereIn('seksi_tujuan', ['Inteldakim', 'Intel_WNA', 'Intel_BAP']);
            } else {
                $query->where('seksi_tujuan', $user->seksi);
            }
        }

        // 2. FILTER BERDASARKAN STATUS
        if ($request->has('status') && in_array($request->status, ['pending', 'proses', 'diteruskan', 'selesai'])) {
            $query->where('status', $request->status);
        }

        // 3. FILTER MANUAL DARI DROPDOWN UI (Jika ada)
        if ($request->filled('seksi')) {
            $query->where('seksi_tujuan', $request->seksi);
        }

        // 4. FILTER KEYWORD SEARCH
        if ($request->filled('keyword')) {
            $kw = trim($request->keyword);
            $isTicket = preg_match('/^IMI-\d{8}-\d+$/i', $kw);
            $query->where(function ($q) use ($kw, $isTicket) {
                if ($isTicket) {
                    $q->where('nomor_tiket', strtoupper($kw));
                } else {
                    $q->where('nama', 'like', "%$kw%")
                    ->orWhere('nomor_tiket', 'like', "%$kw%");
                }
            });
        }

        // 5. EKSEKUSI PENGAMBILAN DATA (Paling Akhir)
        $pengaduans = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('pengaduan.index', compact('pengaduans', 'kanalList'));
    }
    // ═══════════════════════════════════════════════════════════
    // STORE
    // ═══════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        // ── 1. Validasi Input ──────────────────────────
        $nikRule = $request->input('jenis_layanan') === 'penanganan'
            ? 'nullable|numeric|digits:16'
            : 'required|numeric|digits:16';

        $request->validate([
            'nama'         => 'required|string|max:255',
            'nik'          => $nikRule,               
            'whatsapp'     => 'required|string',
            'seksi_tujuan' => 'required|string',
            'kanal'        => 'required|string',
            'aduan'        => 'required|string',
            'bukti'        => 'nullable|array|max:5',
            'bukti.*'      => 'nullable|image|mimes:jpg,png,jpeg,webp|max:10240',
            'foto_ktp'     => 'nullable|image|mimes:jpg,png,jpeg,webp|max:10240',
            'faq_terjawab' => 'nullable|in:0,1',
            'topik_faq'    => 'nullable|string|max:100',
        ], [
            'nik.required' => 'NIK wajib diisi untuk layanan pemberian informasi.',
            'nik.digits'   => 'NIK harus terdiri dari 16 digit angka.',
            'nik.numeric'  => 'NIK hanya boleh berisi angka.',
        ]);

        // ── 2. Tangkap Flag FAQ ──────────────────────────
        $isFaqTerjawab = $request->input('faq_terjawab') === '1';

        // ── 3. Amankan Topik FAQ ke dalam teks Aduan ──
        $teksAduan = $request->aduan;
        if ($request->filled('topik_faq') && $request->topik_faq !== 'lainnya') {
            $topikBersih = ucwords(str_replace('_', ' ', $request->topik_faq));
            $teksAduan = "[Kategori FAQ: {$topikBersih}]\n\n" . $teksAduan;
        }

        // ── 4. Simpan ke Database ──────────────────────────
        $pengaduan                         = new Pengaduan();
        $pengaduan->nama                   = $request->nama;
        $pengaduan->nik                    = $request->nik ?? '-';   
        $pengaduan->alamat                 = $request->alamat ?? '-';
        $pengaduan->whatsapp               = $request->whatsapp;
        $pengaduan->jenis_layanan          = $request->jenis_layanan ?? 'informasi';
        $pengaduan->seksi_tujuan           = $request->seksi_tujuan;
        $pengaduan->kanal_pengaduan        = $request->kanal;
        $pengaduan->aduan                  = $teksAduan;
        $pengaduan->tgl_pengaduan          = Carbon::now();
        
        $pengaduan->status                 = $isFaqTerjawab ? 'selesai' : 'pending';
        $pengaduan->deadline_tindak_lanjut = $isFaqTerjawab ? Carbon::now() : Carbon::now()->addDays(3);

        $pengaduan->save();

        // ── 5. Upload File (HANYA jika bukan FAQ terjawab) ──
        if (!$isFaqTerjawab) {
            if ($request->hasFile('bukti')) {
                $uploadedUrls = [];
                foreach ($request->file('bukti') as $file) {
                    $path = Storage::disk('supabase')->put("pengaduan/{$pengaduan->nomor_tiket}", $file);
                    $uploadedUrls[] = "https://crgjblwavebvnvvdnzbk.supabase.co/storage/v1/object/public/pengaduan/" . $path;
                }
                $pengaduan->bukti_files = $uploadedUrls;
                $pengaduan->bukti       = $uploadedUrls[0] ?? null;
                $pengaduan->save();
            }

            if ($request->hasFile('foto_ktp')) {
                $path = Storage::disk('supabase')->put("pengaduan/{$pengaduan->nomor_tiket}", $request->file('foto_ktp'));
                $pengaduan->foto_ktp = "https://crgjblwavebvnvvdnzbk.supabase.co/storage/v1/object/public/pengaduan/" . $path;
                $pengaduan->save();
            }
        }

        Log::info('Pengaduan saved', [
            'id'           => $pengaduan->id,
            'tiket'        => $pengaduan->nomor_tiket,
            'faq_terjawab' => $isFaqTerjawab
        ]);

        // ── 6. Generate PDF (HANYA jika bukan FAQ terjawab) ──
        if (!$isFaqTerjawab) {
            try {
                $pdfPath = $this->generateAndUploadPdf($pengaduan);
                Log::info('PDF berhasil diupload', ['tiket' => $pengaduan->nomor_tiket]);
            } catch (\Throwable $e) {
                Log::error('PDF gagal', [
                    'tiket' => $pengaduan->nomor_tiket,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // ── 7. Redirect Cerdas (Petugas vs Publik) ────────────────
        $isPetugas = Auth::check();

        if ($isFaqTerjawab) {
            $msg = 'Terima kasih! Senang bisa membantu. Apabila masih ada pertanyaan lain, jangan ragu untuk menghubungi kami kembali.';
            
            if ($isPetugas) {
                return redirect()->route('pengaduan.index')->with('success', 'Tiket berhasil dibuat dan otomatis ditandai Selesai.');
            }
            return redirect()->route('pengaduan.landing')->with(['success' => $msg]);
        }

        $msg = 'Pengaduan berhasil diajukan!';
        
        if ($isPetugas) {
            return redirect()->route('pengaduan.index')->with([
                'success' => $msg,
                'tiket'   => $pengaduan->nomor_tiket,
            ]);
        }

        // 🟢 GABUNGAN FRONTEND: Bawa seksi_tujuan untuk Pop-up JS temanmu
        return redirect()->route('pengaduan.landing')->with([
            'success'      => $msg,
            'tiket'        => $pengaduan->nomor_tiket,
            'seksi_tujuan' => $pengaduan->seksi_tujuan, 
        ]);
    }
    

    // ═══════════════════════════════════════════════════════════
    // GENERATE & UPLOAD PDF
    //
    // ROOT CAUSE error sebelumnya:
    //   IOFactory::load($docxTmpPath) membaca ulang .docx hasil
    //   TemplateProcessor dan menemukan karakter & dari isi aduan
    //   yang tidak di-escape → DOMDocument::loadXML() crash dengan
    //   "xmlParseEntityRef: no name in Entity"
    //
    // FIX YANG DITERAPKAN:
    //   1. IOFactory::load() DIHAPUS SEPENUHNYA dari alur.
    //   2. HTML untuk PDF dibangun langsung dari objek $pengaduan
    //      menggunakan buildPdfHtml() yang memakai htmlspecialchars().
    //   3. TemplateProcessor tetap dipakai HANYA untuk mengisi
    //      dan menyimpan .docx (arsip), tidak dibaca ulang.
    //   4. sanitizeForDocx() membersihkan karakter & sebelum masuk
    //      ke TemplateProcessor agar .docx-pun aman.
    // ═══════════════════════════════════════════════════════════
    private function generateAndUploadPdf(Pengaduan $pengaduan): string
    {
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $slug        = Str::slug($pengaduan->nomor_tiket) . '-' . Str::random(4);
        $docxTmpPath = $tmpDir . DIRECTORY_SEPARATOR . $slug . '.docx';
        $pdfTmpPath  = $tmpDir . DIRECTORY_SEPARATOR . $slug . '.pdf';

        try {
            // ─── 1. Siapkan nilai placeholder ─────────────────
            $values = [
                'tgl_pengaduan' => Carbon::parse($pengaduan->tgl_pengaduan)
                                         ->translatedFormat('d F Y'),
                'nama'          => $this->sanitizeForDocx($pengaduan->nama),
                'jenis_kelamin' => '-',
                'alamat'        => $this->sanitizeForDocx($pengaduan->alamat ?? '-'),
                'no_wa'         => $this->sanitizeForDocx($pengaduan->whatsapp),
                'isi_aduan'     => $this->sanitizeForDocx($pengaduan->aduan),
                'seksi'         => $this->formatSeksi($pengaduan->seksi_tujuan),
                'tindak_lanjut' => Carbon::now()->translatedFormat('d F Y'),
                'nomor_tiket'   => $pengaduan->nomor_tiket,
            ];

            // ─── 2. Isi template .docx → simpan sebagai arsip ─
            $templatePath = storage_path('app/templates/LAPORAN_PENGADUAN.docx');
            if (!file_exists($templatePath)) {
                throw new \RuntimeException("Template tidak ditemukan di: " . $templatePath);
            }

            // dd(file_exists($templatePath));

            $processor = new TemplateProcessor($templatePath);
            foreach ($values as $key => $val) {
                $processor->setValue($key, $val);
            }
            $processor->saveAs($docxTmpPath);

            // ─── 3. Build HTML langsung dari data (SKIP IOFactory::load) ─
            $htmlContent = $this->buildPdfHtml($pengaduan, $values);

            // ─── 4. Render HTML → PDF via DomPDF ──────────────
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled',      false);
            $options->set('defaultFont',          'DejaVu Sans');
            $options->set('defaultPaperSize',     'A4');
            $options->set('chroot',               $tmpDir);
            $options->set('chroot', public_path());


            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlContent, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfOutput = $dompdf->output();
            if (empty($pdfOutput)) {
                throw new \RuntimeException('DomPDF menghasilkan output kosong.');
            }

            file_put_contents($pdfTmpPath, $pdfOutput);

            // ─── 5. Upload PDF ke Supabase Storage ────────────
            $pdfStoragePath  = "pengaduan/{$pengaduan->nomor_tiket}/laporan-pengaduan.pdf";
            $docxStoragePath = "pengaduan/{$pengaduan->nomor_tiket}/laporan-pengaduan.docx";

            Storage::disk('supabase')->put(
                $pdfStoragePath,
                file_get_contents($pdfTmpPath),
                ['visibility' => 'public', 'ContentType' => 'application/pdf']
            );

            Storage::disk('supabase')->put(
                $docxStoragePath,
                file_get_contents($docxTmpPath),
                [
                    'visibility'  => 'public',
                    'ContentType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ]
            );

            // ─── 6. Simpan URL publik ke kolom pdf_url ─────────
            $publicUrl = rtrim(env('SUPABASE_URL'), '/') . '/' . $pdfStoragePath;
            $pengaduan->update(['pdf_url' => $publicUrl]);

            return $pdfStoragePath;

        } finally {
            // Hapus tmp SELALU — sukses maupun gagal di tengah jalan
            foreach ([$docxTmpPath, $pdfTmpPath] as $f) {
                if (file_exists($f)) {
                    @unlink($f);
                }
            }
        }
    }

    private function buildPdfHtml(Pengaduan $pengaduan, array $values): string
    {
        
        $e = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');


        $tgl    = $e($values['tgl_pengaduan']);
        $nama   = $e($values['nama']);
        $alamat = $e($values['alamat']);
        $noWa   = $e($values['no_wa']);
        $aduan  = nl2br($e($values['isi_aduan'])); // nl2br menjaga enter tetap ada
        $seksi  = $e($values['seksi']);
        $tiket  = $e($values['nomor_tiket']);
        $tindak = $e($values['tindak_lanjut']);
        // Logika untuk membedakan Kepala Seksi dan Kepala Subag
        $labelJabatan = "Kepala Seksi";
        if (str_contains(strtolower($seksi), 'tata usaha')) {
            $labelJabatan = "Kepala Sub Bagian";
        }
        
        $logoPath = public_path('images/logo-imigrasi.png');

        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $logoHtml = '<img src="data:image/png;base64,' . $logoBase64 . '" alt="Logo Imigrasi">';

        // Di dalam method buildPdfHtml...

        $jenis = $pengaduan->seksi_tujuan; // Ambil raw value dari DB

        $mappingSasaran = [
            'Tikkim'        => 'Pelayanan Paspor ',
            'Doklan_Paspor' => 'Dokumen Perjalanan ',
            'Doklan_Izin'   => 'Pelayanan Izin Tinggal [WNA] ',
            'Intel_WNA'     => 'Pengawasan Orang Asing [WNA] ',
            'Intel_BAP'     => 'Alur BAP ',
            'Tata Usaha'    => 'Sarana Prasarana ',
        ];

        $sasaranTeks = $mappingSasaran[$jenis] ?? $jenis;

        Log::info('Logo exists?', [
            'path' => $logoPath,
            'exists' => file_exists($logoPath),
            'readable' => is_readable($logoPath)
        ]);

        return <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head>
        <meta charset="UTF-8">
        <style>
        @page { margin: 20mm 25mm; size: A4 portrait; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt; /* Standar surat dinas biasanya 11pt - 12pt */
            color: #000;
            line-height: 1.2;
        }
        .b{
            font-weight: bold;
            font-size: 12pt;
        }

        .kop {
            display: table;
            width: 100%;
            border-bottom: 3px double #000;
            margin-bottom: 12px;
            padding-bottom: 6px;
        }

        .kop-logo {
            display: table-cell;
            width: 90px;
            vertical-align: middle;
            text-align: center;
        }

        .kop-logo img {
            max-width: 90px;
            max-height: 90px;
        }

        .kop-teks {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .kop-teks .instansi {font-size: 10pt; margin-bottom: 2px; }
        .kop-teks .alamat { font-size: 9pt; }

        .judul {
            text-align: center;
            margin: 15px 0;
        }
        .judul h1 { font-size: 14pt; margin: 0; text-decoration: underline; }
        .judul h2 { font-size: 12pt; margin: 0; }

        .dt { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .dt td { padding: 5px; vertical-align: top; }
        .lbl { width: 35%; }
        .sep { width: 10px; }

        .kotak-aduan {
            border: 1px solid #000;
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            box-sizing: border-box;
            min-height: 120px; /* lebih realistis */
        }

        .ttd { margin-top: 40px; }
        .ttd-table { width: 100%; }
        .ttd-table td { width: 50%; text-align: center; }

        .garis-nama {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }
        </style>
        </head>

        <body>

        <div class="kop">
            <div class="kop-logo">
                {$logoHtml}
            </div>
            <div class="kop-teks">
                <div class="instansi">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN REPUBLIK INDONESIA</div>
                <div class="instansi">DIREKTORAT JENDERAL IMIGRASI</div>
                <div class="instansi">KANTOR WILAYAH JAWA TIMUR</div>
                <div class="b">KANTOR IMIGRASI KELAS II NON TPI MADIUN</div>
                <div class="alamat">Jl. Panglima Sudirman, Mejayan, Kab. Madiun, Jawa Timur</div>
                <div class="alamat">Laman : madiun.imigrasi.go.id, Pos-el : kanim_madiun@imigrasi.go.id</div>
            </div>
        </div>

        <div class="judul">
            <h3>FORMULIR PENGADUAN <br>LAYANAN KEIMIGRASIAN</h3>
        </div>

        <div style="margin-bottom: 15px;">
            Yth. Kepala Kantor Imigrasi Kelas II Non TPI Madiun<br>
            Di Tempat
        </div>

        <table class="dt">
            <tr><td class="lbl">Nomor Pengaduan</td><td class="sep">:</td><td>{$tiket}</td></tr>
            <tr><td class="lbl">Tanggal Pengaduan</td><td class="sep">:</td><td>{$tgl}</td></tr>
            <tr><td class="lbl">Nama Lengkap Pelapor</td><td class="sep">:</td><td>{$nama}</td></tr>
            <tr><td class="lbl">Alamat</td><td class="sep">:</td><td>{$alamat}</td></tr>
            <tr><td class="lbl">Nomor WhatsApp</td><td class="sep">:</td><td>{$noWa}</td></tr>
            
            <tr><td class="lbl">Sasaran Pengaduan</td><td class="sep">:</td><td><strong>{$sasaranTeks}</strong></td></tr>
            <tr><td class="lbl">Deskripsi Pengaduan</td><td class="sep">:</td></tr>
            <tr>
                <td colspan="3">
                    <div class="kotak-aduan">
                        {$aduan}
                    </div>
                </td>
            </tr>
        </table>

        <div class="ttd">
            <table class="ttd-table">
                <tr>
                    <td></td>
                    <td>
                        Madiun, {$tgl}<br>
                        <strong>{$labelJabatan} {$seksi}</strong>
                        <div class="garis-nama" style="margin-top: 70px;">( ................................. )</div>
                    </td>
                </tr>
            </table>
        </div>

        </body>
        </html>
        HTML;
    }

    // ═══════════════════════════════════════════════════════════
    // SANITIZE FOR DOCX
    // Karakter & di dalam teks user menyebabkan XML-invalid
    // di dalam .docx → ganti & dengan 'dan', strip control chars
    // ═══════════════════════════════════════════════════════════
    private function sanitizeForDocx(string $value): string
    {
        // & langsung menjadi 'dan' — PHPWord akan escape sisanya sendiri
        $value = str_replace('&', 'dan', $value);

        // Strip karakter kontrol XML-illegal (kecuali tab, LF, CR)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Strip Unicode yang tidak valid di XML 1.0
        $value = preg_replace('/[^\x09\x0A\x0D\x20-\x{FFFD}]/u', '', $value);

        return mb_substr(trim($value), 0, 1000);
    }

    // ═══════════════════════════════════════════════════════════
    // FORMAT SEKSI
    // ═══════════════════════════════════════════════════════════
    private function formatSeksi(string $seksi): string
    {
        $mappingInduk = [
            'Tikkim'        => 'Teknologi Informasi, Intelijen dan Komunikasi Keimigrasian',
            'Doklan_Paspor' => 'Dokumen Perjalanan dan Izin Tinggal Keimigrasian',
            'Doklan_Izin'   => 'Dokumen Perjalanan dan Izin Tinggal Keimigrasian',
            'Intel_WNA'     => 'Intelijen dan Penindakan Keimigrasian',
            'Intel_BAP'     => 'Intelijen dan Penindakan Keimigrasian',
            'Tata Usaha'    => 'Sub Bagian Tata Usaha',
        ];
        
        // Jika ingin versi singkat untuk PDF:
        $mappingSingkat = [
            'Tikkim'        => 'Tikkim',
            'Doklan_Paspor' => 'Doklanintalkim',
            'Doklan_Izin'   => 'Doklanintalkim',
            'Intel_WNA'     => 'Inteldakim',
            'Intel_BAP'     => 'Inteldakim',
            'Tata Usaha'    => 'Tata Usaha',
        ];

        return $mappingSingkat[$seksi] ?? $seksi;
    }

    // ═══════════════════════════════════════════════════════════
    // DOWNLOAD PDF
    // ═══════════════════════════════════════════════════════════
    public function downloadPdf(string $nomorTiket)
    {
        $pengaduan = Pengaduan::where('nomor_tiket', $nomorTiket)->firstOrFail();

        if (!$pengaduan->pdf_url) {
            try {
                $this->generateAndUploadPdf($pengaduan);
                $pengaduan->refresh();
            } catch (\Throwable $e) {
                abort(500, 'PDF tidak tersedia: ' . $e->getMessage());
            }
        }

        return redirect($pengaduan->pdf_url);
    }

    // ─── Method lainnya ────────────────────────────────────────
    public function create()
    {
        if (Auth::check()) {
            return view('pengaduan.create');        // dashboard layout
        }
        return view('pengaduan.create-public');     // standalone publik
    }

    public function track()
    {
        if (Auth::check()) {
            return view('pengaduan.track');         // dashboard layout
        }
        return view('pengaduan.track-public');      // standalone publik
    }

    public function searchTrack(Request $request)
    {
        $request->validate(['nomor_tiket' => 'required|string']);
        $pengaduan = Pengaduan::where('nomor_tiket', $request->nomor_tiket)->first();

        if (Auth::check()) {
            return view('pengaduan.track', compact('pengaduan'));
        }
        return view('pengaduan.track-public', compact('pengaduan'));
    }
    public function updateStatus(Request $request)
    {
        // Pengaduan_id dikirim dari hidden input di modal
        $pengaduan = Pengaduan::findOrFail($request->pengaduan_id);

        $request->validate([
            'status'     => 'required|in:pending,proses,diteruskan,selesai',
            'keterangan' => 'required|string|min:5',
            'bukti_gambar' => 'nullable|image|max:2048'
        ]);

        \DB::transaction(function () use ($request, $pengaduan) {
            
            // 1. Update Tabel Utama
            $pengaduan->update([
                'status'           => $request->status,
                'keterangan_admin' => $request->keterangan,
                'updated_by'       => Auth::id(),
            ]);

            // 2. Handle Bukti Gambar (Jika ada)
            $urlGambar = null;
            if ($request->hasFile('bukti_gambar')) {
                $path = Storage::disk('supabase')->put("tanggapan/{$pengaduan->nomor_tiket}", $request->file('bukti_gambar'));
                $urlGambar = "https://your-project.supabase.co/storage/v1/object/public/pengaduan/" . $path;
            }

            // 3. Mapping Label untuk Timeline
            $statusLabels = [
                'proses'     => 'Sedang Ditindaklanjuti',
                'diteruskan' => 'Disposisi Kasi',
                'selesai'    => 'Selesai'
            ];

            // 4. Simpan ke Histori (Timeline)
            $pengaduan->tanggapans()->create([
                'user_id' => Auth::id(),
                'status'  => $statusLabels[$request->status] ?? $request->status,
                'catatan' => $request->keterangan,
                'bukti_tanggapan' => $urlGambar, // Jika Anda menambah kolom ini di migration tanggapans
            ]);
        });

        return back()->with('success', 'Progres pengaduan berhasil dicatat dalam histori.');
    }
    public function show($id)
    {
        $pengaduan = Pengaduan::with('tindakLanjut')->findOrFail($id);

        return view('pengaduan.show', compact('pengaduan'));
    }

    

     // ─────────────────────────────────────────────────────────
    // Helper: inject CSS styling ke HTML sebelum di-render DomPDF
    // ─────────────────────────────────────────────────────────
}