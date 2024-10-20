<?php

namespace App\Http\Controllers;

use App\Models\Figure;
use App\Models\FigureFamily;
use App\Http\Requests\RandomWalkRequest;
use App\Services\RandomWalkService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class RandomWalkController extends Controller
{
    public function home() {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        return Inertia::render('RandomWalk/Home', [
            'figures' => Figure::getWithOnlyPositionsForUser($userId),
            'figure_families' => FigureFamily::getForUser($userId),
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

        $walk = $randomWalkService->getRandomWalk($userId, $length, [], []);

        return Inertia::render('RandomWalk/Home', [
            'figures' => Figure::getWithOnlyPositionsForUser($userId),
            'figure_families' => FigureFamily::getForUser($userId),
            'length' => $length,
            'walk' => $walk,
            'no_valid_start_position' => is_null($walk),
            'dead_ended' => $walk && (count($walk) < $length),
        ]);
    }

}
