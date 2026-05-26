<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pengaduan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KakanimController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    // DASHBOARD — data strategis + panel SLA alert (summarized)
    // ═══════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);

        // ── Scorecard ─────────────────────────────────────────
        $scorecard = DB::selectOne("
            SELECT
                COUNT(*)                                                        AS total,
                COUNT(*) FILTER (WHERE status = 'selesai')                        AS selesai,
                COUNT(*) FILTER (WHERE status != 'selesai')                       AS belum_selesai,
                COUNT(*) FILTER (WHERE status != 'selesai'
                    AND deadline_tindak_lanjut < NOW())                           AS over_sla,
                ROUND(
                    COUNT(*) FILTER (WHERE status = 'selesai')::numeric
                    / NULLIF(COUNT(*), 0) * 100, 1
                )                                                                 AS pct_selesai,
                COUNT(*) FILTER (
                    WHERE DATE_TRUNC('month', tgl_pengaduan) = DATE_TRUNC('month', NOW())
                      AND EXTRACT(YEAR FROM tgl_pengaduan) = :tahun
                )                                                                 AS masuk_bulan_ini,
                COUNT(*) FILTER (
                    WHERE EXTRACT(MONTH FROM tgl_pengaduan) = EXTRACT(MONTH FROM NOW()) - 1
                      AND EXTRACT(YEAR FROM tgl_pengaduan) = :tahun
                )                                                                 AS masuk_bulan_lalu
            FROM pengaduans
            WHERE EXTRACT(YEAR FROM tgl_pengaduan) = :tahun
        ", ['tahun' => $tahun]);

        $tren = 0;
        if (($scorecard->masuk_bulan_lalu ?? 0) > 0) {
            $tren = round(
                ($scorecard->masuk_bulan_ini - $scorecard->masuk_bulan_lalu)
                / $scorecard->masuk_bulan_lalu * 100, 1
            );
        }

        // ── Distribusi seksi ──────────────────────────────────
        $distribusiSeksi = DB::select("
            SELECT
                seksi_tujuan AS seksi,
                COUNT(*) AS total,
                COUNT(*) FILTER (WHERE status = 'selesai') AS selesai,
                COUNT(*) FILTER (WHERE status != 'selesai'
                    AND deadline_tindak_lanjut < NOW()) AS over_sla,
                ROUND(
                    COUNT(*) FILTER (WHERE status = 'selesai')::numeric
                    / NULLIF(COUNT(*), 0) * 100, 1
                ) AS pct_selesai
            FROM pengaduans
            WHERE EXTRACT(YEAR FROM tgl_pengaduan) = :tahun
            GROUP BY seksi_tujuan
            ORDER BY total DESC
        ", ['tahun' => $tahun]);

        // ── Tren bulanan ──────────────────────────────────────
        $trenBulanan = DB::select("
            SELECT
                EXTRACT(MONTH FROM tgl_pengaduan)::int AS bulan,
                COUNT(*) AS masuk,
                COUNT(*) FILTER (WHERE status = 'selesai') AS selesai
            FROM pengaduans
            WHERE EXTRACT(YEAR FROM tgl_pengaduan) = :tahun
            GROUP BY bulan ORDER BY bulan
        ", ['tahun' => $tahun]);

        $bulanLabel      = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $masukPerBulan   = array_fill(0, 12, 0);
        $selesaiPerBulan = array_fill(0, 12, 0);
        foreach ($trenBulanan as $row) {
            $masukPerBulan[$row->bulan - 1]   = (int) $row->masuk;
            $selesaiPerBulan[$row->bulan - 1] = (int) $row->selesai;
        }

        // ── Avg waktu penyelesaian ────────────────────────────
        $avgWaktu = DB::selectOne("
            SELECT
                ROUND(AVG(EXTRACT(EPOCH FROM (tl.tanggal_selesai - p.tgl_pengaduan)) / 86400.0), 2) AS avg_hari,
                COUNT(*) AS sampel
            FROM pengaduans p
            INNER JOIN tindak_lanjuts tl ON tl.pengaduan_id = p.id
            WHERE tl.tanggal_selesai IS NOT NULL
              AND p.status = 'selesai'
              AND EXTRACT(YEAR FROM p.tgl_pengaduan) = :tahun
        ", ['tahun' => $tahun]);

        $avgWaktuSeksi = DB::select("
            SELECT
                p.seksi_tujuan AS seksi,
                ROUND(AVG(EXTRACT(EPOCH FROM (tl.tanggal_selesai - p.tgl_pengaduan)) / 86400.0), 2) AS avg_hari,
                COUNT(*) AS sampel
            FROM pengaduans p
            INNER JOIN tindak_lanjuts tl ON tl.pengaduan_id = p.id
            WHERE tl.tanggal_selesai IS NOT NULL
              AND p.status = 'selesai'
              AND EXTRACT(YEAR FROM p.tgl_pengaduan) = :tahun
            GROUP BY p.seksi_tujuan
            ORDER BY avg_hari ASC
        ", ['tahun' => $tahun]);

        // ── Distribusi kanal & jenis ──────────────────────────
        $distribusiKanal = DB::select("
            SELECT kanal_pengaduan AS kanal, COUNT(*) AS total,
                ROUND(COUNT(*)::numeric / NULLIF(SUM(COUNT(*)) OVER(), 0) * 100, 1) AS pct
            FROM pengaduans
            WHERE EXTRACT(YEAR FROM tgl_pengaduan) = :tahun
            GROUP BY kanal_pengaduan ORDER BY total DESC
        ", ['tahun' => $tahun]);

        $distribusiJenis = DB::select("
            SELECT jenis_layanan, COUNT(*) AS total
            FROM pengaduans
            WHERE EXTRACT(YEAR FROM tgl_pengaduan) = :tahun
            GROUP BY jenis_layanan
        ", ['tahun' => $tahun]);

        // ── SLA Alert Summary ─────────────────────────────────
        $slaAlert = DB::select("
            SELECT
                seksi_tujuan AS seksi,
                COUNT(*) FILTER (WHERE status != 'selesai' AND deadline_tindak_lanjut < NOW()) AS jumlah_over,
                COUNT(*) FILTER (WHERE status != 'selesai' AND deadline_tindak_lanjut BETWEEN NOW() AND NOW() + INTERVAL '24 hours') AS jumlah_warn,
                ROUND(COUNT(*) FILTER (WHERE status != 'selesai' AND deadline_tindak_lanjut < NOW())::numeric / NULLIF(COUNT(*) FILTER (WHERE status != 'selesai'), 0) * 100, 1) AS risk_percentage
            FROM pengaduans
            WHERE status != 'selesai'
            AND (deadline_tindak_lanjut < NOW() OR deadline_tindak_lanjut BETWEEN NOW() AND NOW() + INTERVAL '24 hours')
            GROUP BY seksi_tujuan
            ORDER BY jumlah_over DESC, jumlah_warn DESC
        ");

        $slaAlert = collect($slaAlert)->map(function ($item) {
            $item->severity       = $item->jumlah_over > 0 ? 'danger' : 'warning';
            $item->priority_score = ($item->jumlah_over * 5) + ($item->jumlah_warn * 2);
            $item->label          = $item->jumlah_over > 0
                ? "{$item->jumlah_over} Aduan Over SLA"
                : "{$item->jumlah_warn} Deadline Mendekati";

            return $item;
        });

        // ── Riwayat peringatan ────────────────────────────────
        $riwayatAlert = Notifikasi::with('penerima:id,name')
            ->where('from_user_id', Auth::id())
            ->where('tipe', 'alert')
            ->latest()
            ->limit(15)
            ->get();

        // ── Tahun tersedia ────────────────────────────────────
        $tahunList = DB::select("
            SELECT DISTINCT EXTRACT(YEAR FROM tgl_pengaduan)::int AS tahun
            FROM pengaduans ORDER BY tahun DESC
        ");
        $tahunList = array_column($tahunList, 'tahun');
        if (!in_array(now()->year, $tahunList)) {
            array_unshift($tahunList, now()->year);
        }

        return view('dashboard.kakanim', compact(
            'scorecard', 'tren', 'tahun', 'tahunList',
            'distribusiSeksi',
            'bulanLabel', 'masukPerBulan', 'selesaiPerBulan',
            'avgWaktu', 'avgWaktuSeksi',
            'distribusiKanal', 'distribusiJenis',
            'slaAlert', 'riwayatAlert'
        ));
    }

    /**
     * AJAX — Ambil daftar tiket berdasarkan seksi (Layer 1)
     */
    public function sectionTickets(string $section)
    {
        $section = urldecode($section);
        $now = Carbon::now();

        $tickets = Pengaduan::query()
            ->where('seksi_tujuan', $section)
            ->where('status', '!=', 'selesai')
            ->where(function ($q) use ($now) {
                $q->where('deadline_tindak_lanjut', '<', $now)
                  ->orWhereBetween('deadline_tindak_lanjut', [$now, $now->copy()->addHours(24)]);
            })
            ->latest('deadline_tindak_lanjut')
            ->get(['id', 'nomor_tiket', 'nama', 'status', 'deadline_tindak_lanjut', 'tgl_pengaduan'])
            ->map(function ($ticket) use ($now) {
                $deadline = Carbon::parse($ticket->deadline_tindak_lanjut);
                $isOver   = $deadline->isPast();

                $ticket->severity       = $isOver ? 'danger' : 'warning';
                $ticket->sla_status     = $isOver ? 'OVER SLA' : 'APPROACHING';
                $ticket->deadline_human = $deadline->translatedFormat('d M Y H:i');
                
                return $ticket;
            });

        return response()->json($tickets);
    }

    /**
     * AJAX — Detail lengkap satu tiket (Layer 2)
     */
    public function ticketDetail($id)
    {
        try {
            // Cukup cari berdasarkan ID, tanpa memanggil fungsi relasi with() yang tidak ada
            $pengaduan = Pengaduan::findOrFail($id);

            return response()->json([
                'id'              => $pengaduan->id,
                'nomor_tiket'     => $pengaduan->nomor_tiket,
                'status'          => $pengaduan->status,
                'severity'        => $pengaduan->severity ?? 'warning',
                'nama'            => $pengaduan->nama, 
                'aduan'           => $pengaduan->aduan, 
                
                // Mengambil string langsung dari kolom tabel pengaduan
                'kanal_pengaduan' => $pengaduan->kanal_pengaduan ?? '—',
                'jenis_layanan'   => $pengaduan->jenis_layanan ?? '—',
                'seksi_tujuan'    => $pengaduan->seksi_tujuan ?? '—',
                
                'deadline_human'  => $pengaduan->deadline_tindak_lanjut ? $pengaduan->deadline_tindak_lanjut->format('d M Y H:i') : '—',
                'sla_reason'      => $pengaduan->keterangan_admin ?? '—'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan di server.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST — Kirim Alert Notifikasi
     */
    public function sendAlert(Request $request)
    {   
        $validated = $request->validate([
            'target_seksi' => ['required', 'string', 'max:100'],
            'pesan'        => ['required', 'string', 'min:10', 'max:1000'],
            'tipe_alert'   => ['required', 'in:over_sla,warn_sla,umum'],
        ]);

        $pengirim = Auth::user();
        $targetSeksi = $validated['target_seksi'];

        $stats = DB::selectOne("
            SELECT
                COUNT(*) FILTER (WHERE deadline_tindak_lanjut < NOW()) AS jumlah_over,
                COUNT(*) FILTER (WHERE deadline_tindak_lanjut BETWEEN NOW() AND NOW() + INTERVAL '24 hours') AS jumlah_warn
            FROM pengaduans
            WHERE seksi_tujuan = :seksi AND status != 'selesai'
        ", ['seksi' => $targetSeksi]);

        $judul = match ($validated['tipe_alert']) {
            'over_sla' => "🚨 Peringatan SLA: {$stats->jumlah_over} aduan di {$targetSeksi}",
            'warn_sla' => "⚠️ Pengingat SLA: {$stats->jumlah_warn} aduan di {$targetSeksi}",
            default    => "📢 Pesan Pimpinan: {$targetSeksi}",
        };

        $targetUsers = User::whereHas('profile', fn($q) =>
            $q->where('role', 'seksi')->where('seksi', $targetSeksi)
        )->get();

        DB::transaction(function () use ($pengirim, $targetSeksi, $validated, $judul, $targetUsers, $stats) {
            $meta = [
                'jumlah_over_sla' => (int)$stats->jumlah_over,
                'jumlah_warn_sla' => (int)$stats->jumlah_warn,
                'dikirim_oleh'    => $pengirim->name,
                'tipe_alert'      => $validated['tipe_alert']
            ];

            if ($targetUsers->isNotEmpty()) {
                foreach ($targetUsers as $user) {
                    Notifikasi::create([
                        'from_user_id' => $pengirim->id,
                        'to_user_id'   => $user->id,
                        'target_seksi' => $targetSeksi,
                        'tipe'         => 'alert',
                        'judul'        => $judul,
                        'pesan'        => $validated['pesan'],
                        'meta'         => $meta,
                    ]);
                }
            } else {
                Notifikasi::create([
                    'from_user_id' => $pengirim->id,
                    'to_user_id'   => null,
                    'target_seksi' => $targetSeksi,
                    'tipe'         => 'alert',
                    'judul'        => $judul,
                    'pesan'        => $validated['pesan'],
                    'meta'         => array_merge($meta, ['catatan' => 'Belum ada user terdaftar']),
                ]);
            }
        });

        return back()->with('success', "Peringatan dikirim ke Seksi {$targetSeksi}.");
    }

    /**
     * POST — Mark as read
     */
    public function markRead(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->from_user_id === Auth::id() || $notifikasi->to_user_id === Auth::id(), 403);
        $notifikasi->markAsRead();
        return response()->json(['ok' => true]);
    }
}