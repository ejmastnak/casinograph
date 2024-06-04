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

class RegenerateCasinoGraph implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // The number of times the job may be attempted.
    public $tries = 1;

    // The number of seconds the job can run before timing out.
    public $timeout = 5;
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userId = Auth::id();
        $positions = $this->getPositions($userId);
        $figures = $this->getFigures($userId);

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/casinograph-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $positions, $figures);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            casinoGraphFullPathForUser($userId),
        ];
        $cleanupCommandWithParams = [ 'rm', '-f', $tmpDotFile ];

        $result = Process::run(implode(' ', $dotCommandWithParams));
        $cleanup = Process::run(implode(' ', $cleanupCommandWithParams));

        if ($result->failed()) {
            Log::error("RegenerateCasinoGraph failed.\n");
            Log::error($result->errorOutput());
            if (\App::environment('local')) {
                dd($result->errorOutput());
            }
        }
    }

    private function writeDotFile($tmpDotFile, $positions, $figures) {
        $file = fopen($tmpDotFile, "w");
        $INDENT = "  ";

        $digraphOpen = 'digraph CasinoGraph {';
        $graphConfig = "graph [{$this->prepareStringFromConfigArray(config('misc.graphs.casinograph.config.graph'))}];";
        $nodeConfig = "node [{$this->prepareStringFromConfigArray(config('misc.graphs.config.node'))}];";
        $edgeConfig = "edge [{$this->prepareStringFromConfigArray(config('misc.graphs.config.edge'))}];";
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
        return [(new RateLimited('casinograph'))->dontRelease()];
    }

}
