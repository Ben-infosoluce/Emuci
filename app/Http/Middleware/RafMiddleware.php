<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class RafMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Le RAF et le Trésorier partagent la même interface (consultation pour le Trésorier).
        if (!Gate::allows("Raf") && !Gate::allows("Tresorier")) {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
