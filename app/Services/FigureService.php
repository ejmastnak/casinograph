<?php
namespace App\Services;

use App\Models\Figure;
use App\Models\FigureFamily;
use App\Exceptions\FigureUpdateCorruptsCompoundFigureException;
use Illuminate\Support\Facades\Log;

class FigureService
{
    public function storeFigure(array $data, int $userId): ?int
    {
        $figure = null;
        try {
            DB::transaction(function () use ($data, $userId, &$figure) {

                $figureFamilyId = null;
                if (isset($data['figure_family']) && $data['figure_family']['id']) {
                    $figureFamilyId = $data['figure_family']['id'];
                } else if (isset($data['figure_family']) && is_null($data['figure_family']['id'])) {
                    $figureFamilyId = FigureFamily::create([
                        'name' => $data['figure_family']['name'],
                        'user_id' => $userId,
                    ])->id;
                }

                $figure = Figure::create([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'weight' => $data['weight'] ? $data['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figureFamilyId,
                    'from_position_id' => $data['from_position_id'],
                    'to_position_id' => $data['to_position_id'],
                    'user_id' => $userId,
                ]);
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $figure->id;
    }

    public function updateFigure(array $data, Figure $figure, int $userId): ?int
    {
        try {
            DB::transaction(function () use ($data, $figure, $userId) {

                $previousFigureFamily = $figure->figure_family;

                $figureFamilyId = null;
                if (isset($data['figure_family']) && $data['figure_family']['id']) {
                    $figureFamilyId = $data['figure_family']['id'];
                } else if (isset($data['figure_family']) && is_null($data['figure_family']['id'])) {
                    $figureFamilyId = FigureFamily::create([
                        'name' => $data['figure_family']['name'],
                        'user_id' => $userId,
                    ])->id;
                }

                $figure->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'weight' => $data['weight'] ? $data['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figureFamilyId,
                    'from_position_id' => $data['from_position_id'],
                    'to_position_id' => $data['to_position_id'],
                ]);

                // If this update will orphan a figure family, delete it.
                if ($previousFigureFamily) {
                    if (($figureFamilyId !== $previousFigureFamily->id) && Figure::where('figure_family_id', $previousFigureFamily->id)->count() === 0) {
                        $previousFigureFamily->delete();
                    }
                }
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $figure->id;
    }

    public function deleteFigure(Figure $figure): array
    {
        $restricted = false;
        $success = false;
        $message = "";

        // Check for foreign key constraints
        if ($figure->compound_figure_figures()->count() > 0) {
            $limit = config('misc.restrict_on_delete_message_limit');
            $i = 0;
            $names = [];
            foreach ($figure->compound_figure_figures as $cff) {
                if ($i === $limit) break;
                $name = $cff->compound_figure->name;
                if (!in_array($name, $names)) {
                    $names[] = $name;
                    $i++;
                }
            }
            $restricted = true;
            $message = "Deleting this figure is intentionally forbidden because one or more compound figures rely on it. You should first delete all dependent compound figures, then delete this figure.\nThe dependent compound figures " . ($i < $limit ? "are " : "include ") . implode(", ", $names) . ".";
        }

        if (!$restricted) {
            try {
                DB::transaction(function () use ($figure, &$success) {
                    $figureFamily = $figure->figure_family;
                    $figure->delete();

                    // If this update will orphan a figure family, delete it.
                    if ($figureFamily && Figure::where('figure_family_id', $figureFamily->id)->count() === 0 && CompoundFigure::where('figure_family_id', $figureFamily->id)->count() === 0) {
                        $figureFamily->delete();
                    }

                    $success = true;
                });
            } catch (\Exception $e) {
                if (\App::environment('local')) throw $e;
                Log::error($e);
            }
        }

        if ($success) $message = 'Success! Figure deleted successfully.';
        else if (!$success && !$restricted) $message = 'Error. Failed to delete figure.';

        return [
            'success' => $success,
            'restricted' => $restricted,
            'message' => $message,
        ];
    }

}
