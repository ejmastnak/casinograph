<?php

namespace App\Http\Controllers;

use App\Models\Figure;
use App\Models\FigureFamily;
use App\Http\Requests\FigureSequenceRequest;
use App\Services\FigureSequenceService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class FigureSequenceController extends Controller
{
    public function home() {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        return Inertia::render('FigureSequence/Home', [
            'figures' => Figure::getWithOnlyPositionsForUser($userId),
            'figure_families' => FigureFamily::getForUser($userId),
            'length' => config('misc.figure_sequence.default_length'),
            'walk' => [],
            'dead_ended' => false,
            'no_valid_start_position' => false,
        ]);
    }

    public function figureSequence(FigureSequenceRequest $request, FigureSequenceService $figureSequenceService) {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        $validated = $request->validated();
        $length = $validated['length'];

        $walk = $figureSequenceService->getFigureSequence($userId, $length, [], []);

        return Inertia::render('FigureSequence/Home', [
            'figures' => Figure::getWithOnlyPositionsForUser($userId),
            'figure_families' => FigureFamily::getForUser($userId),
            'length' => $length,
            'walk' => $walk,
            'no_valid_start_position' => is_null($walk),
            'dead_ended' => $walk && (count($walk) < $length),
        ]);
    }

}
