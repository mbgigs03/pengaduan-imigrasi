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
            return $this->tikkim(); 
        } 

        if ($profile->role === 'seksi' || $profile->role === 'admin') {
            return $this->seksi(); 
        }

        return redirect()->route('pengaduan.landing');
    }

    /**
     * DASHBOARD TIKKIM — God View
     */
    private function tikkim()
    {
        $now = Carbon::now();

        // === STATISTIK GLOBAL ===
        $totalBulanIni = Pengaduan::whereMonth('tgl_pengaduan', $now->month)
            ->whereYear('tgl_pengaduan', $now->year)
            ->count();

        $selesai = Pengaduan::where('status', 'selesai')
            ->whereMonth('tgl_pengaduan', $now->month)
            ->whereYear('tgl_pengaduan', $now->year)
            ->count();

        $slaOver = Pengaduan::where('status', '!=', 'selesai')
            ->where('deadline_tindak_lanjut', '<', $now)
            ->count();

        $slaHMinus1 = Pengaduan::where('status', '!=', 'selesai')
            ->whereBetween('deadline_tindak_lanjut', [$now, $now->copy()->addDay()])
            ->count();

        // === PERFORMA PER SEKSI ===
        $seksiList = ['Tikkim', 'Inteldakim', 'Doklanintalkim', 'Tata Usaha'];

        $performaSeksi = collect($seksiList)->map(function ($seksi) use ($now) {
            $total   = Pengaduan::where('seksi_tujuan', $seksi)->whereMonth('tgl_pengaduan', $now->month)->count();
            $selesai = Pengaduan::where('seksi_tujuan', $seksi)->where('status', 'selesai')->whereMonth('tgl_pengaduan', $now->month)->count();
            // Perbaikan typo: use($now) agar bisa panggil Carbon::now() atau $now
            $slaOver = Pengaduan::where('seksi_tujuan', $seksi)->where('status', '!=', 'selesai')->where('deadline_tindak_lanjut', '<', $now)->count();

            return [
                'nama'     => $seksi,
                'total'    => $total,
                'selesai'  => $selesai,
                'sla_over' => $slaOver,
                'pct'      => $total > 0 ? round($selesai / $total * 100) : 0,
            ];
        });

        // === SEBARAN KANAL & STATUS ===
        $kanalStats = Pengaduan::selectRaw('kanal_pengaduan, COUNT(*) as jumlah')->groupBy('kanal_pengaduan')->get();
        $statusStats = Pengaduan::selectRaw('status, COUNT(*) as jumlah')->groupBy('status')->get();

        $laporanSla = Pengaduan::where('status', '!=', 'selesai')
            ->orderBy('deadline_tindak_lanjut')
            ->get()
            ->map(function ($p) {
                $p->sla_status = $this->hitungSlaStatus($p->deadline_tindak_lanjut);
                return $p;
            });

        $pengaduans = Pengaduan::latest()->paginate(15);

        return view('dashboard.tikkim', compact(
            'totalBulanIni', 'selesai', 'slaOver', 'slaHMinus1',
            'performaSeksi', 'kanalStats', 'statusStats',
            'laporanSla', 'pengaduans'
        ));
    }

    /**
     * DASHBOARD SEKSI — Filtered View
     */
    private function seksi()
    {
        $user      = Auth::user();
        $seksi     = $user->profile->seksi; 
        $now       = Carbon::now();

        $totalMasuk = Pengaduan::where('seksi_tujuan', $seksi)->count();
        $selesai = Pengaduan::where('seksi_tujuan', $seksi)->where('status', 'selesai')->count();
        $slaOver = Pengaduan::where('seksi_tujuan', $seksi)->where('status', '!=', 'selesai')->where('deadline_tindak_lanjut', '<', $now)->count();
        $menunggu = Pengaduan::where('seksi_tujuan', $seksi)->whereIn('status', ['pending', 'proses'])->count();

        $pengaduans = Pengaduan::where('seksi_tujuan', $seksi)
            ->latest()
            ->paginate(15)
            ->through(function ($p) {
                $p->sla_status = $this->hitungSlaStatus($p->deadline_tindak_lanjut);
                return $p;
            });

        return view('dashboard.seksi', compact(
            'seksi', 'totalMasuk', 'selesai', 'slaOver', 'menunggu', 'pengaduans'
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
        $now  = Carbon::now();
        $deadline = Carbon::parse($deadline);
        
        if ($now->greaterThan($deadline)) return 'over';
        if ($now->diffInDays($deadline) <= 1) return 'warn';
        return 'ok';
    }
}