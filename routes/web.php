<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');


Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
Route::get('figures', [FigureController::class, 'index'])->name('figures.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('auth')->group(function () {
        Route::get('positions/create', [PositionController::class, 'create'])->name('positions.create')->can('create', Position::class);
        Route::post('positions', [PositionController::class, 'store'])->name('positions.store')->can('create', Position::class);
        Route::get('positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit')->can('update', 'position');
        Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update')->can('update', 'position');
        Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy')->can('delete', 'position');
    });

    Route::middleware('auth')->group(function () {
        Route::get('figures/create', [FigureController::class, 'create'])->name('figures.create')->can('create', Figure::class);
        Route::post('figures', [FigureController::class, 'store'])->name('figures.store')->can('create', Figure::class);
        Route::get('figures/{figure}/edit', [FigureController::class, 'edit'])->name('figures.edit')->can('update', 'figure');
        Route::put('figures/{figure}', [FigureController::class, 'update'])->name('figures.update')->can('update', 'figure');
        Route::delete('figures/{figure}', [FigureController::class, 'destroy'])->name('figures.destroy')->can('delete', 'figure');
    });
});

Route::get('positions/{position}', [PositionController::class, 'show'])->name('positions.show');
Route::get('figures/{figure}', [FigureController::class, 'show'])->name('figures.show');

require __DIR__.'/auth.php';
