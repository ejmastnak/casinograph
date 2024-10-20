<?php
namespace App\Services;

use App\Models\Position;
use App\Models\Figure;
use App\Models\CompoundFigure;

class RandomWalkService
{
    public function getRandomWalk($userId, $length, $figureIdsToExclude, $figureFamilyIdsToExclude) {

        // All figures matching filters and whose ending position has at least
        // one outgoing figure.
        $figures = Figure::where('user_id', $userId)
            ->when(!empty($figureIdsToExclude), function (Builder $query, string $role) {
                $query->whereNotIn('id', $figureIdsToExclude);
            })
            ->when(!empty($figureFamilyIdsToExclude), function (Builder $query, string $role) {
                $query->whereNotIn('figure_family_id', $figureFamilyIdsToExclude);
            })
            ->with(['from_position:id,name', 'to_position:id,name'])
            ->get(['id', 'name', 'from_position_id', 'to_position_id',])->toArray();

        if (empty($figures)) return null;

        // TODO: preserve only positions that are part of cycles, so that figure
        // sequence is guaranteed not to dead-end.

        // Builds a positionId-indexed associative array of "adjacency lists"
        // of the figures exiting each position
        $vertices = [];
        foreach ($figures as $figure) $vertices[$figure['from_position_id']][] = $figure;

        // Choose a random starting figure
        $figureSequence = [];
        $currentFigure = $figures[array_rand($figures, 1)];
        $figureSequence[] = $currentFigure;

        for ($i = 1; $i < $length; $i++) {
            $nextPositionId = $currentFigure['to_position_id'];

            // Edge case: current figure ends in a position with no outgoing figures
            if (!array_key_exists($nextPositionId, $vertices)) return $figureSequence;

            $outgoingFigures = $vertices[$nextPositionId];

            // Edge case: dead end!
            if (count($outgoingFigures) == 0) return $figureSequence;
            
            $currentFigure = $outgoingFigures[array_rand($outgoingFigures, 1)];
            $figureSequence[] = $currentFigure;
        }
        
        return $figureSequence;
    }

}
