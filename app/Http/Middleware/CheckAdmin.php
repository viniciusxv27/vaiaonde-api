<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Você precisa estar autenticado.');
        }

        if (!Auth::user()->is_admin) {
            return redirect()->route('login')->with('error', 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
