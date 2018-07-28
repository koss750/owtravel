<?php

namespace App\Http\Controllers;

use App\DocumentTypes;
use Illuminate\Http\Request;

class DocumentTypesController extends Controller
{
    public function _construct(){

    }

    public function index(){
        return DocumentTypes::all();
    }
}
