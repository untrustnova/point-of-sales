<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function __construct(
        public ShiftService $shiftService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $activeShift = $this->shiftService->getActiveShift($storeId, $user->id);
        $shiftHistory = CashierShift::where('store_id', $storeId)
            ->where('user_id', $user->id)
            ->with(['user', 'transactions'])
            ->latest('opened_at')
            ->paginate(15);

        return Inertia::render('shifts/index', [
            'active_shift' => $activeShift,
            'shift_history' => $shiftHistory,
        ]);
    }

    public function open(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $validated = $request->validate([
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->shiftService->openShift(
            storeId: $storeId,
            userId: $user->id,
            startingCash: (float) $validated['starting_cash'],
            notes: $validated['notes'] ?? null
        );

        return Redirect::back()->with('success', 'Shift kasir berhasil dibuka.');
    }

    public function close(Request $request, CashierShift $shift): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'actual_ending_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->shiftService->closeShift(
            shiftId: $shift->id,
            endingCashActual: (float) $validated['actual_ending_cash'],
            notes: $validated['notes'] ?? null
        );

        return Redirect::back()->with('success', 'Shift kasir berhasil ditutup.');
    }
}
