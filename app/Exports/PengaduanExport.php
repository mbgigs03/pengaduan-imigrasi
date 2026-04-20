<?php

namespace App\Exports;

use App\Repositories\PengaduanRepository;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * PengaduanExport — Entry point ekspor multi-sheet
 * ─────────────────────────────────────────────────────────────
 * Sheet 1: Ringkasan statistik
 * Sheet 2: Data detail semua pengaduan (sesuai filter)
 *
 * Instalasi: composer require maatwebsite/excel
 */
class PengaduanExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly array $filters,
        private readonly PengaduanRepository $repo,
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\RekapitulasiExport($this->filters, $this->repo),
        ];
    }
}