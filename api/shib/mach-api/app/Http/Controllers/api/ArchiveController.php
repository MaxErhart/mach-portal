<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArchiveSe;
use App\Models\ArchiveSeSuggestion;
use App\Models\Archive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class ArchiveFileHandler {
    protected $file;
    protected $from_source;
    public $name;
    public $name_wo_extension;
    public $extension;
    public $path;
    public $path_full;
    public $dir_path;
    public $dir_path_full;
    public $url;

    private function __constructIlluminateUploadedFile($file) {
        $this->name = $file->getClientOriginalName();
        $this->name_wo_extension = implode(".",array_slice(explode(".",$this->name),0,-1));
        $this->extension = $file->getClientOriginalExtension();
    }

    private function __constructServerPath($file,$strict) {
        $file = rtrim(str_replace("\\", "/",$file),"/");

        $file = $this->prefixRoot($file);
        if(!is_file($file) && $strict) {
            abort(response()->json(["message"=>"File does not exist"],404));
        }

        $dot_fragments = explode(".",$file);
        $this->extension = $dot_fragments[count($dot_fragments)-1];
        $file_wo_extension = implode(".",array_slice($dot_fragments, 0, -1));
        $file_wo_extension = str_replace("\\", "/",$file_wo_extension);
        $slash_fragments = explode("/",$file_wo_extension);
        $this->name_wo_extension = $slash_fragments[count($slash_fragments)-1];
        $this->name = $this->name_wo_extension.".".$this->extension;
        $file_dir_path = implode("/",array_slice($slash_fragments,0,-1));

        $root_path = Storage::disk('archive')->path('');
        $root_path = rtrim(str_replace("\\", "/",$root_path),"/");
        $this->dir_path_full = $file_dir_path;
        $this->dir_path = $this->trimRootPrefix($file_dir_path);
        $this->path = $this->dir_path."/".$this->name;
        $this->path_full = $this->dir_path_full."/".$this->name;
        $this->url = URL::signedRoute(
            'file_hosting', ['fragment'=>$this->path,'disk'=>'archive']
        );
        $this->path = ltrim($this->path, "/");
        $this->dir_path = ltrim($this->dir_path, "/");
    }

    private function __saveAsFromString($target,$delete_source) {
        // abort(response()->json([$this->path_full,$target],403));
        copy($this->path_full,$target);
        if($delete_source) {
            unlink($this->path_full);
        }
        $this->__construct($target);
    }
    private function __saveAsFromIlluminateUploadedFile($target_dir,$target_filename) {
        $target_dir = $this->trimRootPrefix($target_dir);
        $target=$this->file->storeAs($target_dir, $target_filename, "archive");
        $this->__construct("/".$target);
    }

    public function saveAs($target_dir,$target_filename,$delete_source=false) {
        $target_dir = str_replace("\\", "/",$target_dir);
        $target_dir = rtrim($this->prefixRoot($target_dir),"/");
        $target_filename = ltrim($target_filename,"/");
        if($this->from_source==="string") {
            // abort(response()->json([$target_dir,$target_filename],403));
            $target = $target_dir."/".$target_filename;
            $this->__saveAsFromString($target,$delete_source);
        } else if($this->from_source==="Illuminate\\Http\\UploadedFile") {
            $this->__saveAsFromIlluminateUploadedFile($target_dir,$target_filename);
        }


    }
    private function trimRootPrefix($path) {
        $root_path = Storage::disk('archive')->path('');
        $root_path = rtrim(str_replace("\\", "/",$root_path),"/");
        $path = str_replace("\\","/",$path);

        if (substr($path, 0, strlen($root_path)) == $root_path) {
            $path = substr($path, strlen($root_path));
        }

        return $path;
    }
    private function prefixRoot($path) {
        $root_path = Storage::disk('archive')->path('');
        $root_path = rtrim(str_replace("\\", "/",$root_path),"/");
        if(!str_starts_with($path,$root_path)) {
            $path = $root_path."/".ltrim($path,"/");
        }
        return $path;
    }

    function __construct($file, $strict=true) {
        $this->file = $file;
        if(gettype($file)==="string") {
            $this->from_source = "string";
            $this->__constructServerPath($file,$strict);
        } else if(get_class($file)==="Illuminate\\Http\\UploadedFile") {
            $this->from_source = "Illuminate\\Http\\UploadedFile";
            $this->__constructIlluminateUploadedFile($file);
        }
    }
}

class ArchiveController extends Controller
{
    public function index(Request $request) {
        $archive = Archive::all();

        return response()->json($archive);
    }

    private function isWord($word) {
        if($word=="" || $word=="|" || $word=="-" || mb_strlen($word,"utf-8")<=1) {
            return false;
        }
        // Check utf-8 bit value with mb_ord("char","UTF-8")
        return true;
    }

