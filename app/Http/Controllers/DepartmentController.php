<?php

namespace App\Http\Controllers;

use App\Department;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if(!empty($logged_emp_company_id))
        {
            $company = Company::find($logged_emp_company_id);
        }
        else
        {
             $company = Company::where('is_active','1')->get();
        }
        


        return view('module.settings.department', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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

       

        $tab = new Department;
        $tab->name = $request->name;
        $tab->company_id = $request->company_id;

        $tab->is_active = $request->is_active;
        $tab->save();

        return redirect()->action('DepartmentController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function show(Department $department) {


        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        /*if(!empty($logged_emp_company_id)) {
//             $json = Department::where('company_id',$logged_emp_company_id)->get();
            $json = DB::table('departments')
                    ->leftjoin('companies', 'companies.id', '=', 'departments.company_id')
                    ->select(DB::raw('departments.*,
                        companies.name as company_id'))
                    ->where('departments.company_id', $logged_emp_company_id)
                    ->where('departments.is_active','Active')
                    ->get();
        } else {*/
            //$json = Department::all();
            $json = DB::table('departments')
                    ->leftjoin('companies', 'companies.id', '=', 'departments.company_id')
                    ->select(DB::raw('departments.*,
                        companies.name as company_id'))
                    ->where('departments.is_active','Active')
                    ->get();
        //}

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    //filter
    public function filterDepartment(Request $request) {
        $company_id = $request->company_id;
        //$data = Department::where('company_id', $company_id)->where('is_active','Active')->get();
        $data = Department::where('is_active','Active')->get();
        
		return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department, $id) {
        $data = Department::find($id);
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.department', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, [
            'company_id' => 'required',
            'name' => 'required',
            'is_active' => 'required',
        ]);

        $tab = Department::find($id);
        $tab->name = $request->name;
        $tab->company_id = $request->company_id;

        $tab->is_active = $request->is_active;
        $tab->save();

        return redirect()->action('DepartmentController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = Department::destroy($request->id);
        return 1;
    }

}
