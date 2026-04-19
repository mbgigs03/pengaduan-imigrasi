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
    // ═══════════════════════════════════════════════════════════
    // STORE
    // ═══════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $request->validate([
            'nama'         => 'required|string|max:255',
            'nik'          => 'required|numeric|digits:16',
            'whatsapp'     => 'required|string',
            'seksi_tujuan' => 'required|string',
            'kanal'        => 'required|string',
            'aduan'        => 'required|string',
            'bukti'        => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $pengaduan                         = new Pengaduan();
        $pengaduan->nama                   = $request->nama;
        $pengaduan->nik                    = $request->nik;
        $pengaduan->alamat                 = $request->alamat ?? '-';
        $pengaduan->whatsapp               = $request->whatsapp;
        $pengaduan->jenis_layanan          = $request->jenis_layanan ?? 'informasi';
        $pengaduan->seksi_tujuan           = $request->seksi_tujuan;
        $pengaduan->kanal_pengaduan        = $request->kanal;
        $pengaduan->aduan                  = $request->aduan;
        $pengaduan->tgl_pengaduan          = Carbon::now();
        $pengaduan->deadline_tindak_lanjut = Carbon::now()->addDays(3);
        $pengaduan->status                 = 'pending';

        if ($request->hasFile('bukti')) {
            $pengaduan->bukti = $request->file('bukti')->store('bukti-pengaduan', 'public');
        }

        $pengaduan->save();

        Log::info('Pengaduan saved', [
            'id'    => $pengaduan->id,
            'tiket' => $pengaduan->nomor_tiket,
        ]);

        // Trigger PDF — non-blocking
        try {
            $pdfPath = $this->generateAndUploadPdf($pengaduan);
            Log::info('PDF berhasil diupload', [
                'tiket' => $pengaduan->nomor_tiket,
                'path'  => $pdfPath,
            ]);
        } catch (\Throwable $e) {
            Log::error('PDF gagal', [
                'tiket' => $pengaduan->nomor_tiket,
                'error' => $e->getMessage(),
                'at'    => $e->getFile() . ':' . $e->getLine(),
            ]);
        }

        return redirect()->route('pengaduan.landing')->with([
            'success' => 'Pengaduan berhasil diajukan!',
            'tiket'   => $pengaduan->nomor_tiket,
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
                throw new \RuntimeException("Template tidak ditemukan: {$templatePath}");
            }

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
    
    $logoPath = public_path('images/logo-imigrasi.png');

    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoHtml = '<img src="data:image/png;base64,' . $logoBase64 . '" alt="Logo Imigrasi">';

    $jenis = strtolower($pengaduan->seksi_tujuan);

    // LOGIKA MAPPING: Langsung menyebutkan satu sasaran sesuai database
    if (str_contains($jenis, 'informasi')) {
        $sasaranTeks = "Pemberian Informasi (Tikkim)";
    } elseif (str_contains($jenis, 'paspor') || str_contains($jenis, 'tikkim')) {
        $sasaranTeks = "Pelayanan Paspor (Tikkim)";
    } elseif (str_contains($jenis, 'doklan')) {
        $sasaranTeks = "[WNI/WNA] Dokumen Perjalanan (Doklanintalkim)";
    } elseif (str_contains($jenis, 'inteldak')) {
        $sasaranTeks = "Pengawasan WNA & BAP (Inteldakim)";
    } elseif (str_contains($jenis, 'tata usaha') || str_contains($jenis, 'sarpras')) {
        $sasaranTeks = "Sarana Prasarana & Pegawai (Tata Usaha)";
    } else {
        $sasaranTeks = $e($pengaduan->seksi_tujuan); // Fallback ke teks asli database jika tidak cocok
    }

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
    line-height: 1.5;
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
        <div class="b">KANTOR IMIGRASI KELAS II NON TPI MADIUN</div>
        <div class="alamat">Jl. Panglima Sudirman, Mejayan, Kab. Madiun, Jawa Timur</div>
        <div class="alamat">madiun.imigrasi.go.id | kanim_madiun@imigrasi.go.id</div>
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
                <strong>Kepala Seksi {$seksi}</strong>
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
        $mapping = [
            'Tikkim' => 'Tikkim',
            'Doklanintalkim' => 'Doklanintalkim',
            'Inteldakim' => 'Inteldakim',
            'Tata Usaha' => 'Tata Usaha',
        ];
        
        return $mapping[$seksi] ?? ucwords(str_replace('_', ' ', $seksi));
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
    public function create()   { return view('pengaduan.create'); }
    public function track()    { return view('pengaduan.track'); }

    public function searchTrack(Request $request)
    {
        $request->validate(['nomor_tiket' => 'required|string']);
        $pengaduan = Pengaduan::where('nomor_tiket', $request->nomor_tiket)->first();
        return view('pengaduan.track', compact('pengaduan'));
    }

    public function updateStatus(Request $request, Pengaduan $pengaduan)
    {
        $request->validate(['status' => 'required|in:pending,proses,diteruskan,selesai']);
        $pengaduan->update([
            'status'           => $request->status,
            'keterangan_admin' => $request->keterangan,
            'updated_by'       => Auth::id(),
        ]);
        return back()->with('success', 'Status pengaduan diperbarui.');
    }
}