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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Figure;

class RegenerateFigureGraph implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // The number of times the job may be attempted.
    public $tries = 1;

    // The number of seconds the job can run before timing out.
    public $timeout = 5;
    public $failOnTimeout = true;

    protected $figureId = null;

    /**
     * Create a new job instance.
     */
    public function __construct(int $figureId)
    {
        $this->figureId = $figureId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userId = Auth::id();

        $figure = Figure::find($this->figureId);
        $fromPosition = $figure->from_position;
        $toPosition = $figure->to_position;

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/figuregraph-{$this->figureId}-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $figure, $fromPosition, $toPosition);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            figureGraphFullPathForUser($this->figureId, $userId),
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
        $graphConfig = "graph [{$this->prepareStringFromConfigArray(config('misc.graphs.figure_graph.config.graph'))}];";
        $nodeConfig = "node [{$this->prepareStringFromConfigArray(config('misc.graphs.config.node'))}];";
        $edgeConfig = "edge [{$this->prepareStringFromConfigArray(config('misc.graphs.config.edge'))}];";
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
        return [(new RateLimited('figuregraph'))->dontRelease()];
    }

}
