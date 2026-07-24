<?php

namespace App\Filament\Resources\MitraPendaftaranResource\Pages;

use App\Filament\Resources\MitraPendaftaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMitraPendaftaran extends EditRecord
{
    protected static string $resource = MitraPendaftaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
