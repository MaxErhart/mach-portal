<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Stundenzettel;
use App\Models\StundenzettelArbeitstag;

use \Datetime;


class StundenzettelController extends Controller
{
    public function index(Request $request)
    {       
        $stundenzettels = Stundenzettel::all();
        foreach($stundenzettels as $stundenzettel) {
            $stundenzettel->arbeitstage;
        }
        return response()->json($stundenzettels);
    }

    public function store(Request $request)
    {

        $request->validate([
            "personal_nummer"=>"required|max:255",
            "stundensatz"=>"required|max:255",
            "vereinbarte_arbeitszeit"=>"required|max:255",
            "institut"=>"required",
            "arbeitstage"=>"required",
        ]);
        $arbeitstage = json_decode($request->get("arbeitstage"), true);

        $stundenzettel = new Stundenzettel([
            "personal_nummer"=>$request->get('personal_nummer'),
            "stundensatz"=>$request->get('stundensatz'),
            "vereinbarte_arbeitszeit"=>$request->get('vereinbarte_arbeitszeit'),
            "institut"=>$request->get('institut'),
        ]);
        $stundenzettel->user()->associate(Auth::user())->save();

        foreach($arbeitstage as $arbeitstag) {
            $newArbeitstag = new StundenzettelArbeitstag([
                "start"=>new DateTime($arbeitstag["start"]),
                "end"=>new DateTime($arbeitstag["end"]),
                "task"=>$arbeitstag["task"],
                "vacation_millsec"=>$arbeitstag["vacation_millsec"],
            ]);
            $stundenzettel->arbeitstage()->save($newArbeitstag);
        }

        $stundenzettel->user;
        $stundenzettel->arbeitstage;
        return response()->json($stundenzettel);
    }
    

    public function destroy($id)
    {
        $stundenzettel = Stundenzettel::findOrFail($id);
        foreach($stundenzettel->arbeitstage as $arbeitstag) {
            $arbeitstag->delete();
        }
        $stundenzettel->delete();

        return response()->json(Stundenzettel::all());
    }

}
