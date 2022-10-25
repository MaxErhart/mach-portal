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
            "first_name"=> "required",
            "last_name"=> "required",
            "bewerbungs_nummer"=> "required|integer",
            "data_protection"=> "required|boolean",
        ]);
        $bewerber = Bewerber::where([
            "Bewerbungs-nummer"=>$request->get("bewerbungs_nummer"),
        ])->get();

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
            "first_name"=> "required",
            "last_name"=> "required",
            "bewerbungs_nummer"=> "required|integer",
            "register"=>"required|boolean",
        ]);

        $bewerber = Bewerber::where([
            "Bewerbungs-nummer"=>$request->get("bewerbungs_nummer"),
        ])->get();

        if(count($bewerber)>1) {
            return response('Too many entries.', 400);
        } else if(count($bewerber)<1){
            return response('No entry.', 400);
        }

        $bewerber = $bewerber[0];

        $deadline = $bewerber->entrance_exam->deadline->format('U');
        if(time()>=$deadline+86400) {
            return response('Deadline.', 400);
        }

        $bewerber->entrance_exam_registration_changed = date('Y-m-d');
        $bewerber->entrance_exam_registered = $request->get("register");
        $bewerber->save();

        return response()->json($bewerber);

    }

    public function login(Request $request)
    {
        $request->validate([
            // "first_name"=> "required",
            "email"=> "required|email",
            "bewerbungs_nummer"=> "required|integer",
        ]);
        $bewerber = Bewerber::where([
            "Bewerbungs-nummer"=>$request->get("bewerbungs_nummer"),
        ])->get();


        if(count($bewerber)>1) {
            $error = [
                "errors"=> "Too many entries.",
                "message"=> "Too many entries.",
            ];            
            return response($error, 400);
        } else if(count($bewerber)<1){
            $error = [
                "errors"=> "No entry.",
                "message"=> "Application not found. The list of applications is updated every few days. Please try again later or contact the support.",
            ];            
            return response($error, 400);
        }



        $bewerber = $bewerber[0];

        if($request->get("email")!==$bewerber->getAttribute('KIT-E-Mail')) {
            $error = [
                "errors"=> "Wrong inputs.",
                "message"=> "Email wrong.",
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
