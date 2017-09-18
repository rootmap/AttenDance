<?php

namespace App\Http\Controllers;

use App\LeaveApprovalMethod;
use Illuminate\Http\Request;

class LeaveApprovalMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $leave_approval_method = LeaveApprovalMethod::where('company_id',$logged_emp_company_id)->first();

        return view('module.settings.leaveApprovalMethod', ['app_method' => $leave_approval_method, 'logged_emp_com' => $logged_emp_company_id]);
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
      $this->validate($request, [
          'approval_method' => 'required'
        ]);

      $logged_emp_company_id = MenuPageController::loggedUser('company_id');

      if (empty($request->approval_method)) {
          $approval_method = 0;
      } else {
          $approval_method = $request->approval_method;
      }

      if (empty($request->ends_at_first_rejection)) {
          $ends_at_first_rejection = 0;
      } else {
          $ends_at_first_rejection = $request->ends_at_first_rejection;
      }

      $chkCompanyExists = LeaveApprovalMethod::where('company_id',$logged_emp_company_id)->first();

      $countExist = count($chkCompanyExists);

      if($countExist==1){
        $data_id = $chkCompanyExists->id;

        $tab = LeaveApprovalMethod::find($data_id);
        $tab->company_id = $logged_emp_company_id;
        $tab->approval_method = $approval_method;
        $tab->ends_at_first_rejection = $ends_at_first_rejection;
        $tab->save();

        return redirect()->action('LeaveApprovalMethodController@index')->with('success', 'Settings Updated Successfully');
      } else {
        $tab = new LeaveApprovalMethod;
        $tab->company_id = $logged_emp_company_id;
        $tab->approval_method = $approval_method;
        $tab->ends_at_first_rejection = $ends_at_first_rejection;
        $tab->save();

        return redirect()->action('LeaveApprovalMethodController@index')->with('success', 'Settings Added Successfully');
      }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveApprovalMethod  $leaveApprovalMethod
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveApprovalMethod $leaveApprovalMethod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveApprovalMethod  $leaveApprovalMethod
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveApprovalMethod $leaveApprovalMethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveApprovalMethod  $leaveApprovalMethod
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveApprovalMethod $leaveApprovalMethod, $id)
    {
      $this->validate($request, [
          'approval_method' => 'required'
        ]);

      $logged_emp_company_id = MenuPageController::loggedUser('company_id');

      if (empty($request->approval_method)) {
          $approval_method = 0;
      } else {
          $approval_method = $request->approval_method;
      }

      if (empty($request->ends_at_first_rejection)) {
          $ends_at_first_rejection = 0;
      } else {
          $ends_at_first_rejection = $request->ends_at_first_rejection;
      }



      $tab = LeaveApprovalMethod::find($id);
      $tab->company_id = $logged_emp_company_id;
      $tab->approval_method = $approval_method;
      $tab->ends_at_first_rejection = $ends_at_first_rejection;
      $tab->save();

      return redirect()->action('LeaveApprovalMethodController@index')->with('success', 'Settings Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveApprovalMethod  $leaveApprovalMethod
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveApprovalMethod $leaveApprovalMethod)
    {
        //
    }
}
