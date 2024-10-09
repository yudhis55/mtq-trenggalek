<?php

namespace App\Http\Controllers;

use App\Models\NilaiSatuJuz;
use Illuminate\Http\Request;

class NilaiSatuJuzController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiSatuJuz::orderBy('id')->get();
        $records = NilaiSatuJuz::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiSatuJuz::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiSatuJuz::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiSatuJuz::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timersatujuz', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
