<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\PositionFamily;

class PositionFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::disk('db-seed')->get('position-families.json');
        $position_families = json_decode($json, true);

        foreach ($position_families as $position_family) {
            PositionFamily::updateOrCreate([
                'name' => $position_family['name'],
            ]);
        }
    }
}
