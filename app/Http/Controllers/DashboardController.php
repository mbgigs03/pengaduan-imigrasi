<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // TAMBAHKAN INI
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Entry point — arahkan ke dashboard sesuai role
     */
    public function index()
    {
        $user = auth()->user();
        $profile = $user->profile; 

        if (!$profile) {
            return redirect()->route('pengaduan.landing');
        }

        // SINKRONISASI: Panggil method privat yang benar (tikkim() dan seksi())
        if ($profile->role === 'tikkim' || $profile->role === 'super_admin') {
            return $this->tikkim(request());
        } 

        if ($profile->role === 'seksi' || $profile->role === 'admin') {
            return $this->seksi(request());
        }

        return redirect()->route('pengaduan.landing');
    }

    /**
     * DASHBOARD TIKKIM — God View
     */
    private function tikkim(Request $request)
    {
        $now = now();

        // === FILTER INPUT ===
        $keyword = $request->keyword;
        $status  = $request->status;
        $seksi   = $request->seksi;
        $kanal   = $request->kanal;
        $sla     = $request->sla;

        // === BASE QUERY (FILTERED DATA) ===
        $baseQuery = Pengaduan::query();

        if ($keyword) {
            $baseQuery->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%$keyword%")
                ->orWhere('nomor_tiket', 'like', "%$keyword%");
            });
        }

        if ($status) {
            $baseQuery->where('status', $status);
        }

        if ($seksi) {
            $baseQuery->where('seksi_tujuan', $seksi);
        }

        if ($kanal) {
            $baseQuery->where('kanal_pengaduan', $kanal);
        }

        // === FILTER SLA ===
        if ($sla) {
            if ($sla === 'over') {
                $baseQuery->where('status', '!=', 'selesai')
                    ->where('deadline_tindak_lanjut', '<', $now);
            } elseif ($sla === 'warn') {
                $baseQuery->where('status', '!=', 'selesai')
                    ->whereBetween('deadline_tindak_lanjut', [$now, $now->copy()->addDay()]);
            } elseif ($sla === 'ok') {
                $baseQuery->where('deadline_tindak_lanjut', '>', $now->copy()->addDay());
            }
        }

        // === STATISTIK (PAKAI CLONE BIAR AMAN) ===
        $totalBulanIni = (clone $baseQuery)
            ->whereMonth('tgl_pengaduan', $now->month)
            ->whereYear('tgl_pengaduan', $now->year)
            ->count();

        $selesai = (clone $baseQuery)
            ->where('status', 'selesai')
            ->count();

        $slaOver = (clone $baseQuery)
            ->where('status', '!=', 'selesai')
            ->where('deadline_tindak_lanjut', '<', $now)
            ->count();

        $slaHMinus1 = (clone $baseQuery)
            ->where('status', '!=', 'selesai')
            ->whereBetween('deadline_tindak_lanjut', [$now, $now->copy()->addDay()])
            ->count();

        // === LIST FILTER (DINAMIS) ===
        $kanalList = Pengaduan::select('kanal_pengaduan')->distinct()->pluck('kanal_pengaduan');
        $seksiList = Pengaduan::select('seksi_tujuan')->distinct()->pluck('seksi_tujuan');

        // === CHART DATA (TIDAK TERPENGARUH FILTER) ⚠️ PENTING
        $kanalStats = Pengaduan::selectRaw('kanal_pengaduan, COUNT(*) as jumlah')
            ->groupBy('kanal_pengaduan')
            ->get();

        $statusStats = Pengaduan::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->get();

        // === PERFORMA SEKSI (FULL DATA) ⚠️
        $performaSeksi = Pengaduan::selectRaw("
                seksi_tujuan as nama,
                COUNT(*) as total,
                SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status='proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status='diteruskan' THEN 1 ELSE 0 END) as diteruskan
            ")
            ->groupBy('seksi_tujuan')
            ->get()
            ->map(function ($s) {
                $s->pct = $s->total > 0 ? round($s->selesai / $s->total * 100) : 0;
                return $s;
            });

        // === LAPORAN SLA ===
        $laporanSla = (clone $baseQuery)
            ->with('tindakLanjut')
            ->where('status', '!=', 'selesai')
            ->orderBy('deadline_tindak_lanjut')
            ->paginate(10, ['*'], 'sla_page')
            ->withQueryString();

        $laporanSla->getCollection()->transform(function ($p) {
            $p->sla_status = $this->hitungSlaStatus($p->deadline_tindak_lanjut);
            return $p;
        });

        // === SEMUA PENGADUAN ===
        $pengaduans = (clone $baseQuery)
            ->with('tindakLanjut')
            ->latest()
            ->paginate(15, ['*'], 'pengaduan_page')
            ->withQueryString();

        return view('dashboard.tikkim', compact(
            'totalBulanIni',
            'selesai',
            'slaOver',
            'slaHMinus1',
            'performaSeksi',
            'kanalStats',
            'statusStats',
            'laporanSla',
            'pengaduans',
            'kanalList',
            'seksiList'
        ));
    }

    /**
     * DASHBOARD SEKSI — Filtered View
     */
    private function seksi(Request $request)
    {
        $user  = Auth::user();
        $seksi = $user->profile->seksi;
        $now   = now();

        $keyword = $request->keyword;
        $status  = $request->status;
        $kanal   = $request->kanal;
        $sla     = $request->sla;

        $query = Pengaduan::where('seksi_tujuan', $seksi);

        // FILTER
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%$keyword%")
                ->orWhere('nomor_tiket', 'like', "%$keyword%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($kanal) {
            $query->where('kanal_pengaduan', $kanal);
        }

        if ($sla === 'over') {
            $query->where('status','!=','selesai')
                ->where('deadline_tindak_lanjut','<',$now);
        } elseif ($sla === 'warn') {
            $query->where('status','!=','selesai')
                ->whereBetween('deadline_tindak_lanjut', [$now, $now->copy()->addDay()]);
        } elseif ($sla === 'ok') {
            $query->where('deadline_tindak_lanjut','>', $now->copy()->addDay());
        }

        // STAT
        $totalMasuk = (clone $query)->count();
        $selesai    = (clone $query)->where('status','selesai')->count();
        $slaOver    = (clone $query)->where('status','!=','selesai')
                        ->where('deadline_tindak_lanjut','<',$now)->count();
        $menunggu   = (clone $query)->whereIn('status',['pending','proses'])->count();

        // LIST
        $kanalList = Pengaduan::select('kanal_pengaduan')->distinct()->pluck('kanal_pengaduan');

        // DATA
        $pengaduans = (clone $query)
            ->with('tindakLanjut')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pengaduans->getCollection()->transform(function ($p) {
            $p->sla_status = $this->hitungSlaStatus($p->deadline_tindak_lanjut);
            return $p;
        });

        return view('dashboard.seksi', compact(
            'seksi',
            'totalMasuk',
            'selesai',
            'slaOver',
            'menunggu',
            'pengaduans',
            'kanalList'
        ));
    }

    /**
     * Update status & tindak lanjut
     */
    public function updateStatus(Request $request, Pengaduan $pengaduan)
    {
        $user = Auth::user();
        
        if ($user->profile->role === 'seksi') {
            abort_if($pengaduan->seksi_tujuan !== $user->profile->seksi, 403, 'Akses ditolak.');
        }

        $request->validate([
            'status' => 'required|in:pending,proses,diteruskan,selesai',
            'catatan' => $request->status === 'selesai' ? 'required|min:10' : 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $pengaduan, $user) {
                $pengaduan->update([
                    'status' => $request->status,
                    'updated_by' => $user->id,
                ]);

                if ($request->status === 'selesai') {
                    // Perbaikan: pastikan nama relasi di model Pengaduan adalah tindakLanjut()
                    $pengaduan->tindakLanjut()->updateOrCreate(
                        ['pengaduan_id' => $pengaduan->id],
                        [
                            'catatan_petugas' => $request->catatan,
                            'tanggal_selesai' => Carbon::now(),
                        ]
                    );
                }
            });

            return back()->with('success', 'Status berhasil diperbarui.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function hitungSlaStatus($deadline): string
    {
        if (!$deadline) return 'ok';

        $now = now();
        $deadline = \Carbon\Carbon::parse($deadline);

        if ($now->greaterThan($deadline)) {
            return 'over'; // merah
        }

        // H-1 → kurang dari atau sama dengan 1 hari
        if ($now->diffInHours($deadline) <= 24) {
            return 'warn'; // kuning
        }

        return 'ok'; // hijau
    }

    public function downloadPdf(string $nomorTiket)
    {
        $pengaduan = \App\Models\Pengaduan::where('nomor_tiket', $nomorTiket)
            ->firstOrFail();
    
        // ── Guard: hak akses role seksi ─────────────────────────
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->profile->role === 'seksi') {
            abort_if(
                $pengaduan->seksi_tujuan !== $user->profile->seksi,
                403,
                'Anda tidak berwenang mengunduh dokumen ini.'
            );
        }
    
        // ── Kasus 1: pdf_url ada, cek apakah file masih exist di Supabase ──
        if ($pengaduan->pdf_url) {
    
            // Verifikasi file masih ada dengan HEAD request (hemat bandwidth)
            $check = \Illuminate\Support\Facades\Http::timeout(5)
                ->head($pengaduan->pdf_url);
    
            if ($check->successful()) {
                // File ada → redirect ke URL publik Supabase
                // Browser akan menampilkan dialog download karena Content-Type PDF
                return redirect($pengaduan->pdf_url);
            }
    
            // File di Supabase sudah dihapus/tidak ada → reset url, regenerate
            $pengaduan->update(['pdf_url' => null]);
        }
    
        // ── Kasus 2: pdf_url kosong atau file hilang → generate sekarang ──
        try {
            $this->doGeneratePdf($pengaduan);
            $pengaduan->refresh();
    
            if ($pengaduan->pdf_url) {
                return redirect($pengaduan->pdf_url);
            }
    
            return back()->with('error', 'PDF berhasil dibuat tetapi URL tidak tersimpan. Coba lagi.');
    
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Download PDF] Gagal generate', [
                'tiket' => $nomorTiket,
                'error' => $e->getMessage(),
            ]);
    
            return back()->with('error', 'File PDF belum tersedia dan gagal dibuat. Hubungi administrator.');
        }
    }
    
    // ═══════════════════════════════════════════════════════════════
    // DOWNLOAD DOCX — mengambil file .docx arsip dari Supabase
    // Path Supabase: pengaduan/{nomor_tiket}/laporan-pengaduan.docx
    // ═══════════════════════════════════════════════════════════════
    public function downloadDocx(string $nomorTiket)
    {
        $pengaduan = \App\Models\Pengaduan::where('nomor_tiket', $nomorTiket)
            ->firstOrFail();
    
        // Guard seksi
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->profile->role === 'seksi') {
            abort_if($pengaduan->seksi_tujuan !== $user->profile->seksi, 403);
        }
    
        // Susun URL publik file .docx
        $docxPath   = "pengaduan/{$nomorTiket}/laporan-pengaduan.docx";
        $publicBase = rtrim(env('SUPABASE_URL'), '/');
        $docxUrl    = "{$publicBase}/{$docxPath}";
    
        // Cek apakah file ada di Supabase
        $check = \Illuminate\Support\Facades\Http::timeout(5)->head($docxUrl);
    
        if (!$check->successful()) {
            return back()->with('error', 'File dokumen (.docx) belum tersedia untuk tiket ini.');
        }
    
        // Stream file dari Supabase ke browser sebagai download
        // Ini menghindari menyimpan file di server lokal
        $response = \Illuminate\Support\Facades\Http::timeout(30)->get($docxUrl);
    
        if (!$response->successful()) {
            return back()->with('error', 'Gagal mengunduh file dari server. Coba lagi.');
        }
    
        $filename = 'Laporan-Pengaduan-' . $nomorTiket . '.docx';
    
        return response($response->body(), 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($response->body()),
        ]);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // PRIVATE: Generate PDF on-demand (sinkron, tanpa queue)
    // Dipakai sebagai fallback saat pdf_url kosong/rusak
    // ═══════════════════════════════════════════════════════════════
    private function doGeneratePdf(\App\Models\Pengaduan $pengaduan): void
    {
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
    
        $slug       = \Illuminate\Support\Str::slug($pengaduan->nomor_tiket);
        $pdfTmpPath = $tmpDir . DIRECTORY_SEPARATOR . $slug . '.pdf';
    
        try {
            // Render Blade template → HTML
            $jenis = strtolower($pengaduan->seksi_tujuan);
            $html  = \Illuminate\Support\Facades\View::make('pdf.laporan-pengaduan', [
                'pengaduan'      => $pengaduan,
                'seksiFormatted' => $this->formatSeksiLabel($pengaduan->seksi_tujuan),
                'cbPegawai'      => str_contains($jenis, 'pegawai')  ? '&#9745;' : '&#9744;',
                'cbLayanan'      => str_contains($jenis, 'layanan')  ? '&#9745;' : '&#9744;',
                'cbSarpras'      => (str_contains($jenis, 'sarpras') || str_contains($jenis, 'tata usaha'))
                                        ? '&#9745;' : '&#9744;',
            ])->render();
    
            // HTML → PDF via DomPDF
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled',      false);
            $options->set('defaultFont',          'DejaVu Sans');
            $options->set('chroot',               $tmpDir);
    
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
    
            file_put_contents($pdfTmpPath, $dompdf->output());
    
            // Upload ke Supabase
            $storagePath = "pengaduan/{$pengaduan->nomor_tiket}/laporan-pengaduan.pdf";
            \Illuminate\Support\Facades\Storage::disk('supabase')->put(
                $storagePath,
                file_get_contents($pdfTmpPath),
                ['visibility' => 'public', 'ContentType' => 'application/pdf']
            );
    
            // Simpan URL ke database
            $publicUrl = rtrim(env('SUPABASE_URL'), '/') . '/' . $storagePath;
            $pengaduan->update(['pdf_url' => $publicUrl]);
    
        } finally {
            if (file_exists($pdfTmpPath)) {
                @unlink($pdfTmpPath);
            }
        }
    }
}