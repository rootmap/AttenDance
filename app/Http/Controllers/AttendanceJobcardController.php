<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
//use Excel;
//use Dompdf\Dompdf;
//use Dompdf\Options;
use App\AssignEmployeeToShift;
use App\AttendanceJobcard;
use App\AttendanceJobcardPolicy;
use APP\AttendancePolicy;
use APP\EmployeeInfo;
use App\Company;
use App\Shift;
use App\Calendar;
use App\ManualJobCardEntry;
use App\AttendanceRawData;
use App\EmployeeStaffGrade;
use App\StaffGrade;
use App\LeaveApplicationMaster;
use App\ShiftMissingEmployee;

class AttendanceJobcardController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        
    }

    public function Adminindex() {
        return view('module.settings.jobcardAdmin');
    }

    public function Auditindex() {
        return view('module.settings.jobcardAudit');
    }

    public function Userindex() {
        return view('module.settings.jobcardUser');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function OTEligibleCheckEmployee($emp_code = 0, $company_id = 0) {
        $is_emp_ot_eligible = 0;
        $is_company_staffgrade = app('App\Http\Controllers\MenuPageController')->loggedUser('is_company_staffgrade');
        if (!empty($is_company_staffgrade)) {
            $Empstaff_grades = EmployeeStaffGrade::where('emp_code', $emp_code)->orderby('id', 'DESC')->first();
            if (isset($Empstaff_grades)) {
                $staff_grade = StaffGrade::where('id', $Empstaff_grades->staff_grade_id)->first();
                if (isset($staff_grade)) {
                    $is_emp_ot_eligible = $staff_grade->is_ot_eligible;
                } else {
                    $is_emp_ot_eligible = 0;
                }
            } else {
                $is_emp_ot_eligible = 0;
            }
        } else {
            $employee_ot_fid = EmployeeInfo::where('emp_code', $emp_code)->first();
            if (isset($employee_ot_fid)) {
                $is_emp_ot_eligible = $employee_ot_fid->is_ot_eligible;
            } else {
                $is_emp_ot_eligible = 0;
            }
        }

        return $is_emp_ot_eligible;
    }

    private function CalCulateTtalInTimeBetween($in_time = '00:00:00', $out_time = '00:00:00') {
        $Admin_dteStart = new \DateTime($in_time);
        $Admin_dteEnd = new \DateTime($out_time);
        $Admin_dteDiff = $Admin_dteStart->diff($Admin_dteEnd);
        $Admin_Total_WTime = $Admin_dteDiff->format("%H:%I:%S");
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

        //return $Admin_Total_WTime;
    }
	
	private function CarbonMakeOutTime($out_time,$deductTime) {
			$floatStartBuffer=explode(":",$deductTime);
            $objAdminTime = Carbon::parse($out_time);
            $objAdminTime->toDateTimeString();
            $objAdminTime->subHours($floatStartBuffer[0]);
            $objAdminTime->subMinutes($floatStartBuffer[1]);
            $objAdminTime->subSeconds($floatStartBuffer[2]);
            $retData_date = $objAdminTime->format('Y-m-d');
			$retData_time = $objAdminTime->format('H:i:s');
			
        return array($retData_date,$retData_time);
    }
	
	private function CalCulateTtalInTimeBetweenRaw($in_time = '00:00:00', $out_time = '00:00:00') {
        $Admin_dteStart = new \DateTime($in_time);
        $Admin_dteEnd = new \DateTime($out_time);
        $Admin_dteDiff = $Admin_dteStart->diff($Admin_dteEnd);
        $Admin_Total_WTime = $Admin_dteDiff->format("%H:%I:%S");
        

        return $Admin_Total_WTime;
    }

    private function CalCulateRawTtalInTimeBetweenMultipleDates($auto_start_date = '0000-00-00', $shift_start_time, $auto_end_date = '0000-00-00', $shift_end_time) {

        $make_before_time_raw = $auto_start_date . ' ' . $shift_start_time;
        $make_before_time = Carbon::parse($make_before_time_raw);
        $make_before_time->toDateTimeString();
        $make_before_time = $make_before_time->format('Y-m-d H:i:s');


        $pun_log_dt = Carbon::parse($auto_end_date . ' ' . $shift_end_time);
        $pun_log_dt->toDateTimeString();
        $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');

        $calculated_time = $this->CalculateTwoTimeHourMinSec($make_before_time, $pun_log_dt);
        $format_calculated_time = Carbon::parse($calculated_time);
        $format_calculated_time->toDateTimeString();
        $fct = $format_calculated_time->format('H:i:s');

        return $fct;


        //exit();
    }

    private function CalCulateTtalInTimeBetweenNightShift($auto_start_date = '0000-00-00', $shift_start_time, $auto_end_date = '0000-00-00', $shift_end_time) {

        $make_before_time_raw = $auto_start_date . ' ' . $shift_start_time;
        $make_before_time = Carbon::parse($make_before_time_raw);
        $make_before_time->toDateTimeString();
        $make_before_time = $make_before_time->format('Y-m-d H:i:s');


        $pun_log_dt = Carbon::parse($auto_end_date . ' ' . $shift_end_time);
        $pun_log_dt->toDateTimeString();
        $pun_log_dt->addDay();
        $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');


        //echo $make_before_time."-".$pun_log_dt;
        //exit();

        $calculated_time = $this->CalculateTwoTimeHourMinSec($make_before_time, $pun_log_dt);
        $format_calculated_time = Carbon::parse($calculated_time);
        $format_calculated_time->toDateTimeString();
        $fct = $format_calculated_time->format('H:i:s');
        if ($fct != "00:00:00") {
            $intMIn = $format_calculated_time->format('i');
            $FloorMake = floor($intMIn / 15);
            $hourMake = $format_calculated_time->format('H');
            if ($FloorMake == 0) {
                $fct = $hourMake . ":00:00";
                return $fct;
                //exit();
            } else {

                //echo $intMIn;
                $minMake = 15 * $FloorMake;
                $finalMin = str_pad($minMake, 2, '0', STR_PAD_LEFT);
                $fct = $hourMake . ":" . $finalMin . ":00";
                return $fct;
            }
        } else {
            return $fct;
        }

        //exit();
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

    private function MakeTimeDifferenceNightShift($auto_start_date, $shift_start_time, $auto_end_date, $shift_end_time) {
        $make_before_time_raw = $auto_start_date . ' ' . $shift_start_time;
        $make_before_time = Carbon::parse($make_before_time_raw);
        $make_before_time->toDateTimeString();
        $make_before_time = $make_before_time->format('Y-m-d H:i:s');


        $pun_log_dt = Carbon::parse($auto_end_date . ' ' . $shift_end_time);
        $pun_log_dt->toDateTimeString();
        $pun_log_dt->addDay();
        $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');

        $calculated_time = $this->CalculateTwoTimeHourMinSec($make_before_time, $pun_log_dt);
        $format_calculated_time = Carbon::parse($calculated_time);
        $format_calculated_time->toDateTimeString();
        $fct = $format_calculated_time->format('H:i');

        return $fct;
    }

    public function FormatHHMM($time = '00:00:00') {
        $time = date('H:i', strtotime($time));
        return $time;
    }

    private function HumanHours($strDate, $strTime, $strEDate, $strETime) {
		$convertedHour=0;
		if(!empty($strTime) && !empty($strETime))
		{
			if($strTime!='00:00:00')
			{
				if($strETime!='00:00:00')
				{
					/*$strDateTime = date('Y-m-d H:i:s', strtotime($strDate . " " . $strTime));
					$strEDateTime = date('Y-m-d H:i:s', strtotime($strEDate . " " . $strETime));

					$totalHour=$this->CalCulateTtalInTimeBetweenRaw($strDateTime,$strEDateTime);
					$convertedHour=date('H',strtotime($totalHour));*/
					
					$date1 = new \DateTime($strDate . "T" . $strTime);
					$date2 = new \DateTime($strEDate . "T" . $strETime);

					$diff = $date2->diff($date1);

					$hours = $diff->h;
					$hours = $hours + ($diff->days*24);

					$convertedHour=$hours;
				}
			}
			
		}
		
		//echo $strDate . "T" . $strTime."#".$strEDate . "T" . $strETime;
		/*
		if(intval($convertedHour)==0)
		{
			$date1 = new \DateTime($strDate . "T" . $strTime);
			$date2 = new \DateTime($strEDate . "T" . $strETime);

			$diff = $date2->diff($date1);

			$hours = $diff->h;
			$hours = $hours + ($diff->days*24);

			$convertedHour=$hours;
		}*/
		
		return $convertedHour;
    }
	private function DayBeforeMakeDay($log_date)
	{
		$obj_night_shift_day_perse = Carbon::parse($log_date);
	    $obj_night_shift_day_perse->toDateTimeString();
	    $obj_night_shift_day_perse->subDay();
	    $day_before = $obj_night_shift_day_perse->format('Y-m-d');
	 
		return $day_before;
	}
	
	private function CreateShiftMissingLog($emp_code=0,$log_date='0000-00-00',$defShiftID=0)
	{
		$pushShiftMissing=new ShiftMissingEmployee;
		$pushShiftMissing->emp_code=$emp_code;
		$pushShiftMissing->date=$log_date;
		$pushShiftMissing->shift_id=$defShiftID;
		$pushShiftMissing->review_status='Pending';
		$pushShiftMissing->save();
		return 1;
	}
    public function create() {

        ini_set('max_execution_time', 72000);

        $sql = DB::table('attendance_raw_datas')
                ->where('is_read', '0')
                //->orderby('raw_emp_code')
                ->orderby('raw_date')
                ->orderby('raw_time')
                ->get();


        if (count($sql) != 0) {

            foreach ($sql as $row):
                //print_r($row->id);

                $log_company = $row->company_id;
                $companyInfo = Company::find($log_company);
                $company_code_length = $companyInfo->emp_code_length;
                $company_prefix = $companyInfo->company_prefix;


                $log_id = $row->id;
                $log_emp_code = intval($row->raw_emp_code);
                $log_date = $row->raw_date;
                $log_time = $row->raw_time;


                //echo $log_emp_code;
                $only_code = str_pad($log_emp_code, $company_code_length, '0', STR_PAD_LEFT); //   
                //$this->zerofill($company_code_length, $incre_id);
                $emp_code = $company_prefix . $only_code;

                $is_emp_ot_eligible = $this->OTEligibleCheckEmployee($emp_code, $log_company);
//              /* OT Eligible Checking */
//                
                $chkshift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
                        ->where('start_date', '<=', $log_date)
                        ->where('end_date', '>=', $log_date)
                        ->count();
						
		
						
				//echo "<pre>";		
				//print_r($chkshift_info);		
				//exit();
				
				
				$obj_night_shift_day_perse = Carbon::parse($log_date);
				$obj_night_shift_day_perse->toDateTimeString();
				$obj_night_shift_day_perse->subDay();
				$day_before = $obj_night_shift_day_perse->format('Y-m-d');

				
						
				//if shift data not found for specific user assign him/her to default shift start operation		
				if($chkshift_info==0)
				{
					/*if shift not found then start pattern matching with database default pattern start */
					$chkSameDayJobCard = AttendanceJobcard::where('emp_code', $emp_code)
                                    ->where(function($q) use ($log_date) {
                                        $q->where('start_date', $log_date);
                                        $q->orWhere('end_date', $log_date);
                                    })->count();
					
					$chkssGetPattern=DB::select("SELECT count(id) as total FROM `shift_patterns` WHERE '".$log_time."' BETWEEN `start_in_time_pattern` AND `end_in_time_pattern`");
									 
						
					$chkGetPattern=$chkssGetPattern[0]->total;
					if($chkSameDayJobCard==0 && $chkGetPattern==1)
					{
						$sqlGetPattern=DB::select("SELECT * FROM `shift_patterns` WHERE '".$log_time."' BETWEEN `start_in_time_pattern` AND `end_in_time_pattern`");						
						$defShiftID=$sqlGetPattern[0]->shift_id;				 
					}
					elseif($chkSameDayJobCard==1 && $chkGetPattern==1)
					{
						$sqlGetPattern=DB::select("SELECT * FROM `shift_patterns` WHERE '".$log_time."' BETWEEN `start_in_time_pattern` AND `end_in_time_pattern`");
						$defShiftID=$sqlGetPattern[0]->shift_id;	
					}
					else
					{
						$sqlgetDefShift=Shift::where('name','Deafult Shift')->first();
						$defShiftID=$sqlgetDefShift->id;
					}
									 
									 
					/*if shift not found then start pattern matching with database default pattern end */
					$this->CreateShiftMissingLog($emp_code,$log_date,$defShiftID);
					
					$pullToShift=new AssignEmployeeToShift;
					$pullToShift->emp_code=$emp_code;
					$pullToShift->start_date=$log_date;
					$pullToShift->end_date=$log_date;
					$pullToShift->company_id=$row->company_id;
					$pullToShift->shift_id=$defShiftID;
					$pullToShift->save();
					
					
					$chkshift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
					->where('start_date', '<=', $log_date)
					->where('end_date', '>=', $log_date)
					->count();
				
				}
				//if shift data not found for specific user assign him/her to default shift  end operation
				
                if ($chkshift_info != 0) 
				{

                    //echo "Shift Working";
                  // exit();

                    $shift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
                            ->where('start_date', '<=', $log_date)
                            ->where('end_date', '>=', $log_date)
                            ->orderby('id', 'DESC')
                            ->get();

                    $shift_id = $shift_info[0]->shift_id;
                    $shift_data = Shift::find($shift_id);

                    $shift_type_night = $shift_data->is_night_shift;
					
					/*if($shift_type_night==0)
					{
						
						
						
						$chkJobCardDayBefore=AttendanceJobcard::where('emp_code',$emp_code)->where('start_date',$day_before)->count();
						if($chkJobCardDayBefore==1)
						{
						
							$shift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
										->where('start_date', '<=', $day_before)
										->where('end_date', '>=', $day_before)
										->orderby('id', 'DESC')
										->get();

							$shift_id = $shift_info[0]->shift_id;
							$shift_data = Shift::find($shift_id);

							$shift_type_night_day_before = $shift_data->is_night_shift;
						}
					}*/
					
					
                    $attnJobcardPolicy = AttendanceJobcardPolicy::all();
					
					//print_r($attnJobcardPolicy);
					//exit();
					
					$chkJobCardAbsentwh = AttendanceJobcard::where('emp_code', $emp_code)
									->whereIn('admin_day_status',['A','W','H'])
                                    ->where('start_date', $log_date)
                                    ->count();
									
					if($chkJobCardAbsentwh!=0)
					{
						
						
						
						$JobCardAbsentwh = AttendanceJobcard::where('emp_code', $emp_code)
									->whereIn('admin_day_status',['A','W','H'])
                                    ->where('start_date', $log_date)
                                    ->orderBy('id','DESC')
									->first();
						
						if($JobCardAbsentwh->admin_day_status=="A")
						{
							$delTab=AttendanceJobcard::find($JobCardAbsentwh->id)->delete();
						}
						
						if(!empty($JobCardAbsentwh->admin_in_time) && !empty($JobCardAbsentwh->admin_out_time))
						{
							if($JobCardAbsentwh->admin_in_time=='00:00:00' && $JobCardAbsentwh->admin_out_time=='00:00:00')
							{
								$delTab=AttendanceJobcard::find($JobCardAbsentwh->id)->delete();
							}
						}
						
						$JobCardpDay= AttendanceJobcard::where('emp_code', $emp_code)
									->whereIn('admin_day_status',['P','Late IN','Late OUT'])
                                    ->where('start_date', $log_date)
                                    ->orderBy('id','DESC')
									->first();
									
						if(!empty($JobCardpDay->admin_in_time) && !empty($JobCardpDay->admin_out_time))
						{
							if($JobCardpDay->admin_in_time=='00:00:00' && $JobCardpDay->admin_out_time=='00:00:00')
							{
								$delTab=AttendanceJobcard::find($JobCardpDay->id)->delete();
							}
						}

						$JobCardpDayEnd= AttendanceJobcard::where('emp_code', $emp_code)
									->whereIn('admin_day_status',['P','Late IN','Late OUT'])
                                    ->where('end_date', $log_date)
                                    ->orderBy('id','DESC')
									->first();
									
						if(!empty($JobCardpDayEnd->admin_out_time))
						{
							if($JobCardpDayEnd->admin_out_time=='00:00:00')
							{
								$UpTab=AttendanceJobcard::find($JobCardpDayEnd->id);
								$UpTab->end_date='0000-00-00';
								$UpTab->admin_out_time='00:00:00';
								$UpTab->user_out_time='00:00:00';
								$UpTab->audit_out_time='00:00:00';
								$UpTab->save();
								
							}
						}
						
									
					}
													

                    $chkJobCard = AttendanceJobcard::where('emp_code', $emp_code)
                                    ->where('start_date', $log_date)
                                    ->count();
									
					if($chkJobCard>1)
					{
						$JobCardIDIN = AttendanceJobcard::where('emp_code', $emp_code)
                                    ->where('start_date', $log_date)
									->orderBy('id','ASC')
                                    ->first();
						AttendanceJobcard::where('emp_code', $emp_code)
                                    ->where('start_date', $log_date)
									->WhereNotIN('id',[$JobCardIDIN->id])
                                    ->delete();			
					}
					
                    //echo $chkJobCard." Log -".$chkJobCardAbsentwh;
                    //exit();
					
					
					
                    if ($chkJobCard == 0) 
					{

                       //echo "Shift 1st Entry start-".$log_date."-".$log_time;
                       //exit();


                        $punch_pattern = "0";
                        $day_pattern = 0;
                        //dEFINING sHIFT aND mENTIONING dATA sTART
                        if (!empty($shift_data)) {
                            //echo "Shift data found 1st Entry start";
                            //exit();

                            $day_status = "P";
                            $day_pattern = 0;
                            $shift_start_buffer_time = $shift_data->shift_start_buffer_time;
                            $floatStartBuffer = explode(":", $shift_start_buffer_time);
                            $shift_start_time = $shift_data->shift_start_time;
                            $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                            //echo $timeFormattedStartShift."----".$log_time;
                            //exit();

                            if ($timeFormattedStartShift < $log_time) {
                                $day_status = "Late IN";
                            }



                            //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                            if (!empty($attnJobcardPolicy)) {

                                //Admin Formatted time Start`
                                $admin_intime_global = "";
                                if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                    $admin_with_intime = $attnJobcardPolicy[0]->admin_with_intime;
                                    $floatadmin_with_intime = explode(":", $admin_with_intime);
                                    $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->admin_addition_deduction, $log_time, $floatadmin_with_intime);
                                    $admin_intime_global = $timeFormattedAdminTime;
                                } else {
                                    $admin_intime_global = $log_time;
                                }

                                //User Formatted time Start
                                $user_intime_global = "";
                                if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                    $user_with_intime = $attnJobcardPolicy[0]->user_with_intime;
                                    $floatuser_with_intime = explode(":", $user_with_intime);
                                    $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                    $user_intime_global = $timeFormattedUserTime;
                                } else {
                                    $user_intime_global = $log_time;
                                }
                                
                                //Audit Formatted time Start
                                $audit_intime_global = "";
                                if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                    $audit_with_intime = $attnJobcardPolicy[0]->audit_with_intime;
                                    $floataudit_with_intime = explode(":", $audit_with_intime);
                                    $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                    $audit_intime_global = $timeFormattedAuditTime;
                                } else {
                                    $audit_intime_global = $log_time;
                                }
                            } else {
                                $admin_intime_global = $log_time;
                                $user_intime_global = $log_time;
                                $audit_intime_global = $log_time;
                            }
                            //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                        } else {



                            $day_status = "P";
                            $admin_intime_global = $log_time;
                            $user_intime_global = $log_time;
                            $audit_intime_global = $log_time;
                        }
                        //dEFINING sHIFT aND mENTIONING dATA eND




                        if ($shift_type_night == 1) {


                           // echo "Night Start";
                            //exit();

                            $shift_start_date = $shift_info[0]->start_date;
                            $shift_end_date = $shift_info[0]->end_date;
                            //exit();

                            $NightSft_CDate_st = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $log_date)->count();
                            $NightSft_CDate_en = AttendanceJobcard::where('emp_code', $emp_code)->where('end_date', $log_date)->count();

                            

                            $chk_day_before_shift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
                                    ->where('start_date', '<=', $day_before)
                                    ->where('end_date', '>=', $day_before)
                                    ->orderby('id', 'DESC')->count();
									
                            if ($chk_day_before_shift_info != 0) 
							{

                                $day_before_shift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
                                        ->where('start_date', '<=', $day_before)
                                        ->where('end_date', '>=', $day_before)
                                        ->orderby('id', 'DESC')->first();

                                $day_before_shift_id = $day_before_shift_info->shift_id;
								
                            } 
							else 
							{
                                $day_before_shift_id = 0;
                            }
                            
                            $LeaveDay_chk = LeaveApplicationMaster::where('emp_code', $emp_code)
                                        ->where('leave_status', 'Approved')
                                        ->where('start_date', '<=', $day_before)
                                        ->where('end_date', '>=', $day_before)
                                        ->count();
							//echo $day_before_shift_id;
							//exit();
                            if (($shift_start_date == $log_date) && ($shift_id != $day_before_shift_id)) {
                                $Night_dBEd = 0;
                                $startN_flag = 1;
                                $Night_dBSd = 0;
                            } else {

                                $Night_dBEd = AttendanceJobcard::where('emp_code', $emp_code)->where('end_date', $day_before)->count();
                                $Night_dBSd = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $day_before)->count();

                                $startN_flag = 0;
                            }


                            //echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
                           // echo "Night Shift start";
							//exit();
                            // if all is empty and shift day is log date == flag
                            if ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 0 && $startN_flag == 1 && $Night_dBSd == 0 && $LeaveDay_chk == 0) 
							{

                               //echo "Shift data if night shift 1st Entry start is empty";
                               //exit();
                                //Now Insert Data to Job card Start    
                                $tab = new AttendanceJobcard();
                                $tab->company_id = $log_company;
                                $tab->emp_code = $emp_code;
                                $tab->start_date = $log_date;
                                $tab->admin_in_time = $admin_intime_global;
                                $tab->admin_day_status = $day_status;
                                $tab->user_in_time = $user_intime_global;
                                $tab->user_day_status = $day_status;
                                $tab->audit_in_time = $audit_intime_global;
                                $tab->audit_day_status = $day_status;
                                $tab->save();
                                //Now Insert Data To Job Card End

                                $tabRow = AttendanceRawData::find($log_id);
                                $tabRow->is_read = 1;
                                $tabRow->save();

//                                echo "Shift data if night shift 1st Entry start is not empty and date shift start also";
//                                exit();
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 0 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{
//                                echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
//                                echo "Night Shift start33";
//                                exit();
                                //when all got empty today all and yesterday second entry and shift flag is false
//                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
//                                                ->where('company_id', $log_company)
//                                                ->where(function($q) use ($day_before) {
//                                                    $q->Where('start_date', $day_before);
//                                                })->first();
//                                echo "<pre>";                
//                                print_r($ExJobCardNight);
//                                echo "night shift data when all got empty today all and yesterday second entry and shift flag is false";
//                                exit();
                                //          
                                $ExJobCardNightDay = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where(function($q) use ($day_before) {
                                                    $q->Where('start_date', $day_before);
                                                    $q->orWhere('end_date', $day_before);
                                                })->first();

                                //print_r($ExJobCardNightDay);
                                $make_before_time_raw = $ExJobCardNightDay->start_date . ' ' . $ExJobCardNightDay->admin_in_time;
                                $make_before_time = Carbon::parse($make_before_time_raw);
                                $make_before_time->toDateTimeString();
                                $make_before_time = $make_before_time->format('Y-m-d H:i:s');


                                $pun_log_dt = Carbon::parse($log_date . ' ' . $log_time);
                                $pun_log_dt->toDateTimeString();
                                $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');

                                $calculated_time = $this->CalculateTwoTimeHourMinSec($make_before_time, $pun_log_dt);
                                $format_calculated_time = Carbon::parse($calculated_time);
                                $format_calculated_time->toDateTimeString();
                                $fct = $format_calculated_time->format('H');


                                //echo $fct;
                                //exit();
                                if ($fct > 17) {
                                    //echo "IF nightshift data has difference from last punch then create new log";
                                    //exit();
                                    //Now Insert Data to Job card Start    
                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End

                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();

                                    //echo "Shift data if night shift and no previous record using condition";
                                    //exit();
                                } else {


                                    //echo "Shift data if night shift and no previous record using condition";
                                    //exit();
                                    $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $day_before)->first();
                                    $jobcard_id = $ExJobCardNight->id;
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        
                                            // create a insert attendance jobcard day entry log for late Out
                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";



//                                             echo $totalShiftHour;
//                                              exit(); 



                                            if ($Admin_parse_Total > $totalShiftHour) {

//                                                echo "dd";
//                                                exit();
//                                                $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                                $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                                $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }


                                            
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                                $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                                $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                                $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeString($totalShiftHour, $Audit_totalOTHour);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->CalculateTwoTimeHourMinSec($tab->audit_in_time, $Audit_new_out_time);
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }

//                                            echo $Admin_totalOTHour;
//                                            exit();

                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

								
                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "all current empty but using before first is found and second is empty";
//                                exit();

                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->first();


                                $jobcard_id = $ExJobCardNight->id;
                                //echo $ExJobCardNight->start_date;
                                //exit();ss
								
								
								

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
								
								
								//echo $diffHour;
								//exit();
								
                                if ($diffHour > 17) 
								{

                                    AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('start_date', $day_before)
                                            ->update(['end_date' => $log_date,
                                                'admin_out_time' => '00:00:00',
                                                'admin_total_time' => '00:00:00',
                                                'admin_total_ot' => '00:00:00',
                                                'admin_day_status' => 'P',
                                                'user_out_time' => '00:00:00',
                                                'user_total_time' => '00:00:00',
                                                'user_total_ot' => '00:00:00',
                                                'user_day_status' => 'P',
                                                'audit_out_time' => '00:00:00',
                                                'audit_total_time' => '00:00:00',
                                                'audit_total_ot' => '00:00:00',
                                                'audit_day_status' => 'P']);

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } 
								else 
								{

                                    //exit();
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo "ww";
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
									
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();
									
									



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */



//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');
											
											


                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";

                                            //echo $this->MakeTimeDifferenceNightShift(date('Y-m-d'),$shift_data->shift_start_time,date('Y-m-d'),$timeFormattedStartShift);
                                            //echo $shift_data->shift_start_time . "-" . $timeFormattedStartShift . "=" . $totalShiftHour;
                                            //exit();





                                            if ($Admin_parse_Total > $totalShiftHour) {

                                            
											//echo "SHIFT=".$totalShiftHour;
											//exit();
											
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                   // echo "Cond Working - ".$Admin_totalOTHour;
													//exit();
													if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
															
															//echo  $Admin_totalOTHour."=OT";
															//exit();
															
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
												
                                            }

                                            
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }

//                                        echo $Admin_totalOTHour;
//                                            exit();

                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 2 && $LeaveDay_chk == 0) 
							{


                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "all current empty but using before first is found and second is empty";
//                                exit();

                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->first();


                                $jobcard_id = $ExJobCardNight->id;
                                //echo $ExJobCardNight->start_date;
                               // exit();

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('start_date', $day_before)
                                            ->update(['end_date' => $log_date,
                                                'admin_out_time' => '00:00:00',
                                                'admin_total_time' => '00:00:00',
                                                'admin_total_ot' => '00:00:00',
                                                'admin_day_status' => 'P',
                                                'user_out_time' => '00:00:00',
                                                'user_total_time' => '00:00:00',
                                                'user_total_ot' => '00:00:00',
                                                'user_day_status' => 'P',
                                                'audit_out_time' => '00:00:00',
                                                'audit_total_time' => '00:00:00',
                                                'audit_total_ot' => '00:00:00',
                                                'audit_day_status' => 'P']);

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {

                                    //exit();
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */



//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');


                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";

                                            //echo $this->MakeTimeDifferenceNightShift(date('Y-m-d'),$shift_data->shift_start_time,date('Y-m-d'),$timeFormattedStartShift);
                                            //echo $shift_data->shift_start_time . "-" . $timeFormattedStartShift . "=" . $totalShiftHour;
                                            //exit();





                                            if ($Admin_parse_Total > $totalShiftHour) {

//                                            echo "Cond Working";
//                                            exit();
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

                                            
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }

//                                        echo $Admin_totalOTHour;
//                                            exit();

                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 2 && $startN_flag == 0 && $Night_dBSd == 2 && $LeaveDay_chk == 0) 
							{


                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "all current empty but using before first is found and second is empty";
//                                exit();

                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->first();


                                $jobcard_id = $ExJobCardNight->id;
                                //echo $ExJobCardNight->start_date;
                               // exit();

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('start_date', $day_before)
                                            ->update(['end_date' => $log_date,
                                                'admin_out_time' => '00:00:00',
                                                'admin_total_time' => '00:00:00',
                                                'admin_total_ot' => '00:00:00',
                                                'admin_day_status' => 'P',
                                                'user_out_time' => '00:00:00',
                                                'user_total_time' => '00:00:00',
                                                'user_total_ot' => '00:00:00',
                                                'user_day_status' => 'P',
                                                'audit_out_time' => '00:00:00',
                                                'audit_total_time' => '00:00:00',
                                                'audit_total_ot' => '00:00:00',
                                                'audit_day_status' => 'P']);

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {

                                    //exit();
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */



//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');


                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";

                                            //echo $this->MakeTimeDifferenceNightShift(date('Y-m-d'),$shift_data->shift_start_time,date('Y-m-d'),$timeFormattedStartShift);
                                            //echo $shift_data->shift_start_time . "-" . $timeFormattedStartShift . "=" . $totalShiftHour;
                                            //exit();





                                            if ($Admin_parse_Total > $totalShiftHour) {

//                                            echo "Cond Working";
//                                            exit();
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

                                            
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }

//                                        echo $Admin_totalOTHour;
//                                            exit();

                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 2 && $startN_flag == 0 && $Night_dBSd == 2 && $LeaveDay_chk == 0) 
							{


                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "all current empty but using before first is found and second is empty";
//                                exit();

                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->first();


                                $jobcard_id = $ExJobCardNight->id;
                                //echo $ExJobCardNight->start_date;
                               // exit();

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('start_date', $day_before)
                                            ->update(['end_date' => $log_date,
                                                'admin_out_time' => '00:00:00',
                                                'admin_total_time' => '00:00:00',
                                                'admin_total_ot' => '00:00:00',
                                                'admin_day_status' => 'P',
                                                'user_out_time' => '00:00:00',
                                                'user_total_time' => '00:00:00',
                                                'user_total_ot' => '00:00:00',
                                                'user_day_status' => 'P',
                                                'audit_out_time' => '00:00:00',
                                                'audit_total_time' => '00:00:00',
                                                'audit_total_ot' => '00:00:00',
                                                'audit_day_status' => 'P']);

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {

                                    //exit();
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */



//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');


                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";

                                            //echo $this->MakeTimeDifferenceNightShift(date('Y-m-d'),$shift_data->shift_start_time,date('Y-m-d'),$timeFormattedStartShift);
                                            //echo $shift_data->shift_start_time . "-" . $timeFormattedStartShift . "=" . $totalShiftHour;
                                            //exit();





                                            if ($Admin_parse_Total > $totalShiftHour) {

//                                            echo "Cond Working";
//                                            exit();
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

                                            
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }

//                                        echo $Admin_totalOTHour;
//                                            exit();

                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 2 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{


                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "all current empty but using before first is found and second is empty";
//                                exit();

                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->first();


                                $jobcard_id = $ExJobCardNight->id;
                                //echo $ExJobCardNight->start_date;
                               // exit();

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {

                                    //exit();
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */



//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');


                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";

                                            //echo $this->MakeTimeDifferenceNightShift(date('Y-m-d'),$shift_data->shift_start_time,date('Y-m-d'),$timeFormattedStartShift);
                                            //echo $shift_data->shift_start_time . "-" . $timeFormattedStartShift . "=" . $totalShiftHour;
                                            //exit();





                                            if ($Admin_parse_Total > $totalShiftHour) {

//                                            echo "Cond Working";
//                                            exit();
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

                                            
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }

//                                        echo $Admin_totalOTHour;
//                                            exit();

                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 2 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{


                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "all current empty but using before first is found and second is empty";
//                                exit();

                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->first();


                                $jobcard_id = $ExJobCardNight->id;
                                //echo $ExJobCardNight->start_date;
                               // exit();

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {

                                    //exit();
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */



//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');


                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";

                                            //echo $this->MakeTimeDifferenceNightShift(date('Y-m-d'),$shift_data->shift_start_time,date('Y-m-d'),$timeFormattedStartShift);
                                            //echo $shift_data->shift_start_time . "-" . $timeFormattedStartShift . "=" . $totalShiftHour;
                                            //exit();





                                            if ($Admin_parse_Total > $totalShiftHour) {

//                                            echo "Cond Working";
//                                            exit();
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

                                            
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }

//                                        echo $Admin_totalOTHour;
//                                            exit();

                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 2 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

//                                echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
//                                echo "Night Shift start";
//                                exit();
                                //when all got empty today all and yesterday second entry and shift flag is false
                                //echo $Night_dBEd;
                                //echo "all current empty but using before first is found and second is empty-".$log_date;
                                //exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('start_date', $day_before)
                                        ->orderby('id', 'DESC')
                                        ->first();
                                $jobcard_id = $ExJobCardNight->id;

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('start_date', $day_before)
                                            ->update(['end_date' => $log_date,
                                                'admin_out_time' => '00:00:00',
                                                'admin_total_time' => '00:00:00',
                                                'admin_total_ot' => '00:00:00',
                                                'admin_day_status' => 'P',
                                                'user_out_time' => '00:00:00',
                                                'user_total_time' => '00:00:00',
                                                'user_total_ot' => '00:00:00',
                                                'user_day_status' => 'P',
                                                'audit_out_time' => '00:00:00',
                                                'audit_total_time' => '00:00:00',
                                                'audit_total_ot' => '00:00:00',
                                                'audit_day_status' => 'P']);

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


//                                 echo $admin_intime_global;
//                                  exit(); 
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);



                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
//                                         echo $timeFormattedStartShift;
//                                          exit(); 
//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {

                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;

                                                            //echo $Admin_new_out_time."<br>".$Admin_new_total_working_time;
                                                            //exit();
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                           

                                            //echo 123;
                                            //exit();

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}

//                                    echo $User_Total_WTime;
//                                    exit();
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
								
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 3 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

//                                echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
//                                echo "Night Shift start";
//                                exit();
                                //when all got empty today all and yesterday second entry and shift flag is false
                                //echo $Night_dBEd;
                                //echo "all current empty but using before first is found and second is empty-".$log_date;
                                //exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('start_date', $day_before)
                                        ->orderby('id', 'DESC')
                                        ->first();
                                $jobcard_id = $ExJobCardNight->id;

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


//                                 echo $admin_intime_global;
//                                  exit(); 
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);



                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
//                                         echo $timeFormattedStartShift;
//                                          exit(); 
//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {

                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;

                                                            //echo $Admin_new_out_time."<br>".$Admin_new_total_working_time;
                                                            //exit();
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                           

                                            //echo 123;
                                            //exit();

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}

//                                    echo $User_Total_WTime;
//                                    exit();
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
								
                            }
							
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 2 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

//                                echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
//                                echo "Night Shift start";
//                                exit();
                                //when all got empty today all and yesterday second entry and shift flag is false
                                //echo $Night_dBEd;
                                //echo "all current empty but using before first is found and second is empty-".$log_date;
                                //exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('end_date', $day_before)
                                        ->orderby('id', 'DESC')
                                        ->first();
                                $jobcard_id = $ExJobCardNight->id;

                                $diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
                                if ($diffHour > 17) {

                                    AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('start_date', $day_before)
                                            ->update(['end_date' => $log_date,
                                                'admin_out_time' => '00:00:00',
                                                'admin_total_time' => '00:00:00',
                                                'admin_total_ot' => '00:00:00',
                                                'admin_day_status' => 'P',
                                                'user_out_time' => '00:00:00',
                                                'user_total_time' => '00:00:00',
                                                'user_total_ot' => '00:00:00',
                                                'user_day_status' => 'P',
                                                'audit_out_time' => '00:00:00',
                                                'audit_total_time' => '00:00:00',
                                                'audit_total_ot' => '00:00:00',
                                                'audit_day_status' => 'P']);

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                }
								else 
								{
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


//                                 echo $admin_intime_global;
//                                  exit(); 
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);



                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
//                                         echo $timeFormattedStartShift;
//                                          exit(); 
//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {

                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;

                                                            //echo $Admin_new_out_time."<br>".$Admin_new_total_working_time;
                                                            //exit();
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                           

                                            //echo 123;
                                            //exit();

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}

//                                    echo $User_Total_WTime;
//                                    exit();
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
								
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 0 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

//                                echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
                                //echo "Night Shift start";
                              // exit();
                                //when all got empty today all and yesterday second entry and shift flag is false
                                //echo $Night_dBEd;
                                //echo "all current empty but using before first is found and second is empty-".$log_date;
                                //exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('start_date', $day_before)
                                        ->orderby('id', 'DESC')
                                        ->first();
                                $jobcard_id = $ExJobCardNight->id;

                                $diffHour = $this->HumanHours($ExJobCardNight->end_date, $ExJobCardNight->admin_out_time, $log_date, $log_time);
								
								//echo "Night Shift start".$diffHour;
                               //exit();
								
                                if ($diffHour > 7) {
									//echo "Dif New";
									//exit();
                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                }
								else 
								{
									//echo "Dif old";
									//exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


//                                 echo $admin_intime_global;
//                                  exit(); 
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);



                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
//                                         echo $timeFormattedStartShift;
//                                          exit(); 
//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {

                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;

                                                            //echo $Admin_new_out_time."<br>".$Admin_new_total_working_time;
                                                            //exit();
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                           

                                            //echo 123;
                                            //exit();

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}

//                                    echo $User_Total_WTime;
//                                    exit();
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
								
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

//                                echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
                                //echo "01101";
                               //exit();
                                //when all got empty today all and yesterday second entry and shift flag is false
                                //echo $Night_dBEd;
                                //echo "all current empty but using before first is found and second is empty-".$log_date;
                                //exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('start_date', $day_before)
                                        ->orderby('id', 'DESC')
                                        ->first();
                                $jobcard_id = $ExJobCardNight->id;
								
								/*echo "<pre>";
								print_r($ExJobCardNight);
								exit();
								if(($ExJobCardNight->end_date==$log_date) && ($ExJobCardNight->admin_out_time==$log_time))
								{
									
									
									if(empty($ExJobCardNight->admin_in_time) || $ExJobCardNight->admin_in_time=="00:00:00")
									{
										$obj_night_shift_day_perse_two = Carbon::parse($day_before);
										$obj_night_shift_day_perse_two->toDateTimeString();
										$obj_night_shift_day_perse_two->subDay();
										$day_before_two = $obj_night_shift_day_perse_two->format('Y-m-d');
										
										$ExJobCardNight_two = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('start_date',$day_before_two)
                                        ->orderby('id', 'DESC')
                                        ->first();
										$jobcard_id_two = $ExJobCardNight_two->id;
										
										$diffHour = $this->HumanHours($ExJobCardNight_two->end_date, $ExJobCardNight_two->admin_out_time, $log_date, $log_time);
										
									}
									else
									{
										$diffHour = $this->HumanHours($ExJobCardNight->start_date, $ExJobCardNight->admin_in_time, $log_date, $log_time);
									}
									
									
								}
								else
								{
									echo "I am INO";
									$diffHour = $this->HumanHours($ExJobCardNight->end_date, $ExJobCardNight->admin_out_time, $log_date, $log_time);
								}*/
								
								$diffHour = $this->HumanHours($ExJobCardNight->end_date, $ExJobCardNight->admin_out_time, $log_date, $log_time);
								//$this->CalCulateTtalInTimeBetweenNightShift($ExJobCardNight->end_date, $ExJobCardNight->admin_out_time, $log_date, $log_time);
								//echo $ExJobCardNight->end_date."-".$ExJobCardNight->admin_out_time;
								
								//echo "Night Shift start=".$diffHour;
                                //exit();
								
                                if ($diffHour > 6) {

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                }
								else 
								{
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


//                                 echo $admin_intime_global;
//                                  exit(); 
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);



                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
//                                         echo $timeFormattedStartShift;
//                                          exit(); 
//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {

                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;

                                                            //echo $Admin_new_out_time."<br>".$Admin_new_total_working_time;
                                                            //exit();
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                           

                                            //echo 123;
                                            //exit();

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}

//                                    echo $User_Total_WTime;
//                                    exit();
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
								
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 2 && $LeaveDay_chk == 0) 
							{

//                                echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd . "<br>";
								//echo "01102";
                                //exit();
                                //when all got empty today all and yesterday second entry and shift flag is false
                                //echo $Night_dBEd;
                                //echo "all current empty but using before first is found and second is empty-".$log_date;
                                //exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('start_date', $day_before)
                                        ->orderby('id', 'DESC')
                                        ->first();
                                $jobcard_id = $ExJobCardNight->id;
								
								//echo $ExJobCardNight->end_date."#".$ExJobCardNight->admin_out_time."||||".$log_date."#".$log_time."<br>";

                                $diffHour = $this->HumanHours($ExJobCardNight->end_date, $ExJobCardNight->admin_out_time, $log_date, $log_time);
								
								//echo "Night Shift start".$diffHour;
                                //exit();
								
                                if ($diffHour > 8) {

                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End


                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                }
								else 
								{
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


//                                 echo $admin_intime_global;
//                                  exit(); 
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();



                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);



                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
//                                         echo $timeFormattedStartShift;
//                                          exit(); 
//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {

                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;

                                                            //echo $Admin_new_out_time."<br>".$Admin_new_total_working_time;
                                                            //exit();
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                           

                                            //echo 123;
                                            //exit();

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        //}

//                                    echo $User_Total_WTime;
//                                    exit();
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
								
                            }
							
							elseif($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 0 && $LeaveDay_chk == 0)
							{
								//logic 00100 // that means if night shift inpunch is missing
								
								$Night_dBEdsql = AttendanceJobcard::where('emp_code', $emp_code)->where('end_date', $day_before)->first();
								
								
								$diffHour = $this->HumanHours($Night_dBEdsql->end_date, $Night_dBEdsql->admin_out_time, $log_date, $log_time);
								
								//echo $diffHour;
								//exit();
								
								if(intval($diffHour)>24)
								{
									$tab = new AttendanceJobcard();
									$tab->company_id = $log_company;
									$tab->emp_code = $emp_code;
									$tab->start_date = $log_date;
									$tab->admin_in_time = $admin_intime_global;
									$tab->admin_day_status = $day_status;
									$tab->user_in_time = $user_intime_global;
									$tab->user_day_status = $day_status;
									$tab->audit_in_time = $audit_intime_global;
									$tab->audit_day_status = $day_status;
									$tab->save();
									//Now Insert Data To Job Card End

									$tabRow = AttendanceRawData::find($log_id);
									$tabRow->is_read = 1;
									$tabRow->save();
								}
								else
								{

									//Now Insert Data to Job card Start    
									$tab = new AttendanceJobcard();
									$tab->company_id = $log_company;
									$tab->emp_code = $emp_code;
									$tab->end_date = $log_date;
									$tab->admin_out_time = $admin_intime_global;
									$tab->admin_day_status = $day_status;
									$tab->user_out_time = $user_intime_global;
									$tab->user_day_status = $day_status;
									$tab->audit_out_time = $audit_intime_global;
									$tab->audit_day_status = $day_status;
									
									 $tab->start_date = $day_before;
									 $tab->admin_in_time = '00:00:00';
									 $tab->user_in_time = '00:00:00';
									 $tab->audit_in_time = '00:00:00';
									 
									 $tab->admin_total_time = '00:00:00';
									 $tab->user_total_time = '00:00:00';
									 $tab->audit_total_time = '00:00:00';
									 
									 $tab->admin_total_ot = '00:00:00';
									 $tab->user_total_ot = '00:00:00';
									 $tab->audit_total_ot = '00:00:00';
									
									$tab->save();
									//Now Insert Data To Job Card End
								
								}

                                $tabRow = AttendanceRawData::find($log_id);
                                $tabRow->is_read = 1;
                                $tabRow->save();
							}
							elseif($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 0 && $startN_flag == 0 && $Night_dBSd == 0 && $LeaveDay_chk == 0)
							{
								//logic 00100 // that means if night shift inpunch is missing
								//Now Insert Data to Job card Start    
                                $tab = new AttendanceJobcard();
                                $tab->company_id = $log_company;
                                $tab->emp_code = $emp_code;
                                $tab->start_date = $log_date;
                                $tab->admin_in_time = $admin_intime_global;
                                $tab->admin_day_status = $day_status;
                                $tab->user_in_time = $user_intime_global;
                                $tab->user_day_status = $day_status;
                                $tab->audit_in_time = $audit_intime_global;
                                $tab->audit_day_status = $day_status;								
                                $tab->save();
                                //Now Insert Data To Job Card End

                                $tabRow = AttendanceRawData::find($log_id);
                                $tabRow->is_read = 1;
                                $tabRow->save();
							}
							else 
							{

//                                echo "Shift data if night shift and no previous record using condition";
//                                exit();
                                //Now Insert Data to Job card Start    
                                $tab = new AttendanceJobcard();
                                $tab->company_id = $log_company;
                                $tab->emp_code = $emp_code;
                                $tab->start_date = $log_date;
                                $tab->admin_in_time = $admin_intime_global;
                                $tab->admin_day_status = $day_status;
                                $tab->user_in_time = $user_intime_global;
                                $tab->user_day_status = $day_status;
                                $tab->audit_in_time = $audit_intime_global;
                                $tab->audit_day_status = $day_status;
                                $tab->save();
                                //Now Insert Data To Job Card End

                                $tabRow = AttendanceRawData::find($log_id);
                                $tabRow->is_read = 1;
                                $tabRow->save();

//                                echo "Shift data if night shift and no previous record using condition";
//                                exit();
                            }

                            $tabRow = AttendanceRawData::find($log_id);
                            $tabRow->is_read = 1;
                            $tabRow->save();

