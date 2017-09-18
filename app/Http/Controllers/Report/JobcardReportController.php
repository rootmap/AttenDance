<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\AssignEmployeeToShift;
use App\AttendanceJobcardPolicy;
use APP\AttendancePolicy;
use APP\EmployeeInfo;
use App\Company;
use App\AttendanceJobcard;
use App\Shift;
use App\Calendar;
use App\ManualJobCardEntry;
use App\LeaveApplicationMaster;
use App\LeavePolicy;
use App\LeaveAssignedYearlyData;
use App\WeekendOTPolicy;
use App\EmployeeStaffGrade;

class JobcardReportController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ReportdayStatus() {
//$alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
//$dayStatus = '';
//        if (!empty($alt_company_id)) {
//            $leave = \DB::table('leave_policies')
//                    ->select(\DB::raw("leave_short_code as day_short_code"))
//                    ->where('company_id', $alt_company_id);
//
//            $day_short_code = \DB::table('day_types')
//                    ->select(\DB::raw("day_short_code"))
//                    ->where('company_id', $alt_company_id);
//
//            $dayStatus = $leave->union($day_short_code)->get();
//        } else {
//            $leave = \DB::table('leave_policies')
//                    ->select(\DB::raw("leave_short_code as day_short_code"));
//
//            $day_short_code = \DB::table('day_types')
//                    ->select(\DB::raw("day_short_code"));
//
//            $dayStatus = $leave->union($day_short_code)->get();
//        }
//       return view('module.settings.jobcardUser');
    }

    public function index() {
//
    }

    public function Adminindex() {
        return view('module.settings.jobcardAdmin');
    }
	
	public function LateINindex() {
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        return view('module.settings.jobcardLateIN',['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    public function CompanyAttendanceindex() {

        $RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
        
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        return view('module.settings.companywiseAttendanceLog',['company'=>$company]);
    }

    public function Auditindex() {
        return view('module.settings.jobcardAudit');
    }

    public function Userindex() {
        return view('module.settings.jobcardUser');
    }

    private function LeaveDayManuallyCheck($company_id = 0, $date = '0000-00-00', $emp_code = '0') {
        $chk = LeaveApplicationMaster::where('company_id', $company_id)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->where('emp_code', $emp_code)
				->where('leave_status','Approved')
                ->count();
				
				
				
        if ($chk == 0) {
            return "A";
        } else {
            $sql = DB::table('leave_application_masters')
                    ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                    //->where('leave_application_masters.company_id', $company_id)
                    ->where('leave_application_masters.start_date', '<=', $date)
                    ->where('leave_application_masters.end_date', '>=', $date)
                    ->where('leave_application_masters.emp_code', $emp_code)
					->where('leave_application_masters.leave_status','Approved')
                    ->select('leave_policies.leave_short_code','leave_application_masters.total_days_applied')
                    ->groupby('leave_application_masters.id')
                    ->first();
			
			
			
			$chkjobcard=AttendanceJobcard::where('emp_code',$emp_code)->where('start_date',$date)->count();
			if($chkjobcard==0)
			{
				if($sql->total_days_applied=="0.50")
				{
					return "A";
				}
				elseif($sql->total_days_applied=="1")
				{
					return $sql->leave_short_code;
				}
				else
				{
					return $sql->leave_short_code;
				}
			}
			else
			{
				$jobcard=AttendanceJobcard::where('emp_code',$emp_code)->where('start_date',$date)->first();
				if($sql->total_days_applied=="0.50" && (!empty($jobcard->admin_in_time) || !empty($jobcard->admin_out_time)))
				{
					return "P";
				}
				elseif($sql->total_days_applied=="1")
				{
					return $sql->leave_short_code;
				}
				else
				{
					return "A";
				}
			}
        }
		
		
		
    }
	
	private function HalfDayLeaveCheck($date,$company_id,$emp_code)
	{
		$chk = LeaveApplicationMaster::where('company_id', $company_id)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->where('emp_code', $emp_code)
				->where('leave_status','Approved')
				->where('total_days_applied','0.50')
                ->count();
				
        if ($chk == 0) {
            return 0;
        } else {
            return 1;
        }
	}
	

    private function ManualJobCardEntryCheck($param = 0, $date = '0000-00-00', $emp_code = '0', $day_status = '', $company_id = '') {

		
		

        $chk_tab_log_date = DB::table('manual_job_card_entries')
                ->where('emp_code', $emp_code)
                ->where('date', $date)
                ->count();
			
//print_r($tab_log_date);
//$jobcard_day_status = "A";
        if ($chk_tab_log_date != 0) {

            // echo $day_status;
            //exit();
//Manual Job Card Entries if primarily found Start
            $chkDayFromCalender = DB::table('calendars')
                    ->leftjoin('day_types', 'calendars.day_type_id', '=', 'day_types.id')
                    ->where('calendars.date', $date)
                    ->where('calendars.company_id', $company_id)
                    ->where('day_types.company_id', $company_id)
                    ->whereNotIn('day_types.day_short_code', ['P'])
                    ->count();

            if ($chkDayFromCalender == 0) {

					

//                $tab_log_dates = DB::table('manual_job_card_entries')
//                ->where('emp_code', $emp_code)
//                ->where('date', $date)
//                ->first();
//                $jobcard_day_status = $tab_log_dates->day_type;
//                $chkDate = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $date)->count();
//                if (!empty($chkDate)) {
//                    $tab = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $date)
//                    ->update(['admin_day_status' => $jobcard_day_status]);
//                } else {
//                    $tab = new AttendanceJobcard();
//                    $tab->emp_code = $tab_log_dates->emp_code;
//                    $tab->company_id = $tab_log_dates->company_id;
//                    $tab->start_date = $tab_log_dates->date;
//                    $tab->admin_day_status = $jobcard_day_status;
//                    $tab->user_day_status = $jobcard_day_status;
//                    $tab->audit_day_status = $jobcard_day_status;
//                    $tab->save();
//                }
                $jobcard_day_status_get_leave = $this->LeaveDayManuallyCheck($company_id, $date, $emp_code);
				
                if ($jobcard_day_status_get_leave != 'A') {
                    $jobcard_day_status_get = $jobcard_day_status_get_leave;
                } else {
                    $jobcard_day_status_get = $param;
                }

//only absent insert and jobcard update start
                $jobcard_day_status = $this->ManualPNAttenJobcardP($company_id, $emp_code, $date, $jobcard_day_status_get);
            } else {
                $DayFromCalender = DB::table('calendars')
                        ->leftjoin('day_types', 'calendars.day_type_id', '=', 'day_types.id')
                        ->select('day_types.day_short_code')
                        ->where('calendars.date', $date)
                        ->where('calendars.company_id', $company_id)
                        ->where('day_types.company_id', $company_id)
                        ->first();

//check leave status

                $jobcard_day_status_get = $DayFromCalender->day_short_code;



//only absent insert and jobcard update start
                $jobcard_day_status = $this->ManualPNAttenJobcardP($company_id, $emp_code, $date, $jobcard_day_status_get);
//only absent insert and jobcard update end
//check leave status done
            }

//Manual Job Card Entries if primarily found End
        } else {



            $chkDayFromCalender = DB::table('calendars')
                    ->leftjoin('day_types', 'calendars.day_type_id', '=', 'day_types.id')
                    ->where('calendars.date', $date)
                    ->where('calendars.company_id', $company_id)
                    ->where('day_types.company_id', $company_id)
                    ->whereNotIn('day_types.day_short_code', ['P'])
                    ->count();
            if ($chkDayFromCalender == 0) {
//check leave status
                $jobcard_day_status_get = $this->LeaveDayManuallyCheck($company_id, $date, $emp_code);
//only absent insert and jobcard update start
                $jobcard_day_status = $this->ManualPNAttenJobcardP($company_id, $emp_code, $date, $jobcard_day_status_get);
//only absent insert and jobcard update end
//check leave status done
            } else {
                $DayFromCalender = DB::table('calendars')
                        ->leftjoin('day_types', 'calendars.day_type_id', '=', 'day_types.id')
                        ->select('day_types.day_short_code')
                        ->where('calendars.date', $date)
                        ->where('calendars.company_id', $company_id)
                        ->where('day_types.company_id', $company_id)
                        ->first();

//check leave status
                $jobcard_day_status_get = $DayFromCalender->day_short_code;
//only absent insert and jobcard update start
                $jobcard_day_status = $this->ManualPNAttenJobcardP($company_id, $emp_code, $date, $jobcard_day_status_get);
//only absent insert and jobcard update end
//check leave status done
            }
        }


        return $jobcard_day_status;
    }

    public function ManualPNAttenJobcardP($company_id = 0, $emp_code = 0, $date = '0000-00-00', $jobcard_day_status = 'A') {
        $chk_tab_log_date = DB::table('manual_job_card_entries')
                ->where('emp_code', $emp_code)
                ->where('date', $date)
                ->count();

        if ($chk_tab_log_date == 0) {
			if(empty($jobcard_day_status))
			{
				$jobcard_day_status='A';
			}
            $tab = new ManualJobCardEntry();
            $tab->company_id = $company_id;
            $tab->emp_code = $emp_code;
            $tab->day_type = $jobcard_day_status;
            $tab->date = $date;
            $tab->save();
        } else {
            $tab_log_date = DB::table('manual_job_card_entries')
                    ->where('emp_code', $emp_code)
                    ->where('date', $date)
                    ->first();

            $find_id = $tab_log_date->id;

            $tab = ManualJobCardEntry::find($find_id);
            $tab->emp_code = $emp_code;
            $tab->day_type = $jobcard_day_status;
            $tab->date = $date;
            $tab->save();
        }

        $tab_log_dates = DB::table('manual_job_card_entries')
                ->where('emp_code', $emp_code)
                ->where('date', $date)
                ->first();




        $chkDate = AttendanceJobcard::where('emp_code', $emp_code)
                ->where('start_date', $date)
                ->count();
        if (!empty($chkDate)) {
            $jobinfo = AttendanceJobcard::where('emp_code', $emp_code)
                    ->where('start_date', $date)
                    ->first();

            if ($jobinfo->admin_in_time != '00:00:00') {
                if ((!empty($jobinfo->admin_in_time) || !empty($jobinfo->admin_out_time)) && $jobcard_day_status == "A") {
                    $jobcard_day_status = "P";
                }
            }

            $tab = AttendanceJobcard::where('emp_code', $emp_code)
                    ->where('start_date', $date)
                    ->update(['admin_day_status' => $jobcard_day_status]);
        } else {

            $tab = new AttendanceJobcard();
            $tab->emp_code = $tab_log_dates->emp_code;
            $tab->company_id = $tab_log_dates->company_id;
            $tab->start_date = $tab_log_dates->date;
            $tab->admin_day_status = $jobcard_day_status;
            $tab->user_day_status = $jobcard_day_status;
            $tab->audit_day_status = $jobcard_day_status;
            $tab->save();
        }

        return $jobcard_day_status;
    }

    private function CalCulateTtalInTimeBetween($in_time = '00:00:00', $out_time = '00:00:00') {
        $Admin_dteStart = new \DateTime($in_time);
        $Admin_dteEnd = new \DateTime($out_time);
        $Admin_dteDiff = $Admin_dteStart->diff($Admin_dteEnd);
        $Admin_Total_WTime = $Admin_dteDiff->format("%H:%I:%S");
//return $Admin_Total_WTime;

        if ($Admin_Total_WTime != "00:00:00") {
            $intMIn = $Admin_dteDiff->format("%I");
            $FloorMake = floor($intMIn / 15);
            $hourMake = $Admin_dteDiff->format("%H");
            if ($FloorMake == 0) {
                $Admin_Total_WTime = $hourMake . ":00:00";
                return $Admin_Total_WTime;
//exit();
            } else {

//echo $intMIn;
                $minMake = 15 * $FloorMake;
                $finalMin = str_pad($minMake, 2, '0', STR_PAD_LEFT);
                $Admin_Total_WTime = $hourMake . ":" . $finalMin . ":00";
                return $Admin_Total_WTime;
            }
        } else {
            return $Admin_Total_WTime;
        }
    }
	
	private function CalCulateTtalInTimeBetweenRaw($in_time = '00:00:00', $out_time = '00:00:00') {
        $Admin_dteStart = new \DateTime($in_time);
        $Admin_dteEnd = new \DateTime($out_time);
        $Admin_dteDiff = $Admin_dteStart->diff($Admin_dteEnd);
        $Admin_Total_WTime = $Admin_dteDiff->format("%H:%I:%S");
		
		return $Admin_Total_WTime;

    }

    private function WeekendOT($jobcard_id = 0, $jobcard_day_status = "A", $jobcard_total_time = '00:00:00', $jobcard_out_time = '00:00:00', $company_id = 0) {


        if (($jobcard_day_status == "W" || $jobcard_day_status == "H") && !empty($jobcard_out_time)) {


            if ($jobcard_out_time != '00:00:00') {

				

                $chkWHP = WeekendOTPolicy::count();
                if ($chkWHP == 0) {

                    $tab = AttendanceJobcard::find($jobcard_id);

                    $formatedTime = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);
                    $tab->admin_total_ot = $formatedTime;
                    $tab->user_total_ot = $formatedTime;
                    $tab->audit_total_ot = $formatedTime;
                    $tab->save();
                } else {



                    $sqlWHP = WeekendOTPolicy::first();
                    if ($sqlWHP->is_ot_count_as_total_working_hour == 1) {

                        $tab = AttendanceJobcard::find($jobcard_id);
                        $formatedTime = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);

                        $tab->admin_total_ot = $formatedTime;
                        $tab->user_total_ot = $formatedTime;
                        $tab->audit_total_ot = $formatedTime;
                        $tab->save();
                    } elseif ($sqlWHP->is_ot_will_start_after_fix_hour == 1) {



                        if (!empty($sqlWHP->hour_after)) {
							

                            $tab = AttendanceJobcard::find($jobcard_id);
                            $formatedTimeTotal = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);
							
							if($formatedTimeTotal=='00:00:00' || empty($formatedTimeTotal))
							{
								$formatedTime='00:00:00';
							}
							else
							{
								$formatedTime = $this->CalCulateTtalInTimeBetween($sqlWHP->hour_after, $formatedTimeTotal);
							}							
							
                            $tab->admin_total_ot = $formatedTime;
                            $tab->user_total_ot = $formatedTime;
                            $tab->audit_total_ot = $formatedTime;
                            $tab->save();
							
							//echo $formatedTime;
							
                        } else {
                            $tab = AttendanceJobcard::find($jobcard_id);
                            $formatedTime = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);
                            $tab->admin_total_ot = $formatedTime;
                            $tab->user_total_ot = $formatedTime;
                            $tab->audit_total_ot = $formatedTime;
                            $tab->save();
                        }
                    }
                }
            }
        }

        return 1;
    }

	public function FormatHHMM($time = '00:00:00') {
        $time = date('H:i', strtotime($time));
        return $time;
    }
	
	private function MakeTimeDifferenceNightShift($auto_start_date, $shift_start_time, $auto_end_date, $shift_end_time) {
        $make_before_time_raw = $auto_start_date . ' ' . $shift_start_time;
        $make_before_time = Carbon::parse($make_before_time_raw);
        $make_before_time->toDateTimeString();
        $make_before_time = $make_before_time->format('Y-m-d H:i:s');


        $pun_log_dt = Carbon::parse($auto_end_date . ' ' . $shift_end_time);
        $pun_log_dt->toDateTimeString();
        $pun_log_dt->addDay();
        $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');

        $calculated_time = $this->CalCulateTtalInTimeBetween($make_before_time, $pun_log_dt);
        $format_calculated_time = Carbon::parse($calculated_time);
        $format_calculated_time->toDateTimeString();
        $fct = $format_calculated_time->format('H:i:s');

        return $fct;
    }
	
	private function WeekendOTWithGeneralShift($in_time='00:00:00',$out_time='00:00:00',$jobcard_id = 0, $jobcard_day_status = "A", $jobcard_total_time = '00:00:00', $jobcard_out_time = '00:00:00', $company_id = 0) {


        if (($jobcard_day_status == "W" || $jobcard_day_status == "H") && !empty($jobcard_out_time)) {


            if ($jobcard_out_time != '00:00:00') {

				

                $chkWHP = WeekendOTPolicy::count();
                if ($chkWHP == 0) {

                    $tab = AttendanceJobcard::find($jobcard_id);

                    $formatedTime = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);
                    $tab->admin_total_ot = $formatedTime;
                    $tab->user_total_ot = $formatedTime;
                    $tab->audit_total_ot = $formatedTime;
                    $tab->save();
                } else {



                    $sqlWHP = WeekendOTPolicy::first();
                    if ($sqlWHP->is_ot_count_as_total_working_hour == 1) {

                        $tab = AttendanceJobcard::find($jobcard_id);
                        $formatedTime = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);

                        $tab->admin_total_ot = $formatedTime;
                        $tab->user_total_ot = $formatedTime;
                        $tab->audit_total_ot = $formatedTime;
                        $tab->save();
                    } elseif ($sqlWHP->is_ot_will_start_after_fix_hour == 1) {



                        if (!empty($sqlWHP->hour_after)) {
							

                            $tab = AttendanceJobcard::find($jobcard_id);
                            $formatedTimeTotal = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);
							
							if($formatedTimeTotal=='00:00:00' || empty($formatedTimeTotal))
							{
								$formatedTime='00:00:00';
							}
							else
							{
								$formatedTime = $this->CalCulateTtalInTimeBetween($sqlWHP->hour_after, $formatedTimeTotal);
							}							
							
                            $tab->admin_total_ot = $formatedTime;
                            $tab->user_total_ot = $formatedTime;
                            $tab->audit_total_ot = $formatedTime;
                            $tab->save();
							
							//echo $formatedTime;
							
                        } else {
                            $tab = AttendanceJobcard::find($jobcard_id);
                            $formatedTime = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);
                            $tab->admin_total_ot = $formatedTime;
                            $tab->user_total_ot = $formatedTime;
                            $tab->audit_total_ot = $formatedTime;
                            $tab->save();
                        }
                    }
                }
            }
        }
		else
		{
			$Jobcard=AttendanceJobcard::find($jobcard_id);
			$emp_code=$Jobcard->emp_code;
			$start_date=$Jobcard->start_date;
			$end_date=$Jobcard->end_date;
			
			
			$chkshift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
							->where('start_date','<=',$start_date)
							->where('end_date','>=',$start_date)
							->count();
			
			$log_date=$start_date;
							
			if($chkshift_info==0)
			{
				$sqlgetDefShift=Shift::where('name','Deafult Shift')->first();
				$defShiftID=$sqlgetDefShift->id;
				
				$pullToShift=new AssignEmployeeToShift;
				$pullToShift->emp_code=$emp_code;
				$pullToShift->start_date=$log_date;
				$pullToShift->end_date=$log_date;
				$pullToShift->company_id=$company_id;
				$pullToShift->shift_id=$defShiftID;
				$pullToShift->save();
				
				
				$chkshift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
				->where('start_date', '<=', $log_date)
				->where('end_date', '>=', $log_date)
				->count();
			
			}	

			$shift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
						->where('start_date', '<=', $log_date)
						->where('end_date', '>=', $log_date)
						->orderby('id', 'DESC')
						->get();
			
			$shift_id = $shift_info[0]->shift_id;
			$shift_data = Shift::find($shift_id);
			$shift_type_night = $shift_data->is_night_shift;
			
			
			
			if($shift_type_night==1)
			{
				$totalShiftHour=$this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $shift_data->shift_end_time);
				
				$totalWorkingHour=$this->MakeTimeDifferenceNightShift($start_date, $in_time,$end_date, $out_time);
				
				$totalOT=$this->CalCulateTtalInTimeBetween($totalShiftHour, $totalWorkingHour);
				
				
			}
			else
			{
				
				
				
				$totalShiftHour=$this->CalCulateTtalInTimeBetween(date('Y-m-d').' '.$shift_data->shift_start_time, date('Y-m-d').' '.$shift_data->shift_end_time);
				
				$totalWorkingHour=$this->CalCulateTtalInTimeBetween($start_date.' '.$in_time, $end_date.' '.$out_time);
				
				$totalOT=$this->CalCulateTtalInTimeBetween($totalShiftHour, $totalWorkingHour);
				
			}
			
			
			
			$user_new_out_time=$out_time;
			$user_new_ot_time=$totalOT;
			$user_new_total_time=$totalWorkingHour;
			
			$chkattnJobcardPolicy = AttendanceJobcardPolicy::where('is_user_max_ot_fixed',1)->count();
			if($chkattnJobcardPolicy!=0)
			{
				$attnJobcardPolicy = AttendanceJobcardPolicy::where('is_user_max_ot_fixed',1)->first();
				if ($this->FormatHHMM($totalOT) > $this->FormatHHMM($attnJobcardPolicy->user_max_ot_hour)) {
					$totalOTs = $attnJobcardPolicy->user_max_ot_hour;
					//echo "Admin - ".$totalOT."<br>";                                   
					//echo "User - ".$user_new_ot_time."<br>";                                   
               
					if ($attnJobcardPolicy->is_user_ot_adjust_with_outtime == 1) {
						$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetween($totalOTs, $totalOT);
						//echo $left_time_to_deduct_from_out_time;
						//exit();
						$user_new_out_time=$this->CalCulateTtalInTimeBetween($out_time, $left_time_to_deduct_from_out_time);
						//echo $user_new_out_time;
						//exit();
						
						$user_new_ot_time=$attnJobcardPolicy->user_max_ot_hour;
						$user_new_total_time=$this->CalCulateTtalInTimeBetween($start_date.' '.$in_time, $end_date.' '.$out_time);
						
					}
					else
					{
						$user_new_out_time=$out_time;
						$user_new_ot_time=$totalOTs;
						$user_new_total_time=$totalWorkingHour;
					}
                }
				else
				{
					$user_new_out_time=$out_time;
					$user_new_ot_time=$totalOT;
					$user_new_total_time=$totalWorkingHour;
				}
			}
			else
			{
				$user_new_out_time=$out_time;
				$user_new_ot_time=$totalOT;
				$user_new_total_time=$totalWorkingHour;
			}
			
                                             
			//exit();
			
			
			
			$JobcardTab=AttendanceJobcard::find($jobcard_id);
			$JobcardTab->admin_in_time=$in_time;
			$JobcardTab->admin_out_time=$out_time;
			$JobcardTab->admin_total_time=$totalWorkingHour;
			$JobcardTab->admin_total_ot=$totalOT;
			
			$JobcardTab->user_total_time=$user_new_total_time;
			$JobcardTab->user_total_ot=$user_new_ot_time;
			
			$JobcardTab->user_in_time=$in_time;
			$JobcardTab->user_out_time=$user_new_out_time;
			$JobcardTab->save();
			
			//$JobcardTab->admin_total_ot;
			
			//exit();
			
		}
		
		
		$JobcardData=AttendanceJobcard::find($jobcard_id);
		return $JobcardData->admin_total_ot;
		//return 1;

    }

	
    public function ProcessWeekendOTExJobCard() {
        $sql = DB::table('attendance_jobcards')
                ->select('attendance_jobcards.id', 'attendance_jobcards.company_id', 'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status')
                ->whereIn('attendance_jobcards.admin_day_status', ['W', 'H'])
//->where('attendance_jobcards.emp_code', 'RPAC0082')
                ->get();

        foreach ($sql as $row):
            $emp_code = $row->emp_code;
            $start_date = $row->start_date;
            $end_date = $row->end_date;
            $jobcard_id = $row->id;
            $jobcard_day_status = $row->admin_day_status;
            $jobcard_total_time = $row->admin_total_time;
            $jobcard_out_time = $row->admin_out_time;
            $company_id = $row->company_id;
            $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
//break;
        endforeach;
        echo 1;
    }
	
	private function AddNewTimeFromArrayCarbon($param = '+', $shift_start_time = '00:00:00', $floatStartBuffer = array()) {
        $retData = $shift_start_time;
        if (!empty($param)) {
            $objAdminTime = Carbon::parse($shift_start_time);
            if ($param == "+") {
                $objAdminTime->toDateTimeString();
                $objAdminTime->addHours($floatStartBuffer[0]);
                $objAdminTime->addMinutes($floatStartBuffer[1]);
                $objAdminTime->addSeconds($floatStartBuffer[2]);
            } elseif ($param == "-") {
                $objAdminTime->toDateTimeString();
                $objAdminTime->subHours($floatStartBuffer[0]);
                $objAdminTime->subMinutes($floatStartBuffer[1]);
                $objAdminTime->subSeconds($floatStartBuffer[2]);
            } else {
                $objAdminTime->toDateTimeString();
            }

            $retData = $objAdminTime->format('H:i:s');
        }


        return $retData;
    }
	
	private function StandardOTPlace($emp_code='',$date='0000-00-00',$adminOutTime='00:00:00',$AdminOTTime='00:00:00',$totalOT='00:00:00')
	{
		
		$countWeekendOT=WeekendOTPolicy::where('is_standard_max_ot_hour',1)->count();
		if($countWeekendOT!=0)
		{
			$WeekendOTpol=WeekendOTPolicy::where('is_standard_max_ot_hour',1)->first();
			$max_OT=$WeekendOTpol->standard_max_ot_hour;
			if($totalOT>=$max_OT)
			{
				$totalOTs=$max_OT;
			}
			else
			{
				$totalOTs=$totalOT;
			}
			
			
			if($WeekendOTpol->is_ot_will_start_after_fix_hour==1)
			{
				$hour_after=$WeekendOTpol->hour_after;
			}
			else
			{
				$hour_after='00:00:00';
			}
			
			
			
			if($totalOTs=='00:00:00' || empty($totalOTs))
			{
				$totalOTs='00:00:00';
				$totalWeekendStandardOT=$totalOTs;
				$LeftTimeToDeductFromOutTime=$AdminOTTime;
				$newStandardOutTime=$adminOutTime;
				
			}
			else
			{
				
				
				$hour_after_param=explode(":",$hour_after);
				//$totalWeekendStandardOT=$this->AddNewTimeFromArrayCarbon('+',$totalOTs,$hour_after_param);
				$totalWeekendStandardOT=$totalOTs;
				
				
				
				$totalWeekendStandardOT_param=explode(":",$totalWeekendStandardOT);
				if($AdminOTTime<=$totalWeekendStandardOT)
				{
					$LeftTimeToDeductFromOutTime='00:00:00';
				}
				else
				{
					$LeftTimeToDeductFromOutTime=$this->AddNewTimeFromArrayCarbon('-',$AdminOTTime,$totalWeekendStandardOT_param); //1:30
				}
				
				//echo "New = ".$LeftTimeToDeductFromOutTime;
			
				//exit();
				
				
				$LeftTimeToDeductFromOutTime_param=explode(":",$LeftTimeToDeductFromOutTime);
				$newStandardOutTime=$this->AddNewTimeFromArrayCarbon('-',$adminOutTime,$LeftTimeToDeductFromOutTime_param); //12:20
				
				
				
			}
			
			
			

			
			$tab=AttendanceJobcard::where('start_date',$date)->where('emp_code',$emp_code)->first();
			$tab->user_out_time=$newStandardOutTime;
			$tab->user_total_time=$totalWeekendStandardOT;
			$tab->user_total_ot=$totalOTs;
			$tab->save();
			
			
		}
		
		
		
		
		
		return 1;
		
	}
	

    public function Adminshow(Request $request) {

        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
		app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($emp_code);
        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();
		$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
			
            $company_id = $alt_company_id;
        }
		
		//echo $cal_company_id;
		//exit();
		
		if(empty($cal_company_id))
		{
			$cal_company_id=$company_id;
		}

        $sqlDates = Calendar::where('calendars.company_id', $cal_company_id)
				->join('day_types','calendars.day_type_id','=','day_types.id')
                ->whereBetween('date', [$start_date, $end_date])
				->select('calendars.date','day_types.day_short_code')
                ->groupby('calendars.date')
                ->get();
		

		$chkstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->count();
		if($chkstaff_grades==0)
		{
			$isOTElg=0;
		}
		else
		{
				$sqlstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->first();
				if($sqlstaff_grades->is_ot_eligible==1)	
				{
					$isOTElg=1;
				}
				else
				{
					$isOTElg=0;
				}
		}
		
		//echo $isOTElg; //ot elegible check
		//exit();
		
        if (!empty($sqlDates)) {
            $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;

                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 
								'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();
						
						
				
						
				$cal_day_type=$line->day_short_code;		
				$stdDay=array("W","H");
                if (count($data) != 0) {
					
                    $jobcard_id = $data[0]->id;
                    $jobcard_emp_code = $data[0]->emp_code;
                    $jobcard_start_date = $data[0]->start_date;
                    $jobcard_end_date = $data[0]->end_date;
                    $jobcard_in_time = $data[0]->admin_in_time;
                    $jobcard_out_time = $data[0]->admin_out_time;
                    $jobcard_total_time = $data[0]->admin_total_time;
                    $jobcard_total_ot = $data[0]->admin_total_ot;
					
					
					
                    $dayArray = array("A", "H", "P", "W");
					
                    if (in_array($data[0]->admin_day_status, $dayArray)) {
                        $jobcard_day_status = $this->ManualJobCardEntryCheck($data[0]->admin_day_status, $ld, $emp_code, 'admin_day_status', $company_id);
                    } 
					elseif(!empty($jobcard_in_time) || !empty($jobcard_out_time))
					{
						$chkHalfDayLeave=$this->HalfDayLeaveCheck($jobcard_start_date,$company_id,$jobcard_emp_code);
						if($chkHalfDayLeave==1)
						{
							$jobcard_day_status=$line->day_short_code;
							$tablJob=AttendanceJobcard::find($jobcard_id);
							$tablJob->user_day_status=$jobcard_day_status;
							$tablJob->admin_day_status=$jobcard_day_status;
							$tablJob->audit_day_status=$jobcard_day_status;
							$tablJob->save();
						}
						else
						{
							$jobcard_day_status = $data[0]->admin_day_status;
							
						}
					}
					else {
                        $jobcard_day_status = $data[0]->admin_day_status;
                    }
					
					
					
					if(in_array($jobcard_day_status,$stdDay) || in_array($line->day_short_code,$stdDay))
					{
						if($isOTElg==1)
						{
							$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
						}
						else
						{
							$jobcard_total_ot = "00:00:00";
						}
						
						
					}
					
					if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
					{
						$jobcard_day_status=$cal_day_type;
					}
					
					
                } else {
                    $jobcard_id = 0;
                    $jobcard_emp_code = 0;
                    $jobcard_start_date = $ld;
                    $jobcard_end_date = $ld;
                    $jobcard_in_time = "00:00:00";
                    $jobcard_out_time = "00:00:00";
                    $jobcard_total_time = "00:00:00";
                    $jobcard_total_ot = "00:00:00";
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                }
//echo 1;
//exit();
				/*if($isOTElg==1)
				{
					$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
				}
				else
				{
					$jobcard_total_ot='00:00:00';
				}*/
				
				
                if(empty($jobcard_in_time))
				{
					$jobcard_in_time='00:00:00';
				}
				
				if(empty($jobcard_out_time))
				{
					$jobcard_out_time='00:00:00';
				}
				
				
				
				
				if($jobcard_in_time=='00:00:00' || $jobcard_out_time=='00:00:00')
				{
					$jobcard_total_ot='00:00:00';
				}
				
				if(empty($jobcard_end_date))
				{
					$jobcard_end_date=$jobcard_start_date;
				}
				
				
				if($jobcard_in_time=='00:00:00' && $jobcard_out_time=='00:00:00')
				{
					
					
				}
				else
				{
						if($jobcard_day_status=='A')
						{
							$jobcard_day_status='P';
						}
					
				}
				
				if(empty($jobcard_total_ot))
				{
					$jobcard_total_ot='00:00:00';
				}
				
				if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
				{
					$jobcard_day_status=$cal_day_type;
				}
				
				$timecheckByDay=array("W","H","P","Late IN","Late OUT");
				if(!in_array($jobcard_day_status,$timecheckByDay))
				{
					$jobcard_total_time='00:00:00';
					$jobcard_in_time='00:00:00';
					$jobcard_out_time='00:00:00';
					$jobcard_total_ot='00:00:00';
				}
				
                $json[] = array(
                    'id' => $jobcard_id,
                    'emp_code' => $jobcard_emp_code,
                    'start_date' => $jobcard_start_date,
                    'end_date' => $jobcard_end_date,
                    'in_time' => $jobcard_in_time,
                    'out_time' => $jobcard_out_time,
                    'total_time' => $jobcard_total_time,
                    'total_ot' => $jobcard_total_ot,
                    'day_status' => $jobcard_day_status
                );

            endforeach;
        }

        return response()->json(array("data" => $json, "total" => count($json)));
    }
	
	public function LateINShow(Request $request) 
	{

        $company_id = $request->company_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
		
		
		//echo $isOTElg; //ot elegible check
		//exit();
		
            $json = [];

			if(empty($start_date))
			{
				$start_date = date('Y-m-d');
			}
			
			if(empty($end_date))
			{
				$end_date = date('Y-m-d');
			}
			
			$dateBetween=array($start_date,$end_date);

			$data = DB::table('attendance_jobcards')
					->join('employee_infos','attendance_jobcards.emp_code','=','employee_infos.emp_code')
					->select(
							'attendance_jobcards.id', 
							'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
					)
					->where('employee_infos.company_id', $company_id)
					->where('attendance_jobcards.admin_day_status','Late IN')
					->whereBetween('attendance_jobcards.start_date',$dateBetween)
					->orderBy('attendance_jobcards.start_date', 'ASC')
					->get();
						
						
				
						
			foreach($data as $row)
			{
					$json[] = array(
						'id' =>$row->id,
						'emp_code' =>$row->emp_code,
						'start_date' =>$row->start_date,
						'end_date' =>$row->end_date,
						'in_time' =>$row->admin_in_time,
						'out_time' =>$row->admin_out_time,
						'total_time' =>$row->admin_total_time,
						'total_ot' =>$row->admin_total_ot,
						'day_status' =>$row->admin_day_status
					);
            }
        

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    public function CompanyAttendanceLogshow(Request $request) {

        $date = $request->date;
        $req_company_id = $request->company_id;



        $sqlEMpInfo=DB::table('employee_infos')->where('company_id',$req_company_id)->get();
        $jsond = [];
        //echo "<pre>";
        //print_r($sqlEMpInfo);
        //exit();
        foreach($sqlEMpInfo as $emp){
            //emp loop start
            $emp_code=$emp->emp_code;


            //$dd=app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($emp_code);
            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();
            $cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
            if (isset($sqlEmp)) {
                $company_id = $sqlEmp->company_id;
            } else {
                $alt_company_id = $req_company_id;
                
                $company_id = $alt_company_id;
            }
            
            //echo $cal_company_id;
            //exit();
            
            if(empty($cal_company_id))
            {
                $cal_company_id=$company_id;
            }

            $sqlDates = Calendar::where('calendars.company_id', $cal_company_id)
                    ->join('day_types','calendars.day_type_id','=','day_types.id')
                    ->where('date',$date)
                    ->select('calendars.date','day_types.day_short_code')
                    ->groupby('calendars.date')
                    ->get();
            

            $chkstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                    ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
                    ->select('staff_grades.is_ot_eligible')
                    ->count();
            if($chkstaff_grades==0)
            {
                $isOTElg=0;
            }
            else
            {
                    $sqlstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                    ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
                    ->select('staff_grades.is_ot_eligible')
                    ->first();
                    if($sqlstaff_grades->is_ot_eligible==1) 
                    {
                        $isOTElg=1;
                    }
                    else
                    {
                        $isOTElg=0;
                    }
            }
            
            //echo $isOTElg; //ot elegible check
            //exit();
            
            if (!empty($sqlDates)) {
                $json = [];
                foreach ($sqlDates as $line):


                    $ld = $line->date;

                    $data = DB::table('attendance_jobcards')
                            ->select(
                                    'attendance_jobcards.id', 
                                    'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
                            )
                            ->where('attendance_jobcards.emp_code', $emp_code)
                            ->where(function($q) use ($ld) {
                                $q->where('attendance_jobcards.start_date', $ld);
                            })
                            ->orderBy('attendance_jobcards.id', 'DESC')
                            ->get();
                    
                   // echo "<pre>";        
                   // print_r($data);
                   // exit();        
                    
                            
                    $cal_day_type=$line->day_short_code;        
                    $stdDay=array("W","H");
                    if (count($data) != 0) {
                        
                        $jobcard_id = $data[0]->id;
                        $jobcard_emp_code = $data[0]->emp_code;
                        $jobcard_start_date = $data[0]->start_date;
                        $jobcard_end_date = $data[0]->end_date;
                        $jobcard_in_time = $data[0]->admin_in_time;
                        $jobcard_out_time = $data[0]->admin_out_time;
                        $jobcard_total_time = $data[0]->admin_total_time;
                        $jobcard_total_ot = $data[0]->admin_total_ot;
                        
                        
                        
                        $dayArray = array("A", "H", "P", "W");
                        
                        if (in_array($data[0]->admin_day_status, $dayArray)) {
                            $jobcard_day_status = $this->ManualJobCardEntryCheck($data[0]->admin_day_status, $ld, $emp_code, 'admin_day_status', $company_id);
                        } 
                        elseif(!empty($jobcard_in_time) || !empty($jobcard_out_time))
                        {
                            $chkHalfDayLeave=$this->HalfDayLeaveCheck($jobcard_start_date,$company_id,$jobcard_emp_code);
                            if($chkHalfDayLeave==1)
                            {
                                $jobcard_day_status=$line->day_short_code;
                                $tablJob=AttendanceJobcard::find($jobcard_id);
                                $tablJob->user_day_status=$jobcard_day_status;
                                $tablJob->admin_day_status=$jobcard_day_status;
                                $tablJob->audit_day_status=$jobcard_day_status;
                                $tablJob->save();
                            }
                            else
                            {
                                $jobcard_day_status = $data[0]->admin_day_status;
                                
                            }
                        }
                        else {
                            $jobcard_day_status = $data[0]->admin_day_status;
                        }
                        
                        
                        
                        if(in_array($jobcard_day_status,$stdDay) || in_array($line->day_short_code,$stdDay))
                        {
                            if($isOTElg==1)
                            {
                                $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
                            }
                            else
                            {
                                $jobcard_total_ot = "00:00:00";
                            }
                            
                            
                        }
                        
                        if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
                        {
                            $jobcard_day_status=$cal_day_type;
                        }
                        
                        
                    } else {
                        $jobcard_id = 0;
                        $jobcard_emp_code = $emp_code;
                        $jobcard_start_date = $ld;
                        $jobcard_end_date = $ld;
                        $jobcard_in_time = "00:00:00";
                        $jobcard_out_time = "00:00:00";
                        $jobcard_total_time = "00:00:00";
                        $jobcard_total_ot = "00:00:00";
                        $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);


                    }
    //echo 1;
    //exit();
                    /*if($isOTElg==1)
                    {
                        $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
                    }
                    else
                    {
                        $jobcard_total_ot='00:00:00';
                    }*/
                    
                    
                    if(empty($jobcard_in_time))
                    {
                        $jobcard_in_time='00:00:00';
                    }
                    
                    if(empty($jobcard_out_time))
                    {
                        $jobcard_out_time='00:00:00';
                    }
                    
                    
                    
                    
                    if($jobcard_in_time=='00:00:00' || $jobcard_out_time=='00:00:00')
                    {
                        $jobcard_total_ot='00:00:00';
                    }
                    
                    if(empty($jobcard_end_date))
                    {
                        $jobcard_end_date=$jobcard_start_date;
                    }
                    
                    
                    if($jobcard_in_time=='00:00:00' && $jobcard_out_time=='00:00:00')
                    {
                        
                        
                    }
                    else
                    {
                            if($jobcard_day_status=='A')
                            {
                                $jobcard_day_status='P';
                            }
                        
                    }
                    
                    if(empty($jobcard_total_ot))
                    {
                        $jobcard_total_ot='00:00:00';
                    }
                    
                    if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
                    {
                        $jobcard_day_status=$cal_day_type;
                    }
                    
                    $timecheckByDay=array("W","H","P","Late IN","Late OUT");
                    if(!in_array($jobcard_day_status,$timecheckByDay))
                    {
                        $jobcard_total_time='00:00:00';
                        $jobcard_in_time='00:00:00';
                        $jobcard_out_time='00:00:00';
                        $jobcard_total_ot='00:00:00';
                    }

                   // echo $emp_code;
                //exit();
                    
                    $json[] = array(
                        'id' => $jobcard_id,
                        'emp_code' => $emp_code,
                        'start_date' => $jobcard_start_date,
                        'end_date' => $jobcard_end_date,
                        'in_time' => $jobcard_in_time,
                        'out_time' => $jobcard_out_time,
                        'total_time' => $jobcard_total_time,
                        'total_ot' => $jobcard_total_ot,
                        'day_status' => $jobcard_day_status
                    );

                    //echo "<pre>";
                    //print_r($json);
                    //exit();

                endforeach;
                //print_r($json);
                //exit();

                $jsond[] = array(
                        'id' => $json[0]['id'],
                        'emp_code' =>$json[0]['emp_code'],
                        'start_date' => $json[0]['start_date'],
                        'end_date' =>$json[0]['end_date'],
                        'in_time' =>$json[0]['in_time'],
                        'out_time' =>$json[0]['out_time'],
                        'total_time' =>$json[0]['total_time'],
                        'total_ot' =>$json[0]['total_ot'],
                        'day_status' =>$json[0]['day_status']
                    );

                $json=[];

            }

            //emp loop end 
        }

        

        return response()->json(array("data" => $jsond, "total" => count($jsond)));
    }

    public function Auditshow(Request $request) {

        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();

        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }

        $sqlDates = Calendar::where('calendars.company_id', $company_id)
				->leftJoin('day_types','calendars.day_type_id','=','day_types.id')
                ->whereBetween('date', [$start_date, $end_date])
				->select('calendars.date','day_types.day_short_code')
                ->groupby('calendars.date')
                ->get();

        if (!empty($sqlDates)) {
            $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;

                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.audit_in_time', 'attendance_jobcards.audit_out_time', 'attendance_jobcards.audit_total_time', 'attendance_jobcards.audit_total_ot', 'attendance_jobcards.audit_day_status'
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();

                if (count($data) != 0) {
                    $jobcard_id = $data[0]->id;
                    $jobcard_emp_code = $data[0]->emp_code;
                    $jobcard_start_date = $data[0]->start_date;
                    $jobcard_end_date = $data[0]->end_date;
                    $jobcard_in_time = $data[0]->audit_in_time;
                    $jobcard_out_time = $data[0]->audit_out_time;
                    $jobcard_total_time = $data[0]->audit_total_time;
                    $jobcard_total_ot = $data[0]->audit_total_ot;
//$jobcard_day_status = $data[0]->audit_day_status;

                    $dayArray = array("A", "H", "P", "W");
                    if (in_array($data[0]->audit_day_status, $dayArray)) {
                        $jobcard_day_status = $this->ManualJobCardEntryCheck($data[0]->audit_day_status, $ld, $emp_code, 'audit_day_status', $company_id);
                    } else {
                        $jobcard_day_status = $data[0]->audit_day_status;
                    }

                    //$jobcard_day_status = $this->ManualJobCardEntryCheck($data[0]->audit_day_status, $ld, $emp_code, 'audit_day_status', $company_id);
                } else {
                    $jobcard_id = 0;
                    $jobcard_emp_code = 0;
                    $jobcard_start_date = $ld;
                    $jobcard_end_date = $ld;
                    $jobcard_in_time = "00:00:00";
                    $jobcard_out_time = "00:00:00";
                    $jobcard_total_time = "00:00:00";
                    $jobcard_total_ot = "00:00:00";
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'audit_day_status', $company_id);
                }

                $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);

                $json[] = array(
                    'id' => $jobcard_id,
                    'emp_code' => $jobcard_emp_code,
                    'start_date' => $jobcard_start_date,
                    'end_date' => $jobcard_end_date,
                    'in_time' => $jobcard_in_time,
                    'out_time' => $jobcard_out_time,
                    'total_time' => $jobcard_total_time,
                    'total_ot' => $jobcard_total_ot,
                    'day_status' => $jobcard_day_status
                );

            endforeach;
        }

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    public function Usershow(Request $request) {

        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
		app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($emp_code);
        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();
		//print_r($sqlEmp);
		//exit();
		$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
        
		
        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }
		
		if(empty($cal_company_id))
		{
			$cal_company_id=$company_id;
		}

        $sqlDates = Calendar::where('calendars.company_id', $cal_company_id)
				->Join('day_types','calendars.day_type_id','=','day_types.id')
				->leftjoin('attendance_jobcards', function ($join) use ($emp_code) {
                            $join->on('calendars.date', '=', 'attendance_jobcards.start_date')
                                 ->where('attendance_jobcards.emp_code', '=',$emp_code);
                })
                ->whereBetween('date', [$start_date, $end_date])
				->select('calendars.date','day_types.day_short_code',
				'attendance_jobcards.id', 
				'attendance_jobcards.emp_code', 
				'attendance_jobcards.start_date', 
				'attendance_jobcards.end_date', 
				'attendance_jobcards.user_end_date', 
				'attendance_jobcards.user_in_time', 
				'attendance_jobcards.user_out_time', 
				'attendance_jobcards.user_total_time', 
				'attendance_jobcards.user_total_ot', 
				'attendance_jobcards.user_day_status',
				'attendance_jobcards.admin_out_time', 
				'attendance_jobcards.admin_total_ot',
				'attendance_jobcards.ll_ref')
                ->groupby('calendars.date')
                ->get();
		
		
				
		$chkstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->count();
		if($chkstaff_grades==0)
		{
			$isOTElg=0;
		}
		else
		{
				$sqlstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->first();
				if($sqlstaff_grades->is_ot_eligible==1)	
				{
					$isOTElg=1;
				}
				else
				{
					$isOTElg=0;
				}
		}		
				
				
		

        if (!empty($sqlDates)) {
            $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;
				$cal_day_type=$line->day_short_code;
				//exit();
                /*$data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.user_in_time', 'attendance_jobcards.user_out_time', 'attendance_jobcards.user_total_time', 'attendance_jobcards.user_total_ot', 'attendance_jobcards.user_day_status',
								'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_ot' 
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();*/

                if (isset($line->id)) {
					//echo "Geeting Data";
				
					
					
					//exit();
                    $jobcard_id = $line->id;
                    $jobcard_emp_code = $line->emp_code;
                    $jobcard_start_date = $line->start_date;
                    $jobcard_end_date = $line->end_date;
					$jobcard_user_end_date = $line->user_end_date;
                    $jobcard_in_time = $line->user_in_time;
                    $jobcard_out_time = $line->user_out_time;
					$jobcard_admin_out_time = $line->admin_out_time;
					$jobcard_admin_total_ot = $line->admin_total_ot;
                    $jobcard_total_time = $line->user_total_time;
                    $jobcard_total_ot = $line->user_total_ot;
                    $jobcard_day_status = $line->user_day_status;
                    $dayArray = array("A", "H", "P", "W");
					
					//print_r($line);
					//exit();
					
					
					if(!empty($jobcard_user_end_date))
					{
						if($jobcard_user_end_date!='0000-00-00')
						{
							$jobcard_end_date = $jobcard_user_end_date;
						}
					}
					
					if($jobcard_day_status=="P" && $cal_day_type=="W")
					{
						$jobcard_day_status=$cal_day_type;
					}elseif($jobcard_day_status=="P" && $cal_day_type=="H")
					{
						$jobcard_day_status=$cal_day_type;
					}
					
					
                    if (in_array($jobcard_day_status, $dayArray)) {
						
                        $jobcard_day_status = $this->ManualJobCardEntryCheck($line->user_day_status, $ld, $emp_code, 'user_day_status', $company_id);
						
                    } 
					elseif(!empty($jobcard_in_time) || !empty($jobcard_out_time))
					{
					
						$chkHalfDayLeave=$this->HalfDayLeaveCheck($jobcard_start_date,$company_id,$jobcard_emp_code);
						if($chkHalfDayLeave==1)
						{

							$jobcard_day_status=$line->day_short_code;
							$tablJob=AttendanceJobcard::find($jobcard_id);
							$tablJob->user_day_status=$jobcard_day_status;
							$tablJob->admin_day_status=$jobcard_day_status;
							$tablJob->audit_day_status=$jobcard_day_status;
							$tablJob->save();
							
							
						}
						else
						{
							
							$jobcard_day_status = $line->user_day_status;
							
						}
					}
					else {
						
                        $jobcard_day_status = $line->user_day_status;
                    }
					
					//echo $jobcard_day_status;
					//exit();
					
					$stdDay=array("W","H");
					
					if(in_array($jobcard_day_status,$stdDay) || in_array($line->day_short_code,$stdDay))
					{
						if($isOTElg==1)
						{
							//$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
							
							$this->StandardOTPlace($jobcard_emp_code,$jobcard_start_date,$jobcard_admin_out_time,$jobcard_admin_total_ot,$jobcard_total_ot);
						
						
						
							$tab=AttendanceJobcard::find($jobcard_id);
							
							//echo $tab->user_out_time;
							//exit();
							
							$jobcard_out_time = $tab->user_out_time;
							$jobcard_total_time = $tab->user_total_time;
							$jobcard_total_ot = $tab->user_total_ot;
							$jobcard_day_status = $tab->user_day_status;
							
							//if($jobcard_total_ot>='02:00:00')
							//{
								
							//}
							
						}
						
						
					}
					elseif(in_array($jobcard_day_status,array("P","Late IN","Late OUT")))
					{
						if($jobcard_total_ot>='02:00:00')
						{
							$jobcard_total_ot='02:00:00';
							$tab=AttendanceJobcard::find($jobcard_id);
							$tab->user_total_ot=$jobcard_total_ot;
							$tab->save();
							
						}
					}
					
					//$stdDay=array("W","H");
					if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
					{
						$jobcard_day_status=$cal_day_type;
					}
					
					
                    //$jobcard_day_status = $this->ManualJobCardEntryCheck($data[0]->user_day_status, $ld, $emp_code, 'user_day_status', $company_id);
                } else {
					//echo "Not Geeting Data";
				
					//exit();
					
					
					
                    $jobcard_id = 0;
                    $jobcard_emp_code = 0;
                    $jobcard_start_date = $ld;
                    $jobcard_end_date = $ld;
                    $jobcard_in_time = "00:00:00";
                    $jobcard_out_time = "00:00:00";
                    $jobcard_total_time = "00:00:00";
                    $jobcard_total_ot = "00:00:00";
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'user_day_status', $company_id);
                }
				

                //$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
				
				
				
				
                if(empty($jobcard_in_time))
				{
					$jobcard_in_time='00:00:00';
				}
				
				if(empty($jobcard_out_time))
				{
					$jobcard_out_time='00:00:00';
				}
				
				
				if($jobcard_in_time=='00:00:00' || $jobcard_out_time=='00:00:00')
				{
					$jobcard_total_ot='00:00:00';
				}
				
				if(empty($jobcard_end_date))
				{
					$jobcard_end_date=$jobcard_start_date;
				}
				
				
				
				if($jobcard_in_time=='00:00:00' && $jobcard_out_time=='00:00:00')
				{
					
					
				}
				else
				{
						if($jobcard_day_status=='A')
						{
							$jobcard_day_status='P';
						}
					
				}
				
				
				
				$timecheckByDay=array("W","H","P","Late IN","Late OUT");
				if(!in_array($jobcard_day_status,$timecheckByDay))
				{
					$jobcard_total_time='00:00:00';
					$jobcard_in_time='00:00:00';
					$jobcard_out_time='00:00:00';
					$jobcard_total_ot='00:00:00';
				}
				
				
				if(in_array($jobcard_day_status,array("W","H")))
				{
					if($ld>='2017-07-01')
					{
						$ll_flag=0;
						if(!empty($line->ll_ref))
						{
							
							if($line->ll_ref=="0000-00-00")
							{
								$ll_flag=0;
							}
							else
							{
								$ll_flag=1;
							}
						}
						
						if($ll_flag==0)
						{
							$jobcard_total_time='00:00:00';
							$jobcard_in_time='00:00:00';
							$jobcard_out_time='00:00:00';
							$jobcard_total_ot='00:00:00';
						}
					}
				}
				
				if(empty($jobcard_total_ot))
				{
					$jobcard_total_ot='00:00:00';
				}
				
				
				if($jobcard_day_status=="P" && $cal_day_type=="W")
				{
					$jobcard_day_status=$cal_day_type;
				}elseif($jobcard_day_status=="P" && $cal_day_type=="H")
				{
					$jobcard_day_status=$cal_day_type;
				}
				
				

                $json[] = array(
                    'id' => $jobcard_id,
                    'emp_code' => $jobcard_emp_code,
                    'start_date' => $jobcard_start_date,
                    'end_date' => $jobcard_end_date,
                    'in_time' => $jobcard_in_time,
                    'out_time' => $jobcard_out_time,
                    'total_time' => $jobcard_total_time,
                    'total_ot' => $jobcard_total_ot,
                    'day_status' => $jobcard_day_status
                );
				
				
				
				
				
				//exit();

            endforeach;
			
        }
		
			
