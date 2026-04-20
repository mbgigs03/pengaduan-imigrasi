{{-- resources/views/dashboard/tikkim.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard TIKKIM</h2>
            <span class="text-sm text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        .chart-card { background:#fff; border:1px solid #f1f5f9; border-radius:12px; padding:20px; }
        .chart-title { font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; margin-bottom:16px; }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FLASH --}}
            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- ═══ STAT CARDS ═══ --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    ['Total Pengaduan', $totalBulanIni, 'bulan ini',        'text-gray-900',  'bg-blue-50',   '📋'],
                    ['Selesai',         $selesai,       ($totalBulanIni > 0 ? round($selesai/$totalBulanIni*100) : 0).'% dari total', 'text-green-600','bg-green-50','✅'],
                    ['Melebihi SLA',    $slaOver,       'melewati 3 hari',  'text-red-600',   'bg-red-50',    '🚨'],
                    ['H-1 Deadline',    $slaHMinus1,    'perlu tindak lanjut','text-amber-500','bg-amber-50', '⏰'],
                ] as [$label, $val, $sub, $valColor, $iconBg, $icon])
                    <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center text-lg flex-shrink-0">{{ $icon }}</div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">{{ $label }}</p>
                            <p class="text-2xl font-bold {{ $valColor }}">{{ $val }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $sub }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ═══ PIE STATUS + BAR SEKSI ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="chart-card">
                    <p class="chart-title">Distribusi Status Pengaduan</p>
                    <div class="flex items-center gap-6">
                        <div style="width:180px;height:180px;flex-shrink:0">
                            <canvas id="chartStatus"></canvas>
                        </div>
                        <div class="space-y-2 flex-1">
                            @php
                                $statusMeta  = ['pending'=>['Pending','#f59e0b'],'proses'=>['Proses','#3b82f6'],'diteruskan'=>['Diteruskan','#8b5cf6'],'selesai'=>['Selesai','#10b981']];
                                $totalStatus = $statusStats->sum('jumlah') ?: 1;
                            @endphp
                            @foreach ($statusStats as $s)
                                @php [$slabel,$scolor] = $statusMeta[$s->status] ?? [ucfirst($s->status),'#94a3b8']; @endphp
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $scolor }}"></span>
                                        <span class="text-gray-600">{{ $slabel }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-800">{{ $s->jumlah }}</span>
                                        <span class="text-xs text-gray-400">({{ round($s->jumlah/$totalStatus*100) }}%)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <p class="chart-title">Penyelesaian per Seksi (%)</p>
                    <canvas id="chartSeksi" style="max-height:210px"></canvas>
                </div>
            </div>

            {{-- ═══ BAR KANAL + DOUGHNUT SLA ═══ --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="chart-card md:col-span-2">
                    <p class="chart-title">Volume Pengaduan per Kanal</p>
                    <canvas id="chartKanal" style="max-height:200px"></canvas>
                </div>

                <div class="chart-card flex flex-col">
                    <p class="chart-title">SLA Health</p>
                    @php $slaOnTrack = max(0, $totalBulanIni - $selesai - $slaOver - $slaHMinus1); @endphp
                    <div class="flex-1 flex flex-col items-center justify-center">
                        <div style="width:150px;height:150px">
                            <canvas id="chartSla"></canvas>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1.5 w-full text-xs">
                            @foreach([['On Track',$slaOnTrack,'#10b981'],['Selesai',$selesai,'#3b82f6'],['H-1',$slaHMinus1,'#f59e0b'],['Over SLA',$slaOver,'#ef4444']] as [$l,$v,$c])
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
                                    <span class="text-gray-500">{{ $l }}</span>
                                    <span class="font-bold text-gray-700 ml-auto">{{ $v }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ STACKED BAR: DETAIL STATUS PER SEKSI ═══ --}}
            <div class="chart-card">
                <p class="chart-title">Detail Status per Seksi</p>
                <canvas id="chartSeksiStacked" style="max-height:200px"></canvas>
            </div>

            {{-- ═══ LAPORAN SLA ═══ --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="chart-title mb-0">Laporan SLA — Pengaduan Belum Selesai</h3>
                    <div class="flex gap-3 text-xs text-gray-400">
                        @foreach([['bg-green-400','On track'],['bg-amber-400','H-1'],['bg-red-400','Terlambat']] as [$dot,$lbl])
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full {{ $dot }} inline-block"></span>{{ $lbl }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                @foreach(['Tiket','Nama','Seksi','Aduan','Status','Deadline','Aksi'] as $h)
                                    <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($laporanSla as $p)
                                @php
                                    // Meta SLA (UI + label + warna)
                                    $slaMeta = match($p->sla_status) {
                                        'over' => [
                                            'label' => 'Terlambat',
                                            'dot'   => 'bg-red-500',
                                            'pill'  => 'bg-red-100 text-red-700',
                                            'shadow'=> 'shadow-[0_0_8px_rgba(239,68,68,0.4)]'
                                        ],
                                        'warn' => [
                                            'label' => 'H-1 Deadline',
                                            'dot'   => 'bg-amber-500',
                                            'pill'  => 'bg-amber-100 text-amber-700',
                                            'shadow'=> ''
                                        ],
                                        default => [
                                            'label' => 'On Track',
                                            'dot'   => 'bg-green-500',
                                            'pill'  => 'bg-green-100 text-green-700',
                                            'shadow'=> ''
                                        ],
                                    };

                                    // Cek apakah sudah ada tindak lanjut
                                    $tlId = optional($p->tindakLanjut)->id;
                                @endphp

                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                    
                                    {{-- Nomor Tiket + Indicator --}}
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $slaMeta['dot'] }} 
                                                {{ $p->sla_status === 'over' ? 'animate-pulse' : '' }} 
                                                {{ $slaMeta['shadow'] }}">
                                            </span>
                                            <span class="text-xs font-mono text-gray-600">
                                                {{ $p->nomor_tiket }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Nama --}}
                                    <td class="py-3 px-3 font-semibold text-gray-800">
                                        {{ $p->nama }}
                                    </td>

                                    {{-- Seksi --}}
                                    <td class="py-3 px-3 text-xs text-gray-500 uppercase">
                                        {{ $p->seksi_tujuan }}
                                    </td>

                                    {{-- Aduan --}}
                                    <td class="py-3 px-3 text-xs text-gray-500 max-w-xs truncate" title="{{ $p->aduan }}">
                                        {{ Str::limit($p->aduan, 45) }}
                                    </td>

                                    {{-- Status + SLA --}}
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <x-status-pill :status="$p->status" />
                                        </div>
                                    </td>

                                    {{-- Deadline --}}
                                    <td class="py-3 px-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $slaMeta['pill'] }}">
                                            {{ $slaMeta['label'] }}
                                        </span>
                                        <div class="text-xs {{ $p->sla_status === 'over' ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                            {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}
                                        </div>
                                        <div class="text-[9px] text-gray-400">
                                            {{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->diffForHumans() }}
                                        </div>
                                    </td>

                                    {{-- Action --}}
                                    <td class="py-3 px-3 text-right">
                                        <button
                                            onclick="openModalTL(
                                                '{{ $p->id }}',
                                                '{{ $p->nomor_tiket }}',
                                                '{{ addslashes($p->nama) }}',
                                                '{{ $p->status }}',
                                                '{{ addslashes($p->keterangan_admin ?? '') }}',
                                                '{{ $tlId }}'
                                            )"
                                            class="text-xs px-3 py-1.5 rounded-lg font-bold transition shadow-sm
                                            {{ $tlId 
                                                ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' 
                                                : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                            
                                            {{ $tlId ? 'Edit TL' : 'Tindak Lanjut' }}
                                        </button>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="text-3xl">🛡️</span>
                                            <p class="mt-2 text-sm text-gray-400 font-medium">
                                                Sistem Aman. Semua pengaduan berjalan sesuai timeline.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $laporanSla->links() }}</div>
            </div>

            {{-- ═══ SEMUA PENGADUAN ═══ --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h3 class="chart-title">Semua Pengaduan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                @foreach(['Tiket','Nama / Seksi','Kanal','Status','Deadline','Aksi'] as $h)
                                    <th class="text-left py-2 px-3 text-xs text-gray-400 font-semibold uppercase">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengaduans as $p)
                                @php $tlId = optional($p->tindakLanjut)->id; @endphp
                                <tr>
                                    <td class="py-2 px-3 text-xs text-gray-500">{{ $p->nomor_tiket }}</td>
                                    <td class="py-2 px-3">
                                        <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                                        <div class="text-xs text-gray-400">{{ $p->seksi_tujuan }}</div>
                                    </td>
                                    <td class="py-2 px-3 text-xs text-gray-500">{{ $p->kanal_pengaduan }}</td>
                                    <td class="py-2 px-3"><x-status-pill :status="$p->status" /></td>
                                    <td class="py-2 px-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($p->deadline_tindak_lanjut)->format('d M Y') }}</td>
                                    <td class="py-2 px-3">
                                        <button onclick="openModalTL('{{ $p->id }}','{{ $p->nomor_tiket }}','{{ addslashes($p->nama) }}','{{ $p->status }}','{{ addslashes($p->keterangan_admin ?? '') }}','{{ $tlId }}')"
                                            class="text-xs px-3 py-1.5 rounded-lg font-medium transition {{ $tlId ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100' }}">
                                            {{ $tlId ? 'Edit TL' : 'Tindak Lanjut' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-gray-400 text-sm">Belum ada pengaduan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $pengaduans->links() }}</div>
            </div>

        </div>
    </div>

    {{-- MODAL TINDAK LANJUT --}}
    <x-modal-tindak-lanjut />

    {{-- ═══ CHART SCRIPTS ═══ --}}
    <script>
    Chart.defaults.font.family = "'Plus Jakarta Sans','Inter',sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#94a3b8';
    const gridOpts = { color:'#f1f5f9', drawBorder:false };
    const tickOpts = { color:'#94a3b8' };

    // 1. PIE — STATUS
    new Chart(document.getElementById('chartStatus'), {
        type: 'pie',
        data: {
            labels:   {!! json_encode($statusStats->pluck('status')->map(fn($s)=>ucfirst($s))->values()) !!},
            datasets: [{
                data:            {!! json_encode($statusStats->pluck('jumlah')->values()) !!},
                backgroundColor: {!! json_encode($statusStats->pluck('status')->map(fn($s)=>match($s){'pending'=>'#f59e0b','proses'=>'#3b82f6','diteruskan'=>'#8b5cf6','selesai'=>'#10b981',default=>'#94a3b8'})->values()) !!},
                borderWidth: 2, borderColor:'#fff', hoverOffset:6,
            }],
        },
        options: {
            responsive:true, maintainAspectRatio:true,
            plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.label}: ${c.parsed} aduan`}} },
        },
    });

    // 2. BAR HORIZONTAL — PERFORMA SEKSI
    new Chart(document.getElementById('chartSeksi'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($performaSeksi->pluck('nama')->values()) !!},
            datasets: [{
                label: 'Selesai (%)',
                data:  {!! json_encode($performaSeksi->pluck('pct')->values()) !!},
                backgroundColor: {!! json_encode($performaSeksi->pluck('pct')->map(fn($v)=>$v>=80?'#10b981':($v>=50?'#3b82f6':'#f59e0b'))->values()) !!},
                borderRadius:6, borderSkipped:false,
            }],
        },
        options: {
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            scales:{
                x:{ grid:{...gridOpts}, ticks:{...tickOpts, callback:v=>v+'%'}, min:0, max:100 },
                y:{ grid:{display:false}, ticks:{...tickOpts} },
            },
            plugins:{
                legend:{display:false},
                tooltip:{callbacks:{label:c=>` ${c.parsed.x}% selesai`}},
            },
        },
    });

    // 3. BAR VERTIKAL — KANAL
    const kanalPalette = ['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4'];
    const kanalLabels  = {!! json_encode($kanalStats->pluck('kanal_pengaduan')->values()) !!};
    new Chart(document.getElementById('chartKanal'), {
        type: 'bar',
        data: {
            labels: kanalLabels,
            datasets: [{
                label: 'Jumlah Aduan',
                data:  {!! json_encode($kanalStats->pluck('jumlah')->values()) !!},
                backgroundColor: kanalLabels.map((_,i)=>kanalPalette[i%kanalPalette.length]),
                borderRadius:6, borderSkipped:false,
            }],
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            scales:{
                x:{ grid:{display:false}, ticks:{...tickOpts} },
                y:{ grid:{...gridOpts}, ticks:{...tickOpts, stepSize:1}, beginAtZero:true },
            },
            plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.parsed.y} pengaduan`}} },
        },
    });

    // 4. DOUGHNUT — SLA HEALTH
    new Chart(document.getElementById('chartSla'), {
        type: 'doughnut',
        data: {
            labels:   ['On Track','Selesai','H-1','Over SLA'],
            datasets: [{
                data:            [{{ $slaOnTrack }},{{ $selesai }},{{ $slaHMinus1 }},{{ $slaOver }}],
                backgroundColor: ['#10b981','#3b82f6','#f59e0b','#ef4444'],
                borderWidth:2, borderColor:'#fff', hoverOffset:5,
            }],
        },
        options: {
            cutout:'72%', responsive:true, maintainAspectRatio:true,
            plugins:{ legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.label}: ${c.parsed}`}} },
        },
    });

    // 5. STACKED BAR — DETAIL STATUS PER SEKSI
    // Data ini perlu ditambahkan di DashboardController (lihat catatan di bawah)
    const seksiStackedLabels = {!! json_encode($performaSeksi->pluck('nama')->values()) !!};
    const ssd = {!! json_encode($performaSeksi->map(fn($s)=>['pending'=>$s['pending']??0,'proses'=>$s['proses']??0,'diteruskan'=>$s['diteruskan']??0,'selesai'=>$s['selesai']??0])->values()) !!};

    new Chart(document.getElementById('chartSeksiStacked'), {
        type: 'bar',
        data: {
            labels: seksiStackedLabels,
            
            datasets: [
                { label:'Pending',    data:ssd.map(s=>s.pending),    backgroundColor:'#f59e0b', borderSkipped:false },
                { label:'Proses',     data:ssd.map(s=>s.proses),     backgroundColor:'#3b82f6', borderSkipped:false },
                { label:'Diteruskan', data:ssd.map(s=>s.diteruskan), backgroundColor:'#8b5cf6', borderSkipped:false },
                { label:'Selesai',    data:ssd.map(s=>s.selesai),    backgroundColor:'#10b981', borderSkipped:false, borderRadius:{topLeft:4,topRight:4} },
            ],
            
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            scales:{
                x:{ stacked:true, grid:{display:false}, ticks:{...tickOpts} },
                y:{ stacked:true, grid:{...gridOpts},   ticks:{...tickOpts, stepSize:1}, beginAtZero:true },
            },
            plugins:{
                legend:{
                    display:true, position:'bottom',
                    labels:{ boxWidth:10, padding:16, color:'#64748b', usePointStyle:true, pointStyleWidth:10 },
                },
            },
        },
    });
    </script>
</x-app-layout>