<?php

namespace App\Http\Controllers;

use App\Models\MitraPendaftaran;
use App\Models\BoqLine;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MitraExportController extends Controller
{
    private const PPN_PERCENT = 11;
    private const EMPTY_ROWS_COUNT = 20;
    public function exportBarekonmaterial($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggalLegal = DateHelper::formatTanggalLengkap($data->tanggal_ba_legal);
        $tanggalRekon = DateHelper::formatTanggalLengkap($data->tanggal_ba_rekon);

        return $this->generateWordDocument(
            templatePath: 'word/template-barekonmaterial.docx',
            fileName: "BA-REKON-MATERIAL-{$data->id}.docx",
            data: $data,
            tanggal: $tanggalLegal,
            specificData: [
                'no_ba_legal' => $data->no_ba_legal ?? '',
                'tanggal_ba_legal' => $data->tanggal_ba_legal ?? '',
                'tanggal_ba_legal_formatted' => $this->formatTanggalLengkap($tanggalLegal),
                'tanggal_ba_rekon' => $data->tanggal_ba_rekon ?? '', // ⬅️ Tambahkan ini!
            ],
            datePrefix: 'legal'
        );
    }
    public function exportSuratpesananospfoamdpenutup($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_legal);
        return $this->generateWordDocument(
            templatePath: 'word/template-suratpesananospfoamdpenutup.docx',
            fileName: "Surat-Pesanan-OS-PFO-AMD-Penutup-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'no_ba_legal' => $data->no_ba_legal ?? '',
                'tanggal_ba_legal' => $data->tanggal_ba_legal ?? '',
                'tanggal_ba_legal_formatted' => $this->formatTanggalLengkap($tanggal),
            ],
            datePrefix: 'legal'
        );
    }
    public function exportPemotonganTagihan($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_legal);
        return $this->generateWordDocument(
            templatePath: 'word/template-pemotongantagihan.docx',
            fileName: "Pemotongan-Tagihan-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'no_ba_legal' => $data->no_ba_legal ?? '',
                'tanggal_ba_legal' => $data->tanggal_ba_legal ?? '',
                'tanggal_ba_legal_formatted' => $this->formatTanggalLengkap($tanggal),
            ],
            datePrefix: 'legal'
        );
    }
    public function exportBaut($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_baut);
        return $this->generateWordDocument(
            templatePath: 'word/template-baut.docx',
            fileName: "Surat-BAUT-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'no_baut' => $data->no_baut ?? '',
                'tanggal_baut' => $data->tanggal_baut ?? '',
                'tanggal_ba_rekon' => $data->tanggal_ba_rekon ?? '',
                'tanggal_ba_abd' => $data->tanggal_ba_abd ?? '',
            ],
            datePrefix: 'baut'
        );
    }
    public function exportBaAbd($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_abd);
        return $this->generateWordDocument(
            templatePath: 'word/template-bastabd.docx',
            fileName: "Surat-BA-ABD-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'no_ba_abd' => $data->no_ba_abd ?? '',
                'tanggal_ba_abd' => $data->tanggal_ba_abd ?? '',
                'no_ba_rekon' => $data->no_ba_rekon ?? '',
                'tanggal_lengkap_abd' => $this->formatTanggalLengkap($tanggal),
            ],
            datePrefix: 'abd'
        );
    }
    public function exportBaLegal($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_legal);
        return $this->generateWordDocument(
            templatePath: 'word/template-balegal.docx',
            fileName: "Surat-BA-LEGAL-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'no_ba_legal' => $data->no_ba_legal ?? '',
                'tanggal_ba_legal' => $data->tanggal_ba_legal ?? '',
                'tanggal_ba_legal_formatted' => $this->formatTanggalLengkap($tanggal),
            ],
            datePrefix: 'legal'
        );
    }
    public function exportBaRekon($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_rekon);
        $tanggalBaut = DateHelper::formatTanggalLengkap($data->tanggal_baut);
        $template = $this->createTemplate('word/template-barekon.docx', $data);
        $this->safeSetValue($template, 'no_ba_rekon', $data->no_ba_rekon ?? '');
        $this->safeSetValue($template, 'tanggal_ba_rekon', $data->tanggal_ba_rekon ?? '');
        $this->setDateTemplateData($template, $tanggal, 'rekon');
        $this->setDateTemplateData($template, $tanggalBaut, 'baut');
        return $this->saveAndDownloadTemplate($template, "Surat-BA-REKON-{$data->id}.docx");
    }
    public function exportPernyataanMaterial($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_legal);
        return $this->generateWordDocument(
            templatePath: 'word/template-pernyataanmaterial.docx',
            fileName: "Pernyataan-Material-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'tanggal_ba_legal' => $data->tanggal_ba_legal ?? '',
            ],
            datePrefix: 'legal'
        );
    }
    public function exportBast($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_baut);
        return $this->generateWordDocument(
            templatePath: 'word/template-bast.docx',
            fileName: "Surat-BAST-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'tanggal_baut' => $data->tanggal_baut ?? '',
            ],
            datePrefix: 'baut'
        );
    }
    public function exportBoqExcel($mitraId)
    {
        $mitra = MitraPendaftaran::findOrFail($mitraId);
        $lines = $mitra->boqLines;
        $spreadsheet = IOFactory::load(resource_path('excel/template-boq.xlsx'));
        $sheet = $spreadsheet->getActiveSheet();
        $this->populateExcelSheet($sheet, $lines);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'export-boq.xlsx';
        $temp = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp);
        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }
    private function generateWordDocument(
        string $templatePath,
        string $fileName,
        $data,
        $tanggal,
        array $specificData = [],
        string $datePrefix = ''
    ) {
        try {
            $template = $this->createTemplate($templatePath, $data);

            // Inject default values with null safety
            $defaults = [
                'no_ba_rekon' => $data->no_ba_rekon ?? '',
                'tanggal_ba_rekon' => $data->tanggal_ba_rekon ?? '',
            ];

            $specificData = array_merge($defaults, $specificData);

            foreach ($specificData as $key => $value) {
                $safeValue = $value ?? '';
                $this->safeSetValue($template, $key, $safeValue);
                $this->safeSetValue($template, "\${$key}", $safeValue);
            }

            if ($datePrefix && !empty($tanggal)) {
                $this->setDateTemplateData($template, $tanggal, $datePrefix);
            }

            return $this->saveAndDownloadTemplate($template, $fileName);
        } catch (\Exception $e) {
            error_log("Error in generateWordDocument: " . $e->getMessage());
            return back()->with('error', 'Gagal membuat dokumen: ' . $e->getMessage());
        }
    }

    public function exportTemplateRekonsiliasi($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_rekon);
        return $this->generateWordDocument(
            templatePath: 'word/template-rekonsiliasi.docx', // sesuaikan nama file template
            fileName: "Rekonsiliasi-{$data->id}.docx",
            data: $data,
            tanggal: $tanggal,
            specificData: [
                'no_ba_rekon' => $data->no_ba_rekon ?? '',
                'tanggal_ba_rekon' => $data->tanggal_ba_rekon ?? '',
                'mitra_name' => $data->nama_mitra ?? 'PT Mitra Sejahtera',
                'pekerjaan' => 'Pekerjaan Pembangunan FO Area Barat',
                'telkom_akses' => 'PT TELKOM AKSES',
                'tempat' => '', // bisa diisi sesuai kebutuhan
            ],
            datePrefix: 'rekon'
        );
    }
    public function exportRekonsiliasiFO($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        $tanggal = DateHelper::formatTanggalLengkap($data->tanggal_ba_rekon);
        $template = $this->createTemplate('word/template-rekonsiliasi.docx', $data);
        // Set variabel spesifik
        $this->safeSetValue($template, 'no_ba_rekon', $data->no_ba_rekon ?? '');
        $this->safeSetValue($template, 'tanggal_ba_rekon', $data->tanggal_ba_rekon ?? '');
        $this->safeSetValue($template, 'mitra_name', $data->nama_mitra ?? 'PT Mitra Sejahtera');
        $this->safeSetValue($template, 'pekerjaan', 'Pekerjaan Pembangunan FO Area Barat');
        $this->safeSetValue($template, 'telkom_akses', 'PT TELKOM AKSES');
        $this->safeSetValue($template, 'tempat', ''); // bisa diisi sesuai kebutuhan
        // Set variabel dengan format ${variabel}
        $this->safeSetValue($template, '${no_ba_rekon}', $data->no_ba_rekon ?? '');
        $this->safeSetValue($template, '${tanggal_ba_rekon}', $data->tanggal_ba_rekon ?? '');
        $this->safeSetValue($template, '${mitra_name}', $data->nama_mitra ?? 'PT Mitra Sejahtera');
        $this->safeSetValue($template, '${pekerjaan}', 'Pekerjaan Pembangunan FO Area Barat');
        $this->safeSetValue($template, '${telkom_akses}', 'PT TELKOM AKSES');
        $this->safeSetValue($template, '${tempat}', '');
        // Set data tanggal dengan prefix 'rekon'
        $this->setDateTemplateData($template, $tanggal, 'rekon');
        return $this->saveAndDownloadTemplate($template, "Rekonsiliasi-FO-{$data->id}.docx");
    }
    private function setDateTemplateData($template, $tanggal, string $suffix): void
    {
        // Add null check at the beginning
        if (empty($tanggal)) {
            // Set empty values for all date fields to prevent crashes
            $dateFields = [
                'hari',
                'tanggal',
                'bulan',
                'tahun',
                'tahun_terbilang',
                'format_tanggal_slash',
                'tanggal_terbilang'
            ];

            foreach ($dateFields as $field) {
                $this->safeSetValue($template, "{$field}_{$suffix}", '');
                $this->safeSetValue($template, "\${$field}_{$suffix}", '');
            }
            return;
        }

        if (is_string($tanggal)) {
            $tanggalArray = explode(' ', $tanggal);
            if (count($tanggalArray) >= 4) {
                $tanggal = [
                    'hari' => $tanggalArray[0] ?? '',
                    'tanggal' => $tanggalArray[1] ?? '',
                    'bulan' => $tanggalArray[2] ?? '',
                    'tahun' => $tanggalArray[3] ?? '',
                    'tanggal_terbilang' => $this->numberToWords($tanggalArray[1] ?? ''),
                    'tahun_terbilang' => $this->numberToWords($tanggalArray[3] ?? ''),
                ];
            } else {
                // If format is unexpected, set empty values
                $tanggal = [
                    'hari' => '',
                    'tanggal' => '',
                    'bulan' => '',
                    'tahun' => '',
                    'tanggal_terbilang' => '',
                    'tahun_terbilang' => ''
                ];
            }
        }

        if (!isset($tanggal['format_tanggal_slash']) && isset($tanggal['tanggal'], $tanggal['bulan'], $tanggal['tahun'])) {
            $tanggal['format_tanggal_slash'] = sprintf(
                '%s/%s/%s',
                str_pad($tanggal['tanggal'], 2, '0', STR_PAD_LEFT),
                $this->convertBulanToNumber($tanggal['bulan']),
                $tanggal['tahun']
            );
        }

        $terbilang = $tanggal['tanggal_terbilang'] ?? '';
        $dateFields = [
            'hari',
            'bulan',
            'tahun',
            'tahun_terbilang',
            'format_tanggal_slash',
        ];

        foreach ($dateFields as $field) {
            $value = $tanggal[$field] ?? '';
            $this->safeSetValue($template, "{$field}_{$suffix}", $value);
            $this->safeSetValue($template, "\${$field}_{$suffix}", $value);
        }

        $this->safeSetValue($template, "tanggal_{$suffix}", $tanggal['tanggal'] ?? '');
        $this->safeSetValue($template, "\$tanggal_{$suffix}", $tanggal['tanggal'] ?? '');
        $this->safeSetValue($template, "tanggal_terbilang_{$suffix}", $terbilang);
        $this->safeSetValue($template, "\$tanggal_terbilang_{$suffix}", $terbilang);
        $this->safeSetValue($template, "\${hari_rekon}", $tanggal['hari'] ?? '');
        $this->safeSetValue($template, "\${tanggal_terbilang_rekon}", $terbilang);
        $this->safeSetValue($template, "\${bulan_rekon}", $tanggal['bulan'] ?? '');
        $this->safeSetValue($template, "\${tahun_terbilang_rekon}", $tanggal['tahun_terbilang'] ?? '');
        $this->safeSetValue($template, "tanggal_ba_{$suffix}", $tanggal['tanggal'] ?? '');
        $this->safeSetValue($template, "\$tanggal_ba_{$suffix}", $tanggal['tanggal'] ?? '');
    }

    private function convertBulanToNumber(string $bulan): string
    {
        $bulanMap = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
        ];
        return $bulanMap[$bulan] ?? '00';
    }
    private function numberToWords($number)
    {
        $ones = [
            0 => 'nol',
            1 => 'satu',
            2 => 'dua',
            3 => 'tiga',
            4 => 'empat',
            5 => 'lima',
            6 => 'enam',
            7 => 'tujuh',
            8 => 'delapan',
            9 => 'sembilan',
            10 => 'sepuluh',
            11 => 'sebelas',
            12 => 'dua belas',
            13 => 'tiga belas',
            14 => 'empat belas',
            15 => 'lima belas',
            16 => 'enam belas',
            17 => 'tujuh belas',
            18 => 'delapan belas',
            19 => 'sembilan belas',
            20 => 'dua puluh',
            21 => 'dua puluh satu',
            22 => 'dua puluh dua',
            23 => 'dua puluh tiga',
            24 => 'dua puluh empat',
            25 => 'dua puluh lima',
            26 => 'dua puluh enam',
            27 => 'dua puluh tujuh',
            28 => 'dua puluh delapan',
            29 => 'dua puluh sembilan',
            30 => 'tiga puluh',
            31 => 'tiga puluh satu'
        ];
        $num = (int) $number;
        if ($num <= 31) {
            return $ones[$num] ?? (string) $number;
        }
        if ($num >= 2000 && $num <= 2100) {
            $ribu = floor($num / 1000);
            $sisa = $num % 1000;
            if ($ribu == 2) {
                $hasil = 'dua ribu';
            } else {
                $hasil = $ones[$ribu] . ' ribu';
            }
            if ($sisa > 0) {
                if ($sisa <= 31) {
                    $hasil .= ' ' . $ones[$sisa];
                } else {
                    $hasil .= ' ' . $sisa;
                }
            }
            return $hasil;
        }
        return (string) $number;
    }
    private function createTemplate(string $templatePath, $data): TemplateProcessor
    {
        $fullPath = resource_path($templatePath);
        if (!file_exists($fullPath)) {
            throw new \Exception("Template file not found: " . $fullPath);
        }

        $template = new TemplateProcessor($fullPath);

        // Add null safety for data
        if (empty($data) || !isset($data->id)) {
            throw new \Exception("Invalid data provided for template");
        }

        try {
            $droppedLocations = $this->getDroppedLocations($data->id);
            $boqReplacements = $this->getBoqReplacements($data->id, self::PPN_PERCENT, $droppedLocations);

            foreach ($boqReplacements as $key => $value) {
                // Add null safety for safeSetValue
                $safeValue = $value ?? '';
                $this->safeSetValue($template, $key, $safeValue);
                $this->safeSetValue($template, "\${$key}", $safeValue);
            }

            $this->setBasicTemplateData($template, $data);
            $this->setPersonnelTemplateData($template, $data);
        } catch (\Exception $e) {
            // Log error but don't crash - set empty values instead
            error_log("Error in createTemplate: " . $e->getMessage());
        }

        return $template;
    }
    private function saveAndDownloadTemplate(TemplateProcessor $template, string $fileName)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'word_');
        $template->saveAs($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }
    private function safesafeSetValue(TemplateProcessor $template, string $key, $value): void
    {
        if (is_scalar($value) || is_null($value)) {
            $safe = htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $this->safeSetValue($template, $key, $safe);
            $this->safeSetValue($template, "\${$key}", $safe);
        }
    }

    private function setBasicTemplateData(TemplateProcessor $template, $data): void
    {
        $basicFields = [
            'nama_mitra',
            'no_khs_mitra',
            'amd_khs_mitra_1',
            'amd_khs_mitra_2',
            'amd_khs_mitra_3',
            'amd_khs_mitra_4',
            'amd_khs_mitra_5',
            'nomer_sp_mitra',
            'amd_sp',
            'nama_pekerjaan',
            'regional',
            'area',
            'idp',
            'alamat_kantor',
            'toc'
        ];
        foreach ($basicFields as $field) {
            $value = $data->$field ?? '';
            $this->safeSetValue($template, $field, $value);
            $this->safeSetValue($template, "\${$field}", $value);
        }
    }
    private function setPersonnelTemplateData(TemplateProcessor $template, $data): void
    {
        $personnelFields = [
            'waspang',
            'jabatan_waspang',
            'direktur_mitra',
            'jabatan_mitra',
            'nama_mgr_area',
            'jabatan_mgr_area',
            'nama_pejabat_ta',
            'posisi_pejabat_ta',
            'nik_waspang',
            'nik_pejabat',
            'nik_direktur_mitra',
            'nik_mgr_area'
        ];
        foreach ($personnelFields as $field) {
            $value = $data->$field ?? '';
            $this->safeSetValue($template, $field, $value);
            $this->safeSetValue($template, "\${$field}", $value);
        }
    }
    private function populateExcelSheet($sheet, $lines): void
    {
        $row = 3;
        $no = 1;
        foreach ($lines as $line) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $line->nama_lokasi ?? '');
            $sheet->setCellValue("C{$row}", $line->sto ?? '');
            $sheet->setCellValue("D{$row}", $line->sp_material ?? 0);
            $sheet->setCellValue("E{$row}", $line->sp_jasa ?? 0);
            $sheet->setCellValue("F{$row}", ($line->sp_material ?? 0) + ($line->sp_jasa ?? 0));
            $row++;
        }
    }
    private function formatTanggalLengkap($tanggal): string
    {
        if (empty($tanggal)) return '';

        // Handle if tanggal is already a string
        if (is_string($tanggal)) {
            return $tanggal;
        }

        // Handle if tanggal is an array
        if (is_array($tanggal)) {
            return "{$tanggal['tanggal']} {$tanggal['bulan']} {$tanggal['tahun']}";
        }

        return '';
    }

    protected function getBoqReplacements($mitraId, $ppnPercent = self::PPN_PERCENT, $droppedLocations = []): array
    {
        $boqs = BoqLine::where('mitra_pendaftaran_id', $mitraId)->get();
        $result = [];
        // Create lookup for dropped locations
        $droppedLookup = [];
        foreach ($droppedLocations as $dropped) {
            $key = $dropped['index'] . '_' . $dropped['nama_lokasi'];
            $droppedLookup[$key] = $dropped;
        }
        // Process BOQ lines
        $this->processBoqLines($boqs, $result, $droppedLookup);
        $this->calculateGrandTotals($boqs, $result, $ppnPercent, $droppedLocations);
        $this->formatBoqValues($result);
        $this->generateTerbilangValues($result);
        $this->generateDropVariables($result, $droppedLocations);
        $this->fillEmptyRows($result, count($boqs));
        return $result;
    }
    private function getDroppedLocations($mitraId): array
    {
        $allBoqs = BoqLine::where('mitra_pendaftaran_id', $mitraId)
            ->orderBy('id') // Ensure consistent ordering
            ->get();
        $droppedLocations = [];
        foreach ($allBoqs as $index => $boq) {
            if ($boq->is_dropped) {
                $droppedLocations[] = [
                    'index' => $index,
                    'nama_lokasi' => $boq->nama_lokasi,
                    'sto' => $boq->sto ?? '',
                    'boq_id' => $boq->id
                ];
            }
        }
        return $droppedLocations;
    }
    private function processBoqLines($boqs, array &$result, array $droppedLookup = []): void
    {
        foreach ($boqs as $i => $boq) {
            $row = $i + 1;
            $dropKey = $i . '_' . $boq->nama_lokasi;
            $isDropped = isset($droppedLookup[$dropKey]);
            $this->setBoqLineData($result, $boq, $row, $isDropped);
        }
    }
    private function setBoqLineData(array &$result, $boq, int $row, bool $isDropped = false): void
    {
        $result["no_{$row}"] = $row;
        // Keep the original location name
        $result["nama_lokasi_{$row}"] = $boq->nama_lokasi ?? '-';
        // Set drop status per lokasi (ini yang diperbaiki)
        $result["drop_{$row}"] = $isDropped ? 'DROP' : '-';
        $result["sto_{$row}"] = $boq->sto ?? '-';
        $categories = ['sp', 'rekon', 'tambah', 'kurang'];
        $types = ['material', 'jasa'];
        foreach ($categories as $category) {
            foreach ($types as $type) {
                // If location is dropped, set non-rekon values to 0
                if ($isDropped && $category !== 'rekon') {
                    $value = 0;
                } else {
                    $value = $boq->{"{$category}_{$type}"} ?? 0;
                }
                $result["{$category}_{$type}_{$row}"] = $value;
            }
            // Calculate totals
            if ($isDropped && $category !== 'rekon') {
                $result["{$category}_total_{$row}"] = 0;
            } else {
                $material = $boq->{"{$category}_material"} ?? 0;
                $jasa = $boq->{"{$category}_jasa"} ?? 0;
                $result["{$category}_total_{$row}"] = $material + $jasa;
            }
        }
        // Format specific fields
        $result["rekon_material_formatted_{$row}"] = number_format($boq->rekon_material ?? 0, 0, ',', '.');
        $result["rekon_jasa_formatted_{$row}"] = number_format($boq->rekon_jasa ?? 0, 0, ',', '.');
        $result["rekon_total_formatted_{$row}"] = number_format(
            ($boq->rekon_material ?? 0) + ($boq->rekon_jasa ?? 0),
            0,
            ',',
            '.'
        );
        $result["harga_material_{$row}"] = $boq->harga_material ?
            number_format($boq->harga_material, 0, ',', '.') : '-';
        $result["harga_jasa_{$row}"] = $boq->harga_jasa ?
            number_format($boq->harga_jasa, 0, ',', '.') : '-';
        $result["jumlah_{$row}"] = number_format(
            ($boq->harga_material ?? 0) + ($boq->harga_jasa ?? 0),
            0,
            ',',
            '.'
        );
    }
    private function calculateGrandTotals($boqs, array &$result, float $ppnPercent, array $droppedLocations = []): void
    {
        // Create lookup for dropped locations
        $droppedLookup = [];
        foreach ($droppedLocations as $dropped) {
            $key = $dropped['index'] . '_' . $dropped['nama_lokasi'];
            $droppedLookup[$key] = $dropped;
        }
        $categories = ['sp', 'rekon', 'tambah', 'kurang'];
        $types = ['material', 'jasa'];
        foreach ($categories as $category) {
            foreach ($types as $type) {
                $total = 0;
                foreach ($boqs as $i => $boq) {
                    $dropKey = $i . '_' . $boq->nama_lokasi;
                    $isDropped = isset($droppedLookup[$dropKey]);
                    // If location is dropped, don't include non-rekon values in total
                    if ($isDropped && $category !== 'rekon') {
                        $value = 0;
                    } else {
                        $value = $boq->{"{$category}_{$type}"} ?? 0;
                    }
                    $total += $value;
                }
                $result["{$category}_{$type}"] = $total;
                $result["grand_total_{$category}_{$type}"] = $total;
            }
            $materialTotal = $result["{$category}_material"];
            $jasaTotal = $result["{$category}_jasa"];
            $grandTotal = $materialTotal + $jasaTotal;
            $result["{$category}_total"] = $grandTotal;
            $result["grand_total_{$category}_total"] = $grandTotal;
            $ppn = $grandTotal * ($ppnPercent / 100);
            $result["ppn_{$category}"] = $ppn;
            $result["grand_total_{$category}_with_ppn"] = $grandTotal + $ppn;
        }
    }
    private function generateDropVariables(array &$result, array $droppedLocations): void
    {
        // Global drop status - apakah ada lokasi yang di-drop
        if (!empty($droppedLocations)) {
            $result['has_dropped_locations'] = 'ADA DROP';
            $result['drop_count'] = count($droppedLocations);
        } else {
            $result['has_dropped_locations'] = 'TIDAK ADA DROP';
            $result['drop_count'] = 0;
        }
        // Individual drop status per lokasi
        // Ini sudah di-handle di setBoqLineData dengan $result["drop_{$row}"]
        // Untuk kompatibilitas dengan template yang mungkin menggunakan ${drop} secara global
        $result['drop'] = !empty($droppedLocations) ? 'ADA DROP' : '-';
        // Generate drop info untuk setiap lokasi yang di-drop
        foreach ($droppedLocations as $index => $dropped) {
            $row = $dropped['index'] + 1; // karena row dimulai dari 1
            $result["drop_info_{$row}"] = 'DROP: ' . $dropped['nama_lokasi'];
            $result["drop_reason_{$row}"] = 'Lokasi di-drop dari perhitungan';
        }
    }
    private function formatBoqValues(array &$result): void
    {
        $categories = ['sp', 'rekon', 'tambah', 'kurang'];
        $types = ['material', 'jasa', 'total'];
        foreach ($categories as $category) {
            foreach ($types as $type) {
                $value = $result["{$category}_{$type}"] ?? 0;
                $result["{$category}_{$type}_number"] = number_format($value, 0, ',', '.');
                $result["{$category}_{$type}_formatted"] = 'Rp. ' . number_format($value, 0, ',', '.');
                if (isset($result["ppn_{$category}"])) {
                    $ppnValue = $result["ppn_{$category}"];
                    $result["ppn_{$category}_number"] = number_format($ppnValue, 0, ',', '.');
                    $result["ppn_{$category}_formatted"] = 'Rp. ' . number_format($ppnValue, 0, ',', '.');
                }
                if (isset($result["grand_total_{$category}_with_ppn"])) {
                    $withPpnValue = $result["grand_total_{$category}_with_ppn"];
                    $result["grand_total_{$category}_with_ppn_number"] = number_format($withPpnValue, 0, ',', '.');
                    $result["grand_total_{$category}_with_ppn_formatted"] = 'Rp. ' . number_format($withPpnValue, 0, ',', '.');
                }
                if (isset($result["{$category}_{$type}_number"])) {
                    $result["grand_total_{$category}_{$type}_number"] = $result["{$category}_{$type}_number"];
                }
                if (isset($result["{$category}_{$type}_formatted"])) {
                    $result["grand_total_{$category}_{$type}_formatted"] = $result["{$category}_{$type}_formatted"];
                }
            }
        }
        $rekonMaterial = $result['rekon_material'] ?? 0;
        $rekonJasa = $result['rekon_jasa'] ?? 0;
        $rekonTotal = $rekonMaterial + $rekonJasa;
        $result['grand_total_rekon_material_number'] = number_format($rekonMaterial, 0, ',', '.');
        $result['grand_total_rekon_jasa_number'] = number_format($rekonJasa, 0, ',', '.');
        $result['grand_total_rekon_total_number'] = number_format($rekonTotal, 0, ',', '.');
        $result['grand_total_rekon_total_formatted'] = 'Rp. ' . number_format($rekonTotal, 0, ',', '.');
    }
    private function generateTerbilangValues(array &$result): void
    {
        $categories = ['sp', 'rekon', 'tambah', 'kurang'];
        foreach ($categories as $category) {
            $total = $result["grand_total_{$category}_total"] ?? 0;
            $result["terbilang_{$category}"] = $total > 0 ?
                $this->terbilang($total) . ' Rupiah' : 'Rupiah';
            $withPpn = $result["grand_total_{$category}_with_ppn"] ?? 0;
            $result["terbilang_{$category}_with_ppn"] = $withPpn > 0 ?
                $this->terbilang($withPpn) . ' Rupiah' : 'Rupiah';
            $result["terbilang_grand_total_{$category}_with_ppn"] = $result["terbilang_{$category}_with_ppn"];
            $ppn = $result["ppn_{$category}"] ?? 0;
            $result["terbilang_ppn_{$category}"] = $ppn > 0 ?
                $this->terbilang($ppn) . ' Rupiah' : 'Rupiah';
        }
    }
    private function fillEmptyRows(array &$result, int $existingCount): void
    {
        $fields = [
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
        for ($i = $existingCount + 1; $i <= self::EMPTY_ROWS_COUNT; $i++) {
            $result["no_{$i}"] = $i;
            $result["nama_lokasi_{$i}"] = '-';
            $result["sto_{$i}"] = '-';
            // Set drop status untuk baris kosong
            $result["drop_{$i}"] = '-';
            foreach ($fields as $field) {
                $result["{$field}_{$i}"] = '-';
            }
            $result["rekon_material_formatted_{$i}"] = '-';
            $result["rekon_jasa_formatted_{$i}"] = '-';
            $result["rekon_total_formatted_{$i}"] = '-';
            $result["harga_material_{$i}"] = '-';
            $result["harga_jasa_{$i}"] = '-';
            $result["jumlah_{$i}"] = '-';
        }
    }
    private function terbilang($angka): string
    {
        if (is_null($angka) || !is_numeric($angka)) {
            return 'Nol';
        }

        $angka = abs((int) $angka);
        $huruf = [
            '',
            'Satu',
            'Dua',
            'Tiga',
            'Empat',
            'Lima',
            'Enam',
            'Tujuh',
            'Delapan',
            'Sembilan',
            'Sepuluh',
            'Sebelas'
        ];

        if ($angka < 12) {
            return $huruf[$angka];
        } elseif ($angka < 20) {
            return $huruf[$angka - 10] . ' Belas';
        } elseif ($angka < 100) {
            return $this->terbilang(intval($angka / 10)) . ' Puluh ' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return 'Seratus ' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang(intval($angka / 100)) . ' Ratus ' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return 'Seribu ' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang(intval($angka / 1000)) . ' Ribu ' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return $this->terbilang(intval($angka / 1000000)) . ' Juta ' . $this->terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            return $this->terbilang(intval($angka / 1000000000)) . ' Miliar ' . $this->terbilang($angka % 1000000000);
        } elseif ($angka < 1000000000000000) {
            return $this->terbilang(intval($angka / 1000000000000)) . ' Triliun ' . $this->terbilang($angka % 1000000000000);
        }

        // Fix the trim issue at the end
        return 'Nol';
    }
    public function exportAllWord($id)
    {
        try {
            $mitra = MitraPendaftaran::findOrFail($id);

            // Add null safety for mitra properties
            $spMitra = $mitra->nomer_sp_mitra ?? 'Unknown';
            $namaPekerjaan = $mitra->nama_pekerjaan ?? 'Unknown';
            $namaPerusahaan = $mitra->nama_perusahaan ?? $mitra->nama_mitra ?? 'Unknown';

            $zipFileName = 'All Word Documents_' . str_replace(['/', ' '], '_', $spMitra) . '.zip';
            $tempDir = storage_path('app/temp/wordexports_' . $id . '_' . time());

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $documents = [];

            // Generate documents with error handling
            try {
                $documents['BAUT'] = $this->generateBautDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate BAUT document: " . $e->getMessage());
            }

            try {
                $documents['BA_ABD'] = $this->generateBaAbdDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate BA_ABD document: " . $e->getMessage());
            }

            try {
                $documents['BA_Legal'] = $this->generateBaLegalDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate BA_Legal document: " . $e->getMessage());
            }

            try {
                $documents['BA_Rekon'] = $this->generateBaRekonDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate BA_Rekon document: " . $e->getMessage());
            }

            try {
                $documents['Pernyataan_Material'] = $this->generatePernyataanMaterialDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate Pernyataan_Material document: " . $e->getMessage());
            }

            try {
                $documents['BAST'] = $this->generateBastDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate BAST document: " . $e->getMessage());
            }

            try {
                $documents['Pemotongan_Tagihan'] = $this->generatePemotonganTagihanDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate Pemotongan_Tagihan document: " . $e->getMessage());
            }

            try {
                $documents['Barekon_Material'] = $this->generatebarekonmaterialDocument($mitra);
            } catch (\Exception $e) {
                error_log("Failed to generate Barekon_Material document: " . $e->getMessage());
            }

            // Save documents that were successfully generated
            foreach ($documents as $name => $document) {
                if ($document) {
                    // $fileName = $name . '_' . str_replace(' ', '_', $namaPerusahaan) . '.docx';
                    $fileName = 'Word_' . $name . '.docx';
                    $filePath = $tempDir . '/' . $fileName;
                    $document->saveAs($filePath);
                }
            }

            // Create ZIP
            $zipPath = $tempDir . '/' . $zipFileName;
            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $files = glob($tempDir . '/*.docx');
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('Failed to create ZIP file');
            }
        } catch (\Exception $e) {
            if (isset($tempDir)) {
                $this->cleanupTempDirectory($tempDir);
            }

            error_log("Error in exportAllWord: " . $e->getMessage());
            return back()->with('error', 'Gagal membuat file ZIP: ' . $e->getMessage());
        }
    }

    private function safeSetValue(TemplateProcessor $template, string $key, $value): void
    {
        if (is_scalar($value) || is_null($value)) {
            $safe = htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $template->setValue($key, $safe);
            $template->setValue("\${$key}", $safe); // untuk dukung placeholder pakai ${key}
        }
    }

    private function generatebarekonmaterialDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_ba_legal);
        $template = $this->createTemplate('word/template-barekonmaterial.docx', $mitra);

        $this->safeSetValue($template, 'tanggal_ba_legal', $mitra->tanggal_ba_legal ?? '');
        $this->safeSetValue($template, 'no_ba_legal', $mitra->no_ba_legal ?? '');
        $this->safeSetValue($template, 'tanggal_ba_legal_formatted', $this->formatTanggalLengkap($tanggal));

        $this->setDateTemplateData($template, $tanggal, 'legal');
        return $template;
    }

    private function generatePemotonganTagihanDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_ba_legal);
        $template = $this->createTemplate('word/template-pemotongantagihan.docx', $mitra);

        $this->safeSetValue($template, 'tanggal_ba_legal', $mitra->tanggal_ba_legal ?? '');
        $this->safeSetValue($template, 'no_ba_legal', $mitra->no_ba_legal ?? '');
        $this->safeSetValue($template, 'tanggal_ba_legal_formatted', $this->formatTanggalLengkap($tanggal));

        $this->setDateTemplateData($template, $tanggal, 'legal');
        return $template;
    }

    private function generateBautDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_baut);
        $template = $this->createTemplate('word/template-baut.docx', $mitra);

        $this->safeSetValue($template, 'tanggal_baut', $mitra->tanggal_baut ?? '');
        $this->safeSetValue($template, 'no_baut', $mitra->no_baut ?? '');
        $this->safeSetValue($template, 'tanggal_ba_rekon', $mitra->tanggal_ba_rekon ?? '');
        $this->safeSetValue($template, 'tanggal_ba_abd', $mitra->tanggal_ba_abd ?? '');

        $this->setDateTemplateData($template, $tanggal, 'baut');
        return $template;
    }

    private function generateBaAbdDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_ba_abd);
        $template = $this->createTemplate('word/template-bastabd.docx', $mitra);

        $this->safeSetValue($template, 'tanggal_ba_abd', $mitra->tanggal_ba_abd ?? '');
        $this->safeSetValue($template, 'no_ba_abd', $mitra->no_ba_abd ?? '');
        $this->safeSetValue($template, 'no_ba_rekon', $mitra->no_ba_rekon ?? '');
        $this->safeSetValue($template, 'tanggal_lengkap_abd', $this->formatTanggalLengkap($tanggal));

        $this->setDateTemplateData($template, $tanggal, 'abd');
        return $template;
    }

    private function generateBaLegalDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_ba_legal);
        $template = $this->createTemplate('word/template-balegal.docx', $mitra);

        $this->safeSetValue($template, 'tanggal_ba_legal', $mitra->tanggal_ba_legal ?? '');
        $this->safeSetValue($template, 'no_ba_legal', $mitra->no_ba_legal ?? '');
        $this->safeSetValue($template, 'tanggal_ba_legal_formatted', $this->formatTanggalLengkap($tanggal));

        $this->setDateTemplateData($template, $tanggal, 'legal');
        return $template;
    }

    private function generateBaRekonDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_ba_rekon);
        $tanggalBaut = DateHelper::formatTanggalLengkap($mitra->tanggal_baut);

        $template = $this->createTemplate('word/template-barekon.docx', $mitra);

        $this->safeSetValue($template, 'no_ba_rekon', $mitra->no_ba_rekon ?? '');
        $this->safeSetValue($template, 'tanggal_ba_rekon', $mitra->tanggal_ba_rekon ?? '');

        $this->setDateTemplateData($template, $tanggal, 'rekon');
        $this->setDateTemplateData($template, $tanggalBaut, 'baut');

        return $template;
    }

    private function generatePernyataanMaterialDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_ba_legal);
        $template = $this->createTemplate('word/template-pernyataanmaterial.docx', $mitra);

        $this->safeSetValue($template, 'tanggal_ba_legal', $mitra->tanggal_ba_legal ?? '');

        $this->setDateTemplateData($template, $tanggal, 'legal');
        return $template;
    }

    private function generateBastDocument($mitra)
    {
        $tanggal = DateHelper::formatTanggalLengkap($mitra->tanggal_baut);
        $template = $this->createTemplate('word/template-bast.docx', $mitra);

        $this->safeSetValue($template, 'tanggal_baut', $mitra->tanggal_baut ?? '');

        $this->setDateTemplateData($template, $tanggal, 'baut');
        return $template;
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
