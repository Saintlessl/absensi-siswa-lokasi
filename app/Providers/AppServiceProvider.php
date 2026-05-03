<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Paksa semua URL menggunakan HTTPS
        if (config('app.env') !== 'local' || str_contains(request()->getHost(), 'trycloudflare.com')) {
            URL::forceScheme('https');
        }
    }
}