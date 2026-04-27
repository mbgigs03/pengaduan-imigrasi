{{-- resources/views/dashboard/tikkim.blade.php --}}
@php use App\Helpers\StatusHelper; @endphp

<x-layouts.dashboard>
    <x-slot name="header">Dashboard TIKKIM</x-slot>

    <x-slot name="styles">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    </x-slot>

    <div class="p-6 space-y-6 max-w-[1400px]">

        {{-- ═══════════════════════════════════════════
             STAT CARDS
        ═══════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

            <div class="stat-card flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Pengaduan</p>
                    <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ number_format($totalBulanIni) }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">bulan ini</p>
                </div>
            </div>

            <div class="stat-card flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Selesai</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-0.5">{{ number_format($selesai) }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all"
                                 style="width:{{ $totalBulanIni > 0 ? round($selesai/$totalBulanIni*100) : 0 }}%"></div>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-600">{{ $totalBulanIni > 0 ? round($selesai/$totalBulanIni*100) : 0 }}%</span>
                    </div>
                </div>
            </div>

            <div class="stat-card flex items-start gap-4 {{ $slaOver > 0 ? 'border-red-100 bg-red-50/30' : '' }}">
                <div class="w-11 h-11 rounded-xl {{ $slaOver > 0 ? 'bg-red-100' : 'bg-slate-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $slaOver > 0 ? 'text-red-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Melebihi SLA</p>
                    <p class="text-2xl font-bold {{ $slaOver > 0 ? 'text-red-600' : 'text-slate-800' }} mt-0.5">{{ $slaOver }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">melewati 3 hari</p>
                </div>
            </div>

            <div class="stat-card flex items-start gap-4 {{ $slaHMinus1 > 0 ? 'border-amber-100 bg-amber-50/30' : '' }}">
                <div class="w-11 h-11 rounded-xl {{ $slaHMinus1 > 0 ? 'bg-amber-100' : 'bg-slate-100' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 {{ $slaHMinus1 > 0 ? 'text-amber-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">H-1 Deadline</p>
                    <p class="text-2xl font-bold {{ $slaHMinus1 > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-0.5">{{ $slaHMinus1 }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">perlu tindak lanjut</p>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             ROW 2: PIE + BAR HORIZONTAL
        ═══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- PIE: Status --}}
            <div class="chart-card">
                <p class="chart-title">Distribusi Status</p>
                <div class="flex items-center gap-6">
                    <div style="width:176px;height:176px;flex-shrink:0">
                        <canvas id="chartStatus"></canvas>
                    </div>
                    <div class="space-y-2.5 flex-1">
                        @php
                            $statusMeta = [
                                'pending'    => [StatusHelper::label('pending'),    '#f59e0b'],
                                'proses'     => [StatusHelper::label('proses'),     '#3b82f6'],
                                'diteruskan' => [StatusHelper::label('diteruskan'), '#8b5cf6'],
                                'selesai'    => [StatusHelper::label('selesai'),    '#10b981'],
                            ];
                            $totalStatus = $statusStats->sum('jumlah') ?: 1;
                        @endphp
                        @foreach ($statusStats as $s)
                            @php [$slabel, $scolor] = $statusMeta[$s->status] ?? [StatusHelper::label($s->status), '#94a3b8']; @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $scolor }}"></span>
                                        <span class="text-slate-600 font-medium">{{ $slabel }}</span>
                                    </div>
                                    <span class="font-bold text-slate-800">{{ $s->jumlah }}
                                        <span class="font-normal text-slate-400">({{ round($s->jumlah/$totalStatus*100) }}%)</span>
                                    </span>
                                </div>
                                <div class="h-1 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width:{{ round($s->jumlah/$totalStatus*100) }}%;background:{{ $scolor }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- BAR HORIZONTAL: Penyelesaian Seksi --}}
            <div class="chart-card">
                <p class="chart-title">Penyelesaian per Seksi</p>
                <canvas id="chartSeksi" style="max-height:196px"></canvas>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             ROW 3: BAR KANAL + DOUGHNUT SLA
        ═══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <div class="chart-card lg:col-span-2">
                <p class="chart-title">Volume per Kanal Pengaduan</p>
                <canvas id="chartKanal" style="max-height:196px"></canvas>
            </div>

            <div class="chart-card flex flex-col">
                <p class="chart-title">SLA Health</p>
                @php $slaOnTrack = max(0, $totalBulanIni - $selesai - $slaOver - $slaHMinus1); @endphp
                <div class="flex-1 flex flex-col items-center justify-center gap-3">
                    <div style="width:148px;height:148px">
                        <canvas id="chartSla"></canvas>
                    </div>
                    <div class="grid grid-cols-2 gap-x-5 gap-y-2 w-full">
                        @foreach([
                            ['On Track', $slaOnTrack, '#10b981'],
                            ['Selesai',  $selesai,    '#3b82f6'],
                            ['H-1',      $slaHMinus1, '#f59e0b'],
                            ['Over SLA', $slaOver,    '#ef4444'],
                        ] as [$l, $v, $c])
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
                                <span class="text-slate-500">{{ $l }}</span>
                                <span class="font-bold text-slate-700 ml-auto">{{ $v }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             ROW 4: STACKED BAR
        ═══════════════════════════════════════════ --}}
        <div class="chart-card">
            <p class="chart-title">Breakdown Status per Seksi</p>
            <canvas id="chartSeksiStacked" style="max-height:196px"></canvas>
        </div>
    </div>

    <x-modal-tindak-lanjut />

    <x-slot name="scripts">
    <script>
    Chart.defaults.font.family = "'Plus Jakarta Sans','sans-serif'";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#94a3b8';
    const G = { color:'#f1f5f9', drawBorder:false };
    const T = { color:'#94a3b8' };

    // Label SOP baru untuk chart
    const statusLabelMap = {
        'pending':    'Menunggu Verifikasi',
        'proses':     'Disposisi Kasi',
        'diteruskan': 'Sedang Ditindaklanjuti',
        'selesai':    'Selesai',
    };

    new Chart(document.getElementById('chartStatus'), {
        type:'pie',
        data:{
            labels: {!! json_encode($statusStats->pluck('status')->map(fn($s) => match($s) {
                'pending'    => 'Menunggu Verifikasi',
                'proses'     => 'Disposisi Kasi',
                'diteruskan' => 'Sedang Ditindaklanjuti',
                'selesai'    => 'Selesai',
                default      => ucfirst($s),
            })->values()) !!},
            datasets:[{
                data:{!! json_encode($statusStats->pluck('jumlah')->values()) !!},
                backgroundColor:{!! json_encode($statusStats->pluck('status')->map(fn($s)=>match($s){
                    'pending'=>'#f59e0b','proses'=>'#3b82f6','diteruskan'=>'#8b5cf6','selesai'=>'#10b981',default=>'#94a3b8'
                })->values()) !!},
                borderWidth:2,borderColor:'#fff',hoverOffset:6,
            }],
        },
        options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}}},
    });

    new Chart(document.getElementById('chartSeksi'), {
        type:'bar',
        data:{
            labels:{!! json_encode($performaSeksi->pluck('nama')->values()) !!},
            datasets:[{
                label:'Selesai (%)',
                data:{!! json_encode($performaSeksi->pluck('pct')->values()) !!},
                backgroundColor:{!! json_encode($performaSeksi->pluck('pct')->map(fn($v)=>$v>=80?'#10b981':($v>=50?'#3b82f6':'#f59e0b'))->values()) !!},
                borderRadius:6,borderSkipped:false,
            }],
        },
        options:{
            indexAxis:'y',responsive:true,maintainAspectRatio:false,
            scales:{x:{grid:G,ticks:{...T,callback:v=>v+'%'},min:0,max:100},y:{grid:{display:false},ticks:T}},
            plugins:{legend:{display:false}},
        },
    });

    const kP=['#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#06b6d4'];
    const kL={!! json_encode($kanalStats->pluck('kanal_pengaduan')->values()) !!};
    new Chart(document.getElementById('chartKanal'), {
        type:'bar',
        data:{
            labels:kL,
            datasets:[{label:'Jumlah',data:{!! json_encode($kanalStats->pluck('jumlah')->values()) !!},
            backgroundColor:kL.map((_,i)=>kP[i%kP.length]),borderRadius:6,borderSkipped:false}],
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            scales:{x:{grid:{display:false},ticks:T},y:{grid:G,ticks:{...T,stepSize:1},beginAtZero:true}},
            plugins:{legend:{display:false}},
        },
    });

    new Chart(document.getElementById('chartSla'), {
        type:'doughnut',
        data:{
            labels:['On Track','Selesai','H-1','Over SLA'],
            datasets:[{data:[{{ $slaOnTrack }},{{ $selesai }},{{ $slaHMinus1 }},{{ $slaOver }}],
            backgroundColor:['#10b981','#3b82f6','#f59e0b','#ef4444'],borderWidth:2,borderColor:'#fff',hoverOffset:5}],
        },
        options:{cutout:'72%',responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}}},
    });

    const ssl={!! json_encode($performaSeksi->pluck('nama')->values()) !!};
    const ssd={!! json_encode($performaSeksi->map(fn($s)=>['pending'=>$s['pending']??0,'proses'=>$s['proses']??0,'diteruskan'=>$s['diteruskan']??0,'selesai'=>$s['selesai']??0])->values()) !!};
    new Chart(document.getElementById('chartSeksiStacked'), {
        type:'bar',
        data:{
            labels:ssl,
            datasets:[
                {label:'Menunggu Verifikasi',    data:ssd.map(s=>s.pending),    backgroundColor:'#f59e0b',borderSkipped:false},
                {label:'Disposisi Kasi',         data:ssd.map(s=>s.proses),     backgroundColor:'#3b82f6',borderSkipped:false},
                {label:'Sedang Ditindaklanjuti', data:ssd.map(s=>s.diteruskan), backgroundColor:'#8b5cf6',borderSkipped:false},
                {label:'Selesai',                data:ssd.map(s=>s.selesai),    backgroundColor:'#10b981',borderSkipped:false,borderRadius:{topLeft:4,topRight:4}},
            ],
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            scales:{x:{stacked:true,grid:{display:false},ticks:T},y:{stacked:true,grid:G,ticks:{...T,stepSize:1},beginAtZero:true}},
            plugins:{legend:{display:true,position:'bottom',labels:{boxWidth:10,padding:14,color:'#64748b',usePointStyle:true}}},
        },
    });
    </script>
    </x-slot>

</x-layouts.dashboard>