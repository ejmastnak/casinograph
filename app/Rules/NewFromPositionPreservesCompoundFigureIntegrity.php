<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;


/**
 *  Checks that updating this figure's from position will not corrupt to/from
 *  position integrity in the figure sequence of any dependent CompoundFigures.
 */
class NewFromPositionPreservesCompoundFigureIntegrity implements ValidationRule, DataAwareRule
{

    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $figure = request()->route()->parameter('figure');
        $fromPositionChanged = $figure->from_position_id !== $this->data['from_position_id'];
        if ($fromPositionChanged) {
            foreach ($figure->compound_figure_figures as $cff) {
                if ($cff->seq_num > 1) {  // Remember: changing from position is safe when seq_num === 1
                    $fail("Changing this figure's from position would cause incompatible starting and ending positions in figure " . $cff->seq_num . " of the compound figure " . $cff->compound_figure->name . ".");
                }
            }
        }
    }
}
