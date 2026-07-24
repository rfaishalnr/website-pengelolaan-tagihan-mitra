<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Manage Users';

    // protected static ?string $navigationGroup = 'Manage';

    public static function canViewAny(): bool
    {
        if (app()->runningInConsole()) return true;
        // Hanya yang punya is_superadmin yang bisa lihat menu ini
        return Auth::check() && Auth::user()->is_superadmin;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->is_superadmin;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('nik')
                    ->label('NIK (Nomor Induk Kependudukan)')
                    ->helperText('Masukan NIK (16 digit)')
                    ->required()
                    ->numeric()
                    ->rule('digits:16')
                    ->unique(ignoreRecord: true)
                    ->required(fn(Get $get) => $get('role') === 'user')
                    // HANYA muncul di layar jika role-nya adalah 'user'
                    ->visible(fn(Get $get) => $get('role') === 'user')
                    ->validationMessages([
                        'digits' => 'NIK harus terdiri dari tepat 16 angka.',
                        'unique' => 'NIK ini sudah terdaftar di sistem.',
                    ]),

                TextInput::make('npwp')
                    ->label('NPWP Perusahaan (Nomor Pokok Wajib Pajak)')
                    ->helperText('Masukan NPWP Perusahaan (16 digit)')
                    ->required()
                    ->numeric()
                    ->rule('digits:16')
                    ->unique(ignoreRecord: true)
                    // HANYA wajib diisi jika role-nya adalah 'user'
                    ->required(fn(Get $get) => $get('role') === 'user')
                    // HANYA muncul di layar jika role-nya adalah 'user'
                    ->visible(fn(Get $get) => $get('role') === 'user')
                    ->validationMessages([
                        'digits' => 'NPWP harus terdiri dari tepat 16 angka.',
                        'unique' => 'NPWP ini sudah terdaftar di sistem.',
                    ]),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn(string $context): bool => $context === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->helperText('Kosongkan jika tidak ingin mengubah password'),

                    Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ])
                    ->default('user')
                    ->required()
                    ->live()
                    ->disabled(function ($record) {
                        // Jika user yang diedit adalah dirinya sendiri, KUNCI!
                        if ($record && $record->id === Auth::id()) {
                            return true;
                        }

                        // if (!$currentUser->is_superadmin) {
                        //     return true;
                        // }
                        // Jika tidak, biarkan terbuka
                        return false;
                    })
                    ->dehydrated(true),

                // DateTimePicker::make('email_verified_at')
                //     ->label('Email Verified At')
                //     ->nullable(),

                Select::make('mitra_id')
                    ->label('Asal Perusahaan / Mitra')
                    ->relationship('mitra', 'nama_mitra') // Menarik data dari tabel mitras
                    ->searchable()
                    ->preload()
                    // Form ini HANYA wajib diisi dan muncul kalau rolenya adalah 'user' (Mitra)
                    ->required(fn(Get $get) => $get('role') === 'user')
                    ->visible(fn(Get $get) => $get('role') === 'user'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->label('ID')
                //     ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                // ->sortable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'admin' => 'success',
                        'user' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('nik')
                    ->label('NIK')
                    ->placeholder('Belum ada NIK')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('NIK berhasil disalin!'),
                // ->toggleable(isToggledHiddenByDefault: true),

                // Menampilkan NPWP di Tabel
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->placeholder('Belum ada NPWP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('NPWP berhasil disalin!'),
                // ->toggleable(isToggledHiddenByDefault: true),

                // TextColumn::make('email_verified_at')
                //     ->label('Email Verified')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Role')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    public static function canEdit($record): bool
    {
        return Auth::check() && Auth::user()->is_superadmin;
    }

    public static function canDelete($record): bool
    {
        // Cegah superadmin menghapus dirinya sendiri
        if ($record->is_superadmin) {
            return false;
        }
        return Auth::check() && Auth::user()->is_superadmin;
    }
}


// Hint performa:
// public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
// {
//     return parent::getEloquentQuery()->with(['relasiNama1','relasiNama2']);
// }

// public static function canCreate(): bool
// {
//     return Auth::user()?->hasRole('admin') ?? false;
// }

