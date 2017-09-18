<?php

namespace App\Http\Controllers;

use App\BloodGroup;
use Illuminate\Http\Request;

class BloodGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('module.settings.bloodgroup');
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
        $tab=new BloodGroup;
        $tab->name=$request->name;
        $tab->save();

        return redirect()->action('BloodGroupController@index')->with('success','Information Added Successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\bloodgroup  $bloodgroup
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $json=BloodGroup::all();
        return response()->json(array("data"=>$json,"total"=>count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\bloodgroup  $bloodgroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data=BloodGroup::find($id);
        return view('module.settings.bloodgroup',['data'=>$data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\bloodgroup  $bloodgroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $this->validate($request,['name'=>'required']);
        $tab=BloodGroup::find($id);
        $tab->name=$request->name;
        $tab->save();

        return redirect()->action('BloodGroupController@index')->with('success','Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\bloodgroup  $bloodgroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $del=BloodGroup::destroy($request->id);
        return 1;    
    }
}
