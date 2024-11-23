<?php
namespace App\Services;

use App\Models\CompoundFigure;
use App\Models\Position;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class CompoundFigureGraphService
{

    /**
     *  Generates an SVG graph diagram for the given compound figure.
     */
    public function generateCompoundFigureGraph(CompoundFigure $compoundFigure) {
        $user = Auth::id() ?? 'public';
        $executed = RateLimiter::attempt(
            'generate-compound-figure-graph-'.$user,
            $perMinute = config('constants.rate_limits.compound_figure_graph_per_minute'),
            function() use ($compoundFigure) {
                $this->generateCompoundFigureGraphHandler($compoundFigure);
            }
        );
    }

    private function generateCompoundFigureGraphHandler(CompoundFigure $compoundFigure) {
        $userId = $compoundFigure->user_id;
        $numPositions = Position::where('user_id', ($userId ?? config('constants.user_ids.casino')))->count();

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/compoundfiguregraph-{$compoundFigure->id}-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $compoundFigure, $numPositions);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            compoundFigureGraphStoragePathForUser($compoundFigure->id, $userId),
        ];
        $cleanupCommandWithParams = [ 'rm', '-f', $tmpDotFile ];

        $result = Process::run(implode(' ', $dotCommandWithParams));
        $cleanup = Process::run(implode(' ', $cleanupCommandWithParams));

        if ($result->failed()) {
            Log::error("RegenerateCompoundFigureGraph failed.\n");
            Log::error($result->errorOutput());
            if (\App::environment('local')) {
                dd($result->errorOutput());
            }
        }
    }

    private function writeDotFile($tmpDotFile, $compoundFigure, $numPositions) {
        $file = fopen($tmpDotFile, "w");
        $INDENT = "  ";

        $digraphOpen = 'digraph FigureGraph {';
        $graphConfig = "graph [".prepareStringFromConfigArray(config('misc.graphs.compound_figure_graph.config.graph'))."];";
        $nodeConfig = "node [".prepareStringFromConfigArray(config('misc.graphs.compound_figure_graph.config.node'))."];";
        $edgeConfig = "edge [".prepareStringFromConfigArray(config('misc.graphs.compound_figure_graph.config.edge'))."];";
        $digraphClose = '}';

        if ($file) {
            fwrite($file, $digraphOpen . PHP_EOL);

            fwrite($file, PHP_EOL);
            fwrite($file, $INDENT . $graphConfig . PHP_EOL);
            fwrite($file, $INDENT . $nodeConfig . PHP_EOL);
            fwrite($file, $INDENT . $edgeConfig . PHP_EOL);

            // Graphviz node ids are intentionally padded in multiples of
            // numPositions to force Graphviz to draw a separate set of
            // position nodes for every figure in the compound figure's figure
            // sequence, even when the same position occurs multiple times in
            // the figure sequence. The offset of `$i` vs. `$i + 1` for from/to
            // positions ensures the from_position_id of $cff N agrees with
            // Graphviz to_position_id of $cff N - 1.
            foreach ($compoundFigure->compound_figure_figures as $i => $cff) {
                fwrite($file, PHP_EOL);

                # Node for $cff from position
                $graphvizFromPositionId = $cff->figure->from_position->id + $i*$numPositions;
                $line = "{$INDENT}{$graphvizFromPositionId} [label=\"{$cff->figure->from_position->name}\", URL=\"/positions/{$cff->figure->from_position->id}\"];";
                fwrite($file, $line . PHP_EOL);

                # Node for $cff from position
                $graphvizToPositionId = $cff->figure->to_position->id + ($i + 1)*$numPositions;
                $line = "{$INDENT}{$graphvizToPositionId} [label=\"{$cff->figure->to_position->name}\", URL=\"/positions/{$cff->figure->to_position->id}\"];";
                fwrite($file, $line . PHP_EOL);

                # Edge for figure
                fwrite($file, PHP_EOL);
                $line = "{$INDENT}{$graphvizFromPositionId} -> {$graphvizToPositionId} [label=\"{$cff->figure->name} \", URL=\"/figures/{$cff->figure->id}\"];";
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
