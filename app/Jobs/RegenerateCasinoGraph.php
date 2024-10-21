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
use App\Services\CasinoGraphService;

class RegenerateCasinoGraph implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // The number of times the job may be attempted.
    public $tries = 1;

    // The number of seconds the job can run before timing out.
    public $timeout = 5;
    public $failOnTimeout = true;

    /**
     * Execute the job.
     */
    public function handle(CasinoGraphService $casinoGraphService): void
    {
        $userId = Auth::id();
        $positions = $this->getPositions($userId);
        $figures = $this->getFigures($userId);
        $svgOutputPath = casinoGraphStoragePathForUser($userId);
        $casinoGraphService->generateCasinoGraph($positions, $figures, $svgOutputPath);
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
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new RateLimited('casinograph'))->dontRelease()];
    }

}
