<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $units = Unit::where('store_id', $storeId)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return Inertia::render('units/index', [
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'short_name' => ['required', 'string', 'max:20'],
        ]);

        $validated['store_id'] = $storeId;

        Unit::create($validated);

        return Redirect::back()->with('success', 'Satuan produk berhasil ditambahkan.');
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'short_name' => ['required', 'string', 'max:20'],
        ]);

        $unit->update($validated);

        return Redirect::back()->with('success', 'Satuan produk berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return Redirect::back()->with('success', 'Satuan produk berhasil dihapus.');
    }
}
