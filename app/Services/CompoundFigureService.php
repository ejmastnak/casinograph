<?php
namespace App\Services;

use App\Models\CompoundFigure;
use App\Models\CompoundFigureFigure;
use App\Models\Figure;
use App\Models\FigureFamily;
use App\Models\CompoundFigureVideo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompoundFigureService
{

    public function storeCompoundFigure(array $data) {
        $compoundFigure = null;
        $userId = Auth::id();
        try {
            DB::transaction(function () use ($data, $userId, &$compoundFigure) {

                $figureFamilyId = null;
                if (isset($data['figure_family']) && $data['figure_family']['id']) {
                    $figureFamilyId = $data['figure_family']['id'];
                } else if (isset($data['figure_family']) && is_null($data['figure_family']['id'])) {
                    $figureFamilyId = FigureFamily::create([
                        'name' => $data['figure_family']['name'],
                        'user_id' => $userId,
                    ])->id;
                }

                $fromPositionId = Figure::find($data['figure_ids'][0])->from_position_id;
                $toPositionId = Figure::find($data['figure_ids'][count($data['figure_ids']) - 1])->to_position_id;

                $compoundFigure = CompoundFigure::create([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'weight' => isset($data['weight']) ? $data['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figureFamilyId,
                    'from_position_id' => $fromPositionId,
                    'to_position_id' => $toPositionId,
                    'user_id' => $userId,
                ]);

                foreach ($data['figure_ids'] as $idx=>$figureId) {
                    CompoundFigureFigure::create([
                        'figure_id' => $figureId,
                        'compound_figure_id' => $compoundFigure->id,
                        'seq_num' => $idx + 1,
                        'is_final' => $idx === count($data['figure_ids']) - 1,
                        'user_id' => $userId,
                    ]);
                }

                foreach ($data['compound_figure_videos'] as $compoundFigureVideo) {
                    CompoundFigureVideo::create([
                        'url' => $compoundFigureVideo['url'],
                        'description' => $compoundFigureVideo['description'],
                        'compound_figure_id' => $compoundFigure->id,
                    ]);
                }

            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $compoundFigure->id;
    }

    public function updateCompoundFigure(array $data, CompoundFigure $compoundFigure): ?int
    {
        $userId = Auth::id();
        try {
            DB::transaction(function () use ($data, $compoundFigure, $userId) {

                $previousFigureFamily = $compoundFigure->figure_family;

                $figureFamilyId = null;
                if (isset($data['figure_family']) && $data['figure_family']['id']) {
                    $figureFamilyId = $data['figure_family']['id'];
                } else if (isset($data['figure_family']) && is_null($data['figure_family']['id'])) {
                    $figureFamilyId = FigureFamily::create([
                        'name' => $data['figure_family']['name'],
                        'user_id' => $userId,
                    ])->id;
                }

                $fromPositionId = Figure::find($data['figure_ids'][0])->from_position_id;
                $toPositionId = Figure::find($data['figure_ids'][count($data['figure_ids']) - 1])->to_position_id;

                $compoundFigure->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'weight' => isset($data['weight']) ? $data['weight'] : config('misc.default_figure_weight'),
                    'figure_family_id' => $figureFamilyId,
                    'from_position_id' => $fromPositionId,
                    'to_position_id' => $toPositionId,
                ]);

                // I'm just deleting old CompoundFigureFigures and creating new
                // ones on each update, doesn't seem worth the extra complexity of
                // going through create-new-update-existing-delete-stale.
                foreach ($compoundFigure->compound_figure_figures as $cff) $cff->delete();
                foreach ($data['figure_ids'] as $idx=>$figureId) {
                    CompoundFigureFigure::create([
                        'figure_id' => $figureId,
                        'compound_figure_id' => $compoundFigure->id,
                        'seq_num' => $idx + 1,
                        'is_final' => $idx === count($data['figure_ids']) - 1,
                        'user_id' => $userId,
                    ]);
                }

                // I'm just deleting old FigureVideos and creating new ones on
                // each update, doesn't seem worth the extra complexity of
                // going through create-new-update-existing-delete-stale.
                foreach ($compoundFigure->compound_figure_videos as $cfv) $cfv->delete();
                foreach ($data['compound_figure_videos'] as $compoundFigureVideo) {
                    CompoundFigureVideo::create([
                        'url' => $compoundFigureVideo['url'],
                        'description' => $compoundFigureVideo['description'],
                        'compound_figure_id' => $compoundFigure->id,
                    ]);
                }

                // If this update will orphan a figure family, delete it.
                if ($previousFigureFamily) {
                    if (($figureFamilyId !== $previousFigureFamily->id) && Figure::where('figure_family_id', $previousFigureFamily->id)->count() === 0 && CompoundFigure::where('figure_family_id', $previousFigureFamily->id)->count() === 0) {
                        $previousFigureFamily->delete();
                    }
                }
            });
        } catch (\Exception $e) {
            if (\App::environment('local')) throw $e;
            Log::error($e);
            return null;
        }
        return $compoundFigure->id;
    }

    public function deleteCompoundFigure(CompoundFigure $compoundFigure): array
    {
        $success = false;
        $message = "";

        try {
            DB::transaction(function () use ($compoundFigure, &$success) {
                $figureFamily = $compoundFigure->figure_family;

                // Delete CompoundFigureFigures
                $compoundFigureFigures = $compoundFigure->compound_figure_figures;
                foreach ($compoundFigureFigures as $cff) $cff->delete();

                $compoundFigure->delete();

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

        if ($success) $message = 'Success! Figure deleted successfully.';
        else $message = 'Error. Failed to delete figure.';

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

}
