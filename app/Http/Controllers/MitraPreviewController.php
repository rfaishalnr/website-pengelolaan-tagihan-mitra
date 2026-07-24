<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Models\MitraPendaftaran;

class MitraPreviewController extends Controller
{
    public function previewSelect()
    {
        $allData = MitraPendaftaran::all();
        return view('filament.pages.mitra-preview-dropdown', compact('allData'));
    }

    public function previewById($id)
    {
        $data = MitraPendaftaran::findOrFail($id);
        return view('filament.pages.mitra-preview', compact('data'));
    }

    public function preview($id)
    {
        // Load mitra with BOQ lines
        $data = MitraPendaftaran::with('boqLinesOrdered')->findOrFail($id);
        
        // Get all mitras for dropdown
        $mitras = MitraPendaftaran::select('id', 'nama_mitra')
                                 ->orderBy('nama_mitra')
                                 ->get();

        return view('mitra.preview', compact('data', 'mitras'));
    }


}

// Alternative: If you're using a different controller structure
class MitraController extends Controller
{
    public function show($id)
    {
        $data = MitraPendaftaran::with([
            'boqLines' => function($query) {
                $query->orderBy('no')->orderBy('id');
            }
        ])->findOrFail($id);
        
        return view('mitra.show', compact('data'));
    }

}
