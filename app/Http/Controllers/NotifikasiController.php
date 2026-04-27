<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    // ── Halaman daftar semua notif milik user ─────────────────
    public function index()
    {
        $notifs = Notifikasi::where(function ($q) {
                $q->where('to_user_id', Auth::id())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('to_user_id')
                         ->where('target_seksi', Auth::user()->profile->seksi);
                  });
            })
            ->latest()
            ->paginate(20);

        // LOGIKA AUTO-READ DIHAPUS DARI SINI
        // Agar indikator belum dibaca (titik merah) di Blade tetap muncul.

        return view('notifikasi.index', compact('notifs'));
    }

    // ── Tandai satu notif sebagai dibaca (AJAX) ───────────────
    public function markRead(Notifikasi $notifikasi)
    {
        abort_unless(
            $notifikasi->to_user_id === Auth::id()
            || ($notifikasi->to_user_id === null
                && $notifikasi->target_seksi === Auth::user()->profile->seksi),
            403
        );

        $notifikasi->markAsRead();
        return response()->json(['ok' => true]);
    }

    // ── Tandai semua notif sebagai dibaca (AJAX) ──────────────
    public function markAllRead()
    {
        Notifikasi::where(function ($q) {
                $q->where('to_user_id', Auth::id())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('to_user_id')
                         ->where('target_seksi', Auth::user()->profile->seksi);
                  });
            })
            ->whereNull('dibaca_at')
            ->update(['dibaca_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ── Jumlah notif belum dibaca (AJAX polling) ──────────────
    public function count()
    {
        $count = Notifikasi::where(function ($q) {
                $q->where('to_user_id', Auth::id())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('to_user_id')
                         ->where('target_seksi', Auth::user()->profile->seksi);
                  });
            })
            ->whereNull('dibaca_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}