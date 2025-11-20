<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP - HR Yayasan Darussalam</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            background-color: #1e4d8b;
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .otp-code {
            background-color: #ffffff;
            border: 2px dashed #1e4d8b;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            font-size: 32px;
            font-weight: bold;
            color: #1e4d8b;
            letter-spacing: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HR YAYASAN DARUSSALAM</h1>
        </div>
        
        <h2>Kode OTP untuk Reset Password</h2>
        
        <p>Halo,</p>
        
        <p>Anda telah meminta untuk mereset password akun Anda. Gunakan kode OTP berikut untuk melanjutkan proses reset password:</p>
        
        <div class="otp-code">
            {{ $kodeOtp }}
        </div>
        
        <div class="warning">
            <strong>⚠️ Peringatan:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Kode OTP ini hanya berlaku selama <strong>15 menit</strong></li>
                <li>Jangan bagikan kode OTP ini kepada siapapun</li>
                <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
            </ul>
        </div>
        
        <p>Kode OTP akan kadaluarsa pada: <strong>{{ $expiresAt }}</strong></p>
        
        <p>Jika Anda tidak meminta reset password, silakan abaikan email ini atau hubungi administrator sistem.</p>
        
        <p>Terima kasih,<br>
        <strong>Tim HR Yayasan Darussalam</strong></p>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon jangan membalas email ini.</p>
            <p>&copy; {{ date('Y') }} HR Yayasan Darussalam. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

