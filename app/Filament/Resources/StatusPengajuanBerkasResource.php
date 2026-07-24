<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusPengajuanBerkasResource\Pages;
use App\Models\Pengajuan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StatusPengajuanBerkasResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $modelLabel = 'Status Pengajuan';
    protected static ?string $pluralModelLabel = 'Status Pengajuan';
    protected static ?string $navigationLabel = 'Status Pengajuan Dokumen Tagihan';
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Menu Status';


    public static function getNavigationBadge(): ?string
    {
        $jumlahDitolak = static::getModel()::where('user_id', Auth::id())
            ->where('status', 'tolak')
            ->count();

        return $jumlahDitolak > 0 ? (string) $jumlahDitolak : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger'; 
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role !== 'admin';
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->role !== 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    // --- BAGIAN INI UNTUK MENGATUR TAMPILAN DETAIL (POPUP VIEW) ---
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Section::make('Detail Berkas')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Grid::make(3) 
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Tanggal Masuk')
                                    ->date('d M Y'),

                                TextEntry::make('nomor_sp')
                                    ->label('Nomor SP')
                                    ->icon('heroicon-m-hashtag')
                                    ->copyable(), 

                                TextEntry::make('status')
                                    ->label('Status Terkini')
                                    ->badge()
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
                                        'pending' => 'Menunggu',
                                        'acc'     => 'Diterima',
                                        'tolak'   => 'Ditolak',
                                        default   => $state,
                                    }),
                            ]),


                        TextEntry::make('nama_pekerjaan')
                            ->label('Nama Pekerjaan')
                            ->weight('bold')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->columnSpanFull(),
                    ]),


                Section::make('Catatan & Revisi')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->description('Pesan dari Admin Telkom terkait berkas ini.')
                    ->schema([
                        TextEntry::make('catatan')
                            ->hiddenLabel() 
                            ->default('Tidak ada catatan.')
                            ->markdown() 
                            ->prose() 
                            ->color(fn ($record) => $record->status === 'tolak' ? 'danger' : 'gray'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_sp')
                    ->label('Nomor SP')
                    ->searchable(),
                    // ->icon('heroicon-m-hashtag'),

                Tables\Columns\TextColumn::make('nama_pekerjaan')
                    ->label('Pekerjaan')
                    ->wrap()
                    ->limit(40),

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
                        'pending' => 'Menunggu',
                        'acc'     => 'Diterima',
                        'tolak'   => 'Ditolak',
                        default   => $state,
                    }),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->default('-')
                    ->icon(fn ($state) => $state ? 'heroicon-m-chat-bubble-left-right' : null)
                    ->iconColor('danger')
                    ->tooltip(fn ($record) => $record->catatan)
                    ->limit(20),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('user_id', Auth::id());
            })
            ->actions([

                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalWidth('lg'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatusPengajuanBerkas::route('/'),
        ];
    }
}