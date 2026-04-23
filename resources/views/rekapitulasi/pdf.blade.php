<!DOCTYPE html>
<html>
<head>
    <title>Rekapitulasi Pengaduan</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .summary { margin-bottom: 15px; }
        .footer { margin-top: 30px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAPITULASI PENGADUAN LAYANAN KEIMIGRASIAN</h2>
        <p>Periode: {{ $periode }} @if(isset($filters['seksi'])) | Seksi: {{ $filters['seksi'] }} @endif</p>
    </div>

    <div class="summary">
        <strong>Ringkasan:</strong> Total: {{ $summary['total'] }} | Selesai: {{ $summary['selesai'] }} | Proses: {{ $summary['proses'] }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tiket</th>
                <th>Tanggal</th>
                <th>Nama Pemohon</th>
                <th>Seksi</th>
                <th>Kanal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengaduans as $index => $p)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $p->nomor_tiket }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tgl_pengaduan)->format('d/m/Y') }}</td>
                <td>{{ $p->nama }}</td>
                <td>{{ $p->seksi_tujuan }}</td>
                <td>{{ $p->kanal_pengaduan }}</td>
                <td>{{ ucfirst($p->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>