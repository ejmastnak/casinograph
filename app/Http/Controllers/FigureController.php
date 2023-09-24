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
use Illuminate\Support\Facades\DB;
use App\Http\Requests\FigureStoreRequest;
use App\Http\Requests\FigureUpdateRequest;
use Inertia\Inertia;

class FigureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $figures = Figure::with(['figure_family:id,name', 'from_position:id,name', 'to_position:id,name'])->get()->mapWithKeys(function ($figure, $key) {
            return [ $key => [
                'id' => $figure['id'],
                'name' => $figure['name'],
                'weight' => $figure['weight'],
                'figure_family_id' => $figure['figure_family_id'],
                'figure_family' => $figure['figure_family'],
                'from_position_id' => $figure['from_position_id'],
                'from_position' => $figure['from_position'],
                'to_position_id' => $figure['to_position_id'],
                'to_position' => $figure['to_position'],
                'compound' => false,
            ]];
        });

        $compound_figures = CompoundFigure::with(['figure_family:id,name', 'from_position:id,name', 'to_position:id,name'])->get()->mapWithKeys(function ($figure, $key) {
            return [ $key => [
                'id' => $figure['id'],
                'name' => $figure['name'],
                'weight' => $figure['weight'],
                'figure_family_id' => $figure['figure_family_id'],
                'figure_family' => $figure['figure_family'],
                'from_position_id' => $figure['from_position_id'],
                'from_position' => $figure['from_position'],
                'to_position_id' => $figure['to_position_id'],
                'to_position' => $figure['to_position'],
                'compound' => true,
            ]];
        });

        return Inertia::render('Figures/Index', [
            'figures' => $figures->concat($compound_figures)->sortBy('name')->values()->all(),
            'figure_families' => FigureFamily::orderBy('name')->get(['id', 'name']),
            'show_edit_delete_icons' => Auth::user() ? Auth::user()->is_admin === 1 : false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Figures/Create', [
            'figure_families' => FigureFamily::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FigureStoreRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $redirect_figure_id = null;

        try {
            DB::transaction(function () use ($validated, $user, &$redirect_figure_id) {

                $figure_family_id = null;
                if (isset($validated['figure_family_id'])) {
                    $figure_family_id = $validated['figure_family_id'];
                } // Create a new FigureFamily
                else if (!isset($validated['figure_family_id']) && isset($validated['figure_family'])) {
                    $figure_family_id = FigureFamily::create([
                        'name' => $validated['figure_family']['name'],
                        'user_id' => $user->id,
                    ])->id;
                }

                $figure = Figure::create([
                    'name' => $validated['name'],
                    'from_position_id' => $validated['from_position_id'],
                    'to_position_id' => $validated['to_position_id'],
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figure_family_id,
                    'user_id' => $user ? $user->id : null,
                ]);
                $redirect_figure_id = $figure->id;

            });
        } catch (\Exception $e) {
            // throw $e;
            return Redirect::route('figures.index')->with('error', 'Error. Failed to create figure.');
        }

        return Redirect::route('figures.show', $redirect_figure_id)->with('message', 'Success! Figure created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Figure $figure)
    {
        $figure->load(['figure_family:id,name', 'from_position:id,name', 'to_position:id,name']);
        return Inertia::render('Figures/Show', [
            'figure' => $figure->only(['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'from_position_id', 'from_position', 'to_position_id', 'to_position']),
            'show_edit_delete_icons' => Auth::user() ? Auth::user()->is_admin === 1 : false,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Figure $figure)
    {
        $figure->load(['figure_family:id,name', 'from_position:id,name', 'to_position:id,name']);
        return Inertia::render('Figures/Edit', [
            'figure' => $figure->only(['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'from_position_id', 'from_position', 'to_position_id', 'to_position']),
            'figure_families' => FigureFamily::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FigureUpdateRequest $request, Figure $figure)
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::transaction(function () use ($figure, $validated, $user) {

                // Check that updating this figure's to/from position will not
                // corrupt to/from position integrity in the figure sequence of
                // any dependent CompoundFigures.
                $new_from_position = $figure->from_position_id !== $validated['from_position_id'];
                $new_to_position = $figure->to_position_id !== $validated['to_position_id'];
                if (($new_from_position || $new_to_position) && $figure->compound_figure_figures()->count() > 0) {
                    foreach ($figure->compound_figure_figures as $cff) {
                        if ($cff->idx === 1 && $new_to_position) {
                            throw new FigureUpdateCorruptsCompoundFigureException("Updating this figure is intentionally forbidden because it would cause incompatible starting and ending positions in the figure sequence of a dependent compound figure (" . $cff->compound_figure->name . "). You may want to create a new figure instead, update the compound figure to use the new figure, then delete this figure.\nThe problem is the first figure in " . $cff->compound_figure->name . ".");
                        } else if ($cff->is_final && $new_from_position) {
                            throw new FigureUpdateCorruptsCompoundFigureException("Updating this figure is intentionally forbidden because it would cause incompatible starting and ending positions in the figure sequence of a dependent compound figure (" . $cff->compound_figure->name . "). You may want to create a new figure instead, update the compound figure to use the new figure, then delete this figure.\nThe problem is the final figure in " . $cff->compound_figure->name . ".");
                        } else if ($cff->idx > 1 && !$cff->is_final) {
                            throw new FigureUpdateCorruptsCompoundFigureException("Updating this figure is intentionally forbidden because it would cause incompatible starting and ending positions in the figure sequence of a dependent compound figure (" . $cff->compound_figure->name . "). You may want to create a new figure instead, update the compound figure to use the new figure, then delete this figure.\nThe problem is figure " . $cff->idx . " in " . $cff->compound_figure->name . ".");
                        }
                    }
                }

                $previous_figure_family = $figure->figure_family;
                $figure_family_id = null;
                if (isset($validated['figure_family_id'])) {
                    $figure_family_id = $validated['figure_family_id'];
                } // Create a new FigureFamily
                else if (!isset($validated['figure_family_id']) && isset($validated['figure_family'])) {
                    $figure_family_id = FigureFamily::create([
                        'name' => $validated['figure_family']['name'],
                        'user_id' => $user->id,
                    ])->id;
                }

                $figure->update([
                    'name' => $validated['name'],
                    'from_position_id' => $validated['from_position_id'],
                    'to_position_id' => $validated['to_position_id'],
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'figure_family_id' => $figure_family_id,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                ]);

                // If this update will orphan a figure family, delete it.
                if ($previous_figure_family) {
                    if (Figure::where('figure_family_id', $previous_figure_family->id)->count() === 0 && CompoundFigure::where('figure_family_id', $previous_figure_family->id)->count() === 0 && $validated['figure_family_id'] !== $previous_figure_family->id) {
                        $previous_figure_family->delete();
                    }
                }
            });
        } catch (FigureUpdateCorruptsCompoundFigureException $e) {
            // throw $e;
            return Redirect::route('figures.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            // throw $e;
            return Redirect::route('figures.index')->with('error', 'Error. Failed to update figure.');
        }

        return Redirect::route('figures.show', $figure->id)->with('message', 'Success! Figure updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Figure $figure)
    {
        // Warn user about restrictOnDelete foreign key constraints
        if ($figure->compound_figure_figures()->count() > 0) {

            $limit = config('restrict_on_delete_message_limit');
            $i = 0;
            $names = [];
            foreach ($figure->compound_figure_figures as $cff) {
                if ($i === $limit) break;
                $name = $cff->compound_figure->name;
                if (!in_array($name, $names)) {
                    $names[] = $name;
                    $i++;
                }
            }

            return back()->with('error', "Deleting this position is intentionally forbidden because one or more compound figures rely on it. You should first delete all dependent compound figures, then delete this figure.\nThe dependent compound figures " . ($i === $limit ? "include " : "are ") . implode(", ", $names) . ".");
        }

        DB::transaction(function () use ($figure) {
            $figure_family = $figure->figure_family;
            $figure->delete();

            // If this update will orphan a figure family, delete it.
            if ($figure_family && Figure::where('figure_family_id', $figure_family->id)->count() === 0 && CompoundFigure::where('figure_family_id', $figure_family->id)->count() === 0) {
                $figure_family->delete();
            }
        });

        return Redirect::route('figures.index')->with('message', 'Success! Figure deleted successfully.');
    }
}
