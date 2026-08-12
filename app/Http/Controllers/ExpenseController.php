<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $expenses = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->with('user')
            ->latest('expense_date')
            ->paginate(15);

        $totalAmount = Expense::where('store_id', $storeId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        return Inertia::render('expenses/index', [
            'expenses' => $expenses,
            'total_amount' => (float) $totalAmount,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['store_id'] = $storeId;
        $validated['user_id'] = $user->id;

        Expense::create($validated);

        return Redirect::back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return Redirect::back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
