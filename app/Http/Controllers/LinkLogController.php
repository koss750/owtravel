<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LinkLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $linkLogController;

    public function __construct(LinkLogController $linkLogController)
    {
        $this->linkLogController = $linkLogController;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {

    }
}
