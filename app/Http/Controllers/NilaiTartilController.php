<?php
namespace App\Http\Controllers;

use App\Models\NilaiTartil;
use Illuminate\Http\Request;

use Illuminate\View\View;



class NilaiTartilController extends Controller
{

    // public function index()
    // {
        //get posts
        // $records = NilaiTartil::latest()->paginate(1);

        // //render view with posts
        // return view('filament.pages.timer-tartil', compact('records'));
    // }

    public function index($id = null)
{
    // Ambil semua records dan pastikan ada data
    $records = NilaiTartil::orderBy('id')->get();
    $records = NilaiTartil::latest()->paginate(1);

    // Jika tidak ada ID yang diberikan, ambil record pertama
    if (!$id) {
        $currentRecord = $records->first();
    } else {
        $currentRecord = NilaiTartil::findOrFail($id);
    }

    // Ambil record berikutnya
    $nextRecord = NilaiTartil::where('id', '>', $currentRecord->id)
                            ->orderBy('id', 'asc')
                            ->first();

    // Ambil record sebelumnya
    $previousRecord = NilaiTartil::where('id', '<', $currentRecord->id)
                                ->orderBy('id', 'desc')
                                ->first();

    return view('filament.timertartil', compact('currentRecord', 'nextRecord', 'previousRecord', 'records'));
}

}
