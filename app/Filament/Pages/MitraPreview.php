<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\MitraPendaftaran;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;


class MitraPreview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.mitra-preview';
    protected static ?string $title = 'Mitra Preview';

    public static function getNavigationLabel(): string
    {
        // Cukup cek admin
        if (Auth::user()?->role === 'admin') {
            return 'Mitra Preview';
        }
        return '3. Mitra Preview';
    }

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'user']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'user']);
    }

    public static function getNavigationGroup(): ?string
    {
        if (Auth::user()?->role === 'admin') {
            return 'Cek Data Mitra'; 
        }
        return null;
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public ?MitraPendaftaran $data = null;
    public Collection $allData;
    public array $boqData = [];

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    public function mount(): void
    {
        $user = Auth::user();
    
        if ($user->role === 'admin') {
            $this->allData = MitraPendaftaran::all();
        } else {
            $this->allData = MitraPendaftaran::where('user_id', $user->id)->get();
        }
    
        $record = request()->route('record');
    
        if ($record) {
    
            $query = MitraPendaftaran::where('id', $record);
    
            if ($user->role !== 'admin') {
                $query->where('user_id', $user->id);
            }
    
            $this->data = $query->first();
        }
    
        if (!$this->data && $this->allData->isNotEmpty()) {
            $this->data = $this->allData->first();
        }
    
        if ($this->data) {
            $this->loadBoqData();
        }
    }

    private function loadBoqData(): void
    {
        try {
            $this->data->load('boqLines');
            $this->boqData = $this->data->boqLines ? $this->data->boqLines->toArray() : [];
        } catch (\Exception $e) {
            $this->boqData = [];
        }
    }

    private function getLastSelectedMitra(): ?MitraPendaftaran
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        $query = MitraPendaftaran::orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($user->id !== 1) {
            $query->where('user_id', $user->id);
        }
        
        return $query->first();
    }

    protected function getViewData(): array
    {
        return [
            'data' => $this->data,
            'allData' => $this->allData,
            'mitras' => $this->allData,
            'hasData' => !is_null($this->data),
            'boqData' => collect($this->boqData),
            'hasBoqData' => !empty($this->boqData),
            'currentUserId' => Auth::id(),
        ];
    }

    public function hasData(): bool
    {
        return !is_null($this->data);
    }

    public function getTotalMitra(): int
    {
        return $this->allData->count();
    }

    public function selectMitra($mitraId): void
    {
        try {
            $user = Auth::user();
            $mitra = \App\Models\MitraPendaftaran::find($mitraId);
            
            if (!$mitra) {
                session()->flash('error', 'Data mitra tidak ditemukan.');
                return;
            }
            
            // Cukup cek admin
            $isAuthorized = ($user->role === 'admin') || ($mitra->user_id === $user->id);
            
            if (!$isAuthorized) {
                session()->flash('error', 'Anda tidak memiliki izin untuk melihat data mitra ini.');
                return;
            }
            
            $this->data = $mitra;
            $this->loadBoqData();
            $this->redirectRoute('filament.admin.pages.mitra-preview', ['id' => $mitraId]);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memuat data mitra yang dipilih.');
        }
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->role === 'admin') {
            return $query;
        }

        return $query->where('user_id', Auth::id());
    }

    public function getMitraOptions(): array
    {
        return $this->allData->mapWithKeys(function ($mitra) {
            return [$mitra->id => $mitra->nama_mitra . ' - ' . $mitra->nama_pekerjaan];
        })->toArray();
    }
}