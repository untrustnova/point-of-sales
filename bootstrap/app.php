<?php

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidTransactionException;
use App\Exceptions\ShiftAlreadyClosedException;
use App\Http\Middleware\EnsureActiveShift;
use App\Http\Middleware\EnsureStoreActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetTeamUrlDefaults;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SetTeamUrlDefaults::class,
            EnsureStoreActive::class,
        ]);

        $middleware->alias([
            'active_shift' => EnsureActiveShift::class,
            'active_store' => EnsureStoreActive::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (InsufficientStockException $e, Request $request) {
            if ($request->wantsJson()) {
                return ResponseFacade::json(['message' => $e->getMessage()], 422);
            }

            return Redirect::back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (ShiftAlreadyClosedException $e, Request $request) {
            if ($request->wantsJson()) {
                return ResponseFacade::json(['message' => $e->getMessage()], 422);
            }

            return Redirect::back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (InvalidTransactionException $e, Request $request) {
            if ($request->wantsJson()) {
                return ResponseFacade::json(['message' => $e->getMessage()], 422);
            }

            return Redirect::back()->with('error', $e->getMessage());
        });
    })->create();
