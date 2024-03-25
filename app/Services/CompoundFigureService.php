<?php
namespace App\Services;

use App\Models\CompoundFigure;
use App\Models\Figure;
use App\Models\FigureFamily;
use App\Exceptions\FigureUpdateCorruptsCompoundFigureException;
use Illuminate\Support\Facades\Log;

class CompoundFigureService
{
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
