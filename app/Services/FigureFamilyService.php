<?php
namespace App\Services;

use App\Models\FigureFamily;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FigureFamilyService
{
    public function updateFigureFamily(array $data, FigureFamily $figureFamily): ?int
    {
        try {
            DB::transaction(function () use ($data, $figureFamily) {
                $figureFamily->update([
                    'name' => $data['name'],
                ]);
            });
        } catch (\Exception $e) {
            Log::error($e);
            throw $e;
        }
        return $figureFamily->id;
    }
}
