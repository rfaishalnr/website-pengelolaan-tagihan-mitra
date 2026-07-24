<?php

namespace App\Filament\Resources\MitraPendaftaranResource\Pages;

use App\Filament\Resources\MitraPendaftaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMitraPendaftaran extends CreateRecord
{
    protected static string $resource = MitraPendaftaranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
}
