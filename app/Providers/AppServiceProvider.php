<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\CategoryPaid;
use App\Models\CategoryFree;
use App\Models\web\Treandingnews;
use Illuminate\Pagination\Paginator;
use View;
use App\Models\Notification;

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
        //
        $category_paid  = CategoryPaid::where('status',1)->orderby('position')->get();
        $category_free  = CategoryFree::where('status',1)->orderby('position')->get();
        $treandingnews = Treandingnews::where('status',1)->orderby('position')->limit(4)->get();
           Paginator::useBootstrap();
           
           
        
        View::share('category_paid',$category_paid);
        View::share('category_free',$category_free);
        View::share('treandingnews',$treandingnews);
    }
}
