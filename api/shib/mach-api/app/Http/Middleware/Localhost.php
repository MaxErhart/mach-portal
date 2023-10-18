<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Session;

class Localhost
{
    public function handle($request, Closure $next)
    {
        $whitelist = [
            '2a00:1398:4:a000::1:2',
        ];
        if(!Auth::check() && in_array($_SERVER['REMOTE_ADDR'], $whitelist) && str_starts_with($request->headers->get('origin'), 'http://localhost:')) {
            Auth::loginUsingId(4);
            // Auth::loginUsingId(6);
            // Auth::loginUsingId(9);
            // Auth::loginUsingId(12);
            // $user = Auth::loginUsingId(425);
            return $next($request);
        }
        return $next($request);
    }
}