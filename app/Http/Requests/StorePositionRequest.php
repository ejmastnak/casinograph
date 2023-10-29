<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Position;

class StorePositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Position::class);
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
            'position_family_id' => ['nullable', 'integer', 'exists:App\Models\PositionFamily,id'],
            'position_family' => ['nullable', 'array', 'required_array_keys:id,name'],
            'position_family.name' => ['required_with:position_family', 'string', 'min:1', config('validation.max_name_length')],
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
            'position_family_id' => 'position family',
            'position_family.name' => 'position family name',
        ];
    }

}
