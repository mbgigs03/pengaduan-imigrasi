<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PengaduanController extends Controller
{
    /**
     * TAMPILAN DASHBOARD (Filter berdasarkan Seksi - Saran Pak Sony)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Query dasar
        $query = Pengaduan::query();

        // 1. Filter Berdasarkan Seksi Admin (Saran Pak Sony)
        // Admin Seksi Paspor hanya bisa melihat pengaduan untuk Paspor
        if ($user->role === 'admin_seksi') {
            $query->where('seksi_tujuan', $user->seksi);
        }

        // 2. Filter Berdasarkan Kanal (Saran Bu Dessy)
        if ($request->has('filter_kanal')) {
            $query->where('kanal_pengaduan', $request->filter_kanal);
        }

        $pengaduans = $query->latest()->paginate(10);

        return view('pengaduan.index', compact('pengaduans'));
    }

    /**
     * FORM INPUT (Pemohon Langsung - Saran Kakanim)
     */
    public function create()
    {
        return view('pengaduan.create');
    }

    /**
     * SIMPAN DATA (Logika SLA - Saran Pak Lutfi)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|numeric|digits:16',
            'whatsapp' => 'required|string',
            'seksi_tujuan' => 'required',
            'kanal' => 'required', // Nama di form
            'bukti' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Kita petakan manual agar aman
        $pengaduan = new Pengaduan();
        $pengaduan->nama = $request->nama;
        $pengaduan->nik = $request->nik;
        $pengaduan->alamat = $request->alamat;
        $pengaduan->whatsapp = $request->whatsapp;
        $pengaduan->jenis_layanan = $request->jenis_layanan;
        $pengaduan->seksi_tujuan = $request->seksi_tujuan;
        $pengaduan->kanal_pengaduan = $request->kanal; // Memetakan 'kanal' ke 'kanal_pengaduan'
        
        // Logika SLA Pak Lutfi
        $pengaduan->tgl_pengaduan = Carbon::now();
        $pengaduan->deadline_tindak_lanjut = Carbon::now()->addDays(3);
        $pengaduan->status = 'pending';

        if ($request->hasFile('bukti')) {
            $pengaduan->bukti = $request->file('bukti')->store('bukti-pengaduan', 'public');
        }

        $pengaduan->save();

        return redirect()->back()->with('success', 'Pengaduan berhasil terkirim!');
    }

    /**
     * UPDATE STATUS (Alur Admin > Atasan - Saran Pak Sony)
     */
    public function updateStatus(Request $request, Pengaduan $pengaduan)
    {
        $request->validate(['status' => 'required']);

        // Logika alur: Admin meneruskan ke atasan
        $pengaduan->update([
            'status' => $request->status,
            'keterangan_admin' => $request->keterangan,
            'updated_by' => Auth::id()
        ]);

        return back()->with('success', 'Status pengaduan diperbarui.');
    }
}