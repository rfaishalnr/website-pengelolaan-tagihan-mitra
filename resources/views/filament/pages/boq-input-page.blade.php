@php
    $ppnPercent = $ppnPercent ?? 11;
    $droppedLocations = $droppedLocations ?? []; // Array untuk tracking lokasi yang di-drop
@endphp
<x-filament-panels::page>
    <style>
        .fi-ta-table {
            table-layout: fixed;
            width: 100%;
            height: 350px;
            word-break: break-word;
        }
    </style>
    <div class="space-y-6">
        <!-- Form untuk pilih mitra -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <form wire:submit.prevent="submit" class="space-y-6">
                    {{ $this->form }}
                </form>
            </div>
        </div>
        <!-- Tabel Input BOQ -->
        @if ($this->showTable)
            <div
                class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                {{-- Catatan/Keterangan --}}
                <div
                    class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start space-x-3">
                        <div
                            class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 text-xs"></i>
                        </div>
                        <div class="flex-1 text-lg text-blue-800 dark:text-blue-200">
                            <p class="font-medium mb-2"><span class="inline-flex items-center gap-2 font-semibold tracking-wide"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M11 3a1 1 0 0 1 2 0v1.05a7.002 7.002 0 0 1 5.95 5.95H20a1 1 0 1 1 0 2h-1.05a7.002 7.002 0 0 1-5.95 5.95V21a1 1 0 1 1-2 0v-1.05a7.002 7.002 0 0 1-5.95-5.95H4a1 1 0 1 1 0-2h1.05A7.002 7.002 0 0 1 11 4.05V3z"/></svg> Petunjuk Penggunaan</span></p>
                            <ul class="space-y-1 text-sm">
                                <li>• Untuk <strong>FULL SCREEN</strong> klik tanda <strong>(<) di<strong>
                                                SIDEBAR </strong></li>
                                <li>• Gunakan tombol <strong>FONT SIZE</strong> untuk memperbesar/memperkecil ukuran
                                    font</li>
                                <li>• Jangan lupa untuk klik<strong> SIMPAN DATA BOQ </strong>di sebelah<strong> Kanan
                                        Atas </strong></li>
                                <li>• Klik<strong> TAMBAH BARIS </strong>jika ingin tambah baris kebawah</li>
                                <li>• Tombol<strong> DROP </strong> dan <strong> HAPUS </strong>ada di sebelah
                                    <strong>KANAN</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Header dengan tombol -->
                <div
                    class="fi-section-header flex items-center justify-between gap-4 bg-gray-50/50 px-6 py-4 dark:bg-white/5">
                    <div>
                        <h3
                            class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            Input Data BOQ
                        </h3>
                        <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                            Masukkan data BOQ untuk mitra yang dipilih
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Zoom Controls -->
                        <div
                            class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Font Size (%) :</span>
                            <button onclick="zoomOut()" type="button"
                                class="flex items-center justify-center w-6 h-6 rounded text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
                                title="Zoom Out">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                    </path>
                                </svg>
                            </button>
                            <span id="zoomLevel"
                                class="text-xs font-medium text-gray-700 dark:text-gray-300 min-w-8 text-center">100%</span>
                            <button onclick="zoomIn()" type="button"
                                class="flex items-center justify-center w-6 h-6 rounded text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
                                title="Zoom In">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                            <button onclick="resetZoom()" type="button"
                                class="ml-1 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                title="Reset Zoom">
                                Reset
                            </button>
                        </div>
                        <button wire:click="addRow" type="button"
                            class="fi-btn fi-btn-size-md fi-color-primary inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-primary-600 transition-colors hover:bg-primary-50 focus-visible:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-400/10">
                            <svg class="fi-btn-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Tambah Baris
                        </button>
                        <button wire:click="submit" type="button"
                            class="fi-btn fi-btn-size-md fi-color-success inline-flex items-center justify-center gap-1.5 rounded-lg 
           bg-green-600 hover:bg-green-700 focus-visible:bg-green-700 active:bg-green-800
           dark:bg-green-400 dark:hover:bg-green-300 dark:focus-visible:bg-green-300 dark:active:bg-green-500
           px-4 py-2 text-sm font-semibold 
           text-white/90 hover:text-white focus-visible:text-white active:text-gray-100
           dark:text-gray-900/90 dark:hover:text-gray-900 dark:focus-visible:text-gray-900 dark:active:text-gray-800
           shadow-sm transition-all duration-200 
           focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 
           dark:focus:ring-green-300 dark:focus:ring-offset-gray-800
           disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="fi-btn-icon w-4 h-4 
               text-white/90 hover:text-white focus-visible:text-white active:text-gray-100
               dark:text-gray-900/90 dark:hover:text-gray-900 dark:focus-visible:text-gray-900 dark:active:text-gray-800
               transition-colors duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                </path>
                            </svg>
                            <span
                                class="text-white/90 hover:text-white focus-visible:text-white active:text-gray-100
                 dark:text-gray-900/90 dark:hover:text-gray-900 dark:focus-visible:text-gray-900 dark:active:text-gray-800
                 transition-colors duration-200">
                                Simpan Data BOQ
                            </span>
                        </button>
                    </div>
                </div>
                <!-- Tabel -->
                <div class="fi-section-content overflow-x-auto">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5"
                        style="min-width: 1800px;">
                        <thead class="divide-y divide-gray-200 dark:divide-white/5">
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th rowspan="2"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-gray-950 dark:text-white border-r-2 border-gray-300 dark:border-gray-600"
                                    style="width: 60px;">No</th>
                                <th rowspan="2"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-gray-950 dark:text-white border-r-2 border-gray-300 dark:border-gray-600"
                                    style="width: 300px;">Nama Lokasi</th>
                                <th rowspan="2"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-gray-950 dark:text-white border-r-4 border-gray-400 dark:border-gray-500"
                                    style="width: 80px;">STO</th>
                                <th rowspan="2"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-gray-950 dark:text-white border-r-2 border-gray-300 dark:border-gray-600"
                                    style="width: 200px;">ID Project</th>
                                <th colspan="3"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-info-600 dark:text-info-400 bg-success-50 dark:bg-success-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 450px;">Nilai SP</th>
                                <th colspan="3"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-info-600 dark:text-info-400 bg-warning-50 dark:bg-warning-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 450px;">Nilai Rekon</th>
                                <th colspan="3"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-info-600 dark:text-info-400 bg-info-50 dark:bg-info-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 450px;">Nilai Tambah</th>
                                <th colspan="3"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-info-600 dark:text-info-400 bg-danger-50 dark:bg-danger-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 450px;">Nilai Kurang</th>
                                <th rowspan="2"
                                    class="fi-ta-header-cell px-3 py-3.5 text-center text-xs font-medium text-gray-950 dark:text-white"
                                    style="width: 80px;">Aksi</th>
                            </tr>
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <!-- SP -->
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-success-700 dark:text-success-300 bg-success-50 dark:bg-success-400/10 border border-success-200 dark:border-success-600"
                                    style="width: 150px;">Material</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-success-700 dark:text-success-300 bg-success-50 dark:bg-success-400/10 border border-success-200 dark:border-success-600"
                                    style="width: 150px;">Jasa</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-success-700 dark:text-success-300 bg-success-50 dark:bg-success-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 150px;">Total</th>
                                <!-- REKON -->
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-warning-700 dark:text-warning-300 bg-warning-50 dark:bg-warning-400/10 border border-warning-200 dark:border-warning-600"
                                    style="width: 150px;">Material</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-warning-700 dark:text-warning-300 bg-warning-50 dark:bg-warning-400/10 border border-warning-200 dark:border-warning-600"
                                    style="width: 150px;">Jasa</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-warning-700 dark:text-warning-300 bg-warning-50 dark:bg-warning-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 150px;">Total</th>
                                <!-- TAMBAH -->
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-info-700 dark:text-info-300 bg-info-50 dark:bg-info-400/10 border border-info-200 dark:border-info-600"
                                    style="width: 150px;">Material</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-info-700 dark:text-info-300 bg-info-50 dark:bg-info-400/10 border border-info-200 dark:border-info-600"
                                    style="width: 150px;">Jasa</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-info-700 dark:text-info-300 bg-info-50 dark:bg-info-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 150px;">Total</th>
                                <!-- KURANG -->
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-danger-700 dark:text-danger-300 bg-danger-50 dark:bg-danger-400/10 border border-danger-200 dark:border-danger-600"
                                    style="width: 150px;">Material</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-danger-700 dark:text-danger-300 bg-danger-50 dark:bg-danger-400/10 border border-danger-200 dark:border-danger-600"
                                    style="width: 150px;">Jasa</th>
                                <th class="fi-ta-header-cell px-3 py-2 text-xs font-medium text-danger-700 dark:text-danger-300 bg-danger-50 dark:bg-danger-400/10 border border-gray-400 dark:border-gray-500"
                                    style="width: 150px;">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                            @foreach ($this->tableData as $index => $row)
                            <tr wire:key="boq-row-{{ $row['id'] ?? $index }}" class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                                
                                <td class="fi-ta-cell px-3 py-4 text-center text-sm text-gray-950 dark:text-white border-r-2 border-gray-300 dark:border-gray-600"
                                    style="width: 60px;">{{ $row['no'] ?? ($index + 1) }}</td>
                                
                                <td class="fi-ta-cell px-2 py-2 border-r-2 border-gray-300 dark:border-gray-600"
                                    style="width: 200px;">
                                    <input type="text"
                                        wire:model="tableData.{{ $index }}.nama_lokasi"
                                        class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:text-white dark:placeholder:text-gray-500 sm:text-sm"
                                        placeholder="Nama lokasi..." style="width: 100%;">
                                </td>
                                    <td class="fi-ta-cell px-2 py-2 border-r-4 border-gray-400 dark:border-gray-500"
                                        style="width: 120px;">
                                        <input type="text" wire:model.defer="tableData.{{ $index }}.sto"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:text-white dark:placeholder:text-gray-500 sm:text-sm"
                                            placeholder="STO..." style="width: 100%;">
                                    </td>
                                    <td class="fi-ta-cell px-2 py-2 border-r-4 border-gray-400 dark:border-gray-500"
                                        style="width: 200px;">
                                        <input type="text" wire:model.defer="tableData.{{ $index }}.id_project"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:text-white dark:placeholder:text-gray-500 sm:text-sm"
                                            placeholder="ID Project..." style="width: 100%;">
                                    </td>
                                    <!-- SP -->
                                    <td class="fi-ta-cell px-2 py-2 border border-success-200 dark:border-success-600"
                                        style="width: 150px;">
                                        <input type="text"
                                            wire:model.defer="tableData.{{ $index }}.sp_material"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-success-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-2 py-2 border border-success-200 dark:border-success-600"
                                        style="width: 150px;">
                                        <input type="text" wire:model.defer="tableData.{{ $index }}.sp_jasa"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-success-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-medium text-success-700 dark:text-success-300 bg-success-50/50 dark:bg-success-400/5 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format(($row['sp_material'] ?? 0) + ($row['sp_jasa'] ?? 0), 0, ',', '.') }}
                                    </td>
                                    <!-- REKON -->
                                    <td class="fi-ta-cell px-2 py-2 border border-warning-200 dark:border-warning-600"
                                        style="width: 150px;">
                                        <input type="text"
                                            wire:model.defer="tableData.{{ $index }}.rekon_material"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-warning-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-2 py-2 border border-warning-200 dark:border-warning-600"
                                        style="width: 150px;">
                                        <input type="text"
                                            wire:model.defer="tableData.{{ $index }}.rekon_jasa"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-warning-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-medium text-warning-700 dark:text-warning-300 bg-warning-50/50 dark:bg-warning-400/5 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format(($row['rekon_material'] ?? 0) + ($row['rekon_jasa'] ?? 0), 0, ',', '.') }}
                                    </td>
                                    <!-- TAMBAH -->
                                    <td class="fi-ta-cell px-2 py-2 border border-info-200 dark:border-info-600"
                                        style="width: 150px;">
                                        <input type="text"
                                            wire:model.defer="tableData.{{ $index }}.tambah_material"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-info-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-2 py-2 border border-info-200 dark:border-info-600"
                                        style="width: 150px;">
                                        <input type="text"
                                            wire:model.defer="tableData.{{ $index }}.tambah_jasa"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-info-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-medium text-info-700 dark:text-info-300 bg-info-50/50 dark:bg-info-400/5 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format(($row['tambah_material'] ?? 0) + ($row['tambah_jasa'] ?? 0), 0, ',', '.') }}
                                    </td>
                                    <!-- KURANG -->
                                    <td class="fi-ta-cell px-2 py-2 border border-danger-200 dark:border-danger-600"
                                        style="width: 150px;">
                                        <input type="text"
                                            wire:model.defer="tableData.{{ $index }}.kurang_material"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-danger-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-2 py-2 border border-danger-200 dark:border-danger-600"
                                        style="width: 150px;">
                                        <input type="text"
                                            wire:model.defer="tableData.{{ $index }}.kurang_jasa"
                                            oninput="formatNumber(this)" onpaste="handlePaste(this)"
                                            class="fi-input block w-full rounded-lg border-none bg-transparent py-1.5 text-right text-base text-gray-950 focus:ring-2 focus:ring-danger-600 dark:text-white sm:text-sm"
                                            style="width: 100%;" placeholder="0">
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-medium text-danger-700 dark:text-danger-300 bg-danger-50/50 dark:bg-danger-400/5 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format(($row['kurang_material'] ?? 0) + ($row['kurang_jasa'] ?? 0), 0, ',', '.') }}
                                    </td>

                                    
                                    <td class="fi-ta-cell px-3 py-4" style="width: 160px;">
                                        <div class="flex flex-col items-center justify-center space-y-1">
                                            <div class="flex items-center justify-center space-x-2">
                                                @php
                                                    $isDropped = in_array($row['nama_lokasi'] ?? '', $droppedLocations ?? []);
                                                @endphp
                                    
                                                @if (!$isDropped && !empty($row['id']))
                                                    {{-- Tombol Drop --}}
                                                    <form action="{{ route('boq.drop', $row['id']) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin DROP lokasi ini?\nSemua nilai akan dikosongkan kecuali Rekon?')">
                                                        @csrf
                                                        <button type="submit"
                                                            class="fi-ac-btn-action inline-flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-70 h-8 w-8 text-warning-500 hover:text-warning-600 focus-visible:ring-warning-600"
                                                            title="Drop semua nilai kecuali rekon">
                                                            <svg class="fi-btn-icon h-4 w-4"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @elseif ($isDropped)
                                                    <span class="text-xs text-red-600 font-semibold">[DROP]</span>
                                                @endif
                                    
                                                {{-- Tombol Hapus --}}
                                                <button wire:click="removeRow({{ $index }})" type="button"
                                                    class="fi-ac-btn-action inline-flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-70 h-8 w-8 text-danger-500 hover:text-danger-600 focus-visible:ring-danger-600"
                                                    title="Hapus baris">
                                                    <svg class="fi-btn-icon h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                    
                                            {{-- Status Validasi --}}
                                            @php
                                                $sp = floatval($row['sp_total'] ?? 0);
                                                $kurang = floatval($row['kurang_total'] ?? 0);
                                                $rekon = floatval($row['rekon_total'] ?? 0);
                                                $tambah = floatval($row['tambah_total'] ?? 0);
                                                $rumus = round($sp - $kurang - $rekon + $tambah, 2);
                                                $status = $rumus === 0.00 ? 'VALID' : 'NON VALID';
                                                $color = $rumus === 0.00 ? 'text-green-600' : 'text-red-600';
                                            @endphp
                                            <span class="text-xs font-semibold {{ $color }}">{{ $status }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <!-- Grand Total Row -->
                            @if (count($this->tableData) > 0)
                                @php
                                    $grandTotals = [
                                        'sp_material' => 0,
                                        'sp_jasa' => 0,
                                        'rekon_material' => 0,
                                        'rekon_jasa' => 0,
                                        'tambah_material' => 0,
                                        'tambah_jasa' => 0,
                                        'kurang_material' => 0,
                                        'kurang_jasa' => 0,
                                    ];
                                    foreach ($this->tableData as $row) {
                                        $grandTotals['sp_material'] += $row['sp_material'] ?? 0;
                                        $grandTotals['sp_jasa'] += $row['sp_jasa'] ?? 0;
                                        $grandTotals['rekon_material'] += $row['rekon_material'] ?? 0;
                                        $grandTotals['rekon_jasa'] += $row['rekon_jasa'] ?? 0;
                                        $grandTotals['tambah_material'] += $row['tambah_material'] ?? 0;
                                        $grandTotals['tambah_jasa'] += $row['tambah_jasa'] ?? 0;
                                        $grandTotals['kurang_material'] += $row['kurang_material'] ?? 0;
                                        $grandTotals['kurang_jasa'] += $row['kurang_jasa'] ?? 0;
                                    }
                                @endphp
                                <tr
                                    class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                                    <td colspan="4"
                                    class="fi-ta-cell px-3 py-4 text-sm font-bold text-gray-950 dark:text-white border-r-4 border-gray-400 dark:border-gray-500"
                                    style="text-align: center; width: auto;">
                                    GRAND TOTAL
                                </td>
                                    <!-- SP Totals -->
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-success-700 dark:text-success-300 bg-success-100 dark:bg-success-400/20 border border-success-200 dark:border-success-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['sp_material'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-success-700 dark:text-success-300 bg-success-100 dark:bg-success-400/20 border border-success-200 dark:border-success-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['sp_jasa'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-bold text-success-700 dark:text-success-300 bg-success-200 dark:bg-success-400/30 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['sp_material'] + $grandTotals['sp_jasa'], 0, ',', '.') }}
                                    </td>
                                    <!-- REKON Totals -->
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-warning-700 dark:text-warning-300 bg-warning-100 dark:bg-warning-400/20 border border-warning-200 dark:border-warning-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['rekon_material'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-warning-700 dark:text-warning-300 bg-warning-100 dark:bg-warning-400/20 border border-warning-200 dark:border-warning-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['rekon_jasa'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-bold text-warning-700 dark:text-warning-300 bg-warning-200 dark:bg-warning-400/30 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['rekon_material'] + $grandTotals['rekon_jasa'], 0, ',', '.') }}
                                    </td>
                                    <!-- TAMBAH Totals -->
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-info-700 dark:text-info-300 bg-info-100 dark:bg-info-400/20 border border-info-200 dark:border-info-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['tambah_material'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-info-700 dark:text-info-300 bg-info-100 dark:bg-info-400/20 border border-info-200 dark:border-info-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['tambah_jasa'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-bold text-info-700 dark:text-info-300 bg-info-200 dark:bg-info-400/30 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['tambah_material'] + $grandTotals['tambah_jasa'], 0, ',', '.') }}
                                    </td>
                                    <!-- KURANG Totals -->
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-danger-700 dark:text-danger-300 bg-danger-100 dark:bg-danger-400/20 border border-danger-200 dark:border-danger-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['kurang_material'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-center text-sm font-bold text-danger-700 dark:text-danger-300 bg-danger-100 dark:bg-danger-400/20 border border-danger-200 dark:border-danger-600"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['kurang_jasa'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4 text-right text-sm font-bold text-danger-700 dark:text-danger-300 bg-danger-200 dark:bg-danger-400/30 border border-gray-400 dark:border-gray-500"
                                        style="width: 150px;">
                                        {{ number_format($grandTotals['kurang_material'] + $grandTotals['kurang_jasa'], 0, ',', '.') }}
                                    </td>
                                    <td class="fi-ta-cell px-3 py-4" style="width: 80px;"></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <!-- Footer info -->
                <div
                    class="fi-section-content border-t border-gray-200 bg-gray-50/50 px-6 py-4 dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="h-5 w-5 flex-shrink-0 text-info-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-xs">
                            <p class="font-medium text-gray-700 dark:text-gray-300">Tips: Nama Lokasi dan STO wajib
                                diisi. Baris kosong tidak akan disimpan.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Section Grand Total REKON + PPN + Terbilang -->
    <div class="fi-section-content border-t border-gray-200 bg-white px-6 py-6 dark:border-white/10 dark:bg-gray-900">
        @php
            $rekonMaterial = $grandTotals['rekon_material'] ?? 0;
            $rekonJasa = $grandTotals['rekon_jasa'] ?? 0;
            $rekonTotal = $rekonMaterial + $rekonJasa;
            $ppnValue = ($rekonTotal * $ppnPercent) / 100;
            $totalWithPpn = $rekonTotal + $ppnValue;
            function terbilang($angka)
            {
                $angka = abs($angka);
                $bilangan = [
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
                    'Sebelas',
                ];
                if ($angka < 12) {
                    return $bilangan[$angka];
                }
                if ($angka < 20) {
                    return terbilang($angka - 10) . ' Belas';
                }
                if ($angka < 100) {
                    return terbilang($angka / 10) . ' Puluh ' . terbilang($angka % 10);
                }
                if ($angka < 200) {
                    return 'Seratus ' . terbilang($angka - 100);
                }
                if ($angka < 1000) {
                    return terbilang($angka / 100) . ' Ratus ' . terbilang($angka % 100);
                }
                if ($angka < 2000) {
                    return 'Seribu ' . terbilang($angka - 1000);
                }
                if ($angka < 1000000) {
                    return terbilang($angka / 1000) . ' Ribu ' . terbilang($angka % 1000);
                }
                if ($angka < 1000000000) {
                    return terbilang($angka / 1000000) . ' Juta ' . terbilang($angka % 1000000);
                }
                return 'Angka terlalu besar';
            }
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-800 dark:text-white">
            <!-- Kolom Kiri -->
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="font-semibold">Grand Total Rekon:</span>
                    <span>{{ number_format($rekonTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <label class="font-semibold">PPN</label>
                        <span class="px-2 py-1 text-sm rounded bg-gray-100 dark:bg-gray-800 dark:text-white">
                            {{ $ppnPercent }}%
                        </span>
                    </div>
                    <span>{{ number_format($ppnValue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t pt-2 border-gray-300 dark:border-white/10">
                    <span class="font-bold text-base">Total + PPN:</span>
                    <span
                        class="text-green-600 font-bold text-base">{{ number_format($totalWithPpn, 0, ',', '.') }}</span>
                </div>
            </div>
            <!-- Kolom Kanan (Terbilang) -->
            <div class="space-y-2">
                <label class="font-semibold">Terbilang:</label>
                <div
                    class="bg-gray-50 border border-gray-200 rounded p-3 text-sm italic text-gray-800 dark:bg-gray-800 dark:border-white/10 dark:text-white">
                    {{ ucfirst(terbilang($totalWithPpn)) }} Rupiah
                </div>
            </div>
        </div>
        <script>
let droppedLocations = [];
let currentMitraId = null;
let currentFontSize = 100;
const fontSizeStep = 10;
const minFontSize = 20;
const maxFontSize = 250;
function initializeDropData(mitraId, existingDrops = []) {
    currentMitraId = mitraId;
    droppedLocations = existingDrops;
    const saved = localStorage.getItem(`dropped_locations_${mitraId}`);
    if (saved) {
        try {
            const savedData = JSON.parse(saved);
            droppedLocations = savedData;
        } catch (e) {
            console.warn('Error parsing saved dropped locations:', e);
        }
    }
    updateDropIndicators();
}
function zoomIn() {
    if (currentFontSize < maxFontSize) {
        currentFontSize += fontSizeStep;
        applyFontZoom();
    }
}
function zoomOut() {
    if (currentFontSize > minFontSize) {
        currentFontSize -= fontSizeStep;
        applyFontZoom();
    }
}
function resetZoom() {
    currentFontSize = 100;
    applyFontZoom();
}
function zoomInFont() { zoomIn(); }
function zoomOutFont() { zoomOut(); }
function resetFontZoom() { resetZoom(); }
function applyFontZoom() {
    const table = document.querySelector('.fi-ta-table') || 
                  document.querySelector('table') || 
                  document.querySelector('.table');
    const zoomLevel = document.getElementById('zoomLevel');
    if (zoomLevel) {
        zoomLevel.textContent = `${currentFontSize}%`;
    }
    if (!table) {
        const allTables = document.querySelectorAll('table');
        allTables.forEach(t => applyZoomToTable(t));
    } else {
        applyZoomToTable(table);
    }
}
function applyZoomToTable(table) {
    const scale = currentFontSize / 100;
    const headers = table.querySelectorAll('th');
    headers.forEach(header => {
        header.style.fontSize = `${0.75 * scale}rem`;
        header.style.transition = 'font-size 0.2s ease';
    });
    const cells = table.querySelectorAll('td');
    cells.forEach(cell => {
        cell.style.fontSize = `${0.875 * scale}rem`;
        cell.style.transition = 'font-size 0.2s ease';
    });
    const inputs = table.querySelectorAll('input');
    inputs.forEach(input => {
        input.style.fontSize = `${0.875 * scale}rem`;
        input.style.transition = 'font-size 0.2s ease';
    });
    const buttons = table.querySelectorAll('button');
    buttons.forEach(button => {
        button.style.fontSize = `${0.75 * scale}rem`;
        button.style.transition = 'font-size 0.2s ease';
    });
    const textElements = table.querySelectorAll('span, label, p, div');
    textElements.forEach(element => {
        element.style.fontSize = `${0.875 * scale}rem`;
        element.style.transition = 'font-size 0.2s ease';
    });
}
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        if (e.key === '=' || e.key === '+') {
            e.preventDefault();
            zoomIn();
            return false;
        } else if (e.key === '-') {
            e.preventDefault();
            zoomOut();
            return false;
        } else if (e.key === '0') {
            e.preventDefault();
            resetZoom();
            return false;
        } else if (e.key === 'd') {
            e.preventDefault();
            if (typeof showDropManager === 'function') {
                showDropManager();
            }
            return false;
        } else if (e.key === 'r' && e.shiftKey) {
            e.preventDefault();
            if (typeof clearAllDrops === 'function') {
                clearAllDrops();
            }
            return false;
        }
    }
});
function formatNumber(input) {
    let value = input.value;
    let cleanValue = value.replace(/[^\d.]/g, '');
    if (cleanValue.includes('.')) {
        cleanValue = cleanValue.replace(/\./g, '');
    }
    cleanValue = cleanValue.replace(/\D/g, '');
    if (cleanValue === '') {
        input.value = '';
        return;
    }
    let numericValue = parseInt(cleanValue);
    input.value = numericValue.toLocaleString('id-ID');
    if (numericValue >= 1000000000) {
        input.classList.add('text-red-600', 'font-semibold');
        input.title = `${numericValue.toLocaleString('id-ID')} (${Math.round(numericValue/1000000000*100)/100} Miliar)`;
    } else if (numericValue >= 1000000) {
        input.classList.add('text-orange-600', 'font-semibold');
        input.title = `${numericValue.toLocaleString('id-ID')} (${Math.round(numericValue/1000000*100)/100} Juta)`;
    } else if (numericValue >= 1000) {
        input.classList.add('text-blue-600', 'font-medium');
        input.title = `${numericValue.toLocaleString('id-ID')} (${Math.round(numericValue/1000*100)/100} Ribu)`;
    } else {
        input.classList.remove('text-red-600', 'text-orange-600', 'text-blue-600', 'font-semibold', 'font-medium');
        input.title = numericValue.toLocaleString('id-ID');
    }
    input.dispatchEvent(new Event('input', { bubbles: true }));
}
function handlePaste(input) {
    setTimeout(() => {
        formatNumber(input);
    }, 10);
}
document.addEventListener('DOMContentLoaded', function() {
    const numberInputs = document.querySelectorAll('input[oninput*="formatNumber"]');
    numberInputs.forEach(input => {
        if (input.value && input.value !== '0') {
            formatNumber(input);
        }
    });
    setTimeout(() => {
        applyFontZoom();
    }, 500);
    if (typeof updateDropIndicators === 'function') {
        updateDropIndicators();
    }
});
window.tableZoom = {
    font: {
        in: zoomIn,
        out: zoomOut,
        reset: resetZoom,
        current: () => currentFontSize
    }
};
function dropAndSaveToDatabase(mitraId) {
    fetch(`/boq/drop/${mitraId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            dropped_locations: droppedLocations
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Berhasil drop dan simpan ke database!');
        } else {
            alert('Gagal menyimpan!');
        }
    });
}
function testZoom() {
    console.log('Testing zoom functionality...');
    console.log('Current font size:', currentFontSize);
    zoomIn();
    setTimeout(() => {
        console.log('After zoom in:', currentFontSize);
        zoomOut();
        setTimeout(() => {
            console.log('After zoom out:', currentFontSize);
            resetZoom();
            console.log('After reset:', currentFontSize);
        }, 1000);
    }, 1000);
}
window.testZoom = testZoom;
</script>
</x-filament-panels::page>
