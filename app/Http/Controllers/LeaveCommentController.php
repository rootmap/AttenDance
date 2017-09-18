<?php

namespace App\Http\Controllers;

use App\LeaveComment;
use Illuminate\Http\Request;

use App\LeaveApplicationMaster;
use App\LeaveApplicationDetail;
use App\LeaveApplicationApprovalFlow;
use Illuminate\Support\Facades\DB;
use App\Company;
use App\LeavePolicy;
use App\EmployeeInfo;
use App\Year;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class LeaveCommentController extends Controller
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
        //
    }

    //For Commenting On Leave Applications
    public function postComment(Request $request)
    {
      $this->validate($request,[
        'comments'=>'required',
        'master_id'=>'required'
      ]);
      $logged_emp_code=MenuPageController::loggedUser('emp_code');
      $logged_emp_company_id=MenuPageController::loggedUser('company_id');
      $master_id=$request->master_id;

      //For checking in leave application approval settings and then proceed
      $leave_sup_emp = DB::table('leave_application_approval_flows')
      ->select(DB::raw('leave_application_approval_flows.sup_emp_code'))
      ->where('leave_application_approval_flows.master_id', "=", $master_id)
      ->get();

      if($leave_sup_emp[0]->sup_emp_code==$logged_emp_code){
        //For saving data in leave comments
        $comment=new LeaveComment;
        $comment->company_id=$logged_emp_company_id;
        $comment->emp_code=$logged_emp_code;
        $comment->comment=$request->comments;
        $comment->master_id=$master_id;
        $comment->save();

        //Query For Leave Comments Showing with master id and sup_emp_code
        $leaveComments=DB::table('leave_comments')
        ->leftjoin('employee_infos','leave_comments.emp_code','=','employee_infos.emp_code')
        ->selectRaw(DB::raw('leave_comments.master_id,
        leave_comments.emp_code,
        leave_comments.comment,
        DATE_FORMAT(leave_comments.created_at, "%Y-%m-%d %h:%i:%s") as created_at,
        employee_infos.image,
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as sup_emp_name'))
        ->where('leave_comments.master_id','=',$master_id)
        ->orderBy('leave_comments.id','ASC')
        ->get();

        return response()->json($leaveComments);
      } else {
        return 0;
      }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveComment  $leaveComment
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveComment $leaveComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveComment  $leaveComment
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveComment $leaveComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveComment  $leaveComment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveComment $leaveComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveComment  $leaveComment
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveComment $leaveComment)
    {
        //
    }
}
