<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function __construct(
        public PurchaseOrderService $poService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $pos = PurchaseOrder::where('store_id', $storeId)
            ->with(['supplier', 'user', 'items.product'])
            ->latest('order_date')
            ->paginate(15);

        $suppliers = Supplier::where('store_id', $storeId)->get();
        $products = Product::where('store_id', $storeId)->get();

        return Inertia::render('purchase-orders/index', [
            'purchase_orders' => $pos,
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['store_id'] = $storeId;
        $validated['user_id'] = $user->id;

        $this->poService->createPo($validated);

        return Redirect::back()->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.quantity_received_now' => ['required', 'integer', 'min:0'],
        ]);

        $this->poService->receiveItems($purchaseOrder, $validated['items'], $user->id);

        return Redirect::back()->with('success', 'Penerimaan barang PO berhasil dicatat.');
    }
}
