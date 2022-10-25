<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Applicant;


class ApplicantController extends Controller
{


    public function index(Request $request)
    {
        $applicants = Applicant::all();
        return response()->json($applicants);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            "sex"=>"required|max:255",
            "firstname"=>"required|max:255",
            "lastname"=>"required|max:255",
            "email"=>"required|max:255",
            "date_of_birth"=>"required|max:255",
            "street"=>"required|max:255",
            // "street_number"=>"required|max:255",
            "zipcode"=>"required|max:255",
            "city"=>"required|max:255",
            "country"=>"required|max:255",
            'application_number'=>"required|max:255",
            'applicant_number'=>"required|max:255",            
            "degree"=>"required|max:255"
        ]);

        $newApplicant = new Applicant([
            "sex"=>$request->get("sex"),
            "firstname"=>$request->get("firstname"),
            "lastname"=>$request->get("lastname"),
            "email"=>$request->get("email"),
            "date_of_birth"=>$request->get("date_of_birth"),
            "street"=>$request->get("street"),
            // "street_number"=>$request->get("street_number"),
            "zipcode"=>$request->get("zipcode"),
            "city"=>$request->get("city"),
            "country"=>$request->get("country"),
            "application_number"=>$request->get("application_number"),
            "applicant_number"=>$request->get("applicant_number"),
            "degree"=>$request->get("degree"),
        ]);

        $newApplicant->save();

        return response()->json($newApplicant);
    }
}
