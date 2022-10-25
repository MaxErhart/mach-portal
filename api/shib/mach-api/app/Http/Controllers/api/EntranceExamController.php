<?php

namespace App\Http\Controllers\API;
use App\Models\EntranceExam;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class EntranceExamController extends Controller
{

    public function index(Request $request) {

        $entranceExams = EntranceExam::all();
        foreach($entranceExams as $entranceExam) {
            $bewerbers = $entranceExam->bewerber;
            foreach($bewerbers as $bewerber) {
                $bewerber->bescheid;
            }
        }
        return response()->json($entranceExams);
    }

    public function store(Request $request) {

        $request->validate([
            "term"=>"required|max:255",
            "year"=>"required|max:255",
            "exam_date"=>"required|max:255",
            "exam_time"=>"required|max:255",
            "vorsitz"=>"required|max:255",
            "zeichen"=>"required|max:255",
            "current"=>"required|max:255",
        ]);      


        if(filter_var($request->get("current"), FILTER_VALIDATE_BOOLEAN)) {
            foreach(EntranceExam::all() as $entranceExam) {
                $entranceExam->current = false;
                $entranceExam->save();
            }
        }
        $entranceExam = new EntranceExam([
            "term"=>$request->get("term"),
            "year"=>$request->get("year"),
            "exam_date"=>$request->get("exam_date"),
            "exam_time"=>$request->get("exam_time"),
            "vorsitz"=>$request->get("vorsitz"),
            "zeichen"=>$request->get("zeichen"),
            filter_var($request->get("current"), FILTER_VALIDATE_BOOLEAN),
        ]);
        $entranceExam->save();

        return response()->json($entranceExam);

    }

    public function update(Request $request, $id) {

        $request->validate([
            "term"=>"required|max:255",
            "year"=>"required|max:255",
            "exam_date"=>"required|max:255",
            "exam_time"=>"required|max:255",
            "vorsitz"=>"required|max:255",
            "zeichen"=>"required|max:255",
            "current"=>"required|max:255",
        ]);
        
        if(filter_var($request->get("current"), FILTER_VALIDATE_BOOLEAN)) {
            foreach(EntranceExam::all() as $entranceExam) {
                $entranceExam->current = false;
                $entranceExam->save();
            }
        }

        $entranceExam = App::findOrFail($id);

        $entranceExam->term = $request->get("term");
        $entranceExam->year = $request->get("year");
        $entranceExam->exam_date = $request->get("exam_date");
        $entranceExam->exam_time = $request->get("exam_time");
        $entranceExam->vorsitz = $request->get("vorsitz");
        $entranceExam->zeichen = $request->get("zeichen");
        $entranceExam->current = filter_var($request->get("current"), FILTER_VALIDATE_BOOLEAN);
        $entranceExam->save();

        return response()->json($entranceExam);
    }

}
