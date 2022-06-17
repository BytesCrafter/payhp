<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $cookie_jwt = $request->cookie('erpat-pas-jwt');
        $request->headers->add(['Authorization' => "Bearer {$cookie_jwt}"]);
        return $next($request);
    }
}
