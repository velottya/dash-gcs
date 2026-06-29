<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoggedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('login_status')) {
            return redirect()->route('login')->with('message', 'Anda Tidak Mempunyai Akses! Silahkan Login!');
        }

        return $next($request);
    }
}
