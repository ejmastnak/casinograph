<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;


/**
 *  Checks that updating this figure's to position will not corrupt to/from
 *  position integrity in the figure sequence of any dependent CompoundFigures.
 */
class NewToPositionPreservesCompoundFigureIntegrity implements ValidationRule, DataAwareRule
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
        $toPositionChanged = $figure->to_position_id !== $this->data['to_position_id'];
        if ($toPositionChanged) {
            foreach ($figure->compound_figure_figures as $cff) {
                if (!$cff->is_final) {  // Remember: changing to position is safe for final figure
                    $fail("Changing this figure's to position would cause incompatible starting and ending positions in figure " . $cff->seq_num . " of the compound figure " . $cff->compound_figure->name . ".");
                }
            }
        }
    }
}
