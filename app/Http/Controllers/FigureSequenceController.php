<?php

namespace App\Http\Controllers;

use App\Models\Figure;
use App\Models\FigureFamily;
use App\Http\Requests\FigureSequenceRequest;
use App\Services\FigureSequenceService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class FigureSequenceController extends Controller
{
    public function home() {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        return Inertia::render('FigureSequence/Home', [
            'figures' => Figure::getWithOnlyPositionsForUser($userId),
            'figure_families' => FigureFamily::getForUser($userId),
            'length' => config('misc.figure_sequence.default_length'),
            'figure_sequence' => [],
            'dead_ended' => false,
            'no_valid_start_position' => false,
        ]);
    }

    public function figureSequence(FigureSequenceRequest $request, FigureSequenceService $figureSequenceService) {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        $validated = $request->validated();
        $figureSequence = $figureSequenceService->getFigureSequence($userId, $validated);
        return Response::json([
            'length' => $validated['length'],
            'figure_sequence' => $figureSequence,
            'no_valid_start_position' => is_null($figureSequence),
            'dead_ended' => $figureSequence && (count($figureSequence) < $validated['length']),
        ]);
    }

}
