<?php

namespace App\Http\Controllers;

use App\Models\NilaiMmq;
use Illuminate\Http\Request;

class NilaiKtiqController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiMmq::orderBy('id')->get();
        $records = NilaiMmq::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiMmq::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiMmq::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiMmq::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timerktiq', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