//print_r($dayStatus);
//exit();


        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
//
    }

    public function AdminexportExcel(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {


        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();

        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }


        $sqlDates = Calendar::where('company_id', $company_id)
                ->whereBetween('date', [$start_date, $end_date])
                ->groupby('calendars.date')
                ->get();

        if (!empty($sqlDates)) {
// $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;

                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();

                if (count($data) != 0) {
                    $jobcard_id = $data[0]->id;
                    $jobcard_start_date = $data[0]->start_date;
                    $jobcard_end_date = $data[0]->end_date;
                    $jobcard_in_time = $data[0]->admin_in_time;
                    $jobcard_out_time = $data[0]->admin_out_time;
                    $jobcard_total_time = $data[0]->admin_total_time;
                    $jobcard_total_ot = $data[0]->admin_total_ot;
                    $jobcard_day_status = $data[0]->admin_day_status;
                } else {
                    $jobcard_id = 0;
                    $jobcard_start_date = $ld;
                    $jobcard_end_date = $ld;
                    $jobcard_in_time = "00:00:00";
                    $jobcard_out_time = "00:00:00";
                    $jobcard_total_time = "00:00:00";
                    $jobcard_total_ot = "00:00:00";
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                }

                $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
				
				$timecheckByDay=array("W","H","P","Late IN","Late OUT");
				
				if(!in_array($jobcard_day_status,$timecheckByDay))
				{
					$jobcard_total_time='00:00:00';
					$jobcard_in_time='00:00:00';
					$jobcard_out_time='00:00:00';
					$jobcard_total_ot='00:00:00';
				}

                $json[] = array(
//                    'id' => $jobcard_id,
                    'start_date' => $jobcard_start_date,
                    'end_date' => $jobcard_end_date,
                    'in_time' => $jobcard_in_time,
                    'out_time' => $jobcard_out_time,
                    'total_time' => $jobcard_total_time,
                    'total_ot' => $jobcard_total_ot,
                    'day_status' => $jobcard_day_status,
                );

            endforeach;
        }


        $excelArray = [];

// Define the Excel spreadsheet headers
        $excelArray [] = [
//            'ID',
            'Start Date',
            'End Date',
            'In Time',
            'Out Time',
            'Total Working Time',
            'Total Over Time',
            'Day Status',
        ];

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
//exit();
// Generate and return the spreadsheet
        \Excel::create('Admin Jobcard Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

// Set the spreadsheet title, creator, and description
            $excel->setTitle('Admin Jobcard Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Admin Jobcard Report');

// Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function CompanyWiseexportExcel($company_id = 0, $date = 0) {


        //$date = $request->date;
        $req_company_id = $company_id;



        $sqlEMpInfo=DB::table('employee_infos')->where('company_id',$req_company_id)->get();
        $jsond = [];
        //echo "<pre>";
        //print_r($sqlEMpInfo);
        //exit();
        foreach($sqlEMpInfo as $emp){
            //emp loop start
            $emp_code=$emp->emp_code;


            //$dd=app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($emp_code);
            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();
            $cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
            if (isset($sqlEmp)) {
                $company_id = $sqlEmp->company_id;
            } else {
                $alt_company_id = $req_company_id;
                
                $company_id = $alt_company_id;
            }
            
            //echo $cal_company_id;
            //exit();
            
            if(empty($cal_company_id))
            {
                $cal_company_id=$company_id;
            }

            $sqlDates = Calendar::where('calendars.company_id', $cal_company_id)
                    ->join('day_types','calendars.day_type_id','=','day_types.id')
                    ->where('date',$date)
                    ->select('calendars.date','day_types.day_short_code')
                    ->groupby('calendars.date')
                    ->get();
            

            $chkstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                    ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
                    ->select('staff_grades.is_ot_eligible')
                    ->count();
            if($chkstaff_grades==0)
            {
                $isOTElg=0;
            }
            else
            {
                    $sqlstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                    ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
                    ->select('staff_grades.is_ot_eligible')
                    ->first();
                    if($sqlstaff_grades->is_ot_eligible==1) 
                    {
                        $isOTElg=1;
                    }
                    else
                    {
                        $isOTElg=0;
                    }
            }
            
            //echo $isOTElg; //ot elegible check
            //exit();
            
            if (!empty($sqlDates)) {
                $json = [];
                foreach ($sqlDates as $line):


                    $ld = $line->date;

                    $data = DB::table('attendance_jobcards')
                            ->select(
                                    'attendance_jobcards.id', 
                                    'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
                            )
                            ->where('attendance_jobcards.emp_code', $emp_code)
                            ->where(function($q) use ($ld) {
                                $q->where('attendance_jobcards.start_date', $ld);
                            })
                            ->orderBy('attendance_jobcards.id', 'DESC')
                            ->get();
                    
                   // echo "<pre>";        
                   // print_r($data);
                   // exit();        
                    
                            
                    $cal_day_type=$line->day_short_code;        
                    $stdDay=array("W","H");
                    if (count($data) != 0) {
                        
                        $jobcard_id = $data[0]->id;
                        $jobcard_emp_code = $data[0]->emp_code;
                        $jobcard_start_date = $data[0]->start_date;
                        $jobcard_end_date = $data[0]->end_date;
                        $jobcard_in_time = $data[0]->admin_in_time;
                        $jobcard_out_time = $data[0]->admin_out_time;
                        $jobcard_total_time = $data[0]->admin_total_time;
                        $jobcard_total_ot = $data[0]->admin_total_ot;
                        
                        
                        
                        $dayArray = array("A", "H", "P", "W");
                        
                        if (in_array($data[0]->admin_day_status, $dayArray)) {
                            $jobcard_day_status = $this->ManualJobCardEntryCheck($data[0]->admin_day_status, $ld, $emp_code, 'admin_day_status', $company_id);
                        } 
                        elseif(!empty($jobcard_in_time) || !empty($jobcard_out_time))
                        {
                            $chkHalfDayLeave=$this->HalfDayLeaveCheck($jobcard_start_date,$company_id,$jobcard_emp_code);
                            if($chkHalfDayLeave==1)
                            {
                                $jobcard_day_status=$line->day_short_code;
                                $tablJob=AttendanceJobcard::find($jobcard_id);
                                $tablJob->user_day_status=$jobcard_day_status;
                                $tablJob->admin_day_status=$jobcard_day_status;
                                $tablJob->audit_day_status=$jobcard_day_status;
                                $tablJob->save();
                            }
                            else
                            {
                                $jobcard_day_status = $data[0]->admin_day_status;
                                
                            }
                        }
                        else {
                            $jobcard_day_status = $data[0]->admin_day_status;
                        }
                        
                        
                        
                        if(in_array($jobcard_day_status,$stdDay) || in_array($line->day_short_code,$stdDay))
                        {
                            if($isOTElg==1)
                            {
                                $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
                            }
                            else
                            {
                                $jobcard_total_ot = "00:00:00";
                            }
                            
                            
                        }
                        
                        if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
                        {
                            $jobcard_day_status=$cal_day_type;
                        }
                        
                        
                    } else {
                        $jobcard_id = 0;
                        $jobcard_emp_code = $emp_code;
                        $jobcard_start_date = $ld;
                        $jobcard_end_date = $ld;
                        $jobcard_in_time = "00:00:00";
                        $jobcard_out_time = "00:00:00";
                        $jobcard_total_time = "00:00:00";
                        $jobcard_total_ot = "00:00:00";
                        $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);


                    }
    //echo 1;
    //exit();
                    /*if($isOTElg==1)
                    {
                        $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
                    }
                    else
                    {
                        $jobcard_total_ot='00:00:00';
                    }*/
                    
                    
                    if(empty($jobcard_in_time))
                    {
                        $jobcard_in_time='00:00:00';
                    }
                    
                    if(empty($jobcard_out_time))
                    {
                        $jobcard_out_time='00:00:00';
                    }
                    
                    
                    
                    
                    if($jobcard_in_time=='00:00:00' || $jobcard_out_time=='00:00:00')
                    {
                        $jobcard_total_ot='00:00:00';
                    }
                    
                    if(empty($jobcard_end_date))
                    {
                        $jobcard_end_date=$jobcard_start_date;
                    }
                    
                    
                    if($jobcard_in_time=='00:00:00' && $jobcard_out_time=='00:00:00')
                    {
                        
                        
                    }
                    else
                    {
                            if($jobcard_day_status=='A')
                            {
                                $jobcard_day_status='P';
                            }
                        
                    }
                    
                    if(empty($jobcard_total_ot))
                    {
                        $jobcard_total_ot='00:00:00';
                    }
                    
                    if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
                    {
                        $jobcard_day_status=$cal_day_type;
                    }
                    
                    $timecheckByDay=array("W","H","P","Late IN","Late OUT");
                    if(!in_array($jobcard_day_status,$timecheckByDay))
                    {
                        $jobcard_total_time='00:00:00';
                        $jobcard_in_time='00:00:00';
                        $jobcard_out_time='00:00:00';
                        $jobcard_total_ot='00:00:00';
                    }

                   // echo $emp_code;
                //exit();
                    
                    $json[] = array(
                        'id' => $jobcard_id,
                        'emp_code' => $emp_code,
                        'start_date' => $jobcard_start_date,
                        'end_date' => $jobcard_end_date,
                        'in_time' => $jobcard_in_time,
                        'out_time' => $jobcard_out_time,
                        'total_time' => $jobcard_total_time,
                        'total_ot' => $jobcard_total_ot,
                        'day_status' => $jobcard_day_status
                    );

                    //echo "<pre>";
                    //print_r($json);
                    //exit();

                endforeach;
                //print_r($json);
                //exit();

                $jsond[] = array(
                        'id' => $json[0]['id'],
                        'emp_code' =>$json[0]['emp_code'],
                        'start_date' => $json[0]['start_date'],
                        'in_time' =>$json[0]['in_time'],
                        'end_date' =>$json[0]['end_date'],
                        'out_time' =>$json[0]['out_time'],
                        'day_status' =>$json[0]['day_status']
                    );

                $json=[];

            }

            //emp loop end 
        }


        $excelArray = [];

// Define the Excel spreadsheet headers
        $excelArray [] = [
            'ID',
            'Emp Code',
            'Start Date',
            'In Time',
            'End Date',
            'Out Time',
            'Day Status',
        ];

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($jsond as $key => $field) {
            $excelArray[] = $field;
        }
//exit();
// Generate and return the spreadsheet
        \Excel::create('Company Date Wise Attendance Log Report_' . $date, function($excel) use ($excelArray) {

// Set the spreadsheet title, creator, and description
            $excel->setTitle('Company Date Wise Attendance Log Report_');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Company Date Wise Attendance Log Report_');

// Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }







    public function AdminexportPdf(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
        
		
		$emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $sqlEmp = DB::table('employee_infos')
				  ->leftJoin('employee_departments','employee_infos.emp_code','=','employee_departments.emp_code')
				  ->leftJoin('departments','employee_departments.department_id','=','departments.id')
				  ->leftJoin('employee_designations','employee_infos.emp_code','=','employee_designations.emp_code')
				  ->leftJoin('designations','employee_designations.designation_id','=','designations.id')
				  ->select('employee_infos.id',
						   'employee_infos.first_name',
						   'employee_infos.last_name',
						   'employee_infos.company_id',
						   'employee_infos.join_date',
						   DB::Raw('departments.name as depName'),
						   DB::Raw('designations.name as desName'))
				  ->where('employee_infos.emp_code', $emp_code)->first();
		$joinDate=$sqlEmp->join_date;
		
		$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
        
        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }
		
		if(empty($cal_company_id))
		{
			$cal_company_id=$company_id;
		}
		
		$sqlcompanyName=Company::find($company_id);
		$COmpName=$sqlcompanyName->name;
		$depName=$sqlEmp->depName;
		$desName=$sqlEmp->desName;
		$eMPNamefULL=$sqlEmp->first_name;
		if(!empty($sqlEmp->last_name))
		{
			$eMPNamefULL .=" ".$sqlEmp->last_name;
		}
				
		$chkstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->count();
		if($chkstaff_grades==0)
		{
			$isOTElg=0;
		}
		else
		{
				$sqlstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->first();
				if($sqlstaff_grades->is_ot_eligible==1)	
				{
					$isOTElg=1;
				}
				else
				{
					$isOTElg=0;
				}
		}		
		
		
		$content = '<h3 align="center">'.$COmpName.'</h3>';
		$content .='<h5></h5>';
		$content .='<div><b>Code : ' . $emp_code . '</b></div>';
		$content .='<div><b>Name : ' . $eMPNamefULL . '</b></div>';
		$content .='<div><b>Department : ' . $depName . '</b></div>';
		$content .='<div><b>Designation : ' . $desName . '</b></div>';
		$content .='<div><b>Date of Join : ' . $joinDate . '</b></div>';
		$content .='<h5></h5>';
		/*
		Code: RPAC1240
		Full Name: Md. Ashraful Islam
		Department: Production
		Designation: Operator - Printed Label Production
		Date of Join: 2014-09-09
		*/
		
// instantiate and use the dompdf class
        $excelArray = [
            'start_date',
            'in_time',
			'end_date',
            'out_time',
            'total_ot',
            'day_status',
        ];
		
		
		
		
		
		
		

        if (!empty($excelArray)) {
            $content .='<table width="100%" cellpadding="0" align="center">';
            $content .='<thead>';
            $content .='<tr>';
            foreach ($excelArray as $exhead):
				if($exhead=='start_date' || $exhead=='end_date')
				{
					$exhead='Date';
				}
				elseif($exhead=='in_time')
				{
					$exhead='In Time';
				}
				elseif($exhead=='out_time')
				{
					$exhead='Out Time';
				}
				elseif($exhead=='total_ot')
				{
					$exhead='Total OT';
				}
				elseif($exhead=='day_status')
				{
					$exhead='Status';
				}
                $content .='<th align="center">' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
		
		
			$total_working_hour_array=array();


            $sqlDates = Calendar::where('calendars.company_id',$cal_company_id)
					->Join('day_types','calendars.day_type_id','=','day_types.id')
                    ->whereBetween('calendars.date', [$start_date, $end_date])
					->select('calendars.date','day_types.day_short_code')
                    ->groupby('calendars.date')
                    ->get();
			//echo "<pre>";		
			//print_r($sqlDates);		
			//exit();		

            if (!empty($sqlDates)) {
				$json = [];
				$datarows = [];
				$liste = array();
                foreach ($sqlDates as $line):

                    $ld = $line->date;
					$cal_day_type=$line->day_short_code;
                    $data = DB::table('attendance_jobcards')
                            ->select(
                                    'attendance_jobcards.id', 
									'attendance_jobcards.start_date', 
									'attendance_jobcards.end_date', 
									'attendance_jobcards.admin_in_time', 
									'attendance_jobcards.admin_out_time', 
									'attendance_jobcards.admin_total_time', 
									'attendance_jobcards.admin_total_ot',
									'attendance_jobcards.admin_day_status'
                            )
                            ->where('attendance_jobcards.emp_code', $emp_code)
                            ->where(function($q) use ($ld) {
                                $q->where('attendance_jobcards.start_date', $ld);
                            })
                            ->orderBy('attendance_jobcards.id', 'DESC')
                            ->get();

                    if (count($data) != 0) {
                        $jobcard_id = $data[0]->id;
                        $jobcard_start_date = $data[0]->start_date;
                        $jobcard_end_date = $data[0]->end_date;
                        $jobcard_in_time = $data[0]->admin_in_time;
                        $jobcard_out_time = $data[0]->admin_out_time;
                        $jobcard_total_time = $data[0]->admin_total_time;
                        $jobcard_total_ot = $data[0]->admin_total_ot;
                        $jobcard_day_status = $data[0]->admin_day_status;
                    } else {
                        $jobcard_id = 0;
                        $jobcard_start_date = $ld;
                        $jobcard_end_date = $ld;
                        $jobcard_in_time = "00:00:00";
                        $jobcard_out_time = "00:00:00";
                        $jobcard_total_time = "00:00:00";
                        $jobcard_total_ot = "00:00:00";
                        $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                    }
					if($isOTElg==1)
					{
						$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
					}
					
					if(empty($jobcard_in_time))
					{
						$jobcard_in_time='00:00:00';
					}
					
					if(empty($jobcard_out_time))
					{
						$jobcard_out_time='00:00:00';
					}
					
					
					if($jobcard_in_time=='00:00:00' || $jobcard_out_time=='00:00:00')
					{
						$jobcard_total_ot='00:00:00';
					}
					
					if(empty($jobcard_end_date))
					{
						$jobcard_end_date=$jobcard_start_date;
					}
					
					
					if($jobcard_in_time=='00:00:00' && $jobcard_out_time=='00:00:00')
					{
						
						
					}
					else
					{
							if($jobcard_day_status=='A')
							{
								$jobcard_day_status='P';
							}
						
					}
					
					if(empty($jobcard_total_ot))
					{
						$jobcard_total_ot='00:00:00';
					}
					
					if($isOTElg==0)
					{
						$jobcard_total_ot='00:00:00';
					}
					
					$stdDay=array("W","H");
					if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
					{
						$jobcard_day_status=$cal_day_type;
					}
					
					$timecheckByDay=array("W","H","P","Late IN","Late OUT");
					if(!in_array($jobcard_day_status,$timecheckByDay))
					{
						$jobcard_total_time='00:00:00';
						$jobcard_in_time='00:00:00';
						$jobcard_out_time='00:00:00';
						$jobcard_total_ot='00:00:00';
					}
					
                    $datarows[] = array(
                        'start_date' => $jobcard_start_date,
                        'end_date' => $jobcard_end_date,
                        'in_time' => $jobcard_in_time,
                        'out_time' => $jobcard_out_time,
                        'total_time' => $jobcard_total_time,
                        'total_ot' => $jobcard_total_ot,
                        'day_status' => $jobcard_day_status,
                    );
					
					array_push($total_working_hour_array,$jobcard_total_ot);
					array_push($liste,$jobcard_day_status);

                endforeach;
            }
            if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):

                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td align="center">' . $draw[$fid] . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';
			
			$arrayUnique=array_count_values($liste);
			//print_r($arrayUnique);
			foreach($arrayUnique as $key=>$unq):
				$content .='<span style="border:1px #ccc solid; padding:10px; margin-left:3px; line-height:20px;">'.$key.'-'.$unq.'</span> ';
			endforeach;
			//exit();
			if($isOTElg==1)
			{
				$content .='<span style="border:1px #ccc solid; padding:10px; margin-left:3px; line-height:20px;">OT Total - '.$this->SumAllPDFTime($total_working_hour_array).'</span> ';
			}
			//$total_working_hour_array
			
			
            $content .='<br />';

            $content .='<h4>Total : ' . count($datarows) . '</h4>';


            
        }


        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($content);

// (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4');

// Render the HTML as PDF
        $dompdf->render();

// Output the generated PDF to Browser
        $dompdf->stream();
    }

	
	public function LateINexportPdf(Request $request, $company_id = 0, $start_date = 0, $end_date = 0) {
        
		ini_set('max_execution_time', 72000);
		$company_id = $request->company_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
		
		
		//echo $isOTElg; //ot elegible check
		//exit();
		
            $json = [];

			if(empty($start_date))
			{
				$start_date = date('Y-m-d');
			}
			
			if(empty($end_date))
			{
				$end_date = date('Y-m-d');
			}
			
			$dateBetween=array($start_date,$end_date);

			$data = DB::table('attendance_jobcards')
					->join('employee_infos','attendance_jobcards.emp_code','=','employee_infos.emp_code')
					->select(
							'attendance_jobcards.id', 
							'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
					)
					->where('employee_infos.company_id', $company_id)
					->where('attendance_jobcards.admin_day_status','Late IN')
					->whereBetween('attendance_jobcards.start_date',$dateBetween)
					->orderBy('attendance_jobcards.start_date', 'ASC')
					->get();
						
						
				
						
			
		
		
		$content = '<h3 align="center">Late IN Jobcard Report <br> <code>Genarated : '.date('d/m/Y H:i:s').'</code></h3>';
		/*
		Code: RPAC1240
		Full Name: Md. Ashraful Islam
		Department: Production
		Designation: Operator - Printed Label Production
		Date of Join: 2014-09-09
		*/
		
// instantiate and use the dompdf class
        $excelArray = [
            'start_date',
            'in_time',
			'end_date',
            'out_time',
            'total_ot',
            'day_status',
        ];
		
		
		
		
		
		
		

        if (!empty($excelArray)) {
            $content .='<table width="100%" cellpadding="0" align="center">';
            $content .='<thead>';
            $content .='<tr>';
            foreach ($excelArray as $exhead):
				if($exhead=='start_date' || $exhead=='end_date')
				{
					$exhead='Date';
				}
				elseif($exhead=='in_time')
				{
					$exhead='In Time';
				}
				elseif($exhead=='out_time')
				{
					$exhead='Out Time';
				}
				elseif($exhead=='total_ot')
				{
					$exhead='Total OT';
				}
				elseif($exhead=='day_status')
				{
					$exhead='Status';
				}
                $content .='<th align="center">' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
		
		
			$total_working_hour_array=array();


            
				$json = [];
				$datarows = [];
				$liste = array();
                
						
						
						
					
            
			foreach($data as $row)
			{
					$datarows[] = array(
						'id' =>$row->id,
						'emp_code' =>$row->emp_code,
						'start_date' =>$row->start_date,
						'end_date' =>$row->end_date,
						'in_time' =>$row->admin_in_time,
						'out_time' =>$row->admin_out_time,
						'total_time' =>$row->admin_total_time,
						'total_ot' =>$row->admin_total_ot,
						'day_status' =>$row->admin_day_status
					);
            }	
			
			
            if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):

                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td align="center">' . $draw[$fid] . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';
			
			
			
			//$total_working_hour_array
			
			
            $content .='<br />';

            $content .='<h4>Total : ' . count($datarows) . '</h4>';


            
        }


        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($content);

// (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4');

// Render the HTML as PDF
        $dompdf->render();

// Output the generated PDF to Browser
        $dompdf->stream();
    }

	
