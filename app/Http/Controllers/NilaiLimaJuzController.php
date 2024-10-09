<?php

namespace App\Http\Controllers;

use App\Models\NilaiLimaJuz;
use Illuminate\Http\Request;

class NilaiLimaJuzController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiLimaJuz::orderBy('id')->get();
        $records = NilaiLimaJuz::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiLimaJuz::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiLimaJuz::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiLimaJuz::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timerlimajuz', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
