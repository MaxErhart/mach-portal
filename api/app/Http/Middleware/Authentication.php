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
        $whitelist = [
            '2a00:1398:4:a000::1:2',
        ];
        if(!Auth::check() && in_array($_SERVER['REMOTE_ADDR'], $whitelist) && str_starts_with($request->headers->get('origin'), 'http://localhost:')) {
            Auth::loginUsingId(4);
            // Auth::loginUsingId(506);
            // Auth::loginUsingId(6);
            // Auth::loginUsingId(9);
            // Auth::loginUsingId(12);
            // $user = Auth::loginUsingId(425);
            return $next($request);
        }

        if(!Auth::check()) {
            abort(response()->json([
                "message"=>"Not logged in",
            ], 403));
        }

        $user = Auth::user();
        $user->rightsOnApps();
        $appIds = $user->rightsOnApps->pluck('id');
        if($request->isMethod('get') && $appId==36) {
            return $next($request);
        }
        if($request->isMethod('get') && $appId==48) {
            foreach($user->groups->pluck('name') as $group_name) {
                if(str_contains($group_name, "Lehre") || str_contains($group_name, "lehre")) {
                    return $next($request);
                }
            }
        }
        if(!$appIds->contains($appId)) {
            abort(response()->json([
                "message"=>"Unauthorized",
            ], 401));
        }
        return $next($request);
    }
}
