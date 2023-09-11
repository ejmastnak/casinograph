<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Figure;
use App\Models\Position;
use App\Models\FigureFamily;

class FigureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::disk('db-seed')->get('figures.json');
        $figures = json_decode($json, true);

        foreach ($figures as $figure) {
            $description = isset($figure['description']) ? $figure['description'] : null;
            $weight = isset($figure['weight']) ? $figure['weight'] : 1;
            $from_position = Position::where('name', $figure['from_position'])->first();
            $to_position = Position::where('name', $figure['to_position'])->first();

            if (is_null($from_position)) {
                $this->command->info('Warning: ' . $from_position . ' not found when seeding Figures.');
                continue;
            }

            if (is_null($to_position)) {
                $this->command->info('Warning: ' . $to_position . ' not found when seeding Figures.');
                continue;
            }

            $figure_family_id = null;
            if (isset($figure['figure_family'])) {
                $figure_family = FigureFamily::where('name', $figure['figure_family'])->first();
                if ($figure_family) {
                    $figure_family_id = $figure_family->id;
                } else {
                    $this->command->info('Warning: FigureFamily ' . $figure['figure_family'] . ' not found when seeding Figure ' . $figure['name'] . '.');
                }
            }

            Figure::updateOrCreate([
                'name' => $figure['name'],
                'description' => $description,
                'weight' => $weight,
                'from_position_id' => $from_position->id,
                'to_position_id' => $to_position->id,
                'figure_family_id' => $figure_family_id,
            ]);
        }
    }
}
