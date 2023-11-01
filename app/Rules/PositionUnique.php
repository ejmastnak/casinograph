<?php

namespace App\Rules;

use Closure;
use App\Models\Position;
use Illuminate\Contracts\Validation\ValidationRule;use Illuminate\Contracts\Validation\DataAwareRule;

/**
 *  Ensures the position has a unique name. I'm using a custom validation rule
 *  to handle the update case (Laravel's 'unique' validation rule throws a
 *  validation error when updating an existing position but keeping its name).
 */
class PositionUnique implements ValidationRule, DataAwareRule
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
        $position = Position::where('name', $this->data['name'])->first();
        if (!is_null($position) && $position->id !== $this->data['id']) {
            $fail("A Position with this name already exists.");
        }
    }
}
