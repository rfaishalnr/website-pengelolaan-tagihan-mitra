<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    
    // Pastikan hanya admin yang bisa akses
    public function mount(int|string $record): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->is_superadmin, 403);
        
        parent::mount($record);
    }
}