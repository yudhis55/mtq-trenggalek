<?php

namespace App\Http\Controllers;

use App\Models\NilaiDewasa;
use Illuminate\Http\Request;

class NilaiDewasaController extends Controller
{
    public function index($id = null)
{
    // Ambil semua records dan pastikan ada data
    $records = NilaiDewasa::orderBy('id')->get();
    $records = NilaiDewasa::latest()->paginate(1);

    // Jika tidak ada ID yang diberikan, ambil record pertama
    if (!$id) {
        $currentRecord = $records->first();
    } else {
        $currentRecord = NilaiDewasa::findOrFail($id);
    }

    // Ambil record berikutnya
    $nextRecord = NilaiDewasa::where('id', '>', $currentRecord->id)
                            ->orderBy('id', 'asc')
                            ->first();

    // Ambil record sebelumnya
    $previousRecord = NilaiDewasa::where('id', '<', $currentRecord->id)
                                ->orderBy('id', 'desc')
                                ->first();

    return view('filament.timerdewasa', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
}
}