// Audit job card report
#
    #
    #
    #
    // Audit job card report

    public function AuditexportExcel(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {


        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();

        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }


        $sqlDates = Calendar::where('company_id', $company_id)
                ->whereBetween('date', [$start_date, $end_date])
                ->groupby('calendars.date')
                ->get();

        if (!empty($sqlDates)) {
// $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;

                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.audit_in_time', 'attendance_jobcards.audit_out_time', 'attendance_jobcards.audit_total_time', 'attendance_jobcards.audit_total_ot', 'attendance_jobcards.admin_day_status'
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();

                if (count($data) != 0) {
                    $jobcard_id = $data[0]->id;
                    $jobcard_start_date = $data[0]->start_date;
                    $jobcard_end_date = $data[0]->end_date;
                    $jobcard_in_time = $data[0]->audit_in_time;
                    $jobcard_out_time = $data[0]->audit_out_time;
                    $jobcard_total_time = $data[0]->audit_total_time;
                    $jobcard_total_ot = $data[0]->audit_total_ot;
                    $jobcard_day_status = $data[0]->admin_day_status;
                } else {
                    $jobcard_id = 0;
                    $jobcard_start_date = $ld;
                    $jobcard_end_date = $ld;
                    $jobcard_in_time = "00:00:00";
                    $jobcard_out_time = "00:00:00";
                    $jobcard_total_time = "00:00:00";
                    $jobcard_total_ot = "00:00:00";
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'audit_day_status', $company_id);
                }

                $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);

                $json[] = array(
//                    'id' => $jobcard_id,
                    'start_date' => $jobcard_start_date,
                    'end_date' => $jobcard_end_date,
                    'in_time' => $jobcard_in_time,
                    'out_time' => $jobcard_out_time,
                    'total_time' => $jobcard_total_time,
                    'total_ot' => $jobcard_total_ot,
                    'day_status' => $jobcard_day_status,
                );

            endforeach;
        }


        $excelArray = [];

