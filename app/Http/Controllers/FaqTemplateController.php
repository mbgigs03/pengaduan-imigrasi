<?php

namespace App\Http\Controllers;

use App\Models\FaqTemplate;
use Illuminate\Http\Request;

class FaqTemplateController extends Controller
{
    // Menampilkan halaman kelola FAQ
    public function index()
    {
        $faqs = FaqTemplate::orderBy('topik', 'asc')->get();
        return view('faq.faq', compact('faqs')); 
        // Note: Kamu perlu membuat file blade 'resources/views/faq/index.blade.php' nanti untuk UI halamannya
    }

    // Menyimpan FAQ baru
    public function store(Request $request)
    {
        $request->validate([
            'topik'    => 'required|string|max:255',
            'template' => 'required|string',
        ]);

        FaqTemplate::create($request->all());

        return back()->with('success', 'Template FAQ berhasil ditambahkan.');
    }

    // Mengupdate FAQ yang ada
    public function update(Request $request, FaqTemplate $faq)
    {
        $request->validate([
            'topik'    => 'required|string|max:255',
            'template' => 'required|string',
        ]);

        $faq->update($request->all());

        return back()->with('success', 'Template FAQ berhasil diperbarui.');
    }

    // Menghapus FAQ
    public function destroy(FaqTemplate $faq)
    {
        $faq->delete();

        return back()->with('success', 'Template FAQ berhasil dihapus.');
    }
}