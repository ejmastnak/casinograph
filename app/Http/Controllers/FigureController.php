<?php

namespace App\Http\Controllers;

use App\Models\Figure;
use App\Models\CompoundFigure;
use App\Models\FigureFamily;
use App\Models\Position;
use App\Exceptions\FigureUpdateCorruptsCompoundFigureException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreFigureRequest;
use App\Http\Requests\UpdateFigureRequest;
use App\Jobs\RegenerateFigureGraph;
use App\Services\FigureService;
use Inertia\Inertia;

class FigureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Figures/Index', [
            'figures' => Figure::getForUser(Auth::id()),
            'compound_figures' => CompoundFigure::getForUser(Auth::id()),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'positions' => Position::getForUser(Auth::id()),
            'can_delete_figures' => $user ? $user->can('delete', Figure::class) : false,
            'can_delete_compound_figures' => $user ? $user->can('delete', CompoundFigure::class) : false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Figures/Foundational/Create', [
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'positions' => Position::getForUser(Auth::id()),
        ]);
    }

    /**
     * Create a figure with a pre-filled from_position.
     */
    public function createFromPosition(Position $position)
    {
        $this->authorize('createFromPosition', [Figure::class, $position]);
        return Inertia::render('Figures/Foundational/Create', [
            'from_position' => $position->withName(),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'positions' => Position::getForUser(Auth::id()),
        ]);
    }

    /**
     * Create a figure with a pre-filled to_position.
     */
    public function createToPosition(Position $position)
    {
        $this->authorize('createToPosition', [Figure::class, $position]);
        return Inertia::render('Figures/Foundational/Create', [
            'to_position' => $position->withName(),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'positions' => Position::getForUser(Auth::id()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFigureRequest $request, FigureService $figureService)
    {
        $figureId = $figureService->storeFigure($request->validated());
        return $figureId
            ? Redirect::route('figures.show', $figureId)->with('message', 'Success! Figure created successfully.')
            : back()->with('error', 'Error. Failed to create figure.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Figure $figure)
    {
        RegenerateFigureGraph::dispatch($figure->id);
        return Inertia::render('Figures/Foundational/Show', [
            'figure' => $figure->withFamilyAndPositionsAndVideos(),
            'can_create' => Auth::user() && Auth::user()->can('create', Figure::class),
            'can_update' => Auth::user() && Auth::user()->can('update', $figure),
            'can_delete' => Auth::user() && Auth::user()->can('delete', $figure),
            'graph_path' => figureGraphLocalPathForUser($figure->id, Auth::id()),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Figure $figure)
    {
        return Inertia::render('Figures/Foundational/Edit', [
            'figure' => $figure->withFamilyAndPositionsAndVideos(),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'positions' => Position::getForUser(Auth::id()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFigureRequest $request, Figure $figure, FigureService $figureService)
    {
        $figureId = $figureService->updateFigure($request->validated(), $figure);
        return $figureId
            ? Redirect::route('figures.show', $figure->id)->with('message', 'Success! Figure updated successfully.')
            : back()->with('error', 'Error. Failed to update figure.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Figure $figure, FigureService $figureService)
    {
        $result = $figureService->deleteFigure($figure);
        if ($result['success']) return Redirect::route('figures.index')->with('message', $result['message']);
        else if ($result['restricted']) return back()->with('error', $result['message']);
        else return back()->with('error', $result['message']);
    }
}
