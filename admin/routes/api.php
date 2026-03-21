<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CaseStudyController;
use App\Http\Controllers\Api\V1\ContentSlugController;
use App\Http\Controllers\Api\V1\LeadContactController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me'])->middleware('throttle:60,1');
        Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('throttle:60,1');
        Route::get('leads', [LeadContactController::class, 'index'])->middleware('throttle:60,1');
    });

    Route::middleware(['api.public', 'throttle:120,1'])->group(function () {
        Route::get('content/slugs', ContentSlugController::class);
        Route::get('cases', [CaseStudyController::class, 'index']);
        Route::get('cases/{slug}', [CaseStudyController::class, 'show'])->where('slug', '[a-z0-9\-]+');
        Route::get('services', [ServiceController::class, 'index']);
        Route::get('services/{slug}', [ServiceController::class, 'show'])->where('slug', '[a-z0-9\-]+');
    });

    Route::post('lead/contact', [LeadContactController::class, 'store'])
        ->middleware(['api.public', 'throttle:10,1']);
});
