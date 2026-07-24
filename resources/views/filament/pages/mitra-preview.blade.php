@php
    use App\Helpers\DateHelper;
    use App\Models\MitraPendaftaran;
    // Check if data exists
    if (!$data) {
        $hasData = false;
        $mitras = MitraPendaftaran::all();
    } else {
        $hasData = true;
        $baut = DateHelper::formatTanggalLengkap($data->tanggal_baut);
        $rekon = DateHelper::formatTanggalLengkap($data->tanggal_ba_rekon);
        $abd = DateHelper::formatTanggalLengkap($data->tanggal_ba_abd);
        $legal = DateHelper::formatTanggalLengkap($data->tanggal_ba_legal);
        $mitras = MitraPendaftaran::all();
        $boqData = $data->boqLines ?? collect();
        // If boqLines relationship doesn't exist, try to load it
        if (!$boqData || $boqData->isEmpty()) {
            try {
                $boqData = $data->load('boqLines')->boqLines ?? collect();
            } catch (Exception $e) {
                $boqData = collect();
            }
        }
        $hasBoqData = $boqData && $boqData->count() > 0;
    }
@endphp

<!-- CDN CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@4.12.2/dist/jspreadsheet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<!-- CDN JS -->
<script src="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@4.12.2/dist/jspreadsheet.js"></script>
<x-filament::page>
    <div class="max-w-full mx-auto space-y-8 px-4">
{{-- Header Section --}}
<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        {{-- Info Mitra --}}
        <div>
            @if ($data)
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Preview Data
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    SP: {{ $data->nomer_sp_mitra }} | Pekerjaan: {{ $data->nama_pekerjaan }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $data->nomer_sp_mitra }} ({{ $data->nama_mitra }})
                </p>
            @else
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Tidak ada mitra dipilih
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Silakan pilih mitra dari dropdown di sebelah kanan
                </p>
            @endif
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                <strong>NOTE : Selalu Cek Ulang Data, Agar Tidak Ada Kesalahan !!!</strong>
            </p>
            {{-- <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                MOHON MAAF JIKA ADA BUG
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                SEDANG DALAM PERBAIKAN
            </p> --}}
        </div>
        {{-- Dropdown Pemilihan Mitra --}}
        <div class="min-w-0 flex-1 lg:max-w-sm">
            <label for="mitraSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                Pilih SP dan NAMA PEKERJAAN:
            </label>
            <div class="w-[420px] max-w-full">
                <select id="mitraSelect"
                    class="w-full rounded-lg shadow-sm text-ellipsis overflow-hidden whitespace-nowrap 
                           bg-white border-gray-300 text-gray-900 
                           dark:bg-gray-900 dark:border-white/10 dark:text-white dark:ring-white/20"
                    onchange="if(this.value){ window.location.href=this.value; }">
            
                    <option value="">-- Pilih Mitra --</option>
            
                    @foreach($mitras as $mitra)
                        <option
                            value="{{ route('mitra.preview.byid', ['record' => $mitra->id]) }}"
                            @selected(optional($data)->id == $mitra->id)
                            title="{{ $mitra->nomer_sp_mitra }} | {{ $mitra->nama_pekerjaan }}">
                            
                            {{ $mitra->nomer_sp_mitra }} | {{ \Illuminate\Support\Str::limit($mitra->nama_pekerjaan, 40) }}
                        </option>
                    @endforeach
            
                </select>
            </div>
        </div>
    </div>
