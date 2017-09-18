<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\Calendar;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;
use App\WeekendOTPolicy;
use App\AttendanceJobcard;

class AdditionalOTReportController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexSummary() {
        $RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
		
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');

        return view('module.settings.additionalOTSummary', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    public function indexReport() {
        $company = Company::all();

        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');

        return view('module.settings.additionalOTReport', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function CalculateTwoTimeHourMinSec($start_time, $end_time) {
        $ShiftST = new \DateTime($start_time);
        $ShiftED = new \DateTime($end_time);
        $totalShiftHourDiff = $ShiftST->diff($ShiftED);
        $totalShiftHour = $totalShiftHourDiff->format("%H:%I:%S");

        return $totalShiftHour;
    }

    private function MakeTimeDifference($auto_start_date, $shift_start_time, $auto_end_date, $shift_end_time) {
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
    }

    private function sum_time($arg) {
        $i = 0;
        foreach ($arg as $time) {
            sscanf($time, '%d:%d', $hour, $min);
            $i += $hour * 60 + $min;
        }
        if ($h = floor($i / 60)) {
            $i %= 60;
        }
        return sprintf('%02d:%02d', $h, $i);
    }

    //echo sum_time('02:05', '00:02', '05:59');
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
	private function WeekendOT($jobcard_id = 0, $jobcard_day_status = "A", $jobcard_total_time = '00:00:00', $jobcard_out_time = '00:00:00', $company_id = 0) {


        if (($jobcard_day_status == "W" || $jobcard_day_status == "H") && !empty($jobcard_out_time)) {


            if ($jobcard_out_time != '00:00:00') {



                $chkWHP = WeekendOTPolicy::where('company_id', $company_id)->count();
                if ($chkWHP == 0) {

                    $tab = AttendanceJobcard::find($jobcard_id);

                    $formatedTime = $this->CalCulateTtalInTimeBetween($tab->start_date . ' ' . $tab->admin_in_time, $tab->end_date . ' ' . $tab->admin_out_time);
                    $tab->admin_total_ot = $formatedTime;
                    $tab->user_total_ot = $formatedTime;
                    $tab->audit_total_ot = $formatedTime;
                    $tab->save();
                } else {



                    $sqlWHP = WeekendOTPolicy::where('company_id', $company_id)->first();
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
                            $formatedTime = $this->CalCulateTtalInTimeBetween($sqlWHP->hour_after, $formatedTimeTotal);
                            $tab->admin_total_ot = $formatedTime;
                            $tab->user_total_ot = $formatedTime;
                            $tab->audit_total_ot = $formatedTime;
                            $tab->save();
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
    public function showSummary(Request $request) {

        $department = $request->department;
        $end_date = $request->end_date;
        $start_date = $request->start_date;
        if (!empty($request->company)) {
            $company_id = $request->company;
        } else {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }

		$sqlEmp = DB::table('employee_infos')
                ->join('employee_staff_grades', 'employee_infos.emp_code', '=', 'employee_staff_grades.emp_code')
                ->join('staff_grades', 'employee_staff_grades.staff_grade_id', '=', 'staff_grades.id')
                ->select('employee_infos.id', 'employee_infos.emp_code')
				//->where('employee_infos.company_id', $company_id)
                //->where('employee_infos.emp_code','RPAC0020')
                ->when($company_id, function ($query) use ($company_id) {
                    return $query->where('employee_infos.company_id', $company_id);
                })
                ->when($department, function ($query) use ($department) {
                    return $query->where('employee_staff_grades.department_id', $department);
                })
                ->where('staff_grades.is_ot_eligible', 1)
                ->get();		
				
				
				

        $sqlJobcardDataPolicy = DB::table('attendance_jobcard_policies')
								->select('attendance_jobcard_policies.user_max_ot_hour')
								->where('attendance_jobcard_policies.is_user_data_show_policy', 1)
								->first();
		$user_max_ot_hour = '00:00:00';		
        if (isset($sqlJobcardDataPolicy->user_max_ot_hour)) {
            $user_max_ot_hour = $sqlJobcardDataPolicy->user_max_ot_hour;
        } else {
            $user_max_ot_hour = '00:00:00';
        }
		
		$sqlWOTDataPolicy = DB::table('weekend_o_t_policies')->first();
		$wot_user_start_ot_hour = '00:00:00';		
        if (isset($sqlWOTDataPolicy->is_ot_will_start_after_fix_hour)) {
			if($sqlWOTDataPolicy->is_ot_will_start_after_fix_hour==1)
			{
				$wot_user_start_ot_hour = $sqlWOTDataPolicy->hour_after;
			}
			else
			{
				$wot_user_start_ot_hour = '00:00:00';
			}
            
        } else {
            $wot_user_start_ot_hour = '00:00:00';
        }
		
		$wot_user_max_ot_hour = '00:00:00';		
        if (isset($sqlWOTDataPolicy->is_standard_max_ot_hour)) {
			if($sqlWOTDataPolicy->is_standard_max_ot_hour==1)
			{
				$wot_user_max_ot_hour = $sqlWOTDataPolicy->standard_max_ot_hour;
			}
			else
			{
				$wot_user_max_ot_hour = '00:00:00';
			}
            
        } else {
            $wot_user_max_ot_hour = '00:00:00';
        }
		


        $json = [];

        $ddates = [];
        $sqlDates = DB::table('calendars')
                ->select(DB::raw("calendars.date"))
                ->where("calendars.company_id", $company_id)
                ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                ->groupby(DB::raw("calendars.date"))
                ->get();


        if (!empty($sqlDates)) {
            foreach ($sqlDates as $log):
                $ddates[] = array('date' => date('d-M', strtotime($log->date)));
            endforeach;
        }

        $json[] = array('emp_codeH' => 'EMP CODE', 'ddataH' => $ddates, 'totalH' => 'Total Additional OT');

        if (!empty($sqlEmp)) {

            foreach ($sqlEmp as $row):
                $ddate = [];
				$emp_code=$row->emp_code;
				$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
				if(empty($cal_company_id))
				{
					$cal_company_id=$company_id;
				}
                $sqlDates = DB::table('calendars')
							->Join('day_types','calendars.day_type_id','=','day_types.id')
							->leftjoin('attendance_jobcards', function ($join) use ($emp_code) {
								$join->on('calendars.date', '=', 'attendance_jobcards.start_date')
								->where('attendance_jobcards.emp_code', '=', $emp_code);
							})
							->select(DB::raw("calendars.date,day_types.day_short_code,
							attendance_jobcards.admin_day_status, 
							attendance_jobcards.id, 
							attendance_jobcards.company_id, 
							attendance_jobcards.admin_in_time,
							attendance_jobcards.admin_out_time,
							attendance_jobcards.start_date,
							attendance_jobcards.end_date,
							attendance_jobcards.admin_total_time,
							attendance_jobcards.ll_ref,
							IFNULL(attendance_jobcards.admin_total_ot,'00:00:00') as total_ot"))
							->where("calendars.company_id", $cal_company_id)
							->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
							->groupby(DB::raw("calendars.date"))
							->get();

                $day_total_ot = array();
                if (!empty($sqlDates)) {
                    foreach ($sqlDates as $log):
                        //$total_ot = $log->total_ot ? $log->total_ot : '00:00:00';
                        //if ($total_ot > '00:00:00') {
                          //  $total_ot = $this->MakeTimeDifference($log->date, $total_ot, $log->date, $user_max_ot_hour);
                        //}
                        //$ddate[] = array('date' => $log->date, 'total_ot' => $total_ot);
                        //array_push($day_total_ot, $total_ot);
						$rowLLDate=$log->date;
						
						
						
						
						$total_ot = $log->total_ot ? $log->total_ot : '00:00:00';
                        $cal_day_type=$log->day_short_code;
						$new_total_ot='00:00:00';
						$std_day=array("W","H");
						if(in_array($log->admin_day_status,$std_day))
						{
							if($log->admin_in_time=='00:00:00')
							{
								$total_ot = '00:00:00';
							}
							elseif(empty($log->admin_in_time))
							{
								$total_ot = '00:00:00';
							}
							else
							{
								
								if($log->admin_out_time=='00:00:00')
								{
									$total_ot = '00:00:00';	
								}
								elseif(empty($log->admin_out_time))
								{
									$total_ot = '00:00:00';	
								}
								else
								{
								
									$admin_total_time=$this->CalCulateTtalInTimeBetween($log->start_date.' '.$log->admin_in_time,$log->end_date.' '.$log->admin_out_time);
									
									
									$this->WeekendOT($log->id, 
													 $log->admin_day_status, 
													 $admin_total_time, 
													 $log->admin_out_time, 
													 $log->company_id);
													
									$new_total_ot = $this->CalCulateTtalInTimeBetween($wot_user_start_ot_hour, $admin_total_time);
									
									//LL Reference Check Start
									$ref_flag=0;
									$ll_flag=0;
									if($rowLLDate>='2017-07-01')
									{
										$ref_flag=1;
										$ll_flag=0;
										if(!empty($log->ll_ref))
										{
											
											if($log->ll_ref=="0000-00-00")
											{
												$ll_flag=0;
											}
											else
											{
												$ll_flag=1;
											}
										}
									}
									//LL Reference Check End
									
									if($ref_flag==1 && $ll_flag==1)
									{
										$new_total_ots=$new_total_ot; //calculating all in one ot
									}
									else
									{
										if($new_total_ot>=$wot_user_max_ot_hour)
										{
											$new_total_ot=$this->CalCulateTtalInTimeBetween($wot_user_start_ot_hour, $new_total_ot);
										}
										else
										{
											$new_total_ot='00:00:00';
										}
									}
									
									

									$total_ot = $new_total_ot?$new_total_ot:'00:00:00';
								}
							}	
							
							$total_ot =$total_ot?$total_ot:'00:00:00';
							
						}
						elseif(in_array($cal_day_type,$std_day))
						{
							if($log->admin_in_time=='00:00:00')
							{
								$total_ot = '00:00:00';
							}
							elseif(empty($log->admin_in_time))
							{
								$total_ot = '00:00:00';
							}
							else
							{
								
								if($log->admin_out_time=='00:00:00')
								{
									$total_ot = '00:00:00';	
								}
								elseif(empty($log->admin_out_time))
								{
									$total_ot = '00:00:00';	
								}
								else
								{
								
									$admin_total_time=$this->CalCulateTtalInTimeBetween($log->start_date.' '.$log->admin_in_time,$log->end_date.' '.$log->admin_out_time);
									
									
									$this->WeekendOT($log->id, 
													 $log->admin_day_status, 
													 $admin_total_time, 
													 $log->admin_out_time, 
													 $log->company_id);
													
									$new_total_ot = $this->CalCulateTtalInTimeBetween($wot_user_start_ot_hour, $admin_total_time);	


									//LL Reference Check Start
									$ref_flag=0;
									$ll_flag=0;
									if($rowLLDate>='2017-07-01')
									{
										$ref_flag=1;
										$ll_flag=0;
										if(!empty($log->ll_ref))
										{
											
											if($log->ll_ref=="0000-00-00")
											{
												$ll_flag=0;
											}
											else
											{
												$ll_flag=1;
											}
										}
									}
									//LL Reference Check End
									
									if($ref_flag==1 && $ll_flag==1)
									{
										$new_total_ots=$new_total_ot; //calculating all in one ot
									}
									else
									{
									
										if($new_total_ot>=$wot_user_max_ot_hour)
										{
											$new_total_ot=$this->CalCulateTtalInTimeBetween($wot_user_start_ot_hour, $new_total_ot);
										}
										else
										{
											$new_total_ot='00:00:00';
										}
									
									}
									
									$total_ot = $new_total_ot?$new_total_ot:'00:00:00';
								}
							}
							
							$total_ot =$total_ot?$total_ot:'00:00:00';
						}
						else
						{
							if($log->admin_in_time=='00:00:00')
							{
								$total_ot = '00:00:00';
							}
							elseif(empty($log->admin_in_time))
							{
								$total_ot = '00:00:00';
							}
							else
							{
								
								if($log->admin_out_time=='00:00:00')
								{
									$total_ot = '00:00:00';	
								}
								elseif(empty($log->admin_out_time))
								{
									$total_ot = '00:00:00';	
								}
								else
								{
									if ($total_ot >= $user_max_ot_hour) {
										//$total_ot = $user_max_ot_hour;
										$total_ot=$this->CalCulateTtalInTimeBetween($user_max_ot_hour, $total_ot);
									}
									else
									{
										$total_ot = '00:00:00';
									}
									
								}
							}
						}
						
						$ddate[] = array('date' => $log->date, 'total_ot' => $total_ot);
						
                        array_push($day_total_ot, $total_ot);
                    endforeach;
                }

                $total = $this->sum_time($day_total_ot);

                $json[] = array('emp_code' => $row->emp_code, 'ddata' => $ddate, 'totalot' => $total);
            endforeach;
        }

        return response()->json($json);

//return response()->json(array("data" => $json, "total" => count($json)));
    }

    public function showReport(Request $request) {

        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;


        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();

        $company_id = $sqlEmp[0]->company_id;


        $sqlDates = Calendar::where('company_id', $company_id)
                ->whereBetween('date', [$start_date, $end_date])
                ->get();


        $sqlJobcardDataPolicy = DB::table('attendance_jobcard_policies')
                ->select(
                        'attendance_jobcard_policies.user_max_ot_hour'
                )
                ->where('attendance_jobcard_policies.is_user_data_show_policy', 1)
                ->get();
        if (count($sqlJobcardDataPolicy) > 0) {
            $user_max_ot_hour = $sqlJobcardDataPolicy[0]->user_max_ot_hour;
        } else {
            $user_max_ot_hour = '00:00:00';
        }


        $json = [];


        if (!empty($sqlEmp)) {

            foreach ($sqlEmp as $row):
                $ddate = [];
                $sqlDates = DB::table('calendars')
                        ->select(DB::raw("calendars.date, (SELECT attendance_jobcards.admin_total_ot FROM attendance_jobcards WHERE attendance_jobcards.start_date=" . DB::raw("calendars.date") . " AND attendance_jobcards.emp_code='" . $row->emp_code . "') as total_ot"))
                        ->where("calendars.company_id", $company_id)
                        ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                        ->groupby(DB::raw("calendars.date"))
                        ->get();


                if (!empty($sqlDates)) {
                    foreach ($sqlDates as $log):
                        $total_ot = $log->total_ot ? $log->total_ot : '00:00:00';
                        if ($total_ot > '00:00:00') {

                            $total_ot = new \DateTime($total_ot);
                            $user_max_ot_hour = new \DateTime($user_max_ot_hour);
                            $User_dteDiff = $total_ot->diff($user_max_ot_hour);
                            $total_ot = $User_dteDiff->format("%H:%I:%S");
                        }
                        $json[] = array('date' => $log->date, 'total_ot' => $total_ot);
                    endforeach;
                }

//                $json= array($ddate);
            endforeach;
        }

// return response()->json($json);

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AttendanceRawData  $attendanceRawData
     * @return \Illuminate\Http\Response
     */
    public function exportExcelSummary(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
        $department = $request->department;
        $end_date = $request->end_date;
        $start_date = $request->start_date;
        if (!empty($request->company)) {
            $company_id = $request->company;
        } else {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }


        $sqlEmp = DB::table('employee_infos')
                ->join('employee_staff_grades', 'employee_infos.emp_code', '=', 'employee_staff_grades.emp_code')
                ->join('staff_grades', 'employee_staff_grades.staff_grade_id', '=', 'staff_grades.id')
                ->select('employee_infos.id', 'employee_infos.emp_code')
				//->where('employee_infos.company_id', $company_id)
				//->where('employee_infos.emp_code','RPAC0020')
                ->when($company_id, function ($query) use ($company_id) {
                    return $query->where('employee_infos.company_id', $company_id);
                })
                ->when($department, function ($query) use ($department) {
                    return $query->where('employee_staff_grades.department_id', $department);
                })
                ->where('staff_grades.is_ot_eligible', 1)
                ->get();

       
		
		
		$sqlJobcardDataPolicy = DB::table('attendance_jobcard_policies')
								->select('attendance_jobcard_policies.user_max_ot_hour')
								->where('attendance_jobcard_policies.is_user_data_show_policy', 1)
								->first();
		$user_max_ot_hour = '00:00:00';		
        if (isset($sqlJobcardDataPolicy->user_max_ot_hour)) {
            $user_max_ot_hour = $sqlJobcardDataPolicy->user_max_ot_hour;
        } else {
            $user_max_ot_hour = '00:00:00';
        }
		
		$sqlWOTDataPolicy = DB::table('weekend_o_t_policies')->first();
		$wot_user_start_ot_hour = '00:00:00';		
        if (isset($sqlWOTDataPolicy->is_ot_will_start_after_fix_hour)) {
			if($sqlWOTDataPolicy->is_ot_will_start_after_fix_hour==1)
			{
				$wot_user_start_ot_hour = $sqlWOTDataPolicy->hour_after;
			}
			else
			{
				$wot_user_start_ot_hour = '00:00:00';
			}
            
        } else {
            $wot_user_start_ot_hour = '00:00:00';
        }
		
		$wot_user_max_ot_hour = '00:00:00';		
        if (isset($sqlWOTDataPolicy->is_standard_max_ot_hour)) {
			if($sqlWOTDataPolicy->is_standard_max_ot_hour==1)
			{
				$wot_user_max_ot_hour = $sqlWOTDataPolicy->standard_max_ot_hour;
			}
			else
			{
				$wot_user_max_ot_hour = '00:00:00';
			}
            
        } else {
            $wot_user_max_ot_hour = '00:00:00';
        }
		


//        $json = [];
        $ExcelDataArray = [];

        $ddates = [];
        $sqlDates = DB::table('calendars')
                ->select(DB::raw("calendars.date"))
                ->where("calendars.company_id", $company_id)
                ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                ->groupby(DB::raw("calendars.date"))
                ->get();


        if (!empty($sqlDates)) {
            foreach ($sqlDates as $log):
                $ddates[] = array('date' => date('d-M', strtotime($log->date)));
            endforeach;
        }

//        $json[] = array('emp_codeH' => 'EMP CODE', 'ddataH' => $ddates);
        $jsonH[] = array('ddataH' => $ddates);
        if (!empty($sqlEmp)) {

            foreach ($sqlEmp as $row):
                $ddate = [];
                $emp_code = $row->emp_code;
				$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
				if(empty($cal_company_id))
				{
					$cal_company_id=$company_id;
				}
                $sqlDates = DB::table('calendars')
                        ->Join('day_types','calendars.day_type_id','=','day_types.id')
						->leftjoin('attendance_jobcards', function ($join) use ($emp_code) {
                            $join->on('calendars.date', '=', 'attendance_jobcards.start_date')
                            ->where('attendance_jobcards.emp_code', '=', $emp_code);
                        })
                        //->select(DB::raw("calendars.date,day_types.day_short_code, IFNULL(attendance_jobcards.user_total_ot,'00:00:00') as total_ot"))
						->select(DB::raw("calendars.date,day_types.day_short_code,
						attendance_jobcards.admin_day_status, 
						attendance_jobcards.id, 
						attendance_jobcards.company_id, 
						attendance_jobcards.admin_in_time,
						attendance_jobcards.admin_out_time,
						attendance_jobcards.start_date,
						attendance_jobcards.end_date,
						attendance_jobcards.admin_total_time,	
						attendance_jobcards.ll_ref,	
						IFNULL(attendance_jobcards.admin_total_ot,'00:00:00') as total_ot"))
                        ->where("calendars.company_id",$cal_company_id)
                        ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                        ->groupby(DB::raw("calendars.date"))
                        ->get();

                $day_total_ot = array();
                if (!empty($sqlDates)) {
                    foreach ($sqlDates as $log):
						$rowLLDate=$log->date;
                        $total_ot = $log->total_ot ? $log->total_ot : '00:00:00';
                        $cal_day_type=$log->day_short_code;
						$new_total_ot='00:00:00';
						$std_day=array("W","H");
						if(in_array($log->admin_day_status,$std_day))
						{
							if($log->admin_in_time=='00:00:00')
							{
								$total_ot = '00:00:00';
							}
							elseif(empty($log->admin_in_time))
							{
								$total_ot = '00:00:00';
							}
							else
							{
								
								if($log->admin_out_time=='00:00:00')
								{
									$total_ot = '00:00:00';	
								}
								elseif(empty($log->admin_out_time))
								{
									$total_ot = '00:00:00';	
								}
								else
								{
								
									$admin_total_time=$this->CalCulateTtalInTimeBetween($log->start_date.' '.$log->admin_in_time,$log->end_date.' '.$log->admin_out_time);
									
									
									$this->WeekendOT($log->id, 
													 $log->admin_day_status, 
													 $admin_total_time, 
													 $log->admin_out_time, 
													 $log->company_id);
													
									$new_total_ot = $this->CalCulateTtalInTimeBetween($wot_user_start_ot_hour, $admin_total_time);
									
									//LL Reference Check Start
									$ref_flag=0;
									$ll_flag=0;
									if($rowLLDate>='2017-07-01')
									{
										$ref_flag=1;
										$ll_flag=0;
										if(!empty($log->ll_ref))
										{
											
											if($log->ll_ref=="0000-00-00")
											{
												$ll_flag=0;
											}
											else
											{
												$ll_flag=1;
											}
										}
									}
									//LL Reference Check End
									
									if($ref_flag==1 && $ll_flag==1)
									{
										$new_total_ots=$new_total_ot; //calculating all in one ot
									}
									else
									{
									
										if($new_total_ot>=$wot_user_max_ot_hour)
										{
											$new_total_ot=$this->CalCulateTtalInTimeBetween($wot_user_max_ot_hour, $new_total_ot);
										}
										else
										{
											$new_total_ot = '00:00:00';	
										}
									
									}

									$total_ot = $new_total_ot?$new_total_ot:'00:00:00';
								}
							}	
							
							$total_ot =$total_ot?$total_ot:'00:00:00';
							
						}
						elseif(in_array($cal_day_type,$std_day))
						{
							if($log->admin_in_time=='00:00:00')
							{
								$total_ot = '00:00:00';
							}
							elseif(empty($log->admin_in_time))
							{
								$total_ot = '00:00:00';
							}
							else
							{
								
								if($log->admin_out_time=='00:00:00')
								{
									$total_ot = '00:00:00';	
								}
								elseif(empty($log->admin_out_time))
								{
									$total_ot = '00:00:00';	
								}
								else
								{
								
									$admin_total_time=$this->CalCulateTtalInTimeBetween($log->start_date.' '.$log->admin_in_time,$log->end_date.' '.$log->admin_out_time);
									
									
									$this->WeekendOT($log->id, 
													 $log->admin_day_status, 
													 $admin_total_time, 
													 $log->admin_out_time, 
													 $log->company_id);
													
									$new_total_ot = $this->CalCulateTtalInTimeBetween($wot_user_start_ot_hour, $admin_total_time);
									
									//LL Reference Check Start
									$ref_flag=0;
									$ll_flag=0;
									if($rowLLDate>='2017-07-01')
									{
										$ref_flag=1;
										$ll_flag=0;
										if(!empty($log->ll_ref))
										{
											
											if($log->ll_ref=="0000-00-00")
											{
												$ll_flag=0;
											}
											else
											{
												$ll_flag=1;
											}
										}
									}
									//LL Reference Check End
									
									if($ref_flag==1 && $ll_flag==1)
									{
										$new_total_ots=$new_total_ot; //calculating all in one ot
									}
									else
									{
									
										if($new_total_ot>=$wot_user_max_ot_hour)
										{
											$new_total_ot=$this->CalCulateTtalInTimeBetween($wot_user_max_ot_hour, $new_total_ot);
										}
										else
										{
											$new_total_ot = '00:00:00';	
										}
									
									}
									$total_ot = $new_total_ot?$new_total_ot:'00:00:00';
								}
							}
							
							$total_ot =$total_ot?$total_ot:'00:00:00';
						}
						else
						{
							if($log->admin_in_time=='00:00:00')
							{
								$total_ot = '00:00:00';
							}
							elseif(empty($log->admin_in_time))
							{
								$total_ot = '00:00:00';
							}
							else
							{
								
								if($log->admin_out_time=='00:00:00')
								{
									$total_ot = '00:00:00';	
								}
								elseif(empty($log->admin_out_time))
								{
									$total_ot = '00:00:00';	
								}
								else
								{
									if ($total_ot >= $user_max_ot_hour) {
										$total_ot = $this->CalCulateTtalInTimeBetween($user_max_ot_hour,$total_ot);
									}
									else
									{
										$total_ot = '00:00:00';	
									}
									
								}
							}
						}
						
                        
                        $ddate[] = $total_ot;
                    endforeach;
                }

                $total = $this->sum_time($ddate);
                //$total = $day_total_ot;

                array_unshift($ddate, $row->emp_code, $total);

                $ExcelDataArray[] = $ddate;
            endforeach;
        }



        $ExcelHeadding = [];
        $ExcelHeadding = array("Employee Code", "Total OT");

        // Define the Excel spreadsheet headers

        foreach ($jsonH as $key => $value) {
            //  echo $key;
            if ($key == 0) {

                foreach ($value as $key => $value2) {
                    //  print_r($value2);

                    foreach ($value2 as $key => $value3) {
                        //print_r($value3);
                        foreach ($value3 as $key => $value4) {
                            //echo $value4;
                            $ExcelHeadding [] = $value4;
                        }
                    }
                }
            }
        }



        // print_r($ExcelDataArray);

        $excelArray = array($ExcelHeadding);

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($ExcelDataArray as $key => $field) {
            $excelArray[] = $field;
        }

// Generate and return the spreadsheet
        // Generate and return the spreadsheet
        \Excel::create('Additional OT Report_' .$start_date.' TO '.$end_date, function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Over Time Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Over Time Report');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('Additional_OT_Report', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdfSummary(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
        app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        $content = '<h3>Attendance Report</h3>';
        $content .= '<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';

        $department = $request->department;
        $end_date = $request->end_date;
        $start_date = $request->start_date;
        if (!empty($request->company)) {
            $company_id = $request->company;
        } else {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }
        // instantiate and use the dompdf class
        $excelArray = [];
        $excelArray [] = "Employee code";
        $excelArray [] = "Total Additional OT";
        $ddates = [];

        $sqlDates = DB::table('calendars')
                ->select(DB::raw("calendars.date"))
                ->where("calendars.company_id", $company_id)
                ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                ->groupby(DB::raw("calendars.date"))
                ->get();

        if (!empty($sqlDates)) {
            foreach ($sqlDates as $log):
                $ddates[] = array('date' => date('d-M', strtotime($log->date)));
            endforeach;
        }
        $jsonH[] = array('ddataH' => $ddates);

        foreach ($jsonH as $key => $value) {
            //  echo $key;
            if ($key == 0) {

                foreach ($value as $key => $value2) {
                    //  print_r($value2);

                    foreach ($value2 as $key => $value3) {
                        //print_r($value3);
                        foreach ($value3 as $key => $value4) {
                            //echo $value4;
                            $excelArray [] = $value4;
                        }
                    }
                }
            }
        }

//        echo '<pre>';
//        print_r($excelArray);
//        exit();
        if (!empty($excelArray)) {
            $content .= '<table width="100%">';
            $content .= '<thead>';
            $content .= '<tr>';
            foreach ($excelArray as $exhead):
                $content .= '<th>' . $exhead . '</th>';
            endforeach;
            $content .= '</tr>';
            $content .= '</thead>';


            $rows = count($excelArray);



            $sqlEmp = DB::table('employee_departments')
                    ->select(
                            'employee_departments.id', 'employee_departments.emp_code'
                    )
                    ->where('company_id', $company_id)
                    ->where('department_id', $department)
                    ->groupby('employee_departments.emp_code')
                    ->orderby('employee_departments.id')
                    ->get();

            $sqlJobcardDataPolicy = DB::table('attendance_jobcard_policies')
                    ->select(
                            'attendance_jobcard_policies.user_max_ot_hour'
                    )
                    ->where('attendance_jobcard_policies.is_user_data_show_policy', 1)
                    ->get();
            if (count($sqlJobcardDataPolicy) > 0) {
                $user_max_ot_hour = $sqlJobcardDataPolicy[0]->user_max_ot_hour;
            } else {
                $user_max_ot_hour = '00:00:00';
            }

            $json = [];
            $ddate = [];
            if (!empty($sqlEmp)) {

                foreach ($sqlEmp as $row):

                    $sqlDates = DB::table('calendars')
                            ->select(DB::raw("calendars.date, (SELECT attendance_jobcards.admin_total_ot FROM attendance_jobcards WHERE attendance_jobcards.start_date=" . DB::raw("calendars.date") . " AND attendance_jobcards.emp_code='" . $row->emp_code . "') as total_ot"))
                            ->where("calendars.company_id", $company_id)
                            ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                            ->groupby(DB::raw("calendars.date"))
                            ->get();
                    $total = 0;
                    $day_total_ot = array();
                    if (!empty($sqlDates)) {
                        foreach ($sqlDates as $key => $log):
                            $total_ot = $log->total_ot ? $log->total_ot : '00:00:00';

                            if ($total_ot > '00:00:00') {
                                $total_ot = $this->MakeTimeDifference($log->date, $total_ot, $log->date, $user_max_ot_hour);
                            }
                            $ddate['Employee code'] = $row->emp_code;

                            $ddate[date('d-M', strtotime($log->date))] = $total_ot;

                        endforeach;
                    }
                    $total = $this->sum_time($day_total_ot);
                    $ddate['Total Additional OT'] = $total;
                    $json[] = $ddate;
                endforeach;
            }

            // Define the Excel spreadsheet headers
//            echo '<pre>';
//            print_r($json);
//            exit();

            if (!empty($json)) {
                $content .= '<tbody>';
                foreach ($json as $draw):

                    $content .= '<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .= '<td>' . $draw[$fid] . '</td>';
                    endfor;
                    $content .= '</tr>';
                endforeach;
                $content .= '</tbody>';
            }


            $content .= '</table>';

            $content .= '<br />';

            $content .= '<h4>Total : ' . count($json) . '</h4>';


            $content .= '<br /><br /><br /><table border="0" width="100%">';
            $content .= '<tr>';
            $content .= '<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
            $content .= '<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
            $content .= '<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
            $content .= '</tr>';


            $content .= '</table>';
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
//
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

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
    public function update(Request $request, $id) {
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

    public function exportExcelReport(Request $request) {
        $emp_code = $request->emp_code;
        $start_date = $request->start_date;
        $end_date = $request->end_date;


        $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();

        $company_id = $sqlEmp[0]->company_id;


        $sqlDates = Calendar::where('company_id', $company_id)
                ->whereBetween('date', [$start_date, $end_date])
                ->get();


        $sqlJobcardDataPolicy = DB::table('attendance_jobcard_policies')
                ->select(
                        'attendance_jobcard_policies.user_max_ot_hour'
                )
                ->where('attendance_jobcard_policies.is_user_data_show_policy', 1)
                ->get();
        if (count($sqlJobcardDataPolicy) > 0) {
            $user_max_ot_hour = $sqlJobcardDataPolicy[0]->user_max_ot_hour;
        } else {
            $user_max_ot_hour = '00:00:00';
        }


        $json = [];


        if (!empty($sqlEmp)) {

            foreach ($sqlEmp as $row):
                $ddate = [];
                $sqlDates = DB::table('calendars')
                        ->select(DB::raw("calendars.date, (SELECT attendance_jobcards.admin_total_ot FROM attendance_jobcards WHERE attendance_jobcards.start_date=" . DB::raw("calendars.date") . " AND attendance_jobcards.emp_code='" . $row->emp_code . "') as total_ot"))
                        ->where("calendars.company_id", $company_id)
                        ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                        ->groupby(DB::raw("calendars.date"))
                        ->get();


                if (!empty($sqlDates)) {
                    foreach ($sqlDates as $log):
                        $total_ot = $log->total_ot ? $log->total_ot : '00:00:00';
                        if ($total_ot > '00:00:00') {

                            $total_ot = new \DateTime($total_ot);
                            $user_max_ot_hour = new \DateTime($user_max_ot_hour);
                            $User_dteDiff = $total_ot->diff($user_max_ot_hour);
                            $total_ot = $User_dteDiff->format("%H:%I:%S");
                        }
                        $json[] = array('date' => $log->date, 'total_ot' => $total_ot);
                    endforeach;
                }
            endforeach;
        }



        $excelArray = [];

// Define the Excel spreadsheet headers
        $excelArray [] = [
            'Date',
            'Total Additional Over Time',
        ];

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
//exit();
// Generate and return the spreadsheet
        \Excel::create('Additional Over Time Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

// Set the spreadsheet title, creator, and description
            $excel->setTitle('Additional Over Time Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Additional Over Time Report');

// Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdfReport(Request $request) {
        $content = '<h3>Additional Over Time Report</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
// instantiate and use the dompdf class
        $excelArray = [
            'date',
            'total_ot',
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


            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();

            $company_id = $sqlEmp[0]->company_id;


            $sqlDates = Calendar::where('company_id', $company_id)
                    ->whereBetween('date', [$start_date, $end_date])
                    ->get();


            $sqlJobcardDataPolicy = DB::table('attendance_jobcard_policies')
                    ->select(
                            'attendance_jobcard_policies.user_max_ot_hour'
                    )
                    ->where('attendance_jobcard_policies.is_user_data_show_policy', 1)
                    ->get();
            if (count($sqlJobcardDataPolicy) > 0) {
                $user_max_ot_hour = $sqlJobcardDataPolicy[0]->user_max_ot_hour;
            } else {
                $user_max_ot_hour = '00:00:00';
            }

            $json = [];

            if (!empty($sqlEmp)) {

                foreach ($sqlEmp as $row):
                    $ddate = [];
                    $sqlDates = DB::table('calendars')
                            ->select(DB::raw("calendars.date, (SELECT attendance_jobcards.admin_total_ot FROM attendance_jobcards WHERE attendance_jobcards.start_date=" . DB::raw("calendars.date") . " AND attendance_jobcards.emp_code='" . $row->emp_code . "') as total_ot"))
                            ->where("calendars.company_id", $company_id)
                            ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                            ->groupby(DB::raw("calendars.date"))
                            ->get();


                    if (!empty($sqlDates)) {
                        foreach ($sqlDates as $log):
                            $total_ot = $log->total_ot ? $log->total_ot : '00:00:00';
                            if ($total_ot > '00:00:00') {

                                $total_ot = new \DateTime($total_ot);
                                $user_max_ot_hour = new \DateTime($user_max_ot_hour);
                                $User_dteDiff = $total_ot->diff($user_max_ot_hour);
                                $total_ot = $User_dteDiff->format("%H:%I:%S");
                            }
                            $json[] = array('date' => $log->date, 'total_ot' => $total_ot);
                        endforeach;
                    }
                endforeach;
            }
//return response()->json($json);

            if (!empty($json)) {
                $content .='<tbody>';
                foreach ($json as $draw):

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

            $content .='<h4>Total : ' . count($json) . '</h4>';


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

}
