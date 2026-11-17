<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBarberSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('barber_name')) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
