<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->isProductSale() && $this->missing('services') && $this->hasAny(['service_id', 'quoted_price'])) {
            $this->merge(['services' => [$this->only(['service_id', 'quoted_price'])]]);
        }
    }

    public function rules(): array
    {
        $productSale = $this->isProductSale();
        $price = ['required', 'regex:/\A[1-9][0-9]{0,14}\z/'];

        return [
            'customer_name' => ['nullable', 'string', 'max:120'],
            'services' => $productSale ? ['prohibited'] : ['required', 'array', 'list', 'min:1'],
            'services.*' => ['array:service_id,quoted_price,used_product_id'],
            'services.*.service_id' => ['required', 'integer', 'min:1'],
            'services.*.quoted_price' => $price,
            'services.*.used_product_id' => ['nullable', 'integer', 'min:1'],
            'products' => $productSale ? ['required', 'array', 'list', 'min:1'] : ['sometimes', 'array', 'list'],
            'products.*' => ['array:product_id,quantity,quoted_price'],
            'products.*.product_id' => ['required', 'integer', 'min:1'],
            'products.*.quantity' => ['required', 'integer', 'between:1,4294967295'],
            'products.*.quoted_price' => $price,
            'request_key' => ['required', 'uuid:4'],
            'request_signature' => ['required', 'regex:/\A[0-9a-f]{64}\z/'],
        ] + ($productSale ? ['service_id' => ['prohibited'], 'quoted_price' => ['prohibited']] : []);
    }

    public function messages(): array
    {
        return [
            'customer_name.max' => 'Nama pelanggan maksimal 120 karakter.',
            'customer_name.string' => 'Nama pelanggan harus berupa teks.',
            'services.required' => 'Tambahkan minimal satu layanan.',
            'services.prohibited' => 'Gunakan form Layanan untuk menambahkan layanan.',
            'services.*.service_id.*' => 'Pilih layanan yang tersedia.',
            'services.*.used_product_id.*' => 'Pilih produk pemakaian yang tersedia.',
            'services.*.quoted_price.*' => 'Pilih layanan untuk melihat harga sebelum menyimpan.',
            'products.required' => 'Tambahkan minimal satu produk yang dijual.',
            'products.*.product_id.*' => 'Pilih produk yang dijual.',
            'products.*.quantity.*' => 'Jumlah produk harus bilangan bulat antara 1 dan 4.294.967.295.',
            'products.*.quoted_price.*' => 'Pilih produk untuk melihat harga jual sebelum menyimpan.',
            'request_key.*' => 'Form ini tidak valid. Buka form pencatatan lagi.',
            'request_signature.*' => 'Form ini tidak valid. Buka form pencatatan lagi.',
        ];
    }

    public function isProductSale(): bool
    {
        return $this->routeIs('barber.products.store');
    }

    protected function getRedirectUrl(): string
    {
        return route($this->isProductSale() ? 'barber.product-form' : 'barber.entry-form');
    }
}
