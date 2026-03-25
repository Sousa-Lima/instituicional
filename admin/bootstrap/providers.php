<?php

use App\Providers\AppServiceProvider;

$providers = [
    AppServiceProvider::class,
];

if (class_exists(\Filament\PanelProvider::class)) {
    $providers[] = App\Providers\Filament\AdminPanelProvider::class;
}

return $providers;
