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
    Route::prefix('clubs')->as('clubs.')->group(function () {
        Route::patch('{club}/budget', [ClubController::class, 'updateBudget'])->name('updateBudget');
        Route::post('{club}/sign-player', [ClubController::class, 'signPlayer'])->name('signPlayer');
        Route::post('{club}/sign-coach', [ClubController::class, 'signCoach'])->name('signCoach');
        Route::get('{club}/players', [ClubController::class, 'players'])->name('players');
    });

    // -> Para evitar colisiones con /coaches/{coach}, se coloca antes de la apiResource
    Route::get('/coaches/unassigned-list', [CoachController::class, 'unassignedList'])->name('coaches.unassigned-list');
    Route::apiResource('coaches', CoachController::class);
    Route::prefix('coaches')->as('coaches.')->group(function () {
        Route::delete('{coach}/club', [CoachController::class, 'removeFromClub'])->name('removeFromClub');
    });

    // -> Para evitar colisiones con /players/{player}, se coloca antes de la apiResource
    Route::get('/players/unassigned-list', [PlayerController::class, 'unassignedList'])->name('players.unassigned-list');
    Route::apiResource('players', PlayerController::class);
    Route::delete('/players/{player}/club', [PlayerController::class, 'removeFromClub'])->name('players.removeFromClub');
});
