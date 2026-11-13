<?php

namespace App\Providers;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Paksa semua URL menggunakan HTTPS
        if (config('app.env') !== 'local' || str_contains(request()->getHost(), 'trycloudflare.com')) {
            URL::forceScheme('https');
        }

        // Bagikan variabel branding global ke seluruh Blade view
        View::composer('*', function ($view) {
            $view->with('appLogoUrl', SchoolSetting::getLogoUrl());
            $view->with('appFaviconUrl', SchoolSetting::getFaviconUrl());
            $view->with('schoolName', SchoolSetting::value('nama_sekolah', 'SMK Negri 1 Ciomas'));
        });
    }
}
