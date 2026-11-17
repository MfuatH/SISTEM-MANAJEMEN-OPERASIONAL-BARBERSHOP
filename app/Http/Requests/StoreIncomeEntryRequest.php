<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeEntryRequest extends FormRequest
{
    protected $redirectRoute = 'barber.entry-form';

    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:120'],
            'service_id' => ['required', 'integer', 'min:1'],
            'quoted_price' => ['required', 'regex:/\A[1-9][0-9]{0,14}\z/'],
            'request_key' => ['required', 'uuid:4'],
            'request_signature' => ['required', 'regex:/\A[0-9a-f]{64}\z/'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.max' => 'Nama pelanggan maksimal 120 karakter.',
            'customer_name.string' => 'Nama pelanggan harus berupa teks.',
            'service_id.*' => 'Pilih satu layanan.',
            'quoted_price.*' => 'Pilih layanan untuk melihat harga sebelum menyimpan.',
            'request_key.*' => 'Form ini tidak valid. Buka form Tambah lagi.',
            'request_signature.*' => 'Form ini tidak valid. Buka form Tambah lagi.',
        ];
    }
}
