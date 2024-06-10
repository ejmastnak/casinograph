<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FigureVideo;
use App\Models\Figure;

class FigureVideoSeeder extends Seeder {

    /**
   * Run the database seeds.
   *
   * @return void
   */
    public function run()
    {

        $casinoUserId = config('constants.user_ids.casino');
        $figureVideos = [
            [
                'figure' => 'Basico',
                'url' => 'https://www.youtube.com/watch?v=KIJGqON_oi0',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
            [
                'figure' => 'Basico',
                'url' => 'https://www.youtube.com/watch?v=KIJGqON_oi0&t=20s',
                'description' => 'Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            ],
            [
                'figure' => 'Dile que no',
                'url' => 'https://www.youtube.com/watch?v=KIJGqON_oi0',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
            [
                'figure' => 'Dile que sí',
                'url' => 'https://www.youtube.com/watch?v=KIJGqON_oi0',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
        ];

        foreach ($figureVideos as $figureVideo) {
            $figure = Figure::where('name', $figureVideo['figure'])->where('user_id', $casinoUserId)->first();
            if ($figure) {
                FigureVideo::updateOrCreate([
                    'url' => $figureVideo['url'],
                    'description' => $figureVideo['description'],
                    'figure_id' => $figure->id,
                ]);
            } else {
                $this->command->info("Figure {$figureVideo['figure']} not found when seeding FigureVideos.");
            }
        }

    }
}
