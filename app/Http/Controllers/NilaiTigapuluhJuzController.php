<?php

namespace App\Http\Controllers;

use App\Models\NilaiTigapuluhJuz;
use Illuminate\Http\Request;

class NilaiTigapuluhJuzController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiTigapuluhJuz::orderBy('id')->get();
        $records = NilaiTigapuluhJuz::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiTigapuluhJuz::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiTigapuluhJuz::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiTigapuluhJuz::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timertigapuluhjuz', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
