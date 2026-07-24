<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PejabatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori',
        'nama',
        'jabatan',
        'posisi',
        'nik',
        'awal',
        'akhir',
    ];

    protected $casts = [
        'awal' => 'date',
        'akhir' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("HISTORY PEJABAT BARU: {$record->nama} sebagai {$record->jabatan} (ID: {$record->id}) ditambahkan oleh User ID: {$actor}");
        });

        static::updated(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("HISTORY PEJABAT DIUPDATE: ID {$record->id} diubah oleh User ID: {$actor}");
        });

        static::deleted(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::warning("HISTORY PEJABAT DIHAPUS: ID {$record->id} dihapus oleh User ID: {$actor}");
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}