<?php

namespace App\Rules;

use Closure;
use App\Models\CompoundFigure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\Auth;

/**
 *  Ensures the compound figure has a unique combination of name and figures.
 */
class CompoundFigureUniqueForUser implements ValidationRule, DataAwareRule
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
        $candidates = CompoundFigure::where([
            ['name', 'like', $this->data['name']],
            ['user_id', '=', Auth::id()],
        ])->get();

        // Check for a compound figure with an identical figure sequence
        foreach ($candidates as $candidate) {
            $candidateFigureIds = array_map(function($cff) {
                return $cff['figure_id'];
            }, $candidate->compound_figure_figures->toArray());
            if ($candidateFigureIds === $this->data['figure_ids'] && $candidate->id !== $this->data['id']) {
                $fail("A figure with this combination of name and figure sequence already exists. Change this figure's the name or figure sequence.");
            }
        }
    }
}