// Define the Excel spreadsheet headers
        $excelArray [] = [
//            'ID',
            'Start Date',
            'End Date',
            'In Time',
            'Out Time',
            'Total Working Time',
            'Total Over Time',
            'Day Status',
        ];

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
//exit();
// Generate and return the spreadsheet
        \Excel::create('Audit Jobcard Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

// Set the spreadsheet title, creator, and description
            $excel->setTitle('Audit Jobcard Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Audit Jobcard Report');

// Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function AuditexportPdf(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
        $content = '<h3>Audit Jobcard Report</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
// instantiate and use the dompdf class
        $excelArray = [
            'start_date',
            'end_date',
            'in_time',
            'out_time',
            'total_time',
            'total_ot',
            'day_status',
        ];

        if (!empty($excelArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            foreach ($excelArray as $exhead):
                $content .='<th>' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
            $emp_code = $request->emp_code;
            $start_date = $request->start_date;
            $end_date = $request->end_date;


            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();

            if (isset($sqlEmp)) {
                $company_id = $sqlEmp->company_id;
            } else {
                $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
                $company_id = $alt_company_id;
            }


            $sqlDates = Calendar::where('company_id', $company_id)
                    ->whereBetween('date', [$start_date, $end_date])
                    ->groupby('calendars.date')
                    ->get();

            if (!empty($sqlDates)) {
                $datarows = [];
                foreach ($sqlDates as $line):

                    $ld = $line->date;

                    $data = DB::table('attendance_jobcards')
                            ->select(
                                    'attendance_jobcards.id', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.audit_in_time', 'attendance_jobcards.audit_out_time', 'attendance_jobcards.audit_total_time', 'attendance_jobcards.audit_total_ot', 'attendance_jobcards.admin_day_status'
                            )
                            ->where('attendance_jobcards.emp_code', $emp_code)
                            ->where(function($q) use ($ld) {
                                $q->where('attendance_jobcards.start_date', $ld);
                            })
                            ->orderBy('attendance_jobcards.id', 'DESC')
                            ->get();

                    if (count($data) != 0) {
                        $jobcard_id = $data[0]->id;
                        $jobcard_start_date = $data[0]->start_date;
                        $jobcard_end_date = $data[0]->end_date;
                        $jobcard_in_time = $data[0]->audit_in_time;
                        $jobcard_out_time = $data[0]->audit_out_time;
                        $jobcard_total_time = $data[0]->audit_total_time;
                        $jobcard_total_ot = $data[0]->audit_total_ot;
                        $jobcard_day_status = $data[0]->admin_day_status;
                    } else {
                        $jobcard_id = 0;
                        $jobcard_start_date = $ld;
                        $jobcard_end_date = $ld;
                        $jobcard_in_time = "00:00:00";
                        $jobcard_out_time = "00:00:00";
                        $jobcard_total_time = "00:00:00";
                        $jobcard_total_ot = "00:00:00";
                        $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'audit_day_status', $company_id);
                    }

                    $this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);

                    $datarows[] = array(
                        'start_date' => $jobcard_start_date,
                        'end_date' => $jobcard_end_date,
                        'in_time' => $jobcard_in_time,
                        'out_time' => $jobcard_out_time,
                        'total_time' => $jobcard_total_time,
                        'total_ot' => $jobcard_total_ot,
                        'day_status' => $jobcard_day_status,
                    );

                endforeach;
            }
            if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):

                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td>' . $draw[$fid] . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';

            $content .='<br />';

            $content .='<h4>Total : ' . count($datarows) . '</h4>';


            $content .='<br /><br /><br /><table border="0" width="100%">';
            $content .='<tr>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
            $content .='</tr>';


            $content .='</table>';
        }


        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($content);

