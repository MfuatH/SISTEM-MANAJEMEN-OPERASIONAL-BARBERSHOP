<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BarberSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            ['name' => ['required', 'string', 'max:120']],
            [
                'name.required' => 'Masukkan nama Kapster.',
                'name.string' => 'Nama Kapster harus berupa teks.',
                'name.max' => 'Nama Kapster maksimal 120 karakter.',
            ],
        );

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('barber_name', $validated['name']);

        return redirect()->route('barber.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
