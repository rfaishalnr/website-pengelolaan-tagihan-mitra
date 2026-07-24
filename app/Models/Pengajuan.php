<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Mail\StatusBerkasMail;

class Pengajuan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke tabel Users
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::updating(function ($pengajuan) {
            if ($pengajuan->isDirty('status') && $pengajuan->status === 'acc') {
                $pengajuan->catatan = null; 
            }
        });

        static::updated(function ($pengajuan) {
            // Catat Log
            $actor = Auth::id() ?? 'System';
            Log::info("PENGAJUAN DIUPDATE: ID {$pengajuan->id} diubah oleh User ID: {$actor}");

            // Fungsi Email Asli
            if ($pengajuan->wasChanged('status') && in_array($pengajuan->status, ['acc', 'tolak'])) {
                $emailMitra = $pengajuan->user->email ?? null;
                if ($emailMitra) {
                    \Illuminate\Support\Facades\Mail::to($emailMitra)->send(new \App\Mail\StatusBerkasMail($pengajuan));
                }
            }
        });

        static::created(function ($pengajuan) {
            // Catat Log
            $actor = Auth::id() ?? 'System';
            Log::info("PENGAJUAN BARU: ID {$pengajuan->id} untuk Mitra ID {$pengajuan->mitra_id} dibuat oleh User ID: {$actor}");

            // Fungsi Email Asli
            if (in_array($pengajuan->status, ['acc', 'tolak'])) {
                $emailMitra = $pengajuan->user->email ?? null;
                if ($emailMitra) {
                    \Illuminate\Support\Facades\Mail::to($emailMitra)->send(new \App\Mail\StatusBerkasMail($pengajuan));
                }
            }
        });

        static::deleted(function ($pengajuan) {
            $actor = Auth::id() ?? 'System';
            Log::warning("PENGAJUAN DIHAPUS: ID {$pengajuan->id} dihapus oleh User ID: {$actor}");
        });
    }

    
}