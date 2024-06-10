<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CompoundFigure;
use App\Rules\SuccessiveFiguresConsistent;
use App\Rules\CompoundFigureUniqueForUser;

class StoreCompoundFigureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', CompoundFigure::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', config('constants.validation.max_name_length'), new CompoundFigureUniqueForUser],
            'description' => ['nullable', 'string', 'min:0', config('constants.validation.max_description_length')],
            'weight' => ['nullable', 'integer', 'min:1', config('constants.validation.max_weight')],
            'figure_family' => ['nullable', 'array', 'required_array_keys:id,name'],
            'figure_family.id' => ['nullable', 'integer', 'exists:figure_families,id'],
            'figure_family.name' => ['required_with:figure_family', 'string', 'min:1', config('constants.validation.max_name_length')],
            'figure_ids' => ['required', 'array', 'min:2', config('constants.validation.max_compound_figure_figures')],
            'figure_ids.*' => ['required', 'integer', 'exists:figures,id'],
            'figure_ids' => [new SuccessiveFiguresConsistent],
            'compound_figure_videos' => ['nullable', 'array', config('constants.validation.max_videos')],
            'compound_figure_videos.*' => ['required', 'array', 'required_array_keys:url,description'],
            'compound_figure_videos.*.url' => ['required', 'string', 'url', config('constants.validation.max_url_length')],
            'compound_figure_videos.*.description' => ['nullable', 'string', config('constants.validation.max_videos')],
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
            'figure_family.id' => 'figure family',
            'figure_family.name' => 'figure family name',
            'figure_ids.*' => 'figure',
            'compound_figure_videos.*.url' => 'figure video URL',
            'compound_figure_videos.*.description' => 'figure video description',
        ];
    }

}
