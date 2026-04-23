<?php

namespace App\Repositories;

use App\Models\Pengaduan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * PengaduanRepository
 * ─────────────────────────────────────────────────────────────
 * Memisahkan logika query dari Controller.
 * Semua filter & ekspor melewati satu titik ini sehingga
 * optimasi query (index, chunking) cukup diubah di satu tempat.
 */
class PengaduanRepository
{
    // ═══════════════════════════════════════════════════════════
    // CORE QUERY BUILDER
    // Satu method yang membangun query berdasarkan array filter.
    // Dipakai oleh method paginate(), export(), dan summary().
    // ═══════════════════════════════════════════════════════════

    /**
     * @param  array{
     *   periode?: string,        // 'daily'|'weekly'|'monthly'|'yearly'|'custom'
     *   start_date?: string,     // Y-m-d
     *   end_date?: string,       // Y-m-d
     *   seksi?: string,          // nama seksi (untuk scope Seksi)
     *   status?: string,
     *   kanal?: string,
     * } $filters
     */
    public function buildQuery(array $filters): Builder
    {
        $query = Pengaduan::query()
            ->select([
                'id', 'nomor_tiket', 'nama', 'nik', 'whatsapp',
                'seksi_tujuan', 'kanal_pengaduan', 'jenis_layanan',
                'status', 'tgl_pengaduan', 'deadline_tindak_lanjut',
                'keterangan_admin', 'pdf_url', 'created_at',
            ]);

        // ── Filter scope seksi (wajib untuk role 'seksi') ─────
        if (!empty($filters['seksi'])) {
            $query->where('seksi_tujuan', $filters['seksi']);
        }

        // ── Filter status ─────────────────────────────────────
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // ── Filter kanal ──────────────────────────────────────
        if (!empty($filters['kanal'])) {
            $query->where('kanal_pengaduan', $filters['kanal']);
        }

        // ── Filter tanggal ────────────────────────────────────
        $this->applyDateFilter($query, $filters);

        return $query->orderBy('tgl_pengaduan', 'desc');
    }

    // ═══════════════════════════════════════════════════════════
    // APPLY DATE FILTER
    // ═══════════════════════════════════════════════════════════
    private function applyDateFilter(Builder $query, array $filters): void
    {
        $periode = $filters['periode'] ?? null;

        // Mode custom: gunakan start_date & end_date dari request
        if ($periode === 'custom') {
            if (!empty($filters['start_date'])) {
                $query->whereDate('tgl_pengaduan', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $query->whereDate('tgl_pengaduan', '<=', $filters['end_date']);
            }
            return;
        }

        // Mode preset
        $now = Carbon::now();

        match ($periode) {
            'daily'   => $query->whereDate('tgl_pengaduan', $now->toDateString()),
            'weekly'  => $query->whereBetween('tgl_pengaduan', [
                            $now->copy()->subDays(6)->startOfDay(),
                            $now->copy()->endOfDay(),
                         ]),
            'monthly' => $query->whereMonth('tgl_pengaduan', $now->month)
                               ->whereYear('tgl_pengaduan', $now->year),
            'yearly'  => $query->whereYear('tgl_pengaduan', $now->year),
            default   => null, // tanpa filter tanggal = semua data
        };
    }

    // ═══════════════════════════════════════════════════════════
    // PAGINATED — untuk tampilan dashboard
    // ═══════════════════════════════════════════════════════════
    public function paginate(array $filters, int $perPage = 15)
    {
        return $this->buildQuery($filters)->paginate($perPage)->withQueryString();
    }

    // ═══════════════════════════════════════════════════════════
    // EXPORT COLLECTION — untuk ekspor ke Excel/PDF
    // Menggunakan lazy() + chunk agar RAM efisien pada dataset besar
    // ═══════════════════════════════════════════════════════════
    public function forExport(array $filters): \Illuminate\Support\LazyCollection
    {
        return $this->buildQuery($filters)->lazy(500); // chunk 500 baris per iterasi
    }

    // ═══════════════════════════════════════════════════════════
    // RINGKASAN STATISTIK — untuk sheet "Ringkasan" di Excel
    // ═══════════════════════════════════════════════════════════
    public function summary(array $filters): array
    {
        $base = $this->buildQuery($filters);

        return [
            'total'      => (clone $base)->count(),
            'pending'    => (clone $base)->where('status', 'pending')->count(),
            'proses'     => (clone $base)->where('status', 'proses')->count(),
            'diteruskan' => (clone $base)->where('status', 'diteruskan')->count(),
            'selesai'    => (clone $base)->where('status', 'selesai')->count(),
            'sla_over'   => (clone $base)->where('status', '!=', 'selesai')
                                         ->where('deadline_tindak_lanjut', '<', now())
                                         ->count(),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // LABEL PERIODE — untuk judul ekspor
    // ═══════════════════════════════════════════════════════════
    public function periodeLabel(array $filters): string
    {
        $p = $filters['periode'] ?? 'all';

        return match ($p) {
            'daily'   => 'Harian — ' . Carbon::today()->translatedFormat('d F Y'),
            'weekly'  => 'Mingguan — ' . Carbon::now()->subDays(6)->translatedFormat('d F Y')
                         . ' s.d. ' . Carbon::today()->translatedFormat('d F Y'),
            'monthly' => 'Bulanan — ' . Carbon::now()->translatedFormat('F Y'),
            'yearly'  => 'Tahunan — ' . Carbon::now()->year,
            'custom'  => 'Custom — ' . ($filters['start_date'] ?? '?')
                         . ' s.d. ' . ($filters['end_date'] ?? '?'),
            default   => 'Semua Data',
        };
    }
    public function all(array $filters = [])
    {
        $query = Pengaduan::query();

        // Filter berdasarkan Seksi
        if (!empty($filters['seksi'])) {
            $query->where('seksi_tujuan', $filters['seksi']);
        }

        // Filter berdasarkan Status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter berdasarkan Kanal
        if (!empty($filters['kanal'])) {
            $query->where('kanal_pengaduan', $filters['kanal']);
        }

        // Filter Rentang Tanggal (Start & End Date)
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('tgl_pengaduan', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}