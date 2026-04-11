<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $inboxMessage->subject }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
        .container { background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; margin: -30px -30px 30px -30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 8px 0 0 0; opacity: 0.9; font-size: 14px; }
        .subject-box { background-color: #f0f4ff; border-left: 4px solid #667eea; padding: 12px 16px; margin-bottom: 20px; border-radius: 0 6px 6px 0; }
        .subject-box .label { font-size: 11px; color: #666; text-transform: uppercase; font-weight: 600; margin-bottom: 4px; }
        .subject-box .value { font-size: 16px; font-weight: 700; color: #1e293b; }
        .message-body { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; white-space: pre-line; font-size: 14px; color: #334155; }
        .sender-info { font-size: 12px; color: #94a3b8; margin-top: 16px; }
        .btn { display: inline-block; background-color: #667eea; color: white; text-decoration: none; padding: 12px 30px; border-radius: 5px; margin: 20px 0; font-weight: 600; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 13px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 SPMB SMKN 8 TIK KOTA JAYAPURA</h1>
            <p>Pesan Masuk</p>
        </div>

        <p>Yth. <strong>{{ $inboxMessage->student->full_name }}</strong>,</p>
        <p>Anda menerima pesan baru dari {{ $inboxMessage->is_system ? 'Sistem SPMB' : 'Panitia SPMB' }}.</p>

        <div class="subject-box">
            <div class="label">Perihal</div>
            <div class="value">{{ $inboxMessage->subject }}</div>
        </div>

        <div class="message-body">{{ $inboxMessage->message }}</div>

        <div class="sender-info">
            Dikirim pada: {{ $inboxMessage->created_at->format('d M Y, H:i') }} WIT
        </div>

        <div style="text-align: center;">
            <a href="{{ route('student.inbox') }}" class="btn">Buka Kotak Masuk</a>
        </div>

        <div style="background:#f0f4ff; border-left:4px solid #667eea; padding:15px; border-radius:5px; margin-top:20px;">
            <strong>📞 Kontak Panitia:</strong><br>
            Email: {{ env('SCHOOL_EMAIL') }}<br>
            Telepon: {{ env('SCHOOL_PHONE') }}
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis karena Anda menerima pesan di inbox SPMB SMKN 8 TIK KOTA JAYAPURA.</p>
        </div>
    </div>
</body>
</html>
