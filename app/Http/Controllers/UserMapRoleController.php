<?php

namespace App\Http\Controllers;

use App\UserMapRole;
use App\Company;
use App\SystemAccessRole;
use App\SystemModule;
use App\SystemSubModule;
use App\SystemModulePage;
use Illuminate\Http\Request;

class UserMapRoleController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $logged_emp_com = MenuPageController::loggedUser('company_id');
        if (isset($logged_emp_com)) {
            $systemAccessRole = SystemAccessRole::where('company_id', $logged_emp_com)->get();
        } else {
            $systemAccessRole = SystemAccessRole::all();
        }

        $company = Company::where('is_active', '1')->get();
        return view('module.settings.userrolemap', [
            'company' => $company,
            'logged_emp_com' => $logged_emp_com,
            'SystemAccessRole' => $systemAccessRole
        ]);
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

        $this->validate($request, ['system_access_role_id' => 'required', 'company_id' => 'required']);

        if ($request->system_access_role_id == "99999") {
            $this->validate($request, ['user_id' => 'required']);
            $prefil = UserMapRole::where('system_access_role_id', $request->system_access_role_id)
                    ->where('company_id', $request->company_id)
                    ->where('user_id', $request->user_id)
                    ->delete();
        } else {
            $prefil = UserMapRole::where('system_access_role_id', $request->system_access_role_id)
                    ->where('company_id', $request->company_id)
                    ->delete();
        }

        foreach ($request->page_id as $index => $page):
            $page_delete = 0;
            if (isset($request->page_delete[$index])) {
                $page_delete = $request->page_delete[$index] ? 1 : 0;
            }

            $page_update = 0;
            if (isset($request->page_update[$index])) {
                $page_update = $request->page_update[$index] ? 1 : 0;
            }

            $page_create = 0;
            if (isset($request->page_create[$index])) {
                $page_create = $request->page_create[$index] ? 1 : 0;
            }

            $page_view_list = 0;
            if (isset($request->page_view_list[$index])) {
                $page_view_list = $request->page_view_list[$index] ? 1 : 0;
            }


            $UpperMen = SystemModulePage::find($page);
            $tab = new UserMapRole();
            $tab->company_id = $request->company_id;
            $tab->system_module_page_id = $page;
            $tab->system_access_role_id = $request->system_access_role_id;
            $tab->system_module_id = $UpperMen->system_module_id;
            $tab->system_sub_module_id = $UpperMen->system_sub_module_id;
            $tab->create_permission = $page_create;
            $tab->edit_permission = $page_update;
            $tab->view_list_permission = $page_view_list;
            $tab->delete_permission = $page_delete;
            $tab->save();

        endforeach;

        return redirect(url('Settings/UserRoleMap'))->with('success', 'Information Saved Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UserMapRole  $userMapRole
     * @return \Illuminate\Http\Response
     */
    public function show(UserMapRole $userMapRole) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UserMapRole  $userMapRole
     * @return \Illuminate\Http\Response
     */
    public function edit(UserMapRole $userMapRole) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserMapRole  $userMapRole
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserMapRole $userMapRole) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserMapRole  $userMapRole
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserMapRole $userMapRole) {
        //
    }

}