</div>
        {{-- Data Sections Container --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
{{-- STEP 1: Perjanjian --}}
<div class="mt-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="{open:true}">
    <div class="flex items-center mb-4">
        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3">
            <span class="text-sm font-bold text-blue-600 dark:text-blue-400">1</span>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Perjanjian</h3><button type="button" @click="open=!open" class="ml-auto inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-sm"><span x-text="open ? 'Sembunyikan' : 'Tampilkan'"></span><span x-text="open ? '–' : '+'" class="text-lg leading-none"></span></button>
    </div>
    @if ($data)
<div class="space-y-3" x-show="open" x-transition>
            @php
                $perjanjianFields = [
                    'Nama Mitra' => $data->nama_mitra,
                    'No KHS Mitra' => $data->no_khs_mitra,
                    'AMD KHS Mitra 1' => $data->amd_khs_mitra_1,
                    'AMD KHS Mitra 2' => $data->amd_khs_mitra_2,
                    'AMD KHS Mitra 3' => $data->amd_khs_mitra_3,
                    'AMD KHS Mitra 4' => $data->amd_khs_mitra_4,
                    'AMD KHS Mitra 5' => $data->amd_khs_mitra_5,
                    'Nomor SP' => $data->nomer_sp_mitra,
                    'AMD SP' => $data->amd_sp,
                    'Nama Pekerjaan' => $data->nama_pekerjaan,
                    'Regional' => $data->regional,
                    'Area' => $data->area,
                    'IDP' => $data->idp,
                    'TOC' => \Carbon\Carbon::parse($data->toc)->translatedFormat('d F Y'),
                    'Alamat Kantor' => $data->alamat_kantor,
                ];
            @endphp
            @foreach ($perjanjianFields as $label => $value)
                <div
                    class="flex flex-col lg:flex-row lg:justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-b-0">
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 lg:mb-0 lg:w-1/3">
                        {{ $label }}:
                    </dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100 lg:text-right lg:w-2/3 break-words">
                        {{ $value ?: '-' }}
                    </dd>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Data mitra belum dipilih. Silakan pilih mitra terlebih dahulu dari dropdown di atas.
        </div>
    @endif
</div>
{{-- STEP 2: TTD --}}
<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center mb-4">
        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
            <span class="text-sm font-bold text-green-600 dark:text-green-400">2</span>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tanda Tangan</h3>
    </div>
    @if ($data)
        <div class="space-y-3">
            @php
                $ttdFields = [
                    'Nama GM' => $data->nama_pejabat_ta,
                    'Posisi GM' => $data->posisi_pejabat_ta,
                    'NIK GM' => $data->nik_pejabat,
                    'Manager Area' => $data->nama_mgr_area . ' (' . $data->jabatan_mgr_area . ')',
                    'Direktur Mitra' => $data->direktur_mitra . ' (' . $data->jabatan_mitra . ')',
                    'Waspang' => $data->waspang . ' (' . $data->jabatan_waspang . ') - NIK: ' . $data->nik_waspang,
                    'Waspang Mitra' => $data->waspang_mitra . ' (' . $data->jabatan_waspang_mitra . ') - NIK: ' . $data->nik_waspang_mitra,
                ];
            @endphp
            @foreach ($ttdFields as $label => $value)
                <div
                    class="flex flex-col lg:flex-row lg:justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-b-0">
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 lg:mb-0 lg:w-1/3">
                        {{ $label }}:
                    </dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100 lg:text-right lg:w-2/3 break-words">
                        {{ $value ?: '-' }}
                    </dd>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-600 dark:text-gray-400 italic">
            Data mitra belum dipilih. Tidak ada informasi tanda tangan yang ditampilkan.
        </div>
    @endif
</div>
{{-- STEP 3-6: Tanggal Dokumen - Now Full Width --}}
<div class="mt-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center mb-6">
        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3">
            <span class="text-sm font-bold text-purple-600 dark:text-purple-400">3-6</span>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Tanggal Dokumen</h3>
    </div>
    @if ($data)
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
            @php
                $items = [
                    [
                        'label' => 'BAUT',
                        'no' => $data->no_baut,
                        'tgl' => $baut,
                        'color' => 'blue',
                    ],
                    [
                        'label' => 'BA REKON',
                        'no' => $data->no_ba_rekon,
                        'tgl' => $rekon,
                        'color' => 'green',
                    ],
                    [
                        'label' => 'BA ABD',
                        'no' => $data->no_ba_abd,
                        'tgl' => $abd,
                        'color' => 'yellow',
                    ],
                    [
                        'label' => 'BA LEGAL',
                        'no' => null, // HILANGKAN NOMOR BA LEGAL
                        'tgl' => $legal,
                        'color' => 'red',
                    ],
                ];
            @endphp
            @foreach ($items as $item)
                <div class="border-2 border-{{ $item['color'] }}-200 dark:border-{{ $item['color'] }}-800 rounded-lg p-5 bg-{{ $item['color'] }}-50 dark:bg-{{ $item['color'] }}-900/20">
                    <h4 class="font-bold text-{{ $item['color'] }}-700 dark:text-{{ $item['color'] }}-300 mb-4 text-center text-lg">
                        {{ $item['label'] }}
                    </h4>
                    <div class="space-y-3 text-sm">
                        {{-- KONDISI: Tampilkan nomor hanya jika ada (tidak null) --}}
                        @if ($item['no'])
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <span class="font-medium text-gray-600 dark:text-gray-400 block mb-1">Nomor:</span>
                                <div class="text-gray-900 dark:text-gray-100 break-words">
                                    {{ $item['no'] }}
                                </div>
                            </div>
                        @endif
                        {{-- TANGGAL TETAP DITAMPILKAN --}}
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                            <span class="font-medium text-gray-600 dark:text-gray-400 block mb-2">Detail Tanggal:</span>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Tanggal:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $item['tgl']['tanggal'] ?? '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Hari:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $item['tgl']['hari'] ?? '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Bulan:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $item['tgl']['bulan'] ?? '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Tahun:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $item['tgl']['tahun'] ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- DETAIL TANGGAL TERBILANG TETAP DITAMPILKAN --}}
                        @if (!empty($item['tgl']['tanggal_terbilang']))
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <span class="font-medium text-gray-600 dark:text-gray-400 block mb-1">Tanggal Terbilang:</span>
                                <div class="text-gray-900 dark:text-gray-100 text-xs italic">
                                    {{ $item['tgl']['tanggal_terbilang'] }}
                                </div>
                            </div>
                        @endif
                        @if (!empty($item['tgl']['hari_terbilang']))
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <span class="font-medium text-gray-600 dark:text-gray-400 block mb-1">Hari Terbilang:</span>
                                <div class="text-gray-900 dark:text-gray-100 text-xs italic">
                                    {{ $item['tgl']['hari_terbilang'] }}
                                </div>
                            </div>
                        @endif
                        @if (!empty($item['tgl']['bulan_terbilang']))
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <span class="font-medium text-gray-600 dark:text-gray-400 block mb-1">Bulan Terbilang:</span>
                                <div class="text-gray-900 dark:text-gray-100 text-xs italic">
                                    {{ $item['tgl']['bulan_terbilang'] }}
                                </div>
                            </div>
                        @endif
                        @if (!empty($item['tgl']['tahun_terbilang']))
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                                <span class="font-medium text-gray-600 dark:text-gray-400 block mb-1">Tahun Terbilang:</span>
                                <div class="text-gray-900 dark:text-gray-100 text-xs italic">
                                    {{ $item['tgl']['tahun_terbilang'] }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-600 dark:text-gray-400 italic">
            Data mitra belum dipilih. Silakan pilih mitra terlebih dahulu untuk melihat informasi tanggal dokumen.
        </div>
    @endif
</div>
{{-- BOQ Table dengan Fixed Width --}}
@if ($hasBoqData)
    <div class="mt-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Data BOQ Mitra</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total {{ $boqData->count() }} item</p>
                </div>
            </div>
        </div>
        {{-- BOQ Table dengan Fixed Width dan Horizontal Scroll --}}
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="min-w-full" style="min-width: 1600px;">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="table-layout: fixed; width: 1800px;">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 60px;">
                                #
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 200px;">
                                Nama Lokasi
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 80px;">
                                STO
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 120px;">
                                ID Project
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider" style="width: 120px;">
                                SP Material
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider" style="width: 120px;">
                                SP Jasa
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider" style="width: 120px;">
                                Total SP
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 120px;">
                                Rekon Material
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 120px;">
                                Rekon Jasa
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 120px;">
                                Total Rekon
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider" style="width: 120px;">
                                Tambah Material
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider" style="width: 120px;">
                                Tambah Jasa
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider" style="width: 120px;">
                                Total Tambah
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 120px;">
                                Kurang Material
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 120px;">
                                Kurang Jasa
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 120px;">
                                Total Kurang
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $totalSpMaterial = 0;
                            $totalSpJasa = 0;
                            $totalSpTotal = 0;
                            $totalRekonMaterial = 0;
                            $totalRekonJasa = 0;
                            $totalRekonTotal = 0;
                            $totalTambahMaterial = 0;
                            $totalTambahJasa = 0;
                            $totalTambahTotal = 0;
                            $totalKurangMaterial = 0;
                            $totalKurangJasa = 0;
                            $totalKurangTotal = 0;
                        @endphp
                        @foreach ($boqData as $index => $row)
                            @php
                                // Calculate row totals
                                $spTotal = ($row->sp_material ?? 0) + ($row->sp_jasa ?? 0);
                                $rekonTotal = ($row->rekon_material ?? 0) + ($row->rekon_jasa ?? 0);
                                $tambahTotal = ($row->tambah_material ?? 0) + ($row->tambah_jasa ?? 0);
                                $kurangTotal = ($row->kurang_material ?? 0) + ($row->kurang_jasa ?? 0);
                                // Calculate column totals
                                $totalSpMaterial += $row->sp_material ?? 0;
                                $totalSpJasa += $row->sp_jasa ?? 0;
                                $totalSpTotal += $spTotal;
                                $totalRekonMaterial += $row->rekon_material ?? 0;
                                $totalRekonJasa += $row->rekon_jasa ?? 0;
                                $totalRekonTotal += $rekonTotal;
                                $totalTambahMaterial += $row->tambah_material ?? 0;
                                $totalTambahJasa += $row->tambah_jasa ?? 0;
                                $totalTambahTotal += $tambahTotal;
                                $totalKurangMaterial += $row->kurang_material ?? 0;
                                $totalKurangJasa += $row->kurang_jasa ?? 0;
                                $totalKurangTotal += $kurangTotal;
                            @endphp
                            <tr class="">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" style="width: 60px;">
                                    {{ $row->no ?? $index + 1 }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" style="width: 200px;">
                                    <div class="truncate" title="{{ $row->nama_lokasi }}">
                                        {{ $row->nama_lokasi }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" style="width: 80px;">
                                    {{ $row->sto }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" style="width: 120px;">
                                    <div class="truncate" title="{{ $row->id_project }}">
                                        {{ $row->id_project ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                    {{ number_format($row->sp_material ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                    {{ number_format($row->sp_jasa ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-blue-600 dark:text-blue-400 font-semibold font-mono" style="width: 120px;">
                                    {{ number_format($spTotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                    {{ number_format($row->rekon_material ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                    {{ number_format($row->rekon_jasa ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-blue-600 dark:text-blue-400 font-semibold font-mono" style="width: 120px;">
                                    {{ number_format($rekonTotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400 font-mono" style="width: 120px;">
                                    {{ number_format($row->tambah_material ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400 font-mono" style="width: 120px;">
                                    {{ number_format($row->tambah_jasa ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400 font-semibold font-mono" style="width: 120px;">
                                    {{ number_format($tambahTotal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-red-600 dark:text-red-400 font-mono" style="width: 120px;">
                                    {{ number_format($row->kurang_material ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-red-600 dark:text-red-400 font-mono" style="width: 120px;">
                                    {{ number_format($row->kurang_jasa ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-red-600 dark:text-red-400 font-semibold font-mono" style="width: 120px;">
                                    {{ number_format($kurangTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    {{-- Total Footer --}}
                    <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-gray-100">
                                GRAND TOTAL
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                {{ number_format($totalSpMaterial, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                {{ number_format($totalSpJasa, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-blue-600 dark:text-blue-400 font-mono" style="width: 120px;">
                                {{ number_format($totalSpTotal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                {{ number_format($totalRekonMaterial, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-gray-900 dark:text-gray-100 font-mono" style="width: 120px;">
                                {{ number_format($totalRekonJasa, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-blue-600 dark:text-blue-400 font-mono" style="width: 120px;">
                                {{ number_format($totalRekonTotal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-green-600 dark:text-green-400 font-mono" style="width: 120px;">
                                {{ number_format($totalTambahMaterial, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-green-600 dark:text-green-400 font-mono" style="width: 120px;">
                                {{ number_format($totalTambahJasa, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-green-600 dark:text-green-400 font-mono" style="width: 120px;">
                                {{ number_format($totalTambahTotal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-red-600 dark:text-red-400 font-mono" style="width: 120px;">
                                {{ number_format($totalKurangMaterial, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-red-600 dark:text-red-400 font-mono" style="width: 120px;">
                                {{ number_format($totalKurangJasa, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-sm text-right font-bold text-red-600 dark:text-red-400 font-mono" style="width: 120px;">
                                {{ number_format($totalKurangTotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        {{-- BOQ Summary Cards --}}
        <div class="mt-6 flex flex-col sm:flex-row gap-4">
            <div class="flex-1 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="text-sm font-medium text-blue-600 dark:text-blue-400">Total SP</div>
                <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                    {{ number_format($totalSpMaterial + $totalSpJasa, 0, ',', '.') }}
                </div>
                <div class="text-xs text-blue-700 dark:text-blue-300">Material + Jasa</div>
            </div>
            <div class="flex-1 bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <div class="text-sm font-medium text-purple-600 dark:text-purple-400">Total Rekon</div>
                <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                    {{ number_format($totalRekonMaterial + $totalRekonJasa, 0, ',', '.') }}
                </div>
                <div class="text-xs text-purple-700 dark:text-purple-300">Material + Jasa</div>
            </div>
            <div class="flex-1 bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="text-sm font-medium text-green-600 dark:text-green-400">Total Tambah</div>
                <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                    {{ number_format($totalTambahMaterial + $totalTambahJasa, 0, ',', '.') }}
                </div>
                <div class="text-xs text-green-700 dark:text-green-300">Material + Jasa</div>
            </div>
            <div class="flex-1 bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <div class="text-sm font-medium text-red-600 dark:text-red-400">Total Kurang</div>
                <div class="text-2xl font-bold text-red-900 dark:text-red-100">
                    {{ number_format($totalKurangMaterial + $totalKurangJasa, 0, ',', '.') }}
                </div>
                <div class="text-xs text-red-700 dark:text-red-300">Material + Jasa</div>
            </div>
        </div>
    </div>
            @else
            {{-- No BOQ Data Message --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 p-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-table text-2xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Data BOQ Tidak Tersedia</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">
                        Belum ada data BOQ yang tersimpan untuk mitra ini.
                    </p>
                </div>
            </div>
        @endif
{{-- Export Section --}}
@if(auth()->check() && auth()->user()->role !== 'admin')
<div class="mt-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Export Dokumen</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Pilih format dan jenis dokumen yang ingin diexport</p>
    </div>
    <div class="p-6">
        {{-- Export All Buttons --}}
        @if ($data)
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
            {{-- Tombol Export Word --}}
            <a href="{{ route('mitra.export.all.word', $data->id) }}" 
               class="flex-1 text-gray-800 dark:text-white font-normal py-2 px-4 flex items-center justify-center space-x-2">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.9 3.3C12.7 3.1 12.4 3 12 3H6C4.9 3 4 3.9 4 5v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8.5c0-.4-.2-.8-.4-1L12.9 3.3zM12 4.5L18.5 8H13c-.6 0-1-.4-1-1V4.5zM18 19H6V5h5v3c0 1.1.9 2 2 2h5v9zM9.5 11l-.8 2.9L8 11h-.8l-.7 2.9L5.7 11H5l1.2 4h.8l.7-2.7.7 2.7h.8L10.5 11H9.5z" />
                </svg>
                <span>Export All Word Documents</span>
            </a>
            {{-- Tombol Export Excel --}}
            <a href="{{ route('mitra.export.all.excel', $data->id) }}" 
               class="flex-1 text-gray-800 dark:text-white font-normal py-2 px-4 flex items-center justify-center space-x-2">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.9 3.3C12.7 3.1 12.4 3 12 3H6C4.9 3 4 3.9 4 5v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8.5c0-.4-.2-.8-.4-1L12.9 3.3zM12 4.5L18.5 8H13c-.6 0-1-.4-1-1V4.5zM18 19H6V5h5v3c0 1.1.9 2 2 2h5v9zM8.5 11L10 13l-1.5 2h1l1-1.3 1 1.3h1L10 13l1.5-2h-1l-1 1.3L8.5 11h-1z" />
                </svg>
                <span>Export All Excel Documents</span>
            </a>
        </div>
    @endif
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Word Export --}}
            <div class="flex-1 space-y-4">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-blue-600 dark:bg-blue-500 rounded-lg flex items-center justify-center">
                        <!-- Word Icon SVG -->
                        <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.9 3.3C12.7 3.1 12.4 3 12 3H6C4.9 3 4 3.9 4 5v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8.5c0-.4-.2-.8-.4-1L12.9 3.3zM12 4.5L18.5 8H13c-.6 0-1-.4-1-1V4.5zM18 19H6V5h5v3c0 1.1.9 2 2 2h5v9zM9.5 11l-.8 2.9L8 11h-.8l-.7 2.9L5.7 11H5l1.2 4h.8l.7-2.7.7 2.7h.8L10.5 11H9.5z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Microsoft Word</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">Format .docx</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @if ($data)
                        @foreach ([
                            'baut' => ['label' => 'Word BAUT', 'icon' => 'fas fa-file-word'],
                            'ba.abd' => ['label' => 'Word BA ABD', 'icon' => 'fas fa-file-word'],
                            'ba.legal' => ['label' => 'Word BA Legal', 'icon' => 'fas fa-file-word'],
                            'ba.rekon' => ['label' => 'Word BA Rekon', 'icon' => 'fas fa-file-word'],
                            'pernyataan.material' => ['label' => 'Word Pernyataan Material', 'icon' => 'fas fa-file-word'],
                            'bast' => ['label' => 'Word BAST', 'icon' => 'fas fa-file-word'],
                            'pemotongantagihan' => ['label' => 'Word Pemotongan Tagihan', 'icon' => 'fas fa-file-word'],
                            'barekonmaterial' => ['label' => 'Word BA Rekon Material', 'icon' => 'fas fa-file-word'],
                            // 'barekonospfo' => ['label' => 'Word BA Rekon OSPFO', 'icon' => 'fas fa-file-word'],
                        ] as $slug => $info)
                            <a href="{{ route('mitra.export.' . $slug, $data->id) }}"
                                class="btn-export btn-word group block p-3 rounded-lg text-center transition-all duration-200 hover:scale-105">
                                <div class="text-xl mb-2 text-gray-900 dark:text-white">
                                    <i class="{{ $info['icon'] }}"></i>
                                </div>
                                <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $info['label'] }}</div>
                            </a>
                        @endforeach
                    @else
                        <div class="text-sm text-gray-600 dark:text-gray-400 italic col-span-full">
                            Silakan pilih mitra terlebih dahulu untuk mengakses tombol export dokumen.
                        </div>
                    @endif
                </div>
            </div>
            {{-- Excel Export --}}
            <div class="flex-1 space-y-4">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-green-600 dark:bg-green-500 rounded-lg flex items-center justify-center">
                        <!-- Excel Icon SVG -->
                        <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.9 3.3C12.7 3.1 12.4 3 12 3H6C4.9 3 4 3.9 4 5v14c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8.5c0-.4-.2-.8-.4-1L12.9 3.3zM12 4.5L18.5 8H13c-.6 0-1-.4-1-1V4.5zM18 19H6V5h5v3c0 1.1.9 2 2 2h5v9zM8.5 11L10 13l-1.5 2h1l1-1.3 1 1.3h1L10 13l1.5-2h-1l-1 1.3L8.5 11h-1z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-green-900 dark:text-green-100">Microsoft Excel</h4>
                        <p class="text-sm text-green-700 dark:text-green-300">Format .xlsx</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @if ($data)
                        @foreach ([
                            'excel.baut' => ['label' => 'Excel BAUT', 'icon' => 'fas fa-file-excel'],
                            'excel.ba.abd' => ['label' => 'Excel BA ABD', 'icon' => 'fas fa-file-excel'],
                            'excel.ba.legal' => ['label' => 'Excel BA Legal', 'icon' => 'fas fa-file-excel'],
                            'excel.ba.rekon' => ['label' => 'Excel BA Rekon', 'icon' => 'fas fa-file-excel'],
                            'excel.pernyataan.material' => ['label' => 'Excel Pernyataan Material', 'icon' => 'fas fa-file-excel'],
                            'excel.bast' => ['label' => 'Excel BAST', 'icon' => 'fas fa-file-excel'],
                            'excel.checklist' => ['label' => 'Excel Checklist', 'icon' => 'fas fa-file-excel'],
                            'excel.baqclulus' => ['label' => 'Excel BA QC Lulus', 'icon' => 'fas fa-file-excel'],
                            'excel.pemotongantagihan' => ['label' => 'Excel BA Lampiran Pemotongan tagihan', 'icon' => 'fas fa-file-excel'],
                            // 'excel.lampiranbarekontambahkurang' => ['label' => 'Excel Lampiran BA Rekon (Tambah Kurang)', 'icon' => 'fas fa-file-excel'],
                            'excel.resumebarekon' => ['label' => 'Excel Resume BA Rekon', 'icon' => 'fas fa-file-excel'],
                        ] as $slug => $info)
                            <a href="{{ route('mitra.export.' . $slug, $data->id) }}"
                                class="btn-export btn-excel group block p-3 rounded-lg text-center transition-all duration-200 hover:scale-105">
                                <div class="text-xl mb-2 text-gray-900 dark:text-white">
                                    <i class="{{ $info['icon'] }}"></i>
                                </div>
                                <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $info['label'] }}</div>
                            </a>
                        @endforeach
                    @else
                        <div class="text-sm text-gray-600 dark:text-gray-400 italic col-span-full">
                            Silakan pilih mitra terlebih dahulu untuk mengakses export Excel dokumen.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
        {{-- Loading Overlay --}}
        <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="text-lg font-medium text-gray-900 dark:text-gray-100">Menyiapkan dokumen...</span>
                </div>
            </div>
        </div>
    </div>
    {{-- Scripts --}}
    <x-slot name="scripts">
        <script>
            // Mitra selector change handler
            document.getElementById('mitraSelect')?.addEventListener('change', function() {
                if (this.value) {
                    window.location.href = this.value;
                }
            });
            // Export button loading overlay
            document.querySelectorAll('.btn-export').forEach(button => {
                button.addEventListener('click', function(e) {
                    showLoading();
                    // Hide loading after 3 seconds
                    setTimeout(() => {
                        hideLoading();
                    }, 3000);
                });
            });
            function showLoading() {
                document.getElementById('loadingOverlay').classList.remove('hidden');
                document.getElementById('loadingOverlay').classList.add('flex');
            }
            function hideLoading() {
                document.getElementById('loadingOverlay').classList.add('hidden');
                document.getElementById('loadingOverlay').classList.remove('flex');
            }
        </script>
    </x-slot>
    {{-- Styles --}}
    <x-slot name="styles">
        <style>
            .btn-export {
                @apply relative overflow-hidden transition-all duration-300 ease-in-out shadow-md;
            }
            .btn-export::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                transition: left 0.5s;
            }
            .btn-export:hover::before {
                left: 100%;
            }
            .btn-word {
                @apply bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 hover:shadow-lg;
            }
            .btn-excel {
                @apply bg-gradient-to-r from-green-600 to-green-700 text-white hover:from-green-700 hover:to-green-800 hover:shadow-lg;
            }
            /* Responsive improvements */
            @media (max-width: 640px) {
                .btn-export {
                    @apply p-2;
                }
                .btn-export .text-xl {
                    @apply text-lg;
                }
                .btn-export .text-xs {
                    @apply text-xs;
                }
            }
            /* Custom scrollbar for overflow content */
            .overflow-x-auto::-webkit-scrollbar {
                height: 6px;
            }
            .overflow-x-auto::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            .overflow-x-auto::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 3px;
            }
            .overflow-x-auto::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        </style>
    </x-slot>
</x-filament::page>