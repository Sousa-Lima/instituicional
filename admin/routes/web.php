<?php

use App\Http\Controllers\Admin\LinkedinOAuthController;
use App\Http\Controllers\ApiRootController;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', ApiRootController::class);

Route::prefix('admin/integrations/linkedin')
	->middleware([FilamentAuthenticate::class])
	->group(function (): void {
		Route::get('connect', [LinkedinOAuthController::class, 'redirect'])->name('admin.linkedin.connect');
		Route::get('callback', [LinkedinOAuthController::class, 'callback'])->name('admin.linkedin.callback');
	});