//                            echo "Night Shift Entry Done";
//                            exit();
                        } 
						else 
						{


                            //echo "Shift data if day shift 1st Entry start";
                            //exit();
                            //Now Insert Data to Job card Start    
                            $tab = new AttendanceJobcard();
                            $tab->company_id = $log_company;
                            $tab->emp_code = $emp_code;
                            $tab->start_date = $log_date;
                            $tab->admin_in_time = $admin_intime_global;
                            $tab->admin_day_status = $day_status;
                            $tab->user_in_time = $user_intime_global;
                            $tab->user_day_status = $day_status;
                            $tab->audit_in_time = $audit_intime_global;
                            $tab->audit_day_status = $day_status;
                            $tab->save();
                            //Now Insert Data To Job Card End
                        }



                        //Modify Log Read Status so that it's not come again in process log start
                        $tabRow = AttendanceRawData::find($log_id);
                        $tabRow->is_read = 1;
                        $tabRow->save();
                        //Modify Log Read Status so that it's not come again in process log end
//                        echo "Shift 1st Entry Done";
//                        exit();
                    } 
					else 
					{

                       //echo "entry whatever jobcard is not empty" . $log_date."-".$shift_type_night;
                      // exit();

                        if (!empty($shift_data)) {
                            //echo "Shift data found 1st Entry start";
                            //exit();
													

                            $day_status = "P";
                            $shift_start_buffer_time = $shift_data->shift_start_buffer_time;
                            $floatStartBuffer = explode(":", $shift_start_buffer_time);
                            $shift_start_time = $shift_data->shift_start_time;
                            $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);



                            if ($timeFormattedStartShift < $log_time) {
                                // create a insert attendance jobcard day entry log for late entry
                                $day_status = "Late IN";
                            }

							

                            //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                            if (!empty($attnJobcardPolicy)) {



                                //Admin Formatted time Start`
                                $admin_intime_global = "";
								
								//print_r($attnJobcardPolicy);
								//exit();
								
                                if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                    //admin in time out time creation
                                    $admin_with_intime = $attnJobcardPolicy[0]->admin_with_intime;
                                    $floatadmin_with_intime = explode(":", $admin_with_intime);

                                    $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->admin_addition_deduction, $log_time, $floatadmin_with_intime);


                                    //admin in time out time ends here
                                    $admin_intime_global = $timeFormattedAdminTime;
                                } else {
                                    $admin_intime_global = $log_time;
                                }
								
								
								
                                //Admin Formatted Time End
                                //User Formatted time Start
                                $user_intime_global = "";
                                if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                    //User in time out time creation
                                    $user_with_intime = $attnJobcardPolicy[0]->user_with_intime;
                                    $floatuser_with_intime = explode(":", $user_with_intime);

                                    $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                    //User in time out time ends here
                                    $user_intime_global = $timeFormattedUserTime;
                                } else {
                                    $user_intime_global = $log_time;
                                }
                                //User Formatted time End
                                //Audit Formatted time Start
                                $audit_intime_global = "";
                                if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                    //admin in time out time creation
                                    $audit_with_intime = $attnJobcardPolicy[0]->audit_with_intime;
                                    $floataudit_with_intime = explode(":", $audit_with_intime);

                                    $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                    //admin in time out time ends here
                                    $audit_intime_global = $timeFormattedAuditTime;
                                    //echo $timeFormattedAuditTime;
                                    //exit();
                                } else {
                                    $audit_intime_global = $log_time;
                                }
								
								
								
                            } else {
                                $admin_intime_global = $log_time;
                                $user_intime_global = $log_time;
                                $audit_intime_global = $log_time;
                            }
                            //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                        } else {



                            $day_status = "P";
                            $admin_intime_global = $log_time;
                            $user_intime_global = $log_time;
                            $audit_intime_global = $log_time;
                        }
						
						//echo "1";
						//exit();
						
                        //dEFINING sHIFT aND mENTIONING dATA eND
                        //night shift start if day found in prevoious row 
                        if ($shift_type_night == 1) 
						{

                            //echo "Second entry find for night shift";
                            //exit();

                            $shift_start_date = $shift_info[0]->start_date;
                            $shift_end_date = $shift_info[0]->end_date;
                            //exit();

                            $NightSft_CDate_st = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->where('start_date', $log_date)
                                            ->count();

                            $NightSft_CDate_en = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->where('end_date', $log_date)
                                            ->count();

                            $chkDateNightShift = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->where('start_date', $log_date)
                                            ->count();



                            $obj_night_shift_day_perse = Carbon::parse($log_date);
                            $obj_night_shift_day_perse->toDateTimeString();
                            $obj_night_shift_day_perse->subDay();
                            $day_before = $obj_night_shift_day_perse->format('Y-m-d');

                            $chk_day_before_shift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
                                    ->where('start_date', '<=', $day_before)
                                    ->where('end_date', '>=', $day_before)
                                    ->orderby('id', 'DESC')
                                    ->count();
									
								
                            if ($chk_day_before_shift_info != 0) {

                                $day_before_shift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
                                        ->where('start_date', '<=', $day_before)
                                        ->where('end_date', '>=', $day_before)
                                        ->orderby('id', 'DESC')
                                        ->get();

                                $day_before_shift_id = $day_before_shift_info[0]->shift_id;
                            } else {
                                $day_before_shift_id = 0;
                            }

                             $LeaveDay_chk = LeaveApplicationMaster::where('emp_code', $emp_code)
                                        ->where('leave_status', 'Approved')
                                        ->where('start_date', '<=', $day_before)
                                        ->where('end_date', '>=', $day_before)
                                        ->count();
										
										
							//echo $chk_day_before_shift_info;
							//exit();	

                            if (($shift_start_date == $log_date) && ($shift_id != $day_before_shift_id)) {
                                $Night_dBEd = 0;
                                $startN_flag = 1;
                                $Night_dBSd = 0;
                            } 
							else 
							{

                               


                                $Night_dBEd = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('end_date', $day_before)
                                                ->count();
                                $Night_dBSd = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->count();
                                $startN_flag = 0;
                            }



                            //echo $NightSft_CDate_st . "-" . $NightSft_CDate_en . "-" . $Night_dBEd . "-" . $startN_flag . "-" . $Night_dBSd;
//                            echo "Night Shift 2nd-" . $log_date . "-" . $log_time;
                            //exit();
                            // if all is empty and shift day is log date == flag
                            if ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 0 && $startN_flag == 1 && $Night_dBSd == 0 && $LeaveDay_chk == 0) 
							{

//                                echo "Shift data if night shift 1st Entry start is empty";
//                                exit();
                                //Now Insert Data to Job card Start    
                                $tab = new AttendanceJobcard();
                                $tab->company_id = $log_company;
                                $tab->emp_code = $emp_code;
                                $tab->start_date = $log_date;
                                $tab->admin_in_time = $admin_intime_global;
                                $tab->admin_day_status = $day_status;
                                $tab->user_in_time = $user_intime_global;
                                $tab->user_day_status = $day_status;
                                $tab->audit_in_time = $audit_intime_global;
                                $tab->audit_day_status = $day_status;
                                $tab->save();
                                //Now Insert Data To Job Card End

                                $tabRow = AttendanceRawData::find($log_id);
                                $tabRow->is_read = 1;
                                $tabRow->save();

//                                echo "Shift data if night shift 1st Entry start is not empty and date shift start also";
//                                exit();
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

                                //when all got empty today all and yesterday second entry and shift flag is false
//                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
//                                                ->where('company_id', $log_company)
//                                                ->where(function($q) use ($day_before) {
//                                                    $q->Where('start_date', $day_before);
//                                                })->first();
                               //echo "<pre>";                
                               //echo "Stuck in Correct Place";
//                                echo "night shift data when all got empty today all and yesterday second entry and shift flag is false";
                                //exit();
                                ////          
                                $CurJobCardNightDay = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->where('end_date', $log_date)
                                        ->first();

                                //print_r($ExJobCardNightDay);
                                $make_before_time_raw = $CurJobCardNightDay->end_date . ' ' . $CurJobCardNightDay->admin_out_time;
                                $make_before_time = Carbon::parse($make_before_time_raw);
                                $make_before_time->toDateTimeString();
                                $make_before_time = $make_before_time->format('Y-m-d H:i:s');


                                $pun_log_dt = Carbon::parse($log_date . ' ' . $log_time);
                                $pun_log_dt->toDateTimeString();
                                $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');

                                $calculated_time = $this->CalculateTwoTimeHourMinSec($make_before_time, $pun_log_dt);
                                $format_calculated_time = Carbon::parse($calculated_time);
                                $format_calculated_time->toDateTimeString();
                                $fct = $format_calculated_time->format('H');


                               // echo $fct;
                               // exit();
                                if (intval($fct) > 7) {
                                    //echo "IF nightshift data has difference from last punch then create new log";
                                    //exit();
                                    //Now Insert Data to Job card Start    
                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End

                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();

                                    //echo "Shift data if night shift and no previous record using condition";
                                    //exit();
                                } 
								else 
								{


                                    //echo "Shift data if night shift and no previous record using condition";
                                    //exit();



                                    $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('end_date', $log_date)
                                            ->first();
                                    $jobcard_id = $ExJobCardNight->id;
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) 
									{
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    }
									else
									{
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();




                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            $day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */


//                                            $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                            $ShiftED = new \DateTime($timeFormattedStartShift);
//                                            $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                            $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                                $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                                $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                                $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");
                                                //CalCulateTtalInTimeBetweenNightShift($auto_start_date, $shift_start_time, $auto_end_date, $shift_end_time)
                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);


                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }
											
											//user / stanard ot and out time here
											$user_new_end_date='';
                                            if ($User_parse_Total > $totalShiftHour) {
                                                $User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
                                                        $User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
															$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
															
															$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
															
															//echo $user_new_out_time;
															//exit();
															$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
															$user_for_new_out_time=$user_new_end_date_time[1];
															
															
															if($user_new_out_time!=$user_for_new_out_time)
															{
																$user_new_out_time=$user_new_end_date_time[1];
																$user_new_end_date=$user_new_end_date_time[0];
																$tab->admin_total_ot = $user_new_end_date;
															}
															
															
                                                            $tab->user_out_time = $user_new_out_time;
                                                            $User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
                                                            $tab->user_total_time = $User_new_total_working_time;
                                                        }
                                                    }
                                                }
                                            }
											//user / stanard ot and out time here
											
                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                                $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                                $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                                $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        }
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							elseif ($NightSft_CDate_st == 1 && $NightSft_CDate_en == 1 && $Night_dBEd == 1 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

                                //when all got empty today all and yesterday second entry and shift flag is false
//                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
//                                                ->where('company_id', $log_company)
//                                                ->where(function($q) use ($day_before) {
//                                                    $q->Where('start_date', $day_before);
//                                                })->first();
                               //echo "<pre>";                
                               //echo "Stuck in Correct Place";
//                                echo "night shift data when all got empty today all and yesterday second entry and shift flag is false";
                                //exit();
                                ////          
                                $CurJobCardNightDay = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->where('start_date',$log_date)
                                        ->first();

                                //print_r($ExJobCardNightDay);
                                $make_first_time_raw = $CurJobCardNightDay->start_date . ' ' . $CurJobCardNightDay->admin_in_time;
                                $make_first_time = Carbon::parse($make_first_time_raw);
                                $make_first_time->toDateTimeString();
                                $make_first_time = $make_first_time->format('Y-m-d H:i:s');
								
								$make_before_time_raw = $CurJobCardNightDay->end_date . ' ' . $CurJobCardNightDay->admin_out_time;
                                $make_before_time = Carbon::parse($make_before_time_raw);
                                $make_before_time->toDateTimeString();
                                $make_before_time = $make_before_time->format('Y-m-d H:i:s');


                                $pun_log_dt = Carbon::parse($log_date . ' ' . $log_time);
                                $pun_log_dt->toDateTimeString();
                                $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');

                                $calculated_time = $this->CalculateTwoTimeHourMinSec($make_before_time, $pun_log_dt);
                                $format_calculated_time = Carbon::parse($calculated_time);
                                $format_calculated_time->toDateTimeString();
                                $fct = $format_calculated_time->format('H');
								
								$ini_fct=0;
								if(!empty($CurJobCardNightDay->admin_in_time) && !empty($CurJobCardNightDay->admin_out_time))
								{
									if($CurJobCardNightDay->admin_in_time!='00:00:00')
									{
										if($CurJobCardNightDay->admin_out_time!='00:00:00')
										{
											
											$diffHour = $this->HumanHours($CurJobCardNightDay->start_date, $CurJobCardNightDay->admin_in_time,$CurJobCardNightDay->end_date, $CurJobCardNightDay->admin_out_time);
											$ini_fct=$diffHour;
										}
									}
								}

                                ///echo $ini_fct;
                               // exit();
                                if (intval($fct) >7 && intval($ini_fct)>7) {
                                    //echo "IF nightshift data has difference from last punch then create new log";
                                    //exit();
                                    //Now Insert Data to Job card Start    
                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End

                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();

                                    //echo "Shift data if night shift and no previous record using condition";
                                    //exit();
                                } 
								else 
								{


                                    //echo "Shift data if night shift and no previous record using condition";
                                    //exit();



                                    $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('start_date', $log_date)
                                            ->first();
                                    $jobcard_id = $ExJobCardNight->id;
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) 
									{
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    }
									else
									{
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();




                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            $day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */


//                                            $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                            $ShiftED = new \DateTime($timeFormattedStartShift);
//                                            $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                            $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                                $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                                $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                                $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");
                                                //CalCulateTtalInTimeBetweenNightShift($auto_start_date, $shift_start_time, $auto_end_date, $shift_end_time)
                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);


                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }
											
											//user / stanard ot and out time here
											$user_new_end_date='';
                                            if ($User_parse_Total > $totalShiftHour) {
                                                $User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
                                                        $User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
															$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
															
															$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
															
															//echo $user_new_out_time;
															//exit();
															$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
															$user_for_new_out_time=$user_new_end_date_time[1];
															
															
															if($user_new_out_time!=$user_for_new_out_time)
															{
																$user_new_out_time=$user_new_end_date_time[1];
																$user_new_end_date=$user_new_end_date_time[0];
																$tab->admin_total_ot = $user_new_end_date;
															}
															
															
                                                            $tab->user_out_time = $user_new_out_time;
                                                            $User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
                                                            $tab->user_total_time = $User_new_total_working_time;
                                                        }
                                                    }
                                                }
                                            }
											//user / stanard ot and out time here
											
                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                                $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                                $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                                $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        }
                                    }
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            }
							
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 1 && $Night_dBEd == 0 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

