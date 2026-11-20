<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__ . '/../routes/web.php',
        // commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        commands: __DIR__ . '/../routes/console.php',
        using: function () {
            Route::middleware('api')
                ->prefix('api')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/web.php'));

            Route::middleware('internal.secret')
                ->prefix('internal')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/internal.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // behind Cloudflare / the web server: use the visitor's address from X-Forwarded-For, not the proxy's. Without this every visitor of a
        // Cloudflare node shares one IP, so the flood gate (SecurityGate) rate-limits and auto-blocks all of them together.
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);

        // request log for Dev Tools (api logs + daily analytics)
        // flood / DoS gate runs before everything else
        $middleware->prependToGroup('web', \App\Http\Middleware\SecurityGate::class);
        $middleware->prependToGroup('api', \App\Http\Middleware\SecurityGate::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\LogRequest::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\EnforceDeviceSession::class);
        $middleware->appendToGroup('api', \App\Http\Middleware\EnforceDeviceSession::class);
        $middleware->appendToGroup('api', \App\Http\Middleware\LogRequest::class);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'admin.area' => \App\Http\Middleware\EnsureAdminArea::class,
            'staff.can' => \App\Http\Middleware\StaffCan::class,
            'series.exam' => \App\Http\Middleware\SeriesExamRoute::class,
            'staff.module' => \App\Http\Middleware\StaffModule::class,
            'season.access' => \App\Http\Middleware\EnsureSeasonAccess::class,
            'captcha' => \App\Http\Middleware\RequireCaptcha::class,
            'internal.secret' => \App\Http\Middleware\VerifyInternalSecret::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // an expired / missing CSRF token (old page kept by an in-app browser, session cookie not stored yet): back to the form with a fresh token and a message, not a 419 page
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your session expired. Please refresh the page and try again.'], 419);
            }
            $to = $request->is('sign-in', 'sign-up') ? url('sign-in') : url()->previous(url('/'));

            return redirect($to)->withInput($request->except('password', 'password_confirmation', '_token'))->with('message', 'Your session expired. Please try again.');
        });
        $exceptions->report(function (Throwable $exception) {
            $content['message'] = $exception->getMessage();
            $content['file'] = $exception->getFile();
            $content['line'] = $exception->getLine();
            $content['trace'] = $exception->getTrace();
            $content['url'] = request()->url();
            $content['body'] = request()->all();
            $content['ip'] = request()->ip();

            // Render the exception view and save it to an HTML file
            $htmlContent = view('email.exception')->withContent($content)->render();

            // Define the path to save the HTML file
            $htmlFilePath = storage_path('logs/exceptions/' . \Carbon\Carbon::now()->format('d_m_Y_H_i_s') . '.html');

            // Ensure the directory exists
            if (!File::exists(dirname($htmlFilePath))) {
                File::makeDirectory(dirname($htmlFilePath), 0755, true);
            }

            // Save the rendered HTML to the file
            File::put($htmlFilePath, $htmlContent);
        });
    })->create();
