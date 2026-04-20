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
    /**
     * SIMPAN TINDAK LANJUT BARU
     * Dipanggil dari modal di dashboard Seksi / TIKKIM
     * POST /tindak-lanjut
     *
     * Satu pengaduan hanya boleh punya satu tindak lanjut aktif (updateOrCreate).
     * Status pengaduan ikut berubah sesuai pilihan petugas.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pengaduan_id'    => 'required|exists:pengaduans,id',
            'status_baru'     => 'required|in:proses,diteruskan,selesai',
            'catatan_petugas' => 'required|string|max:2000',
            'bukti_gambar'    => 'nullable|image|mimes:jpg,jpeg,png|max:10240', // 🔥 TAMBAH INI
        ], [
            'catatan_petugas.required' => 'Catatan tindak lanjut wajib diisi.',
        ]);

        $pengaduan = Pengaduan::findOrFail($request->pengaduan_id);

        // Otorisasi: Seksi hanya boleh update aduan milik seksinya
        $user = Auth::user();
        if ($user->profile->role === 'seksi') {
            abort_if(
                $pengaduan->seksi_tujuan !== $user->profile->seksi,
                403, 'Anda tidak berwenang menangani aduan ini.'
            );
        }

        $buktiPath = null;

        if ($request->hasFile('bukti_gambar')) {
            $file = $request->file('bukti_gambar');
        
            $filename = 'bukti-tindak-lanjut.' . $file->getClientOriginalExtension();

            $path = Storage::disk('supabase')->putFileAs(
                "pengaduan/{$pengaduan->nomor_tiket}",
                $file,
                $filename
            );
        
            $buktiPath = Storage::disk('supabase')->url($path);
        }

        $tanggalSelesai = $request->status_baru === 'selesai' ? Carbon::now() : null;

        // Simpan / update tindak lanjut (satu record per pengaduan)
        TindakLanjut::updateOrCreate(
            ['pengaduan_id' => $pengaduan->id],
            [
                'catatan_petugas' => $request->catatan_petugas,
                'tanggal_selesai' => $tanggalSelesai,
                'petugas_id'      => $user->id,  // opsional, lihat catatan migrasi di bawah
                'bukti_gambar'    => $buktiPath,
            ]
        );

        // Update status & keterangan di tabel pengaduans
        $pengaduan->update([
            'status'           => $request->status_baru,
            'keterangan_admin' => $request->catatan_petugas,
            'updated_by'       => $user->id,
        ]);

        return back()->with('success', "Tindak lanjut untuk tiket {$pengaduan->nomor_tiket} berhasil disimpan.");
    }

    /**
     * UPDATE TINDAK LANJUT YANG SUDAH ADA
     * Dipakai jika petugas ingin merevisi catatan sebelumnya
     * PUT /tindak-lanjut/{tindakLanjut}
     */
    public function update(Request $request, TindakLanjut $tindakLanjut)
    {
        $request->validate([
            'status_baru'     => 'required|in:proses,diteruskan,selesai',
            'catatan_petugas' => 'required|string|max:2000',
        ]);

        $user      = Auth::user();
        $pengaduan = $tindakLanjut->pengaduans;

        // Otorisasi
        if ($user->profile->role === 'seksi') {
            abort_if($pengaduan->seksi_tujuan !== $user->profile->seksi, 403);
        }

        $tindakLanjut->update([
            'catatan_petugas' => $request->catatan_petugas,
            'tanggal_selesai' => $request->status_baru === 'selesai' ? Carbon::now() : null,
        ]);

        $pengaduan->update([
            'status'           => $request->status_baru,
            'keterangan_admin' => $request->catatan_petugas,
            'updated_by'       => $user->id,
        ]);

        return back()->with('success', 'Tindak lanjut berhasil diperbarui.');
    }

    /**
     * HAPUS TINDAK LANJUT
     * Mengembalikan status pengaduan ke 'pending'
     * DELETE /tindak-lanjut/{tindakLanjut}
     */
    public function destroy(TindakLanjut $tindakLanjut)
    {
        $pengaduan = $tindakLanjut->pengaduans;

        // Otorisasi
        $user = Auth::user();
        if ($user->profile->role === 'seksi') {
            abort_if($pengaduan->seksi_tujuan !== $user->profile->seksi, 403);
        }

        $tindakLanjut->delete();

        // Kembalikan status ke pending
        $pengaduan->update([
            'status'           => 'pending',
            'keterangan_admin' => null,
        ]);

        return back()->with('success', 'Tindak lanjut dihapus. Status aduan dikembalikan ke pending.');
    }
}