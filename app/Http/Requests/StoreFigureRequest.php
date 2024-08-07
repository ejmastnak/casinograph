<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Figure;
use App\Rules\FigureUniqueForUser;

class StoreFigureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Figure::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', config('constants.validation.max_name_length'), new FigureUniqueForUser],
            'description' => ['nullable', 'string', 'min:0', config('constants.validation.max_description_length')],
            'weight' => ['nullable', 'integer', 'min:1', config('constants.validation.max_weight')],
            'from_position_id' => ['required', 'integer', 'exists:positions,id'],
            'to_position_id' => ['required', 'integer', 'exists:positions,id'],
            'figure_family' => ['nullable', 'array', 'required_array_keys:id,name'],
            'figure_family.id' => ['nullable', 'integer', 'exists:figure_families,id'],
            'figure_family.name' => ['required_with:figure_family', 'string', 'min:1', config('constants.validation.max_name_length')],
            'figure_videos' => ['nullable', 'array', config('constants.validation.max_videos')],
            'figure_videos.*' => ['required', 'array', 'required_array_keys:url,description'],
            'figure_videos.*.url' => ['required', 'string', 'url', config('constants.validation.max_url_length')],
            'figure_videos.*.description' => ['nullable', 'string', config('constants.validation.max_description_length')],
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
            'figure_videos.*.url' => 'figure video URL',
            'figure_videos.*.description' => 'figure video description',
        ];
    }

}
