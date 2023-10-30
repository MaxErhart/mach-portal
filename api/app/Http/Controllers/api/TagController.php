<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Tag;


class TagController extends Controller
{

    public function index(Request $request)
    {       
        $tags = Tag::all();
        return response()->json($tags);
    }


    public function store(Request $request)
    {

        $request->validate([
            "name"=>"required|max:255",
        ]);

        $newTag = new Tag([
            "name"=>$request->get("name"),
        ]);

        $newTag->creator()->associate(Auth::user())->save();

        return response()->json($newTag);
    }


    public function show($id)
    {
        $tag = Tag::findOrFail($id);

        return response()->json($tag);
    }

    public function update(Request $request, $id)
    {       
        $tag = Tag::findOrFail($id);

        $request->validate([
          "name"=>"required|max:255"
        ]);

        $tag->name = $request->get("name");

        $tag->save();

        return response()->json($tag);
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->forms()->sync([]);
        $tag->delete();

        return response()->json(Tag::all());
    }

}
