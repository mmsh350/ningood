<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcceptJson
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If 'Accept' header is not set or is not 'application/json', set it
        if (! $request->hasHeader('Accept') || $request->header('Accept') !== 'application/json') {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
