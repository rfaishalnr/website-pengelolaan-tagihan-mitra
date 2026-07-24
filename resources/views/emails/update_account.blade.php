<!DOCTYPE html>
<html>
<head>
    <title>Pembaruan Informasi Akun</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333;">
    <h3>Halo, {{ $user->name }}!</h3>

    <p>Admin baru saja melakukan <strong>pembaruan informasi pada akun Anda</strong> di Website Pengelolaan Tagihan Mitra PT. Telkom Akses Tasikmalaya.</p>

    <p>Berikut adalah detail informasi login dan hak akses Anda saat ini:</p>
    <table style="border-collapse: collapse; width: 100%; max-width: 450px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 6px 0; width: 130px; font-size: 16px;"><strong>Email</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: {{ $user->email }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-size: 16px;"><strong>Role / Hak Akses</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: <strong><span style="background-color: #eee; padding: 2px 8px; border-radius: 4px;">{{ strtoupper($user->role) }}</span></strong></td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-size: 16px;"><strong>Password</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: 
                @if($passwordBaru)
                    <strong style="color: #e31a1a;">{{ $passwordBaru }}</strong>
                @else
                    <span style="color: #888; font-style: italic;">(Tidak ada perubahan)</span>
                @endif
            </td>
        </tr>
    </table>

    <p>Silakan gunakan informasi di atas untuk masuk ke dalam sistem.</p>

    <p style="margin-top: 20px; margin-bottom: 20px;">
        <a href="{{ config('app.url') }}/admin"
            style="background-color: #e31a1a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;">
            Login ke Website PATA
        </a>
    </p>

    <hr style="border: 0; border-top: 1px solid #eee; margin-top: 30px;">
    
    <p style="font-size: 14px; color: #444; background-color: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #e31a1a;">
        <strong>Butuh Bantuan?</strong><br>
        Jika Anda merasa tidak meminta pembaruan data atau menemukan ketidaksesuaian pada hak akses Anda, silakan sampaikan keluhan Anda ke: <br>
        <a href="mailto:admin.pata@telkomakses.co.id" style="color: #e31a1a; font-weight: bold;">admin.pata@telkomakses.co.id</a>
    </p>

    <p style="font-size: 14px; color: #888;">Salam hangat,<br><strong>Admin Tagihan Telkom Akses</strong></p>
</body>
</html>