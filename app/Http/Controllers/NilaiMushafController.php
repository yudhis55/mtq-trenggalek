<?php

namespace App\Http\Controllers;

use App\Models\NilaiMushaf;
use Illuminate\Http\Request;

class NilaiMushafController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiMushaf::orderBy('id')->get();
        $records = NilaiMushaf::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiMushaf::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiMushaf::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiMushaf::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timermushaf', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
