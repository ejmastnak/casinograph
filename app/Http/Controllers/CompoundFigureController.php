<?php

namespace App\Http\Controllers;

use App\Models\CompoundFigure;
use App\Models\CompoundFigureFigure;
use App\Models\Figure;
use App\Models\FigureFamily;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCompoundFigureRequest;
use App\Http\Requests\UpdateCompoundFigureRequest;
use App\Services\CompoundFigureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use App\Jobs\RegenerateCompoundFigureGraph;
use Inertia\Inertia;

class CompoundFigureController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Figures/Compound/Create', [
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'figures' => Figure::getWithOnlyPositionsForUser(Auth::id()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompoundFigureRequest $request, CompoundFigureService $compoundFigureService)
    {
        $compoundFigureId = $compoundFigureService->storeCompoundFigure($request->validated());
        return $compoundFigureId
            ? Redirect::route('compound-figures.show', $compoundFigureId)->with('message', 'Success! Compound Figure created successfully.')
            : back()->with('error', 'Error. Failed to create figure.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompoundFigure $compoundFigure)
    {
        RegenerateCompoundFigureGraph::dispatch($compoundFigure->id);
        return Inertia::render('Figures/Compound/Show', [
            'compound_figure' => $compoundFigure->withFamilyAndFiguresAndVideos(),
            'can_create' => Auth::user() && Auth::user()->can('create', CompoundFigure::class),
            'can_update' => Auth::user() && Auth::user()->can('update', $compoundFigure),
            'can_delete' => Auth::user() && Auth::user()->can('delete', $compoundFigure),
            'graph_path' => compoundFigureGraphPublicPathForUser($compoundFigure->id, Auth::id()),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompoundFigure $compoundFigure)
    {
        return Inertia::render('Figures/Compound/Edit', [
            'compound_figure' => $compoundFigure->withFamilyAndFiguresAndVideos(),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'figures' => Figure::getWithOnlyPositionsForUser(Auth::id()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompoundFigureRequest $request, CompoundFigure $compoundFigure, CompoundFigureService $compoundFigureService)
    {
        $compoundFigureId = $compoundFigureService->updateCompoundFigure($request->validated(), $compoundFigure);
        return $compoundFigureId
            ? Redirect::route('compound-figures.show', $compoundFigureId)->with('message', 'Success! Figure updated successfully.')
            : back()->with('error', 'Error. Failed to update figure.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompoundFigure $compoundFigure, CompoundFigureService $compoundFigureService)
    {
        $result = $compoundFigureService->deleteCompoundFigure($compoundFigure);
        if ($result['success']) return Redirect::route('figures.index')->with('message', $result['message']);
        else if ($result['restricted']) return back()->with('error', $result['message']);
        else return back()->with('error', $result['message']);
    }
}
