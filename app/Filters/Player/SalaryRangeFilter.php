<?php

namespace App\Filters\Player;

use App\Filters\QueryStringFilter;
use Illuminate\Database\Eloquent\Builder;

class SalaryRangeFilter extends QueryStringFilter
{
    protected ?int $min;
    protected ?int $max;

    public function __construct()
    {
        // $this->min = request()->query('salary_min');
        // $this->max = request()->query('salary_max');

        // Para asegurar que los datos se almacenen como de tipo INT
        // $this->min = request()->has('salary_min') ? (int) request()->query('salary_min') : null;
        // $this->max = request()->has('salary_max') ? (int) request()->query('salary_max') : null;

        // Para asegurar que los datos se almacenen como de tipo INT
        // $this->min = is_numeric(request()->has('salary_min')) ? (int) request()->query('salary_min') : null;
        // $this->max = is_numeric(request()->has('salary_max')) ? (int) request()->query('salary_max') : null;

        $this->min = is_numeric(request()->query('salary_min')) ? (int) request()->query('salary_min') : null;
        $this->max = is_numeric(request()->query('salary_max')) ? (int) request()->query('salary_max') : null;
    }

    protected function hasValue(): bool
    {
        return request()->has('salary_min') || request()->has('salary_max');
    }

    protected function apply(Builder $builder): Builder
    {
        // dd($this->min, $this->max);
        // dump($this->min, $this->max);
        // exit;
        // Log::debug('Salary min/max', [
        //     'min' => $this->min,
        //     'max' => $this->max,
        // ]);
        // Log::channel('stack')->debug('Salary min/max', [
        //     'min' => $this->min,
        //     'max' => $this->max,
        // ]);
        // Log::debug('Salary min/max', [
        //     'min' => $this->min,
        //     'max' => $this->max,
        // ]);

        // file_put_contents(
        //     storage_path('logs/debug-salary.log'),
        //     json_encode([
        //         'min' => $this->min,
        //         'max' => $this->max,
        //     ]) . PHP_EOL,
        //     FILE_APPEND
        // );

        // dump('Aloha desde el SalaryRangeFilter...');
        // dd(request()->query());

        // throw new \Exception('SalaryRangeFilter::apply() was called');

        // dump('APPLY SalaryRangeFilter', $this->min, $this->max);


        return $builder
            ->when($this->min !== null, fn($q) => $q->where('salary', '>=', $this->min))
            ->when($this->max !== null, fn($q) => $q->where('salary', '<=', $this->max));
    }

    protected function filterName(): string
    {
        // Este nombre de columna no se usa directamente porque se usan 2 parámetros.
        // Así que se podría devolver como algo genérico o una cadena vacía.
        // Todo para respetar lo que exige el QueryStringFilter
        return 'salary_range';
    }
}
