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
use App\User;
use App\Year;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

//For PhpMailer
use PHPMailerAutoload;
use PHPMailer;

class UserPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }



    /**
     * Change password.
     *
     * @return \Illuminate\Http\Response  forgotPass
     */
    public function changePass()
    {
        return view('module.settings.changepassword');
    }


    /**
     * Forgot password.
     *
     * @return \Illuminate\Http\Response  forgotPass
     */
    public function forgotPass()
    {
        return view('module.settings.forgotpassword');
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
    public function update(Request $request)
    {
      $this->validate($request, [
          'new_pass' => 'required',
          'confirm_pass' => 'required'
        ]);
      if(empty($request->user_id) || $request->user_id==0){
        $user_id = MenuPageController::loggedUser('user_id');
      } else {
        $user_id = $request->user_id;
      }



      $new_pass = $request->new_pass;
      $confirm_pass = $request->confirm_pass;

      if($confirm_pass==$new_pass){
        $tab = User::find($user_id);
        $tab->password = bcrypt($confirm_pass);
        $tab->save();

        return redirect()->action('UserPasswordController@changePass')->with('success', 'Password Changed Successfully');
      } else {
        return redirect()->action('UserPasswordController@changePass')->with('error','Sorry! Password Mismatch. Please Try Again.');
      }
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
