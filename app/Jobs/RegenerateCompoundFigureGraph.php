<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\CompoundFigure;
use App\Models\Position;

class RegenerateCompoundFigureGraph implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // The number of times the job may be attempted.
    public $tries = 1;

    // The number of seconds the job can run before timing out.
    public $timeout = 5;
    public $failOnTimeout = true;

    protected $compoundFigureId = null;

    /**
     * Create a new job instance.
     */
    public function __construct(int $compoundFigureId)
    {
        $this->compoundFigureId = $compoundFigureId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $compoundFigure = CompoundFigure::find($this->compoundFigureId);
        $userId = $compoundFigure->user_id;
        $numPositions = Position::where('user_id', ($userId ?? config('constants.user_ids.casino')))->count();

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/compoundfiguregraph-{$this->compoundFigureId}-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $compoundFigure, $numPositions);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            compoundFigureGraphStoragePathForUser($this->compoundFigureId, $userId),
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
        $graphConfig = "graph [{$this->prepareStringFromConfigArray(config('misc.graphs.compound_figure_graph.config.graph'))}];";
        $nodeConfig = "node [{$this->prepareStringFromConfigArray(config('misc.graphs.config.node'))}];";
        $edgeConfig = "edge [{$this->prepareStringFromConfigArray(config('misc.graphs.config.edge'))}];";
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

    /**
     *  Example input:
     *  > [
     *  >     'fontname' => "Figtree",
     *  >     'fontcolor' => "#172554",
     *  >     'color' => "#172554",
     *  >     'target' => "_top",
     *  > ]
     *
     * Corresponding output:
     * > 'fontname="Figtree", fontcolor="#172554", color="#172554", target="_top"'
     *
     * Strings values are enclosed in double quotes; numeric values are left
     * unquoted.
     */
    private function prepareStringFromConfigArray(array $config) {
        $parts = [];
        foreach ($config as $key => $value) {
            if (is_string($value)) {
                $valueFormatted = "\"{$value}\"";  # enclose strings in quotes
            } else {
                $valueFormatted = strval($value);  # leave numeric values unquoted
            }
            $parts[] = "{$key}={$valueFormatted}";
        }
        return implode(", ", $parts);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new RateLimited('compoundfiguregraph'))->dontRelease()];
    }

}