//                                echo "Shift data if night shift 1st Entry start is empty and date found in previous second date also";
//                                exit();


                                $CurJobCardNightDay = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->where('end_date', $log_date)
                                        ->first();

                                //print_r($ExJobCardNightDay);
                                $make_before_time_raw = $CurJobCardNightDay->end_date . ' ' . $CurJobCardNightDay->admin_out_time;
                                $make_before_time = Carbon::parse($make_before_time_raw);
                                $make_before_time->toDateTimeString();
                                $make_before_time = $make_before_time->format('Y-m-d H:i:s');


                                $pun_log_dt = Carbon::parse($log_date . ' ' . $log_time);
                                $pun_log_dt->toDateTimeString();
                                $pun_log_dt = $pun_log_dt->format('Y-m-d H:i:s');

                                $calculated_time = $this->CalculateTwoTimeHourMinSec($make_before_time, $pun_log_dt);
                                $format_calculated_time = Carbon::parse($calculated_time);
                                $format_calculated_time->toDateTimeString();
                                $fct = $format_calculated_time->format('H');


//                                echo $fct;
//                                exit();

                                if (intval($fct) > 5) {

//                                     echo "Shift data if night shift and this is not similar to last punch and have too much hours difference";
//                                    exit();
                                    //Now Insert Data to Job card Start    
                                    $tab = new AttendanceJobcard();
                                    $tab->company_id = $log_company;
                                    $tab->emp_code = $emp_code;
                                    $tab->start_date = $log_date;
                                    $tab->admin_in_time = $admin_intime_global;
                                    $tab->admin_day_status = $day_status;
                                    $tab->user_in_time = $user_intime_global;
                                    $tab->user_day_status = $day_status;
                                    $tab->audit_in_time = $audit_intime_global;
                                    $tab->audit_day_status = $day_status;
                                    $tab->save();
                                    //Now Insert Data To Job Card End

                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                } else {



//                                    echo "Shift data if night shift and this is similar to last punch and don't have hours difference";
//                                    exit();



                                    $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->Where('end_date', $log_date)
                                            ->orderby('id', 'DESC')
                                            ->first();
                                    $jobcard_id = $ExJobCardNight->id;
                                    //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                    //$sqlpd->end_date = $log_date;
                                    //$sqlpd->admin_out_time = $admin_intime_global;
                                    //$sqlpd->save();
                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();

                                    //if (!empty($shift_data) && $is_emp_ot_eligible==1) {

                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                       
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */


//                                            $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                            $ShiftED = new \DateTime($timeFormattedStartShift);
//                                            $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                            $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                                $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                                $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                                $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

											
											//user / stanard ot and out time here
											$user_new_end_date='';
                                            if ($User_parse_Total > $totalShiftHour) {
                                                $User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
                                                        $User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
															$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
															
															$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
															
															
															$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
															$user_for_new_out_time=$user_new_end_date_time[1];
															
															
															if($user_new_out_time!=$user_for_new_out_time)
															{
																$user_new_out_time=$user_new_end_date_time[1];
																$user_new_end_date=$user_new_end_date_time[0];
																$tab->admin_total_ot = $user_new_end_date;
															}
															
															
                                                            $tab->user_out_time = $user_new_out_time;
                                                            $User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
                                                            $tab->user_total_time = $User_new_total_working_time;
                                                        }
                                                    }
                                                }
                                            }
											//user / stanard ot and out time here
											

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                                $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                                $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                                $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");
//                                                
                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        
                                    }
                                }

