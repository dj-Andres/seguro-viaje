<?php

use App\Support\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApi = fn (Request $request) => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($isApi);

        $exceptions->render(function (ValidationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return ApiResponse::error(
                    'Los datos proporcionados no son válidos.',
                    422,
                    $e->errors(),
                );
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return ApiResponse::error('El recurso solicitado no fue encontrado.', 404);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                report($e);

                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'Ha ocurrido un error interno en el servidor.';

                return ApiResponse::error($message, 500);
            }
        });
    })->create();
