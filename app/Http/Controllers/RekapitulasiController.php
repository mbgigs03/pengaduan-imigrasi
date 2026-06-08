<?php

namespace App\Http\Controllers;

use App\Exports\PengaduanExport;
use App\Repositories\PengaduanRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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
            'status'     => 'nullable|in:pending,proses,diteruskan,ditolak,selesai', // 🟢 BERES: Tambah 'ditolak'
            'kanal'      => 'nullable|string|max:50',
            'seksi'      => 'nullable|string|max:50', 
        ], [
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            'start_date.required_if'  => 'Tanggal awal wajib diisi untuk rentang custom.',
            'end_date.required_if'    => 'Tanggal akhir wajib diisi untuk rentang custom.',
        ]);

        // Gabungkan filter dengan scope seksi (menggunakan buildFilters baru)
        $filters = $this->buildFilters($validated);

        $pengaduans    = $this->repo->paginate($filters);
        $summary       = $this->repo->summary($filters);
        $periodeLabel  = $this->repo->periodeLabel($filters);

        return view('rekapitulasi.index', compact(
            'pengaduans', 'summary', 'periodeLabel', 'filters'
        ));
    }
    
    // ═══════════════════════════════════════════════════════════
    // EXPORT PDF — Download file .pdf
    // ═══════════════════════════════════════════════════════════
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'periode'    => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:periode,custom',
            'end_date'   => 'nullable|date|required_if:periode,custom|after_or_equal:start_date',
            'status'     => 'nullable|in:pending,proses,diteruskan,ditolak,selesai', // 🟢 BERES: Tambah 'ditolak'
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
    // ═══════════════════════════════════════════════════════════
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'periode'    => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'nullable|date|required_if:periode,custom',
            'end_date'   => 'nullable|date|required_if:periode,custom|after_or_equal:start_date',
            'status'     => 'nullable|in:pending,proses,diteruskan,ditolak,selesai', // 🟢 BERES: Tambah 'ditolak'
            'kanal'      => 'nullable|string|max:50',
            'seksi'      => 'nullable|string|max:50',
        ]);

        $filters  = $this->buildFilters($validated);
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
    // BUILD FILTERS — Helper Scope Keamanan & Grouping Sub-Seksi
    // ═══════════════════════════════════════════════════════════
    private function buildFilters(array $validated): array
    {
        $user = Auth::user();
        $filters = $validated;

        // Ambil role dan seksi secara aman (antisipasi jika di tabel users langsung atau via relasi profile)
        $userRole  = $user->profile->role ?? $user->role;
        $userSeksi = $user->profile->seksi ?? $user->seksi;

        // ═══════════════════════════════════════
        // 1. HANDLE ROLE & GROUPING SEKSI (Aman untuk Sub-Seksi)
        // ═══════════════════════════════════════
        if ($userRole === 'seksi' || $userRole === 'admin') {
            
            // Mengunci hak akses petugas seksi agar otomatis menarik sub-seksinya sekalian
            if (strcasecmp($userSeksi, 'Doklanintalkim') === 0 || str_contains(strtolower($userSeksi), 'doklan')) {
                $filters['seksi_group'] = ['Doklanintalkim', 'Doklan_Paspor', 'Doklan_Izin'];
            } elseif (strcasecmp($userSeksi, 'Inteldakim') === 0 || str_contains(strtolower($userSeksi), 'intel')) {
                $filters['seksi_group'] = ['Inteldakim', 'Intel_WNA', 'Intel_BAP'];
            } elseif (strcasecmp($userSeksi, 'Tata Usaha') === 0 || str_contains(strtolower($userSeksi), 'usaha')) {
                $filters['seksi_group'] = ['Tata Usaha', 'Sarana Prasarana'];
            } else {
                $filters['seksi'] = $userSeksi;
            }

        } elseif ($userRole === 'tikkim' || $userRole === 'kakanim') {
            // Role pimpinan bebas memilih filter seksi induk manapun lewat dropdown frontend web
            if (!empty($validated['seksi'])) {
                $seksiFilter = $validated['seksi'];

                if ($seksiFilter === 'Doklanintalkim') {
                    $filters['seksi_group'] = ['Doklanintalkim', 'Doklan_Paspor', 'Doklan_Izin'];
                } elseif ($seksiFilter === 'Inteldakim') {
                    $filters['seksi_group'] = ['Inteldakim', 'Intel_WNA', 'Intel_BAP'];
                } elseif ($seksiFilter === 'Tata Usaha') {
                    $filters['seksi_group'] = ['Tata Usaha', 'Sarana Prasarana'];
                } else {
                    $filters['seksi'] = $seksiFilter;
                }
            }
        }

        // ═══════════════════════════════════════
        // 2. DEFAULT PERIODE TIMESTAMPS
        // ═══════════════════════════════════════
        $periode = $filters['periode'] ?? 'monthly';
        $now = Carbon::now();

        switch ($periode) {
            case 'daily':
                $filters['start_date'] = $now->copy()->startOfDay();
                $filters['end_date']   = $now->copy()->endOfDay();
                break;

            case 'weekly':
                $filters['start_date'] = $now->copy()->subDays(6)->startOfDay();
                $filters['end_date']   = $now->copy()->endOfDay();
                break;

            case 'monthly':
                $filters['start_date'] = $now->copy()->startOfMonth();
                $filters['end_date']   = $now->copy()->endOfMonth();
                break;

            case 'yearly':
                $filters['start_date'] = $now->copy()->startOfYear();
                $filters['end_date']   = $now->copy()->endOfYear();
                break;

            case 'custom':
                // Memakai input manual user (start_date & end_date) yang sudah lolos validasi request
                break;

            default:
                $filters['start_date'] = null;
                $filters['end_date']   = null;
                break;
        }

        return $filters;
    }
}