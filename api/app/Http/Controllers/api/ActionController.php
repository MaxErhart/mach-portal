<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Action;

class ActionController extends Controller
{
    public function index(Request $request) {
        $query_parameters = $request->query();
        $actions = Action::orderBy('created_at', 'desc');
        if($query_parameters["offset"]>0) {
            $actions->offset($query_parameters["offset"]);
        }
        if($query_parameters["limit"]>0) {
            $actions->limit($query_parameters["limit"]);
        }
        $actions = $actions->get();
        foreach($actions as $action) {
            $action->user;
        }
        // ->offset()->limit(1000)->get();
        // foreach($actions as $action) {
        //     $action->user;
        //     $action->submission_url = $action->get_submission_url();
        //     $action->reply_list = $action->get_replies();
        //     $action->email_list = $action->get_emails();
        //     // $action->replies;
        // }
        return response()->json($actions);
    }


    public function sessions(Request $request) {
        $date_cutoff_login = now()->subHours(8);

        $actions = Action::orderBy('created_at', 'desc')->where('request_url','https://www-3.mach.kit.edu/api/public/index.php/api/auth/login')->whereDate('created_at','>',$date_cutoff_login->format('Y-m_d'))->orWhere(function ($query) use ($date_cutoff_login) {
            $query->whereDate('created_at','=',$date_cutoff_login->format('Y-m_d'))->whereTime('created_at', '>',$date_cutoff_login->toTimeString());
        })->get();

        $recent_user_ids = $actions->pluck('user_id')->unique();
        $recent_user_actions = [];
        $date_cutoff_refresh = now()->subHours(1);
        foreach($recent_user_ids as $key=>$user_id) {
            $last_user_action = Action::orderBy('created_at', 'desc')->where('user_id',$user_id)->first();
            if($last_user_action->getRawOriginal('created_at')>$date_cutoff_refresh && ($last_user_action->request_controller!=="App\Http\Controllers\API\CustomAuthController" || $last_user_action->request_controller_method!=="logout")) {
                $recent_user_actions[] = $last_user_action;
                continue;
            }
            $recent_user_ids->forget($key);
        }
        $recent_user_actions = collect($recent_user_actions);
        $recent_user_actions->pluck('user');
        return $recent_user_actions;
    }


}
