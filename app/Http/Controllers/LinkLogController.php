<?php

namespace App\Http\Controllers;

use App\LinkLog;
use Illuminate\Http\Request;

class LinkLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $linkLog;

    public function __construct(LinkLog $linkLog)
    {
        $this->linkLog = $linkLog;
    }

    /**
     * Show the application dashboard.
     *
     * @param $type
     * @param $subtype
     * @return void
     */
    public function getCurrentStatus($type, $subtype)
    {
        $latest = LinkLog::where('type', $type)->where('subtype', $subtype)->latest()->first();
    }
}
