<?php

use App\Exceptions\PurchaseStockConflictException;
use App\Exceptions\ProductDeletionConflictException;
use App\Exceptions\UserDeletionConflictException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\EnsureUserIsAdmin;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->group('session.api', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return null;
        });

        $exceptions->render(function (ModelNotFoundException $exception, $request) {
            if ($request->is('api/*') && $exception->getModel() === App\Models\User::class) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            if ($request->is('api/*') && $exception->getModel() === App\Models\Product::class) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            return null;
        });

        $exceptions->render(function (NotFoundHttpException $exception, $request) {
            $previous = $exception->getPrevious();

            if (
                $request->is('api/*')
                && $previous instanceof ModelNotFoundException
                && $previous->getModel() === App\Models\User::class
            ) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            if (
                $request->is('api/*')
                && $previous instanceof ModelNotFoundException
                && $previous->getModel() === App\Models\Product::class
            ) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            return null;
        });

        $exceptions->render(function (UserDeletionConflictException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 409);
            }

            return null;
        });

        $exceptions->render(function (ProductDeletionConflictException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 409);
            }

            return null;
        });

        $exceptions->render(function (PurchaseStockConflictException $exception, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 409);
            }

            return null;
        });
    })->create();
