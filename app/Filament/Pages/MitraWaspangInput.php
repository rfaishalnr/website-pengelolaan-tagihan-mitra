<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PejabatHistory;
use Filament\Forms;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class MitraWaspangInput extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $title = 'Waspang Perusahaan';
    protected static ?string $navigationGroup = 'Menu Waspang';
    
    protected static string $view = 'filament.pages.mitra-waspang-input'; 
    
    protected static bool $shouldRegisterNavigation = true;
    protected ?string $maxContentWidth = 'full';
    protected ?string $heading = 'Kelola Waspang Mitra';
    protected ?string $subheading = 'Kelola data Pengawas Lapangan (Waspang) khusus untuk perusahaan Anda.';

    public array $histories = [
        ['nama' => '', 'nik' => '', 'jabatan' => '', 'awal' => '', 'akhir' => ''],
    ];

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Tambah Data Waspang Baru')
                ->icon('heroicon-o-user-plus')
                ->schema([
                    Forms\Components\Repeater::make('histories')
                        ->label(' ')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('nama')
                                        ->label('Nama Lengkap')
                                        ->required(),
                                    Forms\Components\TextInput::make('nik')
                                        ->label('NIK')
                                        ->nullable()
                                        ->maxLength(16)
                                        ->numeric(),
                                ]),
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\TextInput::make('jabatan')
                                        ->label('Jabatan')
                                        // ->placeholder('Contoh: Staff Lapangan FO')
                                        ->required(),
                                ]),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('awal')
                                        ->label('Tanggal Mulai')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d/m/Y'),
                                    Forms\Components\DatePicker::make('akhir')
                                        ->label('Tanggal Selesai')
                                        ->nullable()
                                        ->native(false)
                                        ->displayFormat('d/m/Y'),
                                ]),
                        ])
                        ->defaultItems(1)
                        ->minItems(1)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['nama'] ?? 'Data Waspang Baru')
                        ->addActionLabel('Tambah Baris'),
                ]),
        ];
    }

    // QUERY INI YANG PALING PENTING: Mengunci agar Mitra HANYA melihat data miliknya sendiri
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return PejabatHistory::query()
            ->where('user_id', Auth::id())
            ->where('kategori', 'Waspang Mitra')
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('nik')->label('NIK')->placeholder('Tidak ada'),
            Tables\Columns\TextColumn::make('jabatan')->searchable(),
            Tables\Columns\TextColumn::make('awal')->date('d/m/Y')->label('Mulai'),
            Tables\Columns\TextColumn::make('akhir')->date('d/m/Y')->placeholder('Saat ini')->label('Selesai'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            // TOMBOL EDIT DENGAN MODAL
            Tables\Actions\EditAction::make()
                ->form([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Lengkap')
                        ->required(),
                    Forms\Components\TextInput::make('nik')
                        ->label('NIK')
                        ->nullable()
                        ->maxLength(16)
                        ->numeric(),
                    Forms\Components\TextInput::make('jabatan')
                        ->label('Jabatan')
                        ->required(),
                    Forms\Components\DatePicker::make('awal')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    Forms\Components\DatePicker::make('akhir')
                        ->label('Tanggal Selesai')
                        ->nullable()
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                ])
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Data Waspang berhasil diperbarui!')
                ),
            
            Tables\Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->action(function (PejabatHistory $record): void {
                    $record->delete();
                    Notification::make()->title('Data Waspang berhasil dihapus!')->success()->send();
                }),
        ];
    }

    public function submit()
    {
        $this->validate();

        foreach ($this->histories as $data) {
            PejabatHistory::create([
                'user_id' => Auth::id(), 
                'kategori' => 'Waspang Mitra',
                'posisi' => 'Waspang Mitra', 
                'nama' => $data['nama'],
                'nik' => $data['nik'] ?? null,
                'jabatan' => $data['jabatan'],
                'awal' => $data['awal'],
                'akhir' => $data['akhir'] ?: null,
            ]);
        }

        $this->reset('histories');
        $this->histories = [['nama' => '', 'nik' => '', 'jabatan' => '', 'awal' => '', 'akhir' => '']];
        
        Notification::make()->title('Data Waspang berhasil ditambahkan!')->success()->send();
    }

    // Mengunci agar halaman ini HANYA BISA DIAKSES OLEH MITRA
    public static function canAccess(): bool
    {
        // Sesuaikan 'mitra' dengan nama role di database kamu (bisa 'user', 'mitra', dll)
        return Auth::check() && Auth::user()->role === 'user'; 
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->role === 'user';
    }
}