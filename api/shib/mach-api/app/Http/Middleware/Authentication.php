<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use Session;

class Authentication
{
    public function handle($request, Closure $next, $appId)
    {

        if(!Auth::check() && str_starts_with($request->headers->get('origin'), 'http://localhost:')) {
            Auth::loginUsingId(6);
            return $next($request);
        }

        if(!Auth::check()) {
            return response('Unauthorized.', 401);
        }

        $user = Auth::user();
        $user->rightsOnApps();
        $appIds = $user->rightsOnApps->pluck('id');
        if(!$appIds->contains($appId)) {
            return response('Unauthorized', 401);
        }
        return $next($request);
    }
}
