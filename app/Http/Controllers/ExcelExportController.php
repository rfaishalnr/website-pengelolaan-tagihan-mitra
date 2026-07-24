<?php

namespace App\Http\Controllers;

use App\Models\MitraPendaftaran;
use App\Models\BoqLine;
use App\Helpers\DateHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;
use App\Http\Controllers\BoqExportController;
use Illuminate\Http\Request;

class ExcelExportController extends Controller
{
    protected function extractTanggalFromNomor(?string $nomor): ?string
    {
        if (!$nomor) return null;
        // Array nama bulan bahasa Indonesia
        $bulanIndonesia = [
            'januari' => '01',
            'jan' => '01',
            'februari' => '02',
            'feb' => '02',
            'maret' => '03',
            'mar' => '03',
            'april' => '04',
            'apr' => '04',
            'mei' => '05',
            'juni' => '06',
            'jun' => '06',
            'juli' => '07',
            'jul' => '07',
            'agustus' => '08',
            'agu' => '08',
            'september' => '09',
            'sep' => '09',
            'oktober' => '10',
            'okt' => '10',
            'november' => '11',
            'nov' => '11',
            'desember' => '12',
            'des' => '12'
        ];
        // Pattern 1: DD/MM/YYYY
        if (preg_match('/\b(\d{1,2})\/(\d{1,2})\/(\d{4})\b/', $nomor, $match)) {
            $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($match[2], 2, '0', STR_PAD_LEFT);
            $year = $match[3];
            $tanggal = \DateTime::createFromFormat('d/m/Y', "$day/$month/$year");
            return $tanggal ? $tanggal->format('Y-m-d') : null;
        }
        // Pattern 2: DD-MM-YYYY
        if (preg_match('/\b(\d{1,2})-(\d{1,2})-(\d{4})\b/', $nomor, $match)) {
            $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($match[2], 2, '0', STR_PAD_LEFT);
            $year = $match[3];
            $tanggal = \DateTime::createFromFormat('d-m-Y', "$day-$month-$year");
            return $tanggal ? $tanggal->format('Y-m-d') : null;
        }
        // Pattern 3: DD NamaBulan YYYY (contoh: 13 Juli 2025)
        $bulanPattern = implode('|', array_keys($bulanIndonesia));
        if (preg_match('/\b(\d{1,2})\s+(' . $bulanPattern . ')\s+(\d{4})\b/i', $nomor, $match)) {
            $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
            $bulan = strtolower($match[2]);
            $month = $bulanIndonesia[$bulan];
            $year = $match[3];
            $tanggal = \DateTime::createFromFormat('d/m/Y', "$day/$month/$year");
            return $tanggal ? $tanggal->format('Y-m-d') : null;
        }
        // Pattern 4: DD.MM.YYYY (bonus format)
        if (preg_match('/\b(\d{1,2})\.(\d{1,2})\.(\d{4})\b/', $nomor, $match)) {
            $day = str_pad($match[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($match[2], 2, '0', STR_PAD_LEFT);
            $year = $match[3];
            $tanggal = \DateTime::createFromFormat('d.m.Y', "$day.$month.$year");
            return $tanggal ? $tanggal->format('Y-m-d') : null;
        }
        return null;
    }
    private const DEFAULT_PPN_PERCENT = 11;
    private const MAX_BOQ_ROWS = 20;
    private const TEMPLATES = [
        'baut' => [
            'template' => 'template-baut.xlsx',
            'date_field' => 'tanggal_baut',
            'prefix' => 'baut'
        ],
        'balegal' => [
            'template' => 'template-balegal.xlsx',
            'date_field' => 'tanggal_ba_legal',
            'prefix' => 'legal'
        ],
        'barekon' => [
            'template' => 'template-barekon.xlsx',
            'date_field' => 'tanggal_ba_rekon',
            'prefix' => 'rekon'
        ],
        'bast' => [
            'template' => 'template-bast.xlsx',
            'date_field' => 'tanggal_bast',
            'prefix' => 'bast'
        ],
        'bastabd' => [
            'template' => 'template-bastabd.xlsx',
            'date_field' => 'tanggal_ba_abd',
            'prefix' => 'abd'
        ],
        'checklist' => [
            'template' => 'template-checklist.xlsx',
            'date_field' => 'tanggal_baut',
            'prefix' => 'baut'
        ],
        'material' => [
            'template' => 'template-pernyataanmaterial.xlsx',
            'date_field' => 'tanggal_baut',
            'prefix' => 'baut'
        ],
        'baqclulus' => [
            'template' => 'template-baqclulus.xlsx',
            'date_field' => 'tanggal_baut',
            'prefix' => 'baut'
        ],
        'pemotongantagihan' => [
            'template' => 'template-pemotongantagihan.xlsx',
            'date_field' => 'tanggal_baut',
            'prefix' => 'baut'
        ],
        // 'lampiranbarekontambahkurang' => [
        //     'template' => 'template-lampiranbarekontambahkurang.xlsx',
        //     'date_field' => 'tanggal_ba_rekon',
        //     'prefix' => 'rekon'
        // ],
        'resumebarekon' => [
            'template' => 'template-resumebarekon.xlsx',
            'date_field' => 'tanggal_ba_rekon',
            'prefix' => 'rekon'
        ],
    ];
    private function exportTemplate($id, $type, Request $request = null)
    {
        if (!isset(self::TEMPLATES[$type])) {
            throw new \InvalidArgumentException("Template type '{$type}' not found");
        }
        $config = self::TEMPLATES[$type];
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->{$config['date_field']});
        $droppedLocations = [];
        if ($request) {
            $droppedLocations = $request->input('dropped_locations', []);
            if (is_string($droppedLocations)) {
                $droppedLocations = json_decode($droppedLocations, true) ?? [];
            }
        }
        $replacements = array_merge(
            $this->getCommonReplacements($data, $tanggal, $config['prefix']),
            $this->getAllBoqVariables($id, self::DEFAULT_PPN_PERCENT, $droppedLocations)
        );
        $dropVars = array_filter($replacements, function ($key) {
            return strpos($key, 'drop_') === 0;
        }, ARRAY_FILTER_USE_KEY);
        return $this->loadAndReplace(
            resource_path("excel/{$config['template']}"),
            "Export-" . strtoupper($type) . "-{$id}",
            $replacements
        );
    }
    protected function loadAndReplace($templateFile, $exportName, $replacements)
    {
        $spreadsheet = IOFactory::load($templateFile);
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $this->processSheet($sheet, $replacements);
        }
        return $this->saveAndDownload($spreadsheet, $exportName);
    }
    private function processSheet($sheet, $replacements)
    {
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $val = $cell->getValue();
                if (is_string($val)) {
                    $val = $this->replacePlaceholders($val, $replacements);
                    $cell->setValue($val);
                }
            }
        }
    }
    private function replacePlaceholders($val, $replacements)
    {
        foreach ($replacements as $key => $value) {
            if (str_contains($val, '${' . $key . '}')) {
                $replaceVal = $this->formatValue($value);
                $val = str_replace('${' . $key . '}', $replaceVal, $val);
            }
        }
        if (preg_match_all('/\$\{(.+?)\}/', $val, $matches)) {
            foreach ($matches[0] as $placeholder) {
                $val = str_replace($placeholder, '-', $val);
            }
        }
        return $val;
    }
    private function formatValue($value)
    {
        return ($value === null || $value === '' || $value === 0 || $value === '0') ? '-' : $value;
    }
    private function saveAndDownload($spreadsheet, $exportName)
    {
        $exportPath = storage_path("app/public/{$exportName}.xlsx");
        (new Xlsx($spreadsheet))->save($exportPath);
        return response()->download($exportPath)->deleteFileAfterSend(true);
    }
    protected function getCommonReplacements($data, $tanggal = [], $prefix = '')
    {
        $get = fn($key) => $data->{$key} ?? '-';
        return array_merge(
            $this->getMitraReplacements($get),
            $this->getPejabatReplacements($get),
            $this->getSuratReplacements($get),
            $this->getTanggalReplacements($get, $tanggal, $prefix)
        );
    }
    private function getMitraReplacements($get)
    {
        $amd1 = $get('amd_khs_mitra_1');
        $amd2 = $get('amd_khs_mitra_2');
        $amd3 = $get('amd_khs_mitra_3');
        $amd4 = $get('amd_khs_mitra_4');
        $amd5 = $get('amd_khs_mitra_5');
        $teksAmandemenParts = [];
        if ($amd1 !== '-') {
            $teksAmandemenParts[] = "Amandemen Perjanjian Kerjasama Pertama Nomor : {$amd1}";
        }
        if ($amd2 !== '-') {
            $teksAmandemenParts[] = "Amandemen Perjanjian Kerjasama Kedua Nomor : {$amd2}";
        }
        if ($amd3 !== '-') {
            $teksAmandemenParts[] = "Amandemen Perjanjian Kerjasama Ketiga Nomor : {$amd3}";
        }
        if ($amd4 !== '-') {
            $teksAmandemenParts[] = "Amandemen Perjanjian Kerjasama Keempat Nomor : {$amd4}";
        }
        if ($amd5 !== '-') {
            $teksAmandemenParts[] = "Amandemen Perjanjian Kerjasama Kelima Nomor : {$amd5}";
        }
        $teksAmandemen = '';
        if (!empty($teksAmandemenParts)) {
            $teksAmandemen = ', ' . implode(', ', $teksAmandemenParts);
        }
        $nomerSp = $get('nomer_sp_mitra');
        $tanggalSp = $this->extractTanggalFromNomor($nomerSp);
        $spMitraLengkap = $this->formatSpMitraLengkap($nomerSp, $tanggalSp);
        $spMitraDateReplacements = $this->getSpMitraDateReplacements($tanggalSp);
        $baseReplacements = [
            'nama_mitra' => $get('nama_mitra'),
            'no_khs_mitra' => $get('no_khs_mitra'),
            'amd_khs_mitra_1' => $amd1,
            'amd_khs_mitra_2' => $amd2,
            'amd_khs_mitra_3' => $amd3,
            'amd_khs_mitra_4' => $amd4,
            'amd_khs_mitra_5' => $amd5,
            'nomer_sp_mitra' => $nomerSp,
            'tanggal_sp_mitra' => $tanggalSp,
            'sp_mitra_lengkap' => $spMitraLengkap,
            'amd_sp' => $get('amd_sp'),
            'nama_pekerjaan' => $get('nama_pekerjaan'),
            'regional' => $get('regional'),
            'area' => $get('area'),
            'idp' => $get('idp'),
            'alamat_kantor' => $get('alamat_kantor'),
            'toc' => $get('toc'),
            'direktur_mitra' => $get('direktur_mitra'),
            'jabatan_mitra' => $get('jabatan_mitra'),
            'teks_amandemen_kalimat' => $teksAmandemen,
        ];
        return array_merge($baseReplacements, $spMitraDateReplacements);
    }
    private function formatSpMitraLengkap($nomer, $tanggal)
    {
        if ($nomer === '-' || $nomer === null || $nomer === '') {
            return '-';
        }
        if ($tanggal === '-' || $tanggal === null || $tanggal === '') {
            return $nomer;
        }
        try {
            $tanggalFormatted = DateHelper::formatTanggalLengkap($tanggal);
            $tanggalSlash = is_array($tanggalFormatted) ? ($tanggalFormatted['format_tanggal_slash'] ?? '') : '';
            if ($tanggalSlash) {
                return $nomer . ' tanggal ' . $tanggalSlash;
            }
        } catch (\Exception $e) {
            return $nomer;
        }
        return $nomer;
    }

    private function getSpMitraDateReplacements($tanggal)
    {
        $result = [];
        // Jika tanggal kosong, isi semua format dengan '-'
        if ($tanggal === '-' || $tanggal === null || $tanggal === '') {
            foreach ($this->getDateFormats() as $format) {
                $result["sp_mitra_{$format}"] = '-';
            }
            return $result;
        }
        try {
            $tanggalFormatted = DateHelper::formatTanggalLengkap($tanggal);
            foreach ($this->getDateFormats() as $format) {
                $result["sp_mitra_{$format}"] = $tanggalFormatted[$format] ?? '-';
            }
        } catch (\Exception $e) {
            // Jika error, isi semua format dengan '-'
            foreach ($this->getDateFormats() as $format) {
                $result["sp_mitra_{$format}"] = '-';
            }
        }
        return $result;
    }
    private function getPejabatReplacements($get)
    {
        return [
            'nama_pejabat_ta' => $get('nama_pejabat_ta'),
            'posisi_pejabat_ta' => $get('posisi_pejabat_ta'),
            'nik_pejabat' => $get('nik_pejabat'),
            'nama_mgr_area' => $get('nama_mgr_area'),
            'jabatan_mgr_area' => $get('jabatan_mgr_area'),
            'waspang' => $get('waspang'),
            'jabatan_waspang' => $get('jabatan_waspang'),
            'nik_waspang' => $get('nik_waspang'),
            'waspang_mitra' => $get('waspang_mitra'),
            'jabatan_waspang_mitra' => $get('jabatan_waspang_mitra'),
            'nik_waspang_mitra' => $get('nik_waspang_mitra'),
            'periode_waspang_mitra' => $get('periode_waspang_mitra'),
        ];
    }
    private function getSuratReplacements($get)
    {
        return [
            'no_baut' => $get('no_baut'),
            'no_ba_rekon' => $get('no_ba_rekon'),
            'no_ba_abd' => $get('no_ba_abd'),
            'no_ba_legal' => $get('no_ba_legal'),
        ];

        if (!is_array($tanggal)) {
            $tanggal = [];
        }
    }

    private function getTanggalReplacements($get, $tanggal, $prefix)
    {
        $baseReplacements = [
            'tanggal_baut' => $get('tanggal_baut'),
            'tanggal_ba_rekon' => $get('tanggal_ba_rekon'),
            'tanggal_ba_abd' => $get('tanggal_ba_abd'),
            'tanggal_ba_legal' => $get('tanggal_ba_legal'),
            'tanggal_bast' => $get('tanggal_bast'),
        ];
        // Generate prefixed replacements for current template
        $prefixedReplacements = [];
        foreach ($this->getDateFormats() as $format) {
            $key = $format . '_' . $prefix;
            $prefixedReplacements[$key] = $tanggal[$format] ?? '-';
        }
        // Generate slash formats for all types
        $slashFormats = [];
        foreach (['baut', 'rekon', 'legal', 'abd', 'bast'] as $type) {
            $slashFormats["format_tanggal_slash_{$type}"] =
                $prefix === $type ? ($tanggal['format_tanggal_slash'] ?? '-') : '-';
        }
        return array_merge($baseReplacements, $prefixedReplacements, $slashFormats);
    }
    private function getDateFormats()
    {
        return [
            'tanggal',
            'tanggal_terbilang',
            'hari',
            'bulan',
            'tahun',
            'tahun_terbilang',
            'tanggal_lengkap',
            'format_tanggal_slash',
            'tanggal_angka_terbilang',
            'bulan_angka_terbilang',
            'tahun_angka_terbilang',
            'tanggal_lengkap_angka_terbilang'
        ];
    }
    protected function getBoqReplacements($mitraId, $ppnPercent = self::DEFAULT_PPN_PERCENT, $droppedLocations = [])
    {
        $boqs = BoqLine::where('mitra_pendaftaran_id', $mitraId)->get();
        $result = [];
        $droppedLookup = [];
        foreach ($droppedLocations as $dropped) {
            $key = $dropped['index'] . '_' . $dropped['nama_lokasi'];
            $droppedLookup[$key] = $dropped;
        }
        foreach ($boqs as $i => $boq) {
            $dropKey = $i . '_' . $boq->nama_lokasi;
            $isDropped = isset($droppedLookup[$dropKey]);
            $result = array_merge($result, $this->processBoqLine($boq, $i + 1, $isDropped));
        }
        $result = array_merge($result, $this->calculateGrandTotals($boqs, $ppnPercent, $droppedLocations));
        $result = array_merge($result, $this->generateDropVariables($droppedLocations));
        $this->fillEmptyBoqRows($result, count($boqs));
        return $result;
    }
    private function processBoqLine($boq, $row, $isDropped = false)
    {
        $fields = ['sp', 'rekon', 'tambah', 'kurang'];
        $components = ['material', 'jasa'];
        $result = [
            "no_{$row}" => $row,
            "nama_lokasi_{$row}" => $boq->nama_lokasi ?? '-',
            "sto_{$row}" => $boq->sto ?? '-',
            "id_project_{$row}" => $boq->id_project ?? '-'

        ];
        foreach ($fields as $field) {
            foreach ($components as $component) {
                $key = "{$field}_{$component}";
                if ($isDropped && $field !== 'rekon') {
                    $result["{$key}_{$row}"] = 0;
                } else {
                    $result["{$key}_{$row}"] = $boq->{$key} ?? 0;
                }
            }
            if ($isDropped && $field !== 'rekon') {
                $result["{$field}_total_{$row}"] = 0;
            } else {
                $material = $boq->{"{$field}_material"} ?? 0;
                $jasa = $boq->{"{$field}_jasa"} ?? 0;
                $result["{$field}_total_{$row}"] = $material + $jasa;
            }
        }
        return $result;
    }
    private function generateDropVariables($droppedLocations)
    {
        $result = [];
        $allDrops = [];
        for ($i = 1; $i <= self::MAX_BOQ_ROWS; $i++) {
            $result["drop_{$i}"] = '-';
        }
        foreach ($droppedLocations as $dropped) {
            $rowIndex = $dropped['index'] + 1;
            if ($rowIndex <= self::MAX_BOQ_ROWS) {
                $result["drop_{$rowIndex}"] = 'DROP';
                $allDrops[] = $dropped['nama_lokasi'];
            }
        }
        $result["drop"] = count($allDrops) > 0 ? implode(', ', $allDrops) : '-';
        return $result;
    }
    private function calculateGrandTotals($boqs, $ppnPercent, $droppedLocations = [])
    {
        $fields = ['sp', 'rekon', 'tambah', 'kurang'];
        $components = ['material', 'jasa'];
        $result = [];
        $droppedLookup = [];
        foreach ($droppedLocations as $dropped) {
            $key = $dropped['index'] . '_' . $dropped['nama_lokasi'];
            $droppedLookup[$key] = true;
        }
        foreach ($fields as $field) {
            foreach ($components as $component) {
                $key = "grand_total_{$field}_{$component}";
                $total = 0;
                foreach ($boqs as $index => $boq) {
                    $dropKey = $index . '_' . $boq->nama_lokasi;
                    $isDropped = isset($droppedLookup[$dropKey]);
                    if (!$isDropped || $field === 'rekon') {
                        $total += $boq->{"{$field}_{$component}"} ?? 0;
                    }
                }
                $result[$key] = $total;
            }
            $totalKey = "grand_total_{$field}_total";
            $result[$totalKey] = $result["grand_total_{$field}_material"] + $result["grand_total_{$field}_jasa"];
        }
        $rekonTotal = $result['grand_total_rekon_total'];
        $totalWithPpn = $rekonTotal * (1 + $ppnPercent / 100);
        $result['grand_total_rekon_formatted'] = 'Rp. ' . number_format($rekonTotal, 0, ',', '.');
        $result['grand_total_rekon_with_ppn_formatted'] = 'Rp. ' . number_format($totalWithPpn, 0, ',', '.');
        $result['terbilang_rekon'] = ucwords(DateHelper::terbilang($rekonTotal)) . ' Rupiah';
        $result['terbilang_with_ppn'] = ucwords(DateHelper::terbilang($totalWithPpn)) . ' Rupiah';
        return $result;
    }
    private function fillEmptyBoqRows(&$result, $actualCount)
    {
        $fields = [
            'nama_lokasi',
            'sto',
            'id_project',
            'sp_material',
            'sp_jasa',
            'sp_total',
            'rekon_material',
            'rekon_jasa',
            'rekon_total',
            'tambah_material',
            'tambah_jasa',
            'tambah_total',
            'kurang_material',
            'kurang_jasa',
            'kurang_total'
        ];
        for ($i = $actualCount + 1; $i <= self::MAX_BOQ_ROWS; $i++) {
            $result["no_{$i}"] = $i;
            foreach ($fields as $field) {
                $result["{$field}_{$i}"] = '-';
            }
        }
    }
    protected function getAllBoqVariables($mitraId, $ppnPercent = self::DEFAULT_PPN_PERCENT, $droppedLocations = [])
    {
        $boqExport = new BoqExportController();
        $additionalVars = $boqExport->getAdditionalTemplateVariables($mitraId, $ppnPercent);
        $boqReplacements = $this->getBoqReplacements($mitraId, $ppnPercent, $droppedLocations);
        return array_merge($boqReplacements, $additionalVars);
    }
    // Export methods remain the same...
    public function exportBaut($id, Request $request = null)
    {
        if ($request && $request->isMethod('post')) {
            return $this->exportTemplate($id, 'baut', $request);
        }
        return $this->exportTemplate($id, 'baut', $request);
    }
    public function exportBalegal($id, Request $request = null)
    {
        if ($request && $request->isMethod('post')) {
            return $this->exportTemplate($id, 'balegal', $request);
        }
        return $this->exportTemplate($id, 'balegal', $request);
    }
    public function exportBarekon($id, Request $request = null)
    {
        if ($request && $request->isMethod('post')) {
            return $this->exportTemplate($id, 'barekon', $request);
        }
        return $this->exportTemplate($id, 'barekon', $request);
    }
    public function exportBast($id, Request $request = null)
    {
        return $this->exportTemplate($id, 'bast', $request);
    }
    public function exportBastabd($id, Request $request = null)
    {
        return $this->exportTemplate($id, 'bastabd', $request);
    }
    public function exportChecklist($id, Request $request = null)
    {
        return $this->exportTemplate($id, 'checklist', $request);
    }
    public function exportMaterial($id, Request $request = null)
    {
        return $this->exportTemplate($id, 'material', $request);
    }
    public function exportBaqclulus($id, Request $request = null)
    {
        return $this->exportTemplate($id, 'baqclulus', $request);
    }
    public function exportAbd($id, Request $request = null)
    {
        return $this->exportBastabd($id, $request);
    }
    public function exportPemotongantagihan($id, Request $request = null)
    {
        if ($request && $request->isMethod('post')) {
            return $this->exportTemplate($id, 'pemotongantagihan', $request);
        }
        return $this->exportTemplate($id, 'pemotongantagihan', $request);
    }
    // public function exportLampiranbarekontambahkurang($id, Request $request = null)
    // {
    //     if ($request && $request->isMethod('post')) {
    //         return $this->exportTemplate($id, 'lampiranbarekontambahkurang', $request);
    //     }
    //     return $this->exportTemplate($id, 'lampiranbarekontambahkurang', $request);
    // }
    public function exportResumeBarekon($id, Request $request = null)
    {
        if ($request && $request->isMethod('post')) {
            return $this->exportTemplate($id, 'resumebarekon', $request);
        }
        return $this->exportTemplate($id, 'resumebarekon', $request);
    }
    private function exportAndSave($id, $type, $savePath)
    {
        if (!isset(self::TEMPLATES[$type])) {
            throw new \InvalidArgumentException("Template type '{$type}' not found");
        }
        $config = self::TEMPLATES[$type];
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = \App\Helpers\DateHelper::formatTanggalLengkap($data->{$config['date_field']});
        $replacements = array_merge(
            $this->getCommonReplacements($data, $tanggal, $config['prefix']),
            $this->getAllBoqVariables($id, self::DEFAULT_PPN_PERCENT)
        );
        $spreadsheet = IOFactory::load(resource_path("excel/{$config['template']}"));
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $this->processSheet($sheet, $replacements);
        }
        (new Xlsx($spreadsheet))->save($savePath);
    }
    public function exportAllExcel($id)
    {
        $mitra = MitraPendaftaran::findOrFail($id);
        $zipFileName = 'All Excel Documents_' . str_replace('/', '_', $mitra->nomer_sp_mitra) . '.zip';
        $tempDir = storage_path('app/temp/excel_exports_' . $id . '_' . time());

        if (!file_exists($tempDir)) {
            // Gunakan 0777 sementara untuk memastikan tidak ada masalah permission
            mkdir($tempDir, 0777, true);
        }

        try {
            ini_set('memory_limit', '512M');

            $exports = [
                'baut' => 'Excel_BAUT',
                'bastabd' => 'Excel_BA_ABD',
                'balegal' => 'Excel_BA_Legal',
                'barekon' => 'Excel_BA_Rekon',
                'material' => 'Excel_Pernyataan_Material',
                'bast' => 'Excel_BAST',
                'checklist' => 'Excel_Checklist',
                'baqclulus' => 'Excel_BAQ_Clulus',
                'pemotongantagihan' => 'Excel_Pemotongan_Tagihan',
                // 'lampiranbarekontambahkurang' => 'Excel_Lampiran_BA_Rekon_Tambah_Kurang',
                'resumebarekon' => 'Excel_Resume_BA_Rekon',
            ];

            foreach ($exports as $type => $filenamePrefix) {
                $targetFileName = $filenamePrefix . '.xlsx';
                $savePath = $tempDir . '/' . $targetFileName;
                $this->exportAndSave($id, $type, $savePath);
            }

            $zipPath = $tempDir . '/' . $zipFileName;
            $zip = new \ZipArchive();

            // MODIFIKASI UTAMA DI SINI
            $zipStatus = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            if ($zipStatus === TRUE) {
                $files = glob($tempDir . '/*.xlsx');
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                // Jika sukses, download dan otomatis hapus file zip-nya
                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            } else {
                // JIKA GAGAL, PAKSA SISTEM MENGELUARKAN LAYAR ERROR
                throw new \Exception("Gagal membuat file ZIP. Kode Error ZipArchive: " . $zipStatus);
            }
        } catch (\Exception $e) {
            // Bersihkan sampah file excel dulu sebelum error ditampilkan
            $this->cleanupTempDirectory($tempDir);

            // Lempar error ke layar agar kita bisa melihatnya
            throw $e;
        }
    }
    private function cleanupTempDirectory($dir)
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                unlink($dir . '/' . $file);
            }
            rmdir($dir);
        }
    }
}
