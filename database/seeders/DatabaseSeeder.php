<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (\App::environment('local')) {

            $result = Process::path(database_path('seeders/data'))->run('bash ' . database_path('seeders/data/prepare-seed-scripts.bash'));
            if ($result->failed()) {
                $this->command->info('Error: failed to prepare SQL statements for seeding database; seeding aborted.');
            }

            $this->call([
                UserSeeder::class,
                PositionFamilySeeder::class,
                FigureFamilySeeder::class,
                PositionSeeder::class,
                FigureSeeder::class,
                CompoundFigureSeeder::class,
                CompoundFigureFigureSeeder::class,
            ]);

        }
    }
}
