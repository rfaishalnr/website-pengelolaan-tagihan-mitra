<?php

namespace App\Filament\Resources\PengajuanBerkasResource\Pages;

use App\Filament\Resources\PengajuanBerkasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanBerkas extends ListRecords
{
    protected static string $resource = PengajuanBerkasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
