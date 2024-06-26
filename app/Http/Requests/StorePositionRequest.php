<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Position;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

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
            'name' => [
                'required',
                'string',
                'min:1',
                config('constants.validation.max_name_length'),
                Rule::unique('positions')->where(fn (Builder $query) => $query->where('user_id', Auth::id())),
            ],
            'description' => ['nullable', 'string', 'min:0', config('constants.validation.max_description_length')],
            'position_family' => ['nullable', 'array', 'required_array_keys:id,name'],
            'position_family.id' => ['nullable', 'integer', 'exists:position_families,id'],
            'position_family.name' => ['required_with:position_family', 'string', 'min:1', config('constants.validation.max_name_length')],
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
            'name' => 'position name',
            'position_family.id' => 'position family',
            'position_family.name' => 'position family name',
        ];
    }

}
