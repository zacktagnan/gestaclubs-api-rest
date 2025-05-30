<?php

namespace App\Http\Requests\API\V1\Player;

use Illuminate\Foundation\Http\FormRequest;

class StorePlayerRequest extends FormRequest
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
            'full_name' => 'required|string|max:100|unique:players',
            'email' => 'required|email|unique:players,email',
            'salary' => 'required|integer|min:400000',

            'club_id' => 'nullable|exists:clubs,id',
        ];
    }
}
