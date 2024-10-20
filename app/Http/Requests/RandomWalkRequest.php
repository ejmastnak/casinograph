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
            'excluded_figure_ids' => ['nullable', 'array', config('constants.validation.max_figure_sequence_excluded_items')],
            'excluded_figure_ids.*' => ['integer', 'exists:figures,id'],
            'excluded_figure_family_ids' => ['nullable', 'array', config('constants.validation.max_figure_sequence_excluded_items')],
            'excluded_figure_family_ids.*' => ['integer', 'exists:figure_families,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'length' => 'number of figures',
            'excluded_figure_ids' => 'excluded figures',
            'excluded_figure_ids.*' => 'excluded figure id',
            'excluded_figure_family_ids' => 'excluded figure families',
            'excluded_figure_family_ids.*' => 'excluded figure family',
        ];
    }

    

}
