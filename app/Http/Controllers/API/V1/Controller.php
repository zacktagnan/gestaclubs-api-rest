<?php

namespace App\Http\Controllers\API\V1;

abstract class Controller
{
    public function __construct()
    {
        ray()->showQueries();
    }
}
