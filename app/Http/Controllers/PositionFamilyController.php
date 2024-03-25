<?php

namespace App\Http\Controllers;

use App\Models\PositionFamily;
use App\Http\Requests\UpdatePositionFamilyRequest;
use App\Services\PositionFamilyService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PositionFamilyController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(PositionFamily $positionFamily)
    {
        $user = Auth::user();
        return Inertia::render('PositionFamilies/Show', [
            'position_family' => $positionFamily->withPositions(),
            'can_update' => $user ? $user->can('update', $positionFamily) : false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionFamilyRequest $request, PositionFamily $positionFamily, PositionFamilyService $positionFamilyService)
    {
        $positionFamilyId = $positionFamilyService->updatePositionFamily($request->validated, $positionFamily);
        return $positionFamilyId
            ? back()->with('message', 'Success! Position family updated successfully.')
            : back()->with('error', 'Error. Failed to update position family.');
    }
}
