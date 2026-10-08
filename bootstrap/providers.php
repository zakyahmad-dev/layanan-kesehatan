<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,

    // Provider paket didaftarkan eksplisit supaya aplikasi tetap jalan
    // walau `php artisan package:discover` belum sempat berjalan
    // (mis. `composer install --no-scripts`).
    Laravel\Sanctum\SanctumServiceProvider::class,
    Laravel\Tinker\TinkerServiceProvider::class,
];
