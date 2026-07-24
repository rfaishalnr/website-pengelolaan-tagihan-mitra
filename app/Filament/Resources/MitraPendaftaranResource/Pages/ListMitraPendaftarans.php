<?php

namespace App\Filament\Resources\MitraPendaftaranResource\Pages;

use App\Filament\Resources\MitraPendaftaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMitraPendaftarans extends ListRecords
{
    protected static string $resource = MitraPendaftaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
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
