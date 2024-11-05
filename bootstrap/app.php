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
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
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
