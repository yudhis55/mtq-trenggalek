<?php


use Livewire\Livewire;
use App\Http\Middleware\XSS;
use App\Livewire\Peserta\EditPeserta;
use Illuminate\Support\Facades\Route;
use App\Livewire\Peserta\CreatePeserta;
use App\Http\Controllers\NilaiMsqController;
use App\Http\Controllers\NilaiAnakController;
use App\Http\Controllers\NilaiKtiqController;
use App\Http\Controllers\NilaiDewasaController;
use App\Http\Controllers\NilaiMushafController;
use App\Http\Controllers\NilaiNaskahController;
use App\Http\Controllers\NilaiRemajaController;
use App\Http\Controllers\NilaiTartilController;
use App\Http\Controllers\NilaiLimaJuzController;
use App\Http\Controllers\NilaiSatuJuzController;
use App\Http\Controllers\NilaiDekorasiController;
use App\Http\Controllers\NilaiSepuluhJuzController;
use App\Http\Controllers\NilaiDuapuluhJuzController;
use App\Http\Controllers\NilaiKontemporerController;
use App\Http\Controllers\NilaiTigapuluhJuzController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::view('/', 'welcome')->name('home');
// Route::get('/tes1', CreatePeserta::class);
// Route::get('/tes2', EditPeserta::class);
// Route::get('/livewire/update', function () {
//     return view('welcome');
// });

Route::fallback(function() {
    return redirect()->back();
});

// Route::middleware('auth')->group(function () {
//     Route::post('/livewire/update', [HttpConnectionHandler::class, 'update'])->name('livewire.update');
// });

Route::get('/nilai-tartil/{id?}', [NilaiTartilController::class, 'index'])->name('nilai-tartil.index');
Route::get('/nilai-anak/{id?}', [NilaiAnakController::class, 'index'])->name('nilai-anak.index');
Route::get('/nilai-remaja/{id?}', [NilaiRemajaController::class, 'index'])->name('nilai-remaja.index');
Route::get('/nilai-dewasa/{id?}', [NilaiDewasaController::class, 'index'])->name('nilai-dewasa.index');
Route::get('/nilai-satujuz/{id?}', [NilaiSatuJuzController::class, 'index'])->name('nilai-satujuz.index');
Route::get('/nilai-limajuz/{id?}', [NilaiLimaJuzController::class, 'index'])->name('nilai-limajuz.index');
Route::get('/nilai-sepuluhjuz/{id?}', [NilaiSepuluhJuzController::class, 'index'])->name('nilai-sepuluhjuz.index');
Route::get('/nilai-duapuluhjuz/{id?}', [NilaiDuapuluhJuzController::class, 'index'])->name('nilai-duapuluhjuz.index');
Route::get('/nilai-tigapuluhjuz/{id?}', [NilaiTigapuluhJuzController::class, 'index'])->name('nilai-tigapuluhjuz.index');
Route::get('/nilai-msq/{id?}', [NilaiMsqController::class, 'index'])->name('nilai-msq.index');
Route::get('/nilai-naskah/{id?}', [NilaiNaskahController::class, 'index'])->name('nilai-naskah.index');
Route::get('/nilai-mushaf/{id?}', [NilaiMushafController::class, 'index'])->name('nilai-mushaf.index');
Route::get('/nilai-dekorasi/{id?}', [NilaiDekorasiController::class, 'index'])->name('nilai-dekorasi.index');
Route::get('/nilai-kontemporer/{id?}', [NilaiKontemporerController::class, 'index'])->name('nilai-kontemporer.index');
Route::get('/nilai-ktiq/{id?}', [NilaiKtiqController::class, 'index'])->name('nilai-ktiq.index');
