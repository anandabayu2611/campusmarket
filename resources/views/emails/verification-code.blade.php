<!doctype html>
<html lang="id">
<body style="font-family:Arial,sans-serif;color:#1d2433;max-width:480px;margin:auto">
    <h2 style="color:#2f6fed">CampusMarket</h2>
    <p>Halo {{ $name }},</p>
    <p>Gunakan kode berikut untuk memverifikasi email universitas Anda:</p>
    <p style="font-size:32px;letter-spacing:8px;font-weight:bold;background:#f5f7fb;padding:14px;text-align:center;border-radius:8px">{{ $code }}</p>
    <p>Kode berlaku selama {{ $ttlMinutes }} menit. Jangan bagikan kode ini kepada siapa pun.</p>
    <p style="color:#667085;font-size:13px">Jika Anda tidak merasa mendaftar di CampusMarket, abaikan email ini.</p>
</body>
</html>
