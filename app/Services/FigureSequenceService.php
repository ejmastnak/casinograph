<?php
namespace App\Services;

use App\Models\Position;
use App\Models\Figure;
use App\Models\CompoundFigure;
use Illuminate\Database\Eloquent\Builder;

class FigureSequenceService
{
    public function getFigureSequence($userId, $data) {
        $length = $data['length'];
        $excludedFigureIds = $data['excluded_figure_ids'];
        $excludedFigureFamilyIds = $data['excluded_figure_family_ids'];

        // All figures matching filters and whose ending position has at least
        // one outgoing figure.
        $figures = Figure::where('user_id', $userId)
            ->when(!empty($excludedFigureIds), function (Builder $query) use ($excludedFigureIds) {
                $query->whereNotIn('id', $excludedFigureIds);
            })
            ->when(!empty($excludedFigureFamilyIds), function (Builder $query) use ($excludedFigureFamilyIds) {
                $query->whereNotIn('figure_family_id', $excludedFigureFamilyIds);
            })
            ->with(['from_position:id,name', 'to_position:id,name'])
            ->get(['id', 'name', 'from_position_id', 'to_position_id',])->toArray();

        if (empty($figures)) return null;

        // TODO: preserve only positions that are part of cycles, so that figure
        // sequence is guaranteed not to dead-end.

        // Builds an adjacency-list graph representation where
        // `$adjList[$position_id]` contains a list of all figures outbound
        // from the Position with `$position_id`.
        $adjList = [];
        foreach ($figures as $figure) $adjList[$figure['from_position_id']][] = $figure;

        // Choose a random starting figure
        $figureSequence = [];
        $currentFigure = $figures[array_rand($figures, 1)];
        $figureSequence[] = $currentFigure;

        for ($i = 1; $i < $length; $i++) {
            $nextPositionId = $currentFigure['to_position_id'];

            // Edge case: current figure ends in a position with no outgoing figures
            if (!array_key_exists($nextPositionId, $adjList)) return $figureSequence;

            $outgoingFigures = $adjList[$nextPositionId];

            // Edge case: dead end!
            if (count($outgoingFigures) == 0) return $figureSequence;

            $currentFigure = $outgoingFigures[array_rand($outgoingFigures, 1)];
            $figureSequence[] = $currentFigure;
        }

        return $figureSequence;
    }

}