    private function undoWordMutations($word, $unicode=true) {
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
        $word = strtolower($word);

        if($unicode) {
            $word = str_replace("\x00","",$word);
            $word = str_replace("\x01","",$word);
            $word = str_replace("\x02","",$word);
            $word = str_replace("\x03","",$word);
            $word = str_replace("\x04","",$word);
            $word = str_replace("\x05","",$word);
            $word = str_replace("\x06","",$word);
            $word = str_replace("\x07","",$word);
            $word = str_replace("\x08","",$word);
            $word = str_replace("\x09","",$word);
            $word = str_replace("\x0A","",$word);
            $word = str_replace("\x0B","",$word);
            $word = str_replace("\x0C","",$word);
            $word = str_replace("\x0D","",$word);
            $word = str_replace("\x0E","",$word);
            $word = str_replace("\x0F","",$word);

            $word = str_replace("\x10","",$word);
            $word = str_replace("\x11","",$word);
            $word = str_replace("\x12","",$word);
            $word = str_replace("\x13","",$word);
            $word = str_replace("\x14","",$word);
            $word = str_replace("\x15","",$word);
            $word = str_replace("\x16","",$word);
            $word = str_replace("\x17","",$word);
            $word = str_replace("\x18","",$word);
            $word = str_replace("\x19","",$word);
            $word = str_replace("\x1A","",$word);
            $word = str_replace("\x1B","",$word);
            $word = str_replace("\x1C","",$word);
            $word = str_replace("\x1D","",$word);
            $word = str_replace("\x1E","",$word);
            $word = str_replace("\x1F","",$word);

            $word = str_replace("\x20","",$word);
            $word = str_replace("\x21","",$word);
            $word = str_replace("\x22","",$word);
            $word = str_replace("\x23","",$word);
            $word = str_replace("\x24","",$word);
            $word = str_replace("\x25","",$word);
            $word = str_replace("\x26","",$word);
            $word = str_replace("\x27","",$word);
            $word = str_replace("\x28","",$word);
            $word = str_replace("\x29","",$word);
            $word = str_replace("\x2A","",$word);
            $word = str_replace("\x2B","",$word);
            $word = str_replace("\x2C","",$word);
            $word = str_replace("\x2D","",$word);
            $word = str_replace("\x2E","",$word);
            $word = str_replace("\x2F","",$word);
            
            $word = str_replace("\x30","",$word);
            $word = str_replace("\x31","",$word);
            $word = str_replace("\x32","",$word);
            $word = str_replace("\x33","",$word);
            $word = str_replace("\x34","",$word);
            $word = str_replace("\x35","",$word);
            $word = str_replace("\x36","",$word);
            $word = str_replace("\x37","",$word);
            $word = str_replace("\x38","",$word);
            $word = str_replace("\x39","",$word);
            $word = str_replace("\x3A","",$word);
            $word = str_replace("\x3B","",$word);
            $word = str_replace("\x3C","",$word);
            $word = str_replace("\x3D","",$word);
            $word = str_replace("\x3E","",$word);
            $word = str_replace("\x3F","",$word);

            $word = str_replace("\x40","",$word);
            $word = str_replace("\x41","",$word);
            $word = str_replace("\x42","",$word);
            $word = str_replace("\x43","",$word);
            $word = str_replace("\x44","",$word);
            $word = str_replace("\x45","",$word);
            $word = str_replace("\x46","",$word);
            $word = str_replace("\x47","",$word);
            $word = str_replace("\x48","",$word);
            $word = str_replace("\x49","",$word);
            $word = str_replace("\x4A","",$word);
            $word = str_replace("\x4B","",$word);
            $word = str_replace("\x4C","",$word);
            $word = str_replace("\x4D","",$word);
            $word = str_replace("\x4E","",$word);
            $word = str_replace("\x4F","",$word);

            $word = str_replace("\x50","",$word);
            $word = str_replace("\x51","",$word);
            $word = str_replace("\x52","",$word);
            $word = str_replace("\x53","",$word);
            $word = str_replace("\x54","",$word);
            $word = str_replace("\x55","",$word);
            $word = str_replace("\x56","",$word);
            $word = str_replace("\x57","",$word);
            $word = str_replace("\x58","",$word);
            $word = str_replace("\x59","",$word);
            $word = str_replace("\x5A","",$word);
            $word = str_replace("\x5B","",$word);
            $word = str_replace("\x5C","",$word);
            $word = str_replace("\x5D","",$word);
            $word = str_replace("\x5E","",$word);
            $word = str_replace("\x5F","",$word);

            $word = str_replace("\x60","",$word);
            $word = str_replace("\x61","",$word);
            $word = str_replace("\x62","",$word);
            $word = str_replace("\x63","",$word);
            $word = str_replace("\x64","",$word);
            $word = str_replace("\x65","",$word);
            $word = str_replace("\x66","",$word);
            $word = str_replace("\x67","",$word);
            $word = str_replace("\x68","",$word);
            $word = str_replace("\x69","",$word);
            $word = str_replace("\x6A","",$word);
            $word = str_replace("\x6B","",$word);
            $word = str_replace("\x6C","",$word);
            $word = str_replace("\x6D","",$word);
            $word = str_replace("\x6E","",$word);
            $word = str_replace("\x6F","",$word);

            $word = str_replace("\x70","",$word);
            $word = str_replace("\x71","",$word);
            $word = str_replace("\x72","",$word);
            $word = str_replace("\x73","",$word);
            $word = str_replace("\x74","",$word);
            $word = str_replace("\x75","",$word);
            $word = str_replace("\x76","",$word);
            $word = str_replace("\x77","",$word);
            $word = str_replace("\x78","",$word);
            $word = str_replace("\x79","",$word);
            $word = str_replace("\x7A","",$word);
            $word = str_replace("\x7B","",$word);
            $word = str_replace("\x7C","",$word);
            $word = str_replace("\x7D","",$word);
            $word = str_replace("\x7E","",$word);
            $word = str_replace("\x7F","",$word);

            $word = str_replace("\x80","",$word);
            $word = str_replace("\x81","",$word);
            $word = str_replace("\x82","",$word);
            $word = str_replace("\x83","",$word);
            $word = str_replace("\x84","",$word);
            $word = str_replace("\x85","",$word);
            $word = str_replace("\x86","",$word);
            $word = str_replace("\x87","",$word);
            $word = str_replace("\x88","",$word);
            $word = str_replace("\x89","",$word);
            $word = str_replace("\x8A","",$word);
            $word = str_replace("\x8B","",$word);
            $word = str_replace("\x8C","",$word);
            $word = str_replace("\x8D","",$word);
            $word = str_replace("\x8E","",$word);
            $word = str_replace("\x8F","",$word);

            $word = str_replace("\x90","",$word);
            $word = str_replace("\x91","",$word);
            $word = str_replace("\x92","",$word);
            $word = str_replace("\x93","",$word);
            $word = str_replace("\x94","",$word);
            $word = str_replace("\x95","",$word);
            $word = str_replace("\x96","",$word);
            $word = str_replace("\x97","",$word);
            $word = str_replace("\x98","",$word);
            $word = str_replace("\x99","",$word);
            $word = str_replace("\x9A","",$word);
            $word = str_replace("\x9B","",$word);
            $word = str_replace("\x9C","",$word);
            $word = str_replace("\x9D","",$word);
            $word = str_replace("\x9E","",$word);
            $word = str_replace("\x9F","",$word);

            $word = str_replace("\xA0","",$word);
            $word = str_replace("\xA1","",$word);
            $word = str_replace("\xA2","",$word);
            $word = str_replace("\xA3","",$word);
            $word = str_replace("\xA4","",$word);
            $word = str_replace("\xA5","",$word);
            $word = str_replace("\xA6","",$word);
            $word = str_replace("\xA7","",$word);
            $word = str_replace("\xA8","",$word);
            $word = str_replace("\xA9","",$word);
            $word = str_replace("\xAA","",$word);
            $word = str_replace("\xAB","",$word);
            $word = str_replace("\xAC","",$word);
            $word = str_replace("\xAD","",$word);
            $word = str_replace("\xAE","",$word);
            $word = str_replace("\xAF","",$word);

            $word = str_replace("\xB0","",$word);
            $word = str_replace("\xB1","",$word);
            $word = str_replace("\xB2","",$word);
            $word = str_replace("\xB3","",$word);
            $word = str_replace("\xB4","",$word);
            $word = str_replace("\xB5","",$word);
            $word = str_replace("\xB6","",$word);
            $word = str_replace("\xB7","",$word);
            $word = str_replace("\xB8","",$word);
            $word = str_replace("\xB9","",$word);
            $word = str_replace("\xBA","",$word);
            $word = str_replace("\xBB","",$word);
            $word = str_replace("\xBC","",$word);
            $word = str_replace("\xBD","",$word);
            $word = str_replace("\xBE","",$word);
            $word = str_replace("\xBF","",$word);

            $word = str_replace("\xC0","",$word);
            $word = str_replace("\xC1","",$word);
            $word = str_replace("\xC2","",$word);
            $word = str_replace("\xC3","",$word);
            $word = str_replace("\xC4","",$word);
            $word = str_replace("\xC5","",$word);
            $word = str_replace("\xC6","",$word);
            $word = str_replace("\xC7","",$word);
            $word = str_replace("\xC8","",$word);
            $word = str_replace("\xC9","",$word);
            $word = str_replace("\xCA","",$word);
            $word = str_replace("\xCB","",$word);
            $word = str_replace("\xCC","",$word);
            $word = str_replace("\xCD","",$word);
            $word = str_replace("\xCE","",$word);
            $word = str_replace("\xCF","",$word);

            $word = str_replace("\xD0","",$word);
            $word = str_replace("\xD1","",$word);
            $word = str_replace("\xD2","",$word);
            $word = str_replace("\xD3","",$word);
            $word = str_replace("\xD4","",$word);
            $word = str_replace("\xD5","",$word);
            $word = str_replace("\xD6","",$word);
            $word = str_replace("\xD7","",$word);
            $word = str_replace("\xD8","",$word);
            $word = str_replace("\xD9","",$word);
            $word = str_replace("\xDA","",$word);
            $word = str_replace("\xDB","",$word);
            $word = str_replace("\xDC","",$word);
            $word = str_replace("\xDD","",$word);
            $word = str_replace("\xDE","",$word);
            $word = str_replace("\xDF","",$word);

            $word = str_replace("\xE0","",$word);
            $word = str_replace("\xE1","",$word);
            $word = str_replace("\xE2","",$word);
            $word = str_replace("\xE3","",$word);
            $word = str_replace("\xE4","",$word);
            $word = str_replace("\xE5","",$word);
            $word = str_replace("\xE6","",$word);
            $word = str_replace("\xE7","",$word);
            $word = str_replace("\xE8","",$word);
            $word = str_replace("\xE9","",$word);
            $word = str_replace("\xEA","",$word);
            $word = str_replace("\xEB","",$word);
            $word = str_replace("\xEC","",$word);
            $word = str_replace("\xED","",$word);
            $word = str_replace("\xEE","",$word);
            $word = str_replace("\xEF","",$word);

            $word = str_replace("\xF0","",$word);
            $word = str_replace("\xF1","",$word);
            $word = str_replace("\xF2","",$word);
            $word = str_replace("\xF3","",$word);
            $word = str_replace("\xF4","",$word);
            $word = str_replace("\xF5","",$word);
            $word = str_replace("\xF6","",$word);
            $word = str_replace("\xF7","",$word);
            $word = str_replace("\xF8","",$word);
            $word = str_replace("\xF9","",$word);
            $word = str_replace("\xFA","",$word);
            $word = str_replace("\xFB","",$word);
            $word = str_replace("\xFC","",$word);
            $word = str_replace("\xFD","",$word);
            $word = str_replace("\xFE","",$word);
            $word = str_replace("\xFF","",$word);
        }
        // $word = str_replace("\x98","",$word);
        // $word = str_replace("\xE2","",$word);
        // $word = str_replace("\x9A","",$word);
        // $word = str_replace("\x9A","",$word);
        // $word = str_replace("\x84","",$word);
        // $word = str_replace("\x82","",$word);
        // $word = str_replace("‘","",$word);
        
        $word = rtrim($word, ":,.-;<>?@’^\\/_#$%+&!=");
        $word = ltrim($word, ":,.-;<>?@’^\\/_#$%+&!=");
        return $word;
    }

