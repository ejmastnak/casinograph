<?php
namespace App\Services;

use App\Models\PositionFamily;
use Illuminate\Support\Facades\Log;

class PositionFamilyService
{
    public function updatePositionFamily(array $data, PositionFamily $positionFamily, int $userId): ?int
    {
        try {
            DB::transaction(function () use ($data, $positionFamily) {
                $positionFamily->update([
                    'name' => $data['name'],
                ]);
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $positionFamily->id;
    }
}
