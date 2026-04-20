<?php

namespace App\Exports\Sheets;

use App\Repositories\PengaduanRepository;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RekapitulasiExport implements FromCollection, WithColumnWidths, WithStyles, WithEvents
{
    private $data;
    private $summary;
    private $periode;
    private $totalRows;

    public function __construct(
        private readonly array $filters,
        private readonly PengaduanRepository $repo,
    ) {
        $this->data = $this->repo->buildQuery($this->filters)->get();
        $this->summary = $this->repo->summary($this->filters);
        $this->periode = $this->repo->periodeLabel($this->filters);
        $this->totalRows = $this->data->count();
    }

    public function collection()
    {
        $rows = collect();

        // 1. Header Instansi (Tetap di atas)
        $rows->push(['REKAPITULASI PENGADUAN LAYANAN KEIMIGRASIAN']);
        $rows->push(['Kantor Imigrasi Kelas II Non TPI Madiun']);
        $rows->push(['Periode: ' . $this->periode]);
        $rows->push(['Dicetak: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB']);
        $rows->push(['']); 

        // 2. Header Tabel Utama
        $rows->push([
            'No.', 'Nomor Tiket', 'Tanggal', 'Nama Pemohon', 'NIK', 'WhatsApp', 
            'Seksi Tujuan', 'Kanal', 'Jenis Layanan', 'Status', 'Deadline SLA', 'Keterangan'
        ]);

        // 3. Isi Tabel Utama
        foreach ($this->data as $index => $row) {
            $rows->push([
                $index + 1,
                $row->nomor_tiket,
                Carbon::parse($row->tgl_pengaduan)->format('d/m/Y'),
                $row->nama,
                $row->nik,
                $row->whatsapp,
                $row->seksi_tujuan,
                $row->kanal_pengaduan,
                ucfirst($row->jenis_layanan),
                strtoupper($row->status),
                Carbon::parse($row->deadline_tindak_lanjut)->format('d/m/Y'),
                $row->keterangan_admin ?? '-',
            ]);
        }

        // 4. Jeda Baris
        $rows->push(['']);

        // 5. Tabel Ringkasan (Dipaksa ke kolom J, K, L menggunakan array padding)
        $total = $this->summary['total'];
        $getWeight = fn($val) => ($total <= 0) ? '0%' : round(($val / $total) * 100, 1) . '%';

        // Helper untuk mendorong data ke kolom J (indeks ke-9)
        $pushToRight = function($col1, $col2, $col3) {
            return ['', '', '', '', '', '', '', '', '', $col1, $col2, $col3];
        };

        $rows->push($pushToRight('TABEL RINGKASAN', '', '')); // Judul Ringkasan
        $rows->push($pushToRight('Indikator', 'Jumlah', 'Persentase'));
        $rows->push($pushToRight('Total Pengaduan', $total, '100%'));
        $rows->push($pushToRight('Pending', $this->summary['pending'], $getWeight($this->summary['pending'])));
        $rows->push($pushToRight('Proses', $this->summary['proses'], $getWeight($this->summary['proses'])));
        $rows->push($pushToRight('Diteruskan', $this->summary['diteruskan'], $getWeight($this->summary['diteruskan'])));
        $rows->push($pushToRight('Selesai', $this->summary['selesai'], $getWeight($this->summary['selesai'])));
        $rows->push($pushToRight('Melebihi SLA', $this->summary['sla_over'], $getWeight($this->summary['sla_over'])));

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRowMain = 6 + $this->totalRows;
        $titleRow = $lastRowMain + 2;
        $headerRow = $lastRowMain + 3;

        // Merge Header Atas
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        $sheet->mergeCells('A4:L4');

        // Merge Judul Ringkasan di pojok kanan (J-L)
        $sheet->mergeCells("J{$titleRow}:L{$titleRow}");

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            
            // Header Tabel Utama
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F3460']],
            ],

            // Judul Ringkasan (Pojok Kanan)
            $titleRow => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],

            // Header Tabel Ringkasan (Kolom J-L)
            $headerRow => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRowMain = 6 + $this->totalRows;
                $startRingkasan = $lastRowMain + 3;
                $endRingkasan = $startRingkasan + 6;

                // Border Tabel Utama (A-L)
                $event->sheet->getStyle("A6:L{$lastRowMain}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Border Tabel Ringkasan (J-L)
                $event->sheet->getStyle("J{$startRingkasan}:L{$endRingkasan}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Center alignment untuk angka di ringkasan
                $event->sheet->getStyle("K{$startRingkasan}:L{$endRingkasan}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6, 'B' => 20, 'C' => 15, 'D' => 25, 'E' => 18, 'F' => 15,
            'G' => 25, 'H' => 15, 'I' => 15, 'J' => 25, 'K' => 12, 'L' => 14,
        ];
    }
}