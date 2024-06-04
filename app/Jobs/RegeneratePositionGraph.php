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
use App\Models\Position;

class RegeneratePositionGraph implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // The number of times the job may be attempted.
    public $tries = 1;

    // The number of seconds the job can run before timing out.
    public $timeout = 5;
    public $failOnTimeout = true;

    protected $positionId = null;

    /**
     * Create a new job instance.
     */
    public function __construct(int $positionId)
    {
        $this->positionId = $positionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userId = Auth::id();

        $incomingFigures = $this->getIncomingFigures($this->positionId, $userId);
        $outgoingFigures = $this->getOutgoingFigures($this->positionId, $userId);

        $timestamp = str_replace(".", "-", microtime(true));
        $tmpDotFile = "/tmp/positiongraph-{$this->positionId}-{$timestamp}.dot";
        $this->writeDotFile($tmpDotFile, $this->positionId, $incomingFigures, $outgoingFigures);

        $dotCommandWithParams = [
            '/usr/bin/dot',
            '-Tsvg',
            $tmpDotFile,
            '-o',
            positionGraphFullPathForUser($this->positionId, $userId),
        ];
        $cleanupCommandWithParams = [ 'rm', '-f', $tmpDotFile ];

        $result = Process::run(implode(' ', $dotCommandWithParams));
        $cleanup = Process::run(implode(' ', $cleanupCommandWithParams));

        if ($result->failed()) {
            Log::error("RegeneratePositionGraph failed.\n");
            Log::error($result->errorOutput());
            if (\App::environment('local')) {
                dd($result->errorOutput());
            }
        }
    }

    private function writeDotFile($tmpDotFile, $rootPositionId, $incomingFigures, $outgoingFigures) {
        $file = fopen($tmpDotFile, "w");
        $INDENT = "  ";

        $digraphOpen = 'digraph PositionGraph {';
        $graphConfig = "graph [{$this->prepareStringFromConfigArray(config('misc.graphs.position_graph.config.graph'))}];";
        $nodeConfig = "node [{$this->prepareStringFromConfigArray(config('misc.graphs.config.node'))}];";
        $edgeConfig = "edge [{$this->prepareStringFromConfigArray(config('misc.graphs.config.edge'))}];";
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
            $line = "{$INDENT}{$rootNodeId} [label=\"{$rootPosition->name}\", URL=\"/positions/{$rootPosition->id}\", " . $this->prepareStringFromConfigArray(config('misc.graphs.position_graph.config.root_node')) . "];";
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
        return [(new RateLimited('positiongraph'))->dontRelease()];
    }

}
