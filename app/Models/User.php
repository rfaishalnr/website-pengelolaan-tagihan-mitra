<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nik',
        'npwp',
        'mitra_id',
        'is_superadmin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("USER BARU: Email {$record->email} (ID: {$record->id}) dibuat oleh User ID: {$actor}");
        });

        static::updated(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("USER DIUPDATE: Email {$record->email} (ID: {$record->id}) diubah oleh User ID: {$actor}");
        });

        static::deleted(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::warning("USER DIHAPUS: Email {$record->email} (ID: {$record->id}) dihapus oleh User ID: {$actor}");
        });
    }

    /**
     * Relationship with MitraPendaftaran
     */
    public function mitraPendaftarans()
    {
        return $this->hasMany(MitraPendaftaran::class);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }
}