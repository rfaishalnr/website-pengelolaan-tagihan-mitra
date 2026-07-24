<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\PejabatHistory;
use App\Models\Mitra;
use App\Filament\Resources\MitraPendaftaranResource\Pages;
use App\Models\MitraPendaftaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
// use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\Carbon;

class MitraPendaftaranResource extends Resource
{
    protected static ?string $model = MitraPendaftaran::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getLabel(): string
    {
        return 'Tagihan';
    }
    protected static ?string $navigationLabel = '1. Buat Tagihan';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        // Cukup cek admin dan user, superadmin otomatis masuk karena rolenya admin
        return Auth::check() && in_array(Auth::user()->role, ['user']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['user']);
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->role === 'user';
    }

    private static function getMitraOptionsWithData(): array
    {
        return Mitra::orderBy('nama_mitra')
            ->get()
            ->pluck('nama_mitra', 'id')
            ->toArray();
    }
    private static function getMitraData(int $id): ?array
    {
        $mitra = Mitra::find($id);
        if (!$mitra) {
            return null;
        }
        return [
            'nama_mitra' => $mitra->nama_mitra,
            'no_khs_mitra' => $mitra->no_khs_mitra,
            'amd_khs_mitra_1' => $mitra->amd_khs_mitra_1,
            'amd_khs_mitra_2' => $mitra->amd_khs_mitra_2,
        ];
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = Auth::user();
        $query = parent::getEloquentQuery();

        // Jika dia admin (termasuk superadmin), lihat semua data
        if ($user && $user->role === 'admin') {
            return $query;
        }

        // Jika mitra, hanya lihat miliknya
        return $query->where('user_id', $user?->id ?? 0);
    }

    protected static function getMitraDataByName(string $nama): ?array
    {
        $mitra = \App\Models\Mitra::where('nama_mitra', $nama)->first();

        if (!$mitra) {
            return null;
        }

        return [
            'no_khs_mitra'    => $mitra->no_khs_mitra,
            'amd_khs_mitra_1' => $mitra->amd_khs_mitra_1,
            'amd_khs_mitra_2' => $mitra->amd_khs_mitra_2,
            'amd_khs_mitra_3' => $mitra->amd_khs_mitra_3,
            'amd_khs_mitra_4' => $mitra->amd_khs_mitra_4,
            'amd_khs_mitra_5' => $mitra->amd_khs_mitra_5,
            'direktur_mitra'  => $mitra->direktur_mitra,
            'jabatan_mitra'   => $mitra->jabatan_mitra,
            'alamat_kantor'   => $mitra->alamat_kantor,
        ];
    }

    private static function getLastUserInput(): ?array
    {
        $userId = Auth::id();
        if (!$userId) return null;
        $lastRecord = MitraPendaftaran::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$lastRecord) return null;
        $mitra = Mitra::where('nama_mitra', $lastRecord->nama_mitra)->first();
        $mitraId = $mitra ? $mitra->id : null;
        return [
            'mitra_id' => $mitraId,
            'nama_mitra' => $lastRecord->nama_mitra,
            'no_khs_mitra' => $lastRecord->no_khs_mitra,
            'amd_khs_mitra_1' => $lastRecord->amd_khs_mitra_1,
            'amd_khs_mitra_2' => $lastRecord->amd_khs_mitra_2,
            'nomer_sp_mitra' => $lastRecord->nomer_sp_mitra,
            'amd_sp' => $lastRecord->amd_sp,
            'nama_pekerjaan' => $lastRecord->nama_pekerjaan,
            'regional' => $lastRecord->regional,
            'area' => $lastRecord->area,
            'idp' => $lastRecord->idp,
            'toc' => $lastRecord->toc,
            'alamat_kantor' => $lastRecord->alamat_kantor,
            'pejabat_ta_id' => $lastRecord->pejabat_ta_id,
            'nama_pejabat_ta' => $lastRecord->nama_pejabat_ta,
            'posisi_pejabat_ta' => $lastRecord->posisi_pejabat_ta,
            'nik_pejabat' => $lastRecord->nik_pejabat,
            'manager_area_id' => $lastRecord->manager_area_id,
            'nama_mgr_area' => $lastRecord->nama_mgr_area,
            'jabatan_mgr_area' => $lastRecord->jabatan_mgr_area,
            'direktur_mitra_id' => $lastRecord->direktur_mitra_id,
            'direktur_mitra' => $lastRecord->direktur_mitra,
            'jabatan_mitra' => $lastRecord->jabatan_mitra,
            'waspang_id' => $lastRecord->waspang_id,
            'waspang' => $lastRecord->waspang,
            'jabatan_waspang' => $lastRecord->jabatan_waspang,
            'nik_waspang' => $lastRecord->nik_waspang,
            'no_baut' => $lastRecord->no_baut,
            'tanggal_baut' => $lastRecord->tanggal_baut,
            'no_ba_rekon' => $lastRecord->no_ba_rekon,
            'tanggal_ba_rekon' => $lastRecord->tanggal_ba_rekon,
            'no_ba_abd' => $lastRecord->no_ba_abd,
            'tanggal_ba_abd' => $lastRecord->tanggal_ba_abd,
            'tanggal_ba_legal' => $lastRecord->tanggal_ba_legal,
            'waspang_mitra_id' => $lastRecord->waspang_mitra_id,
            'waspang_mitra' => $lastRecord->waspang_mitra,
            'jabatan_waspang_mitra' => $lastRecord->jabatan_waspang_mitra,
            'nik_waspang_mitra' => $lastRecord->nik_waspang_mitra,
        ];
    }
    private static function getMitraNames(): array
    {
        return Mitra::orderBy('nama_mitra')
            ->pluck('nama_mitra', 'nama_mitra')
            ->toArray();
    }
    private static function getAreaNames(): array
    {
        return MitraPendaftaran::distinct()
            ->pluck('area')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
    private static function getNamaPekerjaan(): array
    {
        return MitraPendaftaran::distinct()
            ->pluck('nama_pekerjaan')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
    private static function getAlamatKantor(): array
    {
        return MitraPendaftaran::distinct()
            ->pluck('alamat_kantor')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
    private static function getActivePejabatByKategori(string $kategori): array
    {
        $today = Carbon::today();
        return PejabatHistory::where('kategori', $kategori)
            ->where('awal', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('akhir')
                    ->orWhere('akhir', '>=', $today);
            })
            ->get()
            ->pluck('nama', 'id')
            ->toArray();
    }
    private static function formatIndonesianDate($date): string
    {
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $day = $date->format('d');
        $month = $monthNames[$date->format('n')];
        $year = $date->format('Y');
        return "{$day} {$month} {$year}";
    }

    private static function getAllPejabatByPosisi(string $posisi, ?int $userId = null): array
    {
        $query = PejabatHistory::where('posisi', $posisi);

        // Jika ada filter userId yang dikirim, tambahkan ke query
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $pejabats = $query->orderByRaw('CASE WHEN akhir IS NULL THEN 0 ELSE 1 END')
            ->orderBy('awal', 'desc')
            ->orderBy('akhir', 'desc')
            ->orderBy('nama', 'asc')
            ->get();

        $options = [];
        foreach ($pejabats as $pejabat) {
            $periode = '';
            if ($pejabat->akhir) {
                $periode = " (" . self::formatIndonesianDate($pejabat->awal) . " - " . self::formatIndonesianDate($pejabat->akhir) . ")";
            } else {
                $periode = " (" . self::formatIndonesianDate($pejabat->awal) . " - Sekarang)";
            }
            $options[$pejabat->id] = $pejabat->nama . $periode;
        }
        return $options;
    }

    private static function getPeriodeJabatan(int $pejabatId): ?string
    {
        try {
            $pejabat = PejabatHistory::find($pejabatId);
            if (!$pejabat) {
                return null;
            }
            $awal = self::formatIndonesianDate($pejabat->awal);
            if ($pejabat->akhir) {
                $akhir = self::formatIndonesianDate($pejabat->akhir);
                return "Periode: {$awal} s/d {$akhir}";
            } else {
                return "Periode: {$awal} s/d Sekarang";
            }
        } catch (\Exception $e) {
            return null;
        }
    }
    private static function getPejabatData(int $id): ?array
    {
        $pejabat = PejabatHistory::find($id);
        if (!$pejabat) {
            return null;
        }
        return [
            'nama' => $pejabat->nama,
            'jabatan' => $pejabat->jabatan,
            'nik' => $pejabat->nik ?? null,
        ];
    }
    public static function form(Form $form): Form
    {
        $lastInput = session()->pull('duplicated_data') ?? self::getLastUserInput();
        $lastInput = session()->pull('duplicated_data') ?? self::getLastUserInput();
        $isMitra = Auth::user()->role === 'user';
        $namaPerusahaan = ($isMitra && Auth::user()->mitra) ? Auth::user()->mitra->nama_mitra : null;
        $mitraOtomatis = $namaPerusahaan ? self::getMitraDataByName($namaPerusahaan) : null;
        return $form
            ->schema([
                Auth::id() === 1
                    ? Select::make('user_id')
                    ->label('Pemilik Data (Pilih User)')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->default(Auth::id())
                    ->required()
                    : Hidden::make('user_id')
                    ->default(Auth::id())
                    ->dehydrated(true),
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Data Perjanjian')
                        ->description('Informasi dasar kontrak dan mitra')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Section::make('Informasi Mitra')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('nama_mitra')
                                                ->label('Nama Mitra')
                                                ->options(self::getMitraNames())
                                                ->default(fn() => $isMitra ? $namaPerusahaan : null)
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->searchable()
                                                ->required() // Jika required, berarti Mitra harus punya data perusahaan di database
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                                    if ($state) {
                                                        $mitraData = self::getMitraDataByName($state);
                                                        if ($mitraData) {
                                                            $set('no_khs_mitra', $mitraData['no_khs_mitra']);
                                                            $set('amd_khs_mitra_1', $mitraData['amd_khs_mitra_1']);
                                                            $set('amd_khs_mitra_2', $mitraData['amd_khs_mitra_2']);
                                                            $set('amd_khs_mitra_3', $mitraData['amd_khs_mitra_3']);
                                                            $set('amd_khs_mitra_4', $mitraData['amd_khs_mitra_4']);
                                                            $set('amd_khs_mitra_5', $mitraData['amd_khs_mitra_5']);
                                                            $set('direktur_mitra', $mitraData['direktur_mitra']);
                                                            $set('jabatan_mitra', $mitraData['jabatan_mitra']);
                                                            $set('alamat_kantor', $mitraData['alamat_kantor']);
                                                        }
                                                    }
                                                })
                                                ->createOptionForm([
                                                    Forms\Components\TextInput::make('nama_mitra')
                                                        ->label('Nama Mitra')
                                                        ->required()
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('no_khs_mitra')
                                                        ->label('No. KHS')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('amd_khs_mitra_1')
                                                        ->label('NO AMANDEMEN I (KHS)')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('amd_khs_mitra_2')
                                                        ->label('NO AMANDEMEN II (KHS)')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('amd_khs_mitra_3')
                                                        ->label('NO AMANDEMEN III (KHS)')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('amd_khs_mitra_4')
                                                        ->label('NO AMANDEMEN IV (KHS)')
                                                        ->maxLength(255),
                                                    Forms\Components\TextInput::make('amd_khs_mitra_5')
                                                        ->label('NO AMANDEMEN V (KHS)')
                                                        ->maxLength(255),

                                                ])
                                                ->createOptionUsing(function (array $data) {
                                                    $mitra = Mitra::create([
                                                        'nama_mitra' => $data['nama_mitra'],
                                                        'no_khs_mitra' => $data['no_khs_mitra'] ?? null,
                                                        'amd_khs_mitra_1' => $data['amd_khs_mitra_1'] ?? null,
                                                        'amd_khs_mitra_2' => $data['amd_khs_mitra_2'] ?? null,
                                                        'amd_khs_mitra_3' => $data['amd_khs_mitra_3'] ?? null,
                                                        'amd_khs_mitra_4' => $data['amd_khs_mitra_4'] ?? null,
                                                        'amd_khs_mitra_5' => $data['amd_khs_mitra_5'] ?? null,
                                                    ]);
                                                    return $mitra->nama_mitra;
                                                }),
                                            Forms\Components\TextInput::make('no_khs_mitra')
                                                ->label('No. KHS')
                                                ->default(fn() => $isMitra ? ($mitraOtomatis['no_khs_mitra'] ?? '') : ($lastInput['no_khs_mitra'] ?? ''))
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->required()
                                                ->maxLength(255),
                                        ]),

                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\TextInput::make('amd_khs_mitra_1')
                                                ->label('NO AMANDEMEN I (KHS)')
                                                ->default(fn() => $isMitra ? ($mitraOtomatis['amd_khs_mitra_1'] ?? '') : ($lastInput['amd_khs_mitra_1'] ?? ''))
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('amd_khs_mitra_2')
                                                ->label('NO AMANDEMEN II (KHS)')
                                                ->default(fn() => $isMitra ? ($mitraOtomatis['amd_khs_mitra_2'] ?? '') : ($lastInput['amd_khs_mitra_2'] ?? ''))
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('amd_khs_mitra_3')
                                                ->label('NO AMANDEMEN III (KHS)')
                                                ->default(fn() => $isMitra ? ($mitraOtomatis['amd_khs_mitra_3'] ?? '') : ($lastInput['amd_khs_mitra_3'] ?? ''))
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->maxLength(255),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('amd_khs_mitra_4')
                                                ->label('NO AMANDEMEN IV (KHS)')
                                                ->default(fn() => $isMitra ? ($mitraOtomatis['amd_khs_mitra_4'] ?? '') : ($lastInput['amd_khs_mitra_4'] ?? ''))
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('amd_khs_mitra_5')
                                                ->label('NO AMANDEMEN V (KHS)')
                                                ->default(fn() => $isMitra ? ($mitraOtomatis['amd_khs_mitra_5'] ?? '') : ($lastInput['amd_khs_mitra_5'] ?? ''))
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->maxLength(255),
                                        ]),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('nomer_sp_mitra')
                                                ->label('Nomor SP dan Tanggalnya')
                                                ->default($lastInput['nomer_sp_mitra'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('amd_sp')
                                                ->label('AMANDEMEN SP')
                                                ->default($lastInput['amd_sp'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                        ]),
                                ]),
                            Forms\Components\Section::make('Detail Pekerjaan')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_pekerjaan')
                                        ->label('Nama Pekerjaan')
                                        ->default($lastInput['nama_pekerjaan'] ?? '')
                                        ->required()
                                        ->maxLength(255)
                                        ->datalist(self::getNamaPekerjaan())
                                        ->columnSpanFull(),
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('regional')
                                                ->label('Regional')
                                                ->options([
                                                    'I' => 'I',
                                                    'II' => 'II',
                                                    'III' => 'III',
                                                    'IV' => 'IV',
                                                    'V' => 'V',
                                                    'VI' => 'VI',
                                                    'VII' => 'VII'
                                                ])
                                                ->default($lastInput['regional'] ?? 'II')
                                                ->required()
                                                ->searchable(),
                                            Forms\Components\TextInput::make('area')
                                                ->label('Area')
                                                ->default($lastInput['area'] ?? 'TASIKMALAYA')
                                                ->required()
                                                ->datalist(self::getAreaNames())
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('idp')
                                                ->label('IDP')
                                                ->default($lastInput['idp'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                        ]),
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DatePicker::make('toc')
                                                ->label('Tanggal TOC')
                                                ->default($lastInput['toc'] ?? null)
                                                ->required()
                                                ->displayFormat('d/m/Y'),
                                            Forms\Components\TextInput::make('alamat_kantor')
                                                ->label('Alamat Kantor')
                                                ->default(
                                                    fn() => $isMitra
                                                        ? ($mitraOtomatis['alamat_kantor'] ?? '')
                                                        : ($lastInput['alamat_kantor'] ?? '')
                                                )
                                                ->disabled(fn() => $isMitra)
                                                ->dehydrated(true)
                                                ->required()
                                                ->maxLength(500),
                                        ]),
                                ]),
                        ]),
                    Forms\Components\Wizard\Step::make('Data Penandatangan')
                        ->description('Informasi pejabat yang menandatangani')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Forms\Components\Section::make('GM')
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\Select::make('pejabat_ta_id')
                                                ->label('Pilih GM')
                                                ->options(fn() => self::getAllPejabatByPosisi('Pejabat TA'))
                                                ->default($lastInput['pejabat_ta_id'] ?? null)
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                                    if ($state) {
                                                        $pejabat = self::getPejabatData((int) $state);
                                                        if ($pejabat) {
                                                            $set('nama_pejabat_ta', $pejabat['nama']);
                                                            $set('posisi_pejabat_ta', $pejabat['jabatan']);
                                                            $set('nik_pejabat', $pejabat['nik']);
                                                        }
                                                        $periode = self::getPeriodeJabatan((int) $state);
                                                        if ($periode) {
                                                            $set('periode_pejabat_ta', $periode);
                                                        }
                                                    }
                                                }),
                                            Forms\Components\TextInput::make('nama_pejabat_ta')
                                                ->label('Nama GM')
                                                ->default($lastInput['nama_pejabat_ta'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('posisi_pejabat_ta')
                                                ->label('Posisi GM')
                                                ->default($lastInput['posisi_pejabat_ta'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('nik_pejabat')
                                                ->label('NIK GM')
                                                ->default($lastInput['nik_pejabat'] ?? '')
                                                ->maxLength(255),
                                        ]),
                                    Forms\Components\Placeholder::make('periode_pejabat_ta')
                                        ->label('Periode Jabatan')
                                        ->content(fn($state) => $state ?: 'Belum dipilih')
                                        ->visible(fn($get) => filled($get('pejabat_ta_id'))),
                                ]),
                            Forms\Components\Section::make('Manager Area')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('manager_area_id')
                                                ->label('Pilih Manager Area')
                                                ->options(fn() => self::getAllPejabatByPosisi('Manager Area'))
                                                ->default($lastInput['manager_area_id'] ?? null)
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                                    if ($state) {
                                                        $pejabat = self::getPejabatData((int) $state);
                                                        if ($pejabat) {
                                                            $set('nama_mgr_area', $pejabat['nama']);
                                                            $set('jabatan_mgr_area', $pejabat['jabatan']);
                                                        }
                                                        $periode = self::getPeriodeJabatan((int) $state);
                                                        if ($periode) {
                                                            $set('periode_mgr_area', $periode);
                                                        }
                                                    }
                                                }),
                                            Forms\Components\TextInput::make('nama_mgr_area')
                                                ->label('Nama Manager Area')
                                                ->default($lastInput['nama_mgr_area'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('jabatan_mgr_area')
                                                ->label('Jabatan Manager Area')
                                                ->default($lastInput['jabatan_mgr_area'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                        ]),
                                    Forms\Components\Placeholder::make('periode_mgr_area')
                                        ->label('Periode Jabatan')
                                        ->content(fn($state) => $state ?: 'Belum dipilih')
                                        ->visible(fn($get) => filled($get('manager_area_id'))),
                                ]),
                            Forms\Components\Section::make('Direktur Utama')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('direktur_mitra_id')
                                                ->label('Pilih Direktur Utama')
                                                ->options(fn() => self::getAllPejabatByPosisi('Direktur Utama'))
                                                ->default($lastInput['direktur_mitra_id'] ?? null)
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                                    if ($state) {
                                                        $pejabat = self::getPejabatData((int) $state);
                                                        if ($pejabat) {
                                                            $set('direktur_mitra', $pejabat['nama']);
                                                            $set('jabatan_mitra', $pejabat['jabatan']);
                                                        }
                                                        $periode = self::getPeriodeJabatan((int) $state);
                                                        if ($periode) {
                                                            $set('periode_direktur_mitra', $periode);
                                                        }
                                                    }
                                                }),
                                            Forms\Components\TextInput::make('direktur_mitra')
                                                ->label('Nama Direktur Utama')
                                                ->default($lastInput['direktur_mitra'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('jabatan_mitra')
                                                ->label('Jabatan Direktur Utama')
                                                ->default($lastInput['jabatan_mitra'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                        ]),
                                    Forms\Components\Placeholder::make('periode_direktur_mitra')
                                        ->label('Periode Jabatan')
                                        ->content(fn($state) => $state ?: 'Belum dipilih')
                                        ->visible(fn($get) => filled($get('direktur_mitra_id'))),
                                ]),
                            Forms\Components\Section::make('Waspang')
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\Select::make('waspang_id')
                                                ->label('Pilih Waspang')
                                                ->options(fn() => self::getAllPejabatByPosisi('Waspang'))
                                                ->default($lastInput['waspang_id'] ?? null)
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                                    if ($state) {
                                                        $pejabat = self::getPejabatData((int) $state);
                                                        if ($pejabat) {
                                                            $set('waspang', $pejabat['nama']);
                                                            $set('jabatan_waspang', $pejabat['jabatan']);
                                                            $set('nik_waspang', $pejabat['nik']);
                                                        }
                                                        $periode = self::getPeriodeJabatan((int) $state);
                                                        if ($periode) {
                                                            $set('periode_waspang', $periode);
                                                        }
                                                    }
                                                }),
                                            Forms\Components\TextInput::make('waspang')
                                                ->label('Nama Waspang')
                                                ->default($lastInput['waspang'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('jabatan_waspang')
                                                ->label('Jabatan Waspang')
                                                ->default($lastInput['jabatan_waspang'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('nik_waspang')
                                                ->label('NIK Waspang')
                                                ->default($lastInput['nik_waspang'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                        ]),
                                    Forms\Components\Placeholder::make('periode_waspang')
                                        ->label('Periode Jabatan')
                                        ->content(fn($state) => $state ?: 'Belum dipilih')
                                        ->visible(fn($get) => filled($get('waspang_id'))),
                                ]),

                            Forms\Components\Section::make('Waspang Mitra')
                                ->schema([
                                    Forms\Components\Grid::make(4)
                                        ->schema([
                                            Forms\Components\Select::make('waspang_mitra_id')
                                                ->label('Pilih Waspang Mitra')
                                                ->options(function (Get $get) {
                                                    // Jika Admin, ambil ID dari form 'user_id'. Jika Mitra, ambil dari Auth yang login.
                                                    $userId = Auth::user()->role === 'admin' ? $get('user_id') : Auth::id();

                                                    return self::getAllPejabatByPosisi('Waspang Mitra', $userId);
                                                })
                                                ->default($lastInput['waspang_mitra_id'] ?? null)
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                                    if ($state) {
                                                        $pejabat = self::getPejabatData((int) $state);
                                                        if ($pejabat) {
                                                            $set('waspang_mitra', $pejabat['nama']);
                                                            $set('jabatan_waspang_mitra', $pejabat['jabatan']);
                                                            $set('nik_waspang_mitra', $pejabat['nik']);
                                                        }
                                                        $periode = self::getPeriodeJabatan((int) $state);
                                                        if ($periode) {
                                                            $set('periode_waspang_mitra', $periode);
                                                        }
                                                    }
                                                }),
                                            Forms\Components\TextInput::make('waspang_mitra')
                                                ->label('Nama Waspang Mitra')
                                                ->default($lastInput['waspang_mitra'] ?? '')
                                                // ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('jabatan_waspang_mitra')
                                                ->label('Jabatan Waspang Mitra')
                                                ->default($lastInput['jabatan_waspang_mitra'] ?? '')
                                                // ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('nik_waspang_mitra')
                                                ->label('NIK Waspang Mitra')
                                                ->default($lastInput['nik_waspang_mitra'] ?? '')
                                                // ->required()
                                                ->maxLength(255),
                                        ]),
                                    Forms\Components\Placeholder::make('periode_waspang_mitra')
                                        ->label('Periode Jabatan')
                                        ->content(fn($state) => $state ?: 'Belum dipilih')
                                        ->visible(fn($get) => filled($get('waspang_mitra_id'))),
                                ]),

                        ]),
                    Forms\Components\Wizard\Step::make('Dokumen & Tanggal')
                        ->description('Nomor dan tanggal dokumen pendukung')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Forms\Components\Section::make('BAUT')
                                ->description('Berita Acara Uji Ujung Terbuka')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('no_baut')
                                                ->label('Nomor BAUT')
                                                ->default($lastInput['no_baut'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\DatePicker::make('tanggal_baut')
                                                ->label('Tanggal BAUT')
                                                ->default($lastInput['tanggal_baut'] ?? null)
                                                ->required()
                                                ->displayFormat('d/m/Y'),
                                        ]),
                                ]),
                            Forms\Components\Section::make('BA Rekon')
                                ->description('Berita Acara Rekonsiliasi')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('no_ba_rekon')
                                                ->label('Nomor BA Rekon')
                                                ->default($lastInput['no_ba_rekon'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\DatePicker::make('tanggal_ba_rekon')
                                                ->label('Tanggal BA Rekon')
                                                ->default($lastInput['tanggal_ba_rekon'] ?? null)
                                                ->required()
                                                ->displayFormat('d/m/Y'),
                                        ]),
                                ]),
                            Forms\Components\Section::make('BA ABD')
                                ->description('Berita Acara ABD')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('no_ba_abd')
                                                ->label('Nomor BA ABD')
                                                ->default($lastInput['no_ba_abd'] ?? '')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\DatePicker::make('tanggal_ba_abd')
                                                ->label('Tanggal BA ABD')
                                                ->default($lastInput['tanggal_ba_abd'] ?? null)
                                                ->required()
                                                ->displayFormat('d/m/Y'),
                                        ]),
                                ]),
                            Forms\Components\Section::make('BA Legal')
                                ->description('Berita Acara Legal')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\DatePicker::make('tanggal_ba_legal')
                                                ->label('Tanggal BA Legal')
                                                ->default($lastInput['tanggal_ba_legal'] ?? null)
                                                ->required()
                                                ->displayFormat('d/m/Y'),
                                        ]),
                                ]),
                        ]),
                ])
                    ->columnSpanFull()
                    ->persistStepInQueryString()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomer_sp_mitra')
                    ->label('Nomor SP')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('nama_pekerjaan')
                    ->label('Nama Pekerjaan')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('regional')
                    ->options([
                        'I' => 'I',
                        'II' => 'II',
                        'III' => 'III',
                        'IV' => 'IV',
                        'V' => 'V',
                        'VI' => 'VI',
                        'VII' => 'VII',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Filter by User')
                    ->options(fn() => User::pluck('name', 'id')->toArray())
                    ->visible(fn() => Auth::id() === 1),
                Tables\Filters\SelectFilter::make('nama_mitra')
                    ->label('Filter by Mitra')
                    ->options(fn() => Mitra::pluck('nama_mitra', 'nama_mitra')->toArray())
                    ->searchable(),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $data = $record->replicate()->toArray();
                        if (isset($data['nomer_sp_mitra'])) {
                            $data['nomer_sp_mitra'] .= ' (Copy)';
                        }
                        session(['duplicated_data' => $data]);
                        return redirect()->route('filament.admin.resources.mitra-pendaftarans.create');
                    }),
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
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMitraPendaftarans::route('/'),
            'create' => Pages\CreateMitraPendaftaran::route('/create'),
            'edit' => Pages\EditMitraPendaftaran::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
