<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductExcelController extends Controller
{
    public function export(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        return Excel::download(new ProductsExport($storeId), 'Daftar_Produk_POS_Nova.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $user = $request->user();
        $storeId = $user->store_id ?? 1;

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        Excel::import(new ProductsImport($storeId), $request->file('file'));

        return Redirect::back()->with('success', 'Data produk berhasil diimport massal.');
    }
}
