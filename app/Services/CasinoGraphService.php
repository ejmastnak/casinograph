<?php
namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class CasinoGraphService
{

    /**
     *  Given an array of figures with `[id, name, from_position,
     *  to_position]`, generates and saves an SVG of the corresponding
     *  CasinoGraph at the provided $svgOutputPath.
     */
    public function generateCasinoGraph($positions, $figures, $svgOutputPath) {
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

    /**
     *  Creates a temporary file representing the CasinoGraph digraph. This file is used as input to `dot` to generate the final SVG, and is then deleted
     */
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

}
