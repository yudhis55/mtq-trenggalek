<?php

namespace App\Http\Controllers;

use App\Models\NilaiDuapuluhJuz;
use Illuminate\Http\Request;

class NilaiDuapuluhJuzController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiDuapuluhJuz::orderBy('id')->get();
        $records = NilaiDuapuluhJuz::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiDuapuluhJuz::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiDuapuluhJuz::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiDuapuluhJuz::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timerduapuluhjuz', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
