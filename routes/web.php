<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\ScheduleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/import', [ExcelImportController::class, 'import'])->name('import');
    Route::get('/calendar', [ExcelImportController::class, 'form'])->name('calendar');

    //Route::get('/schedule', [ScheduleController::class, 'index'])->name('');
    Route::get('/deps/load', [ScheduleController::class, 'import_deps'])->name('import.departments');
    Route::get('/deps/{id}/refresh', [ScheduleController::class, 'download_dep'])->name('deps.refresh');
});

require __DIR__ . '/auth.php';