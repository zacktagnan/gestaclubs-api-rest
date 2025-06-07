<?php

namespace App\Traits;

trait CliOutputStyler
{
    public function renderErrorMessage(string $message): void
    {
        \Termwind\render(<<<HTML
            <div class="mt-1 mb-0 ml-2">
                <span class="bg-red-700 text-white px-1"> ERROR </span>
                <span class="ml-1">{$message}</span>
            </div>
        HTML);
    }

    public function renderInfoMessage(string $message): void
    {
        \Termwind\render(<<<HTML
            <div class="my-1.5 ml-2">
                <span class="bg-blue-700 text-white px-1"> INFO </span>
                <span class="ml-1">{$message}</span>
            </div>
        HTML);
    }
}
