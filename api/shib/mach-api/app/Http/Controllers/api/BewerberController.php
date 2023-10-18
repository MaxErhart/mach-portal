<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bewerber;

class BewerberController extends Controller
{
    public function index(Request $request)
    {
        $bewerbers = Bewerber::all();
        foreach($bewerbers as $bewerber) {
            $bewerber->bescheid;
        }
        return response()->json($bewerbers);
    }

    public function data_protection(Request $request)
    {
        $request->validate([
            "Email"=> "required|email",
            "Number"=> "required",
            "data_protection"=> "required|boolean",
        ]);
        $bewerber = Bewerber::where([
            "Number"=>$request->get("Number"),
        ])->get();
        if(count($bewerber)>1) {
            $error = [
                "errors"=> "Too many entries",
                "message"=> "Too many entries",
            ];            
            return response($error, 400);
        } else if(count($bewerber)<1){
            $error = [
                "errors"=> "No entry",
                "message"=> "Application not found. The list of applications is updated regularly. Please try again later or contact the support",
            ];            
            return response($error, 400);
        }
        if($request->get("Email")!==$bewerber[0]->getAttribute('Email')) {
            $error = [
                "message"=> "Input wrong.",
            ];            
            return response($error, 400);
        }
        if(count($bewerber)>1) {
            return response('Too many entries.', 400);
        } else if(count($bewerber)<1){
            return response('No entry.', 400);
        }

        $bewerber = $bewerber[0];
        $bewerber->data_protection = $request->get("data_protection");
        $bewerber->save();


        return response()->json($bewerber);
    }

    public function register(Request $request) {
        $request->validate([
            "email"=> "required",
            "number"=> "required",
            "register"=>"required|boolean",
        ]);

        $bewerber = Bewerber::where([
            "Number"=>$request->get("number"),
        ])->get();

        if(count($bewerber)>1) {
            return response('Too many entries.', 400);
        } else if(count($bewerber)<1){
            return response('No entry.', 400);
        }
        $bewerber = $bewerber[0];
        if($request->get("email")!==$bewerber->Email) {
            $error = [
                "errors"=> "Input wrong",
                "message"=> "Email or Applicant number is wrong",
            ];            
            return response($error, 400);
        }

        $deadline = $bewerber->entrance_exam->deadline->format('U');
        if(time()>=$deadline+86400) {
            return response('Passed deadline', 400);
        }

        $bewerber->entrance_exam_registration_changed = date('Y-m-d');
        $bewerber->entrance_exam_registered = $request->get("register");
        $bewerber->save();

        return response()->json($bewerber);

    }

    public function login(Request $request)
    {
        $request->validate([
            "Email"=> "required|email",
            "Number"=> "required",
        ]);
        $number = $request->get("Number");
        if(strlen($number)===7) {
            $number = substr($number,0,1).",".substr($number,1,3).",".substr($number,4,3);
        }
        if(strlen($number)===9) {
            $number = substr($number,0,1).",".substr($number,2,3).",".substr($number,6,3);
        }
        $bewerber = Bewerber::where([
            "Number"=>$number,
        ])->get();


        if(count($bewerber)>1) {
            $error = [
                "errors"=> "Too many entries",
                "message"=> "Too many entries",
            ];            
            return response($error, 400);
        } else if(count($bewerber)<1){
            $error = [
                "errors"=> ["Login"=>["No entry"]],
                "message"=> "Application not found. The list of applications is updated regularly. Please try again later or contact the support",
            ];            
            return response($error, 400);
        }



        $bewerber = $bewerber[0];

        if($request->get("Email")!==$bewerber->Email) {
            $error = [
                "errors"=> "Input wrong",
                "message"=> "Email or Applicant number is wrong",
            ];            
            return response($error, 400);
        }


        $bewerber->bescheid;
        $bewerber->entrance_exam;

        $bewerber->last_login = date('Y-m-d');
        $bewerber->save();

        return response()->json($bewerber);
    }    

}