//                                echo "Shift data if night shift 1st Entry start is empty and date found in previous second date also";
//                                exit();
                            }
							elseif ($NightSft_CDate_st == 0 && $NightSft_CDate_en == 0 && $Night_dBEd == 0 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "night shift data when all got empty today all and yesterday second entry and shift flag is false";
//                                exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                                ->where('start_date', $day_before)
                                                ->first();
                                $jobcard_id = $ExJobCardNight->id;
                                //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                //$sqlpd->end_date = $log_date;
                                //$sqlpd->admin_out_time = $admin_intime_global;
                                //$sqlpd->save();
                                //echo $ExJobCardNight->id;
                                //echo 1;
                                //exit();
                                //if night shift out data get start
                                //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                if (!empty($attnJobcardPolicy)) {
                                    //Admin Formatted time Start`
                                    $admin_intime_global = "";
                                    if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                        //admin in time out time creation


                                        $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                        $floatadmin_with_intime = explode(":", $admin_with_intime);


                                        $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                        //admin in time out time ends here
                                        $admin_intime_global = $timeFormattedAdminTime;
                                    } else {
                                        $admin_intime_global = $log_time;
                                    }
                                    //Admin Formatted Time End
                                    //User Formatted time Start
                                    $user_intime_global = "";
                                    if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                        //User in time out time creation
                                        $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                        $floatuser_with_intime = explode(":", $user_with_intime);


                                        $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                        //User in time out time ends here
                                        $user_intime_global = $timeFormattedUserTime;
                                    } else {
                                        $user_intime_global = $log_time;
                                    }
                                    //User Formatted time End
                                    //Audit Formatted time Start
                                    $audit_intime_global = "";
                                    if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                        //admin in time out time creation




                                        $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                        $floataudit_with_intime = explode(":", $audit_with_intime);

                                        $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                        //admin in time out time ends here
                                        $audit_intime_global = $timeFormattedAuditTime;
                                        //echo $timeFormattedAuditTime;
                                        //exit();
                                    } else {
                                        $audit_intime_global = $log_time;
                                    }
                                } else {
                                    $admin_intime_global = $log_time;
                                    $user_intime_global = $log_time;
                                    $audit_intime_global = $log_time;
                                }


                                /* echo $admin_intime_global;
                                  exit(); */
                                //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                //Now Update Data to Job card Start    
                                $tab = AttendanceJobcard::find($jobcard_id);
                                $tab->end_date = $log_date;
                                $tab->admin_out_time = $admin_intime_global;
                                $tab->user_out_time = $user_intime_global;
                                $tab->audit_out_time = $audit_intime_global;
                                $tab->save();

                                //Admin Total Hour Calculate Start
                                $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                $tab->admin_total_time = $Admin_Total_WTime;
                                //Admin Total Hour Calculate End
                                //User Total Hour Calculate Start
                                $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                $tab->user_total_time = $User_Total_WTime;
                                //User Total Hour Calculate End
                                //Audit Total Hour Calculate Start
                                $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                $tab->audit_total_time = $Audit_Total_WTime;
                                //Audit Total Hour Calculate End

                                $tab->save();

                                if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                    $day_status = "P";
                                    $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                    $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                    $shift_start_time = $shift_data->shift_end_time;

                                    $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        // create a insert attendance jobcard day entry log for late Out
                                        //$day_status = "Late OUT";
                                        //exit();
                                        /* echo $timeFormattedStartShift;
                                          exit(); */


