{{-- resources/views/pengaduan/show.blade.php --}}
<x-layouts.dashboard>
    <x-slot name="header">Detail Pengaduan</x-slot>

    @php
        
        $slaStatus = $pengaduan->sla_status ?? 'ok';
        $deadline  = \Carbon\Carbon::parse($pengaduan->deadline_tindak_lanjut);
        $now       = now();

        $slaDot   = match($slaStatus) { 'over' => 'bg-red-500', 'warn' => 'bg-amber-500', default => 'bg-emerald-500' };
        $slaLabel = match($slaStatus) { 'over' => 'Melewati SLA', 'warn' => 'H-1 Deadline', default => 'On Track' };
        $slaColor = match($slaStatus) { 'over' => 'text-red-600 bg-red-50 border-red-200', 'warn' => 'text-amber-600 bg-amber-50 border-amber-200', default => 'text-emerald-600 bg-emerald-50 border-emerald-200' };

        $statusColor = \App\Helpers\StatusHelper::badgeClass($pengaduan->status);
    @endphp

    <div class="p-6 max-w-[960px] space-y-5">

        {{-- ── HEADER ROW ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-[14px] border border-slate-100 px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">

            {{-- Kiri: tiket + badge --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="font-mono text-base font-bold text-slate-800 tracking-wide">
                        {{ $pengaduan->nomor_tiket }}
                    </span>
                    <span class="badge {{ $statusColor }}">{{ \App\Helpers\StatusHelper::label($pengaduan->status) }}</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $slaColor }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $slaDot }} {{ $slaStatus === 'over' ? 'animate-pulse' : '' }}"></span>
                        {{ $slaLabel }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1.5">
                    Diajukan {{ \Carbon\Carbon::parse($pengaduan->tgl_pengaduan)->translatedFormat('d F Y') }}
                    · Seksi <strong class="text-slate-600">{{ $pengaduan->seksi_tujuan }}</strong>
                    · via <strong class="text-slate-600">{{ $pengaduan->kanal_pengaduan }}</strong>
                </p>
            </div>

            {{-- Kanan: tombol aksi --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('dashboard.pengaduan.downloadPdf', $pengaduan->nomor_tiket) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                          border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600
                          hover:border-red-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </a>
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                          border border-slate-200 text-slate-500 hover:bg-slate-50 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        {{-- ── ROW 2: INFO PELAPOR + INFO PENGADUAN ──────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Info Pelapor --}}
            <div class="bg-white rounded-[14px] border border-slate-100 p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Data Pelapor</p>
                <div class="space-y-3.5">
                    @foreach([
                        ['Nama Lengkap', $pengaduan->nama, true],
                        ['NIK', $pengaduan->nik ?? '-', false],
                        ['Alamat', $pengaduan->alamat ?? '-', false],
                        ['Nomor WhatsApp', $pengaduan->whatsapp, false],
                    ] as [$lbl, $val, $bold])
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">{{ $lbl }}</span>
                            <span class="text-sm {{ $bold ? 'font-semibold text-slate-800' : 'text-slate-600' }}">{{ $val }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Info Pengaduan --}}
            <div class="bg-white rounded-[14px] border border-slate-100 p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Info Pengaduan</p>
                <div class="space-y-3.5">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Seksi Tujuan</span>
                        <span class="text-sm font-semibold text-slate-800">{{ $pengaduan->seksi_tujuan }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Kanal Pengaduan</span>
                        <span class="text-sm text-slate-600">{{ $pengaduan->kanal_pengaduan }}</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Tanggal Pengaduan</span>
                        <span class="text-sm text-slate-600">
                            {{ \Carbon\Carbon::parse($pengaduan->tgl_pengaduan)->translatedFormat('d F Y') }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Deadline Tindak Lanjut</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm {{ $slaStatus === 'over' ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                {{ $deadline->translatedFormat('d F Y') }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ $deadline->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Status</span>
                        <span class="badge {{ $statusColor }} w-fit">{{ \App\Helpers\StatusHelper::label($pengaduan->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── FOTO KTP ────────────────────────────────────────────── --}}
        @if ($pengaduan->foto_ktp)
        <div class="bg-white rounded-[14px] border border-slate-100 p-5">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Foto KTP</p>
            <img src="{{ $pengaduan->foto_ktp }}"
                alt="Foto KTP"
                class="rounded-xl border border-slate-100 max-w-xs w-full object-cover cursor-pointer"
                onclick="window.open(this.src, '_blank')">
            <p class="text-[10px] text-slate-400 mt-2">Klik foto untuk membuka ukuran penuh.</p>
        </div>
        @endif

        {{-- ── ISI ADUAN ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-[14px] border border-slate-100 p-5">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Isi Aduan</p>
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $pengaduan->aduan }}</div>
        </div>

        {{-- ── BUKTI FOTO (jika ada) ───────────────────────────────── --}}
        @php 
            $rawBukti = $pengaduan->all_bukti ?? []; 

            // 1. Ubah apapun formatnya (mau array dari Laravel atau string dari Supabase) jadi teks mentah
            $stringMentah = is_array($rawBukti) ? json_encode($rawBukti) : $rawBukti;

            // 2. Bersihkan karakter aneh (kurung siku, kutip, spasi, dan garis miring terbalik)
            $bersih = str_replace(['[', ']', '"', "'", ' ', '\\'], '', $stringMentah);

            // 3. [KUNCI RAHASIA] Pecah string berdasarkan tanda koma menjadi Array yang sesungguhnya!
            $arrayAsli = explode(',', $bersih);

            $processedBukti = [];

            // 4. Kita loop satu-satu secara akurat
            foreach($arrayAsli as $url) {
                if (empty($url)) continue;

                if (str_starts_with($url, 'http')) {
                    // Bypass blokir gambar Google Drive
                    if (str_contains($url, 'drive.google.com')) {
                        preg_match('/[-\w]{25,}/', $url, $matches);
                        if (isset($matches[0])) {
                            $url = 'https://drive.google.com/thumbnail?id=' . $matches[0] . '&sz=w1000';
                        }
                    }
                    $processedBukti[] = $url;
                } else {
                    $processedBukti[] = asset('storage/' . $url);
                }
            }
        @endphp
 
        @if (count($processedBukti) > 0)
        <div class="bg-white rounded-[14px] border border-slate-100 p-5"
             x-data="lightbox({{ json_encode($processedBukti) }})">
         
            <div class="flex items-center justify-between mb-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Bukti Lampiran
                </p>
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 border border-slate-100 px-2.5 py-1 rounded-full">
                    {{ count($processedBukti) }} foto
                </span>
            </div>
         
            {{-- ── Thumbnail Grid ───────────────────────────────── --}}
            @php $cols = match(true) { count($processedBukti) === 1 => 1, count($processedBukti) === 2 => 2, default => 3 }; @endphp
         
            <div class="grid gap-2" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr))">
         
                @foreach ($processedBukti as $i => $finalUrl)
                    <button type="button"
                            @click="open({{ $i }})"
                            class="relative group aspect-square overflow-hidden rounded-xl bg-slate-100
                                focus:outline-none focus:ring-2 focus:ring-blue-400
                                {{ $i === 0 && count($processedBukti) >= 3 ? 'row-span-2 col-span-1' : '' }}"
                            style="{{ $i === 0 && count($processedBukti) >= 3 ? 'grid-row: span 2;' : '' }}">
         
                        {{-- URL sudah bersih, tinggal dipanggil langsung --}}
                        <img src="{{ $finalUrl }}"
                             alt="Bukti {{ $i + 1 }}"
                             loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
         
                        {{-- Overlay hover --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-200 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                            </svg>
                        </div>
         
                        {{-- Badge "+N" untuk foto ke-5 jika total > 5 --}}
                        @if ($i === 4 && count($processedBukti) > 5)
                            <div class="absolute inset-0 bg-black/55 flex items-center justify-center">
                                <span class="text-white text-xl font-bold">+{{ count($processedBukti) - 5 }}</span>
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>
         
            {{-- ── Lightbox Overlay ─────────────────────────────── --}}
            <div x-show="visible"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="close()"
                @keydown.arrow-left.window="prev()"
                @keydown.arrow-right.window="next()"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                style="display:none;">
         
                <div class="absolute inset-0" @click="close()"></div>
         
                <div class="relative z-10 flex flex-col items-center gap-3 max-w-4xl w-full">
         
                    <div class="text-white/60 text-xs font-medium tracking-widest uppercase">
                        <span x-text="current + 1"></span> / <span x-text="images.length"></span>
                    </div>
         
                    <div class="relative w-full flex items-center justify-center">
                        <img :src="images[current]"
                            :alt="'Foto ' + (current + 1)"
                            class="max-h-[75vh] max-w-full rounded-xl object-contain shadow-2xl"
                            @click.stop>
         
                        <button @click.stop="prev()"
                                x-show="images.length > 1"
                                class="absolute left-0 sm:-left-14 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
         
                        <button @click.stop="next()"
                                x-show="images.length > 1"
                                class="absolute right-0 sm:-right-14 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
         
                    <div x-show="images.length > 1" class="flex gap-2 overflow-x-auto pb-1 max-w-full px-2">
                        <template x-for="(img, i) in images" :key="i">
                            <button @click.stop="current = i"
                                    class="flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden border-2 transition-all"
                                    :class="current === i ? 'border-white opacity-100' : 'border-transparent opacity-50 hover:opacity-75'">
                                <img :src="img" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>
         
                <button @click="close()" class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
         
                <a :href="images[current]" target="_blank" @click.stop
                   class="absolute top-4 right-16 z-20 w-9 h-9 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition backdrop-blur-sm"
                   title="Buka di tab baru">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4 M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>
         
        <script>
        function lightbox(images) {
            return {
                images,
                visible: false,
                current: 0,
         
                open(index) {
                    this.current = index;
                    this.visible = true;
                    document.body.style.overflow = 'hidden';
                },
                close() {
                    this.visible = false;
                    document.body.style.overflow = '';
                },
                prev() {
                    this.current = (this.current - 1 + this.images.length) % this.images.length;
                },
                next() {
                    this.current = (this.current + 1) % this.images.length;
                }
            }
        }
        </script>
        @endif

        {{-- ── TINDAK LANJUT ───────────────────────────────────────── --}}
        @if ($pengaduan->tindakLanjut)
            @php $tl = $pengaduan->tindakLanjut; @endphp
            <div class="bg-white rounded-[14px] border border-slate-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tindak Lanjut</p>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Sudah Ditindaklanjuti
                    </span>
                </div>

                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-sm text-slate-700 leading-relaxed">
                    {{ $tl->catatan_petugas }}
                </div>

                {{-- 🟢 KODE FINAL: Menampilkan Foto Bukti --}}
                @if ($tl->bukti_gambar)
                    <div class="mt-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Lampiran Bukti</p>
                        <div class="rounded-xl overflow-hidden border border-slate-200 inline-block shadow-sm">
                            <img src="https://crgjblwavebvnvvdnzbk.supabase.co/storage/v1/object/public/pengaduan/{{ $tl->bukti_gambar }}" 
                                alt="Bukti Tindak Lanjut" 
                                class="max-w-xs h-auto cursor-pointer hover:opacity-90 transition"
                                onclick="window.open(this.src, '_blank')"
                                onerror="this.style.display='none'">
                        </div>
                    </div>
                @endif

                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-400">
                    @if ($tl->updated_at)
                        <span>Diperbarui {{ \Carbon\Carbon::parse($tl->updated_at)->translatedFormat('d F Y, H:i')}}</span>
                    @endif
                    @if ($tl->petugas ?? null)
                        <span>| oleh <strong class="text-slate-600">{{ is_object($tl->petugas) ? $tl->petugas->name : $tl->petugas }}</strong></span>
                    @endif
                </div>
            </div>

        @else
            <div class="bg-white rounded-[14px] border border-dashed border-slate-200 p-5 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-500">Belum ada tindak lanjut</p>
                    <p class="text-xs text-slate-400 mt-0.5">Catatan petugas akan muncul di sini setelah diisi</p>
                </div>
            </div>
        @endif

    </div>
</x-layouts.dashboard>
