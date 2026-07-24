<?php

namespace App\Filament\Resources\PengajuanBerkasResource\Pages;

use App\Filament\Resources\PengajuanBerkasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanBerkas extends EditRecord
{
    protected static string $resource = PengajuanBerkasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
