<?php


use Illuminate\Support\Facades\Route;
use App\Livewire\Peserta\CreatePeserta;
use App\Livewire\Peserta\EditPeserta;
use App\Http\Middleware\XSS;
use Livewire\Livewire;

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
