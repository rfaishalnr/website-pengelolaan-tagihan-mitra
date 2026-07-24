<?php

namespace App\Filament\Resources\StatusPengajuanBerkasResource\Pages;

use App\Filament\Resources\StatusPengajuanBerkasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatusPengajuanBerkas extends ListRecords
{
    protected static string $resource = StatusPengajuanBerkasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
