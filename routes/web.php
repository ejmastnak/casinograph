<?php

use App\Models\Position;
use App\Models\Figure;
use App\Models\CompoundFigure;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\FigureController;
use App\Http\Controllers\CompoundFigureController;
use App\Http\Controllers\PositionFamilyController;
use App\Http\Controllers\FigureFamilyController;
use App\Http\Controllers\FigureSequenceController;
use App\Services\CasinoGraphService;
use App\Jobs\RegenerateCasinoGraph;
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

Route::get('/', function (CasinoGraphService $casinoGraphService) {
    RegenerateCasinoGraph::dispatch($casinoGraphService);
    $userId = Auth::id();
    return Inertia::render('Home', [
        'graph_url' => casinoGraphUrlForUser($userId),
        'graph_is_nonempty' => Figure::where('user_id', ($userId ?? config('constants.user_ids.casino')))->count() > 0,
    ]);
})->name('home');

Route::get('figure-sequence', [FigureSequenceController::class, 'home'])->name('figure-sequence.home');
Route::post('figure-sequence', [FigureSequenceController::class, 'figureSequence'])->name('figure-sequence.figure-sequence');

Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
Route::get('figures', [FigureController::class, 'index'])->name('figures.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('positions/create', [PositionController::class, 'create'])->name('positions.create')->can('create', Position::class);
    Route::post('positions', [PositionController::class, 'store'])->name('positions.store')->can('create', Position::class);
    Route::get('positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit')->can('update', 'position');
    Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update')->can('update', 'position');
    Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy')->can('delete', 'position');

    Route::get('figures/create', [FigureController::class, 'create'])->name('figures.create')->can('create', Figure::class);
    Route::get('figures/create-from-position/{position}', [FigureController::class, 'createFromPosition'])->name('figures.create-from-position');
    Route::get('figures/create-to-position/{position}', [FigureController::class, 'createToPosition'])->name('figures.create-to-position');
    Route::post('figures', [FigureController::class, 'store'])->name('figures.store')->can('create', Figure::class);
    Route::get('figures/{figure}/edit', [FigureController::class, 'edit'])->name('figures.edit')->can('update', 'figure');
    Route::put('figures/{figure}', [FigureController::class, 'update'])->name('figures.update')->can('update', 'figure');
    Route::delete('figures/{figure}', [FigureController::class, 'destroy'])->name('figures.destroy')->can('delete', 'figure');

    Route::get('compound-figures/create', [CompoundFigureController::class, 'create'])->name('compound-figures.create')->can('create', CompoundFigure::class);
    Route::post('compound-figures', [CompoundFigureController::class, 'store'])->name('compound-figures.store')->can('create', CompoundFigure::class);
    Route::get('compound-figures/{compound_figure}/edit', [CompoundFigureController::class, 'edit'])->name('compound-figures.edit')->can('update', 'compound_figure');
    Route::put('compound-figures/{compound_figure}', [CompoundFigureController::class, 'update'])->name('compound-figures.update')->can('update', 'compound_figure');
    Route::delete('compound-figures/{compound_figure}', [CompoundFigureController::class, 'destroy'])->name('compound-figures.destroy')->can('delete', 'compound_figure');

    Route::put('position-families/{position_family}', [PositionFamilyController::class, 'update'])->name('position-families.update')->can('update', 'position_family');
    Route::put('figure-families/{figure_family}', [FigureFamilyController::class, 'update'])->name('figure-families.update')->can('update', 'figure_family');
});

Route::get('positions/{position}', [PositionController::class, 'show'])->name('positions.show');
Route::get('figures/{figure}', [FigureController::class, 'show'])->name('figures.show');
Route::get('compound-figures/{compound_figure}', [CompoundFigureController::class, 'show'])->name('compound-figures.show');
Route::get('position-families/{position_family}', [PositionFamilyController::class, 'show'])->name('position-families.show');
Route::get('figure-families/{figure_family}', [FigureFamilyController::class, 'show'])->name('figure-families.show');

require __DIR__.'/auth.php';
