<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Models\Pengaduan;
use App\Models\Tanggapan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // Tambahkan ini

class TindakLanjutController extends Controller
{

    // ─── Konstanta status yang valid ───────────────────────────────────────────
    private const VALID_STATUS = ['proses', 'diteruskan', 'ditolak', 'selesai'];

    // ─── Aturan validasi yang dipakai di store() maupun update() ───────────────
    private function validationRules(bool $requireBukti = false): array
    {
        return [
            'status_baru'     => ['required', 'in:' . implode(',', self::VALID_STATUS)],
            'catatan_petugas' => ['required', 'string', 'max:2000'],
            'bukti_gambar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'status_baru.required'      => 'Pilih status tindak lanjut.',
            'status_baru.in'            => 'Nilai status tidak valid.',
            'catatan_petugas.required'  => 'Catatan tindak lanjut wajib diisi.',
            'catatan_petugas.max'       => 'Catatan maksimal 2.000 karakter.',
            'bukti_gambar.image'        => 'File harus berupa gambar.',
            'bukti_gambar.max'          => 'Ukuran foto maksimal 10 MB.',
        ];
    }

    // ─── Helper: otorisasi seksi (DIPERBAIKI UNTUK TIKKIM) ─────────────────────
    private function authorizeAkses(Pengaduan $pengaduan)
{
    $user = Auth::user();
    
    // 🔥 PASTIKAN ROLE DIAMBIL DARI ACCESSOR
    $userRole = $user->role; // Ini akan otomatis ambil dari profile!
    
    // ✅ TIKKIM, KAKANIM, ADMIN LANGSUNG LOLOS
    if (in_array($userRole, ['tikkim', 'kakanim', 'admin'])) {
        return;
    }
    
    // Untuk role lain, cek akses berdasarkan seksi
    $userSeksi = strtolower(trim($user->seksi ?? ''));
    $seksiTujuan = strtolower(trim($pengaduan->seksi_tujuan));
    
    // Definisikan grup akses
    $grupAkses = [
        'doklanintalkim' => ['doklanintalkim', 'doklan_izin', 'doklan_paspor'],
        'inteldakim' => ['inteldakim', 'intel_wna', 'intel_bap'],
    ];
    
    $allowedSeksi = $grupAkses[$userSeksi] ?? [$userSeksi];
    
    if (!in_array($seksiTujuan, $allowedSeksi)) {
        abort(403, "Anda tidak memiliki akses untuk menindaklanjuti pengaduan seksi {$pengaduan->seksi_tujuan}");
    }
}
    // ─── Helper: upload bukti gambar ───────────────────────────────────────────
    private function uploadBukti(Request $request, Pengaduan $pengaduan): ?string
    {
        if (! $request->hasFile('bukti_gambar')) return null;

        $file = $request->file('bukti_gambar');
        $path = $file->storeAs(
            "pengaduan/{$pengaduan->nomor_tiket}", 
            'bukti-tindak-lanjut.' . $file->getClientOriginalExtension(), 
            'supabase'
        );

        return $path;
    }

    // ───────────────────────────────────────────────────────────────────────────
    // STORE — Buat tindak lanjut baru
    // ───────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate(
            array_merge(['pengaduan_id' => 'required|exists:pengaduans,id'], $this->validationRules()),
            $this->validationMessages()
        );

        $pengaduan = Pengaduan::findOrFail($data['pengaduan_id']);
        $this->authorizeAkses($pengaduan);

        $buktiPath      = $this->uploadBukti($request, $pengaduan);
        $tanggalSelesai = $data['status_baru'] === 'selesai' ? Carbon::now() : null;
        $user           = Auth::user();

        DB::transaction(function () use ($pengaduan, $data, $buktiPath, $tanggalSelesai, $user) {
            // ✅ One-to-One: satu pengaduan → satu TindakLanjut
            TindakLanjut::updateOrCreate(
                ['pengaduan_id' => $pengaduan->id],
                [
                    'catatan_petugas' => $data['catatan_petugas'],
                    'tanggal_selesai' => $tanggalSelesai,
                    'petugas_id'      => $user->id,
                    ...($buktiPath ? ['bukti_gambar' => $buktiPath] : []),
                ]
            );

            $pengaduan->update([
                'status'           => $data['status_baru'],
                'keterangan_admin' => $data['catatan_petugas'],
                'updated_by'       => $user->id,
            ]);

            // Simpan ke timeline/tanggapan
            $pengaduan->tanggapans()->create([
                'user_id' => $user->id,
                'status'  => $this->statusLabel($data['status_baru']),
                'catatan' => $data['catatan_petugas'],
                'bukti_tanggapan' => $buktiPath,
            ]);
        });

        return back()->with('success', "Tindak lanjut tiket {$pengaduan->nomor_tiket} berhasil disimpan.");
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────
    public function update(Request $request, TindakLanjut $tindakLanjut)
    {
        $data = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $user      = Auth::user();
        $pengaduan = $tindakLanjut->pengaduan;

        $this->authorizeAkses($pengaduan);

        $buktiPath = $this->uploadBukti($request, $pengaduan);

        DB::transaction(function () use ($tindakLanjut, $pengaduan, $data, $buktiPath, $user) {
            $tindakLanjut->update([
                'catatan_petugas' => $data['catatan_petugas'],
                'tanggal_selesai' => $data['status_baru'] === 'selesai' ? Carbon::now() : null,
                'petugas_id'      => $user->id,
                ...($buktiPath ? ['bukti_gambar' => $buktiPath] : []),
            ]);

            $pengaduan->update([
                'status'           => $data['status_baru'],
                'keterangan_admin' => $data['catatan_petugas'],
                'updated_by'       => $user->id,
            ]);

            $pengaduan->tanggapans()->create([
                'user_id' => $user->id,
                'status'  => $this->statusLabel($data['status_baru']),
                'catatan' => $data['catatan_petugas'],
                'bukti_tanggapan' => $buktiPath,
            ]);
        });

        return back()->with('success', 'Tindak lanjut berhasil diperbarui.');
    }

    // Helper label status untuk kolom tanggapans.status
    private function statusLabel(string $status): string
    {
        return [
            'proses'     => 'Sedang Ditindaklanjuti',
            'diteruskan' => 'Disposisi Kasi',
            'ditolak'    => 'Pengaduan Ditolak',
            'selesai'    => 'Selesai',
        ][$status] ?? $status;
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────
    public function destroy(TindakLanjut $tindakLanjut)
    {
        $user      = Auth::user();
        $pengaduan = $tindakLanjut->pengaduan;

        $this->authorizeAkses($pengaduan);

        DB::transaction(function () use ($tindakLanjut, $pengaduan) {
            $tindakLanjut->delete();

            $pengaduan->update([
                'status'           => 'pending',
                'keterangan_admin' => null,
            ]);
        });

        return back()->with('success', 'Tindak lanjut dihapus. Status aduan dikembalikan ke pending.');
    }
}
