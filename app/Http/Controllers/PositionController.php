<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\PositionFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Services\PositionService;
use Inertia\Inertia;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Positions/Index', [
            'positions' => Position::getForUserWithPositionFamilies(Auth::id()),
            'position_families' => PositionFamily::getForUser(Auth::id()),
            'can_delete' => $user ? $user->can('delete', Position::class) : false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Positions/Create', [
            'position_families' => PositionFamily::getForUser(Auth::id()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePositionRequest $request, PositionService $positionService)
    {
        $positionId = $positionService->storePosition($request->validated());
        return $positionId
            ? Redirect::route('positions.show', $positionId)->with('message', 'Success! Position created successfully.')
            : back()->with('error', 'Error. Failed to create position.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position)
    {
        return Inertia::render('Positions/Show', [
            'position' => $position->withPositionFamilyAndFigures(),
            'can_create' => Auth::user() && Auth::user()->can('create', Position::class),
            'can_update' => Auth::user() && Auth::user()->can('update', $position),
            'can_delete' => Auth::user() && Auth::user()->can('delete', $position),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position)
    {
        return Inertia::render('Positions/Edit', [
            'position' => $position->withPositionFamily(),
            'position_families' => PositionFamily::getForUser(Auth::id()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionRequest $request, Position $position, PositionService $positionService)
    {
        $positionId = $positionService->updatePosition($request->validated(), $position);
        return $positionId
            ? Redirect::route('positions.show', $positionId)->with('message', 'Success! Position updated successfully.')
            : back()->with('error', 'Error. Failed to update position.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position, PositionService $positionService)
    {
        $result = $positionService->deletePosition($position);
        if ($result['success']) return Redirect::route('positions.index')->with('message', $result['message']);
        else if ($result['restricted']) return back()->with('error', $result['message']);
        else return back()->with('error', $result['message']);
    }

}
