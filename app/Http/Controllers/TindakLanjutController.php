<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class TindakLanjutController extends Controller
{
    // ─── Konstanta status yang valid ───────────────────────────────────────────
    private const VALID_STATUS = ['proses', 'diteruskan', 'ditolak', 'selesai'];

    // ─── Aturan validasi yang dipakai di store() maupun update() ───────────────
    private function validationRules(bool $requireBukti = false): array
    {
        return [
            // ✅ SINKRON: name="status_baru" di form Blade
            'status_baru'     => ['required', 'in:' . implode(',', self::VALID_STATUS)],

            // ✅ SINKRON: name="catatan_petugas" di form Blade
            'catatan_petugas' => ['required', 'string', 'max:2000'],

            // Foto bukti wajib jika status selesai ditangani di level JS/UX,
            // di sini tetap opsional agar fleksibel
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

    // ─── Helper: otorisasi seksi ────────────────────────────────────────────────
    private function authorizeAkses(Pengaduan $pengaduan): void
    {
        $user = Auth::user();
        if ($user->profile->role === 'seksi') {
            abort_if(
                $pengaduan->seksi_tujuan !== $user->profile->seksi,
                403,
                'Anda tidak berwenang menangani aduan ini.'
            );
        }
    }

    // ─── Helper: upload bukti gambar ───────────────────────────────────────────
    private function uploadBukti(Request $request, Pengaduan $pengaduan): ?string
    {
        if (! $request->hasFile('bukti_gambar')) {
            return null;
        }

        $file     = $request->file('bukti_gambar');
        $filename = 'bukti-tindak-lanjut.' . $file->getClientOriginalExtension();
        $path     = Storage::disk('supabase')->putFileAs(
            "pengaduan/{$pengaduan->nomor_tiket}",
            $file,
            $filename
        );

        return Storage::disk('supabase')->url($path);
    }

    // ───────────────────────────────────────────────────────────────────────────
    // STORE — Buat tindak lanjut baru (atau update jika sudah ada via updateOrCreate)
    // POST /tindak-lanjut
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

        // ✅ One-to-One: satu pengaduan → satu TindakLanjut
        TindakLanjut::updateOrCreate(
            ['pengaduan_id' => $pengaduan->id],
            [
                'catatan_petugas' => $data['catatan_petugas'],
                'tanggal_selesai' => $tanggalSelesai,
                'petugas_id'      => $user->id,
                // Hanya timpa foto jika ada unggahan baru
                ...($buktiPath ? ['bukti_gambar' => $buktiPath] : []),
            ]
        );

        $pengaduan->update([
            'status'           => $data['status_baru'],
            'keterangan_admin' => $data['catatan_petugas'],
            'updated_by'       => $user->id,
        ]);

        return back()->with('success', "Tindak lanjut tiket {$pengaduan->nomor_tiket} berhasil disimpan.");
    }

    // ───────────────────────────────────────────────────────────────────────────
    // UPDATE — Revisi tindak lanjut yang sudah ada
    // PATCH /tindak-lanjut/{tindakLanjut}
    // ───────────────────────────────────────────────────────────────────────────
    public function update(Request $request, TindakLanjut $tindakLanjut)
    {
        $data = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $user      = Auth::user();
        // ✅ PERBAIKAN: nama relasi singular ->pengaduan() bukan ->pengaduans()
        $pengaduan = $tindakLanjut->pengaduan;

        $this->authorizeAkses($pengaduan);

        $buktiPath = $this->uploadBukti($request, $pengaduan);

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

        return back()->with('success', 'Tindak lanjut berhasil diperbarui.');
    }

    // ───────────────────────────────────────────────────────────────────────────
    // DESTROY — Hapus tindak lanjut, kembalikan status ke pending
    // DELETE /tindak-lanjut/{tindakLanjut}
    // ───────────────────────────────────────────────────────────────────────────
    public function destroy(TindakLanjut $tindakLanjut)
    {
        $user      = Auth::user();
        $pengaduan = $tindakLanjut->pengaduan; // ✅ relasi singular

        $this->authorizeAkses($pengaduan);

        $tindakLanjut->delete();

        $pengaduan->update([
            'status'           => 'pending',
            'keterangan_admin' => null,
        ]);

        return back()->with('success', 'Tindak lanjut dihapus. Status aduan dikembalikan ke pending.');
    }
}
