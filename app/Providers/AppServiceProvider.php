<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Menu;
use App\Models\Bahan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $menuMenipis = Menu::where('stok', '<', 10)->where('is_available', true)->get();
            $bahanMenipis = Bahan::where('stok', '<', 10)->get();
            $stokMenipisCount = $menuMenipis->count() + $bahanMenipis->count();

            $view->with(compact('menuMenipis', 'bahanMenipis', 'stokMenipisCount'));
        });
    }
}
