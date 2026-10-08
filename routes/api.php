<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HelpRequestController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\RequestVolunteerController;
use App\Http\Controllers\Api\VolunteerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — primeira versão
|--------------------------------------------------------------------------
| Todas as rotas têm o prefixo /api (bootstrap/app.php) e retornam JSON.
| A documentação completa dos endpoints está em docs/API.md.
*/

// Autenticação — limite mais restrito contra força bruta e cadastro em massa.
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Conta do usuário autenticado
    Route::get('me', [MeController::class, 'show'])->name('me.show');
    Route::put('me', [MeController::class, 'update'])->name('me.update');
    Route::put('me/password', [MeController::class, 'updatePassword'])->name('me.password');
    Route::delete('me', [MeController::class, 'destroy'])->name('me.destroy');
    Route::post('me/avatar', [AvatarController::class, 'store'])->middleware('throttle:uploads')->name('me.avatar.store');
    Route::delete('me/avatar', [AvatarController::class, 'destroy'])->name('me.avatar.destroy');
    Route::get('me/activities', [ActivityController::class, 'index'])
        ->middleware('user.type:volunteer')
        ->name('me.activities');

    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

    // ONGs
    Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');

    // Voluntários
    Route::get('volunteers/{volunteer}', [VolunteerController::class, 'show'])->name('volunteers.show');
    Route::put('volunteers/{volunteer}', [VolunteerController::class, 'update'])->name('volunteers.update');

    // Solicitações
    Route::apiResource('requests', HelpRequestController::class)
        ->parameters(['requests' => 'helpRequest']);

    // Fluxo de interesse
    Route::post('requests/{helpRequest}/interest', [InterestController::class, 'store'])->name('requests.interest.store');
    Route::delete('requests/{helpRequest}/interest', [InterestController::class, 'destroy'])->name('requests.interest.destroy');
    Route::get('requests/{helpRequest}/volunteers', [RequestVolunteerController::class, 'index'])->name('requests.volunteers.index');
    Route::patch('request-volunteers/{requestVolunteer}', [RequestVolunteerController::class, 'update'])->name('request-volunteers.update');
});
