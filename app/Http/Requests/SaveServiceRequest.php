<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'current_price' => ['bail', 'required', 'numeric', 'integer', 'min:1', 'max:999999999999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama layanan',
            'current_price' => 'harga',
        ];
    }
}
