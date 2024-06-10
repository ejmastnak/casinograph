<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompoundFigureVideo;
use App\Models\CompoundFigure;

class CompoundFigureVideoSeeder extends Seeder {

    /**
   * Run the database seeds.
   *
   * @return void
   */
    public function run()
    {

        $casinoUserId = config('constants.user_ids.casino');
        $compoundFigureVideos = [
            [
                'compound_figure' => 'Setenta',
                'url' => 'https://www.youtube.com/watch?v=KIJGqON_oi0',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
            [
                'compound_figure' => 'Setenta',
                'url' => 'https://www.youtube.com/watch?v=KIJGqON_oi0&t=20s',
                'description' => 'Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            ],
            [
                'compound_figure' => 'Balsero',
                'url' => 'https://www.youtube.com/watch?v=KIJGqON_oi0',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
        ];

        foreach ($compoundFigureVideos as $compoundFigureVideo) {
            $compoundFigure = CompoundFigure::where('name', $compoundFigureVideo['compound_figure'])->where('user_id', $casinoUserId)->first();
            if ($compoundFigure) {
                CompoundFigureVideo::updateOrCreate([
                    'url' => $compoundFigureVideo['url'],
                    'description' => $compoundFigureVideo['description'],
                    'compound_figure_id' => $compoundFigure->id,
                ]);
            } else {
                $this->command->info("CompoundFigure {$compoundFigureVideo['compound_figure']} not found when seeding CompoundFigureVideos.");
            }
        }

    }
}
