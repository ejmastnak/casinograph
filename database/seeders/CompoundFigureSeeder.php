<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\CompoundFigure;
use App\Models\CompoundFigureFigure;
use App\Models\Figure;
use App\Models\FigureFamily;
use App\Models\Position;

class CompoundFigureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::disk('db-seed')->get('compound-figures.json');
        $compound_figures = json_decode($json, true);

        foreach ($compound_figures as $compound_figure) {
            $description = isset($compound_figure['description']) ? $compound_figure['description'] : null;
            $weight = isset($compound_figure['weight']) ? $compound_figure['weight'] : 1;

            $figure_family_id = null;
            if (isset($compound_figure['figure_family'])) {
                $figure_family = FigureFamily::where('name', $compound_figure['figure_family'])->first();
                if ($figure_family) {
                    $figure_family_id = $figure_family->id;
                } else {
                    $this->command->info('Warning: FigureFamily ' . $compound_figure['figure_family'] . ' not found when seeding CompoundFigure ' . $compound_figure['name'] . '.');
                }
            }

            $figures = [];
            $null_figure = false;
            foreach ($compound_figure['figures'] as $figure_name) {
                $figure = Figure::where('name', $figure_name)->first();
                if (is_null($figure)) {
                    $this->command->info('Warning: ' . $figure_name . ' not found when seeding Figures.');
                    $null_figure = true;
                    break;
                }
                $figures[] = $figure;
            }
            if ($null_figure) continue;
            if (count($figures) < 2) {
                $this->command->info('Warning: fewer than two figures found for ' . $compound_figure['name'] . ' not found when seeding CompoundFigures.');
                continue;
            }

            $eloquent_compound_figure = CompoundFigure::updateOrCreate([
                'name' => $compound_figure['name'],
                'description' => $description,
                'weight' => $weight,
                'figure_family_id' => $figure_family_id,
                'from_position_id' => $figures[0]->from_position_id,
                'to_position_id' => $figures[count($figures) - 1]->to_position_id,
            ]);

            foreach ($figures as $idx=>$figure) {
                CompoundFigureFigure::updateOrCreate([
                    'figure_id' => $figure->id,
                    'compound_figure_id' => $eloquent_compound_figure->id,
                    'idx' => $idx + 1,
                ]);
            }
        }
    }
}
