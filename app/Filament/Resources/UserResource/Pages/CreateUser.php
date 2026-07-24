<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\AkunBaruMail; 

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    // Pastikan hanya admin yang bisa akses
    public function mount(): void
    {
        $user = Auth::user();
        // Hanya yang punya is_superadmin yang bisa akses
        abort_unless($user instanceof User && $user->is_superadmin, 403);
        
        parent::mount();
    }

    protected function afterCreate(): void
    {
        $user = $this->record; 

        $rawPassword = $this->data['password'] ?? '';

        Mail::to($user->email)->send(new AkunBaruMail($user, $rawPassword));
    }
}