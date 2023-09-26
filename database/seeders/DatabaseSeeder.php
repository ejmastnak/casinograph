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

        // Generate SQL statements for seeding tables
        $result = Process::path(storage_path('app/seeders'))->run('bash ' . storage_path('app/seeders/prepare.bash'));
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
