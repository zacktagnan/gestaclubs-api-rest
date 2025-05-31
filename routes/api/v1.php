<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Auth\{
    RegisterController,
    LoginController,
    LogoutController,
};
use App\Http\Controllers\API\V1\Management\{
    ClubController,
    CoachController,
    PlayerController,
};

Route::prefix('auth')->as('auth.')->group(function () {
    Route::post('/register', RegisterController::class)->name('register');
    Route::post('/login', LoginController::class)->name('login');

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('/logout', LogoutController::class)->name('logout');
    });
});

Route::prefix('management')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('clubs', ClubController::class);
    Route::patch('books/{book}/stock', [ClubController::class, 'updateBudget'])->name('clubs.updateBudget');

    Route::apiResource('coach', CoachController::class);
    Route::delete('/coaches/{coach}/club', [CoachController::class, 'removeFromClub'])->name('coaches.removeFromClub');

    Route::apiResource('players', PlayerController::class);
    Route::delete('/players/{player}/club', [CoachController::class, 'removeFromClub'])->name('players.removeFromClub');
});
