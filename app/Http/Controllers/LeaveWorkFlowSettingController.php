<?php

namespace App\Http\Controllers;

use App\LeaveWorkFlowSetting;
use App\EmployeeInfo;
use App\Department;
use App\Company;
use App\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LeaveWorkFlowSettingController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.leaveWorkflowSetting', ['com' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
        $this->validate($request, ['company_id' => 'required', 'company_id' => 'required', 'step_id' => 'required', 'emp_in_approval' => 'required']);


        foreach ($request->emp_in_approval as $eia):
            $leave_workflow_info = leaveWorkFlowSetting::where('leave_work_flow_settings.emp_code', $eia)->count();
            if ($leave_workflow_info != 0) {
                $leave_workflow_info = leaveWorkFlowSetting::where('leave_work_flow_settings.emp_code', $eia)->delete();
                if (isset($leave_workflow_info)) {
                    for ($i = 1; $i <= $request->step_id; $i++):
                        $index = $i;
                        $fid = "step_" . $index;
                        //echo $eia."--step---".$i."====".$request->$fid."<br>";
                        $tab = new LeaveWorkFlowSetting;
                        $tab->company_id = $request->company_id;
                        $tab->emp_code = $eia;
                        $tab->sup_emp_code = $request->$fid;
                        $tab->step = $i;
                        $tab->save();


                    endfor;
                }
            } else {
                for ($i = 1; $i <= $request->step_id; $i++):
                    $index = $i;
                    $fid = "step_" . $index;
                    //echo $eia."--step---".$i."====".$request->$fid."<br>";
                    $tab = new LeaveWorkFlowSetting;
                    $tab->company_id = $request->company_id;
                    $tab->emp_code = $eia;
                    $tab->sup_emp_code = $request->$fid;
                    $tab->step = $i;
                    $tab->save();


                endfor;
            }

            //echo $eia."<br>";
        endforeach;
        /*        print_r($request->steps);
          exit(); */




        return redirect()->action('LeaveWorkFlowSettingController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function show() {
		
        $json = DB::table('leave_work_flow_settings')
                ->leftjoin(\DB::raw('employee_infos as ei'), \DB::raw('ei.emp_code'), '=', 'leave_work_flow_settings.emp_code')
                ->leftjoin(\DB::raw('employee_infos as eii'), \DB::raw('eii.emp_code'), '=', 'leave_work_flow_settings.sup_emp_code')
                ->leftjoin(\DB::raw('companies as c'), \DB::raw('c.id'), '=', 'leave_work_flow_settings.company_id')
                ->select('leave_work_flow_settings.*', 
							\DB::raw('concat(ei.emp_code," - ",ei.first_name," ",IFNULL(ei.last_name,"")) as emp_name'), 
							\DB::raw('concat(eii.emp_code," - ",eii.first_name," ",IFNULL(eii.last_name,"")) as sup_emp_name'), 
							\DB::raw('c.name as company'))
               // ->select(\DB::raw('(SELECT concat(ei.emp_code," - ",ei.first_name," ",IFNULL(ei.last_name,"")) FROM employee_infos as ei WHERE ei.emp_code=leave_work_flow_settings.emp_code) as emp_name'))
				->orderby('leave_work_flow_settings.id','DESC')
                ->get();


		
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $json = DB::table('leave_work_flow_settings')
                ->join(\DB::raw('employee_infos as ei'), \DB::raw('ei.emp_code'), '=', 'leave_work_flow_settings.emp_code')
                ->join(\DB::raw('employee_infos as eii'), \DB::raw('eii.emp_code'), '=', 'leave_work_flow_settings.sup_emp_code')
                ->join(\DB::raw('companies as c'), \DB::raw('c.id'), '=', 'leave_work_flow_settings.company_id')
                ->select('leave_work_flow_settings.*', \DB::raw('concat(ei.emp_code," - ",ei.first_name," ",IFNULL(ei.last_name,"")) as emp_name'), \DB::raw('concat(eii.emp_code," - ",eii.first_name," ",IFNULL(eii.last_name,"")) as sup_emp_name'), \DB::raw('c.name as company'))
                ->where('leave_work_flow_settings.id', $id)
                ->orderby(\DB::raw('leave_work_flow_settings.id'), 'DESC')
                ->get();

        $data = EmployeeInfo::where('company_id', $json[0]->company_id)->get();
        return view('module.settings.leaveWorkflowSetting', ['data' => $json, 'emp_all' => $data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, ['sup_emp_code' => 'required']);

        $tab = LeaveWorkFlowSetting::find($id);
        $tab->sup_emp_code = $request->sup_emp_code;
        $tab->save();

        return redirect()->action('LeaveWorkFlowSettingController@index')->with('success', 'Workflow Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MaritalStatus  $MaritalStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = LeaveWorkFlowSetting::where('emp_code', $request->id);
        $del->delete();
        return 1;
    }

}
