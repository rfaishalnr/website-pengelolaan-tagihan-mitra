<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class MitraPendaftaran extends Model
{
    protected $fillable = [
        'mitra_id',
        // Step 1
        'nama_mitra',
        'no_khs_mitra',
        'amd_khs_mitra_1',
        'amd_khs_mitra_2',
        'amd_khs_mitra_3',
        'amd_khs_mitra_4',
        'amd_khs_mitra_5',
        'nomer_sp_mitra',
        'amd_sp',
        'nama_pekerjaan',
        'regional',
        'area',
        'idp',
        'toc',
        'alamat_kantor',

        // Step 2
        'nama_pejabat_ta',
        'posisi_pejabat_ta',
        'nik_pejabat',
        'nama_mgr_area',
        'jabatan_mgr_area',
        'direktur_mitra',
        'jabatan_mitra',
        'waspang',
        'jabatan_waspang',
        'nik_waspang',

        'waspang_mitra',
        'jabatan_waspang_mitra',
        'nik_waspang_mitra',
        'periode_waspang_mitra',

        // Step 3–6
        'no_baut',
        'tanggal_baut',
        'no_ba_rekon',
        'tanggal_ba_rekon',
        'no_ba_abd',
        'tanggal_ba_abd',
        'no_ba_legal',
        'tanggal_ba_legal',

        'ppn_percent',

        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('user_scope', function ($query) {
            if (App::runningInConsole()) return;
    
            if (!Auth::check()) return;
    
            $user = Auth::user();
    
            if (!in_array($user->role, ['admin', 'superadmin'])) {
                $query->where('user_id', $user->id);
            }
        });

        static::created(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("TAGIHAN BARU: ID {$record->id} (Pekerjaan: {$record->nama_pekerjaan}) dibuat oleh User ID: {$actor}");
        });

        static::updated(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::info("TAGIHAN DIUPDATE: ID {$record->id} diubah oleh User ID: {$actor}");
        });

        static::deleted(function ($record) {
            $actor = Auth::id() ?? 'System';
            Log::warning("TAGIHAN DIHAPUS: ID {$record->id} dihapus oleh User ID: {$actor}");
        });
    }

    public function boqLines(): HasMany
    {
        return $this->hasMany(BoqLine::class, 'mitra_pendaftaran_id');
    }

    public function boqLinesOrdered(): HasMany
    {
        return $this->hasMany(BoqLine::class, 'mitra_pendaftaran_id')
                    ->orderBy('no')
                    ->orderBy('id');
    }

    public function getBoqTotalsAttribute()
    {
        $boqLines = $this->boqLines;
        
        return [
            'sp_material' => $boqLines->sum('sp_material'),
            'sp_jasa' => $boqLines->sum('sp_jasa'),
            'sp_total' => $boqLines->sum('sp_total'),
            'rekon_material' => $boqLines->sum('rekon_material'),
            'rekon_jasa' => $boqLines->sum('rekon_jasa'),
            'rekon_total' => $boqLines->sum('rekon_total'),
            'tambah_material' => $boqLines->sum('tambah_material'),
            'tambah_jasa' => $boqLines->sum('tambah_jasa'),
            'tambah_total' => $boqLines->sum('tambah_total'),
            'kurang_material' => $boqLines->sum('kurang_material'),
            'kurang_jasa' => $boqLines->sum('kurang_jasa'),
            'kurang_total' => $boqLines->sum('kurang_total'),
        ];
    }

    public function getBoqGrandTotalAttribute()
    {
        $totals = $this->boq_totals;
        
        return [
            'material' => ($totals['sp_material'] + $totals['tambah_material']) - $totals['kurang_material'],
            'jasa' => ($totals['sp_jasa'] + $totals['tambah_jasa']) - $totals['kurang_jasa'],
            'total' => (($totals['sp_material'] + $totals['tambah_material']) - $totals['kurang_material']) + 
                      (($totals['sp_jasa'] + $totals['tambah_jasa']) - $totals['kurang_jasa'])
        ];
    }

    public function getBoqPpnAttribute()
    {
        $grandTotal = $this->boq_grand_total;
        $ppnRate = $this->ppn_percent / 100;
        
        return [
            'rate' => $this->ppn_percent,
            'amount' => $grandTotal['total'] * $ppnRate,
            'total_with_ppn' => $grandTotal['total'] * (1 + $ppnRate)
        ];
    }
}