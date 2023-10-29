<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Figure;

class SuccessiveFiguresConsistent implements ValidationRule
{
    /**
     * Run the validation rule.
     * Input array of integers representing the ids of Figures making up a
     * CompoundFigure's CompoundFigureFigures. The rule checks that the
     * `to_position_id` field of each Figure equals the `from_position_id`
     * field of the subsequent Figure.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Should never happen because of `min:2` validation rule earlier in
        // FormRequest, but checking just in case.
        if (count($value) < 2) {
            $fail("A compound figure's figure sequence must have at least two figures. Add more figures first.");
            return;
        }

        $prevFigureId = $value[0];
        for ($i = 1; $i < count($value); $i++) {
            $figureId = $value[$i];
            $prevFigure = Figure::find($prevFigureId);
            $figure = Figure::find($figureId);

            // E.g. if frontend submited empty figures. No need to call fail
            // here because error will have been raised by FormRequest rules.
            if (is_null($prevFigure) || is_null($figure)) {
                $prevFigureId = $figureId;
                continue;
            }

            if ($prevFigure->to_position_id !== $figure->from_position_id) {
                $fail("Incompatible starting and ending positions: the figure sequence's " . $i . ($i === 1 ? "st" : ($i === 2 ? "nd" : ($i === 3 ? "rd" : "th"))) . " figure ends in " . $prevFigure->to_position->name . ", but the " . ($i + 1) . ($i === 1 ? "nd" : ($i === 2 ? "rd" : "th")) . " figure starts from " . $figure->from_position->name . ".");
            }

            $prevFigureId = $figureId;
        }

    }
}
