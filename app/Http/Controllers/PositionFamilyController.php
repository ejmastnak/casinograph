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
        $userId = $user ? $user->id : null;

        return Inertia::render('PositionFamilies/Show', [
            'position_family' => $positionFamily->load([
                'positions:id,name,position_family_id',
            ])->only(['id', 'name', 'positions']),
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
