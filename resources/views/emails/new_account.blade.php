<!DOCTYPE html>
<html>

<head>
    <title>Akun Baru Berhasil Didaftarkan</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333;">
    <h3>Halo, {{ $userBaru->name }}!</h3>

    <p>Admin telah mendaftarkan akun Anda di Website Pengelolaan Tagihan Mitra PT. Telkom Akses Tasikmalaya.</p>

    <p>Berikut adalah detail informasi login akun Anda:</p>
    <table style="border-collapse: collapse; width: 100%; max-width: 400px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 6px 0; width: 120px; font-size: 16px;"><strong>Email</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: {{ $userBaru->email }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-size: 16px;"><strong>Password</strong></td>
            <td style="padding: 6px 0; color: #e31a1a; font-size: 16px;">: <strong>{{ $rawPassword }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-size: 16px;"><strong>Role Akses</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: {{ $userBaru->role }}</td>
        </tr>
    </table>

    <p>Silakan masuk ke dalam sistem untuk mulai mengelola dokumen tagihan Anda:</p>

    <p style="margin-top: 20px; margin-bottom: 20px;">
        <a href="{{ config('app.url') }}/admin"
            style="background-color: #e31a1a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;">
            Login ke Website PATA
        </a>
    </p>

    <p style="font-size: 14px; color: #666;">
        Atau salin tautan berikut ke browser Anda jika tombol tidak berfungsi:<br>
        <a href="{{ config('app.url') }}/admin">{{ config('app.url') }}/admin</a>
    </p>

    <hr style="border: 0; border-top: 1px solid #eee; margin-top: 30px;">
    <p style="font-size: 14px; color: #888;">Mohon segera ubah password bawaan Anda di menu profil setelah berhasil
        login demi menjaga keamanan data pekerjaan.</p>
    <p style="font-size: 14px; color: #888;">Salam hangat,<br><strong>Admin Tagihan Telkom Akses</strong></p>
</body>

</html>
