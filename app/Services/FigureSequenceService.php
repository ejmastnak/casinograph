<?php
namespace App\Services;

use App\Models\Position;
use App\Models\Figure;
use App\Models\CompoundFigure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class FigureSequenceService
{
    public function getFigureSequence($userId, $data) {
        $length = $data['length'];
        $excludedFigureIds = $data['excluded_figure_ids'];
        $excludedFigureFamilyIds = $data['excluded_figure_family_ids'];

        $figures = $this->getFiguresAsArray($userId, $excludedFigureIds, $excludedFigureFamilyIds);
        if (empty($figures)) return null;

        $adjList = [];
        $sccList = $this->findSCCs($figures, $adjList);
        $maxIdx = 0;
        $maxCount = $sccList[$maxIdx]['count'];
        for ($i = 1; $i < count($sccList); $i++) {
            if ($sccList[$i]['count'] > $maxCount) {
                $maxCount = $sccList[$i]['count'];
                $maxIdx = $i;
            }
        }
        $scc = $sccList[$maxIdx];

        /**
         *  Build an adjacency-list representation `$sccAdjList` of `$scc` in
         *  which `$sccAdjList[$position_id]` is an array of all figures
         *  outbound from `$position_id` *that end in figures also in the SCC*.
         */
        $sccAdjList = [];
        foreach ($scc['position_ids'] as $positionId) {
            $sccAdjList[$positionId] = [
                'from_position_name' => $adjList[$positionId]['from_position_name'],
                'adj' => [],
            ];

            foreach ($adjList[$positionId]['adj'] as $figure) {
                if (in_array($figure['to_position_id'], $scc['position_ids'])) {
                    $sccAdjList[$positionId]['adj'][] = $figure;
                }
            }
        }

        // Edge case: the SCC is a single position...
        $singleSelfLoop = false;
        if (count($sccAdjList) === 1) {
            // ...with no outgoing figures that return to the SCC
            if (count($sccAdjList[array_key_first($sccAdjList)]['adj']) === 0) return null;
            // ...with only a single, self-looping figure. This is to protect
            // against infinite loops when applying max repeated figure limit.
            else if (count($sccAdjList[array_key_first($sccAdjList)]['adj']) === 1) $singleSelfLoop = true;
        }


        // Choose a random starting position
        $nextPositionId = array_rand($sccAdjList, 1);

        // Construct figure sequence
        $figureSequence = [];
        $repeatedFigures = 0;
        $prevFigureId = null;
        for ($i = 0; $i < $length; $i++) {

            // Choose a random next figure, avoiding excessively repeated figures
            $justInCase = 0;
            do {
                $figIdx = array_rand($sccAdjList[$nextPositionId]['adj'], 1);
                $currentFigure = $sccAdjList[$nextPositionId]['adj'][$figIdx];
                if ($prevFigureId === $currentFigure['figure_id']) $repeatedFigures++;
                else $repeatedFigures = 0;
                $justInCase++;
            } while ($repeatedFigures > config('misc.figure_sequence.max_repeated_figures') && !$singleSelfLoop && $justInCase <= config('misc.figure_sequence.infinite_loop_guard'));
            $prevFigureId = $currentFigure;

            if ($justInCase > config('misc.figure_sequence.infinite_loop_guard')) {
                Log::warning("Unexpectedly hit infinite loop guard when avoiding excessively repeated figures when generating Figure sequence. Printing sequence so far:");
                Log::warning($figureSequence);
            }

            $figureSequence[] = [
                'figure_id' => $currentFigure['figure_id'],
                'figure_name' => $currentFigure['figure_name'],
                'from_position_id' => $nextPositionId,
                'from_position_name' => $sccAdjList[$nextPositionId]['from_position_name'],
                'to_position_id' => $currentFigure['to_position_id'],
                'to_position_name' => $currentFigure['to_position_name'],
            ];

            $nextPositionId = $currentFigure['to_position_id'];
        }

        return $figureSequence;
    }

    /**
     *  Input an array `$figures` of figures and empty array to hold adjacency
     *  list representation of `$figures`.

     *  Output: a list `$sccs` of SCCs in the CasinoGraph formed by `$figures`,
     *  where each SCC is represented by an associate array with two keys:
     *
     *    - `count`: the number of positions in the SCC
     *    - `postion_ids`: a list of position ids for all positions in that SCC.
     * 
     *  Side effect: fills `$adjList` with an adjacency list representation of
     *  `$figures`.
     */
    private function findSCCs($figures, &$adjList) {
        $index = [];
        $lowLink = [];
        $onStack = [];
        $stack = [];
        $sccList = [];
        $currentIndex = 0;

        // Initialize variables and build adjacency list
        foreach ($figures as $figure) {
            $fromPositionId = $figure['from_position_id'];
            $toPositionId = $figure['to_position_id'];

            if (!isset($adjList[$fromPositionId])) {
                $adjList[$fromPositionId] = [
                    'from_position_name' => $figure['from_position']['name'],
                    'adj' => [],
                ];
            }

            $adjList[$fromPositionId]['adj'][] = [
                'figure_id' => $figure['id'],
                'figure_name' => $figure['name'],
                'to_position_id' => $toPositionId,
                'to_position_name' => $figure['to_position']['name'],
            ];

            if (!isset($index[$fromPositionId])) {
                $index[$fromPositionId] = -1;
                $lowLink[$fromPositionId] = null;
                $onStack[$fromPositionId] = false;
            }

            // Ensure all positions (even isolated ones) are initialized
            if (!isset($index[$toPositionId])) {
                $index[$toPositionId] = -1;
                $lowLink[$toPositionId] = null;
                $onStack[$toPositionId] = false;
            }
        }

        // Run DFS from each position that hasn't been visited yet
        foreach (array_keys($index) as $positionId) {
            if ($index[$positionId] == -1) $this->tarjanSCC($positionId, $adjList, $index, $lowLink, $onStack, $stack, $sccList, $currentIndex);
        }

        return $sccList;
    }

    private function tarjanSCC($positionId, &$adjList, &$index, &$lowLink, &$onStack, &$stack, &$sccList, &$currentIndex) {
        $index[$positionId] = $currentIndex;
        $lowLink[$positionId] = $currentIndex;
        $currentIndex++;
        array_push($stack, $positionId);
        $onStack[$positionId] = true;

        if (isset($adjList[$positionId])) {
            foreach ($adjList[$positionId]['adj'] as $figure) {
                $toPositionId = $figure['to_position_id'];
                if ($index[$toPositionId] === -1) {  // If neighbor is unvisited
                    $this->tarjanSCC($toPositionId, $adjList, $index, $lowLink, $onStack, $stack, $sccList, $currentIndex);
                    $lowLink[$positionId] = min($lowLink[$positionId], $lowLink[$toPositionId]);
                } else if ($onStack[$toPositionId]) {  // Back-edge
                    $lowLink[$positionId] = min($lowLink[$positionId], $index[$toPositionId]);
                }
            }
        }

        if ($lowLink[$positionId] == $index[$positionId]) {
            $currentSCC = [
                'count' => 0,
                'position_ids' => [],
            ];
            while (true) {
                $poppedPositionId = array_pop($stack);
                $onStack[$poppedPositionId] = false;
                $currentSCC['position_ids'][] = $poppedPositionId;
                $currentSCC['count'] += 1;
                if ($poppedPositionId == $positionId) break;
            }
            $sccList[] = $currentSCC;
        }
    }

    /**
     *  All figures satisfying given filters with at least one outgoi
     */
    private function getFiguresAsArray($userId, $excludedFigureIds, $excludedFigureFamilyIds) {
        return Figure::where('user_id', $userId)
            ->when(!empty($excludedFigureIds), function (Builder $query) use ($excludedFigureIds) {
                $query->whereNotIn('id', $excludedFigureIds);
            })
            ->when(!empty($excludedFigureFamilyIds), function (Builder $query) use ($excludedFigureFamilyIds) {
                $query->whereNotIn('figure_family_id', $excludedFigureFamilyIds);
            })
            ->with(['from_position:id,name', 'to_position:id,name'])
            ->get(['id', 'name', 'from_position_id', 'to_position_id',])->toArray();
    }

}
