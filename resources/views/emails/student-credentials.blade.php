<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kredensial Login SPMB</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .info-box {
            background-color: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .credentials-box {
            background-color: #fff9e6;
            border: 2px dashed #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #92400e;
        }
        .credential-item {
            margin: 15px 0;
            padding: 10px;
            background-color: white;
            border-radius: 5px;
        }
        .credential-item label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .credential-item .value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            background-color: #f5f5f5;
            padding: 8px 12px;
            border-radius: 4px;
            display: block;
        }
        .warning-box {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning-box strong {
            color: #92400e;
        }
        .btn {
            display: inline-block;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 SPMB SMKN 8 TIK KOTA JAYAPURA</h1>
            <p>Kredensial Login Anda</p>
        </div>

        <p>Yth. <strong>{{ $student->full_name }}</strong>,</p>

        <p>Terima kasih telah mendaftar di SPMB SMKN 8 TIK KOTA JAYAPURA. Berikut adalah kredensial login Anda:</p>

        <div class="info-box">
            <strong>📋 Nomor Pendaftaran:</strong><br>
            <span style="font-size: 18px; color: #667eea;">{{ $student->registration_number }}</span>
        </div>

        <div class="credentials-box">
            <h3>🔐 Informasi Login</h3>
            
            <div class="credential-item">
                <label>Email Login</label>
                <span class="value">{{ $student->email }}</span>
            </div>

            <div class="credential-item">
                <label>Password</label>
                <span class="value">{{ $password }}</span>
            </div>

            <div class="credential-item">
                <label>URL Login</label>
                <span class="value">{{ route('student.login') }}</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('student.login') }}" class="btn">Login Sekarang</a>
        </div>

        <div class="warning-box">
            <strong>⚠️ PENTING:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Simpan email ini dengan baik</li>
                <li>Jangan berikan password kepada siapapun</li>
                <li>Segera ubah password setelah login pertama kali</li>
                <li>Pastikan data yang Anda isi sudah benar</li>
            </ul>
        </div>

        <div class="info-box">
            <strong>📞 Kontak Panitia:</strong><br>
            Email: {{ env('SCHOOL_EMAIL') }}<br>
            Telepon: {{ env('SCHOOL_PHONE') }}
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem SPMB SMKN 8 TIK KOTA JAYAPURA.</p>
            <p>Jika Anda mengalami kesulitan, silakan hubungi panitia melalui email atau telepon di atas.</p>
        </div>
    </div>
</body>
</html>
