<?php

namespace App\Filament\Resources\StatusPengajuanBerkasResource\Pages;

use App\Filament\Resources\StatusPengajuanBerkasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusPengajuanBerkas extends EditRecord
{
    protected static string $resource = StatusPengajuanBerkasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