//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                        $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                        $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                        $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                        $User_parseTotal = Carbon::parse($tab->user_total_time);
                                        $User_parse_Total = $User_parseTotal->format('H:i');

                                        $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                        $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                        //echo $Admin_parse_Total."-".$totalShiftHour;
                                        $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                        $Admin_totalOTHour = "00:00:00";
                                        $User_totalOTHour = "00:00:00";
                                        $Audit_totalOTHour = "00:00:00";


                                        /* echo $Admin_parse_Total;
                                          exit(); */



                                        if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                            $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                            if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                    $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                    if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                        $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                        $tab->admin_out_time = $Admin_new_out_time;
                                                        $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                        $tab->admin_total_time = $Admin_new_total_working_time;
                                                    }



                                                    //echo "OT Greater Than Max Admin<br>";
                                                }
                                            }
                                        }
										
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here

                                        if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                            $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                            if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                    $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                    if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                        $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                        $tab->audit_out_time = $Audit_new_out_time;
                                                        $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                        $tab->audit_total_time = $Audit_new_total_working_time;
                                                    }
                                                    //echo "OT Greater Than Max Audit<br>";
                                                }
                                            }
                                        }



                                        $tab->admin_total_ot = $Admin_totalOTHour;
                                        $tab->user_total_ot = $User_totalOTHour;
                                        $tab->audit_total_ot = $Audit_totalOTHour;
                                        $tab->save();

                                        /*                                echo $Admin_totalOTHour."<br>";
                                          echo $User_totalOTHour."<br>";
                                          echo $Audit_totalOTHour."<br>"; */


                                        //exit();
                                    
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            } 
							elseif ($NightSft_CDate_st == 1 && $NightSft_CDate_en == 1 && $Night_dBEd == 0 && $startN_flag == 0 && $Night_dBSd == 1 && $LeaveDay_chk == 0) 
							{

                                //when all got empty today all and yesterday second entry and shift flag is false
//                                echo "night shift data when all got empty today all and yesterday second entry and shift flag is false";
//                                exit();
                                $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                        ->Where('start_date', $log_date)
                                        ->first();

                                $jobcard_id = $ExJobCardNight->id;
                                //$sqlpd = AttendanceJobcard::find($jobcard_id);
                                //$sqlpd->end_date = $log_date;
                                //$sqlpd->admin_out_time = $admin_intime_global;
                                //$sqlpd->save();
                                //echo $ExJobCardNight->id;
                                //echo 1;
                                //exit();
                                //if night shift out data get start
                                //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                if (!empty($attnJobcardPolicy)) {
                                    //Admin Formatted time Start`
                                    $admin_intime_global = "";
                                    if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                        //admin in time out time creation


                                        $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                        $floatadmin_with_intime = explode(":", $admin_with_intime);


                                        $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                        //admin in time out time ends here
                                        $admin_intime_global = $timeFormattedAdminTime;
                                    } else {
                                        $admin_intime_global = $log_time;
                                    }
                                    //Admin Formatted Time End
                                    //User Formatted time Start
                                    $user_intime_global = "";
                                    if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                        //User in time out time creation
                                        $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                        $floatuser_with_intime = explode(":", $user_with_intime);


                                        $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                        //User in time out time ends here
                                        $user_intime_global = $timeFormattedUserTime;
                                    } else {
                                        $user_intime_global = $log_time;
                                    }
                                    //User Formatted time End
                                    //Audit Formatted time Start
                                    $audit_intime_global = "";
                                    if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                        //admin in time out time creation




                                        $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                        $floataudit_with_intime = explode(":", $audit_with_intime);

                                        $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                        //admin in time out time ends here
                                        $audit_intime_global = $timeFormattedAuditTime;
                                        //echo $timeFormattedAuditTime;
                                        //exit();
                                    } else {
                                        $audit_intime_global = $log_time;
                                    }
                                } else {
                                    $admin_intime_global = $log_time;
                                    $user_intime_global = $log_time;
                                    $audit_intime_global = $log_time;
                                }


                                /* echo $admin_intime_global;
                                  exit(); */
                                //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND
                                //Now Update Data to Job card Start    
                                $tab = AttendanceJobcard::find($jobcard_id);
                                $tab->end_date = $log_date;
                                $tab->admin_out_time = $admin_intime_global;
                                $tab->user_out_time = $user_intime_global;
                                $tab->audit_out_time = $audit_intime_global;
                                $tab->save();

                                //Admin Total Hour Calculate Start
                                $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                $tab->admin_total_time = $Admin_Total_WTime;
                                //Admin Total Hour Calculate End
                                //User Total Hour Calculate Start
                                $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                $tab->user_total_time = $User_Total_WTime;
                                //User Total Hour Calculate End
                                //Audit Total Hour Calculate Start
                                $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                $tab->audit_total_time = $Audit_Total_WTime;
                                //Audit Total Hour Calculate End

                                $tab->save();



                                if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                    $day_status = "P";
                                    $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                    $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                    $shift_start_time = $shift_data->shift_end_time;

                                    $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        // create a insert attendance jobcard day entry log for late Out
                                        //$day_status = "Late OUT";
                                        //exit();
                                        /* echo $timeFormattedStartShift;
                                          exit(); */


//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                        $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                        $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                        $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                        $User_parseTotal = Carbon::parse($tab->user_total_time);
                                        $User_parse_Total = $User_parseTotal->format('H:i');

                                        $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                        $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                        //echo $Admin_parse_Total."-".$totalShiftHour;
                                        $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                        $Admin_totalOTHour = "00:00:00";
                                        $User_totalOTHour = "00:00:00";
                                        $Audit_totalOTHour = "00:00:00";


                                        /* echo $Admin_parse_Total;
                                          exit(); */



                                        if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                            $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                            if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                    $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                    if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                        $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                        $tab->admin_out_time = $Admin_new_out_time;
                                                        $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                        $tab->admin_total_time = $Admin_new_total_working_time;
                                                    }



                                                    //echo "OT Greater Than Max Admin<br>";
                                                }
                                            }
                                        }
										
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here


                                        

                                        if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                            $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                            if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                    $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                    if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                        $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                        $tab->audit_out_time = $Audit_new_out_time;
                                                        $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                        $tab->audit_total_time = $Audit_new_total_working_time;
                                                    }
                                                    //echo "OT Greater Than Max Audit<br>";
                                                }
                                            }
                                        }



                                        $tab->admin_total_ot = $Admin_totalOTHour;
                                        $tab->user_total_ot = $User_totalOTHour;
                                        $tab->audit_total_ot = $Audit_totalOTHour;
                                        $tab->save();

                                        /*                                echo $Admin_totalOTHour."<br>";
                                          echo $User_totalOTHour."<br>";
                                          echo $Audit_totalOTHour."<br>"; */


                                        //exit();
                                    
                                }

                                //echo "night shift data when all got empty today all and yesterday second entry and shift flag is false"; 
                                //if night shift out data get end
                            } 
							else 
							{

                                //echo "Shift data if night shift 1st Entry start is not empty";
                                //exit();
                                //$LeaveDay_chk==0

                                if ($LeaveDay_chk == 0) {
									
									
									$chkExJobCard=AttendanceJobcard::where('emp_code', $emp_code)
                                            ->where('start_date', $log_date)
                                            ->orderby('id', 'DESC')
                                            ->count();
									if($chkExJobCard==0)
									{
										$day_status="P";
										$tab = new AttendanceJobcard();
										$tab->company_id = $log_company;
										$tab->emp_code = $emp_code;
										$tab->start_date = $log_date;
										$tab->admin_in_time = $log_time;
										$tab->admin_day_status = $day_status;
										$tab->user_in_time = $log_time;
										$tab->user_day_status = $day_status;
										$tab->audit_in_time = $log_time;
										$tab->audit_day_status = $day_status;
										$tab->save();
										//Now Insert Data To Job Card End

										$tabRow = AttendanceRawData::find($log_id);
										$tabRow->is_read = 1;
										$tabRow->save();
									}
									else
									{
										
                                    $ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->where('start_date', $log_date)
                                            ->orderby('id', 'DESC')
                                            ->first();

                                    //print_r($ExJobCardNight);
                                    //exit();

                                    $sqlpd = AttendanceJobcard::find($ExJobCardNight->id);
                                    $sqlpd->end_date = $log_date;
                                    $sqlpd->admin_out_time = $admin_intime_global;
                                    $sqlpd->save();

                                    //echo $ExJobCardNight->id;
                                    //echo 1;
                                    //exit();
                                    //if night shift out data get start
                                    //JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
                                    if (!empty($attnJobcardPolicy)) {
                                        //Admin Formatted time Start`
                                        $admin_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
                                            //admin in time out time creation


                                            $admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
                                            $floatadmin_with_intime = explode(":", $admin_with_intime);


                                            $timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
                                            //admin in time out time ends here
                                            $admin_intime_global = $timeFormattedAdminTime;
                                        } else {
                                            $admin_intime_global = $log_time;
                                        }
                                        //Admin Formatted Time End
                                        //User Formatted time Start
                                        $user_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
                                            //User in time out time creation
                                            $user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
                                            $floatuser_with_intime = explode(":", $user_with_intime);


                                            $timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
                                            //User in time out time ends here
                                            $user_intime_global = $timeFormattedUserTime;
                                        } else {
                                            $user_intime_global = $log_time;
                                        }
                                        //User Formatted time End
                                        //Audit Formatted time Start
                                        $audit_intime_global = "";
                                        if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
                                            //admin in time out time creation




                                            $audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
                                            $floataudit_with_intime = explode(":", $audit_with_intime);

                                            $timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
                                            //admin in time out time ends here
                                            $audit_intime_global = $timeFormattedAuditTime;
                                            //echo $timeFormattedAuditTime;
                                            //exit();
                                        } else {
                                            $audit_intime_global = $log_time;
                                        }
                                    } else {
                                        $admin_intime_global = $log_time;
                                        $user_intime_global = $log_time;
                                        $audit_intime_global = $log_time;
                                    }


                                    /* echo $admin_intime_global;
                                      exit(); */
                                    //JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND

                                    $sqlJobCard = AttendanceJobcard::where('emp_code', $emp_code)
                                            ->where('start_date', $log_date)
                                            ->orderby('id', 'DESC')
                                            ->first();

                                    $jobcard_id = $sqlJobCard->id;


                                    //Now Update Data to Job card Start    
                                    $tab = AttendanceJobcard::find($jobcard_id);
                                    $tab->end_date = $log_date;
                                    $tab->admin_out_time = $admin_intime_global;
                                    $tab->user_out_time = $user_intime_global;
                                    $tab->audit_out_time = $audit_intime_global;
                                    $tab->save();

                                    //Admin Total Hour Calculate Start
                                    $Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
                                    $tab->admin_total_time = $Admin_Total_WTime;
                                    //Admin Total Hour Calculate End
                                    //User Total Hour Calculate Start
                                    $User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
                                    $tab->user_total_time = $User_Total_WTime;
                                    //User Total Hour Calculate End
                                    //Audit Total Hour Calculate Start
                                    $Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
                                    $tab->audit_total_time = $Audit_Total_WTime;
                                    //Audit Total Hour Calculate End

                                    $tab->save();

                                    if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
                                        $day_status = "P";
                                        $shift_start_buffer_time = $shift_data->shift_end_buffer_time;
                                        $floatStartBuffer = explode(":", $shift_start_buffer_time);
                                        $shift_start_time = $shift_data->shift_end_time;

                                        $timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

                                        //if ($timeFormattedStartShift < $log_time) {
                                            // create a insert attendance jobcard day entry log for late Out
                                            //$day_status = "Late OUT";
                                            //exit();
                                            /* echo $timeFormattedStartShift;
                                              exit(); */


//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

                                            $totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

                                            $Admin_parseTotal = Carbon::parse($tab->admin_total_time);
                                            $Admin_parse_Total = $Admin_parseTotal->format('H:i');

                                            $User_parseTotal = Carbon::parse($tab->user_total_time);
                                            $User_parse_Total = $User_parseTotal->format('H:i');

                                            $Audit_parseTotal = Carbon::parse($tab->audit_total_time);
                                            $Audit_parse_Total = $Audit_parseTotal->format('H:i');

                                            //echo $Admin_parse_Total."-".$totalShiftHour;
                                            $Log_TotalShiftHour = new \DateTime($totalShiftHour);

                                            $Admin_totalOTHour = "00:00:00";
                                            $User_totalOTHour = "00:00:00";
                                            $Audit_totalOTHour = "00:00:00";


                                            /* echo $Admin_parse_Total;
                                              exit(); */



                                            if ($Admin_parse_Total > $totalShiftHour) {
//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

                                                $Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

                                                        $Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

                                                        if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
                                                            $Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
                                                            $tab->admin_out_time = $Admin_new_out_time;
                                                            $Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
                                                            $tab->admin_total_time = $Admin_new_total_working_time;
                                                        }



                                                        //echo "OT Greater Than Max Admin<br>";
                                                    }
                                                }
                                            }

											
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here

                                            

                                            if ($Audit_parse_Total > $totalShiftHour) {
//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

                                                $Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

                                                if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
                                                    if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
                                                        $Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
                                                        if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
                                                            $Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
                                                            $tab->audit_out_time = $Audit_new_out_time;
                                                            $Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
                                                            $tab->audit_total_time = $Audit_new_total_working_time;
                                                        }
                                                        //echo "OT Greater Than Max Audit<br>";
                                                    }
                                                }
                                            }



                                            $tab->admin_total_ot = $Admin_totalOTHour;
                                            $tab->user_total_ot = $User_totalOTHour;
                                            $tab->audit_total_ot = $Audit_totalOTHour;
                                            $tab->save();

                                            /*                                echo $Admin_totalOTHour."<br>";
                                              echo $User_totalOTHour."<br>";
                                              echo $Audit_totalOTHour."<br>"; */


                                            //exit();
                                        
                                    }
									}
                                    //if night shift out data get end
                                } 
								else 
								{
									$chkJobcard=AttendanceJobcard::where('start_date',$log_date)->count();
									
									if($chkJobcard==0)
									{									
										$tab = new AttendanceJobcard();
										$tab->company_id = $log_company;
										$tab->emp_code = $emp_code;
										$tab->start_date = $log_date;
										$tab->admin_in_time = $admin_intime_global;
										$tab->admin_day_status = $day_status;
										$tab->user_in_time = $user_intime_global;
										$tab->user_day_status = $day_status;
										$tab->audit_in_time = $audit_intime_global;
										$tab->audit_day_status = $day_status;
										$tab->save();
										//Now Insert Data To Job Card End
									}
									else
									{
										
										
										$ExJobCardNight = AttendanceJobcard::where('emp_code', $emp_code)
												->where('start_date', $log_date)
												->orderby('id', 'DESC')
												->first();

										//print_r($ExJobCardNight);
										//exit();

										$sqlpd = AttendanceJobcard::find($ExJobCardNight->id);
										$sqlpd->end_date = $log_date;
										$sqlpd->admin_out_time = $admin_intime_global;
										$sqlpd->save();

										//echo $ExJobCardNight->id;
										//echo 1;
										//exit();
										//if night shift out data get start
										//JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
										if (!empty($attnJobcardPolicy)) {
											//Admin Formatted time Start`
											$admin_intime_global = "";
											if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
												//admin in time out time creation


												$admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
												$floatadmin_with_intime = explode(":", $admin_with_intime);


												$timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
												//admin in time out time ends here
												$admin_intime_global = $timeFormattedAdminTime;
											} else {
												$admin_intime_global = $log_time;
											}
											//Admin Formatted Time End
											//User Formatted time Start
											$user_intime_global = "";
											if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
												//User in time out time creation
												$user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
												$floatuser_with_intime = explode(":", $user_with_intime);


												$timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
												//User in time out time ends here
												$user_intime_global = $timeFormattedUserTime;
											} else {
												$user_intime_global = $log_time;
											}
											//User Formatted time End
											//Audit Formatted time Start
											$audit_intime_global = "";
											if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
												//admin in time out time creation




												$audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
												$floataudit_with_intime = explode(":", $audit_with_intime);

												$timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
												//admin in time out time ends here
												$audit_intime_global = $timeFormattedAuditTime;
												//echo $timeFormattedAuditTime;
												//exit();
											} else {
												$audit_intime_global = $log_time;
											}
										} else {
											$admin_intime_global = $log_time;
											$user_intime_global = $log_time;
											$audit_intime_global = $log_time;
										}


										/* echo $admin_intime_global;
										  exit(); */
										//JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND

										$sqlJobCard = AttendanceJobcard::where('emp_code', $emp_code)
												->where('start_date', $log_date)
												->orderby('id', 'DESC')
												->first();

										$jobcard_id = $sqlJobCard->id;


										//Now Update Data to Job card Start    
										$tab = AttendanceJobcard::find($jobcard_id);
										$tab->end_date = $log_date;
										$tab->admin_out_time = $admin_intime_global;
										$tab->user_out_time = $user_intime_global;
										$tab->audit_out_time = $audit_intime_global;
										$tab->save();

										//Admin Total Hour Calculate Start
										$Admin_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->admin_in_time, $tab->end_date, $tab->admin_out_time);
										$tab->admin_total_time = $Admin_Total_WTime;
										//Admin Total Hour Calculate End
										//User Total Hour Calculate Start
										$User_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->user_in_time, $tab->end_date, $tab->user_out_time);
										$tab->user_total_time = $User_Total_WTime;
										//User Total Hour Calculate End
										//Audit Total Hour Calculate Start
										$Audit_Total_WTime = $this->CalCulateTtalInTimeBetweenNightShift($tab->start_date, $tab->audit_in_time, $tab->end_date, $tab->audit_out_time);
										$tab->audit_total_time = $Audit_Total_WTime;
										//Audit Total Hour Calculate End

										$tab->save();

										if (!empty($shift_data) && $is_emp_ot_eligible == 1) {
											$day_status = "P";
											$shift_start_buffer_time = $shift_data->shift_end_buffer_time;
											$floatStartBuffer = explode(":", $shift_start_buffer_time);
											$shift_start_time = $shift_data->shift_end_time;

											$timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

											//if ($timeFormattedStartShift < $log_time) {
												// create a insert attendance jobcard day entry log for late Out
												//$day_status = "Late OUT";
												//exit();
												/* echo $timeFormattedStartShift;
												  exit(); */


	//                                        $ShiftST = new \DateTime($shift_data->shift_start_time);
	//                                        $ShiftED = new \DateTime($timeFormattedStartShift);
	//                                        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
	//                                        $totalShiftHour = $totalShiftHourDiff->format("%H:%I");

												$totalShiftHour = $this->MakeTimeDifferenceNightShift(date('Y-m-d'), $shift_data->shift_start_time, date('Y-m-d'), $timeFormattedStartShift);

												$Admin_parseTotal = Carbon::parse($tab->admin_total_time);
												$Admin_parse_Total = $Admin_parseTotal->format('H:i');

												$User_parseTotal = Carbon::parse($tab->user_total_time);
												$User_parse_Total = $User_parseTotal->format('H:i');

												$Audit_parseTotal = Carbon::parse($tab->audit_total_time);
												$Audit_parse_Total = $Audit_parseTotal->format('H:i');

												//echo $Admin_parse_Total."-".$totalShiftHour;
												$Log_TotalShiftHour = new \DateTime($totalShiftHour);

												$Admin_totalOTHour = "00:00:00";
												$User_totalOTHour = "00:00:00";
												$Audit_totalOTHour = "00:00:00";


												/* echo $Admin_parse_Total;
												  exit(); */



												if ($Admin_parse_Total > $totalShiftHour) {
	//                                            $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
	//                                            $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
	//                                            $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

													$Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);

													if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
														if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

															$Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

															if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
																$Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
																$tab->admin_out_time = $Admin_new_out_time;
																$Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
																$tab->admin_total_time = $Admin_new_total_working_time;
															}



															//echo "OT Greater Than Max Admin<br>";
														}
													}
												}

												
											//user / stanard ot and out time here
											$user_new_end_date='';
											if ($User_parse_Total > $totalShiftHour) {
												$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
												if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
													if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
														$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
														if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
															$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
															
															$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
															
															
															$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
															$user_for_new_out_time=$user_new_end_date_time[1];
															
															
															if($user_new_out_time!=$user_for_new_out_time)
															{
																$user_new_out_time=$user_new_end_date_time[1];
																$user_new_end_date=$user_new_end_date_time[0];
																$tab->admin_total_ot = $user_new_end_date;
															}
															
															
															$tab->user_out_time = $user_new_out_time;
															$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
															$tab->user_total_time = $User_new_total_working_time;
														}
													}
												}
											}
											//user / stanard ot and out time here

												

												if ($Audit_parse_Total > $totalShiftHour) {
	//                                            $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
	//                                            $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
	//                                            $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

													$Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

													if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
														if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
															$Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
															if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
																$Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
																$tab->audit_out_time = $Audit_new_out_time;
																$Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
																$tab->audit_total_time = $Audit_new_total_working_time;
															}
															//echo "OT Greater Than Max Audit<br>";
														}
													}
												}



												$tab->admin_total_ot = $Admin_totalOTHour;
												$tab->user_total_ot = $User_totalOTHour;
												$tab->audit_total_ot = $Audit_totalOTHour;
												$tab->save();

												/*                                echo $Admin_totalOTHour."<br>";
												  echo $User_totalOTHour."<br>";
												  echo $Audit_totalOTHour."<br>"; */


												//exit();
											
										}
									}

                                    $tabRow = AttendanceRawData::find($log_id);
                                    $tabRow->is_read = 1;
                                    $tabRow->save();
                                }
                            }

                            $tabRow = AttendanceRawData::find($log_id);
                            $tabRow->is_read = 1;
                            $tabRow->save();

