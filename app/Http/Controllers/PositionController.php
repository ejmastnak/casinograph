<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\PositionStoreRequest;
use App\Http\Requests\PositionUpdateRequest;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionStoreRequest $request)
    {
        $validated = $request->validated();
        $position = Position::create([
            'name' => $validated['name'],
            'description' => isset($validated['description']) ? $validated['description'] : null,
        ]);
        return Redirect::route('positions.show', $position->id)->with('message', 'Success! Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        //
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
        //
    }
}
