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
        return Inertia::render('Position/Create', [
            'posiiton_families' => PositionFamily::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionStoreRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        $position = Position::create([
            'name' => $validated['name'],
            'description' => isset($validated['description']) ? $validated['description'] : null,
            'user_id' => $user ? $user->id : null,
        ]);

        return Redirect::route('positions.show', $position->id)->with('message', 'Success! Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        $position->load(['position_family']);
        return Inertia::render('Position/Show', [
            'position' => $position->only(['id', 'name', 'description', 'position_family_id', 'position_family']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        $position->load(['position_family']);
        return Inertia::render('Position/Edit', [
            'position' => $position->only(['id', 'name', 'description', 'position_family_id', 'position_family']),
            'posiiton_families' => PositionFamily::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionUpdateRequest $request, Position $position)
    {
        $validated = $request->validated();
        $position->update([
            'name' => $validated['name'],
            'description' => isset($validated['description']) ? $validated['description'] : null,
        ]);
        return Redirect::route('positions.show', $position->id)->with('message', 'Success! Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position)
    {
        $position->delete();
        Redirect::route('positions.index')->with('message', 'Success! Position deleted successfully.');
    }
}
