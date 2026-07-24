<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\MitraPendaftaran;
use App\Models\BoqLine;
use App\Models\Pengajuan; // <--- JANGAN LUPA IMPORT MODEL BARU
use Illuminate\Support\Facades\Auth; // <--- IMPORT AUTH

class StatsOverview extends BaseWidget
{
    // Opsional: Atur polling agar data update otomatis tiap 15 detik
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $user = Auth::user();

        // --- TAMPILAN KHUSUS ADMIN ---
        if ($user->role === 'admin') {
            return [
                // 1. Statistik Pengajuan (YANG BARU)
                Stat::make('Total Pengajuan Masuk', Pengajuan::count())
                    ->description('Jumlah berkas fisik yang diinput')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->chart([7, 2, 10, 3, 15, 4, 17])
                    ->color('primary'),

                // 2. Statistik Mitra Pendaftaran (YANG LAMA)
                Stat::make('Total Mitra', MitraPendaftaran::count())
                    ->description('Total Mitra Yang Terdaftar')
                    ->color('gray'),

                Stat::make('Diterima (ACC)', Pengajuan::where('status', 'acc')->count())
                    ->description('Berkas valid & disetujui')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('success'), // Hijau

                Stat::make('Ditolak (Revisi)', Pengajuan::where('status', 'tolak')->count())
                    ->description('Berkas perlu perbaikan')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'), // Merah
            ];
        }

        // --- TAMPILAN KHUSUS MITRA ---
        // Mitra hanya melihat data miliknya sendiri
        return [
            Stat::make('Menunggu Validasi', Pengajuan::where('user_id', $user->id)->where('status', 'pending')->count())
                ->description('Berkas sedang diperiksa Telkom')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Diterima (ACC)', Pengajuan::where('user_id', $user->id)->where('status', 'acc')->count())
                ->description('Berkas valid dan diterima')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Ditolak (Revisi)', Pengajuan::where('user_id', $user->id)->where('status', 'tolak')->count())
                ->description('Perlu perbaikan segera')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}