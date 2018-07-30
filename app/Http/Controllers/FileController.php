<?php

namespace App\Http\Controllers;

use App\Document;
use App\File;
use App\Http\Transformers\DocumentTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Fractal;
use Illuminate\Http\Response;



class FileController extends Controller
{
    public function _construct($file){
        $this->file = $file;
    }

    public function store(Request $request)
    {
        try {
            $prn = Str::uuid();
        $path = Storage::putFile($prn, $request->file('uploaded_file'));
        $new_file = new File;
        $new_file->prn = $path;
        $new_file->name = "test";
        $new_file->save();
        return "good";
        }
        catch (\Exception $e) {
            var_dump($e);
        }
    }

}
