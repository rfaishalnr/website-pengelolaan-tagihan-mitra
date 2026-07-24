<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanBerkasResource\Pages;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class PengajuanBerkasResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $modelLabel = 'Pengajuan Dokumen';
    protected static ?string $pluralModelLabel = 'Pengajuan Dokumen';
    protected static ?string $navigationLabel = 'Pengajuan Dokumen Tagihan';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Menu Pengajuan';
    

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Formulir Pengajuan & Validasi')
                    ->description('Input data pekerjaan dan tentukan status validasi.')
                    ->schema([
                        // --- BARIS 1: Mitra & No SP ---
                        Forms\Components\Select::make('user_id')
                            ->label('Nama Mitra')
                            ->relationship('user', 'name', fn (Builder $query) => $query->where('role', '!=', 'admin'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nomor_sp')
                            ->label('Nomor SP')
                            ->placeholder('Contoh: SP-2024/001')
                            ->required()
                            ->columnSpan(1),

                        // --- BARIS 2: Nama Pekerjaan & Status (Digabung) ---
                        Forms\Components\TextInput::make('nama_pekerjaan')
                            ->label('Nama Pekerjaan')
                            ->placeholder('Masukan nama pekerjaan lengkap')
                            ->required()
                            ->columnSpan(2), // Mengambil 2/3 layar

                            Forms\Components\Select::make('status')
                            ->label('Status Validasi')
                            ->options([
                                'pending' => 'Menunggu (Pending)',
                                'acc'     => 'Diterima (ACC)',
                                'tolak'   => 'Ditolak (Revisi)',
                            ])
                            ->default('pending')
                            
                            ->native(false) 
                            ->selectablePlaceholder(false) 

                            ->extraInputAttributes(['class' => 'text-gray-900 dark:text-white']) 
                            
                            ->live()
                            ->required()
                            ->columnSpan(1),     

                            Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Penolakan')
                            ->placeholder('Jelaskan bagian mana yang perlu diperbaiki oleh mitra...')
                            ->rows(3)
                            ->visible(fn (Get $get) => $get('status') === 'tolak')
                            ->required(fn (Get $get) => $get('status') === 'tolak')
                            ->dehydrated(true) 
                            ->mutateDehydratedStateUsing(fn (?string $state, Get $get) => $get('status') === 'tolak' ? $state : null)
                            ->columnSpanFull(),
                    ])
                    ->columns(3), // Menggunakan Grid 3 Kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->label('Tanggal Input')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Mitra')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nomor_sp')
                    ->label('Nomor SP')
                    ->searchable(),
                    // ->icon('heroicon-m-document-text')
                    // ->color('gray'),

                Tables\Columns\TextColumn::make('nama_pekerjaan')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->nama_pekerjaan),

                // Badge Status dengan Icon Bagus
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'acc'     => 'success',
                        'tolak'   => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-m-clock',
                        'acc'     => 'heroicon-m-check-badge',
                        'tolak'   => 'heroicon-m-x-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'acc'     => 'Diterima',
                        'tolak'   => 'Ditolak',
                        default   => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanBerkas::route('/'),
            'create' => Pages\CreatePengajuanBerkas::route('/create'),
            'edit' => Pages\EditPengajuanBerkas::route('/{record}/edit'),
        ];
    }
    
}