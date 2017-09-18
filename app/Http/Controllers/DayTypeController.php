<?php

namespace App\Http\Controllers;

use App\DayType;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DayTypeController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $dataCompany = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.dayType', ['company' => $dataCompany,'logged_emp_com'=>$logged_emp_company_id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $this->validate($request, [
            'company_id' => 'required',
            'name' => 'required',
            'day_short_code' => 'required',
            'is_active' => 'required' 
        ]);



        $DayType = new DayType;
        $DayType->company_id =  $request->company_id;
        $DayType->title = $request->name;
        $DayType->day_short_code = $request->day_short_code;
        $DayType->is_active = $request->is_active;
        $DayType->save();
        return redirect()->action('DayTypeController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function show() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        
        $DayType = DayType::where('company_id', $logged_emp_company_id)->get();

        return response()->json(array("data" => $DayType, "total" => count($DayType)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function edit(DayType $DayType, $id) {
        $dataCompany = Company::all();
        $data = DayType::find($id);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.dayType', ['data' => $data, 'company' => $dataCompany,'logged_emp_com'=>$logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DayType $dayType, $id) {
        $this->validate($request, [
            'company_id' => 'required',
            'name' => 'required',
            'day_short_code' => 'required'
        ]);



        $DayType = DayType::find($id);
        $DayType->title = $request->name;
        $DayType->company_id = $request->company_id;
        $DayType->day_short_code = $request->day_short_code;
        $DayType->is_active = $request->is_active;
        $DayType->save();
        return redirect()->action('DayTypeController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = DayType::destroy($request->id);
        return 1;
    }

}
