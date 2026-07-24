<?php

namespace App\Filament\Resources\MitraResource\Pages;

use App\Filament\Resources\MitraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMitras extends ListRecords
{
    protected static string $resource = MitraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
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
