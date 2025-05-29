<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    ray('Aloha!!');

    return view('welcome');
});
