<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LehrveranstaltungController extends Controller
{
  
    public function upload(Request $request) {
        $files = $request->file('file');
        $paths = [];
        foreach($files as $file) {
            $path = $file->store('file');
            $path = str_replace("/", "\\", $path);
            $base_path = storage_path('app');
            array_push($paths, "{$base_path}\\{$path}");
        }

        $json = [];
        foreach($paths as $path) {
            $handle = fopen($path, 'r');
            $row = 0;
            $str = "";
            $columns = [];
            while (($data = fgetcsv($handle, NULL, ";")) !== FALSE) {
                if($row==0) {
                    foreach($data as $entry) {
                        $columns[] = $entry;
                    }
                } else {                 
                    $json[] = array_combine($columns, $data);
                }
                $row++;
            }
            fclose($handle);             
        }


       
        return response()->json($json);
    }
}