//                            echo "Night Shift Entry Done";
//                            exit();
                        } 
						else 
						{
                           //echo "Shift 2nd Entry start if day shift";
						   //exit();
							 
							 /* first check current process time is greater than existing in time or less than intime  operation start */
							 $chkJObcardEx=AttendanceJobcard::where('start_date',$log_date)->where('emp_code',$emp_code)->count();
							 if($chkJObcardEx==0)
							 {
									$tab = new AttendanceJobcard();
									$tab->company_id = $log_company;
									$tab->emp_code = $emp_code;
									$tab->start_date = $log_date;
									$tab->admin_in_time = $log_time;
									$tab->admin_day_status = $day_status;
									$tab->user_in_time = $log_time;
									$tab->user_day_status = $day_status;
									$tab->audit_in_time = $log_time;
									$tab->audit_day_status = $day_status;
									$tab->save();
									//Now Insert Data To Job Card End

									$tabRow = AttendanceRawData::find($log_id);
									$tabRow->is_read = 1;
									$tabRow->save();
									
									//wrong coded here
							 }
							 else
							 {
								 
							 
								 $ExJobCard=AttendanceJobcard::where('start_date',$log_date)->where('emp_code',$emp_code)->first();
								 $getExInTimeinINTFormat=$ExJobCard->admin_in_time;
								 $getCurInTimeinINTFormat=$log_time;
								 //echo "<pre>";
								 //print_r($log_time);
								 //exit();
								 
								 if($getExInTimeinINTFormat>$getCurInTimeinINTFormat)
								 {	 
									  
									 
									 $ExJobCard->admin_out_time=$ExJobCard->admin_in_time;
									 $ExJobCard->user_out_time=$ExJobCard->admin_in_time;
									 $ExJobCard->audit_out_time=$ExJobCard->admin_in_time;
									 $ExJobCard->save();
									 
									 $ExJobCard->admin_in_time=$log_time;
									 $ExJobCard->user_in_time=$log_time;
									 $ExJobCard->audit_in_time=$log_time;
									 $ExJobCard->save();
									 
									 $log_time=$ExJobCard->admin_out_time;
									 
									 
									 //$log_time=$new_log_time;
								 }
								 
								 //$sqlJobInfo=AttendanceJobcard::where('start_date',$log_date)->where('emp_code',$emp_code)->first();
								 
								 //print_r($sqlJobInfo);
								 //echo $log_time;
								 //exit();
								 
								 /* first check current process time is greater than existing in time or less than intime operation end */
								//if job card previously not empty start
								//JOBCARD pOLICY START HERE TO SHOW DATA AS IT'S DEFINED POLICY sTRAT
								if (!empty($attnJobcardPolicy)) {
									//Admin Formatted time Start`
									$admin_intime_global = "";
									if ($attnJobcardPolicy[0]->is_admin_data_show_policy == 1) {
										//admin in time out time creation


										$admin_with_intime = $attnJobcardPolicy[0]->admin_with_outime;
										$floatadmin_with_intime = explode(":", $admin_with_intime);


										$timeFormattedAdminTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floatadmin_with_intime);
										//admin in time out time ends here
										$admin_intime_global = $timeFormattedAdminTime;
									} else {
										$admin_intime_global = $log_time;
									}
									//Admin Formatted Time End
									//User Formatted time Start
									$user_intime_global = "";
									if ($attnJobcardPolicy[0]->is_user_data_show_policy == 1) {
										//User in time out time creation
										$user_with_intime = $attnJobcardPolicy[0]->user_with_outime;
										$floatuser_with_intime = explode(":", $user_with_intime);


										$timeFormattedUserTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->user_addition_deduction, $log_time, $floatuser_with_intime);
										//User in time out time ends here
										$user_intime_global = $timeFormattedUserTime;
									} else {
										$user_intime_global = $log_time;
									}
									//User Formatted time End
									//Audit Formatted time Start
									$audit_intime_global = "";
									if ($attnJobcardPolicy[0]->is_audit_data_show_policy == 1) {
										//admin in time out time creation




										$audit_with_intime = $attnJobcardPolicy[0]->audit_with_outime;
										$floataudit_with_intime = explode(":", $audit_with_intime);

										$timeFormattedAuditTime = $this->AddNewTimeFromArrayCarbon($attnJobcardPolicy[0]->audit_addition_deduction, $log_time, $floataudit_with_intime);
										//admin in time out time ends here
										$audit_intime_global = $timeFormattedAuditTime;
										//echo $timeFormattedAuditTime;
										//exit();
									} else {
										$audit_intime_global = $log_time;
									}
								} else {
									$admin_intime_global = $log_time;
									$user_intime_global = $log_time;
									$audit_intime_global = $log_time;
								}


								 /*echo $admin_intime_global;
								  exit(); */
								//JOBCARD pOLICY eND HERE TO SHOW DATA AS IT'S DEFINED POLICY eND

								$sqlJobCard = AttendanceJobcard::where('emp_code', $emp_code)
										->where(function($q) use ($log_date) {
											$q->where('start_date', $log_date);
											$q->orWhere('end_date', $log_date);
										})
										->first();

								$jobcard_id = $sqlJobCard->id;


								//Now Update Data to Job card Start    
								$tab = AttendanceJobcard::find($jobcard_id);
								$tab->end_date = $log_date;
								$tab->admin_out_time = $admin_intime_global;
								$tab->user_out_time = $user_intime_global;
								$tab->audit_out_time = $audit_intime_global;
								$tab->save();

								//Admin Total Hour Calculate Start
								$Admin_Total_WTime = $this->CalCulateTtalInTimeBetween($tab->admin_in_time, $tab->admin_out_time);
								//echo $Admin_Total_WTime;
								//exit();

								$tab->admin_total_time = $Admin_Total_WTime;
								//Admin Total Hour Calculate End
								//User Total Hour Calculate Start
								$User_Total_WTime = $this->CalCulateTtalInTimeBetween($tab->user_in_time, $tab->user_out_time);
								$tab->user_total_time = $User_Total_WTime;
								//User Total Hour Calculate End
								//Audit Total Hour Calculate Start
								$Audit_Total_WTime = $this->CalCulateTtalInTimeBetween($tab->audit_in_time, $tab->audit_out_time);
								$tab->audit_total_time = $Audit_Total_WTime;
								//Audit Total Hour Calculate End

								$tab->save();
								
								//echo $is_emp_ot_eligible;
								//exit();
								
								if (!empty($shift_data) && $is_emp_ot_eligible == 1) 
								{

						

									$day_status = "P";
									$shift_start_buffer_time = $shift_data->shift_end_buffer_time;
									$floatStartBuffer = explode(":", $shift_start_buffer_time);
									$shift_start_time = $shift_data->shift_end_time;

									$timeFormattedStartShift = $this->AddNewTimeFromArrayCarbon("+", $shift_start_time, $floatStartBuffer);

									//echo $timeFormattedStartShift;


									$ShiftST = new \DateTime($shift_data->shift_start_time);
									$ShiftED = new \DateTime($timeFormattedStartShift);
									$totalShiftHourDiff = $ShiftST->diff($ShiftED);
									$totalShiftHour = $totalShiftHourDiff->format("%H:%I");

									//echo $totalShiftHour;

									//if ($timeFormattedStartShift < $log_time) {
									if ($totalShiftHour < $Admin_Total_WTime) 
									{
										// create a insert attendance jobcard day entry log for late Out
										//if ($timeFormattedStartShift < $log_time) {
											//$day_status = "Late OUT";
										//}
										//exit();
										 /*echo $timeFormattedStartShift;
										  exit(); */


										

										$Admin_parseTotal = Carbon::parse($tab->admin_total_time);
										$Admin_parse_Total = $Admin_parseTotal->format('H:i');

										$User_parseTotal = Carbon::parse($tab->user_total_time);
										$User_parse_Total = $User_parseTotal->format('H:i');

										$Audit_parseTotal = Carbon::parse($tab->audit_total_time);
										$Audit_parse_Total = $Audit_parseTotal->format('H:i');

										//echo $Admin_parse_Total."-".$totalShiftHour;
										$Log_TotalShiftHour = new \DateTime($totalShiftHour);

										$Admin_totalOTHour = "00:00:00";
										$User_totalOTHour = "00:00:00";
										$Audit_totalOTHour = "00:00:00";


										/* echo $Admin_parse_Total;
										  exit(); */



										if ($Admin_parse_Total > $totalShiftHour) {
	//                                        $Admin_TotalWorkingHour = new \DateTime($Admin_parse_Total);
	//                                        $Admin_totalOTHourDiff = $Log_TotalShiftHour->diff($Admin_TotalWorkingHour);
	//                                        $Admin_totalOTHour = $Admin_totalOTHourDiff->format("%H:%I:%S");

											$Admin_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Admin_parse_Total);


											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_admin_data_show_policy == 1 && $attnJobcardPolicy[0]->is_admin_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($Admin_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->admin_max_ot_hour)) {

													$Admin_totalOTHour = $attnJobcardPolicy[0]->admin_max_ot_hour;

													if ($attnJobcardPolicy[0]->is_admin_ot_adjust_with_outtime == 1) {
														$Admin_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Admin_totalOTHour, $tab->admin_in_time);
														$tab->admin_out_time = $Admin_new_out_time;
														$Admin_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Admin_totalOTHour));
														$tab->admin_total_time = $Admin_new_total_working_time;
													}



													//echo "OT Greater Than Max Admin<br>";
												}
											}
										}

										
										
										//user / stanard ot and out time here
										$user_new_end_date='';
										if ($User_parse_Total > $totalShiftHour) {
											$User_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $User_parse_Total);
											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_user_data_show_policy == 1 && $attnJobcardPolicy[0]->is_user_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($User_totalOTHour >= $this->FormatHHMM($attnJobcardPolicy[0]->user_max_ot_hour)) {
													$User_totalOTHour_max_ot_hour = $attnJobcardPolicy[0]->user_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_user_ot_adjust_with_outtime == 1) {
														$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($User_totalOTHour_max_ot_hour,$User_totalOTHour);
														
														$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($log_date.' '.$tab->admin_out_time, $left_time_to_deduct_from_out_time);
														
														
														$user_new_end_date_time=$this->CarbonMakeOutTime($log_date.' '.$tab->admin_out_time,$left_time_to_deduct_from_out_time);
														$user_for_new_out_time=$user_new_end_date_time[1];
														
														
														if($user_new_out_time!=$user_for_new_out_time)
														{
															$user_new_out_time=$user_new_end_date_time[1];
															$user_new_end_date=$user_new_end_date_time[0];
															$tab->admin_total_ot = $user_new_end_date;
														}
														
														
														$tab->user_out_time = $user_new_out_time;
														$User_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $User_totalOTHour_max_ot_hour));
														$tab->user_total_time = $User_new_total_working_time;
													}
												}
											}
										}
										//user / stanard ot and out time here

										if ($Audit_parse_Total > $totalShiftHour) {
	//                                        $Audit_TotalWorkingHour = new \DateTime($Audit_parse_Total);
	//                                        $Audit_totalOTHourDiff = $Log_TotalShiftHour->diff($Audit_TotalWorkingHour);
	//                                        $Audit_totalOTHour = $Audit_totalOTHourDiff->format("%H:%I:%S");

											$Audit_totalOTHour = $this->CalCulateTtalInTimeBetween($totalShiftHour, $Audit_parse_Total);

											if (!empty($attnJobcardPolicy) && $attnJobcardPolicy[0]->is_audit_data_show_policy == 1 && $attnJobcardPolicy[0]->is_audit_max_ot_fixed == 1 && $is_emp_ot_eligible == 1) {
												if ($Audit_totalOTHour > $this->FormatHHMM($attnJobcardPolicy[0]->audit_max_ot_hour)) {
													$Audit_totalOTHour = $attnJobcardPolicy[0]->audit_max_ot_hour;
													if ($attnJobcardPolicy[0]->is_audit_ot_adjust_with_outtime == 1) {
														$Audit_new_out_time = $this->ModifiyCarbonTimeStringThree($totalShiftHour, $Audit_totalOTHour, $tab->user_in_time);
														$tab->audit_out_time = $Audit_new_out_time;
														$Audit_new_total_working_time = $this->AddNewTimeFromArrayCarbon("+", $totalShiftHour, explode(":", $Audit_totalOTHour));
														$tab->audit_total_time = $Audit_new_total_working_time;
													}
													//echo "OT Greater Than Max Audit<br>";
												}
											}
										}



										$tab->admin_total_ot = $Admin_totalOTHour;
										$tab->user_total_ot = $User_totalOTHour;
										$tab->audit_total_ot = $Audit_totalOTHour;
										$tab->save();

																	   /* echo $Admin_totalOTHour."<br>";
										  echo $User_totalOTHour."<br>";
										  echo $Audit_totalOTHour."<br>"; */


										//exit();
									}
								}
								//else
								//{
									//echo "OT NOT ELEGIBLE";
									//exit();
								//}
								//echo $Admin_totalOTHour;
								//exit();

								$tabRow = AttendanceRawData::find($log_id);
								$tabRow->is_read = 1;
								$tabRow->save();
							
							}