    // consider word mutations for search

    private $errors = [];

    public function crawler(Request $request) {


        $root_path = Storage::disk('archive')->path('');
        $directory = new \RecursiveDirectoryIterator($root_path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = [];
        $scanned_archive_entries = [];
        $index = 0;
        $start = microtime(true);
        foreach ($iterator as $info) {
            if(is_file($info)) {
                echo "Working on file {$index}/~{13000}\n";
                $file_path = $info->getPathname();
                $archive_file = new ArchiveFileHandler($file_path,false);
                $hash = $this->hashFile($archive_file->path_full);
                $entry = Archive::where('file_hash', $hash)->where('fragment',$archive_file->path)->first();
                if(!$entry) {
                    $entry = $this->createSEEntry($archive_file,$hash,[],null);
                    if(!$entry) {
                        continue;
                    }
                    $index++;
                }
                $scanned_archive_entries[] = $entry->id;
            }
            if($index>240) {
                foreach($this->errors as $error) {
                    echo $error;
                    echo "\n";
                }
                echo microtime(true) - $start;

                return "success";
            }
        }
        $not_found_archive_entries = Archive::whereNotIn('id',$scanned_archive_entries)->get();
        foreach($not_found_archive_entries as $entry) {
            $this->removeSEEntry($entry);
        }
        return "success";
    }

    private function createSEEntry($archive_file,$hash,$tags,$description) {
        $fragment = $archive_file->path;
        $url = URL::signedRoute(
            'file_hosting', ['fragment'=>$fragment,'disk'=>'archive']
        );
        $file_text_contents = $this->getFileContents($archive_file->path_full);
        $new_entry = new Archive([
            "file"=>["url"=>$url, 'disk'=>'archive', 'fragment'=>$fragment, "name"=>$archive_file->name],
            "tags"=>implode("_",$tags),
            "description"=>$description,
            "contents"=>$file_text_contents,
            "fragment"=>$fragment,
            "file_hash"=>$hash,
            "name"=>$archive_file->name,
        ]);
        $admin = User::findOrFail(4);
        $new_entry->creator()->associate($admin);
        $new_entry->save();
        $words = $this->getWords($file_text_contents);
        $previous_se_entry = NULL;
        foreach($words as $word) {
            $se_entry = $new_entry->search_engine()->where('word',$word)->first();
            if($se_entry) {
                $se_entry->count++;
            } else {
                $se_entry = new ArchiveSe([
                    "word"=>$word,
                    "count"=>1,
                ]);

                $se_entry->archive()->associate($new_entry);
            }
            try {
                $se_entry->save();
            } catch (\Illuminate\Database\QueryException $e) {
                // $incorrect_string_value = substr(explode("' for column 'word'", explode("Incorrect string value: '", ))[0],0,6);
                echo "-------\n";
                echo strlen($word);
                echo "-------\n";
                echo $word;
                echo "-------\n";
                echo $e->getMessage();
                // echo "Incorrect string value: '$incorrect_string_value'";
                // $this->errors[] = $incorrect_string_value;
                echo "-------\n";
                $word = substr($word, 1);
                echo $word;
                echo "-------\n";
                $se_entry->word = $word;
                try {
                    $se_entry->save();
                } catch (\Illuminate\Database\QueryException $e) {
                    echo "-------\n";
                    echo strlen($word);
                    echo "-------\n";
                    echo $word;
                    echo "-------\n";
                    echo $e->getMessage();
                    // echo "Incorrect string value: '$incorrect_string_value'";
                    // $this->errors[] = $incorrect_string_value;
                    echo "-------\n";
                    continue;
                }
                // $new_entry->delete();
                // return NULL;
            }

            if($previous_se_entry!==NULL) {
                $previous_se_entry->next()->syncWithoutDetaching($se_entry);
            }
            $previous_se_entry = $se_entry;

            $suggestion = ArchiveSeSuggestion::where("word", $word)->first();
            if(!$suggestion) {
                $suggestion = new ArchiveSeSuggestion([
                    "word"=>$word,
                    "occurrence"=>1,
                ]);
            } else {
                $suggestion->occurrence += 1;
            }
            $suggestion->save();

        }

        return $new_entry;
    }

    private function hashFile($file_path) {
        $file_raw_text = file_get_contents($file_path);
        return hash("sha256", $file_raw_text);
    }


    private function removeSEEntry($entry) {
        $entry->contents;
        $word_counts = $this->count_words($entry->contents);
        foreach($word_counts as $word=>$count) {
            $suggestion = ArchiveSeSuggestion::where("word",$word)->first();
            if(!$suggestion) {
                continue;
            } else if($suggestion->occurrence<=1) {
                $suggestion->delete();
                continue;
            }
            $suggestion->occurrence -= 1;
            $suggestion->save();
        }
        $entry->delete();
    }


    private function count_words($text) {
        $lines = explode("\n", $text);
        $word_counts = [];
        foreach($lines as $line) {
            $tab_separated_fragments = explode("\t", $line);
            foreach($tab_separated_fragments as $tab_separated_fragment) {
                $space_separated_words = explode(" ", $tab_separated_fragment);
                foreach($space_separated_words as $word) {
                    $word = $this->undoWordMutations($word,false);
                    if(!$this->isWord($word)) {
                        continue;
                    }
                    if(!array_key_exists($word,$word_counts)) {
                        $word_counts[$word] = 1;
                        continue;
                    }
                    $word_counts[$word]++;
                }
            }
        }
        return $word_counts;
    }

    private function getWords($text) {
        $lines = explode("\n", $text);
        $words = [];
        foreach($lines as $line) {
            $tab_separated_fragments = explode("\t", $line);
            foreach($tab_separated_fragments as $tab_separated_fragment) {
                $space_separated_words = explode(" ", $tab_separated_fragment);
                foreach($space_separated_words as $word) {
                    $word = $this->undoWordMutations($word,false);
                    if(!$this->isWord($word)) {
                        continue;
                    }

                    $words[] = $word;
                }
            }
        }
        return $words;
    }

    private function getFileContents($file_path) {
        $plain_text_extensions = ["csv", "txt"];
        
        $to_img_extensions = ["pdf", "png", "jpg"];

        $file_extension = pathinfo($file_path)["extension"];

        if(in_array($file_extension,$plain_text_extensions)) {
            $text = file_get_contents($file_path);
        } else if(in_array($file_extension,$to_img_extensions)) {
            $text = $this->imgToText($file_path);
        } else {
            return "";
        }
        return $text;
        // return mb_convert_encoding($text, "UTF-8", "ISO-8859-1");
    }

    public function storeFile(Request $request) {
        $file = $request->get("server_file");
        $target_directory = $request->get("target_directory");
        $target_filename = $request->get("target_filename");
        // return [$target_directory,$target_filename];
        if(!$file) {
            $file =  $request->file("file_upload");
        }
        $file = new ArchiveFileHandler($file);
        $file->saveAs($target_directory, $target_filename);
        return [$file->url,$file->name,$file->extension,$file->name_wo_extension,$file->path,$file->path_full,$file->dir_path,$file->dir_path_full];

        $root_path = Storage::disk('archive')->path('');
        $root_path = str_replace("\\","/",$root_path);
        $root_path = rtrim($root_path,'/');
        $file_path = $root_path."/".$server_file;
        $hash = $this->hashFile($file_path);

        $matches = Archive::where('file_hash', $hash)->get();
        if($matches->count()>0) {
            return ["status"=>"warning","message"=>"File already exists","data"=>$matches];
        }

        return $matches;
        // $target_dir_files = scandir($root_path."/".$target_directory);
        // foreach($target_dir_files)
        // check file exists


    }

    // private function makeArchiveEntry($tags,$description,$file) {

    //     $root_path = Storage::disk('archive')->path('');

    //     $url = URL::signedRoute(
    //         'file_hosting', ['fragment'=>$new_file,'disk'=>'archive']
    //     );
    //     $text = $this->imgToText($$root_path."/".$file);

    //     new Archive([
    //         "tags"=>$tags,
    //         "description"=>$description,
    //         "contents"=>$text,
    //         "file"=>["url"=>$url, 'disk'=>'archive', 'fragment'=>$new_file, "name"=>$file->getClientOriginalName()],
    //         "fragment"=>'/'.$new_file,
    //         "name"=>$file->getClientOriginalName(),
    //     ])
    // }

    public function store(Request $request) {

        $request->validate([
            "file"=>"required",
        ]);
        $user = Auth::user();
        $src = $request->get('src');
        $root_path = Storage::disk('archive')->path('');
        if(!is_dir($root_path.$src)) {
            return abort(response()->json(
                ["errors"=>[
                    "directory"=>"Can not upload to directory {$src}"
                ]],400));
        }
        $tags = json_decode($request->get("tags"),true);
        $description = $request->get("description");
        $file = $request->file("file");

        $file_rel_path = $file->store($src,'archive');


        $old_local_file = $root_path."/".$file_rel_path;
        $file_fragments = explode('/',$file_rel_path);
        $file_name_fragments = explode('.', $file_fragments[count($file_fragments)-1]);
        $new_file = implode('/',array_slice($file_fragments,0,-1)).'/'.$file->getClientOriginalName();
        $local_file = $root_path.'/'.$new_file;
        rename($old_local_file, $local_file);

        $url = URL::signedRoute(
            'file_hosting', ['fragment'=>$new_file,'disk'=>'archive']
        );

        $text = $this->imgToText($local_file);
        $new_entry = new Archive([
            "tags"=>$tags,
            "description"=>$description,
            "contents"=>$text,
            "file"=>["url"=>$url, 'disk'=>'archive', 'fragment'=>$new_file, "name"=>$file->getClientOriginalName()],
            "fragment"=>'/'.$new_file,
            "name"=>$file->getClientOriginalName(),
        ]);
        $new_entry->creator()->associate($user);
        $new_entry->save();

        $word_counts = $this->count_words($text);
        $insertables = [];
        foreach($word_counts as $word=>$count) {
            $se_entry = new ArchiveSe([
                "word"=>$word,
                "count"=>$count,
            ]);
            $se_entry->archive()->associate($new_entry);
            $se_entry->save();
            
            $suggestion = ArchiveSeSuggestion::where("word", $word)->first();
            if(!$suggestion) {
                $suggestion = new ArchiveSeSuggestion([
                    "word"=>$word,
                    "occurrence"=>1,
                ]);
            } else {
                $suggestion->occurrence += 1;
            }
            $suggestion->save();
        }
        

        return response()->json($new_entry);
    }

    private function imgToText($local_file) {
        $convert = "C:\Program Files\ImageMagick-7.1.1-Q16-HDRI\convert";
        $tmp_img = "D:\inetpub\\tmp\\tmp_image";
        echo "Processing file {$local_file}\n\n\n";
        exec('"'.$convert.'" -density 300 "'.$local_file.'" "'.$tmp_img.'.png"');
        

        $tesseract = "C:\Program Files\Tesseract-OCR\\tesseract";

        $tmp_txt = "D:\inetpub\\tmp\\tmp_im_text";
        $index = 0;
        $text = "";
        $base_img = $tmp_img;
        if(is_file($base_img.'.png')) {
            exec('"'.$tesseract.'" -l deu+eng "'.$base_img.'.png" "'.$tmp_txt.'"');
            unlink($base_img.'.png');
            $text .= file_get_contents($tmp_txt.'.txt');
        } else {
            $base_img = $tmp_img.'-0';
            while(is_file($base_img.'.png')) {
                $index++;
                exec('"'.$tesseract.'" -l deu+eng "'.$base_img.'.png" "'.$tmp_txt.'"');
                unlink($base_img.'.png');
                $text .= file_get_contents($tmp_txt.'.txt');
                $base_img = $tmp_img."-{$index}";
            }
        }
        unlink($tmp_txt.'.txt');
        return $text;
    }

    private function isValidDir($directory) {
        if($directory==='.' || $directory==='..' || str_starts_with($directory,'$')) {
            return false;
        }
        return true;
    }

    private function searchDirRecursively($current_dir,$target) {
        $matches = glob($current_dir.$target);
        if(count($matches)==1 && is_dir($matches[0])) {
            return $matches[0];
        } else {
            $dirs = glob($current_dir.'*',GLOB_ONLYDIR | GLOB_MARK );
            foreach($dirs as $dir) {
                $dir = $this->searchDirRecursively($dir,$target);
                if($dir===NULL) {
                    continue;
                }
                return $dir;
            }
        }
        return NULL;
    }


    private function searchForDirectoryRecursively($root,$search_string) {
        $root_path = Storage::disk('archive')->path('');
        $dirs = scandir($root_path.$root);
        $results = [];

        foreach($dirs as $dir) {
            if(!$this->isValidDir($dir) || !is_dir($root_path.$root.$dir)) {
                continue;
            }

            if(str_contains($dir,$search_string)) {
                $results[] = $root.$dir;
            } else {
                $results = array_merge($results,$this->searchForDirectoryRecursively($root.$dir.'/',$search_string));
            }
        }
        return $results;
    }

    private function searchForDirectory($user, $search_string) {
        $permissions = $user->archive_permissions_condensed();

        foreach($permissions as $permission) {
            $res = $this->searchForDirectoryRecursively($permission->directory,$search_string);
            return $res;
        }
    }

    public function directory(Request $request) {
        $user = Auth::user();
        $dir = json_decode($request->get('dir'),true);
        $root = $request->get('root');
        // return [$root,$dir];
        return $this->getSubfoldersWithPermission($dir,$root,$user);
    }


    private function getSubfoldersWithPermission($sub_folders,$root,$user) {
        $root_path = Storage::disk('archive')->path('');
        $root_path = str_replace("\\","/",$root_path);
        $root_path = rtrim($root_path,'/');
        if($root) {
            $fragment = implode("/",[$root,...$sub_folders]);
            $folder = implode("/",[$root_path,$root,...$sub_folders]);
        } else {
            $fragment = implode("/",$sub_folders);
            $folder = implode("/",[$root_path,...$sub_folders]);
        }

        $dirs = scandir($folder);
        // return [$fragment,$folder];
        $dirs_result = [];
        $files_result = [];
        foreach($user->archive_permissions_condensed() as $permission) {
            $permission_dir = $permission->directory;
            foreach($dirs as $dir) {
                if(!$this->isValidDir($dir)) {
                    continue;
                }

                if(str_starts_with(strtolower($folder."/".$dir),strtolower($root_path.$permission_dir))) {
                    
                    if($fragment) {
                        $curr_fragments = $fragment."/".$dir; 
                    } else {
                        $curr_fragments = $dir; 
                    }
                    if(is_dir($folder."/".$dir)){
                        $dirs_result[] = [
                            "fragments"=>explode("/",$curr_fragments),
                            "contents"=>[],
                            "is_dir"=>true,
                            "name"=>$dir,
                            "permission"=>$permission->permission,
                        ];
                    } else {
                        $url = URL::signedRoute(
                            'file_hosting', ['fragment'=>$curr_fragments,'disk'=>'archive']
                        );
                        $files_result[] = [
                            "fragments"=>explode("/",$curr_fragments),
                            "is_dir"=>false,
                            "name"=>$dir,
                            "href"=>$url,
                            "permission"=>$permission->permission,
                        ];  
                    }

                }
            }
        }
        return ["folders"=>$dirs_result,"files"=>$files_result];
    }

    private function getWordDistance($first, $second, $intermediate=[]) {
        $new_intermediate = $intermediate;
        $nexts = $first->next;
        $min_distance = 1000;
        $c = [];
        foreach($nexts as $next) {
            if($min_distance<=0) {
                return $min_distance;
            }
            if($next->word!==$second->word && count($new_intermediate)<10 && !in_array($next->word,$new_intermediate)) {
                $new_intermediate[] = $next->word;
                $distance = $this->getWordDistance($next, $second, $new_intermediate);
            } else {
                $distance = count($new_intermediate);
            }
            if($distance<$min_distance) {
                $min_distance = $distance;
            }
        }
        return $min_distance;
    }
    private function getSuggestionOrder($min_distances_per_word, $archive_ses_per_word,$matches_by_description,$matches_by_filename) {
        $scores = [];
        foreach($archive_ses_per_word as $archive_ses) {
            foreach($archive_ses as $archive_se) {
                if(array_key_exists($archive_se->archive_id,$scores)) {
                    $scores[$archive_se->archive_id] += $archive_se->count;
                } else {
                    $scores[$archive_se->archive_id] = $archive_se->count;
                }
            }
        }
        foreach($min_distances_per_word as $distances) {
            foreach($distances as $archive_id=>$distance) {
                $scores[$archive_id] += 20-2*$distance;
            }         
        }

        foreach($matches_by_description as $match) {
            if(array_key_exists($match->id,$scores)) {
                $scores[$match->id] += 20;
            } else {
                $scores[$match->id] = 20;
            } 
        }

        foreach($matches_by_filename as $match) {
            if(array_key_exists($match->id,$scores)) {
                $scores[$match->id] += 10;
            } else {
                $scores[$match->id] = 10;
            } 
        }

        $order = [];
        foreach($scores as $archive_id=>$score) {
            if(count($order)<=0) {
                $order[] = $archive_id;
                continue;
            }
            if(count($order)==1) {
                if($score>$scores[$order[0]]) {
                    array_unshift($order,$archive_id);
                } else {
                    $order[] = $archive_id;
                }
                continue;
            }
            $insert_at = NULL;
            foreach($order as $index=>$order_archive_id) {
                if($insert_at!==NULL) {
                    continue;
                }
                if($score>$scores[$order_archive_id]) {
                    if($index===0) {
                        $insert_at="first";
                    } else {
                        $insert_at = $index;
                    }
                    continue;
                }
                if($index===count($order)-1) {
                    $insert_at = "last";
                }
            }
            if($insert_at!==NULL) {
                if($insert_at==="first") {
                    array_unshift($archive_id,$order);
                } else if($insert_at==="last") {
                    $order[] = $archive_id;
                } else {
                    array_splice($order, $insert_at,0,$archive_id);
                }
            }
        }
        return $order;
    }

    public function searchsuggestions(Request $request) {
        $search_string = $request->get('search_string');
        $search_words = explode(" ", $search_string);
        $se_suggestions = NULL;
        for($i=count($search_words)-1;$i>=0;$i--) {
            $search_word = $search_words[$i];
            $search_word = $this->undoWordMutations($search_word,false);
            if(!$this->isWord($search_word)) {
                continue;
            }
            $se_suggestions = ArchiveSeSuggestion::orderBy('occurrence','DESC')->where('word', 'LIKE', '%'.$search_word.'%')->limit(5)->get();
            break;
        }
        if(!$se_suggestions) {
            return response()->json([
                "search_string"=>$search_string,
                "search_word"=>NULL,
                "search_suggestions"=>[],
            ]);
        }
        return response()->json([
            "search_string"=>$search_string,
            "search_word"=>$search_words[$i],
            "search_suggestions"=>$se_suggestions,
        ]);
    }


    private function getMatchingDirContents($dir, $folder, &$results = []) {
        $contents = scandir($dir);
        foreach($contents as $key=>$content) {
            if($content=="." || $content=="..") {
                continue;
            }
            if(count($results)>10) {
                return $results;
            }
            $path = realpath($dir."/".$content);
            if(is_dir($path)) {
                $this->getMatchingDirContents($path,$folder, $results);
                if(str_contains(strtolower($content),strtolower($folder))) {
                    $results[] = $path;
                }
            }

        }
        return $results;
    }

    private function getMatchingFolders($folder,$user,$root) {
        $root_path = Storage::disk('archive')->path('');
        $root_path = str_replace("\\","/",$root_path);
        $root_path = rtrim($root_path,'/');
        foreach($user->archive_permissions_condensed() as $permission) {
            $permission_dir = $permission->directory;
            // abort(response()->json([str_starts_with($active_dir,$permission_dir),$active_dir,$permission_dir],401));
            if(!str_starts_with("/".$root,$permission_dir)) {
                continue;
            } 
            $dirs = $this->getMatchingDirContents($root_path."/".$root,$folder);
            return $dirs;
        }
        return NULL;
    }

    public function search(Request $request) {
        $user = Auth::user();
        // $user = Auth::loginUsingId(4);

        $search_string = $request->get('search_string');
        $search_offset = $request->get('search_offset');
        $root = $request->get('root');
        $search_words = explode(" ", $search_string);
        // return [$search_string,$root];
        $archive_ses_per_word = [];
        $min_distances_per_word = [];

        foreach($search_words as $search_word) {
            $search_word = $this->undoWordMutations($search_word,false);
            if(!$this->isWord($search_word)) {
                continue;
            }
            if(count($archive_ses_per_word)<=0) {
                $se_suggestions = ArchiveSeSuggestion::orderBy('occurrence','DESC')->where('word', 'LIKE', '%'.$search_word.'%')->limit(10)->get();
                $archive_ses = ArchiveSe::orderBy('count', 'DESC')->whereIn('word',$se_suggestions->pluck('word'))->skip($search_offset)->limit(10)->get();
                $archive_ses_per_word[] = $archive_ses;
                continue;
            }

            $min_distances_per_word[$search_word] = [];
            $se_suggestions = ArchiveSeSuggestion::orderBy('occurrence','DESC')->where('word', 'LIKE', '%'.$search_word.'%')->limit(10)->get();
            $archive_ses = ArchiveSe::orderBy('count', 'DESC')->whereIn('word',$se_suggestions->pluck('word'))->whereIn('archive_id', $archive_ses_per_word[count($archive_ses_per_word)-1]->pluck('archive_id'))->limit(10)->get();
            $archive_ses_per_word[] = $archive_ses;

            foreach($archive_ses_per_word[count($archive_ses_per_word)-2] as $first_word_archive_se) {
                foreach($archive_ses as $archive_se) {
                    if($first_word_archive_se->archive_id!==$archive_se->archive_id) {
                        continue;
                    }
                    $distance = $this->getWordDistance($first_word_archive_se,$archive_se,[],$archive_ses_per_word[0]->pluck('word')->toArray());
                    if(array_key_exists($first_word_archive_se->archive_id,$min_distances_per_word[$search_word])) {
                        if($distance<$min_distances_per_word[$search_word][$first_word_archive_se->archive_id]) {
                            $min_distances_per_word[$search_word][$first_word_archive_se->archive_id] = $distance;
                        }
                    } else {
                        $min_distances_per_word[$search_word][$first_word_archive_se->archive_id] = $distance;
                    }
                }
            }

        }
        $matches_by_description = Archive::where('description', 'LIKE', '%'.$search_word.'%')->limit(30)->get();
        $matches_by_filename = Archive::where('name', 'LIKE', '%'.$search_word.'%')->limit(30)->get();


        $order = $this->getSuggestionOrder($min_distances_per_word, $archive_ses_per_word,$matches_by_description,$matches_by_filename);
        $results = [];
        foreach($order as $archive_id) {
            $archive_result = Archive::where('fragment', 'LIKE',$root.'%')->where("id",$archive_id)->first();
            if($archive_result) {
                $results[] = $archive_result;
            }
        }


        $root_path = Storage::disk('archive')->path('');
        $root_path = str_replace("\\","/",$root_path);
        $root_path = rtrim($root_path,'/');
        $folders = collect($this->getMatchingFolders($search_string,$user,$root))->map(function ($dir) use ($root_path){
            $dir = str_replace("\\", "/", $dir);
            $fragments = explode("/", $dir);
            if (substr($dir, 0, strlen($root_path)) == $root_path) {
                $dir = substr($dir, strlen($root_path));
            }
            return [
                "name"=>$fragments[count($fragments)-1],
                "fragments"=>explode("/",ltrim($dir,"/")),
                "contents"=>[],
                "is_dir"=>true,
                "permission"=>3,
            ];
        });
        $results = collect($results)->map(function ($file){
            return [
                "name"=>$file->file["name"],
                "fragments"=>explode("/",$file->file["fragment"]),
                "is_dir"=>false,
                "href"=>$file->file["url"],
                "permission"=>3,
            ];
        });
        return ["files"=>$results,"folders"=>$folders];
    }


    public function searchsuggestions2(Request $request) {

        $search_string = $request->get('search_string');
        $search_words = explode(" ", $search_string);
        $results = NULL;
        $archive_ses_per_word = [];
        $min_distances_per_word = [];
        $word_indices = [];
        foreach($search_words as $search_word) {
            if(!$this->isWord($search_word)) {
                continue;
            }
            $search_word = $this->undoWordMutations($search_word,false);
            if(count($word_indices)<=0) {
                $se_suggestions = ArchiveSeSuggestion::orderBy('occurrence','DESC')->where('word', 'LIKE', '%'.$search_word.'%')->limit(10)->get();
                $archive_ses = ArchiveSe::orderBy('count', 'DESC')->whereIn('word',$se_suggestions->pluck('word'))->limit(10)->get();
                $archive_ses_per_word[] = $archive_ses;
                $word_indices[] = $search_word;
                continue;
            }
            $min_distances_per_word[$search_word] = [];
            $se_suggestions = ArchiveSeSuggestion::orderBy('occurrence','DESC')->where('word', 'LIKE', '%'.$search_word.'%')->limit(10)->get();
            $archive_ses = ArchiveSe::orderBy('count', 'DESC')->whereIn('word',$se_suggestions->pluck('word'))->whereIn('archive_id', $archive_ses_per_word[count($word_indices)-1]->pluck('archive_id'))->limit(10)->get();
            $archive_ses_per_word[] = $archive_ses;
            foreach($archive_ses_per_word[count($word_indices)-1] as $first_word_archive_se) {
                foreach($archive_ses as $archive_se) {
                    if($first_word_archive_se->archive_id!==$archive_se->archive_id) {
                        continue;
                    }
                    $distance = $this->getWordDistance($first_word_archive_se,$archive_se,[],$archive_ses_per_word[0]->pluck('word')->toArray());
                    if(array_key_exists($first_word_archive_se->archive_id,$min_distances_per_word[$search_word])) {
                        if($distance<$min_distances_per_word[$search_word][$first_word_archive_se->archive_id]) {
                            $min_distances_per_word[$search_word][$first_word_archive_se->archive_id] = $distance;
                        }
                    } else {
                        $min_distances_per_word[$search_word][$first_word_archive_se->archive_id] = $distance;
                    }

                }
            }
            $word_indices[] = $search_word;
        }
        
        $order = $this->getSuggestionOrder($min_distances_per_word, $archive_ses_per_word);
        $results = [];
        foreach($order as $archive_id) {
            $results[] = Archive::findOrFail($archive_id);
        }
        return $results;
        // $se_suggestions = $this->getSeSuggestions($search_words[0]);
        // $archive_ses = ArchiveSe::orderBy('count', 'DESC')->whereIn('word',$se_suggestions->pluck('word'))->limit(10)->get();
        // foreach($archive_ses as $archive_se) {
        //     return $archive_se->previous;
        // }

        // foreach($search_words as $index=>$search_word) {
        //     if($index===0) {
        //         continue;
        //     }
        // }
        // foreach($search_words as $search_word) {

        //     $asdf = $this->undoWordMutations($search_word,false);
        //     $se_suggestions = ArchiveSeSuggestion::orderBy('occurrence','DESC')->where('word', 'LIKE', '%'.$search_word.'%')->limit(10)->get();
        //     foreach
        //     $results = $se_suggestions;
        // }

        $user = Auth::user();
        $dir_suggestions = $this->searchForDirectory($user,$search_string);
        // $dir_suggestions = "";
        return ["value"=>$search_string,"suggestions"=>$results,"dir_suggestions"=>$dir_suggestions];
    }


    public function moveFile(Request $request) {
        $src = $request->get('src');
        $src_fragments = explode("/",$src);
        $tar = $request->get('tar');
        $tar_fragments = explode("/",$tar);

        $id = $request->get('id');

        $root_path = Storage::disk('archive')->path('');
        $url = URL::signedRoute(
            'file_hosting', ['fragment'=>$tar.'/'.$src_fragments[count($src_fragments)-1],'disk'=>'archive']
        );
        if($id!=='undefined') {
            $archive = Archive::findOrFail($id);
            $file = $archive->file;
            $file["url"] = $url;
            $file["fragment"] = $tar.'/'.$src_fragments[count($src_fragments)-1];
            $archive->file = $file;
            $archive->fragment = $tar.'/'.$src_fragments[count($src_fragments)-1];
            $archive->save();
        } else {
            $archive = Archive::where('fragment',$src)->first();
            if($archive) {
                $file = $archive->file;
                $file["url"] = $url;
                $file["fragment"] = $tar.'/'.$src_fragments[count($src_fragments)-1];
                $archive->file = $file;
                $archive->fragment = $tar.'/'.$src_fragments[count($src_fragments)-1];
                $archive->save();
            }
        }
        rename($root_path.$src, $root_path.$tar."/".$src_fragments[count($src_fragments)-1]);

        return ["fragment"=>$src_fragments[count($src_fragments)-1],"url"=>$url,"is_dir"=>false,"dir"=>$tar.'/'.$src_fragments[count($src_fragments)-1]];
    }

    public function copyFile(Request $request) {
        $user = Auth::user();
        $id = $request->get('id');
        $src = $request->get('src');
        if($id!=='undefined') {
            $archive = Archive::findOrFail($id);
        } else {
            $archive = Archive::where('fragment',$src)->first();
            if(!$archive) {
                abort(response()->json([
                    "message"=>"No archive entry found"],404));
            }
        }

        $src_fragments = explode("/",$src);
        $tar = $request->get('tar');
        $tar_fragments = explode("/",$tar);
        $root_path = Storage::disk('archive')->path('');
        $filename_fragments = explode('.',$src_fragments[count($src_fragments)-1]);
        $file_extension = $filename_fragments[count($filename_fragments)-1];
        $filename = implode('.',array_slice($filename_fragments,0,-1));
        copy($root_path.$src, $root_path.$tar."/".$filename.'_copy.'.$file_extension);
        $url = URL::signedRoute(
            'file_hosting', ['fragment'=>$tar.'/'.$filename.'_copy.'.$file_extension,'disk'=>'archive']
        );


        $local_file = $root_path.$tar."/".$filename.'_copy.'.$file_extension;
        $new_entry = new Archive([
            "tags"=>$archive->tags,
            "description"=>$archive->description,
            "contents"=>$archive->contents,
            "file"=>["url"=>$url, 'disk'=>'archive', 'fragment'=>$tar.'/'.$filename.'_copy.'.$file_extension, "name"=>$archive->file["name"]],
            "fragment"=>$tar.'/'.$filename.'_copy.'.$file_extension,
            "name"=>$archive->file["name"],
        ]);
        $new_entry->creator()->associate($user);
        $new_entry->save();

        $word_counts = $this->count_words($archive->contents);
        $insertables = [];
        foreach($word_counts as $word=>$count) {
            $se_entry = new ArchiveSe([
                "word"=>$word,
                "count"=>$count,
            ]);
            $se_entry->archive()->associate($new_entry);
            $se_entry->save();
            
            $suggestion = ArchiveSeSuggestion::where("word", $word)->first();
            if(!$suggestion) {
                $suggestion = new ArchiveSeSuggestion([
                    "word"=>$word,
                    "occurrence"=>1,
                ]);
            } else {
                $suggestion->occurrence += 1;
            }
            $suggestion->save();
        }

        return ["fragment"=>$filename.'_copy.'.$file_extension,"url"=>$url,"is_dir"=>false,"dir"=>$tar.'/'.$filename.'_copy.'.$file_extension];
    }

    public function renameFile(Request $request) {
        $id = $request->get('id');
        $src = $request->get('src');
        $filename = $request->get('filename');
        if(!$this->validName($filename)) {
            abort(response()->json(
                ["errors"=>[
                    "filename"=>"File contains invalid characters"
                ]],422));
        }
        $file_extension = $request->get('file_extension');
        $src_fragments = explode("/",$src);
        $tar = implode("/",array_slice($src_fragments,0,-1));
        $root_path = Storage::disk('archive')->path('');
        $url = URL::signedRoute(
            'file_hosting', ['fragment'=>$tar."/".$filename.'.'.$file_extension,'disk'=>'archive']
        );
        if($id!=='undefined') {
            $archive = Archive::findOrFail($id);
            $file = $archive->file;
            $file["url"] = $url;
            $file["fragment"] =  $tar."/".$filename.'.'.$file_extension;
            $archive->file = $file;
            $archive->fragment = $tar."/".$filename.'.'.$file_extension;
            $archive->save();
        } else {
            $archive = Archive::where('fragment',$src)->first();
            if(!$archive) {
                abort(response()->json("Archive entry not found",404));
            }
            $file = $archive->file;
            $file["url"] = $url;
            $file["fragment"] =  $tar."/".$filename.'.'.$file_extension;
            $archive->file = $file;
            $archive->fragment = $tar."/".$filename.'.'.$file_extension;
            $archive->save();
        }
        rename($root_path.$src, $root_path.$tar."/".$filename.'.'.$file_extension);
        return ["fragment"=>$filename.'.'.$file_extension,"url"=>$url,"is_dir"=>false,"dir"=>$tar."/".$filename.'.'.$file_extension];

    }
    private function validName($name) {
        if (strpbrk($name, "\\/?%*:|\"<>")) {
            return false;
        }
        return true;
    }
    public function renameDir(Request $request) {

        $request->validate([
            "rename_dirname"=>"required",
        ]);
        $dirname = $request->get('rename_dirname');
        if(!$this->validName($dirname)) {
            abort(response()->json(
                ["errors"=>[
                    "rename_dirname"=>"Directory contains invalid characters"
                ]],422));
        }
        $src = $request->get('src');
        $src_fragments = explode("/",$src);
        $tar = implode("/",array_slice($src_fragments,0,-1));
        $root_path = Storage::disk('archive')->path('');
        rename($root_path.$src, $root_path.$tar."/".$dirname);
        $entries = Archive::where('fragment', 'LIKE', '%'.$src.'%')->get();
        foreach($entries as $entry) {
            $file = $entry->file;
            $suffix = ltrim($entry->fragment,$src);
            $url = URL::signedRoute(
                'file_hosting', ['fragment'=>$tar."/".$dirname.'/'.$suffix,'disk'=>'archive']
            );
            $file["url"] = $url;
            $file["fragment"] = $tar."/".$dirname.'/'.$suffix;
            $entry->file = $file;
            $entry->fragment = $tar."/".$dirname.'/'.$suffix;
            $entry->save();
        }
        if($tar) {
            return ["fragment"=>$dirname,"url"=>null,"is_dir"=>true,"dir"=>$tar."/".$dirname];
        } else {
            return ["fragment"=>$dirname,"url"=>null,"is_dir"=>true,"dir"=>$dirname];
        }
    }
    public function createDir(Request $request) {
        $src = $request->get('src');
        $dirname = $request->get('create_dirname');
        if(!$this->validName($dirname)) {
            abort(response()->json(
                ["errors"=>[
                    "create_dirname"=>"Directory contains invalid characters"
                ]],422));
        }
        $root_path = Storage::disk('archive')->path('');
        if(is_dir($root_path.$src.'/'.$dirname)) {
            return abort(response()->json(
                ["errors"=>[
                    "create_dirname"=>"Directory {$src}/{$dirname} already exists"
                ]],422));
        }
        mkdir($root_path.$src.'/'.$dirname);
        return ["fragment"=>$dirname,"url"=>null,"is_dir"=>true,"dir"=>$src.'/'.$dirname];
    }



}
