<?php

namespace App\Http\Controllers;

use App\Models\NilaiSepuluhJuz;
use Illuminate\Http\Request;

class NilaiSepuluhJuzController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiSepuluhJuz::orderBy('id')->get();
        $records = NilaiSepuluhJuz::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiSepuluhJuz::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiSepuluhJuz::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiSepuluhJuz::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timersepuluhjuz', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
