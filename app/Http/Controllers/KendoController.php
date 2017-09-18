<?php

namespace App\Http\Controllers;

use App\kendo;
use Illuminate\Http\Request;

class KendoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	public function pagenotfound()
    {
        return view('error_layout.master');
    } 
	 
	 
    public function index()
    {
        $data=kendo::all();
        $array=array("data"=>$data,"total"=>count($data));
        return response()->json($array);
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
     * @param  \App\kendo  $kendo
     * @return \Illuminate\Http\Response
     */
    public function show(kendo $kendo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\kendo  $kendo
     * @return \Illuminate\Http\Response
     */
    public function edit(kendo $kendo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\kendo  $kendo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, kendo $kendo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\kendo  $kendo
     * @return \Illuminate\Http\Response
     */
    public function destroy(kendo $kendo)
    {
        //
    }
}
