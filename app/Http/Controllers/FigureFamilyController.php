<?php

namespace App\Http\Controllers;

use App\Http\Requests\FigureFamilyUpdateRequest;
use App\Models\FigureFamily;
use Illuminate\Support\Facades\Auth;

class FigureFamilyController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(FigureFamily $figureFamily)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        return Inertia::render('FigureFamilies/Show', [
            'figure' => $figureFamily->load([
                'figures:id,name,figure_family_id,from_position_id,to_position_id',
                'figures.from_position:id,name',
                'figures.to_position:id,name',
            ])->only(['id', 'name', 'figures']),
            'can_update' => $user ? $user->can('update', $figureFamily) : false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FigureFamilyUpdateRequest $request, FigureFamily $figureFamily)
    {
        $validated = $request->validated();
        $figureFamily->update(['name' => $validated['name']]);
        return back()->with('message', 'Success! Figure family renamed successfully.');
    }
}
