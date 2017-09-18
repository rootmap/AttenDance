<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;
use Illuminate\Support\Facades\DB;
use App\AttendanceRawData;
use App\AttendanceJobcard;
use App\EmployeeInfo;

class AjaxProcessController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    public function Attendance($flag = 0) {
        if ($flag == 0) {
            return 0;
        } else {
            return $this->AttendanceLogProcess();
        }
    }
	
	public function updateCompanyInfoFromEx()
	{
		$sql=DB::table('tmp_employee_upd')->orderBy('company_id')->get();
		foreach($sql as $row):
			echo "<pre>".$row->emp_code."-".$row->company_id."</pre>";
			$tab=EmployeeInfo::where('emp_code',$row->emp_code)->first();
			$tab->company_id=$row->company_id;
			$tab->save();
			
		endforeach;
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    public function AttendanceLogProcesscheck() {

        $checkProcessedData = DB::table('attendance_raw_datas')->where('is_read', '0')->count();
        // echo $checkProcessedData;
        return response()->json($checkProcessedData);
    }

    public function totalUser() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $totalUser = DB::table('employee_infos')->select('user_id')->where('company_id', $logged_emp_company_id)->groupBy('employee_infos.user_id')->get();
        //echo count($totalUser);

        return response()->json(count($totalUser));
    }

    public function totalLeave() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $totalLeave = DB::table('leave_application_masters')->where('company_id', $logged_emp_company_id)->count();
        // echo $checkProcessedData;
        return response()->json($totalLeave);
    }

    public function totalLeavePending() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $totalLeavePending = DB::table('leave_application_masters')->where('company_id', $logged_emp_company_id)->where('leave_status', 'pending')->count();
        // echo $checkProcessedData;
        return response()->json($totalLeavePending);
    }

    public function totalEmployee() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $totalEmployee = DB::table('employee_infos')->where('company_id', $logged_emp_company_id)->count();
        // echo $checkProcessedData;
        return response()->json($totalEmployee);
    }

    public function totalLog() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $totalLog = DB::table('attendance_raw_datas')->where('company_id', $logged_emp_company_id)->count();
        // echo $checkProcessedData;
        return response()->json($totalLog);
    }

    public function PushWeekendNHoliday() {

        $company_id = MenuPageController::loggedUser('company_id');


        $chkEmp = DB::table('employee_infos')
                ->select('employee_infos.id', 'employee_infos.company_id', 'employee_infos.emp_code')
                ->count();

        if ($chkEmp != 0) {

            $sqlEmp = DB::table('employee_infos')
                    ->select('employee_infos.id', 'employee_infos.company_id', 'employee_infos.emp_code')
                    ->get();



            foreach ($sqlEmp as $row):

                $sqlHW = DB::table('calendars')
                        ->leftjoin('day_types', 'calendars.day_type_id', '=', 'day_types.id')
                        ->select('calendars.date', 'day_types.day_short_code')
                        ->where('calendars.company_id', $row->company_id)
                        ->where('calendars.year',date('Y'))
                        ->where('day_types.company_id', DB::Raw('calendars.company_id'))
                        ->whereIn('day_types.day_short_code', array('W', 'H'))
                        ->get();

                foreach ($sqlHW as $HW):
                    $day_code = $HW->day_short_code;
                    $chkJobCard = AttendanceJobcard::where('company_id', $row->company_id)
                            ->where('start_date', $HW->date)
                            ->where('emp_code', $row->emp_code)
                            ->count();
                    if ($chkJobCard == 0) {
                        $tab = new AttendanceJobcard();
                        $tab->start_date = $HW->date;
                        $tab->emp_code = $row->emp_code;
                        $tab->company_id = $row->company_id;
                        $tab->admin_day_status = $HW->day_short_code;
                        $tab->user_day_status = $HW->day_short_code;
                        $tab->audit_day_status = $HW->day_short_code;
                        $tab->save();
                        //echo "Inserted <br> ";
                    } else {
                        $sqlJOBDetail = AttendanceJobcard::where('start_date', $HW->date)->where('emp_code', $row->emp_code)->where('company_id', $row->company_id)->first();
                        $tab = AttendanceJobcard::find($sqlJOBDetail->id);
                        $tab->admin_day_status = $HW->day_short_code;
                        $tab->user_day_status = $HW->day_short_code;
                        $tab->audit_day_status = $HW->day_short_code;
                        $tab->save();

                        //echo "Updated <br> ";
                    }

                endforeach;
            endforeach;
            
            echo 1;
            
        }
    }


    public function ReplaceDepartment()
    {
        $dbquery=DB::table('employee_sections')
                   ->where('company_id','14')
                   ->get();

        $count=count($dbquery);

        echo "Total 14 =".$count;      

        $array_dep=array();
        $array_sec=array();
        $i=1;
        foreach($dbquery as $row):
            echo "<pre>";
            print_r($row);
            echo $i.". is DOne";
            array_push($array_dep, $row->department_id);
            array_push($array_sec, $row->section_id);
        $i++;    
        endforeach;  

        echo DB::table('departments')->whereIn('id',$array_dep)->update(['company_id' => '14']);
        echo DB::table('sections')->whereIn('id',$array_sec)->update(['company_id' => '14']);


        $dbquery=DB::table('employee_designations')
                   ->where('company_id','14')
                   ->get();

        $count=count($dbquery);

        echo "Total 14 =".$count;      

        $array_des=array();
        $i=1;
        foreach($dbquery as $row):
            echo "<pre>";
            print_r($row);
            echo $i.". is DOne";
            array_push($array_des, $row->designation_id);
        $i++;    
        endforeach;  

        echo DB::table('designations')->whereIn('id',$array_des)->update(['company_id' => '14']);



        $dbquery=DB::table('employee_staff_grades')
                   ->where('company_id','14')
                   ->get();

        $count=count($dbquery);

        echo "Total 14 =".$count;      

        $array_stf=array();
        $i=1;
        foreach($dbquery as $row):
            echo "<pre>";
            print_r($row);
            echo $i.". is DOne";
            array_push($array_stf, $row->staff_grade_id);
        $i++;    
        endforeach;  

        echo DB::table('staff_grades')->whereIn('id',$array_stf)->update(['company_id' => '14']);


             

    }


    public function ReplacEmployeeCompany()
    {
        $dbquery=DB::table('tmp_employee')
                   ->where('company_id','2')
                   ->get();
        $count=count($dbquery);

        echo "Total 2 =".$count;
        $i=1;
        $array_emp=array();
        foreach($dbquery as $row):
            echo "<pre>";
            print_r($row);
            echo $i.". is DOne";
            array_push($array_emp, $row->emp_code);
        $i++;    
        endforeach;    

        //print_r($array_emp);

        echo DB::table('employee_infos')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('manual_job_card_entries')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('assign_employee_to_shifts')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('attendance_jobcards')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('employee_assign_roles')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('employee_departments')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('employee_designations')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('employee_sections')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('employee_staff_grades')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('leave_application_details')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('leave_application_masters')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('leave_assigned_yearly_datas')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('leave_assigned_yearly_datas')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);


        $dbquery=DB::table('tmp_employee')
                   ->where('company_id','4')
                   ->get();
        $count=count($dbquery);

        echo "Total in 4 =".$count;
        $i=1;
        $array_emp=array();
        foreach($dbquery as $row):
            echo "<pre>";
            print_r($row);
            echo $i.". is DOne";
            array_push($array_emp, $row->emp_code);
        $i++;    
        endforeach;    

        //print_r($array_emp);

        echo DB::table('employee_infos')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('manual_job_card_entries')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('assign_employee_to_shifts')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('attendance_jobcards')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('employee_assign_roles')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('employee_departments')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('employee_designations')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('employee_sections')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('employee_staff_grades')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('leave_application_details')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('leave_application_masters')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('leave_assigned_yearly_datas')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);
        DB::table('leave_assigned_yearly_datas')->whereIn('emp_code',$array_emp)->update(['company_id' => '13']);

        $dbquery=DB::table('tmp_employee')
                   ->where('company_id','5')
                   ->get();
        $count=count($dbquery);

        echo "Total 5 =".$count;
        $i=1;
        $array_emp=array();
        foreach($dbquery as $row):
            echo "<pre>";
            print_r($row);
            echo $i.". is DOne";
            array_push($array_emp, $row->emp_code);
        $i++;    
        endforeach;    

        //print_r($array_emp);

        echo DB::table('employee_infos')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('manual_job_card_entries')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('assign_employee_to_shifts')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('attendance_jobcards')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('employee_assign_roles')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('employee_departments')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('employee_designations')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('employee_sections')->whereIn('emp_code',$array_emp)->update(['company_id' => '12']);
        DB::table('employee_staff_grades')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('leave_application_details')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('leave_application_masters')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('leave_assigned_yearly_datas')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);
        DB::table('leave_assigned_yearly_datas')->whereIn('emp_code',$array_emp)->update(['company_id' => '14']);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) 
	{
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    private function AttendanceLogProcess() {
        Artisan::call('custom:command');
        return 1;
    }

}
