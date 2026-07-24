<!DOCTYPE html>
<html>
<head>
    <title>Pemberitahuan Status Berkas</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333;">
    <h3>Halo, {{ $pengajuan->user->name ?? 'Mitra' }}!</h3>

    <p>Admin PT. Telkom Akses Tasikmalaya baru saja memperbarui status pengajuan dokumen tagihan Anda.</p>

    <p>Berikut adalah detail status berkas Anda saat ini:</p>
    <table style="border-collapse: collapse; width: 100%; max-width: 500px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 6px 0; width: 140px; font-size: 16px;"><strong>Nomor SP</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: {{ $pengajuan->nomor_sp }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-size: 16px;"><strong>Nama Pekerjaan</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: {{ $pengajuan->nama_pekerjaan }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; font-size: 16px;"><strong>Status Terkini</strong></td>
            <td style="padding: 6px 0; font-size: 16px;">: 
                @if($pengajuan->status === 'acc')
                    <strong style="color: #16a34a; background-color: #dcfce7; padding: 4px 8px; border-radius: 4px;">DITERIMA (ACC)</strong>
                @elseif($pengajuan->status === 'tolak')
                    <strong style="color: #dc2626; background-color: #fee2e2; padding: 4px 8px; border-radius: 4px;">DITOLAK / REVISI</strong>
                @else
                    <strong style="color: #d97706; background-color: #fef3c7; padding: 4px 8px; border-radius: 4px;">MENUNGGU</strong>
                @endif
            </td>
        </tr>
    </table>

    @if($pengajuan->status === 'tolak')
    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #dc2626; margin-bottom: 20px;">
        <strong style="font-size: 14px; color: #444;">Catatan / Alasan Penolakan:</strong><br>
        <p style="margin-top: 5px; font-size: 15px; margin-bottom: 0;">
            {!! nl2br(e($pengajuan->catatan ?? 'Tidak ada catatan tambahan.')) !!}
        </p>
    </div>
    @endif

    <p>Silakan masuk ke dalam sistem untuk melihat detail lebih lanjut atau melakukan tindakan yang diperlukan:</p>

    <p style="margin-top: 20px; margin-bottom: 20px;">
        <a href="{{ config('app.url') }}/admin"
            style="background-color: #e31a1a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;">
            Login ke Website PATA
        </a>
    </p>

    <hr style="border: 0; border-top: 1px solid #eee; margin-top: 30px;">
    <p style="font-size: 14px; color: #888;">Pesan ini dibuat otomatis oleh sistem. Mohon tidak membalas langsung ke alamat email ini.</p>
</body>
</html>