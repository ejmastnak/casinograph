<?php

namespace App\Http\Controllers;

use App\Http\Requests\RandomWalkRequest;
use App\Services\RandomWalkService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RandomWalkController extends Controller
{
    public function home() {
        return Inertia::render('RandomWalk/Home', [
            'length' => config('misc.random_walk.default_length'),
            'walk' => [],
            'dead_ended' => false,
            'no_valid_start_position' => false,
        ]);
    }

    public function randomWalk(RandomWalkRequest $request, RandomWalkService $randomWalkService) {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        $validated = $request->validated();
        $length = $validated['length'];
        $walk = $randomWalkService->getRandomWalk($length, $userId);
        return Inertia::render('RandomWalk/Home', [
            'length' => $length,
            'walk' => $walk,
            'no_valid_start_position' => !is_null($walk),
            'dead_ended' => count($walk) < $length,
        ]);
    }

    
}
