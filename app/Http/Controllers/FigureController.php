<?php

namespace App\Http\Controllers;

use App\Models\Figure;
use App\Models\FigureFamily;
use App\Models\Position;
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
        return Inertia::render('Figures/Index', [
          'figures' => Figure::with(['figure_family:id,name'])->get(['id', 'name', 'weight', 'figure_family_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Figures/Create', [
          'figure_families' => FigureFamily::all(['id', 'name']),
          'positions' => Posiiton::all(['id', 'name']),
        ]);
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
        $figure->load(['figure_family:id,name', 'from_position:id,name', 'to_position:id,name']);
        return Inertia::render('Figures/Show', [
          'figure' => $figure->only['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'from_position_id', 'from_position', 'to_position_id', 'to_position'],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Figure $figure)
    {
        $figure->load(['figure_family:id,name', 'from_position:id,name', 'to_position:id,name']);
        return Inertia::render('Figures/Show', [
          'figure' => $figure->only['id', 'name', 'description', 'weight', 'figure_family_id', 'figure_family', 'from_position_id', 'from_position', 'to_position_id', 'to_position'],
          'figure_families' => FigureFamily::all(['id', 'name']),
          'positions' => Posiiton::all(['id', 'name']),
        ]);
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
        $figure->delete();
        Redirect::route('figures.index')->with('message', 'Success! Figure deleted successfully.');
    }
}
