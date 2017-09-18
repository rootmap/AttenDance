<?php

namespace App\Http\Controllers;

use App\City;
use App\Country;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $country=Country::all();
        return view('module.settings.city',['country'=>$country]);
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

    public function filterCity(Request $request)
    {
        $data=City::where('country_id',$request->country_id)->get();
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,['name'=>'required','country_id'=>'required','is_active'=>'required']);
        $tab=new City;
        $tab->name=$request->name;
        $tab->country_id=$request->country_id;
        $tab->is_active=$request->is_active;
        $tab->save();

        return redirect()->action('CityController@index')->with('success','Information Added Successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\City  $City
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $json=City::all();
        return response()->json(array("data"=>$json,"total"=>count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\City  $City
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data=City::find($id);
        $country=Country::all();
        return view('module.settings.city',['data'=>$data,'country'=>$country]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\City  $City
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $this->validate($request,['name'=>'required','country_id'=>'required']);
        $tab=City::find($id);
        $tab->name=$request->name;
        $tab->country_id=$request->country_id;
        $tab->is_active=$request->is_active;
        $tab->save();

        return redirect()->action('CityController@index')->with('success','Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\City  $City
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $del=City::destroy($request->id);
        return 1;
    }
}
