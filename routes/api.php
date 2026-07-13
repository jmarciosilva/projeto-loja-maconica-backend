<?php

use App\Modules\Administration\Http\Controllers\MemberApprovalController;
use App\Modules\Administration\Http\Controllers\PublicLodgeController;
use App\Modules\Administration\Http\Controllers\RegistrationOptionsController;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Auth\Http\Controllers\RegistrationController;
use App\Modules\Auth\Http\Middleware\ResolveTenantFromHeader;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::post('auth/register/lodge', [RegistrationController::class, 'registerLodge'])
        ->middleware('throttle:5,1');

    Route::post('auth/register/member', [RegistrationController::class, 'registerMember'])
        ->middleware('throttle:5,1');

    Route::get('public/lodges', [PublicLodgeController::class, 'index']);
    Route::get('public/lodge-registration-options', [RegistrationOptionsController::class, 'index']);

    Route::middleware(['auth:sanctum', ResolveTenantFromHeader::class])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('members/pending', [MemberApprovalController::class, 'index']);
        Route::post('members/{user:uuid}/approve', [MemberApprovalController::class, 'approve']);
        Route::post('members/{user:uuid}/reject', [MemberApprovalController::class, 'reject']);
    });
});
