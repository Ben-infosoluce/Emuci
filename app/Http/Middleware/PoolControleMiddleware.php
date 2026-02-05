<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PoolControleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     if (Gate::allows("PoolControle")) {
    //         return $next($request);
    //     }
    //     return $next($request);
    // }
    public function handle(Request $request, Closure $next): Response
    {
        if (!Gate::allows("PoolControle")) {
            return redirect()->route('home');
            // return Inertia::location("/");
            // abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
