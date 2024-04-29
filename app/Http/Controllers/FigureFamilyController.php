<?php

namespace App\Http\Controllers;

use App\Models\FigureFamily;
use App\Http\Requests\UpdateFigureFamilyRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Services\FigureFamilyService;

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
    public function update(UpdateFigureFamilyRequest $request, FigureFamily $figureFamily, FigureFamilyService $figureFamilyService)
    {
        $figureFamilyId = $figureFamilyService->updateFigureFamily($request->validated(), $figureFamily);
        return $figureFamilyId
            ? back()->with('message', 'Success! Figure family updated successfully.')
            : back()->with('error', 'Error. Failed to update figure family.');
    }
}
