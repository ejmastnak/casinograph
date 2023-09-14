<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\PositionFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
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
            'positions' => Position::with('position_family:id,name')->get(['id', 'name', 'position_family_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Positions/Create', [
            'position_families' => PositionFamily::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionStoreRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        DB::transaction(function () use ($validated, $user) {

            $position_family_id = null;
            if (isset($validated['position_family_id'])) {
                $position_family_id = $validated['position_family_id'];
            } // Create a new PositionFamily
            else if (!isset($validated['position_family_id']) && isset($validated['position_family'])) {
                $position_family_id = PositionFamily::create([
                    'name' => $validated['position_family']['name'],
                    'user_id' => $user->id,
                ])->id;
            }

            $position = Position::create([
                'name' => $validated['name'],
                'description' => isset($validated['description']) ? $validated['description'] : null,
                'position_family_id' => $position_family_id,
                'user_id' => $user ? $user->id : null,
            ]);

        });

        return Redirect::route('positions.show', $position->id)->with('message', 'Success! Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        $position->load(['position_family:id,name']);
        return Inertia::render('Positions/Show', [
            'position' => $position->only(['id', 'name', 'description', 'position_family_id', 'position_family']),
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
            'position_families' => PositionFamily::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionUpdateRequest $request, Position $position)
    {
        $validated = $request->validated();
        $user = Auth::user();

        DB::transaction(function () use ($validated, $user) {

            $previous_position_family = $position->position_family;

            $position_family_id = null;
            if (isset($validated['position_family_id'])) {
                $position_family_id = $validated['position_family_id'];
            } // Create a new PositionFamily
            else if (!isset($validated['position_family_id']) && isset($validated['position_family'])) {
                $position_family_id = PositionFamily::create([
                    'name' => $validated['position_family']['name'],
                    'user_id' => $user->id,
                ])->id;
            }

            $position->update([
                'name' => $validated['name'],
                'description' => isset($validated['description']) ? $validated['description'] : null,
                'position_family_id' => $position_family_id,
            ]);

            // If this update will orphan a position family, delete it.
            // Note === 1, not === 0, because the transaction is not commited yet.
            if ($previous_position_family) {
                if (count(Position::where('position_family_id', $previous_position_family->id)) === 1 && $validated['position_family_id'] !== $previous_position_family->id) {
                    $previous_position_family->delete();
                }
            }

        });

        return Redirect::route('positions.show', $position->id)->with('message', 'Success! Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        DB::transaction(function () use ($position) {
            // If this update will orphan a position family, delete it.
            $position_family = $position->position_family;
            if ($position_family && count(Position::where('position_family_id', $position_family->id)) === 1) {
                $position_family->delete();
            }
            $position->delete();
        });

        Redirect::route('positions.index')->with('message', 'Success! Position deleted successfully.');
    }
}
