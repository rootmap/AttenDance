<?php

namespace App\Http\Controllers;

use App\EmployeeWeekendDifferentCompanyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Company;
use App\EmployeeInfo;


class EmployeeWeekendDifferentCompanyPolicyController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $company = Company::all();
        $employee = EmployeeInfo::all();

        return view('module.settings.employeeWeekendPolicyAsDiffrentCompany', ['company' => $company,
            'employee' => $employee]);
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
        $this->validate($request, ['emp_code' => 'required', 'company_id' => 'required', 'effective_from' => 'required']);

        $emp_weekend_info = EmployeeWeekendDifferentCompanyPolicy::where('emp_code', $request->emp_code)
                        ->where('company_id', $request->company_id)
                        ->where('effective_from', '>=', $request->effective_from)->count();
        if (!empty($emp_weekend_info == 0)) {
            $weekend = new EmployeeWeekendDifferentCompanyPolicy;
            $weekend->company_id = $request->company_id;
            $weekend->emp_code = $request->emp_code;
            $weekend->effective_from = $request->effective_from;
            $weekend->save();
            
             return redirect()->action('EmployeeWeekendDifferentCompanyPolicyController@index')->with('success', 'Information Added Successfully');
        } else {
             return redirect()->action('EmployeeWeekendDifferentCompanyPolicyController@index')->with('error', 'Sorry! Data Already Exists.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeWeekendDifferentCompanyPolicy  $employeeWeekendDifferentCompanyPolicy
     * @return \Illuminate\Http\Response
     */
    public function show() {
        $json=DB::table('employee_weekend_different_company_policies')
          ->leftjoin('employee_infos','employee_weekend_different_company_policies.emp_code','=','employee_infos.emp_code')
          ->leftjoin('companies','employee_weekend_different_company_policies.company_id','=','companies.id')
          ->select(DB::raw('employee_weekend_different_company_policies.*,
            concat(employee_infos.emp_code,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
            companies.name as company_name'))
          ->get();
        return response()->json(array("data"=>$json,"total"=>count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeWeekendDifferentCompanyPolicy  $employeeWeekendDifferentCompanyPolicy
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeWeekendDifferentCompanyPolicy $employeeWeekendDifferentCompanyPolicy, $id) {
        $company = Company::all();
        $employee = EmployeeInfo::all();
        $data = EmployeeWeekendDifferentCompanyPolicy::find($id);
        return view('module.settings.employeeWeekendPolicyAsDiffrentCompany', ['data' => $data, 'company' => $company,
            'employee' => $employee]);
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeWeekendDifferentCompanyPolicy  $employeeWeekendDifferentCompanyPolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeWeekendDifferentCompanyPolicy $employeeWeekendDifferentCompanyPolicy, $id) {
        $this->validate($request, ['emp_code' => 'required', 'company_id' => 'required', 'effective_from' => 'required']);

        $emp_weekend_info = EmployeeWeekendDifferentCompanyPolicy::where('emp_code', $request->emp_code)
                        ->where('company_id', $request->company_id)
                        ->where('effective_from', '>=', $request->effective_from)->count();
        if (!empty($emp_weekend_info == 0)) {
            $weekend =new EmployeeWeekendDifferentCompanyPolicy;
            $weekend->company_id = $request->company_id;
            $weekend->emp_code = $request->emp_code;
            $weekend->effective_from = $request->effective_from;
            $weekend->save();
            
             return redirect()->action('EmployeeWeekendDifferentCompanyPolicyController@index')->with('success', 'Information Updated Successfully');
        } else {
			$weekend = EmployeeWeekendDifferentCompanyPolicy::find($id);
            $weekend->company_id = $request->company_id;
            $weekend->emp_code = $request->emp_code;
            $weekend->effective_from = $request->effective_from;
            $weekend->save();
             return redirect()->action('EmployeeWeekendDifferentCompanyPolicyController@index')->with('success', 'Congrates! Data Updated Successfully.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmployeeWeekendDifferentCompanyPolicy  $employeeWeekendDifferentCompanyPolicy
     * @return \Illuminate\Http\Response
     */
    
    public function destroy(Request $request)
    {
        $del=EmployeeWeekendDifferentCompanyPolicy::destroy($request->id);
        return 1;    
    }

}
