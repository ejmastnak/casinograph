<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Position;

class PositionUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $position = Position::find($this->route('position'));
        return $position && $this->user()->can('update', $position);
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
        ];
    }
}
