<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FigureFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seedScript = config('constants.seeding.sqldir') . DIRECTORY_SEPARATOR . config('constants.seeding.sqlscripts.figure_families');
        if (file_exists($seedScript) && is_file($seedScript)) {
            DB::unprepared(file_get_contents($seedScript));
        } else {
            $this->command->warn("Warning: FigureFamily seed script does not exist at " . $seedScript . ". Aborting seeding FigureFamilies.");
        }
    }
}
