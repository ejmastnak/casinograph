<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::disk('db-seed')->get('positions.json');
        $positions = json_decode($json, true);

        foreach ($positions as $position) {
            $description = isset($position['description']) ? $position['description'] : null;
            Position::updateOrCreate([
                'name' => $position['name'],
                'description' => $description,
            ]);
        }
    }
}
