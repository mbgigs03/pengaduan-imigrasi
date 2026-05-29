<x-layouts.dashboard>
    <x-slot name="header">Kelola Topik FAQ</x-slot>

    <div class="p-6 max-w-5xl mx-auto space-y-6" x-data="faqManager()">
        
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Daftar Template FAQ</h2>
                <p class="text-sm text-slate-500 mt-1">Kelola template balasan otomatis untuk layanan Pemberian Informasi (Khusus Tikkim).</p>
            </div>
            <button @click="openModal('create')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 px-5 rounded-xl transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah FAQ Baru
            </button>
        </div>

        {{-- Session Alerts --}}
        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm font-medium border border-red-200">
                Gagal menyimpan data. Pastikan semua kolom diisi.
            </div>
        @endif

        {{-- Tabel FAQ --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-bold">
                        <tr>
                            <th class="px-6 py-4 w-1/4">Topik</th>
                            <th class="px-6 py-4 w-1/2">Isi Template</th>
                            <th class="px-6 py-4 w-1/4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($faqs as $faq)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800 align-top">{{ $faq->topik }}</td>
                                <td class="px-6 py-4 align-top">
                                    <div class="line-clamp-3 text-xs leading-relaxed whitespace-pre-line">{{ $faq->template }}</div>
                                </td>
                                <td class="px-6 py-4 align-top text-center">
                                    <div class="flex justify-center gap-2">
                                        <button @click="openModal('edit', {{ $faq }})" class="px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg text-xs font-bold transition">Edit</button>
                                        
                                        <form action="{{ route('faq.destroy', $faq->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus FAQ ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-bold transition">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400">Belum ada template FAQ yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── MODAL CREATE / EDIT ── --}}
        <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div @click.away="closeModal()" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden" x-transition>
                <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-white text-base" x-text="mode === 'create' ? 'Tambah FAQ Baru' : 'Edit FAQ'"></h3>
                    <button @click="closeModal()" class="text-blue-200 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="p-6">
                    @csrf
                    <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Judul Topik <span class="text-red-500">*</span></label>
                            <input type="text" name="topik" x-model="formData.topik" required placeholder="Contoh: Persyaratan Paspor Baru"
                                   class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">Isi Template Jawaban <span class="text-red-500">*</span></label>
                            <textarea name="template" x-model="formData.template" rows="8" required placeholder="Tulis rincian syarat dan informasi di sini..."
                                      class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition resize-none"></textarea>
                            <p class="text-[10px] text-slate-400 mt-1">Gunakan 'Enter' untuk membuat baris baru. Teks ini akan muncul otomatis saat pemohon memilih topik.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition">Simpan FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function faqManager() {
                return {
                    isOpen: false,
                    mode: 'create', // 'create' atau 'edit'
                    formAction: '{{ route('faq.store') }}',
                    formData: { id: '', topik: '', template: '' },

                    openModal(mode, data = null) {
                        this.mode = mode;
                        if (mode === 'edit' && data) {
                            this.formData.id = data.id;
                            this.formData.topik = data.topik;
                            this.formData.template = data.template;
                            this.formAction = `/faq/${data.id}`;
                        } else {
                            this.formData.id = '';
                            this.formData.topik = '';
                            this.formData.template = '';
                            this.formAction = '{{ route('faq.store') }}';
                        }
                        this.isOpen = true;
                    },
                    closeModal() {
                        this.isOpen = false;
                    }
                }
            }
        </script>
    </x-slot>
</x-layouts.dashboard>