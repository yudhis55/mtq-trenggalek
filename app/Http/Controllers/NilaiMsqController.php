<?php

namespace App\Http\Controllers;

use App\Models\NilaiMsq;
use Illuminate\Http\Request;

class NilaiMsqController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiMsq::orderBy('id')->get();
        $records = NilaiMsq::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiMsq::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiMsq::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiMsq::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timermsq', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
