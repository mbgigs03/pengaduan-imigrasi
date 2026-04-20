<?php
namespace App\Exports\Sheets;
 
use App\Repositories\PengaduanRepository;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
 
class DetailSheet implements FromQuery, WithTitle, WithHeadings,
    WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    public function __construct(
        private readonly array $filters,
        private readonly PengaduanRepository $repo,
    ) {}
 
    public function title(): string { return 'Data Pengaduan'; }
 
    // WithChunkReading: proses 500 baris per chunk — hemat RAM
    public function chunkSize(): int { return 500; }
 
    public function query()
    {
        return $this->repo->buildQuery($this->filters);
    }
 
    public function headings(): array
    {
        return [
            'No.',
            'Nomor Tiket',
            'Tanggal Pengaduan',
            'Nama Pemohon',
            'NIK',
            'No. WhatsApp',
            'Seksi Tujuan',
            'Kanal',
            'Jenis Layanan',
            'Status',
            'Deadline SLA',
            'Keterangan Petugas',
        ];
    }
 
    private int $rowNo = 1;
 
    public function map($row): array
    {
        return [
            $this->rowNo++,
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
        ];
    }
 
    public function styles(Worksheet $sheet): array
    {
        return [
            // Header baris pertama
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F3460']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }
 
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No.
            'B' => 22,  // Nomor Tiket
            'C' => 16,  // Tanggal
            'D' => 28,  // Nama
            'E' => 18,  // NIK
            'F' => 16,  // WA
            'G' => 26,  // Seksi
            'H' => 16,  // Kanal
            'I' => 16,  // Jenis
            'J' => 12,  // Status
            'K' => 14,  // Deadline
            'L' => 40,  // Keterangan
        ];
    }
}
 