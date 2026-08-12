<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->store_id) {
            $store = Store::find($user->store_id);

            if ($store && ! $store->is_active) {
                if ($request->wantsJson()) {
                    return ResponseFacade::json(['message' => 'Toko Anda saat ini nonaktif.'], 403);
                }

                Auth::logout();

                return Redirect::route('login')->with('error', 'Toko Anda saat ini dalam status nonaktif. Hubungi Owner.');
            }
        }

        return $next($request);
    }
}
