<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FigureFamilyUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $figureFamily = $this->route('figure_family');
        $user = $this->user();
        return $figureFamily && $user && $user->can('update', $figureFamily);
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
        ];
    }
}
