<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Models\Meta;
use Session;

class Maintenance
{
    public function handle($request, Closure $next)
    {
        $whitelist = [
            '2a00:1398:4:a000::1:2',
        ];
        if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
            return $next($request);
        }
        $meta = Meta::findOrfail(0);
        if($meta->maintenance_on) {
            abort(response()->json([
                "message"=>"Server Maintenance",
                "meta"=>$meta
            ],503));
        }
        return $next($request);
    }
}
