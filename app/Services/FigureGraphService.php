<?php
namespace App\Services;

use App\Models\Figure;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class FigureGraphService
{

    /**
     *  Generates an SVG graph diagram for the given figure.
     */
    public function generateFigureGraph(Figure $figure) {
        $user = Auth::id() ?? 'public';
        $executed = RateLimiter::attempt(
            'generate-figure-graph-'.$user,
            $perMinute = config('constants.rate_limits.figure_graph_per_minute'),
            function() use ($figure) {
                $this->generateFigureGraphHandler($figure);
            }
        );
    }

    private function generateFigureGraphHandler(Figure $figure) {
        $userId = $figure->user_id;
        $fromPosition = $figure->from_position;
        $toPosition = $figure->to_position;

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/figuregraph-{$figure->id}-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $figure, $fromPosition, $toPosition);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            figureGraphStoragePathForUser($figure->id, $userId),
        ];
        $cleanupCommandWithParams = [ 'rm', '-f', $tmpDotFile ];

        $result = Process::run(implode(' ', $dotCommandWithParams));
        $cleanup = Process::run(implode(' ', $cleanupCommandWithParams));

        if ($result->failed()) {
            Log::error("RegenerateFigureGraph failed.\n");
            Log::error($result->errorOutput());
            if (\App::environment('local')) {
                dd($result->errorOutput());
            }
        }
    }

    private function writeDotFile($tmpDotFile, $figure, $fromPosition, $toPosition) {
        $file = fopen($tmpDotFile, "w");
        $INDENT = "  ";

        $digraphOpen = 'digraph FigureGraph {';
        $graphConfig = "graph [".prepareStringFromConfigArray(config('misc.graphs.figure_graph.config.graph'))."];";
        $nodeConfig = "node [".prepareStringFromConfigArray(config('misc.graphs.figure_graph.config.node'))."];";
        $edgeConfig = "edge [".prepareStringFromConfigArray(config('misc.graphs.figure_graph.config.edge'))."];";
        $digraphClose = '}';

        if ($file) {
            fwrite($file, $digraphOpen . PHP_EOL);

            fwrite($file, PHP_EOL);
            fwrite($file, $INDENT . $graphConfig . PHP_EOL);
            fwrite($file, $INDENT . $nodeConfig . PHP_EOL);
            fwrite($file, $INDENT . $edgeConfig . PHP_EOL);

            # Node for from position. Negative is intentional to force
            # Graphviz to draw separate nodes for from and to positions for
            # self-referencing figures.
            fwrite($file, PHP_EOL);
            $line = "{$INDENT}-{$fromPosition->id} [label=\"{$fromPosition->name}\", URL=\"/positions/{$fromPosition->id}\"];";
            fwrite($file, $line . PHP_EOL);

            # Node for to position
            fwrite($file, PHP_EOL);
            $line = "{$INDENT}{$toPosition->id} [label=\"{$toPosition->name}\", URL=\"/positions/{$toPosition->id}\"];";
            fwrite($file, $line . PHP_EOL);

            # Edge for figure
            fwrite($file, PHP_EOL);
            $line = "{$INDENT}-{$fromPosition->id} -> {$toPosition->id} [label=\"{$figure->name} \", URL=\"/figures/{$figure->id}\"];";
            fwrite($file, $line . PHP_EOL);

            fwrite($file, PHP_EOL);
            fwrite($file, $digraphClose . PHP_EOL);
            fclose($file);

        } else {
            Log::error("Error opening file {$tmpDotFile}.");
        }
    }

}
