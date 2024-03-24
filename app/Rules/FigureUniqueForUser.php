<?php

namespace App\Rules;

use Closure;
use App\Models\Figure;
use Illuminate\Contracts\Validation\ValidationRule;use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\Auth;

/**
 *  Ensures the figure has a unique combination of name, from_position_id, and
 *  to_position_id for the given user.
 */
class FigureUniqueForUser implements ValidationRule, DataAwareRule
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
        $figure = Figure::where([
            ['name', 'like', $this->data['name']],
            ['from_position_id', '=', $this->data['from_position_id']],
            ['to_position_id', '=', $this->data['to_position_id']],
            ['user_id', '=', Auth::id()],
        ])->first();
        if (!is_null($figure) && $figure->id !== $this->data['id']) {
            $fail("A figure with this combination of name, from position, and to position already exists. Change this figure's the name, from position, or to position.");
        }
    }
}
