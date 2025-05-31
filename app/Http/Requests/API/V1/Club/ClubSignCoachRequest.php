<?php

namespace App\Http\Requests\API\V1\Club;

use Illuminate\Foundation\Http\FormRequest;

class ClubSignCoachRequest extends FormRequest
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
            'coach_id' => 'required|exists:coaches,id',
            'salary' => 'required|integer|min:1',
        ];
    }
}
