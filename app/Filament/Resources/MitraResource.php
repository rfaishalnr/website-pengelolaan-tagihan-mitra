<?php
namespace App\Filament\Resources;
use App\Models\Mitra;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MitraResource\Pages;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
class MitraResource extends Resource
{
    protected static ?string $model = Mitra::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Data Mitra';
    // protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 1;
    // protected function getMaxContentWidth(): ?string
    // {
    //     return 'full'; // Atau 'screen' jika ingin 100% penuh
    // }
    
    // Hanya admin yang bisa akses resource ini
    public static function canViewAny(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'superadmin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'superadmin']);
    }
    public static function getLabel(): string
    {
        return 'Data Mitra';
    }
    public static function getPluralLabel(): string
    {
        return 'Data Mitra';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_mitra')
                    ->label('Nama Mitra')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Masukkan nama mitra'),
                TextInput::make('no_khs_mitra')
                    ->label('No. KHS Mitra')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Masukkan nomor KHS mitra'),
                TextInput::make('amd_khs_mitra_1')
                    ->label('AMD KHS Mitra 1')
                    ->maxLength(255)
                    ->placeholder('Masukkan AMD KHS Mitra 1')
                    ->helperText('Amendment KHS Mitra 1'),
                TextInput::make('amd_khs_mitra_2')
                    ->label('AMD KHS Mitra 2')
                    ->maxLength(255)
                    ->placeholder('Masukkan AMD KHS Mitra 2')
                    ->helperText('Amendment KHS Mitra 2'),
                TextInput::make('amd_khs_mitra_3')
                    ->label('AMD KHS Mitra 3')
                    ->maxLength(255)
                    ->placeholder('Masukkan AMD KHS Mitra 3')
                    ->helperText('Amendment KHS Mitra 3'),
                TextInput::make('amd_khs_mitra_4')
                    ->label('AMD KHS Mitra 4')
                    ->maxLength(255)
                    ->placeholder('Masukkan AMD KHS Mitra 4')
                    ->helperText('Amendment KHS Mitra 4'),
                TextInput::make('amd_khs_mitra_5')
                    ->label('AMD KHS Mitra 5')
                    ->maxLength(255)
                    ->placeholder('Masukkan AMD KHS Mitra 5')
                    ->helperText('Amendment KHS Mitra 5'),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('nama_mitra')
                ->label('Nama Mitra')
                ->searchable()
                ->sortable()
                ->wrap(),
            TextColumn::make('no_khs_mitra')
                ->label('No. KHS Mitra')
                ->searchable()
                ->sortable()
                ->wrap(),
            TextColumn::make('amd_khs_mitra_1')
                ->label('AMD KHS Mitra 1')
                ->searchable()
                ->sortable()
                ->wrap()
                ->placeholder('Tidak ada'),
            TextColumn::make('amd_khs_mitra_2')
                ->label('AMD KHS Mitra 2')
                ->searchable()
                ->sortable()
                ->wrap()
                ->placeholder('Tidak ada'),
            TextColumn::make('amd_khs_mitra_3')
                ->label('AMD KHS Mitra 3')
                ->searchable()
                ->sortable()
                ->wrap()
                ->placeholder('Tidak ada'),
            TextColumn::make('amd_khs_mitra_4')
                ->label('AMD KHS Mitra 4')
                ->searchable()
                ->sortable()
                ->wrap()
                ->placeholder('Tidak ada'),
            TextColumn::make('amd_khs_mitra_5')
                ->label('AMD KHS Mitra 5')
                ->searchable()
                ->sortable()
                ->wrap()
                ->placeholder('Tidak ada'),
        ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat dari'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListMitras::route('/'),
            'create' => Pages\CreateMitra::route('/create'),
            // 'view' => Pages\ViewMitra::route('/{record}'),
            'edit' => Pages\EditMitra::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    private static function getMitraNames(): array
    {
        return Mitra::orderBy('nama_mitra')
            ->pluck('nama_mitra')
            ->toArray();
    }


// Hint performa:
// public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
// {
//     return parent::getEloquentQuery()->with(['relasiNama1','relasiNama2']);
// }
}