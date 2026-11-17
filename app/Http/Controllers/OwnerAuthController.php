<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OwnerAuthController extends Controller
{
    public function create(): View
    {
        return view('auth.owner-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $username = $request->input('username');
        if (is_string($username)) {
            $request->merge(['username' => User::normalizeUsername($username)]);
        }

        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:80', User::USERNAME_FORMAT],
            'password' => ['required', 'string', 'max:72'],
        ]);

        if (strlen($credentials['password']) > 72 || ! Auth::attempt($credentials + ['disabled_at' => null])) {
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi tidak valid.',
            ]);
        }

        $request->session()->forget('barber_name');
        $request->session()->regenerate();

        return redirect()->route('owner.home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
