<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PengaduanSlaController
 * ─────────────────────────────────────────────────────────────
 * Concern tunggal: monitoring & prioritas SLA.
 * Tidak ada logika dashboard umum di sini.
 */
class PengaduanSlaController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // INDEX — Halaman monitoring SLA
    // ═══════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->profile->role;
        $now  = Carbon::now();

        // ── Base query dengan Eager Loading ───────────────────
        // withCount lebih efisien daripada load relasi penuh
        $query = Pengaduan::query()
            ->with(['tindakLanjut:id,pengaduan_id,catatan_petugas,tanggal_selesai'])
            ->where('status', '!=', 'selesai');

        // Seksi hanya lihat miliknya
        if ($role === 'seksi') {
            $query->where('seksi_tujuan', $user->profile->seksi);
        }

        // ── Filter ────────────────────────────────────────────
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->keyword . '%')
                  ->orWhere('nomor_tiket', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('seksi') && $role === 'tikkim') {
            $query->where('seksi_tujuan', $request->seksi);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kanal')) {
            $query->where('kanal_pengaduan', $request->kanal);
        }

        // Filter SLA via query (lebih cepat daripada filter di PHP)
        if ($request->filled('sla')) {
            match ($request->sla) {
                'over' => $query->where('deadline_tindak_lanjut', '<', $now),
                'warn' => $query->whereBetween('deadline_tindak_lanjut', [
                              $now,
                              $now->copy()->addHours(24),
                          ]),
                'ok'   => $query->where('deadline_tindak_lanjut', '>', $now->copy()->addHours(24)),
                default => null,
            };
        }

        // ── PRIORITY SORT — kunci KPI "3 detik identifikasi" ──
        // Urutan: Over SLA → H-1 → On Track → dalam masing-masing grup: deadline terlama dulu
        //
        // PostgreSQL: CASE expression di ORDER BY
        // MySQL      : sama, sudah didukung
        $query->orderByRaw("
            CASE
                WHEN deadline_tindak_lanjut < NOW()                          THEN 1
                WHEN deadline_tindak_lanjut < NOW() + INTERVAL '24 hours'    THEN 2
                ELSE 3
            END ASC,
            deadline_tindak_lanjut ASC
        ");

        // ── Paginate + append filter ke link halaman ──────────
        $pengaduans = $query->paginate(10)->withQueryString();

        // Hitung sla_status di PHP (satu kali per item, tidak ada query tambahan)
        $pengaduans->getCollection()->transform(function ($p) {
            $p->sla_status = $this->getSlaStatus($p->deadline_tindak_lanjut);
            return $p;
        });

        // ── Summary counters (satu query agregasi, bukan 4 query) ──
        $counters = $this->getCounters($user, $role);

        // ── Data filter dropdown ───────────────────────────────
        $kanalList = Pengaduan::distinct()->pluck('kanal_pengaduan')->filter()->sort()->values();
        $seksiList = Pengaduan::distinct()->pluck('seksi_tujuan')->filter()->sort()->values();

        return view('pengaduan.sla', compact(
            'pengaduans', 'counters', 'kanalList', 'seksiList'
        ));
    }

    // ═══════════════════════════════════════════════════════════
    // COUNTERS — satu query dengan GROUP BY, bukan 4 query terpisah
    // ═══════════════════════════════════════════════════════════
    private function getCounters($user, string $role): array
    {
        $now = Carbon::now();

        $base = Pengaduan::query()->where('status', '!=', 'selesai');
        if ($role === 'seksi') {
            $base->where('seksi_tujuan', $user->profile->seksi);
        }

        // Satu query dengan conditional COUNT
        $row = (clone $base)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN deadline_tindak_lanjut < NOW() THEN 1 ELSE 0 END)                                    as over,
            SUM(CASE WHEN deadline_tindak_lanjut BETWEEN NOW() AND NOW() + INTERVAL '24 hours' THEN 1 ELSE 0 END) as warn,
            SUM(CASE WHEN deadline_tindak_lanjut > NOW() + INTERVAL '24 hours' THEN 1 ELSE 0 END)              as ok
        ")->first();

        return [
            'total' => (int) $row->total,
            'over'  => (int) $row->over,
            'warn'  => (int) $row->warn,
            'ok'    => (int) $row->ok,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // SLA STATUS helper
    // ═══════════════════════════════════════════════════════════
    private function getSlaStatus($deadline): string
    {
        if (!$deadline) return 'ok';
        $deadline = Carbon::parse($deadline);
        $now      = Carbon::now();

        if ($now->greaterThan($deadline))           return 'over';
        if ($now->diffInHours($deadline) <= 24)     return 'warn';
        return 'ok';
    }
}