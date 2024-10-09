<?php

namespace App\Http\Controllers;

use App\Models\NilaiAnak;
use Illuminate\Http\Request;

class NilaiAnakController extends Controller
{
    public function index($id = null)
{
    // Ambil semua records dan pastikan ada data
    $records = NilaiAnak::orderBy('id')->get();
    $records = NilaiAnak::latest()->paginate(1);

    // Jika tidak ada ID yang diberikan, ambil record pertama
    if (!$id) {
        $currentRecord = $records->first();
    } else {
        $currentRecord = NilaiAnak::findOrFail($id);
    }

    // Ambil record berikutnya
    $nextRecord = NilaiAnak::where('id', '>', $currentRecord->id)
                            ->orderBy('id', 'asc')
                            ->first();

    // Ambil record sebelumnya
    $previousRecord = NilaiAnak::where('id', '<', $currentRecord->id)
                                ->orderBy('id', 'desc')
                                ->first();

    return view('filament.timeranak', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
}
}
