<?php

namespace App\Http\Controllers;

use App\Models\NilaiKontemporer;
use Illuminate\Http\Request;

class NilaiKontemporerController extends Controller
{
    public function index($id = null)
    {
        // Ambil semua records dan pastikan ada data
        $records = NilaiKontemporer::orderBy('id')->get();
        $records = NilaiKontemporer::latest()->paginate(1);

        // Jika tidak ada ID yang diberikan, ambil record pertama
        if (!$id) {
            $currentRecord = $records->first();
        } else {
            $currentRecord = NilaiKontemporer::findOrFail($id);
        }

        // Ambil record berikutnya
        $nextRecord = NilaiKontemporer::where('id', '>', $currentRecord->id)
                                ->orderBy('id', 'asc')
                                ->first();

        // Ambil record sebelumnya
        $previousRecord = NilaiKontemporer::where('id', '<', $currentRecord->id)
                                    ->orderBy('id', 'desc')
                                    ->first();

        return view('filament.timerkontemporer', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
    }
}
