<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class Mt1Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     if (Gate::allows("MT2")) {
    //         return $next($request);
    //     }
    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Gate::allows("MT1")) {
            return redirect()->route('home');
            // return Inertia::location("/");
            // abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
