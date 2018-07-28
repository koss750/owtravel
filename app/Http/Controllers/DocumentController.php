<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Http\Request;

class DocumentTypesController extends Controller
{
    public function _construct(){

    }

    public function index(){
        return Document::all();
    }
}
