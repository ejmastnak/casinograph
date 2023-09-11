<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Position;
use App\Models\PositionFamily;

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

            $position_family_id = null;
            if (isset($position['position_family'])) {
                $position_family = PositionFamily::where('name', $position['position_family'])->first();
                if ($position_family) {
                    $position_family_id = $position_family->id;
                } else {
                    $this->command->info('Warning: PositionFamily ' . $position['position_family'] . ' not found when seeding Position ' . $position['name'] . '.');
                }
            }

            $description = isset($position['description']) ? $position['description'] : null;

            Position::updateOrCreate([
                'name' => $position['name'],
                'description' => $description,
                'position_family_id' => $position_family_id,
            ]);
        }
    }
}
