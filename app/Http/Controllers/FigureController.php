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
use App\Http\Requests\StoreFigureRequest;
use App\Http\Requests\UpdateFigureRequest;
use Inertia\Inertia;

class FigureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Figures/Index', [
            'figures' => Figure::getCombinedFiguresAndCompoundFiguresForUser(Auth::id()),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'show_edit_delete_icons' => Auth::user() && Auth::user()->is_admin === 1,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Figures/Create', [
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
        return Inertia::render('Figures/Create', [
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
        return Inertia::render('Figures/Create', [
            'to_position' => $position->withName(),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'positions' => Position::getForUser(Auth::id()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFigureRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $redirectFigureId = null;

        try {
            DB::transaction(function () use ($validated, $user, &$redirectFigureId) {

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

                $figure = Figure::create([
                    'name' => $validated['name'],
                    'from_position_id' => $validated['from_position_id'],
                    'to_position_id' => $validated['to_position_id'],
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figureFamilyId,
                    'user_id' => $user ? $user->id : null,
                ]);
                $redirectFigureId = $figure->id;

            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            return Redirect::route('figures.index')->with('error', 'Error. Failed to create figure.');
        }

        return Redirect::route('figures.show', $redirectFigureId)->with('message', 'Success! Figure created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Figure $figure)
    {
        return Inertia::render('Figures/Show', [
            'figure' => $figure->withFamilyAndPositions(),
            'can_create' => Auth::user() && Auth::user()->can('create', Figure::class),
            'can_update' => Auth::user() && Auth::user()->can('update', $figure),
            'can_delete' => Auth::user() && Auth::user()->can('delete', $figure),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Figure $figure)
    {
        return Inertia::render('Figures/Edit', [
            'figure' => $figure->withFamilyAndPositions(),
            'figure_families' => FigureFamily::getForUser(Auth::id()),
            'positions' => Position::getForUser(Auth::id()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFigureRequest $request, Figure $figure)
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::transaction(function () use ($figure, $validated, $user) {

                // Check that updating this figure's to/from position will not
                // corrupt to/from position integrity in the figure sequence of
                // any dependent CompoundFigures.
                $newFromPosition = $figure->from_position_id !== $validated['from_position_id'];
                $newToPosition = $figure->to_position_id !== $validated['to_position_id'];
                if (($newFromPosition || $newToPosition) && $figure->compound_figure_figures()->count() > 0) {
                    foreach ($figure->compound_figure_figures as $cff) {
                        if ($cff->seq_num === 1 && $newToPosition) {
                            throw new FigureUpdateCorruptsCompoundFigureException("Updating this figure is intentionally forbidden because it would cause incompatible starting and ending positions in the figure sequence of a dependent compound figure (" . $cff->compound_figure->name . "). You may want to create a new figure instead, update the compound figure to use the new figure, then delete this figure.\nThe problem is the first figure in " . $cff->compound_figure->name . ".");
                        } else if ($cff->is_final && $newFromPosition) {
                            throw new FigureUpdateCorruptsCompoundFigureException("Updating this figure is intentionally forbidden because it would cause incompatible starting and ending positions in the figure sequence of a dependent compound figure (" . $cff->compound_figure->name . "). You may want to create a new figure instead, update the compound figure to use the new figure, then delete this figure.\nThe problem is the final figure in " . $cff->compound_figure->name . ".");
                        } else if ($cff->seq_num > 1 && !$cff->is_final) {
                            throw new FigureUpdateCorruptsCompoundFigureException("Updating this figure is intentionally forbidden because it would cause incompatible starting and ending positions in the figure sequence of a dependent compound figure (" . $cff->compound_figure->name . "). You may want to create a new figure instead, update the compound figure to use the new figure, then delete this figure.\nThe problem is figure " . $cff->seq_num . " in " . $cff->compound_figure->name . ".");
                        }
                    }
                }

                $previousFigureFamily = $figure->figure_family;
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

                $figure->update([
                    'name' => $validated['name'],
                    'from_position_id' => $validated['from_position_id'],
                    'to_position_id' => $validated['to_position_id'],
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'figure_family_id' => $figureFamilyId,
                    'weight' => isset($validated['weight']) ? $validated['weight'] : config('misc.default_figure_weight'),
                ]);

                // If this update will orphan a figure family, delete it.
                if ($previousFigureFamily) {
                    if (Figure::where('figure_family_id', $previousFigureFamily->id)->count() === 0 && CompoundFigure::where('figure_family_id', $previousFigureFamily->id)->count() === 0 && $validated['figure_family_id'] !== $previousFigureFamily->id) {
                        $previousFigureFamily->delete();
                    }
                }
            });
        } catch (FigureUpdateCorruptsCompoundFigureException $e) {
            if (\App::environment('local')) throw $e;
            return Redirect::route('figures.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
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
            $figureFamily = $figure->figure_family;
            $figure->delete();

            // If this update will orphan a figure family, delete it.
            if ($figureFamily && Figure::where('figure_family_id', $figureFamily->id)->count() === 0 && CompoundFigure::where('figure_family_id', $figureFamily->id)->count() === 0) {
                $figureFamily->delete();
            }
        });

        return Redirect::route('figures.index')->with('message', 'Success! Figure deleted successfully.');
    }
}
