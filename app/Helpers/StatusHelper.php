<?php
// app/Helpers/StatusHelper.php
// Satu sumber kebenaran untuk semua label & warna status pengaduan.
// Gunakan di mana saja: StatusHelper::label($status), StatusHelper::badgeClass($status)

namespace App\Helpers;

class StatusHelper
{
    /**
     * Label tampilan sesuai SOP RAP.
     * Nilai DB tidak berubah — hanya label UI yang berubah.
     */
    public static function label(string $status): string
    {
        return match($status) {
            'pending'    => 'Menunggu Verifikasi',
            'proses'     => 'Disposisi Kasi',
            'diteruskan' => 'Sedang Ditindaklanjuti',
            'selesai'    => 'Selesai',
            default      => ucfirst($status),
        };
    }

    /**
     * Tailwind badge classes — konsisten dengan sistem dashboard.
     */
    public static function badgeClass(string $status): string
    {
        return match($status) {
            'pending'    => 'badge-pending',
            'proses'     => 'badge-proses',
            'diteruskan' => 'badge-diteruskan',
            'selesai'    => 'badge-selesai',
            default      => '',
        };
    }

    /**
     * Semua status sebagai array [value => label] — untuk dropdown/select.
     */
    public static function options(): array
    {
        return [
            'pending'    => 'Menunggu Verifikasi',
            'proses'     => 'Disposisi Kasi',
            'diteruskan' => 'Sedang Ditindaklanjuti',
            'selesai'    => 'Selesai',
        ];
    }

    /**
     * Warna ikon/emoji untuk modal tindak lanjut.
     */
    public static function modalMeta(string $status): array
    {
        return match($status) {
            'proses'     => ['icon' => '🔄', 'label' => 'Disposisi Kasi',        'active' => 'border-blue-400 bg-blue-50 text-blue-700'],
            'diteruskan' => ['icon' => '📤', 'label' => 'Sedang Ditindaklanjuti','active' => 'border-purple-400 bg-purple-50 text-purple-700'],
            'selesai'    => ['icon' => '✅', 'label' => 'Selesai',               'active' => 'border-emerald-400 bg-emerald-50 text-emerald-700'],
            default      => ['icon' => '⏳', 'label' => ucfirst($status),        'active' => 'border-slate-400 bg-slate-50 text-slate-700'],
        };
    }
}