<?php

namespace App\Http\Requests\API\V1\Club;

use App\Traits\API\V1\ValidatesClubBudget;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @method mixed route(string $key = null, mixed $default = null)
 */
class UpdateClubBudgetRequest extends FormRequest
{
    use ValidatesClubBudget;

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
            'budget' => 'required|integer|min:1',
        ];
    }

    public function withValidator($validator): void
    {
        $this->validateBudgetSufficiency($validator);
    }
}