//                        echo "Shift Data 2nd entry is done for day shift";
//                        exit();
                            //echo $Admin_Total_WTime."<br>";
                            // echo $User_Total_WTime."<br>";
                            //echo $Audit_Total_WTime."<br>";
                            //Now Update Data To Job Card End
                            //print_r($tab);
                            //exit();
                            //if job card previously not empty end
                        }
                    }


                    //echo "<pre>";
                    //print_r($shift_data);
                    //echo "Modify Flag True";
                    //exit();
                    //echo $log_time;
                    //Modify Log Read Status so that it's not come again in process log start
                    $tabRow = AttendanceRawData::find($log_id);
                    $tabRow->is_read = 1;
                    $tabRow->save();
                    //Modify Log Read Status so that it's not come again in process log end
//                    echo "Shift END here";
//                    exit();
                }

                //break;
            endforeach;


            
            

                    return 1;

        }
        else
        {
            return "Nothing To Process";
        }

        



        
    }

    private function ModifiyCarbonTimeString($Inittime, $InMerge) {

        if (!empty($Inittime)) {

            $shift_start_time = $Inittime;
            $objShiftStartTime = Carbon::parse($shift_start_time);
            if (!empty($InMerge)) {
                $floatStartBuffer = explode(":", $InMerge);
                $objShiftStartTime->toDateTimeString();
                $objShiftStartTime->addHours($floatStartBuffer[0]);
                $objShiftStartTime->addMinutes($floatStartBuffer[1]);
                $objShiftStartTime->addSeconds($floatStartBuffer[2]);
            }
            $timeFormattedStartShift = $objShiftStartTime->format('H:i:s');

            return $timeFormattedStartShift;
        } else {
            return "00:00:00";
        }
    }

    private function ModifiyCarbonTimeStringThree($Inittime, $InMerge, $entryTime) {

        if (!empty($Inittime)) {

            $shift_start_time = $Inittime;
            $objShiftStartTime = Carbon::parse($shift_start_time);
            if (!empty($InMerge)) {
                $floatStartBuffer = explode(":", $InMerge);
                $objShiftStartTime->toDateTimeString();
                $objShiftStartTime->addHours($floatStartBuffer[0]);
                $objShiftStartTime->addMinutes($floatStartBuffer[1]);
                $objShiftStartTime->addSeconds($floatStartBuffer[2]);
            }
            $timeFormattedStartShift = $objShiftStartTime->format('H:i:s');


            $shift_start_time_entry = $entryTime;
            $objShiftStartTime_entry = Carbon::parse($shift_start_time_entry);
            if ($timeFormattedStartShift != '00:00:00') {
                $floatStartBuffer_entry = explode(":", $timeFormattedStartShift);
                $objShiftStartTime_entry->toDateTimeString();
                $objShiftStartTime_entry->addHours($floatStartBuffer_entry[0]);
                $objShiftStartTime_entry->addMinutes($floatStartBuffer_entry[1]);
                $objShiftStartTime_entry->addSeconds($floatStartBuffer_entry[2]);
            }
            $timeFormattedStartShift_entry = $objShiftStartTime_entry->format('H:i:s');

            return $timeFormattedStartShift_entry;
        } else {
            return "00:00:00";
        }
    }

    private function CalculateTwoTimeHourMinSec($start_time, $end_time) {
        $ShiftST = new \DateTime($start_time);
        $ShiftED = new \DateTime($end_time);
        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
        $totalShiftHour = $totalShiftHourDiff->format("%H:%I:%S");

        return $totalShiftHour;
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
     * @param  \App\AttendanceJobcard  $attendanceJobcard
     * @return \Illuminate\Http\Response
     */
//    private function ManualJobCardEntryCheck($param = 0, $date = '0000-00-00', $emp_code = '0', $day_status = '', $company_id = '') {
//
//        if ($param == "A") {
//            $tab_log_date = ManualJobCardEntry::where('emp_code', $emp_code)
//                    ->where('date', $date)
//                    ->count();
//            if (count($tab_log_date) == 0) {
//                $jobcard_day_status = "A";
//            } else {
//
//                $tab_log_date = DB::table('manual_job_card_entries')
//                        ->where('emp_code', $emp_code)
//                        ->where('date', $date)
//                        ->take(1)
//                        ->get();
//                //print_r($tab_log_date);
//                $jobcard_day_status = "A";
//                if (isset($tab_log_date[0])) {
//                    $tab_log_dates = $tab_log_date[0];
//                    $jobcard_day_status = $tab_log_dates->day_type;
//
//
//                    $chkDate = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $date)->count();
////                    print_r($chkDate);
////                          exit();
//                    if (!empty($chkDate)) {
//                        $tab = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $date)
//                                ->update(['admin_day_status' => $jobcard_day_status]);
//                    } else {
//                        $tab = new AttendanceJobcard();
//                        $tab->emp_code = $tab_log_dates->emp_code;
//                        $tab->company_id = $tab_log_dates->company_id;
//                        $tab->start_date = $tab_log_dates->date;
//                        $tab->$day_status = $jobcard_day_status;
//                        $tab->save();
//                    }
//                } else {
//                    $jobcard_day_status = "A";
//                }
//            }
//
//            return $jobcard_day_status;
//        } else {
//            return $param;
//        }
//    }
//
//    public function Adminshow(Request $request) {
//
//        $emp_code = $request->emp_code;
//        $start_date = $request->start_date;
//        $end_date = $request->end_date;
//
//        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//        $company_id = $sqlEmp[0]->company_id;
//
//
//        $sqlDates = Calendar::where('company_id', $company_id)
//                ->whereBetween('date', [$start_date, $end_date])
//                ->get();
//
//        if (!empty($sqlDates)) {
//            $json = [];
//            foreach ($sqlDates as $line):
//
//                $ld = $line->date;
//
//                $data = DB::table('attendance_jobcards')
//                        ->select(
//                                'attendance_jobcards.id', 'attendance_jobcards.start_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
//                        )
//                        ->where('attendance_jobcards.emp_code', $emp_code)
//                        ->where(function($q) use ($ld) {
//                            $q->where('attendance_jobcards.start_date', $ld);
//                            $q->orWhere('attendance_jobcards.end_date', $ld);
//                        })
//                        ->orderBy('attendance_jobcards.id', 'DESC')
//                        ->get();
//
//                if (count($data) != 0) {
//                    $jobcard_id = $data[0]->start_date;
//                    $jobcard_in_time = $data[0]->admin_in_time;
//                    $jobcard_out_time = $data[0]->admin_out_time;
//                    $jobcard_total_time = $data[0]->admin_total_time;
//                    $jobcard_total_ot = $data[0]->admin_total_ot;
//                    $jobcard_day_status = $data[0]->admin_day_status;
//                } else {
//                    $jobcard_in_time = "00:00:00";
//                    $jobcard_out_time = "00:00:00";
//                    $jobcard_total_time = "00:00:00";
//                    $jobcard_total_ot = "00:00:00";
//                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
//                }
//
//                $json[] = array(
//                    'date' => $ld,
//                    'in_time' => $jobcard_in_time,
//                    'out_time' => $jobcard_in_time,
//                    'total_time' => $jobcard_total_time,
//                    'total_ot' => $jobcard_total_ot,
//                    'day_status' => $jobcard_day_status
//                );
//
//            endforeach;
//        }
//
//        //print_r($json);
//
//        return response()->json(array("data" => $json, "total" => count($json)));
//    }
//
//    public function Auditshow(Request $request, AttendanceJobcard $attendanceJobcard) {
//
//        $emp_code = $request->emp_code;
//        $start_date = $request->start_date;
//        $end_date = $request->end_date;
//
//        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//        $company_id = $sqlEmp[0]->company_id;
//
//
//        $sqlDates = Calendar::where('company_id', $company_id)
//                ->whereBetween('date', [$start_date, $end_date])
//                ->get();
//
//        if (!empty($sqlDates)) {
//            $json = [];
//            foreach ($sqlDates as $line):
//
//                $ld = $line->date;
//
//                $data = DB::table('attendance_jobcards')
//                        ->select(
//                                'attendance_jobcards.id', 'attendance_jobcards.start_date', 'attendance_jobcards.audit_in_time', 'attendance_jobcards.audit_out_time', 'attendance_jobcards.audit_total_time', 'attendance_jobcards.audit_total_ot', 'attendance_jobcards.audit_day_status'
//                        )
//                        ->where('attendance_jobcards.emp_code', $emp_code)
//                        ->where(function($q) use ($ld) {
//                            $q->where('attendance_jobcards.start_date', $ld);
//                            $q->orWhere('attendance_jobcards.end_date', $ld);
//                        })
//                        ->orderBy('attendance_jobcards.id', 'DESC')
//                        ->get();
//
//                if (count($data) != 0) {
//                    $jobcard_id = $data[0]->id;
//                    $jobcard_in_time = $data[0]->audit_in_time;
//                    $jobcard_out_time = $data[0]->audit_out_time;
//                    $jobcard_total_time = $data[0]->audit_total_time;
//                    $jobcard_total_ot = $data[0]->audit_total_ot;
//                    $jobcard_day_status = $data[0]->audit_day_status;
//                } else {
//                    $jobcard_in_time = "00:00:00";
//                    $jobcard_out_time = "00:00:00";
//                    $jobcard_total_time = "00:00:00";
//                    $jobcard_total_ot = "00:00:00";
//                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'audit_day_status', $company_id);
//                }
//
//                $json[] = array('date' => $ld,
//                    'id' => $jobcard_id,
//                    'in_time' => $jobcard_in_time,
//                    'out_time' => $jobcard_in_time,
//                    'total_time' => $jobcard_total_time,
//                    'total_ot' => $jobcard_total_ot,
//                    'day_status' => $jobcard_day_status,
//                );
//
//            endforeach;
//        }
//
//
//        return response()->json(array("data" => $json, "total" => count($json)));
//    }
//
//    public function Usershow(Request $request, AttendanceJobcard $attendanceJobcard) {
//
//        $emp_code = $request->emp_code;
//        $start_date = $request->start_date;
//        $end_date = $request->end_date;
//
//        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//        $company_id = $sqlEmp[0]->company_id;
//
//
//        $sqlDates = Calendar::where('company_id', $company_id)
//                ->whereBetween('date', [$start_date, $end_date])
//                ->get();
//
//        if (!empty($sqlDates)) {
//            $json = [];
//            foreach ($sqlDates as $line):
//
//                $ld = $line->date;
//
//                $data = DB::table('attendance_jobcards')
//                        ->select(
//                                'attendance_jobcards.id', 'attendance_jobcards.start_date', 'attendance_jobcards.user_in_time', 'attendance_jobcards.user_out_time', 'attendance_jobcards.user_total_time', 'attendance_jobcards.user_total_ot', 'attendance_jobcards.user_day_status'
//                        )
//                        ->where('attendance_jobcards.emp_code', $emp_code)
//                        ->where(function($q) use ($ld) {
//                            $q->where('attendance_jobcards.start_date', $ld);
//                            $q->orWhere('attendance_jobcards.end_date', $ld);
//                        })
//                        ->orderBy('attendance_jobcards.id', 'DESC')
//                        ->get();
//
//                if (count($data) != 0) {
//                    $jobcard_id = $data[0]->id;
//                    $jobcard_in_time = $data[0]->user_in_time;
//                    $jobcard_out_time = $data[0]->user_out_time;
//                    $jobcard_total_time = $data[0]->user_total_time;
//                    $jobcard_total_ot = $data[0]->user_total_ot;
//                    $jobcard_day_status = $data[0]->user_day_status;
//                } else {
//                    $jobcard_in_time = "00:00:00";
//                    $jobcard_out_time = "00:00:00";
//                    $jobcard_total_time = "00:00:00";
//                    $jobcard_total_ot = "00:00:00";
//                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'user_day_status', $company_id);
//                }
//
//                $json[] = array('date' => $ld,
//                    'id' => $jobcard_id,
//                    'in_time' => $jobcard_in_time,
//                    'out_time' => $jobcard_in_time,
//                    'total_time' => $jobcard_total_time,
//                    'total_ot' => $jobcard_total_ot,
//                    'day_status' => $jobcard_day_status,
//                );
//
//            endforeach;
//        }
//
//
//        return response()->json(array("data" => $json, "total" => count($json)));
//    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AttendanceJobcard  $attendanceJobcard
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendanceJobcard $attendanceJobcard) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AttendanceJobcard  $attendanceJobcard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendanceJobcard $attendanceJobcard) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AttendanceJobcard  $attendanceJobcard
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceJobcard $attendanceJobcard) {
        //
    }

