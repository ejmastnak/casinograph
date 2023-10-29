<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\PositionFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use App\Http\Requests\PositionStoreRequest;
use App\Http\Requests\PositionUpdateRequest;
use Inertia\Inertia;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Positions/Index', [
            'positions' => Position::with('position_family:id,name')->orderBy('name')->get(['id', 'name', 'position_family_id']),
            'position_families' => PositionFamily::orderBy('name')->get(['id', 'name']),
            'show_edit_delete_icons' => Auth::user() ? Auth::user()->is_admin === 1 : false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Positions/Create', [
            'position_families' => PositionFamily::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionStoreRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $redirectPositionId = null;

        try {
            DB::transaction(function () use ($validated, $user, &$redirectPositionId) {

                $positionFamilyId = null;
                if (isset($validated['position_family_id'])) {
                    $positionFamilyId = $validated['position_family_id'];
                } // Create a new PositionFamily
                else if (!isset($validated['position_family_id']) && isset($validated['position_family'])) {
                    $positionFamilyId = PositionFamily::create([
                        'name' => $validated['position_family']['name'],
                        'user_id' => $user->id,
                    ])->id;
                }

                $position = Position::create([
                    'name' => $validated['name'],
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'position_family_id' => $positionFamilyId,
                    'user_id' => $user ? $user->id : null,
                ]);
                $redirectPositionId = $position->id;

            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            return Redirect::route('positions.index')->with('error', 'Error. Failed to create position.');
        }

        return Redirect::route('positions.show', $redirectPositionId)->with('message', 'Success! Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        $position->load(['position_family:id,name', 'incoming_figures:id,name,to_position_id,from_position_id', 'incoming_figures.from_position:id,name', 'outgoing_figures:id,name,from_position_id,to_position_id', 'outgoing_figures.to_position:id,name']);
        return Inertia::render('Positions/Show', [
            'position' => $position->only(['id', 'name', 'description', 'position_family_id', 'position_family', 'incoming_figures', 'outgoing_figures']),
            'can_create' => Auth::user() && Auth::user()->can('create', Position::class),
            'can_update' => Auth::user() && Auth::user()->can('update', $position),
            'can_delete' => Auth::user() && Auth::user()->can('delete', $position),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        $position->load(['position_family:id,name']);
        return Inertia::render('Positions/Edit', [
            'position' => $position->only(['id', 'name', 'description', 'position_family_id', 'position_family']),
            'position_families' => PositionFamily::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionUpdateRequest $request, Position $position)
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::transaction(function () use ($position, $validated, $user) {

                $previousPositionFamily = $position->position_family;

                $positionFamilyId = null;
                if (isset($validated['position_family_id'])) {
                    $positionFamilyId = $validated['position_family_id'];
                } // Create a new PositionFamily
                else if (!isset($validated['position_family_id']) && isset($validated['position_family'])) {
                    $positionFamilyId = PositionFamily::create([
                        'name' => $validated['position_family']['name'],
                        'user_id' => $user->id,
                    ])->id;
                }

                $position->update([
                    'name' => $validated['name'],
                    'description' => isset($validated['description']) ? $validated['description'] : null,
                    'position_family_id' => $positionFamilyId,
                ]);

                // If this update will orphan a position family, delete it.
                if ($previousPositionFamily) {
                    if (Position::where('position_family_id', $previousPositionFamily->id)->count() === 0 && $validated['position_family_id'] !== $previousPositionFamily->id) {
                        $previousPositionFamily->delete();
                    }
                }

            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            return Redirect::route('positions.index')->with('error', 'Error. Failed to update position.');
        }

        return Redirect::route('positions.show', $position->id)->with('message', 'Success! Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {

        // Warn user about restrictOnDelete foreign key constraints
        if ($position->incoming_figures()->count() > 0 || $position->outgoing_figures()->count() > 0) {

            $limit = config('restrict_on_delete_message_limit');
            $i = 0;
            $names = [];
            foreach ($position->incoming_figures as $figure) {
                if ($i === $limit) break;
                $names[] = $figure->name;
                $i++;
            } foreach ($position->outgoing_figures as $figure) {
                if ($i === $limit) break;
                $name = $figure->name;
                if (!in_array($name, $names)) {
                    $names[] = $name;
                    $i++;
                }
            }

            return back()->with('error', "Deleting this position is intentionally forbidden because one or more figures rely on it. You should first delete all dependent figures, then delete this position.\nThe dependent figures " . ($i === $limit ? "include " : "are ") . implode(", ", $names) . ".");
        }

        DB::transaction(function () use ($position) {
            $positionFamily = $position->position_family;
            $position->delete();

            // If this update will orphan a position family, delete it.
            if ($positionFamily && Position::where('position_family_id', $positionFamily->id)->count() === 0) {
                $positionFamily->delete();
            }
        });

        return Redirect::route('positions.index')->with('message', 'Success! Position deleted successfully.');
    }

}