// (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
        $dompdf->render();

// Output the generated PDF to Browser
        $dompdf->stream();
    }

// User job card report
#
    #
    #
    #
    // User job card report

    public function LateINexportExcel(Request $request, $company_id = 0, $start_date = 0, $end_date = 0) {


        $company_id = $request->company_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

		
		if(empty($start_date))
		{
			$start_date = date('Y-m-d');
		}
		
		if(empty($end_date))
		{
			$end_date = date('Y-m-d');
		}
		
		$dateBetween=array($start_date,$end_date);

		$data = DB::table('attendance_jobcards')
				->join('employee_infos','attendance_jobcards.emp_code','=','employee_infos.emp_code')
				->select(
						'attendance_jobcards.id', 
						'attendance_jobcards.emp_code', 
						'attendance_jobcards.start_date', 
						'attendance_jobcards.end_date', 
						'attendance_jobcards.admin_in_time', 
						'attendance_jobcards.admin_out_time', 
						'attendance_jobcards.admin_total_time', 
						'attendance_jobcards.admin_total_ot', 
						'attendance_jobcards.admin_day_status'
				)
				->where('employee_infos.company_id', $company_id)
				->where('attendance_jobcards.admin_day_status','Late IN')
				->whereBetween('attendance_jobcards.start_date',$dateBetween)
				->orderBy('attendance_jobcards.start_date', 'ASC')
				->get();
        
		foreach($data as $row)
		{
				$json[] = array(
					'id' =>$row->id,
					'emp_code' =>$row->emp_code,
					'start_date' =>$row->start_date,
					'end_date' =>$row->end_date,
					'in_time' =>$row->admin_in_time,
					'out_time' =>$row->admin_out_time,
					'total_time' =>$row->admin_total_time,
					'total_ot' =>$row->admin_total_ot,
					'day_status' =>$row->admin_day_status
				);
		}	
                

           


        $excelArray = [];

// Define the Excel spreadsheet headers
        $excelArray [] = [
//            'ID',
            'Start Date',
            'End Date',
            'In Time',
            'Out Time',
            'Total Working Time',
            'Total Over Time',
            'Day Status',
        ];

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
//exit();
// Generate and return the spreadsheet
        \Excel::create('Late IN Jobcard Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

// Set the spreadsheet title, creator, and description
            $excel->setTitle('Late IN Jobcard Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Late IN Jobcard Report');

// Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

	public function UserexportExcel(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {


        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();

        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }


        $sqlDates = Calendar::where('calendars.company_id', $company_id)
				->leftJoin('day_types','calendars.day_type_id','=','day_types.id')
				->select(DB::Raw('calendars.*,day_types.day_short_code'))
                ->whereBetween('calendars.date', [$start_date, $end_date])
                ->groupby('calendars.date')
                ->get();

        if (!empty($sqlDates)) {
// $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;

                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.start_date', 
								'attendance_jobcards.end_date', 
								'attendance_jobcards.user_end_date', 
								'attendance_jobcards.user_in_time', 'attendance_jobcards.user_out_time', 'attendance_jobcards.user_total_time', 'attendance_jobcards.user_total_ot', 'attendance_jobcards.admin_day_status','attendance_jobcards.ll_ref'
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();
					$cal_day_type=$line->day_short_code;
                if (count($data) != 0) {
                    $jobcard_id = $data[0]->id;
                    $jobcard_start_date = $data[0]->start_date;
                    $jobcard_end_date = $data[0]->end_date;
					$jobcard_user_end_date = $data[0]->user_end_date;
                    $jobcard_in_time = $data[0]->user_in_time;
                    $jobcard_out_time = $data[0]->user_out_time;
                    $jobcard_total_time = $data[0]->user_total_time;
                    $jobcard_total_ot = $data[0]->user_total_ot;
                    $jobcard_day_status = $data[0]->admin_day_status;
					
					
					$dayArray = array("A", "H", "P", "W");
					if(!empty($jobcard_user_end_date))
					{
						if($jobcard_user_end_date!='0000-00-00')
						{
							$jobcard_end_date = $jobcard_user_end_date;
						}
					}
					
					if (in_array($jobcard_day_status, $dayArray)) {
						$jobcard_day_status = $this->ManualJobCardEntryCheck($line->user_day_status, $ld, $emp_code, 'audit_day_status', $company_id);
					} 
					elseif(!empty($jobcard_in_time) || !empty($jobcard_out_time))
					{

						
						$chkHalfDayLeave=$this->HalfDayLeaveCheck($jobcard_start_date,$company_id,$jobcard_emp_code);
						if($chkHalfDayLeave==1)
						{

							$jobcard_day_status=$line->day_short_code;
							$tablJob=AttendanceJobcard::find($jobcard_id);
							$tablJob->user_day_status=$jobcard_day_status;
							$tablJob->admin_day_status=$jobcard_day_status;
							$tablJob->audit_day_status=$jobcard_day_status;
							$tablJob->save();
							
							
						}
						else
						{
							
							$jobcard_day_status = $line->user_day_status;
							
						}
						
						
						
						
					}
					else {
						
						$jobcard_day_status = $line->user_day_status;
					}
					
					$stdDay=array("W","H");
					
					if(in_array($jobcard_day_status,$stdDay) || in_array($line->day_short_code,$stdDay))
					{
						if($isOTElg==1)
						{
							//$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
							
							$this->StandardOTPlace($jobcard_emp_code,$jobcard_start_date,$jobcard_admin_out_time,$jobcard_admin_total_ot,$jobcard_total_ot);
						
							$tab=AttendanceJobcard::find($jobcard_id);
							$jobcard_out_time = $tab->user_out_time;
							$jobcard_total_time = $tab->user_total_time;
							$jobcard_total_ot = $tab->user_total_ot;
							$jobcard_day_status = $tab->user_day_status;
						}
						
						
					}
					elseif(in_array($jobcard_day_status,array("P","Late IN","Late OUT")))
					{
						if($jobcard_total_ot>='02:00:00')
						{
							
							$jobcard_total_ot='02:00:00';
							$tab=AttendanceJobcard::find($jobcard_id);
							$tab->user_total_ot=$jobcard_total_ot;
							$tab->save();
							
						}
					}
					
					if($isOTElg==1)
					{
						$tab=AttendanceJobcard::find($jobcard_id);
						$tab->user_total_ot=$jobcard_total_ot;
						$tab->save();
					}
					else
					{
						$jobcard_total_ot='00:00:00';
					}
					
					if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
					{
						$jobcard_day_status=$cal_day_type;
					}
					
					
                } else {
                    $jobcard_id = 0;
                    $jobcard_start_date = $ld;
                    $jobcard_end_date = $ld;
                    $jobcard_in_time = "00:00:00";
                    $jobcard_out_time = "00:00:00";
                    $jobcard_total_time = "00:00:00";
                    $jobcard_total_ot = "00:00:00";
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                }
				
				

                //$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
				
				if(!empty($jobcard_user_end_date))
				{
					if($jobcard_user_end_date!='0000-00-00')
					{
						$jobcard_end_date = $jobcard_user_end_date;
					}
				}
				
				$timecheckByDay=array("W","H","P");
				if(!in_array($jobcard_day_status,$timecheckByDay))
				{
					$jobcard_total_time='00:00:00';
					$jobcard_in_time='00:00:00';
					$jobcard_out_time='00:00:00';
					$jobcard_total_ot='00:00:00';
				}
				
				if(in_array($jobcard_day_status,array("W","H")))
				{
					if($ld>='2017-07-01')
					{
						$ll_flag=0;
						if(!empty($data[0]->ll_ref))
						{
							
							if($data[0]->ll_ref=="0000-00-00")
							{
								$ll_flag=0;
							}
							else
							{
								$ll_flag=1;
							}
						}
						
						if($ll_flag==0)
						{
							$jobcard_total_time='00:00:00';
							$jobcard_in_time='00:00:00';
							$jobcard_out_time='00:00:00';
							$jobcard_total_ot='00:00:00';
						}
					}
				}

                $json[] = array(
//                    'id' => $jobcard_id,
                    'start_date' => $jobcard_start_date,
                    'end_date' => $jobcard_end_date,
                    'in_time' => $jobcard_in_time,
                    'out_time' => $jobcard_out_time,
                    'total_time' => $jobcard_total_time,
                    'total_ot' => $jobcard_total_ot,
                    'day_status' => $jobcard_day_status,
                );

            endforeach;
        }


        $excelArray = [];

// Define the Excel spreadsheet headers
        $excelArray [] = [
//            'ID',
            'Start Date',
            'End Date',
            'In Time',
            'Out Time',
            'Total Working Time',
            'Total Over Time',
            'Day Status',
        ];

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
//exit();
// Generate and return the spreadsheet
        \Excel::create('User Jobcard Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

// Set the spreadsheet title, creator, and description
            $excel->setTitle('User Jobcard Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('User Jobcard Report');

// Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

	
	
    public function UserexportPdf(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
        
		
		//ex code 
		
		$emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $sqlEmp = DB::table('employee_infos')
				  ->leftJoin('employee_departments','employee_infos.emp_code','=','employee_departments.emp_code')
				  ->leftJoin('departments','employee_departments.department_id','=','departments.id')
				  ->leftJoin('employee_designations','employee_infos.emp_code','=','employee_designations.emp_code')
				  ->leftJoin('designations','employee_designations.designation_id','=','designations.id')
				  ->select('employee_infos.id',
						   'employee_infos.first_name',
						   'employee_infos.last_name',
						   'employee_infos.company_id',
						   'employee_infos.join_date',
						   DB::Raw('departments.name as depName'),
						   DB::Raw('designations.name as desName'))
				  ->where('employee_infos.emp_code', $emp_code)->first();
		$joinDate=$sqlEmp->join_date;
		
		$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
        
        if (isset($sqlEmp)) {
            $company_id = $sqlEmp->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }
		
		if(empty($cal_company_id))
		{
			$cal_company_id=$company_id;
		}
		
		$sqlcompanyName=Company::find($company_id);
		$COmpName=$sqlcompanyName->name;
		$depName=$sqlEmp->depName;
		$desName=$sqlEmp->desName;
		$eMPNamefULL=$sqlEmp->first_name;
		if(!empty($sqlEmp->last_name))
		{
			$eMPNamefULL .=" ".$sqlEmp->last_name;
		}


        $sqlDates = Calendar::where('calendars.company_id', $cal_company_id)
				->Join('day_types','calendars.day_type_id','=','day_types.id')
				->leftjoin('attendance_jobcards', function ($join) use ($emp_code) {
                            $join->on('calendars.date', '=', 'attendance_jobcards.start_date')
                                 ->where('attendance_jobcards.emp_code', '=',$emp_code);
                })
                ->whereBetween('date', [$start_date, $end_date])
				->select('calendars.date','day_types.day_short_code',
				'attendance_jobcards.id', 'attendance_jobcards.emp_code', 
				'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.user_end_date', 
				'attendance_jobcards.user_in_time', 'attendance_jobcards.user_out_time', 
				'attendance_jobcards.user_total_time', 'attendance_jobcards.user_total_ot', 
				'attendance_jobcards.user_day_status',
								'attendance_jobcards.admin_out_time', 
								'attendance_jobcards.admin_total_ot', 
								'attendance_jobcards.ll_ref')
                ->groupby('calendars.date')
                ->get();
		
		
				
		$chkstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->count();
		if($chkstaff_grades==0)
		{
			$isOTElg=0;
		}
		else
		{
				$sqlstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)
                ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')
				->select('staff_grades.is_ot_eligible')
                ->first();
				if($sqlstaff_grades->is_ot_eligible==1)	
				{
					$isOTElg=1;
				}
				else
				{
					$isOTElg=0;
				}
		}		
		
		
		$content = '<h3 align="center">'.$COmpName.'</h3>';
		$content .='<h5></h5>';
		$content .='<div><b>Code : ' . $emp_code . '</b></div>';
		$content .='<div><b>Name : ' . $eMPNamefULL . '</b></div>';
		$content .='<div><b>Department : ' . $depName . '</b></div>';
		$content .='<div><b>Designation : ' . $desName . '</b></div>';
		$content .='<div><b>Date of Join : ' . $joinDate . '</b></div>';
		$content .='<h5></h5>';
		/*
		Code: RPAC1240
		Full Name: Md. Ashraful Islam
		Department: Production
		Designation: Operator - Printed Label Production
		Date of Join: 2014-09-09
		*/
		
// instantiate and use the dompdf class
        $excelArray = [
            'start_date',
            'in_time',
			'end_date',
            'out_time',
            'total_ot',
            'day_status',
        ];
		

        if (!empty($excelArray)) {
            $content .='<table width="100%" cellpadding="0" align="center">';
            $content .='<thead>';
            $content .='<tr>';
            foreach ($excelArray as $exhead):
				if($exhead=='start_date' || $exhead=='end_date')
				{
					$exhead='Date';
				}
				elseif($exhead=='in_time')
				{
					$exhead='In Time';
				}
				elseif($exhead=='out_time')
				{
					$exhead='Out Time';
				}
				elseif($exhead=='total_ot')
				{
					$exhead='Total OT';
				}
				elseif($exhead=='day_status')
				{
					$exhead='Status';
				}
                $content .='<th align="center">' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
		
		
			$total_working_hour_array=array();
			if (!empty($sqlDates)) {
				$json = [];
				$datarows = [];
				$liste = array();
				foreach ($sqlDates as $line):

					$ld = $line->date;
					//exit();
					/*$data = DB::table('attendance_jobcards')
							->select(
									'attendance_jobcards.id', 'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.user_in_time', 'attendance_jobcards.user_out_time', 'attendance_jobcards.user_total_time', 'attendance_jobcards.user_total_ot', 'attendance_jobcards.user_day_status',
									'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_ot' 
							)
							->where('attendance_jobcards.emp_code', $emp_code)
							->where(function($q) use ($ld) {
								$q->where('attendance_jobcards.start_date', $ld);
							})
							->orderBy('attendance_jobcards.id', 'DESC')
							->get();*/
					$cal_day_type=$line->day_short_code;
					if (isset($line->id)) {
						//echo "Geeting Data";
					
						//exit();
						$jobcard_id = $line->id;
						$jobcard_emp_code = $line->emp_code;
						$jobcard_start_date = $line->start_date;
						$jobcard_end_date = $line->end_date;
						$jobcard_user_end_date = $line->user_end_date;
						$jobcard_in_time = $line->user_in_time;
						$jobcard_out_time = $line->user_out_time;
						$jobcard_admin_out_time = $line->admin_out_time;
						$jobcard_admin_total_ot = $line->admin_total_ot;
						$jobcard_total_time = $line->user_total_time;
						$jobcard_total_ot = $line->user_total_ot;
						$jobcard_day_status = $line->user_day_status;
						$dayArray = array("A", "H", "P", "W");
						
						
						if(!empty($jobcard_user_end_date))
						{
							if($jobcard_user_end_date!='0000-00-00')
							{
								$jobcard_end_date = $jobcard_user_end_date;
							}
						}
						
						if (in_array($jobcard_day_status, $dayArray)) {
							$jobcard_day_status = $this->ManualJobCardEntryCheck($line->user_day_status, $ld, $emp_code, 'audit_day_status', $company_id);
						} 
						elseif(!empty($jobcard_in_time) || !empty($jobcard_out_time))
						{

							
							$chkHalfDayLeave=$this->HalfDayLeaveCheck($jobcard_start_date,$company_id,$jobcard_emp_code);
							if($chkHalfDayLeave==1)
							{

								$jobcard_day_status=$line->day_short_code;
								$tablJob=AttendanceJobcard::find($jobcard_id);
								$tablJob->user_day_status=$jobcard_day_status;
								$tablJob->admin_day_status=$jobcard_day_status;
								$tablJob->audit_day_status=$jobcard_day_status;
								$tablJob->save();
								
								
							}
							else
							{
								
								$jobcard_day_status = $line->user_day_status;
								
							}
							
							
							
							
						}
						else {
							
							$jobcard_day_status = $line->user_day_status;
						}
						
						$stdDay=array("W","H");
						
						if(in_array($jobcard_day_status,$stdDay) || in_array($line->day_short_code,$stdDay))
						{
							if($isOTElg==1)
							{
								//$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
								
								$this->StandardOTPlace($jobcard_emp_code,$jobcard_start_date,$jobcard_admin_out_time,$jobcard_admin_total_ot,$jobcard_total_ot);
							
								$tab=AttendanceJobcard::find($jobcard_id);
								$jobcard_out_time = $tab->user_out_time;
								$jobcard_total_time = $tab->user_total_time;
								$jobcard_total_ot = $tab->user_total_ot;
								$jobcard_day_status = $tab->user_day_status;
							}
							
							
						}
						elseif(in_array($jobcard_day_status,array("P","Late IN","Late OUT")))
						{
							if($jobcard_total_ot>='02:00:00')
							{
								
								$jobcard_total_ot='02:00:00';
								$tab=AttendanceJobcard::find($jobcard_id);
								$tab->user_total_ot=$jobcard_total_ot;
								$tab->save();
								
							}
						}
						
						if($isOTElg==1)
						{
							$tab=AttendanceJobcard::find($jobcard_id);
							$tab->user_total_ot=$jobcard_total_ot;
							$tab->save();
						}
						else
						{
							$jobcard_total_ot='00:00:00';
						}
						
						if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
						{
							$jobcard_day_status=$cal_day_type;
						}
						
						//$jobcard_day_status = $this->ManualJobCardEntryCheck($data[0]->user_day_status, $ld, $emp_code, 'user_day_status', $company_id);
					} else {
						//echo "Not Geeting Data";
					
						//exit();
						
						$jobcard_id = 0;
						$jobcard_emp_code = 0;
						$jobcard_start_date = $ld;
						$jobcard_end_date = $ld;
						$jobcard_in_time = "00:00:00";
						$jobcard_out_time = "00:00:00";
						$jobcard_total_time = "00:00:00";
						$jobcard_total_ot = "00:00:00";
						$jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'user_day_status', $company_id);
					}

					//$this->WeekendOT($jobcard_id, $jobcard_day_status, $jobcard_total_time, $jobcard_out_time, $company_id);
					
					
					
					
					if(empty($jobcard_in_time))
					{
						$jobcard_in_time='00:00:00';
					}
					
					if(empty($jobcard_out_time))
					{
						$jobcard_out_time='00:00:00';
					}
					
					
					if($jobcard_in_time=='00:00:00' || $jobcard_out_time=='00:00:00')
					{
						$jobcard_total_ot='00:00:00';
					}
					
					if(empty($jobcard_end_date))
					{
						$jobcard_end_date=$jobcard_start_date;
					}
					
					
					if($jobcard_in_time=='00:00:00' && $jobcard_out_time=='00:00:00')
					{
						
						
					}
					else
					{
							if($jobcard_day_status=='A')
							{
								$jobcard_day_status='P';
							}
						
					}
					
					if(empty($jobcard_total_ot))
					{
						$jobcard_total_ot='00:00:00';
					}
					
					if($isOTElg==0)
					{
						$jobcard_total_ot='00:00:00';
					}
					
					$timecheckByDay=array("W","H","P","Late IN","Late OUT");
					if(!in_array($jobcard_day_status,$timecheckByDay))
					{
						$jobcard_total_time='00:00:00';
						$jobcard_in_time='00:00:00';
						$jobcard_out_time='00:00:00';
						$jobcard_total_ot='00:00:00';
					}
					
					if(in_array($jobcard_day_status,array("W","H")))
					{
						if($ld>='2017-07-01')
						{
							$ll_flag=0;
							if(!empty($line->ll_ref))
							{
								
								if($line->ll_ref=="0000-00-00")
								{
									$ll_flag=0;
								}
								else
								{
									$ll_flag=1;
								}
							}
							
							if($ll_flag==0)
							{
								$jobcard_total_time='00:00:00';
								$jobcard_in_time='00:00:00';
								$jobcard_out_time='00:00:00';
								$jobcard_total_ot='00:00:00';
							}
						}
					}
					
					$datarows[] = array(
                        'start_date' => $jobcard_start_date,
                        'in_time' => $jobcard_in_time,
						'end_date' => $jobcard_end_date,
                        'out_time' => $jobcard_out_time,
                        'total_ot' => $jobcard_total_ot,
                        'day_status' => $jobcard_day_status,
                    );
					
					
					
					array_push($total_working_hour_array,$jobcard_total_ot);
					array_push($liste,$jobcard_day_status);
					

				endforeach;
			}
			
			//echo "<pre>";
			//print_r($liste);
			//exit();
			
			
			
			if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):

                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td align="center">' . $draw[$fid] . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';
			
			$arrayUnique=array_count_values($liste);
			//print_r($arrayUnique);
			foreach($arrayUnique as $key=>$unq):
				$content .='<span style="border:1px #ccc solid; padding:10px; margin-left:3px; line-height:20px;">'.$key.'-'.$unq.'</span> ';
			endforeach;
			//exit();
			if($isOTElg==1)
			{
				$content .='<span style="border:1px #ccc solid; padding:10px; margin-left:3px; line-height:20px;">OT Total - '.$this->SumAllPDFTime($total_working_hour_array).'</span> ';
			}
			//$total_working_hour_array
			
			//echo $this->SumAllPDFTime($total_working_hour_array);
			//exit();
            $content .='<h4>Total : ' . count($datarows) . '</h4>';


            
			
		
		}
		
		//excode
		
		
		
			
			
            
        


        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($content);

// (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4');

// Render the HTML as PDF
        $dompdf->render();

// Output the generated PDF to Browser
        $dompdf->stream();
    }
	
	function SumAllPDFTime($a) {
    $array =$a;
    $totalTimeSecs = 0;
    foreach ($array as $time) { // Loop outer array
      list($hours,$mins,$secs) = explode(':',$time); // Split into H:m:s
      $totalTimeSecs += (int) ltrim($secs,'0'); // Add seconds to total
      $totalTimeSecs += ((int) ltrim($mins,'0')) * 60; // Add minutes to total
      $totalTimeSecs += ((int) ltrim($hours,'0')) * 3600; // Add hours to total
    }

    $hours = str_pad(floor($totalTimeSecs / 3600),2,'0',STR_PAD_LEFT);
    $mins = str_pad(floor(($totalTimeSecs % 3600) / 60),2,'0',STR_PAD_LEFT);
    $secs = str_pad($totalTimeSecs % 60,2,'0',STR_PAD_LEFT);
    return "$hours:$mins:$secs";
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

    public function dayStatus() {
        $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        $dayStatus = '';
        if (!empty($alt_company_id)) {
            $leave = DB::select("SELECT leave_short_code as day_short_code FROM `leave_policies`");
            $day_short_code = DB::select("SELECT day_short_code FROM `day_types` WHERE company_id=$alt_company_id");
            $dayStatus = array_merge($day_short_code, $leave);
        } else {
            $leave = DB::select("SELECT leave_short_code as day_short_code FROM `leave_policies`");
            $day_short_code = DB::select("SELECT day_short_code FROM `day_types`");
            $dayStatus = array_merge($day_short_code, $leave);
        }
        return response()->json($dayStatus);
// return $dayStatus;
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
    private function getTime($time, $inctime = true) {
        date_default_timezone_set('Asia/Dhaka');
        $chkdtarr = explode("GMT", $time);
        $newdtime = strtotime($chkdtarr[0]);
        $NdateTime = date("Y-m-d H:i:s", $newdtime);
        $int = strtotime(substr($NdateTime, 0, 24));
        $tm = ($inctime) ? ' H:i:s' : '';
        return date("$tm", $int);
    }

    private function getDate($date) {
        date_default_timezone_set('Asia/Dhaka');
        $chkdtarr = explode("GMT", $date);
        $new = strtotime($chkdtarr[0]);
        $Ndate = date("Y-m-d", $new);
        return $Ndate;
    }

    private function EmptyNInsertNewGeneral($jobcard_id = 0, $day_status = 'A', $start_date = '0000-00-00', $emp_code = '0000', $company_id = '0', $in_time = '00:00:00', $out_time = '00:00:00', $total_time = '00:00:00', $total_ot = '00:00:00') {
        $edited_user_id = app('App\Http\Controllers\MenuPageController')->loggedUser('user_id');

        $this->ExLeaveRemainAdjust($start_date, $emp_code);

        $chkManualReplace = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->count();
        if ($chkManualReplace == 0) {
            $tab = new ManualJobcardEntry();
            $tab->day_type = $day_status;
            $tab->date = $start_date;
            $tab->emp_code = $emp_code;
            $tab->company_id = $company_id;
            $tab->save();
        } else {
            ManualJobcardEntry::where('date', $start_date)
                    ->where('emp_code', $emp_code)
                    ->update(['day_type' => $day_status]);
        }


        $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
        if ($chkjobCard == 0) {
            $tab = new AttendanceJobcard();
            $tab->start_date = $start_date;
            $tab->emp_code = $emp_code;
            $tab->company_id = $company_id;
            $tab->admin_in_time = $in_time;
            $tab->admin_out_time = $out_time;
            $tab->admin_total_time = $total_time;
            $tab->admin_total_ot = $total_ot;
            $tab->admin_day_status = $day_status;
            $tab->user_day_status = $day_status;
            $tab->audit_day_status = $day_status;
            $tab->save();
        } else {

            //echo "WORKING".$day_status;
			
			
            $tab = AttendanceJobcard::find($jobcard_id);
            $tab->admin_in_time = $in_time;
            $tab->admin_out_time = $out_time;
            $tab->admin_total_time = $total_time;
            $tab->admin_total_ot = $total_ot;
            $tab->admin_day_status = $day_status;
            $tab->user_day_status = $day_status;
            $tab->audit_day_status = $day_status;
            $tab->edit_flag = '1';
            $tab->edited_emp_code = $edited_user_id;
            $tab->save();
            $day_array = array("W", "H");
            if (in_array($day_status, $day_array)) {
                $this->WeekendOT($jobcard_id, $day_status, $total_time, $out_time, $company_id);
            }
        }

        //exit();

        return 1;
    }

    private function EmptyNInsertNewLeave($jobcard_id = 0, $day_status = 'A', $start_date = '0000-00-00', $emp_code = '0000', $company_id = '0', $in_time = '00:00:00', $out_time = '00:00:00', $total_time = '00:00:00', $total_ot = '00:00:00') {
        $edited_user_id = app('App\Http\Controllers\MenuPageController')->loggedUser('user_id');

        $LeaveType = LeavePolicy::where('leave_short_code', $day_status)->first();
//echo "<pre>";
//print_r($LeaveType);
//exit();


        $empLeaveBalanceChk = LeaveAssignedYearlyData::where('emp_code', $emp_code)
                ->where('leave_policy_id', $LeaveType->id)
                ->where('year', date('Y'))
                ->count();
        if ($empLeaveBalanceChk == 0) {
            return 0;
        } else {
            $empLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)
                    ->where('leave_policy_id', $LeaveType->id)
                    ->where('year', date('Y'))
                    ->first();
            if ($empLeaveBalance->remaining_days<0) {
                return 2;
            } else {
                $newAvailDays = $empLeaveBalance->availed_days + 1;
                $newLeaveRemain = $empLeaveBalance->remaining_days - 1;
                if ($newLeaveRemain >= 0) {

                    $tabLeave = LeaveAssignedYearlyData::find($empLeaveBalance->id);
                    $tabLeave->availed_days = $newAvailDays;
                    $tabLeave->remaining_days = $newLeaveRemain;
                    $tabLeave->save();

                    $this->ExLeaveRemainAdjust($start_date, $emp_code);

                    $chkManualReplace = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->count();
                    if ($chkManualReplace == 0) {
                        $tab = new ManualJobcardEntry();
                        $tab->day_type = $day_status;
                        $tab->date = $start_date;
                        $tab->emp_code = $emp_code;
                        $tab->company_id = $company_id;
                        $tab->save();
                    } else {
                        ManualJobcardEntry::where('date', $start_date)
                                ->where('emp_code', $emp_code)
                                ->update(['day_type' => $day_status]);
                    }

                    $tab = AttendanceJobcard::find($jobcard_id);
                    $tab->admin_in_time = "00:00:00";
                    $tab->admin_out_time = "00:00:00";
                    $tab->admin_total_time = "00:00:00";
                    $tab->admin_total_ot = "00:00:00";
                    $tab->admin_day_status = $day_status;
                    $tab->user_day_status = $day_status;
                    $tab->audit_day_status = $day_status;
                    $tab->edit_flag = '1';
                    $tab->edited_emp_code = $edited_user_id;
                    $tab->save();


                    return 1;
                }
            }
        }
    }

    private function ExLeaveRemainAdjust($start_date = '0000-00-00', $emp_code = '0') {
        $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
        if ($chkjobCard != 0) {
            $getExJobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
            $day_array = array("W", "H", "P", "A");
            if (!in_array($getExJobCard->admin_day_status, $day_array)) {
                //if ($getExJobCard->admin_day_status != "W" && $getExJobCard->admin_day_status != "H" && $getExJobCard->admin_day_status != "A" && $getExJobCard->admin_day_status != "P") {
                $LeaveTypeEx = LeavePolicy::where('leave_short_code', $getExJobCard->admin_day_status)->first();
				if(isset($LeaveTypeEx))
				{
					$empLeaveBalanceEx = LeaveAssignedYearlyData::where('emp_code', $emp_code)
                        ->where('leave_policy_id', $LeaveTypeEx->id)
                        ->where('year', date('Y'))
                        ->first();
						

					$ExAvailDays = $empLeaveBalanceEx->availed_days - 1;
					$ExLeaveRemain = $empLeaveBalanceEx->remaining_days + 1;

					$tabExUpLeave = LeaveAssignedYearlyData::find($empLeaveBalanceEx->id);
					$tabExUpLeave->availed_days = $ExAvailDays;
					$tabExUpLeave->remaining_days = $ExLeaveRemain;
					$tabExUpLeave->save();
				}
                
            }
        }

        return 1;
    }

    public function Adminupdate(Request $request) {
        $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        $edited_user_id = app('App\Http\Controllers\MenuPageController')->loggedUser('user_id');
        $retValue=1;
		foreach ($request->models as $key => $value):

            $currentYear = date('Y');
            $start_date = $this->getDate($value['start_date']);
            $end_date = $this->getDate($value['end_date']);
            $emp_code = $value['emp_code'];
            
            $in_time=$value['in_time']?$this->getTime($value['in_time'], true):'00:00:00';
            $out_time=$value['out_time']?$this->getTime($value['out_time'], true):'00:00:00';
            $total_time=$value['total_time']?$this->getTime($value['total_time'], true):'00:00:00';
			$jobcard_id = $value['id'];
			
			$tabJob=AttendanceJobcard::find($jobcard_id);
			$tabJob->end_date=$end_date;
			$tabJob->save();
			
			
			
            if($in_time=='00:00:00' || $out_time=='00:00:00')
			{
				$total_ot='00:00:00';

			}
			else
			{
				$total_ot=$value['total_ot']?$this->getTime($value['total_ot'], true):'00:00:00';

			}
			
			
            
            $day_status = $value['day_status'];
			if($day_status=="A")
			{
				$total_ot='00:00:00';
				$in_time='00:00:00';
				$out_time='00:00:00';
			}
            
			
			if($in_time!='' && $out_time!='')
			{
				if($in_time!='00:00:00' && $out_time!='00:00:00')
				{
					$total_ot=$this->WeekendOTWithGeneralShift($in_time,$out_time,$jobcard_id,$day_status,$total_time, $out_time, $company_id);
				}
			}
            
			
            $chkExDay = AttendanceJobcard::where('start_date', $start_date)
                    ->where('emp_code', $emp_code)
                    ->count();

            if ($chkExDay != 0) {
                $ExDay = AttendanceJobcard::where('start_date', $start_date)
                        ->where('emp_code', $emp_code)
                        ->first();

                $day_array = array("W", "H", "A", "P");
                if (in_array($day_status, $day_array)) {
                    //echo "WORK";
                    $this->EmptyNInsertNewGeneral($jobcard_id, $day_status, $start_date, $emp_code, $ExDay->company_id, $in_time, $out_time, $total_time, $total_ot);
					$retValue=1;
				} else {
					app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($emp_code);
                    $lret=$this->EmptyNInsertNewLeave($jobcard_id, $day_status, $start_date, $emp_code, $ExDay->company_id, $in_time, $out_time, $total_time, $total_ot);
					if($lret==2 || $lret==0)
					{
						$retValue=3; 
					}
				}
            }


        endforeach;
        return $retValue;
    }

    public function AdminupdateSS(Request $request) {
        $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        $edited_user_id = app('App\Http\Controllers\MenuPageController')->loggedUser('user_id');
        foreach ($request->models as $key => $value) {
//echo $value['id'];
//exit();
            $currentYear = date('Y');
            $start_date = $this->getDate($value['start_date']);
            $end_date = $this->getDate($value['end_date']);
            $emp_code = $value['emp_code'];


            $in_time = $this->getTime($value['in_time'], true);
            $out_time = $this->getTime($value['out_time'], true);
            $total_time = $this->getTime($value['total_time'], true);
            $total_ot = $this->getTime($value['total_ot'], true);
            $day_status = $value['day_status'];


            $chkExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->count();
            if ($chkExDay == 0) {
                if ($day_status == 'W' || $day_status == 'P' || $day_status == 'H' || $day_status == 'A') {
                    $tab = new ManualJobcardEntry();
                    $tab->day_type = $day_status;
                    $tab->date = $start_date;
                    $tab->emp_code = $emp_code;
                    $tab->company_id = $company_id;
                    $tab->save();

                    $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
                    if ($chkjobCard == 0) {
                        $tab = new AttendanceJobcard();
                        $tab->start_date = $start_date;
                        $tab->emp_code = $emp_code;
                        $tab->company_id = $company_id;
                        $tab->admin_in_time = $in_time;
                        $tab->admin_out_time = $out_time;
                        $tab->admin_total_time = $total_time;
                        $tab->admin_total_ot = $total_ot;
                        $tab->admin_day_status = $day_status;
                        $tab->user_day_status = $day_status;
                        $tab->audit_day_status = $day_status;
                        $tab->save();
                    } else {
                        $tab = AttendanceJobcard::find($value['id']);
                        $tab->admin_in_time = $in_time;
                        $tab->admin_out_time = $out_time;
                        $tab->admin_total_time = $total_time;
                        $tab->admin_total_ot = $total_ot;
                        $tab->admin_day_status = $day_status;
                        $tab->user_day_status = $day_status;
                        $tab->audit_day_status = $day_status;
                        $tab->edit_flag = '1';
                        $tab->edited_emp_code = $edited_user_id;
                        $tab->save();
                    }
                } else {
                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                    if (!empty($chkLeaveBalance)) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();

                        if (!empty($chkLeaveBalance->remaining_days)) {
                            $chkDuplicate = ManualJobCardEntry::where('emp_code', $emp_code)->where('date', $start_date)->count();
                            if ($chkDuplicate == 0) {

                                $manulaJob = new ManualJobCardEntry();
                                $manulaJob->company_id = $company_id;
                                $manulaJob->emp_code = $emp_code;
                                $manulaJob->day_type = $day_status;
                                $manulaJob->date = $start_date;
                                $manulaJob->save();
                                if ($manulaJob->save() == 1) {
                                    $pre_availed = $chkLeaveBalance->availed_days;
                                    $new_availed = ($pre_availed + 1);
                                    $pre_rem = $chkLeaveBalance->remaining_days;
                                    $new_rem = ($pre_rem - 1);

                                    $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

//  return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Added Successfully');
                                }

                                $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
                                if ($chkjobCard == 0) {
                                    $tab = new AttendanceJobcard();
                                    $tab->start_date = $start_date;
                                    $tab->emp_code = $emp_code;
                                    $tab->company_id = $company_id;
                                    $tab->admin_in_time = $in_time;
                                    $tab->admin_out_time = $out_time;
                                    $tab->admin_total_time = $total_time;
                                    $tab->admin_total_ot = $total_ot;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                } else {
                                    $tab = AttendanceJobcard::find($value['id']);
                                    $tab->admin_in_time = $in_time;
                                    $tab->admin_out_time = $out_time;
                                    $tab->admin_total_time = $total_time;
                                    $tab->admin_total_ot = $total_ot;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_day_status = $day_status;
                                    $tab->edit_flag = '1';
                                    $tab->edited_emp_code = $edited_user_id;
                                    $tab->save();
                                }


                                return 1;
                            }
                        }
                    }
                }
            } else {

                $ExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->first();

                $daytype = $ExDay->day_type;
                if ($daytype == 'W' || $daytype == 'P' || $daytype == 'H' || $daytype == 'A') {
//   echo'if';
//                    exit();
//echo $day_status;
                    if ($day_status == 'A' || $day_status == 'P' || $day_status == 'W' || $day_status == 'H') {
// echo 'last if';
                        $tab = ManualJobcardEntry::find($ExDay->id);
                        $tab->day_type = $day_status;
                        $tab->save();
//Attendance job card update
                        $tab = AttendanceJobcard::find($value['id']);
                        $tab->admin_in_time = $in_time;
                        $tab->admin_out_time = $out_time;
                        $tab->admin_total_time = $total_time;
                        $tab->admin_total_ot = $total_ot;
                        $tab->admin_day_status = $day_status;
                        $tab->user_day_status = $day_status;
                        $tab->audit_day_status = $day_status;
                        $tab->edit_flag = '1';
                        $tab->edited_emp_code = $edited_user_id;
                        $tab->save();
                        return 1;
//                    return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated normal Successfully');
                    } else {
//   echo 'last else';


                        $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                        if (!empty($chkLeaveBalance)) {
                            $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                            if (!empty($chkLeaveBalance->remaining_days)) {
                                $pre_availed = $chkLeaveBalance->availed_days;
                                $new_availed = ($pre_availed + 1);
                                $pre_rem = $chkLeaveBalance->remaining_days;
                                $new_rem = ($pre_rem - 1);

                                $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

                                $tab = ManualJobcardEntry::find($ExDay->id);
                                $tab->day_type = $day_status;
                                $tab->save();
//Attendance job card update
                                $tab = AttendanceJobcard::find($value['id']);
                                $tab->admin_in_time = $in_time;
                                $tab->admin_out_time = $out_time;
                                $tab->admin_total_time = $total_time;
                                $tab->admin_total_ot = $total_ot;
                                $tab->admin_day_status = $day_status;
                                $tab->user_day_status = $day_status;
                                $tab->audit_day_status = $day_status;
                                $tab->edit_flag = '1';
                                $tab->edited_emp_code = $edited_user_id;
                                $tab->save();


                                return 1;
//exit();
// return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated Successfully');
                            } else {
                                return 3;

// return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Balance Found In the System For this Employee');
// exit();
                            }
                        } else {
                            return 2;
//exit();
//return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Entry Found In the System For this Employee');
                        }
                    }
                } else {
//                    echo 'else';
//  echo $day_status;
                    if ($day_status == 'W' || $day_status == 'P' || $day_status == 'H' || $day_status == 'A') {
//     echo 'if';

                        $tab = ManualJobcardEntry::find($ExDay->id);
                        $tab->day_type = $day_status;
                        $tab->save();
//Attendance job card update
                        $tab = AttendanceJobcard::find($value['id']);
                        $tab->admin_in_time = $in_time;
                        $tab->admin_out_time = $out_time;
                        $tab->admin_total_time = $total_time;
                        $tab->admin_total_ot = $total_ot;
                        $tab->admin_day_status = $day_status;
                        $tab->user_day_status = $day_status;
                        $tab->audit_day_status = $day_status;
                        $tab->edit_flag = '1';
                        $tab->edited_emp_code = $edited_user_id;
                        $tab->save();
                        $getLeavePolicyID = LeavePolicy::where('leave_short_code', $daytype)->where('company_id', $company_id)->first();
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();

                        if (!empty($chkLeaveBalance)) {
                            $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();

                            if (!empty($chkLeaveBalance->remaining_days)) {
//   echo 'last if';
                                $pre_availed = $chkLeaveBalance->availed_days;
                                $new_availed = ($pre_availed - 1);
                                $pre_rem = $chkLeaveBalance->remaining_days;
                                $new_rem = ($pre_rem + 1);

                                $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

                                return 1;
//exit();
// return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated Successfully');
                            } else {
                                return 3;

// return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Balance Found In the System For this Employee');
// exit();
                            }
                        } else {
                            return 2;
//exit();
//return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Entry Found In the System For this Employee');
                        }
// return 1;
                    } else {
// echo'else';
// exit()
                        $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                        if ($chkLeaveBalance != 0) {
                            $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                            if (!empty($chkLeaveBalance->remaining_days)) {
//  echo 'if';
                                $tab = ManualJobcardEntry::find($ExDay->id);
                                $tab->day_type = $day_status;

                                $tab->save();

//Attendance job card update
                                $tab = AttendanceJobcard::find($value['id']);
                                $tab->admin_in_time = $in_time;
                                $tab->admin_out_time = $out_time;
                                $tab->admin_total_time = $total_time;
                                $tab->admin_total_ot = $total_ot;
                                $tab->admin_day_status = $day_status;
                                $tab->user_day_status = $day_status;
                                $tab->audit_day_status = $day_status;
                                $tab->edit_flag = '1';
                                $tab->edited_emp_code = $edited_user_id;
                                $tab->save();

// if ($tab->save() == 1) {
                                $pre_availed = $chkLeaveBalance->availed_days;
                                $new_availed = ($pre_availed + 1);
                                $pre_rem = $chkLeaveBalance->remaining_days;
                                $new_rem = ($pre_rem - 1);

                                $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);
// return 1;

                                if ($LeaveBalanceUpdate) {
                                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $daytype)->where('company_id', $company_id)->first();
                                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                                    if ($chkLeaveBalance != 0) {
                                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                                        if ($chkLeaveBalance->remaining_days != 0) {
                                            $pre_availed = $chkLeaveBalance->availed_days;
                                            $new_availed = ($pre_availed - 1);
                                            $pre_rem = $chkLeaveBalance->remaining_days;
                                            $new_rem = ($pre_rem + 1);

                                            $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

                                            return 1;
// exit();
// return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated Successfully');
                                        } else {
                                            return 3;
// return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Balance Found In the System For this Employee');
// exit();
                                        }
                                    } else {
                                        return 2;
//exit();
//return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Entry Found In the System For this Employee');
                                    }
                                }
//  }
// exit();
// return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated Successfully');
                            } else {
                                return 3;

// return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Balance Found In the System For this Employee');
// exit();
                            }
                        } else {
                            return 2;
//exit();
//return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Entry Found In the System For this Employee');
                        }
                    }
                }
            }
        }
    }

    public function Userupdate(Request $request) {
        $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        foreach ($request->models as $key => $value) {


            $start_date = $this->getDate($value['start_date']);
            $end_date = $this->getDate($value['end_date']);
            $emp_code = $value['emp_code'];


            $in_time = $this->getTime($value['in_time'], true);
            $out_time = $this->getTime($value['out_time'], true);
            $total_time = $this->getTime($value['total_time'], true);
            $total_ot = $this->getTime($value['total_ot'], true);
            $day_status = $value['day_status'];


            $chkExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->count();
            if ($chkExDay == 0) {
                if ($day_status == 'W' || $day_status == 'P' || $day_status == 'H' || $day_status == 'A') {
                    $tab = new ManualJobcardEntry();
                    $tab->company_id = $company_id;
                    $tab->emp_code = $request->emp_code;
                    $tab->day_type = $day_status;
                    $tab->date = $start_date;
                    $tab->save();
                } else {
                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                    if (!empty($chkLeaveBalance)) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->first();

                        if (!empty($chkLeaveBalance->remaining_days)) {
                            $chkDuplicate = ManualJobCardEntry::where('emp_code', $emp_code)->where('date', $start_date)->count();
                            if ($chkDuplicate == 0) {

                                $manulaJob = new ManualJobCardEntry();
                                $manulaJob->company_id = $company_id;
                                $manulaJob->emp_code = $emp_code;
                                $manulaJob->day_type = $day_status;
                                $manulaJob->date = $start_date;
                                $manulaJob->save();
                                if ($manulaJob->save() == 1) {
                                    $pre_availed = $chkLeaveBalance->availed_days;
                                    $new_availed = ($pre_availed + 1);
                                    $pre_rem = $chkLeaveBalance->remaining_days;
                                    $new_rem = ($pre_rem - 1);

                                    $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

                                    return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Added Successfully');
                                }
                            }
                        }
                    }
                }
            } else {

                $ExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->first();

                $daytype = $ExDay->day_type;
                if ($daytype == 'W' || $daytype == 'P' || $daytype == 'H' || $daytype == 'A') {
//                    echo'if';
//                    exit();
                    $tab = ManualJobcardEntry::find($ExDay->id);
                    $tab->day_type = $day_status;
                    $tab->save();
                    return 1;
//                    return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated normal Successfully');
                } else {
//                    echo 'else';
// exit();
                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                    if (!empty($chkLeaveBalance)) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                        if (!empty($chkLeaveBalance->remaining_days)) {
                            $tab = ManualJobcardEntry::find($ExDay->id);
                            $tab->day_type = $day_status;
                            $tab->save();
                            if ($tab->save() == 1) {
                                $pre_availed = $chkLeaveBalance->availed_days;
                                $new_availed = ($pre_availed - 1);
                                $pre_rem = $chkLeaveBalance->remaining_days;
                                $new_rem = ($pre_rem + 1);

                                $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);
                                return 1;
// exit();
// return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated Successfully');
                            }
                        } else {
                            return 0;

// return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Balance Found In the System For this Employee');
// exit();
                        }
                    } else {
                        return 2;
//exit();
//return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Entry Found In the System For this Employee');
                    }
                }
            }

            $tab = AttendanceJobcard::find($value['id']);
            $tab->start_date = $start_date;
            $tab->end_date = $end_date;
            $tab->user_in_time = $in_time;
            $tab->user_out_time = $out_time;
            $tab->user_total_time = $total_time;
            $tab->user_total_ot = $total_ot;
            $tab->admin_day_status = $day_status;
            $tab->user_day_status = $day_status;
            $tab->audit_day_status = $day_status;
            $tab->save();

