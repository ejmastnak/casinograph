<?php
namespace App\Services;

use App\Models\Figure;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CasinoGraphService
{

    public function generateCasinoGraph() {
        $userId = Auth::id() ?? config('constants.user_ids.casino');
        $focusedNodeCoordinates = [  // safe default values
            'x' => 0,
            'y' => 0,
        ];

        $userHasFigures = Figure::where('user_id', $userId)->count() > 0;
        if ($userHasFigures) {
            $executed = RateLimiter::attempt(
                'generate-casino-graph-'.$userId,
                $perMinute = config('constants.rate_limits.casino_graph_per_minute'),
                function() use (&$focusedNodeCoordinates) {
                    $focusedNodeCoordinates = $this->generateCasinoGraphHandler();
                }
            );
        }

        return $focusedNodeCoordinates;
    }

    /**
     *  Given an array of figures with `[id, name, from_position,
     *  to_position]`, generates and saves an SVG of the corresponding
     *  CasinoGraph at the provided $svgOutputPath.
     */
    private function generateCasinoGraphHandler() {

        $userId = Auth::id();
        $positions = $this->getPositions($userId);
        $figures = $this->getFigures($userId);
        $svgOutputPath = casinoGraphStoragePathForUser($userId);

        # Identify position with the most incoming figures.
        # Frontend will focus SVG around this position.
        $incomingEdgeCounts = [];
        foreach ($positions as $position) $incomingEdgeCounts[$position->id] = 0;
        foreach ($figures as $figure) $incomingEdgeCounts[$figure->to_position_id]++;
        $maxCount = 0;
        $focusedPositionId = null;
        foreach ($incomingEdgeCounts as $positionId => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $focusedPositionId = $positionId;
            }
        }

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/casinograph-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $positions, $figures);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            $svgOutputPath,
        ];

        // Finds XY coordinates of focused node in SVG
        $grepCommandWithParams = [
            'grep',
            '-m',
            '1',
            '-A',
            config('misc.graphs.casinograph.grep.after'),
            # searches for e.g. 'id="a_node42"'
            'id=\"a_node' . $focusedPositionId . '\"',
            $svgOutputPath,
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
            Log::error("CasinoGraphService->generateCasinoGraphHandler failed.\n");
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

    /**
     *  Designed to leave out orphaned positions.
     */
    private function getPositions($userId) {
        $positionQuery = "
        select
        positions.id,
        positions.name
        from positions
        inner join figures
        on figures.from_position_id
        = positions.id
        or figures.to_position_id
        = positions.id 
        where positions.user_id = :user_id
        group by positions.id; ";
        $positions = DB::select($positionQuery, ['user_id' => ($userId ?? config('constants.user_ids.casino'))]);
        return $positions;
    }

    /**
     *  Designed to only choose one figure between any two nodes, to avoid
     *  overcrowding the graph. The figure is chosen randomly, so the graph
     *  should change on every regeneration... to keep things interesting!
     */
    private function getFigures($userId) {
        $figuresQuery = "
        select
        *
        from (
        select
        id,
        from_position_id,
        to_position_id,
        name
        from figures
        where figures.user_id = :user_id
        order by random()
        )
        group by from_position_id, to_position_id;
        ";
        $figures = DB::select($figuresQuery, ['user_id' => ($userId ?? config('constants.user_ids.casino'))]);
        return $figures;
    }

    /**
     *  Creates a temporary file representing the CasinoGraph digraph. This file is used as input to `dot` to generate the final SVG, and is then deleted
     */
    private function writeDotFile($tmpDotFile, $positions, $figures) {
        $file = fopen($tmpDotFile, "w");
        $INDENT = "  ";

        $digraphOpen = 'digraph CasinoGraph {';
        $graphConfig = "graph [".prepareStringFromConfigArray(config('misc.graphs.casinograph.config.graph'))."];";
        $nodeConfig = "node [".prepareStringFromConfigArray(config('misc.graphs.casinograph.config.node'))."];";
        $edgeConfig = "edge [".prepareStringFromConfigArray(config('misc.graphs.casinograph.config.edge'))."];";
        $digraphClose = '}';

        if ($file) {
            fwrite($file, $digraphOpen . PHP_EOL);

            fwrite($file, PHP_EOL);
            fwrite($file, $INDENT . $graphConfig . PHP_EOL);
            fwrite($file, $INDENT . $nodeConfig . PHP_EOL);
            fwrite($file, $INDENT . $edgeConfig . PHP_EOL);

            # Write positions
            fwrite($file, PHP_EOL);
            foreach ($positions as $position) {
                $line = "{$INDENT}{$position->id} [label=\"{$position->name}\", URL=\"/positions/{$position->id}\"];";
                fwrite($file, $line . PHP_EOL);
            }

            # Write figures
            fwrite($file, PHP_EOL);
            foreach ($figures as $figure) {
                $line = "{$INDENT}{$figure->from_position_id} -> {$figure->to_position_id} [label=\"{$figure->name} \", URL=\"/figures/{$figure->id}\"];";
                fwrite($file, $line . PHP_EOL);
            }

            fwrite($file, PHP_EOL);
            fwrite($file, $digraphClose . PHP_EOL);
            fclose($file);

        } else {
            Log::error("Error opening file {$tmpDotFile}.");
        }
    }

}
