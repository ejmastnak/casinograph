<?php

namespace App\Http\Controllers;

use App\Models\Figure;
use Illuminate\Http\Request;
use App\Http\Requests\FigureStoreRequest;
use App\Http\Requests\FigureUpdateRequest;

class FigureController extends Controller
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
    public function store(FigureStoreRequest $request)
    {
        $validated = $request->validated();
        $figure = Figure::create([
            'name' => $validated['name'],
            'from_position_id' => $validated['from_position_id'],
            'to_position_id' => $validated['to_position_id'],
            'description' => isset($validated['description']) ? $validated['description'] : null,
            'weight' => isset($validated['weight']) ? $validated['weight'] : null,
        ]);
        return Redirect::route('figures.show', $figure->id)->with('message', 'Success! Figure created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Figure $figure)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Figure $figure)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FigureUpdateRequest $request, Figure $figure)
    {
        $validated = $request->validated();
        $figure->update([
            'name' => $validated['name'],
            'from_position_id' => $validated['from_position_id'],
            'to_position_id' => $validated['to_position_id'],
            'description' => isset($validated['description']) ? $validated['description'] : null,
            'weight' => isset($validated['weight']) ? $validated['weight'] : null,
        ]);
        return Redirect::route('figures.show', $figure->id)->with('message', 'Success! Figure updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Figure $figure)
    {
        //
    }
}
