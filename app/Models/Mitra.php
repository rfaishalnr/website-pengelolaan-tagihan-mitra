<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Mitra extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_mitra',
        'no_khs_mitra',
        'amd_khs_mitra_1',
        'amd_khs_mitra_2',
        'amd_khs_mitra_3',
        'amd_khs_mitra_4',
        'amd_khs_mitra_5',
        'direktur_mitra',
        'jabatan_mitra',
        'alamat_kantor',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("MITRA BARU: {$record->nama_mitra} (ID: {$record->id}) ditambahkan oleh User ID: {$actor}");
        });

        static::updated(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("MITRA DIUPDATE: {$record->nama_mitra} (ID: {$record->id}) diubah oleh User ID: {$actor}");
        });

        static::deleted(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::warning("MITRA DIHAPUS: {$record->nama_mitra} (ID: {$record->id}) dihapus oleh User ID: {$actor}");
        });
    }
}