<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password SPMB</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
        .container { background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; margin: -30px -30px 30px -30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .btn { display: inline-block; background-color: #667eea; color: white; text-decoration: none; padding: 14px 36px; border-radius: 6px; margin: 20px 0; font-weight: 600; font-size: 16px; }
        .warning-box { background-color: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 5px; font-size: 14px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 13px; color: #666; text-align: center; }
        .url-box { background:#f5f5f5; padding:10px; border-radius:4px; font-family:monospace; font-size:13px; word-break:break-all; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 SPMB SMKN 8 TIK KOTA JAYAPURA</h1>
        </div>

        <h2 style="color:#333;">Reset Password</h2>
        <p>Kami menerima permintaan reset password untuk akun dengan email <strong>{{ $email }}</strong>.</p>
        <p>Klik tombol di bawah untuk membuat password baru. Link ini berlaku selama <strong>60 menit</strong>.</p>

        <div style="text-align:center;">
            <a href="{{ url('/siswa/reset-password/' . $token . '?email=' . urlencode($email)) }}" class="btn">
                Reset Password Sekarang
            </a>
        </div>

        <p style="font-size:14px; color:#666;">Atau salin link berikut ke browser Anda:</p>
        <div class="url-box">{{ url('/siswa/reset-password/' . $token . '?email=' . urlencode($email)) }}</div>

        <div class="warning-box">
            <strong>⚠️ Perhatian:</strong>
            <ul style="margin:8px 0 0 0; padding-left:20px;">
                <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                <li>Link ini hanya berlaku 60 menit</li>
                <li>Jangan bagikan link ini kepada siapapun</li>
            </ul>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem SPMB SMKN 8 TIK KOTA JAYAPURA.</p>
        </div>
    </div>
</body>
</html>
