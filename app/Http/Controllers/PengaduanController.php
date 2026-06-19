<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\FaqTemplate;
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
    $user = Auth::user();
    
    // 🔥 PAKAI ACCESSOR (otomatis ambil dari profile)
    $userRole = $user->role;   // 'tikkim', 'seksi', 'kakanim', 'admin', dll
    $userSeksi = $user->seksi; // nama seksi dari profile
    
    // ✅ TIKKIM dan KAKANIM bisa melihat SEMUA pengaduan (tanpa filter seksi)
    $isSuperUser = in_array($userRole, ['tikkim', 'kakanim', 'admin']);
    
    // ✅ Ambil tab aktif dari query string, default ke 'pengaduan'
    $activeTab = in_array($request->get('tab'), ['pengaduan', 'informasi'])
                 ? $request->get('tab')
                 : 'pengaduan';

    // ── Base query ─────────────────────────────────────────
    $baseQuery = Pengaduan::query();

    // ── Filter berdasarkan role (kecuali super user) ───────
    if (!$isSuperUser && in_array($userRole, ['seksi'])) {
        $seksiClean = strtolower(trim($userSeksi ?? ''));
        
        if ($seksiClean === 'doklanintalkim') {
            // Grup Doklanintalkim mencakup beberapa sub-seksi
            $baseQuery->whereIn('seksi_tujuan', [
                'Doklanintalkim', 'doklanintalkim', 
                'Doklan_Izin', 'doklan_izin',
                'Doklan_Paspor', 'doklan_paspor'
            ]);
        } elseif ($seksiClean === 'inteldakim') {
            // Grup Inteldakim mencakup beberapa sub-seksi
            $baseQuery->whereIn('seksi_tujuan', [
                'Inteldakim', 'inteldakim',
                'Intel_WNA', 'intel_wna',
                'Intel_BAP', 'intel_bap'
            ]);
        } elseif ($seksiClean === 'tatausaha' || $seksiClean === 'tata usaha') {
            // Grup Tata Usaha
            $baseQuery->whereIn('seksi_tujuan', [
                'Tata Usaha', 'tata usaha',
                'Sarana Prasarana', 'sarana prasarana'
            ]);
        } else {
            // Seksi biasa, hanya lihat pengaduan seksi sendiri
            $baseQuery->where('seksi_tujuan', $userSeksi);
        }
    }
    
    // Jika user adalah pemohon (biasanya tidak akan akses index, tapi antisipasi)
    if ($userRole === 'pemohon') {
        $baseQuery->where('nik', $user->nik ?? '0'); // filter berdasarkan NIK
    }

    // ── FILTER PERIODE ───────────────────────────────────
    $periode = $request->get('periode');

    if ($periode) {
        $now = Carbon::now();
        switch ($periode) {
            case 'daily':
                $baseQuery->whereDate('tgl_pengaduan', $now->toDateString());
                break;
            case 'weekly':
                $baseQuery->whereBetween('tgl_pengaduan', [
                    $now->copy()->subDays(7)->startOfDay(), 
                    $now->endOfDay()
                ]);
                break;
            case 'monthly':
                $baseQuery->whereMonth('tgl_pengaduan', $now->month)
                          ->whereYear('tgl_pengaduan', $now->year);
                break;
            case 'yearly':
                $baseQuery->whereYear('tgl_pengaduan', $now->year);
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $baseQuery->whereBetween('tgl_pengaduan', [
                        $request->start_date . ' 00:00:00',
                        $request->end_date . ' 23:59:59'
                    ]);
                } elseif ($request->filled('start_date')) {
                    $baseQuery->where('tgl_pengaduan', '>=', $request->start_date . ' 00:00:00');
                } elseif ($request->filled('end_date')) {
                    $baseQuery->where('tgl_pengaduan', '<=', $request->end_date . ' 23:59:59');
                }
                break;
        }
    }

    // ── Clone query untuk hitung badge (tanpa filter UI) ──
    $countPengaduan = (clone $baseQuery)->where('jenis_layanan', 'penanganan')->count();
    $countInformasi = (clone $baseQuery)->where('jenis_layanan', 'informasi')->count();

    // ── Filter berdasarkan tab aktif ──────────────────────
    $query = (clone $baseQuery)->where('jenis_layanan', 
        $activeTab === 'informasi' ? 'informasi' : 'penanganan'
    );

    // ── Filter STATUS (hanya relevan di tab pengaduan) ────
    $validStatus = ['pending', 'proses', 'diteruskan', 'ditolak', 'selesai'];
    if ($activeTab === 'pengaduan' && $request->filled('status') && in_array($request->status, $validStatus)) {
        $query->where('status', $request->status);
    }

    // ── Filter SEKSI (untuk super user) ───────────────────
    if ($isSuperUser && $request->filled('seksi')) {
        $seksiFilter = $request->seksi;
        
        if ($seksiFilter === 'Doklanintalkim') {
            $query->whereIn('seksi_tujuan', ['Doklanintalkim', 'Doklan_Paspor', 'Doklan_Izin']);
        } elseif ($seksiFilter === 'Inteldakim') {
            $query->whereIn('seksi_tujuan', ['Inteldakim', 'Intel_WNA', 'Intel_BAP']);
        } elseif ($seksiFilter === 'Tata Usaha') {
            $query->whereIn('seksi_tujuan', ['Tata Usaha', 'Sarana Prasarana']);
        } else {
            $query->where('seksi_tujuan', $seksiFilter);
        }
    }

    // ── Filter KEYWORD ────────────────────────────────────
    if ($request->filled('keyword')) {
        $kw = trim($request->keyword);
        $isTicket = preg_match('/^IMI-\d{8}-\d+$/i', $kw);
        $query->where(function ($q) use ($kw, $isTicket) {
            if ($isTicket) {
                $q->where('nomor_tiket', strtoupper($kw));
            } else {
                $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($kw) . '%'])
                  ->orWhereRaw('LOWER(nomor_tiket) LIKE ?', ['%' . strtolower($kw) . '%']);
            }
        });
    }

    // ── Eksekusi ──────────────────────────────────────────
    $pengaduans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

    return view('pengaduan.index', compact(
        'pengaduans',
        'activeTab',
        'countPengaduan',
        'countInformasi',
        'isSuperUser' // kirim ke view untuk UI
    ));
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

        // ── 6. Generate PDF (Untuk semua jenis layanan) ──
        try {
            $pdfPath = $this->generateAndUploadPdf($pengaduan);
            Log::info('PDF berhasil diupload', ['tiket' => $pengaduan->nomor_tiket]);
        } catch (\Throwable $e) {
            Log::error('PDF gagal', [
                'tiket' => $pengaduan->nomor_tiket,
                'error' => $e->getMessage()
            ]);
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

    $slug = Str::slug($pengaduan->nomor_tiket) . '-' . Str::random(4);
    $docxTmpPath = $tmpDir . DIRECTORY_SEPARATOR . $slug . '.docx';
    $pdfTmpPath = $tmpDir . DIRECTORY_SEPARATOR . $slug . '.pdf';

    try {
        Log::info('Mulai generate PDF', ['tiket' => $pengaduan->nomor_tiket]);

        // Prepare values
        $values = $this->preparePdfValues($pengaduan);

        // Generate DOCX
        $templatePath = storage_path('app/templates/LAPORAN_PENGADUAN.docx');
        if (!file_exists($templatePath)) {
            Log::error('Template DOCX tidak ditemukan', ['path' => $templatePath]);
            throw new \RuntimeException("Template tidak ditemukan di: " . $templatePath);
        }

        $processor = new TemplateProcessor($templatePath);
        foreach ($values as $key => $val) {
            $processor->setValue($key, $val);
        }
        $processor->saveAs($docxTmpPath);
        Log::info('DOCX berhasil dibuat', ['path' => $docxTmpPath]);

        // Generate HTML & PDF
        $htmlContent = $this->buildPdfHtml($pengaduan, $values);
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();
        if (empty($pdfOutput)) {
            throw new \RuntimeException('DomPDF menghasilkan output kosong.');
        }
        file_put_contents($pdfTmpPath, $pdfOutput);
        Log::info('PDF berhasil dibuat', ['path' => $pdfTmpPath, 'size' => filesize($pdfTmpPath)]);

        // Upload ke Supabase
        $pdfStoragePath = "pengaduan/{$pengaduan->nomor_tiket}/laporan-pengaduan.pdf";
        $docxStoragePath = "pengaduan/{$pengaduan->nomor_tiket}/laporan-pengaduan.docx";

        // Cek koneksi Supabase sebelum upload
        try {
            Storage::disk('supabase')->put(
                $pdfStoragePath,
                file_get_contents($pdfTmpPath),
                ['visibility' => 'public', 'ContentType' => 'application/pdf']
            );
            Log::info('PDF berhasil diupload ke Supabase', ['path' => $pdfStoragePath]);
        } catch (\Exception $e) {
            Log::error('Gagal upload PDF ke Supabase', [
                'error' => $e->getMessage(),
                'tiket' => $pengaduan->nomor_tiket
            ]);
            throw $e; 
        }

        Storage::disk('supabase')->put(
            $docxStoragePath,
            file_get_contents($docxTmpPath),
            [
                'visibility' => 'public',
                'ContentType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        );
        Log::info('DOCX berhasil diupload ke Supabase', ['path' => $docxStoragePath]);

        // ═══════════════════════════════════════════════════════════
        // FIX URL: Ekstrak hanya domain bersih dari .env 
        // ═══════════════════════════════════════════════════════════
        $rawUrl = env('SUPABASE_URL');
        $parsedUrl = parse_url($rawUrl);
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $host = $parsedUrl['host'] ?? 'crgjblwavebvnvvdnzbk.supabase.co';
        $pureDomain = $scheme . '://' . $host;
        
        // Disusun pas tanpa menduplikasi kata 'pengaduan/'
        $publicUrl = $pureDomain . '/storage/v1/object/public/pengaduan/' . $pdfStoragePath;
        
        $pengaduan->update(['pdf_url' => $publicUrl]);
        Log::info('PDF URL berhasil disimpan', ['url' => $publicUrl]);

        return $pdfStoragePath;

    } catch (\Throwable $e) {
        Log::error('Generate & Upload PDF GAGAL', [
            'tiket' => $pengaduan->nomor_tiket,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    } 
    finally {
        // Bersihkan temporary files (Selalu dieksekusi)
        foreach ([$docxTmpPath, $pdfTmpPath] as $f) {
            if (file_exists($f)) {
                @unlink($f);
            }
        }
    }
}

    private function preparePdfValues(Pengaduan $pengaduan): array
    {
        $seksiFormatted = $this->formatSeksi($pengaduan->seksi_tujuan);
        
        // Logika untuk membedakan Kepala Seksi dan Kepala Subag
        $labelJabatan = "Kepala Seksi";
        if (str_contains(strtolower($seksiFormatted), 'tata usaha')) {
            $labelJabatan = "Kepala Sub Bagian";
        }
        
        return [
            'tgl_pengaduan' => Carbon::parse($pengaduan->tgl_pengaduan)
                                    ->translatedFormat('d F Y'),
            'nama'          => $this->sanitizeForDocx($pengaduan->nama),
            'jenis_kelamin' => '-',
            'alamat'        => $this->sanitizeForDocx($pengaduan->alamat ?? '-'),
            'no_wa'         => $this->sanitizeForDocx($pengaduan->whatsapp),
            'isi_aduan'     => $this->sanitizeForDocx($pengaduan->aduan),
            'seksi'         => $seksiFormatted,
            'tindak_lanjut' => Carbon::now()->translatedFormat('d F Y'),
            'nomor_tiket'   => $pengaduan->nomor_tiket,
            'label_jabatan' => $labelJabatan,
            'sasaran_teks'  => $this->getSasaranTeks($pengaduan->seksi_tujuan)
        ];
    }

    private function getSasaranTeks(string $jenis): string
    {
        $mappingSasaran = [
            'Tikkim'        => 'Pelayanan Paspor',
            'Doklan_Paspor' => 'Dokumen Perjalanan',
            'Doklan_Izin'   => 'Pelayanan Izin Tinggal [WNA]',
            'Intel_WNA'     => 'Pengawasan Orang Asing [WNA]',
            'Intel_BAP'     => 'Alur BAP',
            'Tata Usaha'    => 'Sarana Prasarana',
        ];
        
        return $mappingSasaran[$jenis] ?? $jenis;
    }

    private function buildPdfHtml(Pengaduan $pengaduan, array $values): string
    {
        $e = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        $tgl    = $e($values['tgl_pengaduan']);
        $nama   = $e($values['nama']);
        $alamat = $e($values['alamat']);
        $noWa   = $e($values['no_wa']);
        $aduan  = nl2br($e($values['isi_aduan']));
        $seksi  = $e($values['seksi']);
        $tiket  = $e($values['nomor_tiket']);
        $sasaran = $e($values['sasaran_teks']);
        $labelJabatan = $e($values['label_jabatan']);
        
        // Path logo dengan fallback
        $logoPath = public_path('images/logo-imigrasi.png');
        if (!file_exists($logoPath)) {
            Log::warning('Logo tidak ditemukan di: ' . $logoPath);
            $logoHtml = '<div style="font-size:24px;font-weight:bold;">KANIM MADIUN</div>';
        } else {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/png;base64,' . $logoBase64 . '" alt="Logo Imigrasi">';
        }
        
        return <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head>
        <meta charset="UTF-8">
        <style>
        @page { margin: 20mm 25mm; size: A4 portrait; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.2;
        }
        .b { font-weight: bold; font-size: 12pt; }
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
        .kop-teks .instansi { font-size: 10pt; margin-bottom: 2px; }
        .kop-teks .alamat { font-size: 9pt; }
        .judul {
            text-align: center;
            margin: 15px 0;
        }
        .judul h3 { font-size: 14pt; margin: 0; text-decoration: underline; }
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
            min-height: 120px;
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
            <div class="kop-logo">{$logoHtml}</div>
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
            <tr><td class="lbl">Sasaran Pengaduan</td><td class="sep">:</td><td><strong>{$sasaran}</strong></td></tr>
            <tr><td class="lbl">Deskripsi Pengaduan</td><td class="sep">:</td></tr>
            <tr>
                <td colspan="3">
                    <div class="kotak-aduan">{$aduan}</div>
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

        // 🔥 PRIORITAS UTAMA: Cek apakah PDF sudah tersedia di Supabase
        if (!empty($pengaduan->pdf_url)) {
            // Cek apakah URL valid dan file benar-benar ada
            try {
                $headers = get_headers($pengaduan->pdf_url);
                if ($headers && strpos($headers[0], '200 OK') !== false) {
                    return redirect($pengaduan->pdf_url);
                }
            } catch (\Throwable $e) {
                Log::warning('PDF URL tidak dapat diakses, akan fallback ke generate ulang', [
                    'tiket' => $nomorTiket,
                    'url' => $pengaduan->pdf_url,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 🟡 FALLBACK OPTION: Generate PDF on-the-fly tanpa upload
        try {
            $htmlContent = $this->buildPdfHtml($pengaduan, $this->preparePdfValues($pengaduan));
            
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('defaultPaperSize', 'A4');
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($htmlContent, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Stream langsung ke browser tanpa menyimpan file
            return $dompdf->stream("LAPORAN_PENGADUAN_{$pengaduan->nomor_tiket}.pdf", [
                'Attachment' => true
            ]);
            
        } catch (\Throwable $e) {
            Log::error('Gagal generate PDF fallback', [
                'tiket' => $nomorTiket,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // 🛑 Jika semua gagal, tampilkan error yang informatif
            abort(500, 'PDF tidak dapat dihasilkan. Silakan hubungi administrator.');
        }
    }

    // ─── Method lainnya ────────────────────────────────────────
    public function create()
    {
        // Tarik data FAQ dari database
        $faqs = FaqTemplate::orderBy('topik', 'asc')->get();

        if (Auth::check()) {
            return view('pengaduan.create', compact('faqs'));        // dashboard layout
        }
        return view('pengaduan.create-public', compact('faqs'));     // standalone publik
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
        
        // ✅ Load relasi tanggapans beserta user-nya
        $pengaduan = Pengaduan::with(['tanggapans.user'])
                        ->where('nomor_tiket', trim($request->nomor_tiket))
                        ->first();

        if (Auth::check()) {
            return view('pengaduan.track', compact('pengaduan'));
        }
        return view('pengaduan.track-public', compact('pengaduan'));
    }
   public function updateStatus(Request $request)
{
    $pengaduan = Pengaduan::findOrFail($request->pengaduan_id);
    $user = Auth::user();
    
    // 🔥 PASTIKAN ROLE DIAMBIL DARI ACCESSOR
    $userRole = $user->role;
    
    // ✅ TIKKIM, KAKANIM, ADMIN LANGSUNG LOLOS
    if (in_array($userRole, ['tikkim', 'kakanim', 'admin'])) {
        // Skip pengecekan, langsung lanjut
    } 
    else {
        $userSeksi = strtolower(trim($user->seksi ?? ''));
        $seksiTujuan = strtolower(trim($pengaduan->seksi_tujuan));
        
        $grupAkses = [
            'doklanintalkim' => ['doklanintalkim', 'doklan_izin', 'doklan_paspor'],
            'inteldakim' => ['inteldakim', 'intel_wna', 'intel_bap'],
        ];
        
        $allowedSeksi = $grupAkses[$userSeksi] ?? [$userSeksi];
        
        if (!in_array($seksiTujuan, $allowedSeksi)) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah status pengaduan ini.');
        }
    }
    
    // Lanjutkan dengan proses update status...
    $request->validate([
        'status_baru' => 'required|in:pending,proses,diteruskan,ditolak,selesai',
        'catatan_petugas' => 'required|string|min:5',
        'bukti_gambar' => 'nullable|image|max:2048'
    ]);
    DB::transaction(function () use ($request, $pengaduan) {
        $statusBaru = $request->status_baru;
        $catatan = $request->catatan_petugas;

        // 1. Update Tabel Utama
        $pengaduan->update([
            'status'           => $statusBaru,
            'keterangan_admin' => $catatan,
            'updated_by'       => Auth::id(),
        ]);

        // 2. Handle Bukti Gambar (Jika ada)
        $urlGambar = null;
        if ($request->hasFile('bukti_gambar')) {
            $path = $request->file('bukti_gambar')->storeAs(
                "pengaduan/{$pengaduan->nomor_tiket}", 
                'bukti-tindak-lanjut.' . $request->file('bukti_gambar')->getClientOriginalExtension(), 
                'supabase'
            );
            $urlGambar = $path; 
        }

        // 3. Mapping Label untuk Timeline
        $statusLabels = [
            'proses'     => 'Sedang Ditindaklanjuti',
            'diteruskan' => 'Disposisi Kasi',
            'ditolak'    => 'Pengaduan Ditolak',
            'selesai'    => 'Selesai'
        ];

        // 4. Simpan ke Histori (Timeline)
        $pengaduan->tanggapans()->create([
            'user_id' => Auth::id(),
            'status'  => $statusLabels[$statusBaru] ?? $statusBaru,
            'catatan' => $catatan,
            'bukti_tanggapan' => $urlGambar,
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