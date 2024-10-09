<?php

namespace App\Http\Controllers;

use App\Models\NilaiRemaja;
use Illuminate\Http\Request;

class NilaiRemajaController extends Controller
{
    public function index($id = null)
{
    // Ambil semua records dan pastikan ada data
    $records = NilaiRemaja::orderBy('id')->get();
    $records = NilaiRemaja::latest()->paginate(1);

    // Jika tidak ada ID yang diberikan, ambil record pertama
    if (!$id) {
        $currentRecord = $records->first();
    } else {
        $currentRecord = NilaiRemaja::findOrFail($id);
    }

    // Ambil record berikutnya
    $nextRecord = NilaiRemaja::where('id', '>', $currentRecord->id)
                            ->orderBy('id', 'asc')
                            ->first();

    // Ambil record sebelumnya
    $previousRecord = NilaiRemaja::where('id', '<', $currentRecord->id)
                                ->orderBy('id', 'desc')
                                ->first();

    return view('filament.timerremaja', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
}
}
