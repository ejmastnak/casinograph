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
        $command_with_params = [
            './casinograph.bash',
            database_path('sqlite/database.sqlite'),
            public_path(config('misc.casinograph_public_path')),
            config('app.url'),
        ];
        $result = Process::path(resource_path('scripts'))->run(implode(' ', $command_with_params));

        if (\App::environment('local')) {
            if ($result->failed()) {
                print("Process failed.\n");
                dd($result->errorOutput());
            }
        }

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
