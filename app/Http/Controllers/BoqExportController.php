<?php
namespace App\Http\Controllers;
use App\Filament\Pages\MitraPreview;
use App\Models\BoqLine;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Style\Fill;
class BoqExportController extends Controller
{
    private float $totalRekon = 0;
    private float $totalWithPpn = 0;
    private string $terbilangRekon = '';
    private string $terbilangWithPpn = '';
    private $data;
    public function export($mitraId)
    {
        $mitra = MitraPreview::findOrFail($mitraId);
        $ppnPercent = (float) ($mitra->ppn_percent ?? 11);
        // Ambil data dari database
        $data = BoqLine::where('mitra_pendaftaran_id', $mitraId)
                    ->orderBy('no')
                    ->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $this->data = $data;
        // Setup header dengan merge cells untuk kategori
        $this->setupHeaders($sheet);
        // Tulis data dengan template variables
        $this->writeDataRows($sheet, $data);
        // Tambahkan grand total
        $this->addGrandTotal($sheet, $data->count());
        $this->addPPNandTerbilang($sheet, $data->count(), $ppnPercent);
        // Apply styling
        $this->applyStyles($sheet, $data->count());
        // Auto resize kolom
        $this->autoResizeColumns($sheet);
        // Output ke browser
        $filename = 'BOQ_Mitra_' . $mitraId . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    private function setupHeaders($sheet)
    {
        // Header utama (baris 1)
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Lokasi');
        $sheet->setCellValue('C1', 'STO');
        // Kategori headers
        $sheet->setCellValue('D1', 'NILAI SP');
        $sheet->setCellValue('G1', 'NILAI REKON');
        $sheet->setCellValue('J1', 'NILAI TAMBAH');
        $sheet->setCellValue('M1', 'NILAI KURANG');
        // Merge cells untuk kategori
        $sheet->mergeCells('D1:F1'); // SP
        $sheet->mergeCells('G1:I1');  // REKON
        $sheet->mergeCells('J1:L1');  // TAMBAH
        $sheet->mergeCells('M1:O1');  // KURANG
        // Sub headers (baris 2)
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'Nama Lokasi');
        $sheet->setCellValue('C2', 'STO');
        // SP columns
        $sheet->setCellValue('D2', 'Material');
        $sheet->setCellValue('E2', 'Jasa');
        $sheet->setCellValue('F2', 'Total');
        // REKON columns
        $sheet->setCellValue('G2', 'Material');
        $sheet->setCellValue('H2', 'Jasa');
        $sheet->setCellValue('I2', 'Total');
        // TAMBAH columns
        $sheet->setCellValue('J2', 'Material');
        $sheet->setCellValue('K2', 'Jasa');
        $sheet->setCellValue('L2', 'Total');
        // KURANG columns
        $sheet->setCellValue('M2', 'Material');
        $sheet->setCellValue('N2', 'Jasa');
        $sheet->setCellValue('O2', 'Total');
        // Merge cells untuk kolom yang tidak berubah
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        $sheet->mergeCells('C1:C2');
        $sheet->setCellValue('P1', 'STATUS');
        $sheet->mergeCells('P1:P2');
    }
    private function writeDataRows($sheet, $data)
    {
        $startRow = 3; // Data mulai dari baris 3
        foreach ($data as $index => $item) {
            $currentRow = $startRow + $index;
            $isDropped = (bool) $item->is_dropped;
            // Template variables mapping
            $templateVars = [
                'no' => $item->no ?? ($index + 1),
                'nama_lokasi' => $item->nama_lokasi ?? '',
                'sto' => $item->sto ?? '',
                'sp_material' => (float) ($item->sp_material ?? 0),
                'sp_jasa' => (float) ($item->sp_jasa ?? 0),
                'rekon_material' => (float) ($item->rekon_material ?? 0),
                'rekon_jasa' => (float) ($item->rekon_jasa ?? 0),
                'tambah_material' => (float) ($item->tambah_material ?? 0),
                'tambah_jasa' => (float) ($item->tambah_jasa ?? 0),
                'kurang_material' => (float) ($item->kurang_material ?? 0),
                'kurang_jasa' => (float) ($item->kurang_jasa ?? 0),
            ];
            // Calculated totals
            $templateVars['sp_total'] = $templateVars['sp_material'] + $templateVars['sp_jasa'];
            $templateVars['rekon_total'] = $templateVars['rekon_material'] + $templateVars['rekon_jasa'];
            $templateVars['tambah_total'] = $templateVars['tambah_material'] + $templateVars['tambah_jasa'];
            $templateVars['kurang_total'] = $templateVars['kurang_material'] + $templateVars['kurang_jasa'];
            // Write data ke sheet
            $sheet->setCellValue("A{$currentRow}", $templateVars['no']);
            $sheet->setCellValue("B{$currentRow}", $templateVars['nama_lokasi']);
            $sheet->setCellValue("C{$currentRow}", $templateVars['sto']);
            // SP values
            $sheet->setCellValue("D{$currentRow}", $templateVars['sp_material']);
            $sheet->setCellValue("E{$currentRow}", $templateVars['sp_jasa']);
            $sheet->setCellValue("F{$currentRow}", "=D{$currentRow}+E{$currentRow}");
            // REKON values
            $sheet->setCellValue("G{$currentRow}", $templateVars['rekon_material']);
            $sheet->setCellValue("H{$currentRow}", $templateVars['rekon_jasa']);
            $sheet->setCellValue("I{$currentRow}", "=G{$currentRow}+H{$currentRow}");
            // TAMBAH values
            $sheet->setCellValue("J{$currentRow}", $templateVars['tambah_material']);
            $sheet->setCellValue("K{$currentRow}", $templateVars['tambah_jasa']);
            $sheet->setCellValue("L{$currentRow}", "=J{$currentRow}+K{$currentRow}");
            // KURANG values
            $sheet->setCellValue("M{$currentRow}", $templateVars['kurang_material']);
            $sheet->setCellValue("N{$currentRow}", $templateVars['kurang_jasa']);
            $sheet->setCellValue("O{$currentRow}", "=M{$currentRow}+N{$currentRow}");
            // Kolom status
            $sheet->setCellValue("P{$currentRow}", $isDropped ? 'DROPPED' : '');
            // Styling jika baris dropped
            if ($isDropped) {
                $sheet->getStyle("A{$currentRow}:P{$currentRow}")
                    ->getFont()->getColor()->setRGB('888888'); // abu-abu
                $sheet->getStyle("A{$currentRow}:P{$currentRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FEE2E2'); // merah muda
            }
        }
    }
    private function addGrandTotal($sheet, $dataCount)
    {
        $totalRow = 3 + $dataCount;
        $lastDataRow = $totalRow - 1;
        $sheet->setCellValue("A{$totalRow}", 'GRAND TOTAL');
        $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
        // Grand total formulas
        $sheet->setCellValue("F{$totalRow}", "=SUM(F3:F{$lastDataRow})");
        $sheet->setCellValue("I{$totalRow}", "=SUM(I3:I{$lastDataRow})");
        $sheet->setCellValue("L{$totalRow}", "=SUM(L3:L{$lastDataRow})");
        $sheet->setCellValue("O{$totalRow}", "=SUM(O3:O{$lastDataRow})");
    }
    private function addPPNandTerbilang($sheet, $dataCount, $ppnPercent)
    {
        $rowGrandTotal = 3 + $dataCount;
        $rowPPN = $rowGrandTotal + 1;
        $rowTotalWithPPN = $rowGrandTotal + 2;
        $rowTerbilang = $rowGrandTotal + 3;
        $rowTerbilangTanpaPPN = $rowGrandTotal + 4;
        // Pastikan $this->data tidak null dan berisi data yang valid
        if (empty($this->data) || !is_iterable($this->data)) {
            $this->data = BoqLine::where('mitra_pendaftaran_id', request()->route('mitraId'))
                                ->orderBy('no')
                                ->get();
        }
        // Hitung total rekon
        $totalRekon = 0;
        if (!empty($this->data) && is_iterable($this->data)) {
            foreach ($this->data as $item) {
                $rekonMaterial = (float) ($item->rekon_material ?? 0);
                $rekonJasa = (float) ($item->rekon_jasa ?? 0);
                $totalRekon += $rekonMaterial + $rekonJasa;
            }
        }
        $ppnValue = round($totalRekon * $ppnPercent / 100);
        $totalWithPpn = $totalRekon + $ppnValue;
        // Simpan ke properti controller
        $this->totalRekon = $totalRekon;
        $this->totalWithPpn = $totalWithPpn;
        $this->terbilangRekon = $this->terbilang($totalRekon) . ' Rupiah';
        $this->terbilangWithPpn = $this->terbilang($totalWithPpn) . ' Rupiah';
        // Isi ke Excel dengan format yang benar
        $sheet->setCellValue("F{$rowPPN}", "PPN ({$ppnPercent}%)");
        $sheet->setCellValue("I{$rowPPN}", $ppnValue);
        $sheet->setCellValue("F{$rowTotalWithPPN}", "TOTAL + PPN");
        $sheet->setCellValue("I{$rowTotalWithPPN}", $totalWithPpn);
        $sheet->setCellValue("F{$rowTerbilang}", "Terbilang:");
        $sheet->mergeCells("G{$rowTerbilang}:O{$rowTerbilang}");
        $sheet->setCellValue("G{$rowTerbilang}", $this->terbilangWithPpn);
        $sheet->setCellValue("F{$rowTerbilangTanpaPPN}", "Terbilang (Tanpa PPN):");
        $sheet->mergeCells("G{$rowTerbilangTanpaPPN}:O{$rowTerbilangTanpaPPN}");
        $sheet->setCellValue("G{$rowTerbilangTanpaPPN}", $this->terbilangRekon);
    }
    private function applyStyles($sheet, $dataCount)
    {
        $totalRow = 3 + $dataCount;
        $ppnRows = 4; // PPN, Total+PPN, Terbilang, Terbilang tanpa PPN
        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A1:O2')->applyFromArray($headerStyle);
        // Category colors
        $sheet->getStyle('D1:F2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5'); // Green
        $sheet->getStyle('G1:I2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7'); // Yellow
        $sheet->getStyle('J1:L2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DBEAFE'); // Blue
        $sheet->getStyle('M1:O2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2'); // Red
        // Data styling
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];
        $lastRow = $totalRow + $ppnRows;
        $sheet->getStyle("A3:O{$lastRow}")->applyFromArray($dataStyle);
        // Number formatting untuk kolom currency - format dengan titik sebagai thousand separator
        $currencyColumns = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];
        foreach ($currencyColumns as $col) {
            $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        }
        // Grand total styling
        $grandTotalStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle("A{$totalRow}:O{$totalRow}")->applyFromArray($grandTotalStyle);
        // PPN section styling
        $ppnStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
        ];
        for ($i = 1; $i <= $ppnRows; $i++) {
            $currentPpnRow = $totalRow + $i;
            $sheet->getStyle("F{$currentPpnRow}:O{$currentPpnRow}")->applyFromArray($ppnStyle);
        }
    }
    private function autoResizeColumns($sheet)
    {
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Set minimum width untuk kolom tertentu
        $sheet->getColumnDimension('B')->setWidth(25); // Nama Lokasi
        $sheet->getColumnDimension('C')->setWidth(12); // STO
    }

    public function getTemplateVariables($mitraId)
{
    $data = BoqLine::where('mitra_pendaftaran_id', $mitraId)
                ->orderBy('no')
                ->get();
    $templateData = [];
    // Pisahkan data berdasarkan status dropped
    $activeData = $data->where('is_dropped', false);
    $droppedData = $data->where('is_dropped', true);
    foreach ($data as $index => $item) {
        $isDropped = (bool) $item->is_dropped;
        $templateVars = [
            'no' => $item->no ?? ($index + 1),
            'nama_lokasi' => $item->nama_lokasi ?? '',
            'sto' => $item->sto ?? '',
            // Status drop
            'is_dropped' => $isDropped,
            'status' => $isDropped ? 'DROPPED' : 'ACTIVE',
            // Raw numbers (untuk perhitungan)
            'sp_material' => (float) ($item->sp_material ?? 0),
            'sp_jasa' => (float) ($item->sp_jasa ?? 0),
            'rekon_material' => (float) ($item->rekon_material ?? 0),
            'rekon_jasa' => (float) ($item->rekon_jasa ?? 0),
            'tambah_material' => (float) ($item->tambah_material ?? 0),
            'tambah_jasa' => (float) ($item->tambah_jasa ?? 0),
            'kurang_material' => (float) ($item->kurang_material ?? 0),
            'kurang_jasa' => (float) ($item->kurang_jasa ?? 0),
            // Formatted numbers (untuk display)
            'sp_material_formatted' => $this->formatNumber($item->sp_material ?? 0),
            'sp_jasa_formatted' => $this->formatNumber($item->sp_jasa ?? 0),
            'rekon_material_formatted' => $this->formatNumber($item->rekon_material ?? 0),
            'rekon_jasa_formatted' => $this->formatNumber($item->rekon_jasa ?? 0),
            'tambah_material_formatted' => $this->formatNumber($item->tambah_material ?? 0),
            'tambah_jasa_formatted' => $this->formatNumber($item->tambah_jasa ?? 0),
            'kurang_material_formatted' => $this->formatNumber($item->kurang_material ?? 0),
            'kurang_jasa_formatted' => $this->formatNumber($item->kurang_jasa ?? 0),
        ];
        // Calculated totals (raw)
        $templateVars['sp_total'] = $templateVars['sp_material'] + $templateVars['sp_jasa'];
        $templateVars['rekon_total'] = $templateVars['rekon_material'] + $templateVars['rekon_jasa'];
        $templateVars['tambah_total'] = $templateVars['tambah_material'] + $templateVars['tambah_jasa'];
        $templateVars['kurang_total'] = $templateVars['kurang_material'] + $templateVars['kurang_jasa'];
        // Calculated totals (formatted)
        $templateVars['sp_total_formatted'] = $this->formatNumber($templateVars['sp_total']);
        $templateVars['rekon_total_formatted'] = $this->formatNumber($templateVars['rekon_total']);
        $templateVars['tambah_total_formatted'] = $this->formatNumber($templateVars['tambah_total']);
        $templateVars['kurang_total_formatted'] = $this->formatNumber($templateVars['kurang_total']);
        // Rupiah formatted
        $templateVars['sp_total_rupiah'] = $this->formatRupiah($templateVars['sp_total']);
        $templateVars['rekon_total_rupiah'] = $this->formatRupiah($templateVars['rekon_total']);
        $templateVars['tambah_total_rupiah'] = $this->formatRupiah($templateVars['tambah_total']);
        $templateVars['kurang_total_rupiah'] = $this->formatRupiah($templateVars['kurang_total']);
        $templateData[] = $templateVars;
    }
    return $templateData;
}
    /**
     * Get template variables untuk totals, PPN, dan terbilang
     * Format: single object dengan semua perhitungan grand total
     */
    public function getAdditionalTemplateVariables($mitraId, $ppnPercent = 11)
    {
        $data = BoqLine::where('mitra_pendaftaran_id', $mitraId)->get();
        if ($data->isEmpty()) {
            return $this->getEmptyAdditionalTemplateVariables($ppnPercent);
        }
        $this->data = $data;
        $droppedData = $data->where('is_dropped', true);
        $grandTotalSp = $grandTotalRekon = $grandTotalTambah = $grandTotalKurang = 0;
        foreach ($data as $item) {
            // Rekon selalu dihitung dari semua item (termasuk yang di-drop)
            $grandTotalRekon += (float) ($item->rekon_material ?? 0) + (float) ($item->rekon_jasa ?? 0);
            // SP, Tambah, Kurang hanya dihitung jika item tidak di-drop
            if (!$item->is_dropped) {
                $grandTotalSp += (float) ($item->sp_material ?? 0) + (float) ($item->sp_jasa ?? 0);
                $grandTotalTambah += (float) ($item->tambah_material ?? 0) + (float) ($item->tambah_jasa ?? 0);
                $grandTotalKurang += (float) ($item->kurang_material ?? 0) + (float) ($item->kurang_jasa ?? 0);
            }
        }
        $ppnValue = round($grandTotalRekon * $ppnPercent / 100);
        $totalWithPpn = $grandTotalRekon + $ppnValue;
        $this->totalRekon = $grandTotalRekon;
        $this->totalWithPpn = $totalWithPpn;
        $this->terbilangRekon = $this->terbilang($grandTotalRekon) . ' Rupiah';
        $this->terbilangWithPpn = $this->terbilang($totalWithPpn) . ' Rupiah';
        // Ambil lokasi yang di-drop
        $dropTextVariables = [];
        $uniqueDroppedLocations = $droppedData->pluck('id')->unique()->values();
        for ($i = 0; $i < 20; $i++) {
            $dropTextVariables["drop_" . ($i + 1)] = isset($uniqueDroppedLocations[$i]) ? 'Drop' : '';
        }
        return array_merge([
            'total_rekon' => $grandTotalRekon,
            'ppn_value' => $ppnValue,
            'total_with_ppn' => $totalWithPpn,
            'total_rekon_formatted' => $this->formatNumber($grandTotalRekon),
            'ppn_value_formatted' => $this->formatNumber($ppnValue),
            'total_with_ppn_formatted' => $this->formatNumber($totalWithPpn),
            'total_rekon_rupiah' => $this->formatRupiah($grandTotalRekon),
            'ppn_value_rupiah' => $this->formatRupiah($ppnValue),
            'total_with_ppn_rupiah' => $this->formatRupiah($totalWithPpn),
            'terbilang_rekon' => $this->terbilangRekon,
            'terbilang_with_ppn' => $this->terbilangWithPpn,
            'grand_total_sp' => $grandTotalSp,
            'grand_total_rekon' => $grandTotalRekon,
            'grand_total_tambah' => $grandTotalTambah,
            'grand_total_kurang' => $grandTotalKurang,
            'grand_total_sp_formatted' => $this->formatNumber($grandTotalSp),
            'grand_total_rekon_formatted' => $this->formatNumber($grandTotalRekon),
            'grand_total_tambah_formatted' => $this->formatNumber($grandTotalTambah),
            'grand_total_kurang_formatted' => $this->formatNumber($grandTotalKurang),
            'grand_total_sp_rupiah' => $this->formatRupiah($grandTotalSp),
            'grand_total_rekon_rupiah' => $this->formatRupiah($grandTotalRekon),
            'grand_total_tambah_rupiah' => $this->formatRupiah($grandTotalTambah),
            'grand_total_kurang_rupiah' => $this->formatRupiah($grandTotalKurang),
            'total_count' => $data->count(),
            'active_count' => $data->where('is_dropped', false)->count(),
            'dropped_count' => $droppedData->count(),
            'ppn_percent' => $ppnPercent,
        ], $dropTextVariables);
    }
    private function getEmptyAdditionalTemplateVariables($ppnPercent)
    {
        return [
            'total_rekon' => 0,
            'ppn_value' => 0,
            'total_with_ppn' => 0,
            'total_rekon_formatted' => '0',
            'ppn_value_formatted' => '0',
            'total_with_ppn_formatted' => '0',
            'total_rekon_rupiah' => 'Rp. 0',
            'ppn_value_rupiah' => 'Rp. 0',
            'total_with_ppn_rupiah' => 'Rp. 0',
            'terbilang_rekon' => 'Nol Rupiah',
            'terbilang_with_ppn' => 'Nol Rupiah',
            'grand_total_sp' => 0,
            'grand_total_rekon' => 0,
            'grand_total_tambah' => 0,
            'grand_total_kurang' => 0,
            'grand_total_sp_formatted' => '0',
            'grand_total_rekon_formatted' => '0',
            'grand_total_tambah_formatted' => '0',
            'grand_total_kurang_formatted' => '0',
            'grand_total_sp_rupiah' => 'Rp. 0',
            'grand_total_rekon_rupiah' => 'Rp. 0',
            'grand_total_tambah_rupiah' => 'Rp. 0',
            'grand_total_kurang_rupiah' => 'Rp. 0',
            'drop_1' => '0',
            'drop_2' => '0',
            'drop_3' => '0',
            'drop_4' => '0',
            'drop_1_rupiah' => 'Rp. 0',
            'drop_2_rupiah' => 'Rp. 0',
            'drop_3_rupiah' => 'Rp. 0',
            'drop_4_rupiah' => 'Rp. 0',
            'drop_1_raw' => 0,
            'drop_2_raw' => 0,
            'drop_3_raw' => 0,
            'drop_4_raw' => 0,
            'total_count' => 0,
            'active_count' => 0,
            'dropped_count' => 0,
            'ppn_percent' => $ppnPercent,
        ];
    }
    private function terbilang($angka)
    {
        $angka = abs((int)$angka);
        $baca = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        if ($angka < 12) {
            return $baca[$angka];
        } elseif ($angka < 20) {
            return $baca[$angka - 10] . " Belas";
        } elseif ($angka < 100) {
            return $this->terbilang(floor($angka / 10)) . " Puluh " . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return "Seratus " . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang(floor($angka / 100)) . " Ratus " . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return "Seribu " . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang(floor($angka / 1000)) . " Ribu " . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return $this->terbilang(floor($angka / 1000000)) . " Juta " . $this->terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            return $this->terbilang(floor($angka / 1000000000)) . " Miliar " . $this->terbilang($angka % 1000000000);
        }
        return "Angka terlalu besar";
    }
    private function formatRupiah($angka)
    {
        $angka = (float) $angka;
        return 'Rp. ' . number_format($angka, 0, ',', '.');
    }
    private function formatNumber($angka)
    {
        $angka = (float) $angka;
        return number_format($angka, 0, ',', '.');
    }
    public function dropBoq($id)
    {
        $boq = BoqLine::findOrFail($id);
        $boq->sp_material = 0;
        $boq->sp_jasa = 0;
        $boq->tambah_material = 0;
        $boq->tambah_jasa = 0;
        $boq->kurang_material = 0;
        $boq->kurang_jasa = 0;
        $boq->sp_total = 0;
        $boq->tambah_total = 0;
        $boq->kurang_total = 0;
        $boq->is_dropped = true;
        $boq->save();
        return redirect()->back()->with('success', 'Lokasi berhasil di-drop.');
    }
}