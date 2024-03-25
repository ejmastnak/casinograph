<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePositionFamilyRequest;
use App\Models\PositionFamily;
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
    public function update(UpdatePositionFamilyRequest $request, PositionFamily $positionFamily)
    {
        $validated = $request->validated();
        $positionFamily->update(['name' => $validated['name']]);
        return back()->with('message', 'Success! Position family renamed successfully.');
    }
}