// return redirect()->action('AttendanceJobcardController@Userindex');
        }
    }

    public function Auditupdate(Request $request) {
        $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        foreach ($request->models as $key => $value) {


            $start_date = $this->getDate($value['start_date']);
            $end_date = $this->getDate($value['end_date']);
            $emp_code = $value['emp_code'];

            $in_time = $this->getTime($value['in_time'], true);
            $out_time = $this->getTime($value['out_time'], true);
            $total_time = $this->getTime($value['total_time'], true);
            $total_ot = $this->getTime($value['total_ot'], true);
            $day_status = $value['day_status'];


            $chkExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->count();
            if ($chkExDay == 0) {
                if ($day_status == 'W' || $day_status == 'P' || $day_status == 'H' || $day_status == 'A') {
                    $tab = new ManualJobcardEntry();
                    $manulaJob->company_id = $company_id;
                    $manulaJob->emp_code = $request->emp_code;
                    $manulaJob->day_type = $day_status;
                    $manulaJob->date = $start_date;
                    $tab->save();
                } else {
                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                    if (!empty($chkLeaveBalance)) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->first();

                        if (!empty($chkLeaveBalance->remaining_days)) {
                            $chkDuplicate = ManualJobCardEntry::where('emp_code', $emp_code)->where('date', $start_date)->count();
                            if ($chkDuplicate == 0) {

                                $manulaJob = new ManualJobCardEntry();
                                $manulaJob->company_id = $company_id;
                                $manulaJob->emp_code = $request->emp_code;
                                $manulaJob->day_type = $day_status;
                                $manulaJob->date = $start_date;
                                $manulaJob->save();
                                if ($manulaJob->save() == 1) {
                                    $pre_availed = $chkLeaveBalance->availed_days;
                                    $new_availed = ($pre_availed + 1);
                                    $pre_rem = $chkLeaveBalance->remaining_days;
                                    $new_rem = ($pre_rem - 1);

                                    $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);
                                    return 1;
//                                    return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Added Successfully');
                                }
                            }
                        }
                    }
                }
            } else {

                $ExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->first();

                $daytype = $ExDay->day_type;
                if ($daytype == 'W' || $daytype == 'P' || $daytype == 'H' || $daytype == 'A') {
//                    echo'if';
//                    exit();
                    $tab = ManualJobcardEntry::find($ExDay->id);
                    $tab->day_type = $day_status;
                    $tab->save();
                    return 1;
//                    return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated normal Successfully');
                } else {
//                    echo 'else';
// exit();
                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $start_date)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                    if (!empty($chkLeaveBalance)) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                        if (!empty($chkLeaveBalance->remaining_days)) {
                            $tab = ManualJobcardEntry::find($ExDay->id);
                            $tab->day_type = $day_status;
                            $tab->save();
                            if ($tab->save() == 1) {
                                $pre_availed = $chkLeaveBalance->availed_days;
                                $new_availed = ($pre_availed - 1);
                                $pre_rem = $chkLeaveBalance->remaining_days;
                                $new_rem = ($pre_rem + 1);

                                $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);
                                return 1;
// exit();
// return redirect()->action('AttendanceJobcardController@Adminindex')->with('success', 'Information Updated Successfully');
                            }
                        } else {
                            return 0;

// return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Balance Found In the System For this Employee');
// exit();
                        }
                    } else {
                        return 2;
//exit();
//return redirect()->action('AttendanceJobcardController@Adminindex')->with('error', 'NO Leave Entry Found In the System For this Employee');
                    }
                }
            }

            $tab = AttendanceJobcard::find($value['id']);
            $tab->start_date = $start_date;
            $tab->end_date = $end_date;
            $tab->audit_in_time = $in_time;
            $tab->audit_out_time = $out_time;
            $tab->audit_total_time = $total_time;
            $tab->audit_total_ot = $total_ot;
            $tab->admin_day_status = $day_status;
            $tab->user_day_status = $day_status;
            $tab->audit_day_status = $day_status;
// $tab->save();
//  return redirect()->action('AttendanceJobcardController@Auditindex');
        }
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

}
