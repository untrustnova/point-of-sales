<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProductService
{
    public function createProduct(array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);
            $data['sku'] = $data['sku'] ?? $this->generateSku($data['name']);
            $data['barcode'] = $data['barcode'] ?? $data['sku'];

            if ($image !== null) {
                $data['image_path'] = $this->uploadAndCompressImage($image);
            }

            $product = Product::create($data);

            if (! empty($data['has_variants']) && ! empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variantData['sku'] = $variantData['sku'] ?? ($product->sku.'-'.Str::slug($variantData['name']));
                    $variantData['barcode'] = $variantData['barcode'] ?? $variantData['sku'];
                    $product->variants()->create($variantData);
                }

                $product->update([
                    'stock' => $product->variants()->sum('stock'),
                ]);
            }

            return $product;
        });
    }

    public function updateProduct(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($product, $data, $image) {
            if ($image !== null) {
                if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $data['image_path'] = $this->uploadAndCompressImage($image);
            }

            $product->update($data);

            return $product->fresh('variants');
        });
    }

    public function generateSku(string $productName): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $productName), 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }

        return $prefix.'-'.mt_rand(100000, 999999);
    }

    private function uploadAndCompressImage(UploadedFile $file): string
    {
        $filename = 'products/'.Str::uuid().'.webp';

        $img = Image::read($file);
        $img->scaleDown(width: 800);
        $encoded = $img->toWebp(80);

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }
}
