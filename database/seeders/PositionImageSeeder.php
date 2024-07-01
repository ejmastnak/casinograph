<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PositionImage;
use App\Models\Position;

class PositionImageSeeder extends Seeder {

    /**
   * Run the database seeds.
   *
   * @return void
   */
    public function run()
    {

        $casinoUserId = config('constants.user_ids.casino');
        $positionImages = [
            [
                'position' => 'Son',
                'image' => 'son.png',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
            [
                'position' => 'Caida',
                'image' => 'caida-1.png',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
            [
                'position' => 'Caida',
                'image' => 'caida-2.png',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
            [
                'position' => 'Open',
                'image' => 'open.png',
                'description' => 'Lorem ipsum dolor sit amet.',
            ],
        ];

        foreach ($positionImages as $positionImage) {
            $position = Position::where('name', $positionImage['position'])->where('user_id', $casinoUserId)->first();
            if ($position) {
                $truePath = storage_path('app/seeding/position_images/' . $positionImage['image']);
                if (!is_file($truePath)) {
                    $this->command->info("Image file {$truePath} not found when seeding PositionImages.");
                    continue;
                }
                // Paths stored on database relative to root of `public`
                // directory, and rely on a symlink pointing from
                // public/img/position_images/seeded to
                // storage/app/seeding/position_images/.
                PositionImage::updateOrCreate([
                    'path' => 'img/position_images/seeded/' . $positionImage['image'],
                    'description' => $positionImage['description'],
                    'position_id' => $position->id,
                ]);
            } else {
                $this->command->info("Position {$positionImage['position']} not found when seeding PositionImages.");
            }
        }

    }
}
