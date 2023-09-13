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
        if (count($value < 2)) $fail('The Compound Figure must have at least two Figures.');

        $prev_figure_id = $value[0];
        for ($i = 1; $i < count($value); $i++) {
            $figure_id = $value[$i];
            $prev_figure = Figure::find($prev_figure_id);
            $figure = Figure::find($figure_id);

            // Again, should never happen, but playing it safe
            if (is_null($prev_figure)) {
                $fail("The Compound Figure's " . $i . ($i === 1 ? "st" : ($i === 2 ? "nd" : ($i === 3 ? "rd" : "th"))) . " Figure is invalid");
            }
            if (is_null($figure)) {
                $fail("The Compound Figure's " . ($i + 1) . ($i === 1 ? "nd" : ($i === 2 ? "rd" : "th")) . " Figure is invalid");
            }

            if ($prev_figure->to_position_id !== $figure->from_position_id) {
                $fail("The Compound Figure's " . $i . ($i === 1 ? "st" : ($i === 2 ? "nd" : ($i === 3 ? "rd" : "th"))) . " Figure does not lead to the " . ($i + 1) . ($i === 1 ? "nd" : ($i === 2 ? "rd" : "th")) . " Figure.");
            }

            $prev_figure_id = $figure_id;
        }

    }
}