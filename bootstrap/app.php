<?php

use App\Models\Rolador;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $previousException = $e->getPrevious();
            if ($previousException instanceof ModelNotFoundException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => match ($previousException->getModel()) {
                            Rolador::class => 'Rolador no encontrado',
                            default => 'Recurso no encontrado',
                        },
                    ], 404);
                }
                // For web requests, you might redirect or show a custom view
                // return redirect()->route('home')->with('error', 'The requested resource was not found.');
            }
            // Let Laravel handle other NotFoundHttpExceptions
        });
    })->create();
