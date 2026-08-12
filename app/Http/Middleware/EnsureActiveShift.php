<?php

namespace App\Http\Middleware;

use App\Services\ShiftService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveShift
{
    public function __construct(
        public ShiftService $shiftService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $storeId = $user->store_id ?? 1;
            $activeShift = $this->shiftService->getActiveShift($storeId, $user->id);

            if ($activeShift === null) {
                if ($request->wantsJson()) {
                    return ResponseFacade::json([
                        'message' => 'Shift kasir tidak aktif. Silakan buka shift kasir terlebih dahulu.',
                    ], 422);
                }

                return Redirect::route('shifts.index')->with('error', 'Silakan buka shift kasir terlebih dahulu sebelum melakukan transaksi.');
            }
        }

        return $next($request);
    }
}
