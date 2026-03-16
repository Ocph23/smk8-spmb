<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran SPMB - {{ $student->registration_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .container {
            border: 2px solid #2563eb;
            border-radius: 10px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 18pt;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14pt;
            color: #666;
            font-weight: normal;
        }

        .header .school-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
            margin-top: 10px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-section h3 {
            font-size: 13pt;
            color: #1e40af;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-label {
            display: table-cell;
            width: 180px;
            font-weight: bold;
            color: #555;
        }

        .info-separator {
            display: table-cell;
            width: 10px;
        }

        .info-value {
            display: table-cell;
            color: #333;
        }

        .majors-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .majors-table th,
        .majors-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .majors-table th {
            background-color: #f0f7ff;
            color: #1e40af;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .footer p {
            margin-bottom: 5px;
        }

        .note {
            margin-top: 20px;
            padding: 15px;
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            font-size: 11pt;
        }

        .note h4 {
            color: #92400e;
            margin-bottom: 5px;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(37, 99, 235, 0.05);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="watermark">SPMB</div>

    <div class="container">
        <div class="header">
            <h1>BUKTI PENDAFTARAN SPMB</h1>
            <h2>Tahun Ajaran {{ date('Y') }}/2027</h2>
            <div class="school-name">SMK NEGERI 8 TIK KOTA JAYAPURA</div>
        </div>

        <div class="info-section">
            <h3>Data Pendaftaran</h3>
            <div class="info-row">
                <div class="info-label">Nomor Pendaftaran</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->registration_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Daftar</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->created_at->format('d F Y, H:i') }} WIT</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status Verifikasi</div>
                <div class="info-separator">:</div>
                <div class="info-value">
                    @if ($student->verification_status === 'pending')
                        <span style="color: #f59e0b;">⏳ Menunggu Verifikasi</span>
                    @elseif($student->verification_status === 'verified')
                        <span style="color: #10b981;">✓ Terverifikasi</span>
                    @else
                        <span style="color: #ef4444;">✗ Ditolak</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>Data Pribadi</h3>
            <div class="info-row">
                <div class="info-label">Nama Lengkap</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->full_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">NIK</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->nik }}</div>
            </div>
            @if ($student->nisn)
                <div class="info-row">
                    <div class="info-label">NISN</div>
                    <div class="info-separator">:</div>
                    <div class="info-value">{{ $student->nisn }}</div>
                </div>
            @endif
            <div class="info-row">
                <div class="info-label">Tempat, Tanggal Lahir</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->place_of_birth }}, {{ $student->date_of_birth->format('d F Y') }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Jenis Kelamin</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">No. Telepon</div>
                <div class="info-separator">:</div>
                <div class="info-value">{{ $student->phone }}</div>
            </div>
        </div>

        <div class="info-section">
            <h3>Pilihan Jurusan</h3>
            <table class="majors-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Pilihan</th>
                        <th>Jurusan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($student->majors as $major)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if ($major->pivot->preference == 1)
                                    Pilihan 1
                                @elseif($major->pivot->preference == 2)
                                    Pilihan 2
                                @else
                                    Pilihan 3
                                @endif
                            </td>
                            <td>{{ $major->name }} ({{ $major->code }})</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($student->is_accepted && $student->acceptedMajor)
            <div class="info-section"
                style="background-color: #f0fdf4; padding: 15px; border-radius: 5px; border-left: 4px solid #10b981;">
                <h3 style="color: #10b981; border-color: #10b981;">Hasil Seleksi</h3>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-separator">:</div>
                    <div class="info-value"><strong style="color: #10b981;">✓ DITERIMA</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jurusan</div>
                    <div class="info-separator">:</div>
                    <div class="info-value"><strong>{{ $student->acceptedMajor->name }}</strong></div>
                </div>
            </div>
        @endif

        <div class="note">
            <h4>Catatan Penting:</h4>
            <p>1. Simpan bukti pendaftaran ini dengan baik sebagai tanda bukti Anda telah mendaftar.</p>
            <p>2. Proses verifikasi akan dilakukan dalam waktu 1-3 hari kerja.</p>
            <p>3. Hasil seleksi akan diumumkan melalui website SPMB SMKN 8 TIK Kota Jayapura.</p>
            <p>4. Untuk informasi lebih lanjut, hubungi panitia SPMB di nomor yang tersedia.</p>
        </div>

        <div class="footer">
            <p><strong>SMK NEGERI 8 TIK KOTA JAYAPURA</strong></p>
            <p>JL. Gelanggan II RT 04 RW 01 Keluaran Waena, Distrik Heram, Kota Jayapura, Papua</p>
            <p>Email: admin@smkn8tikjayapura.sch.id</p>
            <p style="margin-top: 15px; font-size: 10pt;">Dokumen ini dicetak pada {{ now()->format('d F Y, H:i') }}
                WIT</p>
        </div>
    </div>
</body>

</html>
