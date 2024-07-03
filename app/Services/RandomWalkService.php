<?php
namespace App\Services;

use App\Models\Position;
use App\Models\Figure;
use App\Models\CompoundFigure;

class RandomWalkService
{
    public function getRandomWalk($length, $userId) {
        $walk = [];
        $positionProperties = ['id', 'name'];
        $figureProperties = ['id', 'name', 'from_position_id', 'to_position_id'];

        // Pick a random starting position with outgoing figures, and handle
        // the edge case where every single one of a user's positions will not
        // have outgoing figures.
        $positions = Position::where('user_id', $userId)->inRandomOrder()->get($positionProperties);
        $position = null;
        $i = 0;
        do {
            $position = $positions[$i++];
        } while (($position->outgoing_figures->count() === 0 && $position->outgoing_compound_figures->count() === 0) && $i < $positions->count());
        if ($i === $positions->count()) {
            // Abort—the user does not have any positions with outgoing figures
            return null;
        }

        $walk[] = [
            'type' => 'position',
            'payload' => $position->only($positionProperties),
        ];

        for ($i = 0; $i < $length; $i++) {
            // Find position's outgoing figures and outgoing compound figures,
            // of which there is guaranteed to be at least one.
            $figures = Figure::where('user_id', $userId)
                ->where('from_position_id', $position->id)
                ->get($figureProperties);
            $compoundFigures = CompoundFigure::where('user_id', $userId)
                ->where('from_position_id', $position->id)
                ->get($figureProperties);
            $figures = $figures->merge($compoundFigures);

            // Choose a random figure or compound figure that ends at a
            // position with outgoing figures, and handle the edge case where
            // every single figure ends in an orphaned position.
            $rand_idxs = range(0, $figures->count() - 1);
            shuffle($rand_idxs);
            $figure = null;
            $deadEnd = false;
            $j = 0;
            while (!$deadEnd) {
                $figure = $figures[$rand_idxs[$j++]];
                if ($figure->to_position->outgoing_figures->count() > 0 || $figure->to_position->outgoing_compound_figures->count() > 0) {
                    break;
                }
                if ($j === $figures->count()) $deadEnd = true;
            }

            $position = $figure->to_position;
            $walk[] = [
                'type' => $figure instanceof Figure ? "figure" : "compound_figure",
                'payload' => $figure->only($figureProperties),
            ];
            $walk[] = [
                'type' => 'position',
                'payload' => $position->only($positionProperties),
            ];

            // End early—the walk ended at a position whose outgoing figures all lead to orphaned positions
            if ($deadEnd) {
                return $walk;
            }
        }

        return $walk;
    }

}