//    public function AdminexportExcel(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
//
//
//        $emp_code = $request->emp_code;
//        $start_date = $request->start_date;
//        $end_date = $request->end_date;
//
//        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//        $company_id = $sqlEmp[0]->company_id;
//
//
//        $sqlDates = Calendar::where('company_id', $company_id)
//                ->whereBetween('date', [$start_date, $end_date])
//                ->get();
//
//        if (!empty($sqlDates)) {
//            // $json = [];
//            foreach ($sqlDates as $line):
//
//                $ld = $line->date;
//
//                $data = DB::table('attendance_jobcards')
//                        ->select(
//                                'attendance_jobcards.id', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
//                        )
//                        ->where('attendance_jobcards.emp_code', $emp_code)
//                        ->where(function($q) use ($ld) {
//                            $q->where('attendance_jobcards.start_date', $ld);
//                            $q->orWhere('attendance_jobcards.end_date', $ld);
//                        })
//                        ->orderBy('attendance_jobcards.id', 'DESC')
//                        ->get();
//
//                if (count($data) != 0) {
//                    $jobcard_id = $data[0]->id;
//                    $jobcard_in_time = $data[0]->admin_in_time;
//                    $jobcard_out_time = $data[0]->admin_out_time;
//                    $jobcard_total_time = $data[0]->admin_total_time;
//                    $jobcard_total_ot = $data[0]->admin_total_ot;
//                    $jobcard_day_status = $data[0]->admin_day_status;
//                } else {
//                    $jobcard_in_time = "00:00:00";
//                    $jobcard_out_time = "00:00:00";
//                    $jobcard_total_time = "00:00:00";
//                    $jobcard_total_ot = "00:00:00";
//                    $jobcard_day_status = "A";
//                }
//
//                $json[] = array('date' => $ld,
//                    'in_time' => $jobcard_in_time,
//                    'out_time' => $jobcard_in_time,
//                    'total_time' => $jobcard_total_time,
//                    'total_ot' => $jobcard_total_ot,
//                    'day_status' => $jobcard_day_status,
//                );
//
//            endforeach;
//        }
//
//
//        $excelArray = [];
//
//        // Define the Excel spreadsheet headers
//        $excelArray [] = [
//            'Date',
//            'In Time',
//            'Out Time',
//            'Total Working Time',
//            'Total Over Time',
//            'Day Status',
//        ];
//
//        // Convert each member of the returned collection into an array,
//        // and append it to the payments array.get_object_vars()
//        foreach ($json as $key => $field) {
//            $excelArray[] = $field;
//        }
//        //exit();
//        // Generate and return the spreadsheet
//        \Excel::create('Admin Jobcard Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {
//
//            // Set the spreadsheet title, creator, and description
//            $excel->setTitle('Admin Jobcard Report');
//            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
//            $excel->setDescription('Admin Jobcard Report');
//
//            // Build the spreadsheet, passing in the payments array
//            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
//                $sheet->fromArray($excelArray, null, 'A1', false, false);
//            });
//        })->download('xlsx');
//    }
//
//    public function AdminexportPdf(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
//        $content = '<h3>Admin Jobcard Report</h3>';
//        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
//        // instantiate and use the dompdf class
//        $excelArray = [
//            'date',
//            'in_time',
//            'out_time',
//            'total_time',
//            'total_ot',
//            'day_status',
//        ];
//
//        if (!empty($excelArray)) {
//            $content .='<table width="100%">';
//            $content .='<thead>';
//            $content .='<tr>';
//            foreach ($excelArray as $exhead):
//                $content .='<th>' . $exhead . '</th>';
//            endforeach;
//            $content .='</tr>';
//            $content .='</thead>';
//
//
//            $rows = count($excelArray);
//            $emp_code = $request->emp_code;
//            $start_date = $request->start_date;
//            $end_date = $request->end_date;
//
//
//            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//            $company_id = $sqlEmp[0]->company_id;
//
//
//            $sqlDates = Calendar::where('company_id', $company_id)
//                    ->whereBetween('date', [$start_date, $end_date])
//                    ->get();
//
//            if (!empty($sqlDates)) {
//                $datarows = [];
//                foreach ($sqlDates as $line):
//
//                    $ld = $line->date;
//
//                    $data = DB::table('attendance_jobcards')
//                            ->select(
//                                    'attendance_jobcards.id', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
//                            )
//                            ->where('attendance_jobcards.emp_code', $emp_code)
//                            ->where(function($q) use ($ld) {
//                                $q->where('attendance_jobcards.start_date', $ld);
//                                $q->orWhere('attendance_jobcards.end_date', $ld);
//                            })
//                            ->orderBy('attendance_jobcards.id', 'DESC')
//                            ->get();
//
//                    if (count($data) != 0) {
//                        $jobcard_id = $data[0]->id;
//                        $jobcard_in_time = $data[0]->admin_in_time;
//                        $jobcard_out_time = $data[0]->admin_out_time;
//                        $jobcard_total_time = $data[0]->admin_total_time;
//                        $jobcard_total_ot = $data[0]->admin_total_ot;
//                        $jobcard_day_status = $data[0]->admin_day_status;
//                    } else {
//                        $jobcard_in_time = "00:00:00";
//                        $jobcard_out_time = "00:00:00";
//                        $jobcard_total_time = "00:00:00";
//                        $jobcard_total_ot = "00:00:00";
//                        $jobcard_day_status = "A";
//                    }
//
//                    $datarows[] = array('date' => $ld,
//                        'in_time' => $jobcard_in_time,
//                        'out_time' => $jobcard_in_time,
//                        'total_time' => $jobcard_total_time,
//                        'total_ot' => $jobcard_total_ot,
//                        'day_status' => $jobcard_day_status,
//                    );
//
//                endforeach;
//            }
//            if (!empty($datarows)) {
//                $content .='<tbody>';
//                foreach ($datarows as $draw):
//
//                    $content .='<tr>';
//                    for ($i = 0; $i <= $rows - 1; $i++):
//                        $fid = $excelArray[$i];
//                        $content .='<td>' . $draw[$fid] . '</td>';
//                    endfor;
//                    $content .='</tr>';
//                endforeach;
//                $content .='</tbody>';
//            }
//
//
//            $content .='</table>';
//
//            $content .='<br />';
//
//            $content .='<h4>Total : ' . count($datarows) . '</h4>';
//
//
//            $content .='<br /><br /><br /><table border="0" width="100%">';
//            $content .='<tr>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
//            $content .='</tr>';
//
//
//            $content .='</table>';
//        }
//
//
//        $dompdf = new Dompdf();
//        $dompdf->set_option('isHtml5ParserEnabled', true);
//        $dompdf->loadHtml($content);
//
//        // (Optional) Setup the paper size and orientation
//        $dompdf->setPaper('A4', 'landscape');
//
//        // Render the HTML as PDF
//        $dompdf->render();
//
//        // Output the generated PDF to Browser
//        $dompdf->stream();
//    }
//
//    // Audit job card report
//    #
//    #
//    #
//    #
//    // Audit job card report
//
//    public function AuditexportExcel(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
//
//
//        $emp_code = $request->emp_code;
//        $start_date = $request->start_date;
//        $end_date = $request->end_date;
//
//        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//        $company_id = $sqlEmp[0]->company_id;
//
//
//        $sqlDates = Calendar::where('company_id', $company_id)
//                ->whereBetween('date', [$start_date, $end_date])
//                ->get();
//
//        if (!empty($sqlDates)) {
//            // $json = [];
//            foreach ($sqlDates as $line):
//
//                $ld = $line->date;
//
//                $data = DB::table('attendance_jobcards')
//                        ->select(
//                                'attendance_jobcards.id', 'attendance_jobcards.audit_in_time', 'attendance_jobcards.audit_out_time', 'attendance_jobcards.audit_total_time', 'attendance_jobcards.audit_total_ot', 'attendance_jobcards.audit_day_status'
//                        )
//                        ->where('attendance_jobcards.emp_code', $emp_code)
//                        ->where(function($q) use ($ld) {
//                            $q->where('attendance_jobcards.start_date', $ld);
//                            $q->orWhere('attendance_jobcards.end_date', $ld);
//                        })
//                        ->orderBy('attendance_jobcards.id', 'DESC')
//                        ->get();
//
//                if (count($data) != 0) {
//                    $jobcard_id = $data[0]->id;
//                    $jobcard_in_time = $data[0]->audit_in_time;
//                    $jobcard_out_time = $data[0]->audit_out_time;
//                    $jobcard_total_time = $data[0]->audit_total_time;
//                    $jobcard_total_ot = $data[0]->audit_total_ot;
//                    $jobcard_day_status = $data[0]->audit_day_status;
//                } else {
//                    $jobcard_in_time = "00:00:00";
//                    $jobcard_out_time = "00:00:00";
//                    $jobcard_total_time = "00:00:00";
//                    $jobcard_total_ot = "00:00:00";
//                    $jobcard_day_status = "A";
//                }
//
//                $json[] = array('date' => $ld,
//                    'in_time' => $jobcard_in_time,
//                    'out_time' => $jobcard_in_time,
//                    'total_time' => $jobcard_total_time,
//                    'total_ot' => $jobcard_total_ot,
//                    'day_status' => $jobcard_day_status,
//                );
//
//            endforeach;
//        }
//
//
//        $excelArray = [];
//
//        // Define the Excel spreadsheet headers
//        $excelArray [] = [
//            'Date',
//            'In Time',
//            'Out Time',
//            'Total Working Time',
//            'Total Over Time',
//            'Day Status',
//        ];
//
//        // Convert each member of the returned collection into an array,
//        // and append it to the payments array.get_object_vars()
//        foreach ($json as $key => $field) {
//            $excelArray[] = $field;
//        }
//        //exit();
//        // Generate and return the spreadsheet
//        \Excel::create('Audit Jobcard Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {
//
//            // Set the spreadsheet title, creator, and description
//            $excel->setTitle('Audit Jobcard Report');
//            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
//            $excel->setDescription('Audit Jobcard Report');
//
//            // Build the spreadsheet, passing in the payments array
//            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
//                $sheet->fromArray($excelArray, null, 'A1', false, false);
//            });
//        })->download('xlsx');
//    }
//
//    public function AuditexportPdf(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
//        $content = '<h3>Audit Jobcard Report</h3>';
//        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
//        // instantiate and use the dompdf class
//        $excelArray = [
//            'date',
//            'in_time',
//            'out_time',
//            'total_time',
//            'total_ot',
//            'day_status',
//        ];
//
//        if (!empty($excelArray)) {
//            $content .='<table width="100%">';
//            $content .='<thead>';
//            $content .='<tr>';
//            foreach ($excelArray as $exhead):
//                $content .='<th>' . $exhead . '</th>';
//            endforeach;
//            $content .='</tr>';
//            $content .='</thead>';
//
//
//            $rows = count($excelArray);
//            $emp_code = $request->emp_code;
//            $start_date = $request->start_date;
//            $end_date = $request->end_date;
//
//
//            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//            $company_id = $sqlEmp[0]->company_id;
//
//
//            $sqlDates = Calendar::where('company_id', $company_id)
//                    ->whereBetween('date', [$start_date, $end_date])
//                    ->get();
//
//            if (!empty($sqlDates)) {
//                $datarows = [];
//                foreach ($sqlDates as $line):
//
//                    $ld = $line->date;
//
//                    $data = DB::table('attendance_jobcards')
//                            ->select(
//                                    'attendance_jobcards.id', 'attendance_jobcards.audit_in_time', 'attendance_jobcards.audit_out_time', 'attendance_jobcards.audit_total_time', 'attendance_jobcards.audit_total_ot', 'attendance_jobcards.audit_day_status'
//                            )
//                            ->where('attendance_jobcards.emp_code', $emp_code)
//                            ->where(function($q) use ($ld) {
//                                $q->where('attendance_jobcards.start_date', $ld);
//                                $q->orWhere('attendance_jobcards.end_date', $ld);
//                            })
//                            ->orderBy('attendance_jobcards.id', 'DESC')
//                            ->get();
//
//                    if (count($data) != 0) {
//                        $jobcard_id = $data[0]->id;
//                        $jobcard_in_time = $data[0]->audit_in_time;
//                        $jobcard_out_time = $data[0]->audit_out_time;
//                        $jobcard_total_time = $data[0]->audit_total_time;
//                        $jobcard_total_ot = $data[0]->audit_total_ot;
//                        $jobcard_day_status = $data[0]->audit_day_status;
//                    } else {
//                        $jobcard_in_time = "00:00:00";
//                        $jobcard_out_time = "00:00:00";
//                        $jobcard_total_time = "00:00:00";
//                        $jobcard_total_ot = "00:00:00";
//                        $jobcard_day_status = "A";
//                    }
//
//                    $datarows[] = array('date' => $ld,
//                        'in_time' => $jobcard_in_time,
//                        'out_time' => $jobcard_in_time,
//                        'total_time' => $jobcard_total_time,
//                        'total_ot' => $jobcard_total_ot,
//                        'day_status' => $jobcard_day_status,
//                    );
//
//                endforeach;
//            }
//            if (!empty($datarows)) {
//                $content .='<tbody>';
//                foreach ($datarows as $draw):
//
//                    $content .='<tr>';
//                    for ($i = 0; $i <= $rows - 1; $i++):
//                        $fid = $excelArray[$i];
//                        $content .='<td>' . $draw[$fid] . '</td>';
//                    endfor;
//                    $content .='</tr>';
//                endforeach;
//                $content .='</tbody>';
//            }
//
//
//            $content .='</table>';
//
//            $content .='<br />';
//
//            $content .='<h4>Total : ' . count($datarows) . '</h4>';
//
//
//            $content .='<br /><br /><br /><table border="0" width="100%">';
//            $content .='<tr>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
//            $content .='</tr>';
//
//
//            $content .='</table>';
//        }
//
//
//        $dompdf = new Dompdf();
//        $dompdf->set_option('isHtml5ParserEnabled', true);
//        $dompdf->loadHtml($content);
//
//        // (Optional) Setup the paper size and orientation
//        $dompdf->setPaper('A4', 'landscape');
//
//        // Render the HTML as PDF
//        $dompdf->render();
//
//        // Output the generated PDF to Browser
//        $dompdf->stream();
//    }
//
//    // User job card report
//    #
//    #
//    #
//    #
//    // User job card report
//
//    public function UserexportExcel(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
//
//
//        $emp_code = $request->emp_code;
//        $start_date = $request->start_date;
//        $end_date = $request->end_date;
//
//        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//        $company_id = $sqlEmp[0]->company_id;
//
//
//        $sqlDates = Calendar::where('company_id', $company_id)
//                ->whereBetween('date', [$start_date, $end_date])
//                ->get();
//
//        if (!empty($sqlDates)) {
//            // $json = [];
//            foreach ($sqlDates as $line):
//
//                $ld = $line->date;
//
//                $data = DB::table('attendance_jobcards')
//                        ->select(
//                                'attendance_jobcards.id', 'attendance_jobcards.user_in_time', 'attendance_jobcards.user_out_time', 'attendance_jobcards.user_total_time', 'attendance_jobcards.user_total_ot', 'attendance_jobcards.user_day_status'
//                        )
//                        ->where('attendance_jobcards.emp_code', $emp_code)
//                        ->where(function($q) use ($ld) {
//                            $q->where('attendance_jobcards.start_date', $ld);
//                            $q->orWhere('attendance_jobcards.end_date', $ld);
//                        })
//                        ->orderBy('attendance_jobcards.id', 'DESC')
//                        ->get();
//
//                if (count($data) != 0) {
//                    $jobcard_id = $data[0]->id;
//                    $jobcard_in_time = $data[0]->user_in_time;
//                    $jobcard_out_time = $data[0]->user_out_time;
//                    $jobcard_total_time = $data[0]->user_total_time;
//                    $jobcard_total_ot = $data[0]->user_total_ot;
//                    $jobcard_day_status = $data[0]->user_day_status;
//                } else {
//                    $jobcard_in_time = "00:00:00";
//                    $jobcard_out_time = "00:00:00";
//                    $jobcard_total_time = "00:00:00";
//                    $jobcard_total_ot = "00:00:00";
//                    $jobcard_day_status = "A";
//                }
//
//                $json[] = array('date' => $ld,
//                    'in_time' => $jobcard_in_time,
//                    'out_time' => $jobcard_in_time,
//                    'total_time' => $jobcard_total_time,
//                    'total_ot' => $jobcard_total_ot,
//                    'day_status' => $jobcard_day_status,
//                );
//
//            endforeach;
//        }
//
//
//        $excelArray = [];
//
//        // Define the Excel spreadsheet headers
//        $excelArray [] = [
//            'Date',
//            'In Time',
//            'Out Time',
//            'Total Working Time',
//            'Total Over Time',
//            'Day Status',
//        ];
//
//        // Convert each member of the returned collection into an array,
//        // and append it to the payments array.get_object_vars()
//        foreach ($json as $key => $field) {
//            $excelArray[] = $field;
//        }
//        //exit();
//        // Generate and return the spreadsheet
//        \Excel::create('User Jobcard Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {
//
//            // Set the spreadsheet title, creator, and description
//            $excel->setTitle('User Jobcard Report');
//            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
//            $excel->setDescription('User Jobcard Report');
//
//            // Build the spreadsheet, passing in the payments array
//            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
//                $sheet->fromArray($excelArray, null, 'A1', false, false);
//            });
//        })->download('xlsx');
//    }
//
//    public function UserexportPdf(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
//        $content = '<h3>User Jobcard Report</h3>';
//        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
//        // instantiate and use the dompdf class
//        $excelArray = [
//            'date',
//            'in_time',
//            'out_time',
//            'total_time',
//            'total_ot',
//            'day_status',
//        ];
//
//        if (!empty($excelArray)) {
//            $content .='<table width="100%">';
//            $content .='<thead>';
//            $content .='<tr>';
//            foreach ($excelArray as $exhead):
//                $content .='<th>' . $exhead . '</th>';
//            endforeach;
//            $content .='</tr>';
//            $content .='</thead>';
//
//
//            $rows = count($excelArray);
//            $emp_code = $request->emp_code;
//            $start_date = $request->start_date;
//            $end_date = $request->end_date;
//
//
//            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();
//
//            $company_id = $sqlEmp[0]->company_id;
//
//
//            $sqlDates = Calendar::where('company_id', $company_id)
//                    ->whereBetween('date', [$start_date, $end_date])
//                    ->get();
//
//            if (!empty($sqlDates)) {
//                $datarows = [];
//                foreach ($sqlDates as $line):
//
//                    $ld = $line->date;
//
//                    $data = DB::table('attendance_jobcards')
//                            ->select(
//                                    'attendance_jobcards.id', 'attendance_jobcards.user_in_time', 'attendance_jobcards.user_out_time', 'attendance_jobcards.user_total_time', 'attendance_jobcards.user_total_ot', 'attendance_jobcards.user_day_status'
//                            )
//                            ->where('attendance_jobcards.emp_code', $emp_code)
//                            ->where(function($q) use ($ld) {
//                                $q->where('attendance_jobcards.start_date', $ld);
//                                $q->orWhere('attendance_jobcards.end_date', $ld);
//                            })
//                            ->orderBy('attendance_jobcards.id', 'DESC')
//                            ->get();
//
//                    if (count($data) != 0) {
//                        $jobcard_id = $data[0]->id;
//                        $jobcard_in_time = $data[0]->user_in_time;
//                        $jobcard_out_time = $data[0]->user_out_time;
//                        $jobcard_total_time = $data[0]->user_total_time;
//                        $jobcard_total_ot = $data[0]->user_total_ot;
//                        $jobcard_day_status = $data[0]->user_day_status;
//                    } else {
//                        $jobcard_in_time = "00:00:00";
//                        $jobcard_out_time = "00:00:00";
//                        $jobcard_total_time = "00:00:00";
//                        $jobcard_total_ot = "00:00:00";
//                        $jobcard_day_status = "A";
//                    }
//
//                    $datarows[] = array('date' => $ld,
//                        'in_time' => $jobcard_in_time,
//                        'out_time' => $jobcard_in_time,
//                        'total_time' => $jobcard_total_time,
//                        'total_ot' => $jobcard_total_ot,
//                        'day_status' => $jobcard_day_status,
//                    );
//
//                endforeach;
//            }
//            if (!empty($datarows)) {
//                $content .='<tbody>';
//                foreach ($datarows as $draw):
//
//                    $content .='<tr>';
//                    for ($i = 0; $i <= $rows - 1; $i++):
//                        $fid = $excelArray[$i];
//                        $content .='<td>' . $draw[$fid] . '</td>';
//                    endfor;
//                    $content .='</tr>';
//                endforeach;
//                $content .='</tbody>';
//            }
//
//
//            $content .='</table>';
//
//            $content .='<br />';
//
//            $content .='<h4>Total : ' . count($datarows) . '</h4>';
//
//
//            $content .='<br /><br /><br /><table border="0" width="100%">';
//            $content .='<tr>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
//            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
//            $content .='</tr>';
//
//
//            $content .='</table>';
//        }
//
//
//        $dompdf = new Dompdf();
//        $dompdf->set_option('isHtml5ParserEnabled', true);
//        $dompdf->loadHtml($content);
//
//        // (Optional) Setup the paper size and orientation
//        $dompdf->setPaper('A4', 'landscape');
//
//        // Render the HTML as PDF
//        $dompdf->render();
//
//        // Output the generated PDF to Browser
//        $dompdf->stream();
//    }
}
