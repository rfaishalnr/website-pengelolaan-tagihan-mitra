<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\AkunUpdateMail;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Aksi di header, seperti tombol View dan Delete
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Validasi akses: hanya user ID 1 (admin) yang bisa edit user lain
     */
    public function mount(int|string $record): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->is_superadmin, 403);
        
        parent::mount($record);
    }

    protected function afterSave(): void
    {
        $user = $this->record; 
        
        // Cek apakah admin mengetik password. Jika tidak, variabel ini akan bernilai null
        $passwordBaru = !empty($this->data['password']) ? $this->data['password'] : null;

        // Tembak email gabungan (berisi info role & password) ke user
        Mail::to($user->email)->send(new AkunUpdateMail($user, $passwordBaru));
    }
}
