<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'sale_price' => ['bail', 'nullable', 'numeric', 'integer', 'min:1', 'max:999999999999999'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama produk', 'sale_price' => 'harga jual'];
    }
}
