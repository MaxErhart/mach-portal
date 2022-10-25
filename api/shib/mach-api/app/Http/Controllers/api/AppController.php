<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\App;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupAppSettings;

class AppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexLocal($rights)
    {
        $apps = App::whereIn('id', $rights)->get();
        // if($rights['unrestricted']) {
        //     $apps = App::all();
        // } else {
        //     // Get all apps from creators who the user has rights on.
        //     $apps = App::whereIn('creator_id', $rights['users'])->get();
        // }
        foreach($apps as $app) {
            $app->group_app_settings;
        }
        return $apps;
    }    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $apps = App::all();
        return response()->json($apps);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $request->validate([
            "name"=>"required|max:255",
        ]);
        
        $newApp = new App([
            "name"=>$request->get("name"),
        ]);

        $newApp->creator()->associate(Auth::user())->save();

        return response()->json($newApp);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $app = App::findOrFail($id);

        return response()->json($app);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {       
        $app = App::findOrFail($id);

        $request->validate([
          "name"=>"required|max:255"
        ]);

        $app->name = $request->get("name");

        $app->save();

        return response()->json($app);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $app = App::findOrFail($id);
        $app->delete();

        return response()->json(App::all());
    }

}
