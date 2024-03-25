<?php

namespace App\Http\Controllers;

use App\Models\CompoundFigure;
use App\Models\CompoundFigureFigure;
use App\Models\Figure;
use App\Models\FigureFamily;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCompoundFigureRequest;
use App\Http\Requests\UpdateCompoundFigureRequest;
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
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'figures' => Figure::getWithPositionsForUser(Auth::id()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompoundFigureRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $redirectCompoundFigureId = null;

        try {
            DB::transaction(function () use ($validated, $user, &$redirectCompoundFigureId) {

                $figureFamilyId = null;
                if (isset($validated['figure_family_id'])) {
                    $figureFamilyId = $validated['figure_family_id'];
                } // Create a new FigureFamily
                else if (!isset($validated['figure_family_id']) && isset($validated['figure_family'])) {
                    $figureFamilyId = FigureFamily::create([
                        'name' => $validated['figure_family']['name'],
                        'user_id' => $user->id,
                    ])->id;
                }

                $fromPositionId = Figure::find($validated['figure_ids'][0])->from_position_id;
                $toPositionId = Figure::find($validated['figure_ids'][count($validated['figure_ids']) - 1])->to_position_id;

                $compoundFigure = CompoundFigure::create([
                    'name' => $validated['name'],
                    'from_position_id' => $fromPositionId,
                    'to_position_id' => $toPositionId,
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figureFamilyId,
                    'user_id' => $user ? $user->id : null,
                ]);
                $redirectCompoundFigureId = $compoundFigure->id;

                foreach ($validated['figure_ids'] as $idx=>$figureId) {
                    CompoundFigureFigure::create([
                        'figure_id' => $figureId,
                        'compound_figure_id' => $compoundFigure->id,
                        'seq_num' => $idx + 1,
                        'is_final' => $idx === count($validated['figure_ids']) - 1,
                        'user_id' => $user ? $user->id : null,
                    ]);
                }
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            return Redirect::route('figures.index')->with('error', 'Error. Failed to create figure.');
        }

        return Redirect::route('compound_figures.show', $redirectCompoundFigureId)->with('message', 'Success! Compound Figure created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompoundFigure $compoundFigure)
    {
        return Inertia::render('CompoundFigures/Show', [
            'compound_figure' => $compoundFigure->withFamilyAndFigures(),
            'can_create' => Auth::user() && Auth::user()->can('create', CompoundFigure::class),
            'can_update' => Auth::user() && Auth::user()->can('update', $compoundFigure),
            'can_delete' => Auth::user() && Auth::user()->can('delete', $compoundFigure),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompoundFigure $compoundFigure)
    {
        return Inertia::render('CompoundFigures/Edit', [
            'compound_figure' => $compoundFigure->withFamilyAndFigures(),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'figures' => Figure::getWithPositionsForUser(Auth::id()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompoundFigureRequest $request, CompoundFigure $compoundFigure)
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::transaction(function () use ($validated, $user, $compoundFigure) {

                $previousFigureFamily = $compoundFigure->figure_family;
                $figureFamilyId = null;
                if (isset($validated['figure_family_id'])) {
                    $figureFamilyId = $validated['figure_family_id'];
                } // Create a new FigureFamily
                else if (!isset($validated['figure_family_id']) && isset($validated['figure_family'])) {
                    $figureFamilyId = FigureFamily::create([
                        'name' => $validated['figure_family']['name'],
                        'user_id' => $user->id,
                    ])->id;
                }

                $fromPositionId = Figure::find($validated['figure_ids'][0])->from_position_id;
                $toPositionId = Figure::find($validated['figure_ids'][count($validated['figure_ids']) - 1])->to_position_id;

                $compoundFigure->update([
                    'name' => $validated['name'],
                    'from_position_id' => $fromPositionId,
                    'to_position_id' => $toPositionId,
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figureFamilyId,
                    'user_id' => $user ? $user->id : null,
                ]);

                // I'm just deleting old CompoundFigureFigures and creating new
                // ones on each update, doesn't seem worth the extra complexity of
                // trying to update existing ones.
                foreach ($compoundFigure->compound_figure_figures as $cff) $cff->delete();
                foreach ($validated['figure_ids'] as $idx=>$figureId) {
                    CompoundFigureFigure::create([
                        'figure_id' => $figureId,
                        'compound_figure_id' => $compoundFigure->id,
                        'seq_num' => $idx + 1,
                        'is_final' => $idx === count($validated['figure_ids']) - 1,
                        'user_id' => $user ? $user->id : null,
                    ]);
                }

                // If this update will orphan a figure family, delete it.
                if ($previousFigureFamily) {
                    if (Figure::where('figure_family_id', $previousFigureFamily->id)->count() === 0 && CompoundFigure::where('figure_family_id', $previousFigureFamily->id)->count() === 0 && $validated['figure_family_id'] !== $previousFigureFamily->id) {
                        $previousFigureFamily->delete();
                    }
                }
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            return Redirect::route('figures.index')->with('error', 'Error. Failed to update figure.');
        }

        return Redirect::route('compound_figures.show', $compoundFigure->id)->with('message', 'Success! Compound Figure updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompoundFigure $compoundFigure)
    {
        DB::transaction(function () use ($compoundFigure) {
            $figureFamily = $compoundFigure->figure_family;

            // Delete CompoundFigureFigures
            $compoundFigureFigures = $compoundFigure->compound_figure_figures;
            foreach ($compoundFigureFigures as $cff) $cff->delete();

            $compoundFigure->delete();

            // If this update will orphan a figure family, delete it.
            if ($figureFamily && Figure::where('figure_family_id', $figureFamily->id)->count() === 0 && CompoundFigure::where('figure_family_id', $figureFamily->id)->count() === 0) {
                $figureFamily->delete();
            }
        });

        return Redirect::route('figures.index')->with('message', 'Success! Figure deleted successfully.');
    }
}
