<?php

namespace App\Http\Controllers;

use App\Exports\PengaduanExport;
use App\Repositories\PengaduanRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapitulasiController extends Controller
{
    public function __construct(
        private readonly PengaduanRepository $repo
    ) {}

    // ═══════════════════════════════════════════════════════════
    // INDEX — Halaman filter + tabel preview
    // ═══════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        // Validasi filter dari form
        $validated = $request->validate([
            'periode'    => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:periode,custom',
            'end_date'   => 'nullable|date|required_if:periode,custom|after_or_equal:start_date',
            'status'     => 'nullable|in:pending,proses,diteruskan,selesai',
            'kanal'      => 'nullable|string|max:50',
            'seksi'     => 'nullable|string|max:50', // Hanya untuk role 'tikkim'
        ], [
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            'start_date.required_if'  => 'Tanggal awal wajib diisi untuk rentang custom.',
            'end_date.required_if'    => 'Tanggal akhir wajib diisi untuk rentang custom.',
        ]);

        // Gabungkan filter dengan scope seksi (untuk role 'seksi')
        $filters = $this->buildFilters($validated);

        $pengaduans    = $this->repo->paginate($filters);
        $summary       = $this->repo->summary($filters);
        $periodeLabel  = $this->repo->periodeLabel($filters);

        return view('rekapitulasi.index', compact(
            'pengaduans', 'summary', 'periodeLabel', 'filters'
        ));
    }
    

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'periode'    => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:periode,custom',
            'end_date'   => 'nullable|date|required_if:periode,custom|after_or_equal:start_date',
            'status'     => 'nullable|in:pending,proses,diteruskan,selesai',
            'kanal'      => 'nullable|string|max:50',
            'seksi'      => 'nullable|string|max:50',
        ]);

        $filters = $this->buildFilters($validated);

        $data = $this->getFilteredData($filters);

        $pdf = Pdf::loadView('rekapitulasi.pdf', [
            ...$data,
            'filters' => $filters
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rekap-Pengaduan-' . now()->format('Ymd-His') . '.pdf');
    }

    // ═══════════════════════════════════════════════════════════
    // EXPORT EXCEL — Download file .xlsx
    // Middleware 'role:tikkim,seksi' sudah memproteksi route ini
    // ═══════════════════════════════════════════════════════════
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'periode'    => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:periode,custom',
            'end_date'   => 'nullable|date|required_if:periode,custom|after_or_equal:start_date',
            'status'     => 'nullable|in:pending,proses,diteruskan,selesai',
            'kanal'      => 'nullable|string|max:50',
        ]);

        $filters  = $this->buildFilters($validated);
        $periode  = $this->repo->periodeLabel($filters);
        $filename = 'Rekap-Pengaduan-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new PengaduanExport($filters, $this->repo),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

        private function getFilteredData(array $filters)
    {
        return [
            'pengaduans' => $this->repo->all($filters),
            'summary'    => $this->repo->summary($filters),
            'periode'    => $this->repo->periodeLabel($filters),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // BUILD FILTERS — tambahkan scope seksi otomatis
    // ═══════════════════════════════════════════════════════════
    private function buildFilters(array $validated): array
    {
        $user = Auth::user();
        $filters = $validated;

        // 1. Logika Hak Akses Berdasarkan Role
        if ($user->profile->role === 'seksi') {
            // Admin Seksi dikunci ke unit mereka sendiri
            $filters['seksi'] = $user->profile->seksi;
        } elseif ($user->profile->role === 'tikkim') {
            // Super Admin (Tikkim) bisa memilih seksi dari request
            $filters['seksi'] = $validated['seksi'] ?? null;
        }

        // 2. Default periode
        if (empty($filters['periode'])) {
            $filters['periode'] = 'monthly';
        }

        return $filters;
    }
}