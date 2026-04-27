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
    // DASHBOARD — data strategis + panel SLA alert
    // ═══════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);

        // ── Scorecard (1 query) ───────────────────────────────
        $scorecard = DB::selectOne("
            SELECT
                COUNT(*)                                                          AS total,
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
        $trenBulanan     = DB::select("
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

        // ── Panel SLA Alert: seksi dengan kondisi gawat ───────
        // Menggabungkan over SLA + H-1 dalam satu query
        $slaAlert = DB::select("
            SELECT
                seksi_tujuan AS seksi,
                COUNT(*) FILTER (WHERE deadline_tindak_lanjut < NOW())
                    AS jumlah_over,
                COUNT(*) FILTER (
                    WHERE deadline_tindak_lanjut BETWEEN NOW()
                      AND NOW() + INTERVAL '24 hours'
                )           AS jumlah_warn,
                MIN(deadline_tindak_lanjut) FILTER (WHERE deadline_tindak_lanjut < NOW())
                    AS deadline_paling_lama,
                ARRAY_AGG(nomor_tiket ORDER BY deadline_tindak_lanjut ASC)
                    FILTER (WHERE deadline_tindak_lanjut < NOW() OR
                                  deadline_tindak_lanjut <= NOW() + INTERVAL '24 hours')
                    AS tiket_terdampak
            FROM pengaduans
            WHERE status != 'selesai'
              AND (
                  deadline_tindak_lanjut < NOW()
                  OR deadline_tindak_lanjut BETWEEN NOW() AND NOW() + INTERVAL '24 hours'
              )
            GROUP BY seksi_tujuan
            ORDER BY jumlah_over DESC, jumlah_warn DESC
        ");

        // ── Riwayat peringatan yang sudah dikirim Kakanim ─────
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

    // ═══════════════════════════════════════════════════════════
    // SEND ALERT — POST /kakanim/alert
    // Kirim peringatan ke semua admin seksi yang bersangkutan
    // ═══════════════════════════════════════════════════════════
    public function sendAlert(Request $request)
    {
        $validated = $request->validate([
            'target_seksi' => ['required', 'string', 'max:100'],
            'pesan'        => ['required', 'string', 'min:10', 'max:1000'],
            'tipe_alert'   => ['required', 'in:over_sla,warn_sla,umum'],
        ], [
            'target_seksi.required' => 'Pilih seksi tujuan.',
            'pesan.required'        => 'Isi pesan peringatan.',
            'pesan.min'             => 'Pesan minimal 10 karakter.',
            'tipe_alert.required'   => 'Pilih jenis peringatan.',
        ]);

        $pengirim    = Auth::user();
        $targetSeksi = $validated['target_seksi'];
        $tipeAlert   = $validated['tipe_alert'];

        // Hitung konteks SLA untuk meta
        $stats = DB::selectOne("
            SELECT
                COUNT(*) FILTER (WHERE deadline_tindak_lanjut < NOW())         AS jumlah_over,
                COUNT(*) FILTER (WHERE deadline_tindak_lanjut BETWEEN NOW()
                    AND NOW() + INTERVAL '24 hours')                           AS jumlah_warn
            FROM pengaduans
            WHERE seksi_tujuan = :seksi AND status != 'selesai'
        ", ['seksi' => $targetSeksi]);

        // Susun judul otomatis berdasarkan tipe
        $judul = match ($tipeAlert) {
            'over_sla'  => "🚨 Peringatan: {$stats->jumlah_over} aduan melewati SLA di Seksi {$targetSeksi}",
            'warn_sla'  => "⚠️ Pengingat: {$stats->jumlah_warn} aduan mendekati tenggat di Seksi {$targetSeksi}",
            default     => "📢 Pemberitahuan dari Pimpinan untuk Seksi {$targetSeksi}",
        };

        // Cari semua admin seksi target
        $targetUsers = User::whereHas('profile', fn($q) =>
            $q->where('role', 'seksi')->where('seksi', $targetSeksi)
        )->get();

        $meta = [
            'jumlah_over_sla' => (int) ($stats->jumlah_over ?? 0),
            'jumlah_warn_sla' => (int) ($stats->jumlah_warn ?? 0),
            'dikirim_oleh'    => $pengirim->name,
            'jabatan'         => 'Kepala Kantor Imigrasi',
            'tipe_alert'      => $tipeAlert,
        ];

        DB::transaction(function () use (
            $pengirim, $targetSeksi, $validated, $judul, $meta, $targetUsers
        ) {
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
                // Broadcast ke seksi meski belum ada user terdaftar
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

        $jumlahPenerima = $targetUsers->count() ?: 1;

        return back()->with(
            'success',
            "Peringatan berhasil dikirim ke Seksi {$targetSeksi} ({$jumlahPenerima} penerima)."
        );
    }

    // ═══════════════════════════════════════════════════════════
    // MARK NOTIF READ — POST /kakanim/notif/{id}/baca
    // ═══════════════════════════════════════════════════════════
    public function markRead(Notifikasi $notifikasi)
    {
        // Hanya pengirim atau penerima yang bisa mark read
        abort_unless(
            $notifikasi->from_user_id === Auth::id()
            || $notifikasi->to_user_id === Auth::id(),
            403
        );

        $notifikasi->markAsRead();

        return response()->json(['ok' => true]);
    }
}