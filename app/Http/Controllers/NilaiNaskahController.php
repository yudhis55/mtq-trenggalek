<?php

namespace App\Http\Controllers;

use App\Models\NilaiNaskah;
use Illuminate\Http\Request;

class NilaiNaskahController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiNaskah::orderBy('id')->get();
        $records = NilaiNaskah::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiNaskah::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiNaskah::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiNaskah::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timernaskah', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
