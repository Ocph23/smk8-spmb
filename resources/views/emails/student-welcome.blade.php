<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun SPMB Berhasil Dibuat</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
        .container { background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; margin: -30px -30px 30px -30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 8px 0 0 0; opacity: 0.9; font-size: 14px; }
        .credentials-box { background-color: #fff9e6; border: 2px dashed #f59e0b; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .credentials-box h3 { margin-top: 0; color: #92400e; }
        .credential-item { margin: 12px 0; padding: 10px; background-color: white; border-radius: 5px; }
        .credential-item label { display: block; font-size: 11px; color: #666; margin-bottom: 4px; text-transform: uppercase; font-weight: 600; }
        .credential-item .value { font-size: 15px; color: #333; font-weight: 600; font-family: 'Courier New', monospace; background-color: #f5f5f5; padding: 6px 10px; border-radius: 4px; display: block; }
        .steps-box { background-color: #f0fdf4; border-left: 4px solid #22c55e; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .steps-box h3 { margin-top: 0; color: #15803d; }
        .steps-box ol { margin: 8px 0 0 0; padding-left: 20px; }
        .steps-box li { margin-bottom: 6px; }
        .warning-box { background-color: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .btn { display: inline-block; background-color: #667eea; color: white; text-decoration: none; padding: 12px 30px; border-radius: 5px; margin: 20px 0; font-weight: 600; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 13px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 SPMB SMKN 8 TIK KOTA JAYAPURA</h1>
            <p>Akun Anda Berhasil Dibuat</p>
        </div>

        <p>Yth. Calon Peserta Didik Baru,</p>
        <p>Akun SPMB Anda telah berhasil dibuat. Berikut informasi login Anda:</p>

        <div class="credentials-box">
            <h3>🔐 Informasi Login</h3>
            <div class="credential-item">
                <label>Email</label>
                <span class="value">{{ $student->email }}</span>
            </div>
            <div class="credential-item">
                <label>Password</label>
                <span class="value">{{ $plainPassword }}</span>
            </div>
            <div class="credential-item">
                <label>URL Login</label>
                <span class="value">{{ route('student.login') }}</span>
            </div>
        </div>

        <div class="steps-box">
            <h3>📋 Langkah Selanjutnya</h3>
            <ol>
                <li>Login menggunakan email dan password di atas</li>
                <li>Klik menu <strong>"Lengkapi Pendaftaran"</strong> di dashboard</li>
                <li>Isi seluruh data diri dengan lengkap dan benar</li>
                <li>Upload dokumen yang diperlukan (Ijazah, KK, Akta, Pas Foto)</li>
                <li>Pilih jurusan yang diminati</li>
                <li>Simpan dan tunggu verifikasi dari panitia</li>
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('student.login') }}" class="btn">Login & Lengkapi Pendaftaran</a>
        </div>

        <div class="warning-box">
            <strong>⚠️ PENTING:</strong>
            <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                <li>Simpan email ini sebagai bukti pembuatan akun</li>
                <li>Jangan berikan password kepada siapapun</li>
                <li>Segera lengkapi data pendaftaran sebelum batas waktu</li>
            </ul>
        </div>

        <div style="background:#f0f4ff; border-left:4px solid #667eea; padding:15px; border-radius:5px;">
            <strong>📞 Kontak Panitia:</strong><br>
            Email: info@smkn8jayapura.sch.id<br>
            Telepon: 0851-8681-0279
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem SPMB SMKN 8 TIK KOTA JAYAPURA.</p>
        </div>
    </div>
</body>
</html>
