<?php

namespace App\Http\Controllers;

use App\MaritalStatus;
use Illuminate\Http\Request;

class MaritalStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('module.settings.maritalstatus');
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
        $this->validate($request,['name'=>'required']);
        $tab=new MaritalStatus;
        $tab->name=$request->name;
        $tab->save();

        return redirect()->action('MaritalStatusController@index')->with('success','Information Added Successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $json=MaritalStatus::all();
        return response()->json(array("data"=>$json,"total"=>count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data=MaritalStatus::find($id);
        return view('module.settings.maritalstatus',['data'=>$data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $this->validate($request,['name'=>'required']);
        $tab=MaritalStatus::find($id);
        $tab->name=$request->name;
        $tab->save();

        return redirect()->action('MaritalStatusController@index')->with('success','Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $del=MaritalStatus::destroy($request->id);
        return 1;    
    }
}