<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\MitraPendaftaran;
use App\Models\BoqLine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class BoqInputPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static string $view = 'filament.pages.boq-input-page';
    protected static ?string $title = 'Input BOQ';
    protected static ?string $navigationLabel = '2. Input BOQ';
    protected static ?int $navigationSort = 2;
    // protected static bool $shouldRegisterNavigation = false;
    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->role === 'user';
    }

    public $viewingRow = null;
    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
    public ?array $data = [];
    public array $tableData = [];
    public bool $showTable = false;
    public float $ppnPercent = 11.0;
    protected $rules = [
        'ppnPercent' => 'required|numeric|min:0|max:100',
    ];
    public function mount(): void
    {
        $lastMitraId = session('last_mitra_id');
        $this->data = [
            'mitra_id' => $lastMitraId,
        ];
        if ($lastMitraId) {
            $mitra = MitraPendaftaran::find($lastMitraId);
            $this->ppnPercent = $mitra?->ppn_percent ?? 11;
            $this->loadExistingData($lastMitraId);
        }
        $this->form->fill($this->data);
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pilih Mitra')
                    ->schema([
                        Select::make('mitra_id')
                            ->label('Pilih SP dan Nama Pekerjaan')
                            ->options(
                                MitraPendaftaran::all()->mapWithKeys(function ($mitra) {
                                    return [$mitra->id => "{$mitra->nomer_sp_mitra} | {$mitra->nama_pekerjaan}"];
                                })
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->loadExistingData($state);
                            }),
                        TextInput::make('ppnPercent')
                            ->label('PPN (%)')
                            ->numeric()
                            ->default(11)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->rules(['numeric', 'min:0', 'max:100'])
                            ->live(debounce: 500)
                            ->suffix('%')
                            ->afterStateUpdated(function ($state) {
                                $this->ppnPercent = floatval($state);
                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ])
            ->statePath('data');
    }
    public function loadExistingData($mitraId)
    {
        if ($mitraId) {
            $mitra = MitraPendaftaran::find($mitraId);
            $this->ppnPercent = $mitra?->ppn_percent ?? 11;
            $this->data['ppnPercent'] = $this->ppnPercent;
            $this->data['mitra_id'] = $mitraId;
            $this->form->fill($this->data);
            $existingData = BoqLine::where('mitra_pendaftaran_id', $mitraId)
                ->orderBy('id')
                ->get();
            if ($existingData->count() > 0) {
                $this->tableData = $existingData->map(function ($item, $index) {
                    return [
                        'id' => $item->id,
                        'no' => $index + 1,
                        'nama_lokasi' => $item->nama_lokasi,
                        'sto' => $item->sto,
                        'id_project' => $item->id_project,
                        // SP Fields
                        'sp_material' => $this->formatDecimal($item->sp_material ?? 0),
                        'sp_jasa' => $this->formatDecimal($item->sp_jasa ?? 0),
                        'sp_total' => $this->formatDecimal(($item->sp_material ?? 0) + ($item->sp_jasa ?? 0)),
                        // Rekon Fields
                        'rekon_material' => $this->formatDecimal($item->rekon_material ?? 0),
                        'rekon_jasa' => $this->formatDecimal($item->rekon_jasa ?? 0),
                        'rekon_total' => $this->formatDecimal(($item->rekon_material ?? 0) + ($item->rekon_jasa ?? 0)),
                        // Tambah Fields
                        'tambah_material' => $this->formatDecimal($item->tambah_material ?? 0),
                        'tambah_jasa' => $this->formatDecimal($item->tambah_jasa ?? 0),
                        'tambah_total' => $this->formatDecimal(($item->tambah_material ?? 0) + ($item->tambah_jasa ?? 0)),
                        // Kurang Fields
                        'kurang_material' => $this->formatDecimal($item->kurang_material ?? 0),
                        'kurang_jasa' => $this->formatDecimal($item->kurang_jasa ?? 0),
                        'kurang_total' => $this->formatDecimal(($item->kurang_material ?? 0) + ($item->kurang_jasa ?? 0)),
                        // Grand Total
                        'grand_total' => $this->formatDecimal(
                            (($item->sp_material ?? 0) + ($item->sp_jasa ?? 0)) +
                                (($item->rekon_material ?? 0) + ($item->rekon_jasa ?? 0)) +
                                (($item->tambah_material ?? 0) + ($item->tambah_jasa ?? 0)) -
                                (($item->kurang_material ?? 0) + ($item->kurang_jasa ?? 0))
                        ),
                    ];
                })->toArray();
            } else {
                $this->initializeEmptyTable();
            }
            $this->showTable = true;
            session(['last_mitra_id' => $mitraId]);
        } else {
            $this->showTable = false;
            $this->tableData = [];
        }
    }
    private function initializeEmptyTable()
    {
        $this->tableData = [];
        for ($i = 1; $i <= 20; $i++) { // Changed from 5 to 20 rows
            $this->tableData[] = [
                'id' => null,
                'no' => $i,
                'nama_lokasi' => '',
                'sto' => '',
                'id_project' => '',
                // SP Fields
                'sp_material' => 0.00,
                'sp_jasa' => 0.00,
                'sp_total' => 0.00,
                // Rekon Fields
                'rekon_material' => 0.00,
                'rekon_jasa' => 0.00,
                'rekon_total' => 0.00,
                // Tambah Fields
                'tambah_material' => 0.00,
                'tambah_jasa' => 0.00,
                'tambah_total' => 0.00,
                // Kurang Fields
                'kurang_material' => 0.00,
                'kurang_jasa' => 0.00,
                'kurang_total' => 0.00,
                // Grand Total
                'grand_total' => 0.00,
            ];
        }
    }
    public function addRow()
    {
        $this->tableData[] = [
            'id' => null,
            'no' => count($this->tableData) + 1,
            'nama_lokasi' => '',
            'sto' => '',
            'id_project' => '',
            // SP Fields
            'sp_material' => 0.00,
            'sp_jasa' => 0.00,
            'sp_total' => 0.00,
            // Rekon Fields
            'rekon_material' => 0.00,
            'rekon_jasa' => 0.00,
            'rekon_total' => 0.00,
            // Tambah Fields
            'tambah_material' => 0.00,
            'tambah_jasa' => 0.00,
            'tambah_total' => 0.00,
            // Kurang Fields
            'kurang_material' => 0.00,
            'kurang_jasa' => 0.00,
            'kurang_total' => 0.00,
            // Grand Total
            'grand_total' => 0.00,
        ];
    }
    public function removeRow($index)
    {
        if (isset($this->tableData[$index])) {
            if ($this->tableData[$index]['id']) {
                BoqLine::find($this->tableData[$index]['id'])?->delete();
            }
            unset($this->tableData[$index]);
            $this->tableData = array_values($this->tableData);
            // Renumber rows
            foreach ($this->tableData as $i => &$row) {
                $row['no'] = $i + 1;
            }
            Notification::make()
                ->title('Baris dihapus')
                ->success()
                ->send();
        }
    }
    public function dropBoq($id)
    {
        try {
            $response = Http::delete(route('boq.drop', ['id' => $id]));
            if ($response->successful()) {
                Notification::make()
                    ->title('BOQ berhasil dihapus')
                    ->success()
                    ->send();
                if ($this->data['mitra_id']) {
                    $this->loadExistingData($this->data['mitra_id']);
                }
            } else {
                Notification::make()
                    ->title('Error')
                    ->body('Gagal menghapus BOQ')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    public function clearAllBoqData()
    {
        $mitraId = $this->data['mitra_id'] ?? null;
        if (!$mitraId) {
            Notification::make()
                ->title('Error')
                ->body('Silakan pilih mitra terlebih dahulu')
                ->danger()
                ->send();
            return;
        }
        try {
            BoqLine::where('mitra_pendaftaran_id', $mitraId)->delete();
            $this->initializeEmptyTable();
            Notification::make()
                ->title('Semua data BOQ berhasil dihapus')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    public function viewRow($index)
    {
        if (isset($this->tableData[$index])) {
            $row = $this->tableData[$index];
            $this->viewingRow = $row;
            $this->dispatch('open-view-modal');
        }
    }
    public function submit()
    {
        $data = $this->form->getState();
        $mitraId = $data['mitra_id'] ?? null;
        if (!$mitraId) {
            Notification::make()
                ->title('Error')
                ->body('Silakan pilih mitra terlebih dahulu')
                ->danger()
                ->send();
            return;
        }
        // Update PPN percent
        MitraPendaftaran::where('id', $mitraId)->update([
            'ppn_percent' => $this->ppnPercent,
        ]);
        // Delete existing BOQ lines
        BoqLine::where('mitra_pendaftaran_id', $mitraId)->delete();
        $savedCount = 0;
        foreach ($this->tableData as $line) {
            // Check if essential fields are filled
            if (!empty($line['nama_lokasi']) && !empty($line['sto'])) {
                // Parse all decimal values
                $spMaterial = $this->parseDecimal($line['sp_material']);
                $spJasa = $this->parseDecimal($line['sp_jasa']);
                $rekonMaterial = $this->parseDecimal($line['rekon_material']);
                $rekonJasa = $this->parseDecimal($line['rekon_jasa']);
                $tambahMaterial = $this->parseDecimal($line['tambah_material']);
                $tambahJasa = $this->parseDecimal($line['tambah_jasa']);
                $kurangMaterial = $this->parseDecimal($line['kurang_material']);
                $kurangJasa = $this->parseDecimal($line['kurang_jasa']);
                BoqLine::create([
                    'mitra_pendaftaran_id' => $mitraId,
                    'nama_lokasi' => $line['nama_lokasi'],
                    'sto' => $line['sto'],
                    'id_project' => $line['id_project'] ?? '',
                    // SP Fields
                    'sp_material' => $spMaterial,
                    'sp_jasa' => $spJasa,
                    'sp_total' => $spMaterial + $spJasa,
                    // Rekon Fields
                    'rekon_material' => $rekonMaterial,
                    'rekon_jasa' => $rekonJasa,
                    'rekon_total' => $rekonMaterial + $rekonJasa,
                    // Tambah Fields
                    'tambah_material' => $tambahMaterial,
                    'tambah_jasa' => $tambahJasa,
                    'tambah_total' => $tambahMaterial + $tambahJasa,
                    // Kurang Fields
                    'kurang_material' => $kurangMaterial,
                    'kurang_jasa' => $kurangJasa,
                    'kurang_total' => $kurangMaterial + $kurangJasa,
                    // Grand Total
                    'grand_total' => ($spMaterial + $spJasa) + ($rekonMaterial + $rekonJasa) + ($tambahMaterial + $tambahJasa) - ($kurangMaterial + $kurangJasa),
                ]);
                $savedCount++;
            }
        }
        if ($savedCount > 0) {
            Notification::make()
                ->title('Berhasil!')
                ->body("$savedCount baris BOQ berhasil disimpan")
                ->success()
                ->send();
            $this->loadExistingData($mitraId);
        } else {
            Notification::make()
                ->title('Tidak ada data untuk disimpan')
                ->body('Pastikan Nama Lokasi dan STO sudah diisi')
                ->warning()
                ->send();
        }
    }
    public function getGrandTotals()
    {
        $totals = [
            'sp_material' => 0,
            'sp_jasa' => 0,
            'sp_total' => 0,
            'rekon_material' => 0,
            'rekon_jasa' => 0,
            'rekon_total' => 0,
            'tambah_material' => 0,
            'tambah_jasa' => 0,
            'tambah_total' => 0,
            'kurang_material' => 0,
            'kurang_jasa' => 0,
            'kurang_total' => 0,
            'grand_total' => 0,
        ];
        foreach ($this->tableData as $row) {
            $totals['sp_material'] += $this->parseDecimal($row['sp_material'] ?? 0);
            $totals['sp_jasa'] += $this->parseDecimal($row['sp_jasa'] ?? 0);
            $totals['rekon_material'] += $this->parseDecimal($row['rekon_material'] ?? 0);
            $totals['rekon_jasa'] += $this->parseDecimal($row['rekon_jasa'] ?? 0);
            $totals['tambah_material'] += $this->parseDecimal($row['tambah_material'] ?? 0);
            $totals['tambah_jasa'] += $this->parseDecimal($row['tambah_jasa'] ?? 0);
            $totals['kurang_material'] += $this->parseDecimal($row['kurang_material'] ?? 0);
            $totals['kurang_jasa'] += $this->parseDecimal($row['kurang_jasa'] ?? 0);
        }
        $totals['sp_total'] = $totals['sp_material'] + $totals['sp_jasa'];
        $totals['rekon_total'] = $totals['rekon_material'] + $totals['rekon_jasa'];
        $totals['tambah_total'] = $totals['tambah_material'] + $totals['tambah_jasa'];
        $totals['kurang_total'] = $totals['kurang_material'] + $totals['kurang_jasa'];
        $totals['grand_total'] = $totals['sp_total'] + $totals['rekon_total'] + $totals['tambah_total'] - $totals['kurang_total'];
        return $totals;
    }
    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Simpan Data BOQ')
                ->action('submit')
                ->color('success')
                ->size('lg')
                ->visible(fn() => $this->showTable),
        ];
    }
    public function getHeaderActions(): array
    {
        return [

            Action::make('clearAllBoqData')
                ->label('Hapus Semua BOQ')
                ->action('clearAllBoqData')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Semua Data BOQ')
                ->modalDescription('Apakah Anda yakin ingin menghapus semua data BOQ untuk mitra ini?')
                ->modalSubmitActionLabel('Ya, Hapus Semua')
                ->visible(fn() => $this->showTable),
        ];
    }
    public function getExtraBodyAttributes(): array
    {
        return [
            'class' => 'boq-fullscreen-page',
        ];
    }
    // Helper methods for number formatting and parsing
    public function formatNumber($number)
    {
        return number_format($this->parseDecimal($number), 2, ',', '.');
    }
    public function parseNumber($value)
    {
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        return floatval($value);
    }
    public function formatDecimal($value)
    {
        return round(floatval($value), 2);
    }
    public function parseDecimal($value)
    {
        if (is_string($value)) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        return round(floatval($value), 2);
    }
    public function updateCell($index, $field, $value)
    {
        if (isset($this->tableData[$index])) {
            $this->tableData[$index][$field] = $this->parseDecimal($value);
            $this->calculateRowTotals($index);
        }
    }
    private function calculateRowTotals($index)
    {
        if (isset($this->tableData[$index])) {
            $row = &$this->tableData[$index];
            // Calculate totals for each category
            $row['sp_total'] = $this->parseDecimal($row['sp_material']) + $this->parseDecimal($row['sp_jasa']);
            $row['rekon_total'] = $this->parseDecimal($row['rekon_material']) + $this->parseDecimal($row['rekon_jasa']);
            $row['tambah_total'] = $this->parseDecimal($row['tambah_material']) + $this->parseDecimal($row['tambah_jasa']);
            $row['kurang_total'] = $this->parseDecimal($row['kurang_material']) + $this->parseDecimal($row['kurang_jasa']);
            // Calculate grand total
            $row['grand_total'] = $row['sp_total'] + $row['rekon_total'] + $row['tambah_total'] - $row['kurang_total'];
        }
    }
    public function duplicateRow($index)
    {
        if (isset($this->tableData[$index])) {
            $newRow = $this->tableData[$index];
            $newRow['id'] = null;
            $newRow['no'] = count($this->tableData) + 1;
            $this->tableData[] = $newRow;
            Notification::make()
                ->title('Baris diduplikasi')
                ->success()
                ->send();
        }
    }
    public function clearAllData()
    {
        $this->initializeEmptyTable();
        Notification::make()
            ->title('Data dikosongkan')
            ->success()
            ->send();
    }
    // New method to get template variables for export/reporting
    public function getTemplateVariables()
    {
        $variables = [];
        // Generate variables for each row (1-20)
        for ($i = 1; $i <= 20; $i++) {
            $rowIndex = $i - 1;
            $row = $this->tableData[$rowIndex] ?? null;
            if ($row) {
                // SP Variables
                $variables["sp_material_{$i}"] = $this->formatDecimal($row['sp_material'] ?? 0);
                $variables["sp_jasa_{$i}"] = $this->formatDecimal($row['sp_jasa'] ?? 0);
                $variables["sp_total_{$i}"] = $this->formatDecimal($row['sp_total'] ?? 0);
                // Rekon Variables
                $variables["rekon_material_{$i}"] = $this->formatDecimal($row['rekon_material'] ?? 0);
                $variables["rekon_jasa_{$i}"] = $this->formatDecimal($row['rekon_jasa'] ?? 0);
                $variables["rekon_total_{$i}"] = $this->formatDecimal($row['rekon_total'] ?? 0);
                // Tambah Variables
                $variables["tambah_material_{$i}"] = $this->formatDecimal($row['tambah_material'] ?? 0);
                $variables["tambah_jasa_{$i}"] = $this->formatDecimal($row['tambah_jasa'] ?? 0);
                $variables["tambah_total_{$i}"] = $this->formatDecimal($row['tambah_total'] ?? 0);
                // Kurang Variables
                $variables["kurang_material_{$i}"] = $this->formatDecimal($row['kurang_material'] ?? 0);
                $variables["kurang_jasa_{$i}"] = $this->formatDecimal($row['kurang_jasa'] ?? 0);
                $variables["kurang_total_{$i}"] = $this->formatDecimal($row['kurang_total'] ?? 0);
                // Grand Total
                $variables["grand_total_{$i}"] = $this->formatDecimal($row['grand_total'] ?? 0);
            } else {
                // Initialize empty variables if row doesn't exist
                $variables["sp_material_{$i}"] = 0.00;
                $variables["sp_jasa_{$i}"] = 0.00;
                $variables["sp_total_{$i}"] = 0.00;
                $variables["rekon_material_{$i}"] = 0.00;
                $variables["rekon_jasa_{$i}"] = 0.00;
                $variables["rekon_total_{$i}"] = 0.00;
                $variables["tambah_material_{$i}"] = 0.00;
                $variables["tambah_jasa_{$i}"] = 0.00;
                $variables["tambah_total_{$i}"] = 0.00;
                $variables["kurang_material_{$i}"] = 0.00;
                $variables["kurang_jasa_{$i}"] = 0.00;
                $variables["kurang_total_{$i}"] = 0.00;
                $variables["grand_total_{$i}"] = 0.00;
            }
        }
        return $variables;
    }
    // public function importFromCsv($csvData)
    // {
    // }
    // public function exportToCsv()
    // {
    // }

    public function getValidityStatus()
    {
        $totals = $this->getGrandTotals();

        $result = $totals['sp_total']
            - $totals['kurang_total']
            - $totals['rekon_total']
            + $totals['tambah_total'];

        $isValid = round($result, 2) === 0.00;

        return [
            'result' => $result,
            'status' => $isValid ? 'VALID' : 'NON VALID',
            'color' => $isValid ? 'text-green-600' : 'text-red-600',
        ];
    }
}
