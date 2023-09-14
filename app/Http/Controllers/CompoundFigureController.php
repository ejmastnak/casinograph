<?php

namespace App\Http\Controllers;

use App\Models\CompoundFigure;
use App\Models\CompoundFigureFigure;
use App\Models\Figure;
use App\Models\FigureFamily;
use Illuminate\Http\Request;
use App\Http\Requests\CompoundFigureStoreRequest;
use App\Http\Requests\CompoundFigureUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompoundFigureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('CompoundFigures/Index', [
            'compound_figures' => CompoundFigure::with(['figure_family:id,name'])->get(['id', 'name', 'weight', 'figure_family_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('CompoundFigures/Create', [
            'figure_families' => FigureFamily::all(['id', 'name']),
            'figures' => Figure::all(['id', 'name', 'from_position_id', 'to_position_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompoundFigureStoreRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        DB::transaction(function () use ($validated, $user) {
            $from_position_id = Figure::find($validated['figure_ids'][0])->from_position_id;
            $to_position_id = Figure::find($validated['figure_ids'][count($validated['figure_ids']) - 1])->to_position_id;

            $compound_figure = CompoundFigure::create([
                'name' => $validated['name'],
                'from_position_id' => $from_position_id,
                'to_position_id' => $to_position_id,
                'description' => isset($validated['description']) ? $validated['description'] : null,
                'weight' => isset($validated['weight']) ? $validated['weight'] : null,
                'figure_family_id' => isset($validated['figure_family_id']) ? $validated['figure_family_id'] : null,
            ]);

            foreach ($validated['figure_ids'] as $idx=>$figure_id) {
                CompoundFigureFigure::create([
                    'figure_id' => $figure_id,
                    'compound_figure_id' => $compound_figure->id,
                    'idx' => $idx + 1,
                    'user_id' => $user ? $user->id : null,
                ]);
            }
        });

        return Redirect::route('compound-figures.show', $figure->id)->with('message', 'Success! Compound Figure created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompoundFigure $compound_figure)
    {
        $compound_figure->load(['figure_family:id,name', 'figures:id,name,description,from_position_id,to_position_id', 'figures.from_position:id,name', 'figures.to_position:id,name']);
        return Inertia::render('CompoundFigures/Show', [
            'compound_figure' => $compound_figure->only(['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'figures']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompoundFigure $compound_figure)
    {
        $compound_figure->load(['figure_family:id,name', 'figures:id,name,description,from_position_id,to_position_id', 'figures.from_position:id,name', 'figures.to_position:id,name']);
        return Inertia::render('CompoundFigures/Edit', [
            'compound_figure' => $compound_figure->only(['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'figures']),
            'figure_families' => FigureFamily::all(['id', 'name']),
            'figures' => Figure::all(['id', 'name', 'from_position_id', 'to_position_id']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompoundFigureUpdateRequest $request, CompoundFigure $compound_figure)
    {
        $validated = $request->validated();
        $user = Auth::user();

        DB::transaction(function () use ($validated, $user) {
            $from_position_id = Figure::find($validated['figure_ids'][0])->from_position_id;
            $to_position_id = Figure::find($validated['figure_ids'][count($validated['figure_ids']) - 1])->to_position_id;

            $compound_figure->update([
                'name' => $validated['name'],
                'from_position_id' => $from_position_id,
                'to_position_id' => $to_position_id,
                'description' => isset($validated['description']) ? $validated['description'] : null,
                'weight' => isset($validated['weight']) ? $validated['weight'] : null,
                'figure_family_id' => isset($validated['figure_family_id']) ? $validated['figure_family_id'] : null,
            ]);

            // I'm just deleting old CompoundFigureFigures and creating new ones on
            // each update, doesn't seem worth trying updating existing ones.
            foreach ($compound_figure->compound_figure_figures as $compound_figure_figure) {
                $compound_figure_figure->delete();
            }
            foreach ($validated['figure_ids'] as $idx=>$figure_id) {
                CompoundFigureFigure::create([
                    'figure_id' => $figure_id,
                    'compound_figure_id' => $compound_figure->id,
                    'idx' => $idx + 1,
                    'user_id' => $user ? $user->id : null,
                ]);
            }
        });

        return Redirect::route('compound-figures.show', $compound_figure->id)->with('message', 'Success! Compound Figure updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompoundFigure $compound_figure)
    {
        //
    }
}
