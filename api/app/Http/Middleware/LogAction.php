<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Action;
use App\Models\Submission;
use App\Models\Form;

use Session;

class LogAction
{
    

    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $response = $next($request);
        list($controller, $method) = explode('@',Route::currentRouteAction());
        $action = new Action([
            "request_url"=>$request->url(),
            "request_method"=>$request->method(),
            "request_controller"=>$controller,
            "request_controller_method"=>$method,
            "request_post_data"=>$request->post(),
            "request_get_data"=>$request->query(),
        ]);
        if(!$user) {
            $user = Auth::user();
            if(!$user) {
                $user = 1;
            }
        }
        $action->user()->associate($user);
        $action->save();
        return $response;
    }

    // public function terminate($request, $response)
    // {

    //     $action = new Action();
    //     $user = Auth::user();
    //     $action->user()->associate($user);
    //     $action->save();

    //     $data = $response->getData();

    //     list($controller, $method) = explode('@',Route::currentRouteAction());
    //     $controller = preg_replace('/.*\\\/', '', $controller);
    //     // if($controller==='SubmissionController') {
    //     //     $action->submission()->attach([$data->id]);
    //     // }

    //     if($controller==='ReplyController') {
    //         if(count($data->replies)>0) {
    //             $action->replies()->attach(collect($data->replies)->pluck('id'));
    //         }
    //         if(count($data->emails)>0) {
    //             $action->emails()->attach(collect($data->emails)->pluck('id'));
    //         }
    //     }
    //     $action->save();
    // }
}
