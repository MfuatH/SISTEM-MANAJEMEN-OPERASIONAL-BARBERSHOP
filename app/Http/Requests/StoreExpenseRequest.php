<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    protected $redirectRoute = 'owner.expenses.create';

    public function rules(): array
    {
        return [
            'incurred_on' => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:1000-01-01', 'before_or_equal:9999-12-31'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['bail', 'required', 'numeric', 'integer', 'min:1', 'max:999999999999999'],
            'request_key' => ['required', 'uuid:4'],
        ];
    }

    public function attributes(): array
    {
        return [
            'incurred_on' => 'tanggal pengeluaran',
            'description' => 'deskripsi',
            'amount' => 'jumlah pengeluaran',
        ];
    }

    public function messages(): array
    {
        return [
            'incurred_on.date_format' => 'Masukkan tanggal pengeluaran yang valid.',
            'description.required' => 'Masukkan deskripsi pengeluaran.',
            'amount.*' => 'Gunakan jumlah rupiah bulat antara 1 dan 999.999.999.999.999.',
            'request_key.*' => 'Form ini tidak valid. Buka form tambah pengeluaran lagi.',
        ];
    }
}
