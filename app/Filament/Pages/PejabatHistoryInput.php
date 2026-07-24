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

class PejabatHistoryInput extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    // protected static ?string $navigationGroup = 'Manage';
    protected static ?string $title = 'Manage Pejabat';
    protected static string $view = 'filament.pages.pejabat-history-input';
    protected static bool $shouldRegisterNavigation = true;
    protected ?string $maxContentWidth = 'full';
    protected ?string $heading = 'Manage Pejabat';
    protected ?string $subheading = 'Pilih Kategori > Isi Data > Simpan Data';
    public array $histories = [
        ['user_id' => null, 'nama' => '', 'nik' => '', 'posisi' => '', 'jabatan' => '', 'awal' => '', 'akhir' => ''],
    ];
    public string $kategori = 'GM Cirebon';
    public string $filterKategori = 'all';
    protected function getCategoryOptions(): array
    {
        return [
            'GM Cirebon' => 'GM Cirebon',
            'Manager Operation' => 'Manager Operation',
            'Manager SS Cirebon' => 'Manager SS Cirebon',
            'Manager Konstruksi Cirebon' => 'Manager Konstruksi Cirebon',
            'Waspang Mitra' => 'Waspang Mitra', // Pastikan ini ada
        ];
    }

    protected function getPositionOptions(): array
    {
        return [
            'Pejabat TA' => 'Pejabat TA',
            'Manager Area' => 'Manager Area',
            'Direktur Mitra' => 'Direktur Mitra',
            'Waspang' => 'Waspang',
            'Waspang Mitra' => 'Waspang Mitra',
        ];
    }
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Jabatan')
                ->description('Pilih kategori jabatan untuk pejabat yang akan ditambahkan')
                ->icon('heroicon-o-briefcase')
                ->schema([
                    Forms\Components\Select::make('kategori')
                        ->label('Kategori')
                        ->options($this->getCategoryOptions())
                        ->required()
                        ->native(false)
                        ->placeholder('Pilih kategori jabatan'),
                ]),
            Forms\Components\Select::make('user_id')
                ->label('Instansi / Mitra Perusahaan')
                ->options(\App\Models\User::where('role', 'mitra')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->nullable()
                ->placeholder('Kosongkan jika Pejabat Internal Telkom'),
            Forms\Components\Section::make('Isi Data')
                ->description('Tambahkan informasi detail untuk setiap pejabat')
                ->icon('heroicon-o-users')
                ->schema([
                    Forms\Components\Repeater::make('histories')
                        ->label('  ')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('nama')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->placeholder('Masukkan nama lengkap'),
                                    Forms\Components\TextInput::make('nik')
                                        ->label('NIK')
                                        ->nullable()
                                        ->maxLength(16)
                                        ->numeric()
                                        ->placeholder('Masukkan NIK (16 digit)'),
                                ]),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('posisi')
                                        ->label('Posisi')
                                        ->options($this->getPositionOptions())
                                        ->required()
                                        ->native(false)
                                        ->placeholder('Pilih posisi')
                                        ->searchable(),
                                    Forms\Components\TextInput::make('jabatan')
                                        ->label('Jabatan')
                                        ->required()
                                        ->placeholder('Masukkan jabatan'),
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
                                        ->helperText('Kosongkan jika masih aktif saat ini')
                                        ->nullable()
                                        ->native(false)
                                        ->displayFormat('d/m/Y'),
                                ]),
                        ])
                        ->defaultItems(1)
                        ->minItems(1)
                        ->collapsible()
                        ->itemLabel(fn(array $state): ?string => $state['nama'] ?? 'Pejabat Baru')
                        ->addActionLabel('Tambah')
                        ->deleteAction(
                            fn(Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                        ),
                ]),
        ];
    }
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = PejabatHistory::query()->latest();
        if ($this->filterKategori !== 'all') {
            $query->where('kategori', $this->filterKategori);
        }
        return $query;
    }
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('kategori')
                ->label('Kategori')
                ->badge()
                ->color(fn(string $state): string => match (true) {
                    str_contains($state, 'GM Cirebon') => 'success',
                    str_contains($state, 'Manager Operation') => 'warning',
                    str_contains($state, 'Manager SS Cirebon') => 'info',
                    str_contains($state, 'Manager Konstruksi Cirebon') => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('user.name')
                ->label('Instansi / Mitra')
                ->placeholder('Internal Telkom Akses')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('nama')
                ->label('Nama')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('nik')
                ->label('NIK')
                ->searchable()
                ->sortable()
                ->placeholder('Tidak ada'),
            Tables\Columns\TextColumn::make('posisi')
                ->label('Posisi')
                ->searchable()
                ->sortable()
                ->badge()
                ->color(fn(string $state): string => match (true) {
                    str_contains($state, 'Pejabat TA') => 'success',
                    str_contains($state, 'Manager Area') => 'warning',
                    str_contains($state, 'Direktur Mitra') => 'info',
                    str_contains($state, 'Waspang Mitra') => 'danger',
                    str_contains($state, 'Waspang') => 'primary',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('jabatan')
                ->label('Jabatan')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('awal')
                ->label('Mulai')
                ->date('d/m/Y')
                ->sortable(),
            Tables\Columns\TextColumn::make('akhir')
                ->label('Selesai')
                ->date('d/m/Y')
                ->placeholder('Saat ini')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Ditambahkan')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('kategori')
                ->label('Filter Kategori')
                ->options($this->getCategoryOptions())
                ->placeholder('Semua Kategori')
                ->multiple(),
            Tables\Filters\SelectFilter::make('posisi')
                ->label('Filter Posisi')
                ->options($this->getPositionOptions())
                ->placeholder('Semua Posisi')
                ->multiple(),
            Tables\Filters\Filter::make('active_only')
                ->label('Hanya Yang Aktif')
                ->query(fn($query) => $query->whereNull('akhir'))
                ->toggle(),
            Tables\Filters\Filter::make('dengan_nik')
                ->label('Memiliki NIK')
                ->query(fn($query) => $query->whereNotNull('nik'))
                ->toggle(),
        ];
    }
    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->form([
                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori Jabatan')
                            ->disabled(),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->disabled(),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->disabled(),
                        Forms\Components\TextInput::make('posisi')
                            ->label('Posisi')
                            ->disabled(),
                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->disabled(),
                        Forms\Components\DatePicker::make('awal')
                            ->label('Tanggal Mulai')
                            ->disabled(),
                        Forms\Components\DatePicker::make('akhir')
                            ->label('Tanggal Selesai')
                            ->disabled(),
                    ])
                    ->modalHeading('Detail Pejabat')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('kategori')
                            ->label('Kategori Jabatan')
                            ->options($this->getCategoryOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required(),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->nullable()
                            ->maxLength(16)
                            ->numeric(),
                        Forms\Components\Select::make('posisi')
                            ->label('Posisi')
                            ->options($this->getPositionOptions())
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->required(),
                        Forms\Components\DatePicker::make('awal')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('akhir')
                            ->label('Tanggal Selesai')
                            ->helperText('Kosongkan jika masih aktif')
                            ->nullable()
                            ->native(false),
                    ])
                    ->modalHeading('Edit Data Pejabat')
                    ->mutateRecordDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->action(function (PejabatHistory $record, array $data): void {
                        $record->update($data);
                        Notification::make()
                            ->title('Data berhasil diupdate!')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Pejabat')
                    ->modalDescription('Apakah Anda yakin ingin menghapus data pejabat ini? Data yang telah dihapus tidak dapat dikembalikan.')
                    ->action(function (PejabatHistory $record): void {
                        $record->delete();
                        Notification::make()
                            ->title('Data berhasil dihapus!')
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->size('sm')
                ->color('gray')
                ->button(),
        ];
    }
    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('update_posisi')
                ->label('Update Posisi')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('posisi_baru')
                        ->label('Posisi Baru')
                        ->options($this->getPositionOptions())
                        ->required()
                        ->native(false)
                        ->placeholder('Pilih posisi baru')
                        ->searchable()
                        ->helperText('Posisi ini akan diterapkan ke semua data yang dipilih'),
                ])
                ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                    $count = $records->count();
                    $records->each(function ($record) use ($data) {
                        $record->update(['posisi' => $data['posisi_baru']]);
                    });
                    Notification::make()
                        ->title("Posisi berhasil diupdate untuk $count data!")
                        ->success()
                        ->send();
                }),
            Tables\Actions\BulkAction::make('update_jabatan')
                ->label('Update Jabatan')
                ->icon('heroicon-o-briefcase')
                ->color('warning')
                ->form([
                    Forms\Components\TextInput::make('jabatan_baru')
                        ->label('Jabatan Baru')
                        ->required()
                        ->placeholder('Masukkan jabatan baru')
                        ->helperText('Jabatan ini akan diterapkan ke semua data yang dipilih'),
                ])
                ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                    $count = $records->count();
                    $records->each(function ($record) use ($data) {
                        $record->update(['jabatan' => $data['jabatan_baru']]);
                    });
                    Notification::make()
                        ->title("Jabatan berhasil diupdate untuk $count data!")
                        ->success()
                        ->send();
                }),
            Tables\Actions\BulkAction::make('update_kategori')
                ->label('Update Kategori')
                ->icon('heroicon-o-tag')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('kategori_baru')
                        ->label('Kategori Baru')
                        ->options($this->getCategoryOptions())
                        ->required()
                        ->native(false)
                        ->placeholder('Pilih kategori baru')
                        ->helperText('Kategori ini akan diterapkan ke semua data yang dipilih'),
                ])
                ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                    $count = $records->count();
                    $records->each(function ($record) use ($data) {
                        $record->update(['kategori' => $data['kategori_baru']]);
                    });
                    Notification::make()
                        ->title("Kategori berhasil diupdate untuk $count data!")
                        ->success()
                        ->send();
                }),
            Tables\Actions\BulkAction::make('delete')
                ->label('Hapus yang dipilih')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Data Terpilih')
                ->modalDescription('Apakah Anda yakin ingin menghapus semua data yang dipilih? Data yang telah dihapus tidak dapat dikembalikan.')
                ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                    $count = $records->count();
                    $records->each->delete();
                    Notification::make()
                        ->title("$count data berhasil dihapus!")
                        ->success()
                        ->send();
                }),
            Tables\Actions\BulkAction::make('export')
                ->label('Export ke CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                    Notification::make()
                        ->title('Export akan segera dimulai')
                        ->info()
                        ->send();
                }),
        ];
    }
    public function submit()
    {
        $this->validate();
        foreach ($this->histories as $data) {
            PejabatHistory::create([
                'user_id' => $data['user_id'] ?? null,
                'kategori' => $this->kategori,
                'nama' => $data['nama'],
                'nik' => $data['nik'] ?? null,
                'posisi' => $data['posisi'],
                'jabatan' => $data['jabatan'],
                'awal' => $data['awal'],
                'akhir' => $data['akhir'] ?: null,
            ]);
        }
        $this->reset('histories');
        $this->histories = [
            ['nama' => '', 'nik' => '', 'posisi' => '', 'jabatan' => '', 'awal' => '', 'akhir' => ''],
        ];
        Notification::make()
            ->title('Data berhasil disimpan!')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        // Izinkan admin dan superadmin
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'superadmin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'superadmin']);
    }
}
