<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

// PHPWord untuk mengisi template .docx
use PhpOffice\PhpWord\TemplateProcessor;

// DomPDF untuk render HTML menjadi PDF
use Dompdf\Dompdf;
use Dompdf\Options;

class PengaduanController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // STORE — Simpan pengaduan + trigger export PDF
    // ─────────────────────────────────────────────────────────
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

        // ── Simpan data ke database ──────────────────────────
        $pengaduan = new Pengaduan();

        $pengaduan->nama              = $request->nama;
        $pengaduan->nik               = $request->nik;
        $pengaduan->alamat            = $request->alamat ?? '-';
        $pengaduan->whatsapp          = $request->whatsapp;
        $pengaduan->jenis_layanan     = $request->jenis_layanan ?? 'informasi';
        $pengaduan->seksi_tujuan      = $request->seksi_tujuan;
        $pengaduan->kanal_pengaduan   = $request->kanal;
        $pengaduan->aduan             = $request->aduan;
        $pengaduan->tgl_pengaduan     = Carbon::now();
        $pengaduan->deadline_tindak_lanjut = Carbon::now()->addDays(3);
        $pengaduan->status            = 'pending';

        if ($request->hasFile('bukti')) {
            $pengaduan->bukti = $request->file('bukti')->store('bukti-pengaduan', 'public');
        }

        $pengaduan->save(); // nomor_tiket di-generate via model booted()

        // ── Trigger export PDF (non-blocking: error tidak menghentikan redirect) ──
        try {
            $pdfPath = $this->exportPdf($pengaduan);
            Log::info("PDF pengaduan {$pengaduan->nomor_tiket} berhasil dibuat: {$pdfPath}");
        } catch (\Throwable $e) {
            // PDF gagal dibuat tapi pengaduan tetap tersimpan
            Log::error("Gagal membuat PDF untuk {$pengaduan->nomor_tiket}: " . $e->getMessage());
        }

        return redirect()->route('pengaduan.landing')->with([
            'success' => 'Pengaduan berhasil diajukan!',
            'tiket'   => $pengaduan->nomor_tiket,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // EXPORT PDF
    // Alur: isi template .docx → render HTML → DomPDF → upload Supabase
    // ─────────────────────────────────────────────────────────
    private function exportPdf(Pengaduan $pengaduan): string
    {
        // ── 1. Path file temporer ───────────────────────────
        $tmpDir      = storage_path('app/tmp');
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $uniqueId    = $pengaduan->nomor_tiket . '-' . Str::random(6);
        $docxTmpPath = $tmpDir . "/{$uniqueId}.docx";
        $pdfTmpPath  = $tmpDir . "/{$uniqueId}.pdf";

        // ── 2. Isi placeholder template .docx ──────────────
        // Pastikan file template sudah ada di: storage/app/templates/LAPORAN_PENGADUAN.docx
        // Instruksi konversi .doc → .docx: lihat README di bawah
        $templatePath = storage_path('app/templates/LAPORAN_PENGADUAN.docx');

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template tidak ditemukan: {$templatePath}");
        }

        $processor = new TemplateProcessor($templatePath);

        // Mapping placeholder template → data pengaduan
        // Placeholder di template: ${tgl_pengaduan}, ${nama}, dst.
        $processor->setValue('tgl_pengaduan', Carbon::parse($pengaduan->tgl_pengaduan)->translatedFormat('d F Y'));
        $processor->setValue('nama',          $pengaduan->nama);
        $processor->setValue('jenis_kelamin', '-');   // belum ada di model, isi default
        $processor->setValue('alamat',        $pengaduan->alamat ?? '-');
        $processor->setValue('no_wa',         $pengaduan->whatsapp);
        $processor->setValue('isi_aduan',     $pengaduan->aduan);
        $processor->setValue('seksi',         $this->formatSeksi($pengaduan->seksi_tujuan));
        $processor->setValue('tindak_lanjut', Carbon::now()->translatedFormat('d F Y'));
        $processor->setValue('nomor_tiket',   $pengaduan->nomor_tiket);

        $processor->saveAs($docxTmpPath);

        // ── 3. Render .docx → HTML → PDF via DomPDF ────────
        // PHPWord membaca .docx yang sudah diisi dan mengonversinya ke HTML
        $phpWord       = \PhpOffice\PhpWord\IOFactory::load($docxTmpPath);
        $htmlWriter    = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

        // Tangkap output HTML ke string (bukan tulis ke file)
        ob_start();
        $htmlWriter->save('php://output');
        $htmlContent = ob_get_clean();

        // Tambahkan styling agar output PDF rapi (A4, font, dll)
        $htmlContent = $this->injectPdfStyles($htmlContent);

        // ── 4. DomPDF: HTML → PDF ───────────────────────────
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);    // keamanan: nonaktifkan remote resources
        $options->set('defaultFont', 'DejaVu Sans'); // font yang mendukung karakter UTF-8
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Simpan PDF ke file temporer
        file_put_contents($pdfTmpPath, $dompdf->output());

        // ── 5. Upload ke Supabase Storage ───────────────────
        // Struktur folder: pengaduan/{nomor_tiket}/laporan.pdf
        $storagePath = "pengaduan/{$pengaduan->nomor_tiket}/laporan-pengaduan.pdf";

        $uploaded = Storage::disk('supabase')->put(
            $storagePath,
            file_get_contents($pdfTmpPath),
            ['visibility' => 'public', 'ContentType' => 'application/pdf']
        );

        if (!$uploaded) {
            throw new \RuntimeException("Gagal mengupload PDF ke Supabase: {$storagePath}");
        }

        // ── 6. Simpan URL PDF ke database ───────────────────
        $publicUrl = env('SUPABASE_URL') . '/' . $storagePath;
        $pengaduan->update(['pdf_url' => $publicUrl]);

        // ── 7. Hapus file temporer di server lokal ──────────
        @unlink($docxTmpPath);
        @unlink($pdfTmpPath);

        return $storagePath;
    }

   

    // ─────────────────────────────────────────────────────────
    // Helper: format nama seksi untuk template surat
    // ─────────────────────────────────────────────────────────
    private function formatSeksi(string $seksi): string
    {
        return match ($seksi) {
            'Paspor'                 => 'Pelayanan Paspor',
            'Dokumen Perjalanan'     => 'Dokumen Perjalanan',
            'Izin Tinggal'           => 'Izin Tinggal',
            'Pengawasan Orang Asing' => 'Pengawasan Orang Asing',
            'Alur BAP'               => 'Alur BAP',
            'Tata Usaha'             => 'Tata Usaha',
            default                  => ucwords(str_replace('_', ' ', $seksi)),
        };
    }

    // ─────────────────────────────────────────────────────────
    // DOWNLOAD PDF — endpoint untuk download ulang (opsional)
    // GET /pengaduan/{nomor_tiket}/pdf
    // ─────────────────────────────────────────────────────────
    public function downloadPdf(string $nomorTiket)
    {
        $pengaduan = Pengaduan::where('nomor_tiket', $nomorTiket)->firstOrFail();

        if (!$pengaduan->pdf_url) {
            // Coba generate ulang jika belum ada
            try {
                $this->exportPdf($pengaduan);
                $pengaduan->refresh();
            } catch (\Throwable $e) {
                abort(500, 'PDF belum tersedia dan gagal dibuat ulang.');
            }
        }

        return redirect($pengaduan->pdf_url);
    }

    // ─────────────────────────────────────────────────────────
    // Method lainnya (tidak berubah)
    // ─────────────────────────────────────────────────────────
    public function create()
    {
        return view('pengaduan.create');
    }

    public function track()
    {
        return view('pengaduan.track');
    }

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

    public function show($id)
    {
        $pengaduan = Pengaduan::with('tindakLanjut')->findOrFail($id);

        return view('pengaduan.show', compact('pengaduan'));
    }

     // ─────────────────────────────────────────────────────────
    // Helper: inject CSS styling ke HTML sebelum di-render DomPDF
    // ─────────────────────────────────────────────────────────

}