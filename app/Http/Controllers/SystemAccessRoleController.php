<?php

namespace App\Http\Controllers;

use App\SystemAccessRole;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemAccessRoleController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.role', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
            'role_title' => 'required', 
            'is_active'=>'required'
            ]);
        $company_id = MenuPageController::loggedUser('company_id');
        $chk=SystemAccessRole::where('company_id',$company_id)
                             ->where('name',$request->role_title)
                             ->count();

        if($chk==0)
        {
            $role = new SystemAccessRole;
            $role->name = $request->role_title;
            $role->company_id = $company_id;
            $role->is_active = $request->is_active;
            $role->save();
            return redirect()->action('SystemAccessRoleController@index')->with('success', 'Information Added Successfully');
        }
        else
        {
            return redirect()->action('SystemAccessRoleController@index')->with('warning', 'Role ('.$request->role_title.') is already exists.');
        }                     

        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SystemAccessRole  $systemAccessRole
     * @return \Illuminate\Http\Response
     */
    public function show(SystemAccessRole $systemAccessRole) {
//        $role = SystemAccessRole::all();

        ///$logged_emp_company_id = MenuPageController::loggedUser('company_id');
        /*if (!empty($logged_emp_company_id)) {
            $role = DB::table('system_access_roles')
                    ->join('companies', 'companies.id', '=', 'system_access_roles.company_id')
                    ->select(DB::raw('system_access_roles.*,
                        companies.name as company_id'))
                    ->where('system_access_roles.company_id', $logged_emp_company_id)
                    ->groupBy('system_access_roles.id')
                    ->get();
        } else {*/
            $role = DB::table('system_access_roles')
                    ->join('companies', 'companies.id', '=', 'system_access_roles.company_id')
                    ->select(DB::raw('system_access_roles.*,
                        companies.name as company_id'))
                    ->groupBy('system_access_roles.id')
                    ->get();
        //}
        return response()->json(array("data" => $role, "total" => count($role)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SystemAccessRole  $systemAccessRole
     * @return \Illuminate\Http\Response
     */
    public function edit(SystemAccessRole $SystemAccessRole, $id) {
        $data = SystemAccessRole::find($id);
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.role', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StaffGrade  $staffGrade
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, ['role_title' => 'required', 'company_id' => 'required','is_active'=>'required']);
        $role = SystemAccessRole::find($id);
        $role->name = $request->role_title;
        $role->company_id = $request->company_id;
        $role->is_active = $request->is_active;
        $role->save();
        return redirect()->action('SystemAccessRoleController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SystemAccessRole  $systemAccessRole
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = SystemAccessRole::destroy($request->id);
        return 1;
    }

}
