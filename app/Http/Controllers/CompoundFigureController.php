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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('CompoundFigures/Create', [
            'figure_families' => FigureFamily::all(['id', 'name']),
            'figures' => Figure::with(['from_position:id,name', 'to_position:id,name'])->get(['id', 'name', 'from_position_id', 'to_position_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompoundFigureStoreRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $redirect_compound_figure_id = null;

        try {
            DB::transaction(function () use ($validated, $user, &$redirect_compound_figure_id) {
                $from_position_id = Figure::find($validated['figure_ids'][0])->from_position_id;
                $to_position_id = Figure::find($validated['figure_ids'][count($validated['figure_ids']) - 1])->to_position_id;

                $compound_figure = CompoundFigure::create([
                    'name' => $validated['name'],
                    'from_position_id' => $from_position_id,
                    'to_position_id' => $to_position_id,
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => isset($validated['figure_family_id']) ? $validated['figure_family_id'] : null,
                    'user_id' => $user ? $user->id : null,
                ]);
                $redirect_compound_figure_id = $compound_figure->id;

                foreach ($validated['figure_ids'] as $idx=>$figure_id) {
                    CompoundFigureFigure::create([
                        'figure_id' => $figure_id,
                        'compound_figure_id' => $compound_figure->id,
                        'idx' => $idx + 1,
                        'user_id' => $user ? $user->id : null,
                    ]);
                }
            });
        } catch (\Exception $e) {
            throw $e;
            return Redirect::route('figures.index')->with('error', 'Error. Failed to create figure.');
        }

        return Redirect::route('compound_figures.show', $redirect_compound_figure_id)->with('message', 'Success! Compound Figure created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompoundFigure $compound_figure)
    {
        $compound_figure->load([
            'figure_family:id,name',
            'compound_figure_figures:id,idx,compound_figure_id,figure_id',
            'compound_figure_figures.figure:id,name,from_position_id,to_position_id',
            'compound_figure_figures.figure.from_position:id,name',
            'compound_figure_figures.figure.to_position:id,name'
        ]);
        return Inertia::render('CompoundFigures/Show', [
            'compound_figure' => $compound_figure->only(['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'compound_figure_figures']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompoundFigure $compound_figure)
    {
        $compound_figure->load([
            'figure_family:id,name',
            'compound_figure_figures:id,idx,compound_figure_id,figure_id',
            'compound_figure_figures.figure:id,name,from_position_id,to_position_id',
            'compound_figure_figures.figure.from_position:id,name',
            'compound_figure_figures.figure.to_position:id,name'
        ]);
        return Inertia::render('CompoundFigures/Edit', [
            'compound_figure' => $compound_figure->only(['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'compound_figure_figures']),
            'figure_families' => FigureFamily::all(['id', 'name']),
            'figures' => Figure::with(['from_position:id,name', 'to_position:id,name'])->get(['id', 'name', 'from_position_id', 'to_position_id']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompoundFigureUpdateRequest $request, CompoundFigure $compound_figure)
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::transaction(function () use ($validated, $user, $compound_figure) {
                $from_position_id = Figure::find($validated['figure_ids'][0])->from_position_id;
                $to_position_id = Figure::find($validated['figure_ids'][count($validated['figure_ids']) - 1])->to_position_id;

                $compound_figure->update([
                    'name' => $validated['name'],
                    'from_position_id' => $from_position_id,
                    'to_position_id' => $to_position_id,
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => isset($validated['figure_family_id']) ? $validated['figure_family_id'] : null,
                    'user_id' => $user ? $user->id : null,
                ]);

                // I'm just deleting old CompoundFigureFigures and creating new
                // ones on each update, doesn't seem worth the extra complexity of
                // trying to update existing ones.
                foreach ($compound_figure->compound_figure_figures as $compound_figure_figure) $compound_figure_figure->delete();
                foreach ($validated['figure_ids'] as $idx=>$figure_id) {
                    CompoundFigureFigure::create([
                        'figure_id' => $figure_id,
                        'compound_figure_id' => $compound_figure->id,
                        'idx' => $idx + 1,
                        'user_id' => $user ? $user->id : null,
                    ]);
                }
            });
        } catch (\Exception $e) {
            throw $e;
            return Redirect::route('figures.index')->with('error', 'Error. Failed to update figure.');
        }

        return Redirect::route('compound_figures.show', $compound_figure->id)->with('message', 'Success! Compound Figure updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompoundFigure $compound_figure)
    {
        DB::transaction(function () use ($compound_figure) {
            $figure_family = $compound_figure->figure_family;

            // Delete CompoundFigureFigures
            $compound_figure_figures = $compound_figure->compound_figure_figures;
            foreach ($compound_figure_figures as $compound_figure_figure) $compound_figure_figure->delete();

            $compound_figure->delete();

            // If this update will orphan a figure family, delete it.
            if ($figure_family && Figure::where('figure_family_id', $figure_family->id)->count() === 0 && CompoundFigure::where('figure_family_id', $figure_family->id)->count() === 0) {
                $figure_family->delete();
            }
        });

        return Redirect::route('figures.index')->with('message', 'Success! Figure deleted successfully.');
    }
}
