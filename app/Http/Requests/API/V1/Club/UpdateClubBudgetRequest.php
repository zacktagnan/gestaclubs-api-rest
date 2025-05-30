<?php

namespace App\Http\Requests\API\V1\Club;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @method mixed route(string $key = null, mixed $default = null)
 * @method mixed input(string $key = null, mixed $default = null)
 */
class UpdateClubBudgetRequest extends FormRequest
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
            'budget' => 'required|integer|min:1',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $club = $this->route('club');

            // Suma de salarios de los jugadores, puede ser 0 si no hay jugadores
            $playersSalary = $club->players()->sum('salary');

            // Salario del coach, puede ser 0 si no hay coach
            $coachSalary = $club->coach?->salary ?? 0;

            // Total que debe tener al menos el budget
            $requiredBudget = $playersSalary + $coachSalary;

            // Validamos si el budget enviado es menor a ese total
            if ($this->input('budget') < $requiredBudget) {
                $v->errors()->add(
                    'budget',
                    "El presupuesto mínimo requerido es de {$requiredBudget} para cubrir los salarios actuales."
                );
            }
        });
    }
}
