<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Figure;
use App\Rules\FigureUniqueForUser;
use App\Rules\NewFromPositionPreservesCompoundFigureIntegrity;
use App\Rules\NewToPositionPreservesCompoundFigureIntegrity;

class UpdateFigureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $figure = $this->route('figure');
        return $figure && $this->user()->can('update', $figure);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', config('validation.max_name_length'), new FigureUniqueForUser],
            'description' => ['nullable', 'string', 'min:0', config('validation.max_description_length')],
            'weight' => ['nullable', 'integer', 'min:1', config('validation.max_weight')],
            'from_position_id' => ['required', 'integer', 'exists:positions,id', new NewFromPositionPreservesCompoundFigureIntegrity],
            'to_position_id' => ['required', 'integer', 'exists:positions,id', new NewToPositionPreservesCompoundFigureIntegrity],
            'figure_family' => ['nullable', 'array', 'required_array_keys:id,name'],
            'figure_family.id' => ['nullable', 'integer', 'exists:figure_families,id'],
            'figure_family.name' => ['required_with:figure_family', 'string', 'min:1', config('validation.max_name_length')],
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
            'from_position_id' => 'from position',
            'to_position_id' => 'to position',
            'figure_family.id' => 'figure family',
            'figure_family.name' => 'figure family name',
        ];
    }

}
