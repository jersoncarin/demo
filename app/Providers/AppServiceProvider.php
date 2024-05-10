<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::unguard();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        FilamentAsset::register([
            Css::make('leaflet', resource_path('css/leaflet.css')),
            Css::make('geosearch', resource_path('css/geosearch.css')),
            Js::make('leaflet-js',  resource_path('js/leaflet.js')),
            Js::make('geosearch-js',  resource_path('js/geosearch.umd.js')),
        ]);
    }
}
