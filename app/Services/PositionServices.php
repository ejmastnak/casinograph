<?php
namespace App\Services;

use App\Models\Position;
use App\Models\PositionFamily;
use Illuminate\Support\Facades\Log;

class PositionService
{
    public function storePosition(array $data, int $userId): ?int
    {
        $position = null;
        try {
            DB::transaction(function () use ($data, $userId, &$position) {

                $positionFamilyId = null;
                if (isset($data['position_family']) && $data['position_family']['id']) {
                    $positionFamilyId = $data['position_family']['id'];
                } else if (isset($data['position_family']) && is_null($data['position_family']['id'])) {
                    $positionFamilyId = PositionFamily::create([
                        'name' => $data['position_family']['name'],
                        'user_id' => $userId,
                    ])->id;
                }

                $position = Position::create([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'position_family_id' => $positionFamilyId,
                    'user_id' => $userId,
                ]);
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $position->id;
    }

    public function updatePosition(array $data, Position $position, int $userId): ?int
    {
        try {
            DB::transaction(function () use ($data, $position, $userId) {

                $previousPositionFamily = $position->position_family;

                $positionFamilyId = null;
                if (isset($data['position_family']) && $data['position_family']['id']) {
                    $positionFamilyId = $data['position_family']['id'];
                } else if (isset($data['position_family']) && is_null($data['position_family']['id'])) {
                    $positionFamilyId = PositionFamily::create([
                        'name' => $data['position_family']['name'],
                        'user_id' => $userId,
                    ])->id;
                }

                $position->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'position_family_id' => $positionFamilyId,
                ]);

                // If this update will orphan a position family, delete it.
                if ($previousPositionFamily) {
                    if (($positionFamilyId !== $previousPositionFamily->id) && Position::where('position_family_id', $previousPositionFamily->id)->count() === 0) {
                        $previousPositionFamily->delete();
                    }
                }
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $position->id;
    }
}
