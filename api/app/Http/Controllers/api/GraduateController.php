<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Graduate;
use App\Models\GraduateThesis;
use App\Models\User;
use Illuminate\Http\Request;

class GraduateController extends Controller
{
    public function upload(Request $request) {
        // graduates
    }

    public function getListByMatr(Request $request) {
        $numbers = json_decode($request->get('numbers'),true);

        $graduates = Graduate::whereIn('MatrNr',$numbers)->get();
        foreach($graduates as $graduate) {
            $graduate->theses;
            $graduate->user;
        }
        $mapped = $graduates->mapWithKeys(function ($graduate) {
            return [$graduate['MatrNr'] => $graduate];
        });
        return $mapped;
    }

    private function isWord($word) {
        if($word=="" || $word=="|" || $word=="-" || mb_strlen($word,"utf-8")<=1) {
            return false;
        }
        // Check utf-8 bit value with mb_ord("char","UTF-8")
        return true;
    }

    private function undoWordMutations($word) {
        $word = strtolower($word);
        $word = str_replace("(","",$word);
        $word = str_replace(")","",$word);
        $word = str_replace("*","",$word);
        $word = str_replace("[","",$word);
        $word = str_replace("]","",$word);
        $word = str_replace("|","",$word);
        $word = str_replace("{","",$word);
        $word = str_replace("}","",$word);
        $word = str_replace("—","",$word);
        $word = str_replace('"',"",$word);
        $word = str_replace("'","",$word);
        $word = str_replace("©","",$word);
        $word = str_replace("„","",$word);
        $word = str_replace("´","",$word);
        $word = str_replace("`","",$word);
        $word = str_replace("“","",$word);
        $word = str_replace("ı","",$word);
        $word = rtrim($word, ":,.-;<>?@’^\\/_#$%+&!=");
        $word = ltrim($word, ":,.-;<>?@’^\\/_#$%+&!=");
        return $word;
    }


    private function getWords($string) {
        $words = explode(" ", $string);
        $word_results = [];
        foreach($words as $word) {
            $this->undoWordMutations($word);
            if(!$this->isWord($word)) {
                continue;
            }
            $word_results[] = $word;
        }
        return $word_results;
    }

    public function search(Request $request) {
        $search_string = $request->get("search_string");
        $words = $this->getWords($search_string);
        // $graduates = collect([]);
        $graduates = [];
        $graduate_scores = [];
        $graduates_columns = ['MatrNr','Vorname','Nachname','E-Mail'];
        foreach($words as $word) {
            foreach($graduates_columns as $column) {
                $current_graduates = Graduate::where($column, 'LIKE', "%{$word}%")->limit(50)->get();
                foreach($current_graduates as $current_graduate) {
                    if(array_key_exists($current_graduate->id,$graduate_scores)) {
                        $graduate_scores[$current_graduate->id] += 1;
                    } else {
                        $graduate_scores[$current_graduate->id] = 1;
                        $graduates[$current_graduate->id] = $current_graduate;
                    }
                }
            }
        }
    
        $veto_users = collect(array_values($graduates))->pluck('user')->filter(function($value) {
            return $value!=null;
        });
        $users_columns = ['matriculationNumber','firstname','lastname','email'];
        $users = [];
        $user_scores = [];
        foreach($words as $word) {
            foreach($users_columns as $column) {
                $current_users = User::where($column, 'LIKE', "%{$word}%")->whereNotIn('id',$veto_users->pluck('id'))->limit(50)->get();
                foreach($current_users as $current_user) {
                    if(array_key_exists($current_user->id,$user_scores)) {
                        $user_scores[$current_user->id] += 1;
                    } else {
                        $user_scores[$current_user->id] = 1;
                        $users[$current_user->id] = $current_user;
                    }
                }
            }
        }
        $result = [];
        foreach($users as $id=>$user) {
            $offset = 0;
            while(array_key_exists($user_scores[$id]+$offset,$result)) {
                $offset--;
            }
            $result[$user_scores[$id]+$offset] = $user;
        }
        foreach($graduates as $id=>$graduate) {
            $offset = 0;
            while(array_key_exists($graduate_scores[$id]+$offset,$result)) {
                $offset--;
            }
            $result[$graduate_scores[$id]+$offset] = $graduate;
        }

        krsort($result);
        return array_values($result);

    }

    public function commit(Request $request) {
        $graduates = json_decode($request->get('graduates'),true);
        $new_graduates_index = json_decode($request->get('new_graduates_index'),true);
        foreach($graduates as $index=>$graduate) {
            if($new_graduates_index[$index]) {
                $new_graduate = new Graduate();
                Graduate::parseGraduate($graduate, $new_graduate);
                $new_graduate->save();
                $new_graduate->syncTheses($graduate["theses"]);
            } else {
                $existin_graduate = Graduate::where('MatrNr', $graduate['MatrNr'])->first();
                Graduate::parseGraduate($graduate, $existin_graduate);
                $existin_graduate->save();
                $existin_graduate->syncTheses($graduate["theses"]);
            }
        }
        
        return response()->json('Success');
    }

}
