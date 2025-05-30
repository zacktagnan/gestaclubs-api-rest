<?php

namespace App\Http\Requests\API\V1\Coach;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @method mixed route(string $key = null, mixed $default = null)
 */
class UpdateCoachRequest extends FormRequest
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
            'full_name' => 'required|string|max:100|unique:coaches,full_name,' . $this->route('coach')->id,
            'email' => 'required|email|unique:coaches,email,' . $this->route('coach')->id,
            'salary' => 'required|integer|min:700000',

            'club_id' => 'nullable|exists:clubs,id|unique:coaches,club_id,' . $this->route('coach')->id,
        ];
    }
}
