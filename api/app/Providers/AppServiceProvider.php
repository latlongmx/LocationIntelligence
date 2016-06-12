<?php

namespace App\Providers;

//use DB;
use Illuminate\Support\ServiceProvider;
//use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        /*DB::listen(function($query) {
            Log::info('DB::listening: ');
            Log::info($query->sql);
            Log::info($query->bindings);
            Log::info($query->time);
        });*/
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
