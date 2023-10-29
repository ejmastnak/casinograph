<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PositionFamilyUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $positionFamily = $this->route('position_family');
        $user = $this->user();
        return $positionFamily && $user && $user->can('update', $positionFamily);
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
