<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController extends Controller
{
    public function __construct(
        public StockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $movements = StockMovement::where('store_id', $storeId)
            ->with(['product', 'user'])
            ->latest()
            ->paginate(20);

        $products = Product::where('store_id', $storeId)->get();

        return Inertia::render('stock/index', [
            'movements' => $movements,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'type' => ['required', 'in:in,out,adjustment,damage,loss'],
            'quantity' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->stockService->recordMovement(
            storeId: $storeId,
            productId: $validated['product_id'],
            type: $validated['type'],
            quantity: (int) $validated['quantity'],
            variantId: $validated['product_variant_id'] ?? null,
            userId: $user->id,
            notes: $validated['notes'] ?? null
        );

        return Redirect::back()->with('success', 'Stok opname/mutasi berhasil dicatat.');
    }
}
