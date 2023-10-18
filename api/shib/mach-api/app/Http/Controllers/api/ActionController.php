<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Action;

class ActionController extends Controller
{
    public function index(Request $request) {
        $actions = Action::orderBy('created_at', 'desc')->get();
        foreach($actions as $action) {
            $action->user;
            $action->submission_url = $action->get_submission_url();
            $action->reply_list = $action->get_replies();
            $action->email_list = $action->get_emails();
            // $action->replies;
        }
        return response()->json($actions);
    }
}
