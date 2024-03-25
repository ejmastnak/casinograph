<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFigureFamilyRequest;
use App\Models\FigureFamily;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class FigureFamilyController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(FigureFamily $figureFamily)
    {
        $user = Auth::user();
        return Inertia::render('FigureFamilies/Show', [
            'figure_family' => $figureFamily->withFiguresAndCompoundFigures(),
            'can_update' => $user ? $user->can('update', $figureFamily) : false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFigureFamilyRequest $request, FigureFamily $figureFamily)
    {
        $validated = $request->validated();
        $figureFamily->update(['name' => $validated['name']]);
        return back()->with('message', 'Success! Figure family renamed successfully.');
    }
}
