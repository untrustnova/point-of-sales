<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $store = Store::findOrFail($storeId);

        return Inertia::render('settings/store', [
            'store' => $store,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $store = Store::findOrFail($storeId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'settings' => ['nullable', 'array'],
            'settings.tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'settings.receipt_header' => ['nullable', 'string'],
            'settings.receipt_footer' => ['nullable', 'string'],
            'settings.currency' => ['nullable', 'string'],
        ]);

        $store->update($validated);

        return Redirect::back()->with('success', 'Pengaturan toko berhasil diperbarui.');
    }
}
