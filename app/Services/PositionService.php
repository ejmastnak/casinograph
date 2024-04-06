<?php
namespace App\Services;

use App\Models\Position;
use App\Models\PositionFamily;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PositionService
{
    public function storePosition(array $data): ?int
    {
        $position = null;
        $userId = Auth::id();
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

    public function updatePosition(array $data, Position $position): ?int
    {
        $userId = Auth::id();
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

    public function deletePosition(Position $position): array
    {
        $restricted = false;
        $success = false;
        $message = "";

        // Check for foreign key constraints
        if ($position->incoming_figures()->count() > 0 || $position->outgoing_figures()->count() > 0) {

            $limit = config('misc.restrict_on_delete_message_limit');
            $i = 0;
            $names = [];
            foreach ($position->incoming_figures as $figure) {
                if ($i === $limit) break;
                $names[] = $figure->name;
                $i++;
            } foreach ($position->outgoing_figures as $figure) {
                if ($i === $limit) break;
                $name = $figure->name;
                if (!in_array($name, $names)) {
                    $names[] = $name;
                    $i++;
                }
            }

            $restricted = true;
            $message = "Deleting this position is intentionally forbidden because one or more figures rely on it. You should first delete all dependent figures, then delete this position.\nThe dependent figures " . ($i < $limit ? "are " : "include ") . implode(", ", $names) . ".";
        }

        if (!$restricted) {
            try {
                DB::transaction(function () use ($position, &$success) {
                    $positionFamily = $position->position_family;
                    $position->delete();

                    // If this update will orphan a position family, delete it.
                    if ($positionFamily && Position::where('position_family_id', $positionFamily->id)->count() === 0) {
                        $positionFamily->delete();
                    }

                    $success = true;
                });
            } catch (\Exception $e) {
                if (\App::environment('local')) throw $e;
                Log::error($e);
            }
        }

        if ($success) $message = 'Success! Position deleted successfully.';
        else if (!$success && !$restricted) $message = 'Error. Failed to delete position.';

        return [
            'success' => $success,
            'restricted' => $restricted,
            'message' => $message,
        ];
    }

}
