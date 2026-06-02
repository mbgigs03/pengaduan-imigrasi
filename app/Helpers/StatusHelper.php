<?php
namespace App\Helpers;

/**
 * Class StatusHelper
 * Satu sumber kebenaran untuk manajemen label, warna badge, dan metadata status pengaduan.
 */
class StatusHelper
{
    // 💡 Definisikan semua mapping status dalam satu tempat agar mudah dirawat
    private static array $map = [
        'pending'    => [
            'label'  => 'Menunggu Verifikasi', 
            'badge'  => 'badge-pending', // sesuaikan dengan CSS class di dashboard kamu
            'icon'   => '⏳', 
            'active' => 'border-slate-400 bg-slate-50 text-slate-700'
        ],
        'proses'     => [
            'label'  => 'Sedang Ditindaklanjuti', 
            'badge'  => 'badge-proses', 
            'icon'   => '🔄', 
            'active' => 'border-blue-400 bg-blue-50 text-blue-700'
        ],
        'diteruskan' => [
            'label'  => 'Disposisi Kasi', 
            'badge'  => 'badge-diteruskan', 
            'icon'   => '📤', 
            'active' => 'border-purple-400 bg-purple-50 text-purple-700'
        ],
        'ditolak'    => [
            'label'  => 'Ditolak / Tidak Valid', 
            'badge'  => 'badge-danger bg-red-50 text-red-700 border-red-200', 
            'icon'   => '🚫', 
            'active' => 'border-red-400 bg-red-50 text-red-700'
        ],
        'selesai'    => [
            'label'  => 'Selesai', 
            'badge'  => 'badge-selesai', 
            'icon'   => '✅', 
            'active' => 'border-emerald-400 bg-emerald-50 text-emerald-700'
        ],
    ];

    /**
     * Mengambil label tampilan UI manusia berdasarkan nilai string database.
     */
    public static function label(string $status): string
    {
        return self::$map[$status]['label'] ?? ucfirst($status);
    }

    /**
     * Mengambil CSS Class untuk badge tabel dashboard.
     */
    public static function badgeClass(string $status): string
    {
        return self::$map[$status]['badge'] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Mengambil metadata (icon, label, style active) khusus untuk modal tindak lanjut (Alpine.js).
     */
    public static function modalMeta(string $status): array
    {
        return self::$map[$status] ?? [
            'label'  => ucfirst($status), 
            'icon'   => '❓', 
            'active' => 'border-gray-200 bg-gray-50 text-gray-500'
        ];
    }

    /**
     * Mengambil list status untuk dropdown filter atau pilihan looping.
     * Mengeluarkan array berformat: ['pending' => 'Menunggu Verifikasi', ...]
     */
    public static function options(): array
    {
        return array_map(fn($item) => $item['label'], self::$map);
    }
}