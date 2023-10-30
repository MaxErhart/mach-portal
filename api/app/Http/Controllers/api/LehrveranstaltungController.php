<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LehrveranstaltungController extends Controller
{
  
    public function upload(Request $request) {

        // abort(response()->json(["message"=>"Test"], 500));
        $files = $request->file('file');
        $paths = [];
        if(!$files) {
            return response()->json([]);
        }
        foreach($files as $file) {
            $path = $file->store('file');
            $path = str_replace("/", "\\", $path);
            $base_path = storage_path('app');
            array_push($paths, "{$base_path}\\{$path}");
        }

        $json = [];
        $columnsPrevFile = NULL;
        foreach($paths as $index=>$path) {
            $handle = fopen($path, 'r');
            $row = -1;
            $str = "";
            $columns = ['fileId', 'fileEntryId'];
            while (($data = fgetcsv($handle, NULL, ";")) !== FALSE) {
                if($row==-1) {
                    foreach($data as $entry) {
                        $columns[] = $entry;
                    }
                    if($columnsPrevFile==NULL) {
                        $columnsPrevFile = $columns;
                    } else {
                        if($columnsPrevFile!==$columns) {
                            abort(response()->json([
                                "message"=>"Columns of files do not match",
                            ], 400));
                        }
                    }

                } else {
                    array_unshift($data, $row);
                    array_unshift($data, $request->get('fileId')[$index]);

                    if(count($columns)!=count($data)) {
                        abort(response()->json([
                            "message"=>"Number of columns do not match in file {$files[$index]->getClientOriginalName()} at row {$row}",
                        ], 400));
                    }
                    $json[] = array_combine($columns, $data);

                }

                $row++;
            }
            fclose($handle);             
        }


       
        return response()->json($json);
    }
}
