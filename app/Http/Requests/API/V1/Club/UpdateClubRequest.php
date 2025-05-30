<?php

namespace App\Http\Requests\API\V1\Club;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @method mixed route(string $key = null, mixed $default = null)
 */
class UpdateClubRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:clubs,name,' . $this->route('club')->id,
            'budget' => 'required|integer|min:7400000',
        ];
    }
}
