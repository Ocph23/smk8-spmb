<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendaftaran SPMB</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            color: #666;
        }
        
        .header .school-name {
            font-size: 16pt;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .info-table td {
            padding: 5px 0;
        }
        
        .info-table td:first-child {
            width: 150px;
            font-weight: bold;
        }
        
        .info-table td:nth-child(2) {
            width: 10px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9fafb;
        }
        
        .stat-box h3 {
            font-size: 10pt;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-box .value {
            font-size: 24pt;
            font-weight: bold;
            color: #2563eb;
        }
        
        .stat-box.diterima .value {
            color: #16a34a;
        }
        
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1e40af;
            margin: 20px 0 10px 0;
            padding: 8px 12px;
            background: #eff6ff;
            border-left: 4px solid #2563eb;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        table.data-table th {
            background: #1e40af;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
        }
        
        table.data-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        table.data-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        table.data-table tr:hover {
            background: #eff6ff;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-verified {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-yes {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-no {
            background: #f3f4f6;
            color: #374151;
        }
        
        .major-stats {
            margin-bottom: 20px;
        }
        
        .major-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .major-table th {
            background: #1e40af;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .major-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .major-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            font-size: 9pt;
            color: #666;
        }
        
        .footer-row {
            display: table;
            width: 100%;
        }
        
        .footer-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .signature-box {
            text-align: center;
            padding: 10px;
        }
        
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: bold;
        }
        
        @page {
            margin: 15mm;
            size: A4 portrait;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN PENDAFTARAN</h1>
        <h2>SELEKSI PENERIMAAN PESERTA DIDIK BARU (SPMB)</h2>
        <div class="school-name">SMK NEGERI 8 KOTA JAYAPURA</div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Tanggal Cetak</td>
                <td>:</td>
                <td>{{ $generated_at }}</td>
            </tr>
            @if($filters['major'])
            <tr>
                <td>Filter Jurusan</td>
                <td>:</td>
                <td>{{ $filters['major'] }}</td>
            </tr>
            @endif
            @if($filters['verification_status'])
            <tr>
                <td>Filter Status Verifikasi</td>
                <td>:</td>
                <td>{{ ucfirst($filters['verification_status']) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stat-box">
                <h3>Total Pendaftar</h3>
                <div class="value">{{ $stats['total_pendaftar'] }}</div>
            </div>
            <div class="stat-box">
                <h3>Laki-laki</h3>
                <div class="value">{{ $stats['laki_laki'] }}</div>
            </div>
            <div class="stat-box">
                <h3>Perempuan</h3>
                <div class="value">{{ $stats['perempuan'] }}</div>
            </div>
            <div class="stat-box diterima">
                <h3>Diterima</h3>
                <div class="value">{{ $stats['diterima'] }}</div>
            </div>
        </div>
    </div>

    <!-- Major Statistics -->
    <div class="section-title">STATISTIK PER JURUSAN</div>
    <table class="major-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Jurusan</th>
                <th style="width: 20%;">Kode</th>
                <th style="width: 20%;">Total Pendaftar</th>
                <th style="width: 20%;">Diterima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($majorStats as $index => $major)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $major->name }}</td>
                <td>{{ $major->code }}</td>
                <td class="text-center">{{ $major->total_pendaftar }}</td>
                <td class="text-center">{{ $major->diterima }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Student List -->
    <div class="section-title">DAFTAR PENDAFTAR</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">No. Daftar</th>
                <th style="width: 25%;">Nama Lengkap</th>
                <th style="width: 10%;">L/P</th>
                <th style="width: 15%;">Pilihan 1</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 10%;">Diterima</th>
                <th style="width: 10%;">Jurusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
            @php
                $pilihan1 = $student->majors->firstWhere('pivot.preference', 1);
                $statusClass = 'badge-' . $student->verification_status;
                $statusLabel = ucfirst($student->verification_status);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $student->registration_number }}</td>
                <td>{{ $student->full_name }}</td>
                <td class="text-center">{{ $student->gender === 'male' ? 'L' : 'P' }}</td>
                <td>{{ $pilihan1 ? $pilihan1->name : '-' }}</td>
                <td class="text-center">
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td class="text-center">
                    <span class="badge {{ $student->is_accepted ? 'badge-yes' : 'badge-no' }}">
                        {{ $student->is_accepted ? 'Ya' : 'Belum' }}
                    </span>
                </td>
                <td>{{ $student->acceptedMajor ? $student->acceptedMajor->name : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">
                    <em>Belum ada data pendaftar</em>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-row">
            <div class="footer-cell">
                <div class="signature-box">
                    <p>Mengetahui,<br>Kepala SMK Negeri 8 Kota Jayapura</p>
                    <div class="signature-line">
                        (________________________)<br>
                        NIP. ...........................
                    </div>
                </div>
            </div>
            <div class="footer-cell">
                <div class="signature-box">
                    <p>Jayapura, {{ now()->format('d F Y') }}<br>Ketua Panitia SPMB</p>
                    <div class="signature-line">
                        (________________________)<br>
                        NIP. ...........................
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
