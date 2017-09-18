<?php

namespace App\Http\Controllers;

use App\LeaveApplicationMaster;
use App\LeaveApplicationDetail;
use App\LeaveApplicationApprovalFlow;
use App\LeaveComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Company;
use App\LeavePolicy;
use App\Employeeinfo;
use App\Year;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

//For PhpMailer
use PHPMailerAutoload;
use PHPMailer;

class testController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('module.settings.jobcard');
    }

    public function index2()
    {
        return view('module.settings.test');
    }


    /**
     * Change password.
     *
     * @return \Illuminate\Http\Response  forgotPass
     */
    public function changePass()
    {
        return view('module.Settings.changepassword');
    }


    /**
     * Forgot password.
     *
     * @return \Illuminate\Http\Response  forgotPass
     */
    public function forgotPass()
    {
        return view('module.Settings.forgotpassword');
    }

    /**
     * Forgot password.
     *
     * @return \Illuminate\Http\Response  forgotPass
     */
    public function employeesendmessage()
    {
        return view('module.Settings.employee_send_message');
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

    }

    //For Approving Leave Application
    public function approveLeave(Request $request)
    {
      //
    }

    //For Rejecting Leave Application
    public function rejectLeave(Request $request)
    {
      //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */

    public function show()
    {

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveApplicationApproval $leaveApplicationApproval)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveApplicationApproval $leaveApplicationApproval)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveApplicationApproval $leaveApplicationApproval)
    {
        //
    }
}
