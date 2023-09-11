<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\FigureFamily;

class FigureFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = Storage::disk('db-seed')->get('figure-families.json');
        $figure_families = json_decode($json, true);

        foreach ($figure_families as $figure_family) {
            FigureFamily::updateOrCreate([
                'name' => $figure_family['name'],
            ]);
        }
    }
}
