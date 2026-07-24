<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MitraExportController;

use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ExcelExportController;
use App\Filament\Pages\MitraPreview;

use App\Http\Controllers\BoqExportController;
use App\Http\Controllers\MitraPreviewController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/admin');
});

// TEMPLATE WORD
Route::get('/mitra/{id}/export-baut', [MitraExportController::class, 'exportBaut'])->name('mitra.export.baut');
Route::get('/mitra/{id}/export-ba-abd', [MitraExportController::class, 'exportBaAbd'])->name('mitra.export.ba.abd');
Route::get('/mitra/{id}/export-ba-legal', [MitraExportController::class, 'exportBaLegal'])->name('mitra.export.ba.legal');
Route::get('/mitra/{id}/export-ba-rekon', [MitraExportController::class, 'exportBaRekon'])->name('mitra.export.ba.rekon');
Route::get('/mitra/{id}/export-pernyataan-material', [MitraExportController::class, 'exportPernyataanMaterial'])->name('mitra.export.pernyataan.material');
Route::get('/mitra/{id}/export-bast', [MitraExportController::class, 'exportBast'])->name('mitra.export.bast');
Route::get('/mitra/{id}/export-pemotongantagihan', [MitraExportController::class, 'exportPemotonganTagihan'])->name('mitra.export.pemotongantagihan');
Route::get('/mitra/{id}/export-barekonmaterial', [MitraExportController::class, 'exportBarekonMaterial'])->name('mitra.export.barekonmaterial');
Route::get('/mitra/{id}/export-barekonospfo', [MitraExportController::class, 'exportBarekonospfo'])->name('mitra.export.barekonospfo');

// TEMPLATE EXCEL
Route::get('/export/excel/baut/{id}', [ExcelExportController::class, 'exportBaut'])->name('mitra.export.excel.baut');
Route::get('/export/excel/balegal/{id}', [ExcelExportController::class, 'exportBalegal'])->name('mitra.export.excel.ba.legal');
Route::get('/export/excel/barekon/{id}', [ExcelExportController::class, 'exportBarekon'])->name('mitra.export.excel.ba.rekon');
Route::get('/export/excel/bast/{id}', [ExcelExportController::class, 'exportBast'])->name('mitra.export.excel.bast');
Route::get('/export/excel/bastabd/{id}', [ExcelExportController::class, 'exportBastabd'])->name('mitra.export.excel.bastabd');
Route::get('/export/excel/checklist/{id}', [ExcelExportController::class, 'exportChecklist'])->name('mitra.export.excel.checklist');
Route::get('/export/excel/material/{id}', [ExcelExportController::class, 'exportMaterial'])->name('mitra.export.excel.pernyataan.material');
Route::get('/export/excel/baqclulus/{id}', [ExcelExportController::class, 'exportBaqclulus'])->name('mitra.export.excel.baqclulus');
Route::get('/export/excel/pemotongantagihan/{id}', [ExcelExportController::class, 'exportPemotonganTagihan'])->name('mitra.export.excel.pemotongantagihan');
Route::get('/export/excel/lampiranbarekontambahkurang/{id}', [ExcelExportController::class, 'exportLampiranBarekonTambahKurang'])->name('mitra.export.excel.lampiranbarekontambahkurang');
Route::get('/export/excel/resumebarekon/{id}', [ExcelExportController::class, 'exportResumeBarekon'])->name('mitra.export.excel.resumebarekon');


Route::post('/boq/drop/{id}', [BoqExportController::class, 'dropBoq'])->name('boq.drop');

Route::get('/export/excel/ba-abd/{id}', [ExcelExportController::class, 'exportAbd'])->name('mitra.export.excel.ba.abd');
Route::post('/export/excel/ba-abd/{id}', [ExcelExportController::class, 'exportAbd'])->name('mitra.export.excel.ba.abd.post');

Route::get('/export/excel/ba-abd/{id}', [ExcelExportController::class, 'exportAbd'])->name('mitra.export.excel.ba.abd');

Route::get('/preview-select', [MitraPreviewController::class, 'previewSelect'])->name('mitra.preview.select');

Route::get('/admin/mitra-preview/{record?}', MitraPreview::class)
    ->name('mitra.preview.byid');

Route::get('/export-boq/{id}', [BoqExportController::class, 'export'])->name('boq.export');

Route::get('/mitra/{id}/export-all-word', [MitraExportController::class, 'exportAllWord'])
    ->name('mitra.export.all.word');

Route::get('/mitra/{id}/export-all-excel', [ExcelExportController::class, 'exportAllExcel'])
    ->name('mitra.export.all.excel');

    // masuk ke laravel logs
Route::get('/secret-laravel-log', function () {
    abort_if(!Auth::check() || !Auth::user()->is_superadmin, 403);

    return app(\Rap2hpoutre\LaravelLogViewer\LogViewerController::class)->index();
});
