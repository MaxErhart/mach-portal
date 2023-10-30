<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Bewerber;

class UploadController extends Controller
{
    public function getColumns(Request $request) {
        $tableName = $request->get('table_name');
        $cols = DB::getSchemaBuilder()->getColumnListing($tableName);
        $vetos = ['id','user_id','created_at','updated_at'];
        foreach($vetos as $veto) {
            if (($key = array_search($veto, $cols)) !== false) {
                unset($cols[$key]);
            }
        }
        return response()->json(array_values($cols));
    }

    public function commit(Request $request) {
        $table_name = $request->get('table_name');
        $table_identifier = $request->get('table_identifier');
        $use_unique_identifier = $request->get('use_unique_identifier');
        $db_columns = collect(json_decode($request->get('db_columns'), true))->pluck("name");
        $file_data = collect(json_decode($request->get('file_data'), true));
        $existing = collect(json_decode($request->get('existing'), true));
        $upload_new = filter_var($request->get("upload_new"), FILTER_VALIDATE_BOOLEAN);
        $update_existing = filter_var($request->get("update_existing"), FILTER_VALIDATE_BOOLEAN);
        $identifier_list = $existing->pluck($table_identifier);
        // return $db_columns;
        $bewerbers = [];
        foreach($file_data as $row) {
            if($use_unique_identifier) {

                if($identifier_list->contains($row[$table_identifier])) {
                    if(!$update_existing) {
                        continue;
                    }
                    $bewerber = Bewerber::where($table_identifier,$row[$table_identifier])->first();
                } else {
                    if(!$upload_new) {
                        continue;
                    }
                    $bewerber = new Bewerber();
                }
            } else {
                $bewerber = new Bewerber();
            }

            foreach($db_columns as $db_column) {
                $bewerber->setAttribute($db_column, $row[$db_column]);
            }
            $bewerber->save();
            $bewerbers[] = $bewerber;
        }
        return $bewerbers;

    }

    public function getRows(Request $request) {
        $tableName = $request->get('table_name');
        $tableIdentifier = $request->get('table_identifier');
        $fileData = collect(json_decode($request->get('file_data')));
        $identifierList = $fileData->pluck($tableIdentifier);
        $rows = DB::table($tableName)->whereIn($tableIdentifier, $identifierList)->get();
        return response()->json($rows);
    }

    public function getTable(Request $request) {
        $tableName = $request->get('table_name');
        $entries = DB::table($tableName)->get();
        return response()->json($entries);
    }
    public function getTables() {
        $tables = collect(DB::select("SHOW TABLES"))->pluck('Tables_in_new_db');
        return response()->json($tables);
    }

    public function table(Request $request) {
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
            $columns = ["row_idx"];
            while (($data = fgetcsv($handle, NULL, ";")) !== FALSE) {
                if($row==-1) {
                    foreach($data as $entry) {
                        $entry = mb_convert_encoding($entry, "UTF-8", 'ASCII,UTF-8,ISO-8859-15');
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
                    // return $columns;
                } else {
                    array_unshift($data, $row);
                    if(count($columns)!=count($data)) {
                        abort(response()->json([
                            "message"=>"Number of columns do not match in file {$files[$index]->getClientOriginalName()} at row {$row}",
                        ], 400));
                    }
                    // $data = array_map(function($string){return mb_convert_encoding($string,"UTF-8");},$data);
                    $data = mb_convert_encoding($data, "UTF-8", "ASCII,UTF-8,ISO-8859-15");
                    $json[] = array_combine($columns, $data);

                }

                $row++;
            }
            fclose($handle);             
        }


        return response()->json($json);
    }
}
