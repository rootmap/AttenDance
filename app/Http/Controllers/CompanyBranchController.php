<?php

namespace App\Http\Controllers;

use App\CompanyBranch;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CompanyBranchController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.branch', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
            'name' => 'required',
            'company_id' => 'required',
            'is_active' => 'required',
        ]);
        
       
        $calender = new CompanyBranch;
        $calender->name = $request->name;
        $calender->company_id = $request->company_id;
        $calender->is_active = $request->is_active;
        $calender->save();
        return redirect()->action('CompanyBranchController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CompanyBranch  $companyBranch
     * @return \Illuminate\Http\Response
     */
    public function filterBranch(Request $request) {
		//$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $company_id = $request->company_id;
        $data = CompanyBranch::where('is_active','Active')->get();
        return response()->json($data);
    }

    public function show() {
         $logged_emp_company_id = MenuPageController::loggedUser('company_id');
         if(!empty($logged_emp_company_id)) {
             $branch = DB::table('company_branches')
                    ->leftjoin('companies', 'companies.id', '=', 'company_branches.company_id')
                    ->select(DB::raw('company_branches.*,
                        companies.name as company_id'))
                    ->where('company_branches.company_id', $logged_emp_company_id)
                    ->where('company_branches.is_active','Active')
                    ->get();
         }  else {
             $branch =  DB::table('company_branches')
                    ->leftjoin('companies', 'companies.id', '=', 'company_branches.company_id')
                    ->select(DB::raw('company_branches.*,
                        companies.name as company_id'))
                    ->where('company_branches.is_active','Active')
                    ->get();
         }
        
        return response()->json(array("data" => $branch, "total" => count($branch)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CompanyBranch  $companyBranch
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyBranch $companyBranch, $id) {
        $data = CompanyBranch::find($id);
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.branch', ['data' => $data, 'company' => $company,'logged_emp_com'=>$logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CompanyBranch  $companyBranch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, [
            'name' => 'required',
            'company_id' => 'required',
        ]);

        

        $branch = CompanyBranch::find($id);
        $branch->name = $request->name;
        $branch->company_id =$request->company_id;
        $branch->is_active = $request->is_active;
        $branch->save();
        return redirect()->action('CompanyBranchController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CompanyBranch  $companyBranch
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = CompanyBranch::destroy($request->id);
        return 1;
    }

}
