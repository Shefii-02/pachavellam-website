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
        $this->app->singleton(\App\Services\SeasonAccess::class);
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // the main admin sidebar lists one tab per exam type, on every page that uses the layout
        \Illuminate\Support\Facades\View::composer('admin.main-layout', function ($view) {
            $view->with('examTabs', \App\Models\CompetitiveExam::active()->get());
        });

        // staff sign-ins go to the activity log
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($e) {
            if ($e->user && $e->user->type !== 'Student') {
                \App\Services\ActivityLogger::record('login', 'Session', 'Signed in');
            }
        });
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($e) {
            if ($e->user && $e->user->type !== 'Student') {
                \App\Services\ActivityLogger::record('logout', 'Session', 'Signed out');
            }
        });
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($e) {
            $u = $e->user;
            if ($u && $u->type !== 'Student') {
                \App\Services\ActivityLogger::record('login_failed', 'Session', 'Wrong password for ' . ($u->email ?: $u->name));
            }
        });

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
