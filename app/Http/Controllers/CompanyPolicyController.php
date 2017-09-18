<?php

namespace App\Http\Controllers;

use App\Company;
use App\CompanyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CompanyPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $dataCompany = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.companyPolicy', ['company' => $dataCompany,'logged_emp_com'=>$logged_emp_company_id]);
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
            'policy_heading' => 'required',
            'policy_publish_date' => 'required',
            'policy_description' => 'required', 
            'policy_status' => 'required' 
        ]);
		$logged_emp_company_id = MenuPageController::loggedUser('company_id');
		$policy_status=$request->policy_status?'1':0;

        $DayType = new CompanyPolicy;
        $DayType->company_id =  $logged_emp_company_id;
        $DayType->policy_heading = $request->policy_heading;
        $DayType->policy_publish_date = $request->policy_publish_date;
        $DayType->policy_description = $request->policy_description;
        $DayType->policy_status = $policy_status;
        $DayType->save();
        return redirect()->action('CompanyPolicyController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function show() {
        
        $DayType = CompanyPolicy::all();

        return response()->json(array("data" => $DayType, "total" => count($DayType)));
    }
	
	public function showDetail() {
        
        $DayType = CompanyPolicy::where('policy_status',1)->get();

        return view('module.settings.companyShowPolicy', ['data' => $DayType]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $dataCompany = Company::all();
        $data = CompanyPolicy::find($id);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.companyPolicy', ['data' => $data, 'company' => $dataCompany,'logged_emp_com'=>$logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, [
            'policy_heading' => 'required',
            'policy_publish_date' => 'required',
            'policy_description' => 'required', 
            'policy_status' => 'required' 
        ]);
		$logged_emp_company_id = MenuPageController::loggedUser('company_id');
		$policy_status=$request->policy_status?'1':0;

        $DayType = CompanyPolicy::find($id);
        $DayType->company_id =  $logged_emp_company_id;
        $DayType->policy_heading = $request->policy_heading;
        $DayType->policy_publish_date = $request->policy_publish_date;
        $DayType->policy_description = $request->policy_description;
        $DayType->policy_status = $policy_status;
        $DayType->save();
        return redirect()->action('CompanyPolicyController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DayType  $dayType
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = CompanyPolicy::destroy($request->id);
        return 1;
    }

}
