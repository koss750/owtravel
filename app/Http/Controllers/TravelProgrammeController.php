<?php

namespace App\Http\Controllers;

use App\Http\Transformers\TravelProgrammeTransformer;
use App\TravelProgramme;
use Illuminate\Http\Request;

class TravelProgrammeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all = TravelProgramme::all();
        return $this->respond($this->showCollection($all, new TravelProgrammeTransformer));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TravelProgramme  $travelProgramme
     * @return \Illuminate\Http\Response
     */
    public function show(TravelProgramme $travelProgramme)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TravelProgramme  $travelProgramme
     * @return \Illuminate\Http\Response
     */
    public function edit(TravelProgramme $travelProgramme)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TravelProgramme  $travelProgramme
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TravelProgramme $travelProgramme)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TravelProgramme  $travelProgramme
     * @return \Illuminate\Http\Response
     */
    public function destroy(TravelProgramme $travelProgramme)
    {
        //
    }
}
