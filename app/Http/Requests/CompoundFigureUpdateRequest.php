<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CompoundFigure;
use App\Rules\SuccessiveFiguresConsistent;

class CompoundFigureUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $compound_figure = CompoundFigure::find($this->route('compound_figure'));
        return $compound_figure && $this->user()->can('update', $compound_figure);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', config('validation.max_name_length')],
            'description' => ['nullable', 'string', 'min:0', config('validation.max_description_length')],
            'weight' => ['nullable', 'integer', 'min:1', config('validation.max_weight')],
            'figure_family_id' => ['nullable', 'integer', 'exists:App\Models\FigureFamily,id'],
            'figure_ids' => ['required', 'array', 'min:2', config('validation.max_compound_figure_figures')],
            'figure_ids.*' => ['integer', 'exists:App\Models\Figure,id'],
            'figure_ids' => [new SuccessiveFiguresConsistent],
        ];
    }
}
