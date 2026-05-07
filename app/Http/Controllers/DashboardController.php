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
        if ($profile->role === 'kakanim') {
            return redirect()->route('kakanim.dashboard');
        }

         // Default fallback (jika role tidak dikenali)

        return redirect()->route('pengaduan.landing');
    }

    /**
     * DASHBOARD TIKKIM — God View
     */
    private function tikkim(Request $request)
    {
        $now    = Carbon::now();
        $kw     = $request->keyword;
        $status = $request->status;
        $seksiF = $request->seksi;
        $kanalF = $request->kanal;
 
        // ── 1 query untuk 4 statistik global ──────────────────
        $stats = DB::selectOne("
            SELECT
                COUNT(*) FILTER (WHERE DATE_TRUNC('month', tgl_pengaduan) = DATE_TRUNC('month', NOW()))
                    AS total_bulan,
                COUNT(*) FILTER (WHERE status = 'selesai'
                    AND DATE_TRUNC('month', tgl_pengaduan) = DATE_TRUNC('month', NOW()))
                    AS selesai,
                -- 🟢 PERBAIKAN: SLA Over jangan hitung tiket Selesai
                COUNT(*) FILTER (WHERE status != 'selesai' AND deadline_tindak_lanjut < NOW())
                    AS sla_over,
                -- 🟢 PERBAIKAN: SLA H-1 jangan hitung tiket Selesai
                COUNT(*) FILTER (WHERE status != 'selesai'
                    AND deadline_tindak_lanjut BETWEEN NOW() AND NOW() + INTERVAL '24 hours')
                    AS sla_hminus1
            FROM pengaduans
        ");
 
        $totalBulanIni = (int) $stats->total_bulan;
        $selesai       = (int) $stats->selesai;
        $slaOver       = (int) $stats->sla_over;
        $slaHMinus1    = (int) $stats->sla_hminus1;
 
        // ── Performa seksi: 1 query GROUP BY (bukan N+1 loop) ─
        $performaSeksi = Pengaduan::selectRaw("
            seksi_tujuan AS nama,
            COUNT(*) AS total,
            SUM(CASE WHEN status='selesai'    THEN 1 ELSE 0 END) AS selesai,
            SUM(CASE WHEN status='proses'     THEN 1 ELSE 0 END) AS proses,
            SUM(CASE WHEN status='pending'    THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status='diteruskan' THEN 1 ELSE 0 END) AS diteruskan,
            -- 🟢 PERBAIKAN: SLA Over Per Seksi jangan hitung tiket Selesai
            SUM(CASE WHEN status != 'selesai'
                      AND deadline_tindak_lanjut < NOW() THEN 1 ELSE 0 END) AS sla_over
        ")
        ->groupBy('seksi_tujuan')
        ->having(DB::raw('COUNT(*)'), '>', 0)
        ->get()
        ->map(fn($s) => collect($s)->put(
            'pct', $s->total > 0 ? round($s->selesai / $s->total * 100) : 0
        ));
 
        // ── Chart data ────────────────────────────────────────
        $kanalStats  = Pengaduan::selectRaw('kanal_pengaduan, COUNT(*) as jumlah')
            ->groupBy('kanal_pengaduan')->orderByDesc('jumlah')->get();
 
        $statusStats = Pengaduan::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')->get();
 
        // ── Dropdown filter ───────────────────────────────────
        $kanalList = $kanalStats->pluck('kanal_pengaduan')->filter()->values()->toArray();
        $seksiList = $performaSeksi->pluck('nama')->filter()->values()->toArray();
 
        // ── Tabel pengaduan dengan eager loading ──────────────
        $q = Pengaduan::with('tindakLanjut:id,pengaduan_id,catatan_petugas');
 
        // DashboardController@tikkim — ganti blok keyword
        if ($kw) {
            $isTicket = preg_match('/^IMI-\d{8}-\d+$/i', trim($kw));
            $q->where(function ($x) use ($kw, $isTicket) {
                if ($isTicket) {
                    $x->where('nomor_tiket', strtoupper(trim($kw)));
                } else {
                    $x->where('nama', 'like', "%$kw%")
                    ->orWhere('nomor_tiket', 'like', "%$kw%");
                }
            });
        }
        if ($status) $q->where('status', $status);
        if ($seksiF) $q->where('seksi_tujuan', $seksiF);
        if ($kanalF) $q->where('kanal_pengaduan', $kanalF);
 
        $pengaduans = $q->latest()->paginate(15, ['*'], 'pengaduan_page')->withQueryString();
 
        return view('dashboard.tikkim', compact(
            'totalBulanIni', 'selesai', 'slaOver', 'slaHMinus1',
            'performaSeksi', 'kanalStats', 'statusStats',
            'pengaduans', 'kanalList', 'seksiList'
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
            $isTicket = preg_match('/^IMI-\d{8}-\d+$/i', trim($keyword));
            $query->where(function ($q) use ($keyword, $isTicket) {
                if ($isTicket) {
                    $q->where('nomor_tiket', strtoupper(trim($keyword)));
                } else {
                    $q->where('nama', 'like', "%$keyword%")
                    ->orWhere('nomor_tiket', 'like', "%$keyword%");
                }
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