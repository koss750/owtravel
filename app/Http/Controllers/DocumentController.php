<?php

namespace App\Http\Controllers;

use App\Document;
use App\Http\Transformers\DocumentTransformer;
use App\User;
use League\Fractal;
use Illuminate\Http\Response;



class DocumentController extends Controller
{
    public function _construct($document){
        $this->document = $document;
    }

    public function index(DocumentTransformer $documentTransformer){
        $docs = Document::all();
        return $this->respond($this->showCollection($docs,$documentTransformer));
    }

    public function my_docs($id, DocumentTransformer $documentTransformer){


        try {
            $user = User::where('id', $id)->firstOrFail();
        } catch (\Exception $e) {
            abort (500,"User not found");
        }

        try {
            $docs = Document::where('user_id', $user->id)->get();

            return $this->respond($this->showCollection($docs,$documentTransformer));
        } catch (\Exception $e) {
                abort (500,"No documents found for $user->name");
            }


    }
}
