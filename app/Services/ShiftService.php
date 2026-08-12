<?php

namespace App\Services;

use App\Events\ShiftStatusChangedEvent;
use App\Exceptions\ShiftAlreadyClosedException;
use App\Models\CashierShift;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class ShiftService
{
    /**
     * Membuka shift kasir baru.
     *
     * @throws ShiftAlreadyClosedException jika user masih memiliki shift aktif
     */
    public function openShift(int $storeId, int $userId, float $startingCash, ?string $notes = null): CashierShift
    {
        $existingShift = $this->getActiveShift($storeId, $userId);

        if ($existingShift !== null) {
            throw new ShiftAlreadyClosedException('Pengguna sudah memiliki shift kasir yang aktif.');
        }

        $shift = CashierShift::create([
            'store_id' => $storeId,
            'user_id' => $userId,
            'opened_at' => Carbon::now(),
            'starting_cash' => $startingCash,
            'status' => 'open',
            'notes' => $notes,
        ]);

        Event::dispatch(new ShiftStatusChangedEvent($shift));

        return $shift;
    }

    /**
     * Menutup shift kasir & menghitung rekonsiliasi kas.
     */
    public function closeShift(int $shiftId, float $endingCashActual, ?string $notes = null): CashierShift
    {
        return DB::transaction(function () use ($shiftId, $endingCashActual, $notes) {
            $shift = CashierShift::lockForUpdate()->findOrFail($shiftId);

            if ($shift->status === 'closed') {
                throw new ShiftAlreadyClosedException('Shift kasir ini sudah ditutup.');
            }

            // Hitung total penjualan tunai selama shift ini
            $totalCashSales = Transaction::where('cashier_shift_id', $shiftId)
                ->where('status', 'completed')
                ->where('payment_method', 'cash')
                ->sum('total_amount');

            $expectedCash = $shift->starting_cash + $totalCashSales;
            $cashDifference = $endingCashActual - $expectedCash;

            $shift->update([
                'closed_at' => Carbon::now(),
                'ending_cash_expected' => $expectedCash,
                'ending_cash_actual' => $endingCashActual,
                'cash_difference' => $cashDifference,
                'status' => 'closed',
                'notes' => $notes ? trim(($shift->notes ?? '').' '.$notes) : $shift->notes,
            ]);

            Event::dispatch(new ShiftStatusChangedEvent($shift));

            return $shift;
        });
    }

    public function getActiveShift(int $storeId, int $userId): ?CashierShift
    {
        return CashierShift::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();
    }
}
