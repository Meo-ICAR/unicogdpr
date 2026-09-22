<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CompanyAdminPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    CompanyAdminPanelProvider::class,
];
