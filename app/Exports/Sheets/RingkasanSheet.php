<?php
namespace App\Exports\Sheets;
 
use App\Repositories\PengaduanRepository;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
 
class RingkasanSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private readonly array $filters,
        private readonly PengaduanRepository $repo,
    ) {}
 
    public function title(): string
    {
        return 'Ringkasan';
    }
 
    public function array(): array
    {
        $summary = $this->repo->summary($this->filters);
        $periode = $this->repo->periodeLabel($this->filters);
        $pct     = $summary['total'] > 0
            ? round($summary['selesai'] / $summary['total'] * 100, 1)
            : 0;
 
        return [
            // Baris header instansi
            ['REKAPITULASI PENGADUAN LAYANAN KEIMIGRASIAN'],
            ['Kantor Imigrasi Kelas II Non TPI Madiun'],
            ['Periode: ' . $periode],
            ['Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB'],
            [], // baris kosong
 
            // Header tabel ringkasan
            ['Indikator', 'Jumlah', 'Persentase'],
 
            // Data
            ['Total Pengaduan',         $summary['total'],      '100%'],
            ['Pending',                 $summary['pending'],    $summary['total'] > 0 ? round($summary['pending']    / $summary['total'] * 100, 1).'%' : '-'],
            ['Proses',                  $summary['proses'],     $summary['total'] > 0 ? round($summary['proses']     / $summary['total'] * 100, 1).'%' : '-'],
            ['Diteruskan ke Atasan',    $summary['diteruskan'], $summary['total'] > 0 ? round($summary['diteruskan'] / $summary['total'] * 100, 1).'%' : '-'],
            ['Selesai',                 $summary['selesai'],    $pct.'%'],
            ['Melebihi SLA (3 hari)',   $summary['sla_over'],   $summary['total'] > 0 ? round($summary['sla_over']  / $summary['total'] * 100, 1).'%' : '-'],
        ];
    }
 
    public function styles(Worksheet $sheet): array
    {
        // Judul instansi — bold, merge, center
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('A3:C3');
        $sheet->mergeCells('A4:C4');
 
        return [
            1  => ['font' => ['bold' => true, 'size' => 13], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2  => ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3  => ['font' => ['italic' => true],             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            4  => ['font' => ['italic' => true, 'color' => ['argb' => 'FF888888']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
 
            // Header tabel
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Baris Total — bold
            7 => ['font' => ['bold' => true]],
            // Baris Selesai — hijau
            11 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']]],
            // Baris SLA Over — merah muda
            12 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']]],
        ];
    }
 
    public function columnWidths(): array
    {
        return ['A' => 35, 'B' => 12, 'C' => 14];
    }
}