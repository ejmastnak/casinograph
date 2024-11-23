<?php
namespace App\Services;

use App\Models\Position;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

class PositionGraphService
{

    /**
     *  Generates an SVG graph diagram for the given position, and returns the
     *  X and Y coordinates of the root position node (used by frontend to
     *  center SVG around root node).
     */
    public function generatePositionGraph(Position $position) {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        $key = 'generate-position-graph-'.$userId.'-'.$position->id;
        $focusedNodeCoordinates = config('misc.graphs.fallback_focus_coordinates');

        $executed = RateLimiter::attempt(
            $key,
            $perMinute = config('constants.rate_limits.position_graph_per_minute'),
            function() use ($position, &$focusedNodeCoordinates) {
                $focusedNodeCoordinates = $this->generatePositionGraphHandler($position);
            }
        );

        if ($executed) Cache::put($key, $focusedNodeCoordinates);
        else $focusedNodeCoordinates = Cache::get($key, config('misc.graphs.fallback_focus_coordinates'));

        return $focusedNodeCoordinates;
    }

    private function generatePositionGraphHandler(Position $position) {
        $userId = $position->user_id;

        $incomingFigures = $this->getIncomingFigures($position->id, $userId);
        $outgoingFigures = $this->getOutgoingFigures($position->id, $userId);

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/positiongraph-{$position->id}-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $position->id, $incomingFigures, $outgoingFigures);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            positionGraphStoragePathForUser($position->id, $userId),
        ];
        // Finds XY coordinates of root node in SVG
        $grepCommandWithParams = [
            'grep',
            '-m',
            '1',
            config('misc.graphs.position_graph.grep'),
            positionGraphStoragePathForUser($position->id, $userId),
        ];
        $cleanupCommandWithParams = [ 'rm', '-f', $tmpDotFile ];

        $dotResult = Process::run(implode(' ', $dotCommandWithParams));
        $grepResult = Process::run(implode(' ', $grepCommandWithParams));
        $cleanupResult = Process::run(implode(' ', $cleanupCommandWithParams));

        # Extracts values of cx and cy attributes from a string like
        # <ellipse fill="#bfdbfe" stroke="#172554" cx="633.28" cy="-96.16" rx="47.91" ry="23.16"/>
        if ($grepResult->successful()) {
            $pattern = '/\bcx="([\d.-]+)" cy="([\d.-]+)"/';
            $matches = [];
            if (preg_match($pattern, $grepResult->output(), $matches)) {
                $x = $matches[1];
                $y = $matches[2];
            } else {
                $x = "0";
                $y = "0";
            }
        } else {
            $x = "0";
            $y = "0";
        }

        if ($dotResult->failed()) {
            Log::error("RegeneratePositionGraph failed.\n");
            Log::error($dotResult->errorOutput());
            if (\App::environment('local')) {
                dd($dotResult->errorOutput());
            }
        }

        return [
            'x' => $x,
            'y' => $y,
        ];
    }


    private function writeDotFile($tmpDotFile, $rootPositionId, $incomingFigures, $outgoingFigures) {
        $file = fopen($tmpDotFile, "w");
        $INDENT = "  ";

        $digraphOpen = 'digraph PositionGraph {';
        $graphConfig = "graph [".prepareStringFromConfigArray(config('misc.graphs.position_graph.config.graph'))."];";
        $nodeConfig = "node [".prepareStringFromConfigArray(config('misc.graphs.position_graph.config.node'))."];";
        $edgeConfig = "edge [".prepareStringFromConfigArray(config('misc.graphs.position_graph.config.edge'))."];";
        $digraphClose = '}';

        if ($file) {
            fwrite($file, $digraphOpen . PHP_EOL);

            fwrite($file, PHP_EOL);
            fwrite($file, $INDENT . $graphConfig . PHP_EOL);
            fwrite($file, $INDENT . $nodeConfig . PHP_EOL);
            fwrite($file, $INDENT . $edgeConfig . PHP_EOL);

            # Node for root position. Intentionally given node id zero to
            # distinguish it from the from/to positions of incoming/outgoing
            # figures.
            $rootNodeId = 0;
            $rootPosition = Position::find($rootPositionId);
            fwrite($file, PHP_EOL);
            $line = "{$INDENT}{$rootNodeId} [label=\"{$rootPosition->name}\", URL=\"/positions/{$rootPosition->id}\", " . prepareStringFromConfigArray(config('misc.graphs.position_graph.config.root_node')) . "];";
            fwrite($file, $line . PHP_EOL);

            # Nodes for from positions of incoming figures. Intentionally given
            # negative node id to distinguish them from the to positions of
            # outgoing figures.
            fwrite($file, PHP_EOL);
            foreach ($incomingFigures as $figure) {
                $line = "{$INDENT}-{$figure->from_position_id} [label=\"{$figure->from_position_name}\", URL=\"/positions/{$figure->from_position_id}\"];";
                fwrite($file, $line . PHP_EOL);
            }

            # Nodes for to positions of outgoing figures
            fwrite($file, PHP_EOL);
            foreach ($outgoingFigures as $figure) {
                $line = "{$INDENT}{$figure->to_position_id} [label=\"{$figure->to_position_name}\", URL=\"/positions/{$figure->to_position_id}\"];";
                fwrite($file, $line . PHP_EOL);
            }

            # Edges for incoming figures
            fwrite($file, PHP_EOL);
            foreach ($incomingFigures as $figure) {
                $line = "{$INDENT}-{$figure->from_position_id} -> {$rootNodeId} [label=\"{$figure->figure_name} \", URL=\"/figures/{$figure->figure_id}\"];";
                fwrite($file, $line . PHP_EOL);
            }

            # Edges for outgoing figures
            fwrite($file, PHP_EOL);
            foreach ($outgoingFigures as $figure) {
                $line = "{$INDENT}{$rootNodeId} -> {$figure->to_position_id} [label=\"{$figure->figure_name} \", URL=\"/figures/{$figure->figure_id}\"];";
                fwrite($file, $line . PHP_EOL);
            }

            fwrite($file, PHP_EOL);
            fwrite($file, $digraphClose . PHP_EOL);
            fclose($file);

        } else {
            Log::error("Error opening file {$tmpDotFile}.");
        }
    }

    /**
     *  Selects all figures entering the position with id `$positionId` and
     *  loads the figures' from positions.
     */
    private function getIncomingFigures($positionId, $userId) {
        $query = "
        select
        figures.id as figure_id,
        figures.name as figure_name,
        figures.from_position_id as from_position_id,
        positions.name as from_position_name
        from figures
        inner join positions
        on positions.id
        = figures.from_position_id
        where figures.to_position_id = :position_id
        and figures.user_id = :user_id
        order by figures.name;";
        $incomingFigures = DB::select($query, [
            'position_id' => $positionId,
            'user_id' => ($userId ?? config('constants.user_ids.casino')),
        ]);
        return $incomingFigures;
    }

    /**
     *  Selects all figures leaving the position with id `$positionId` and
     *  loads the figures' to positions.
     */
    private function getOutgoingFigures($positionId, $userId) {
        $query = "
        select
        figures.id as figure_id,
        figures.name as figure_name,
        figures.to_position_id as to_position_id,
        positions.name as to_position_name
        from figures
        inner join positions
        on positions.id
        = figures.to_position_id
        where figures.from_position_id = :position_id
        and figures.user_id = :user_id
        order by figures.name;";
        $outgoingFigures = DB::select($query, [
            'position_id' => $positionId,
            'user_id' => ($userId ?? config('constants.user_ids.casino')),
        ]);
        return $outgoingFigures;
    }

}
