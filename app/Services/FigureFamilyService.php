<?php
namespace App\Services;

use App\Models\FigureFamily;
use Illuminate\Support\Facades\Log;

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
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $figureFamily->id;
    }
}
