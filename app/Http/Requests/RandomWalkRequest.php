<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Position;
use App\Rules\FiguresRequiredIfCompoundFiguresAbsent;

class RandomWalkRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'length' => ['required', 'integer', 'min:1', config('constants.validation.max_random_walk_length')],
        ];
    }

}
