<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\TransactionResource;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\ShiftService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function __construct(
        public TransactionService $transactionService,
        public ShiftService $shiftService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $activeShift = $this->shiftService->getActiveShift($storeId, $user->id);

        $categories = Category::where('store_id', $storeId)->where('is_active', true)->get();
        $customers = Customer::where('store_id', $storeId)->where('is_active', true)->get();

        $products = Product::where('store_id', $storeId)
            ->where('is_active', true)
            ->with(['category', 'unit', 'variants'])
            ->orderBy('name')
            ->get();

        $heldTransactions = Transaction::where('store_id', $storeId)
            ->where('status', 'held')
            ->with('items')
            ->latest()
            ->get();

        return Inertia::render('pos/index', [
            'active_shift' => $activeShift,
            'categories' => $categories,
            'customers' => $customers,
            'products' => ProductResource::collection($products),
            'held_transactions' => TransactionResource::collection($heldTransactions),
        ]);
    }

    public function storeTransaction(CreateTransactionRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['user_id'] = $user->id;

        $transaction = $this->transactionService->createTransaction($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses.',
                'transaction' => new TransactionResource($transaction->load(['items', 'payments'])),
            ]);
        }

        return back()->with('success', 'Transaksi berhasil diproses.');
    }

    public function void(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->transactionService->voidTransaction($transaction->id, $user->id, $request->input('reason'));

        return back()->with('success', 'Transaksi berhasil dibatalkan (void).');
    }
}
