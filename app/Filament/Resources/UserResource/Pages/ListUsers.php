<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    // Pastikan hanya admin yang bisa akses
    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->is_superadmin, 403);
        
        parent::mount();
    }


protected function getDefaultTableRecordsPerPageSelectOptions(): array
{
    return [10, 25, 50, 100];
}


protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
{
    // TODO: Tambahkan eager loading relasi yang dipakai di tabel, contoh:
    // return parent::getTableQuery()->with(['relasiNama1','relasiNama2']);
    return parent::getTableQuery();
}
}