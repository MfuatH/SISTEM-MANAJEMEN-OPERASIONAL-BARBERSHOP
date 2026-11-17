<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function entryForm(): View
    {
        $services = Service::query()->active()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'current_price']);

        return view('pos.entry-form', ['services' => $services]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
