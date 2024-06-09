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
        $this->call([
            UserSeeder::class,
        ]);
        $this->seedResources();
    }

    private function seedResources() {
        // Only seed other resources if appropriate infrastructure is in place
        if (! (file_exists(config('constants.seeding.db')) && is_file(config('constants.seeding.db')))) {
            $this->command->warn("Warning: seed database does not exist at " . config('constants.seeding.db') . ". Aborting seeding.");
            return;
        }

        // Create directory for SQL seed scripts, if needed
        if (!is_dir(config('constants.seeding.sqldir'))) {
            mkdir(config('constants.seeding.sqldir', 0777, true));
        }

        $casinoUserId = config('constants.user_ids.casino');

        // Array keys are table names; values are SQL script filenames
        foreach (config('constants.seeding.sqlscripts') as $table => $script) {
            $updateUserIds = [
                'sqlite3',
                config('constants.seeding.db'),
                "'UPDATE {$table} SET user_id = {$casinoUserId} WHERE user_id IS NULL;'"
            ];
            $createSqlScript = [
                'sqlite3',
                config('constants.seeding.db'),
                "'.dump {$table}'"
            ];

            Process::run(implode(" ", $updateUserIds));
            $result = Process::run(implode(" ", $createSqlScript));
            file_put_contents(config('constants.seeding.sqldir') . DIRECTORY_SEPARATOR . $script, $result->output());
        }

        // Seed resources
        $this->call([
            PositionFamilySeeder::class,
            FigureFamilySeeder::class,
            PositionSeeder::class,
            FigureSeeder::class,
            FigureVideoSeeder::class,
            CompoundFigureSeeder::class,
            CompoundFigureFigureSeeder::class,
        ]);

    }
}
