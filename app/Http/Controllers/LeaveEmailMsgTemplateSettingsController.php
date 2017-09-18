<?php

namespace App\Http\Controllers;

use App\LeaveEmailMsgTemplateSettings;
use App\LeaveEmailTemplateType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveEmailMsgTemplateSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $template_type=LeaveEmailTemplateType::all();
        return view('module.Leave.leaveEmailTemplateSettings',['template_type'=>$template_type]);
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
      $this->validate($request,[
        'template_type_id'=>'required',
        'message_template'=>'required'
      ]);

      $logged_emp_company=MenuPageController::loggedUser('company_id');


      $msgtemp=new LeaveEmailMsgTemplateSettings;
      $msgtemp->company_id=$logged_emp_company;
      $msgtemp->template_type_id=$request->template_type_id;
      $msgtemp->msg_template=$request->message_template;
      $msgtemp->save();

      return redirect()->action('LeaveEmailMsgTemplateSettingsController@index')->with('success','Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveEmailMsgTemplateSettings  $leaveEmailMsgTemplateSettings
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveEmailMsgTemplateSettings $leaveEmailMsgTemplateSettings)
    {
      $logged_emp_company_id = MenuPageController::loggedUser('company_id');
      $json = DB::table('leave_email_msg_template_settings')
                  ->leftjoin('leave_email_template_types','leave_email_template_types.id','=','leave_email_msg_template_settings.template_type_id')
                  ->select(DB::raw('leave_email_msg_template_settings.id,
                  leave_email_template_types.template_type,
                  leave_email_msg_template_settings.msg_template,
                  leave_email_msg_template_settings.updated_at'))
                  ->get();

      return response()->json(array("data" => $json, "total" => count($json)));
    }

    //For Ajax Data loading with template_type_id
    public function loadMsgTemplate(Request $request, LeaveEmailMsgTemplateSettings $leaveEmailMsgTemplateSettings)
    {
      $logged_emp_company_id = MenuPageController::loggedUser('company_id');
      $template_type_id = $request->template_type_id;

      $data = DB::table('leave_email_msg_template_settings')
                  ->select(DB::raw('leave_email_msg_template_settings.msg_template'))
                  ->where('leave_email_msg_template_settings.company_id','=',$logged_emp_company_id)
                  ->where('leave_email_msg_template_settings.template_type_id','=',$template_type_id)
                  ->first();

      return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveEmailMsgTemplateSettings  $leaveEmailMsgTemplateSettings
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveEmailMsgTemplateSettings $leaveEmailMsgTemplateSettings, $id)
    {
      $template_type=LeaveEmailTemplateType::all();
      $data = LeaveEmailMsgTemplateSettings::find($id);
      return view('module.Leave.leaveEmailTemplateSettings',['data' => $data, 'template_type'=>$template_type]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveEmailMsgTemplateSettings  $leaveEmailMsgTemplateSettings
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveEmailMsgTemplateSettings $leaveEmailMsgTemplateSettings, $id)
    {
      $this->validate($request,[
        'template_type_id'=>'required',
        'message_template'=>'required'
      ]);

      $logged_emp_company=MenuPageController::loggedUser('company_id');


      $msgtemp=LeaveEmailMsgTemplateSettings::find($id);
      $msgtemp->company_id=$logged_emp_company;
      $msgtemp->template_type_id=$request->template_type_id;
      $msgtemp->msg_template=$request->message_template;
      $msgtemp->save();

      return redirect()->action('LeaveEmailMsgTemplateSettingsController@index')->with('success','Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveEmailMsgTemplateSettings  $leaveEmailMsgTemplateSettings
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, LeaveEmailMsgTemplateSettings $leaveEmailMsgTemplateSettings)
    {
      $del = LeaveEmailMsgTemplateSettings::destroy($request->id);
      return 1;
    }
}
