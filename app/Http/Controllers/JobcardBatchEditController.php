<?php

namespace App\Http\Controllers;

use App\jobcardBatchEdit;
use Illuminate\Http\Request;

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

class JobcardBatchEditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('module.settings.jobcardBatchEditMode');
    }
	
	public function indexEx($emp_code=0,$start_date='0000-00-00',$end_date='0000-00-00',$msg_type='error')
    {
		
		if($msg_type=='error')
		{
			\session()->flash('error','Nothing to Modify.');
		}
		elseif($msg_type=='success')
		{
			\session()->flash('success','Information successfully modified.');
		}
        return view('module.settings.jobcardBatchEditMode',['emp_code'=>$emp_code,'start_date'=>$start_date,'end_date'=>$end_date]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
		
		if(empty($cal_company_id))
		{
			$cal_company_id=$company_id;
		}

        $sqlDates = Calendar::where('calendars.company_id', $cal_company_id)
				->leftJoin('day_types','calendars.day_type_id','=','day_types.id')
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
		$edit_jobcard_flag=0;
        if (!empty($sqlDates)) {
            $json = [];
			$admin_time_array_total=array();
			$user_time_array_total=array();
            foreach ($sqlDates as $line):
				$edit_jobcard_flag=0;
                $ld = $line->date;
				$cal_day_type=$line->day_short_code;
                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.emp_code', 'attendance_jobcards.start_date', 'attendance_jobcards.end_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 
								'attendance_jobcards.user_out_time', 
								'attendance_jobcards.admin_total_time', 
								'attendance_jobcards.admin_total_ot', 
								'attendance_jobcards.user_total_ot', 
								'attendance_jobcards.admin_day_status',
								'attendance_jobcards.ll_ref'
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
                    $jobcard_in_time = $data[0]->admin_in_time;
                    $jobcard_out_time = $data[0]->admin_out_time;
                    
                    $jobcard_total_time = $data[0]->admin_total_time;
                    $jobcard_total_ot = $data[0]->admin_total_ot;
					$jobcard_user_out_time = $data[0]->user_out_time;
                    $jobcard_user_total_ot = $data[0]->user_total_ot;
					$jobcard_ll_ref = $data[0]->ll_ref;
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
					
					$stdDay=array("W","H");
					
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
					else
					{
						$totalOT=$jobcard_total_ot;
						$chkattnJobcardPolicy = AttendanceJobcardPolicy::where('is_user_max_ot_fixed',1)->count();
						if($chkattnJobcardPolicy!=0)
						{
							$attnJobcardPolicy = AttendanceJobcardPolicy::where('is_user_max_ot_fixed',1)->first();
							if (date('H:i:s',strtotime($totalOT)) > date('H:i:s',strtotime($attnJobcardPolicy->user_max_ot_hour))) {
								$totalOTs = $attnJobcardPolicy->user_max_ot_hour;
								//echo "Admin - ".$totalOT."<br>";                                   
								//echo "User - ".$user_new_ot_time."<br>";                                   
						   
								if ($attnJobcardPolicy->is_user_ot_adjust_with_outtime == 1) {
									$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetween($totalOTs, $totalOT);
									//echo $left_time_to_deduct_from_out_time;
									//exit();
									$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($jobcard_out_time, $left_time_to_deduct_from_out_time);
									//echo $user_new_out_time; // this is need to be fixed.
									//exit();
									
									$user_new_ot_time=$attnJobcardPolicy->user_max_ot_hour;
									$user_new_total_time=$this->CalCulateTtalInTimeBetween($jobcard_start_date.' '.$jobcard_in_time, $jobcard_end_date.' '.$jobcard_out_time);
								
								}
								else
								{
									$user_new_out_time=$jobcard_out_time;
									$user_new_ot_time=$totalOTs;
									//$user_new_total_time=$totalWorkingHour;
									$user_new_total_time=$this->CalCulateTtalInTimeBetween($jobcard_start_date.' '.$jobcard_in_time, $jobcard_end_date.' '.$jobcard_out_time);
								}
								$edit_jobcard_flag=1;
								/*$tab=AttendanceJobcard::where('start_date',$jobcard_start_date)->first();
								$tab->user_out_time=$user_new_out_time;
								$tab->user_total_time=$user_new_total_time;
								$tab->user_total_ot=$user_new_ot_time;
								$tab->save();*/
								
								
							}
							else
							{
								$user_new_out_time=$jobcard_out_time;
								$user_new_ot_time=$totalOT;
								//$user_new_total_time=$totalWorkingHour;
							}
						}
						else
						{
							$user_new_out_time=$jobcard_out_time;
							$user_new_ot_time=$totalOT;
							//$user_new_total_time=$totalWorkingHour;
						}
					}
					
					$jobcard_user_out_time = $user_new_out_time;
                    $jobcard_user_total_ot = $user_new_ot_time;
					
					
                } else {
                    $jobcard_id = 0;
                    $jobcard_emp_code = 0;
                    $jobcard_start_date = $ld;
                    $jobcard_end_date = $ld;
                    $jobcard_in_time = "00:00:00";
                    $jobcard_out_time = "00:00:00";
                    $jobcard_total_time = "00:00:00";
                    $jobcard_total_ot = "00:00:00";
					$jobcard_user_out_time = "00:00:00";
                    $jobcard_user_total_ot = "00:00:00";
					$jobcard_ll_ref='';
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
				
				if(empty($jobcard_user_out_time))
				{
					$jobcard_user_out_time='00:00:00';
				}
				
				if(empty($jobcard_user_total_ot))
				{
					$jobcard_user_total_ot='00:00:00';
				}
				$stdDay=array("H","W");
				if(in_array($cal_day_type,$stdDay) && $jobcard_day_status=='A')
				{
					$jobcard_day_status=$cal_day_type;
				}
				
				if($jobcard_day_status=="A")
				{
					if(!empty($jobcard_in_time))
					{
						$jobcard_day_status="P";
					}
					elseif(!empty($jobcard_out_time))
					{
						$jobcard_day_status="P";
					}				
				}
				
				if(in_array($jobcard_day_status,array("Late IN","Late OUT")))
				{
					$jobcard_day_status="P";
				}
				
				if(empty($edit_jobcard_flag))
				{
					$edit_jobcard_flag=0;
				}
				
				if($jobcard_in_time=='00:00:00' && $jobcard_out_time=='00:00:00' && in_array($jobcard_day_status,array("P","Late IN","Late OUT")))
				{
					$jobcard_day_status="A";
					$edit_jobcard_flag=1;
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
                    'user_out_time' => $jobcard_user_out_time,
                    'user_total_ot' => $jobcard_user_total_ot,
                    'day_status' => $jobcard_day_status,
					'edit_jobcard_flag'=>$edit_jobcard_flag,
					'll_ref' => $jobcard_ll_ref
                );
				
				//echo $jobcard_day_status;
				//exit();
				
				array_push($admin_time_array_total,$jobcard_total_ot);
				array_push($user_time_array_total,$jobcard_user_total_ot);

            endforeach;
        }
		
		$admin_time_total_tray=$this->SumAllPDFTime($admin_time_array_total);
		$user_time_total_tray=$this->SumAllPDFTime($user_time_array_total);
	
	
		
        //return response()->json(array("data" => $json, "total" => count($json)));
		//$response_data=response()->json($json);
		$day_status=$this->dayStatus();
		return view('module.settings.jobcardBatchEditMode',['jobData'=>$json,
		'day_status'=>$day_status,
		'count'=>count($json),
		'emp_code'=>$emp_code,
		'start_date'=>$start_date,
		'end_date'=>$end_date,
		'admin_time_total_tray'=>$admin_time_total_tray,
		'user_time_total_tray'=>$user_time_total_tray]);
		
    }
	
	function SumAllPDFTime($a) 
	{
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
	
	public function reviewOT(Request $request)
	{

            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $emp_code = $request->emp_code;
            
            $in_time=$request->in_time;
            $out_time=$request->out_time;
            
			$jobcard_id = $request->id;
				
            if($in_time=='00:00:00' || $out_time=='00:00:00')
			{
				$total_ot='00:00:00';

			}
			
			
            
            $day_status = $request->day_status;
			if($day_status=="A")
			{
				$total_ot='00:00:00';
				$in_time='00:00:00';
				$out_time='00:00:00';
			}
            
			$data=array();
			
			if($in_time!='' && $out_time!='')
			{
				if($in_time!='00:00:00' && $out_time!='00:00:00')
				{
					$data=$this->ReviewWeekendOTWithGeneralShift($start_date,$end_date,$in_time,$out_time,$jobcard_id,$day_status,$emp_code);
				}
			}
			
		return response()->json($data);
			
	}
	
	
	public function saveBatch(Request $request)
	{
		//print_r($request->id);
		
		$chkBatch=count($request->id);
		if($chkBatch==0)
		{
			return redirect()->action('JobcardBatchEditController@index')->with('error', 'Please Genarate Jobcard First.');
		}
		else
		{
			$success_edit=0;
			$lastIndex=$chkBatch-1;
			$loop_start_date = $request->start_date[0];
			$loop_end_date = $request->jobcard_end_date[$lastIndex];
			$loop_emp_code = $request->emp_code[0];
			
			foreach($request->id as $key=>$jbID):
				$edit_flag=$request->edit_flag[$key];
				if($edit_flag==1)
				{			
			
					
					$emp_code=$request->emp_code[$key];
					
					$sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();

					if (isset($sqlEmp)) {
						$company_id = $sqlEmp->company_id;
					} else {
						$alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
						$company_id = $alt_company_id;
					}
					
					$start_date=$request->start_date[$key];
					$end_date=$request->jobcard_end_date[$key];
					$day_status=$request->day_status[$key];
					//admin data
					$admin_in_time=$request->in_time[$key];
					$admin_out_time=$request->out_time[$key];
					$admin_total_time=$this->CalCulateTtalInTimeBetween($start_date.' '.$admin_in_time,$end_date.' '.$admin_out_time);
					$admin_total_ot=$request->admin_ot[$key];
					
					//user data
					$user_out_time=$request->user_out_time[$key];
					$user_total_time=$this->CalCulateTtalInTimeBetween($start_date.' '.$admin_in_time,$end_date.' '.$user_out_time);
					$user_total_ot=$request->user_total_ot[$key];
					
					$user_end_date=$request->user_end_date[$key];
					
					//echo $emp_code.'-'.$start_date.'-'.$admin_in_time.'-'.$end_date.'-'.$admin_out_time.'-'.$admin_total_time.'-'.$admin_total_ot.'-'.$user_out_time.'-'.$user_total_time.'-'.$user_total_ot.'-'.$user_total_ot.'-'.$day_status."<br>";
					
					//echo $user_out_time;
					//exit();
					
					$chkjobCard=AttendanceJobcard::where('emp_code',$emp_code)
												 ->where('start_date',$start_date)
												 ->orderBy('id','DESC')
												 ->count();
												 
												 
					$logged_emp_code = app('App\Http\Controllers\MenuPageController')->loggedUser('emp_code');							 
					if($chkjobCard==0)
					{
						if($day_status=='LL')
						{
							$ll_ref=$request->ll_ref[$key];
							if(!empty($ll_ref))
							{
								$tab=new AttendanceJobcard;
								$tab->emp_code=$emp_code;
								$tab->start_date=$start_date;
								$tab->end_date=$end_date;
								$tab->company_id=$company_id;
								
								$tab->admin_in_time=$admin_in_time;
								$tab->admin_out_time=$admin_out_time;
								$tab->admin_total_time=$admin_total_time;
								$tab->admin_total_ot=$admin_total_ot;
								$tab->admin_day_status=$day_status;
								
								$tab->user_end_date=$user_end_date;
								
								$tab->user_in_time=$admin_in_time;
								$tab->user_out_time=$user_out_time;
								$tab->user_total_time=$user_total_time;
								$tab->user_total_ot=$user_total_ot;
								$tab->user_day_status=$day_status;
								
								$tab->audit_in_time=$admin_in_time;
								$tab->audit_out_time=$user_out_time;
								$tab->audit_total_time=$user_total_time;
								$tab->audit_total_ot=$user_total_ot;
								$tab->audit_day_status=$day_status;
								$tab->ll_ref=$ll_ref;
								
								$tab->edit_flag='1';
								$tab->edited_emp_code=$logged_emp_code;
								
								
								$tab->save();
							}
						}
						else
						{
							$tab=new AttendanceJobcard;
							$tab->emp_code=$emp_code;
							$tab->start_date=$start_date;
							$tab->end_date=$end_date;
							$tab->company_id=$company_id;
							
							$tab->admin_in_time=$admin_in_time;
							$tab->admin_out_time=$admin_out_time;
							$tab->admin_total_time=$admin_total_time;
							$tab->admin_total_ot=$admin_total_ot;
							$tab->admin_day_status=$day_status;
							
							$tab->user_end_date=$user_end_date;
							
							$tab->user_in_time=$admin_in_time;
							$tab->user_out_time=$user_out_time;
							$tab->user_total_time=$user_total_time;
							$tab->user_total_ot=$user_total_ot;
							$tab->user_day_status=$day_status;
							
							$tab->audit_in_time=$admin_in_time;
							$tab->audit_out_time=$user_out_time;
							$tab->audit_total_time=$user_total_time;
							$tab->audit_total_ot=$user_total_ot;
							$tab->audit_day_status=$day_status;
							$tab->ll_ref='0000-00-00';
							
							$tab->edit_flag='1';
							$tab->edited_emp_code=$logged_emp_code;
							
							$tab->save();
						}
						
					}
					else
					{
						
						$SqlFixjobCardID=AttendanceJobcard::where('emp_code',$emp_code)
														->where('start_date',$start_date)
														->orderBy('id','DESC')
														->first();
						$FixjobCardID=$SqlFixjobCardID->id;	

						$clearDumpData=AttendanceJobcard::where('emp_code',$emp_code)
														->where('start_date',$start_date)
														->WhereNotIN('id',[$FixjobCardID])
														->delete();
						
						
						if($day_status=='LL')
						{
							$ll_ref=$request->ll_ref[$key];
							if(!empty($ll_ref))
							{
								$tab=AttendanceJobcard::where('emp_code',$emp_code)->where('start_date',$start_date)->first();
								$tab->emp_code=$emp_code;
								$tab->start_date=$start_date;
								$tab->end_date=$end_date;
								$tab->company_id=$company_id;
								
								$tab->admin_in_time=$admin_in_time;
								$tab->admin_out_time=$admin_out_time;
								$tab->admin_total_time=$admin_total_time;
								$tab->admin_total_ot=$admin_total_ot;
								$tab->admin_day_status=$day_status;
								
								
								$tab->user_end_date=$user_end_date;
								$tab->user_in_time=$admin_in_time;
								$tab->user_out_time=$user_out_time;
								$tab->user_total_time=$user_total_time;
								$tab->user_total_ot=$user_total_ot;
								$tab->user_day_status=$day_status;
								
								$tab->audit_in_time=$admin_in_time;
								$tab->audit_out_time=$user_out_time;
								$tab->audit_total_time=$user_total_time;
								$tab->audit_total_ot=$user_total_ot;
								$tab->audit_day_status=$day_status;
								$tab->ll_ref=$ll_ref;
								
								$tab->edit_flag='1';
								$tab->edited_emp_code=$logged_emp_code;
							
								
								$tab->save();
							}
							
						}
						else
						{
						
							$tab=AttendanceJobcard::where('emp_code',$emp_code)->where('start_date',$start_date)->first();
							$tab->emp_code=$emp_code;
							$tab->start_date=$start_date;
							$tab->end_date=$end_date;
							$tab->company_id=$company_id;
							
							$tab->admin_in_time=$admin_in_time;
							$tab->admin_out_time=$admin_out_time;
							$tab->admin_total_time=$admin_total_time;
							$tab->admin_total_ot=$admin_total_ot;
							$tab->admin_day_status=$day_status;
							

							$tab->user_end_date=$user_end_date;
							$tab->user_in_time=$admin_in_time;
							$tab->user_out_time=$user_out_time;
							$tab->user_total_time=$user_total_time;
							$tab->user_total_ot=$user_total_ot;
							$tab->user_day_status=$day_status;
							
							$tab->audit_in_time=$admin_in_time;
							$tab->audit_out_time=$user_out_time;
							$tab->audit_total_time=$user_total_time;
							$tab->audit_total_ot=$user_total_ot;
							$tab->audit_day_status=$day_status;
							$tab->ll_ref='0000-00-00';
							
							$tab->edit_flag='1';
							$tab->edited_emp_code=$logged_emp_code;
							
							$tab->save();
						}
					}
					$success_edit++;
				}
			endforeach;
			
			//echo $tab->admin_out_time;
			//exit();
			
			if($success_edit==0)
			{
				return redirect(url('Jobcard/EditMode/'.$loop_emp_code.'/'.$loop_start_date.'/'.$loop_end_date.'/error'));
			}
			else
			{
				return redirect(url('Jobcard/EditMode/'.$loop_emp_code.'/'.$loop_start_date.'/'.$loop_end_date.'/success'));
			}
			
			
		}
	}
	
	public function updateMissingPunch(Request $request) {

        foreach($request->models as $key => $value) {
			$emp_code=$value['emp_code'];
			$in_time=$this->getTime($value['admin_in_time'],true);
			$out_time=$this->getTime($value['admin_out_time'],true);
			$start_date=date('Y-m-d',strtotime($value['start_date']));
			$end_date=date('Y-m-d',strtotime($value['end_date']));
			$jobcard_id=$value['id'];
			$day_status=$value['admin_day_status'];
			$data=array();
			
			
			
			if(!empty($in_time) && !empty($out_time) && !empty($start_date) && !empty($end_date))
			{
				if($in_time!=$this->getTime('00:00:00'))
				{
					if($out_time!=$this->getTime('00:00:00'))
					{
						if($start_date!='0000-00-00')
						{
							if($end_date!='0000-00-00')
							{
								$data=$this->ReviewWeekendOTWithGeneralShift($start_date,$end_date,$in_time,$out_time,$jobcard_id,$day_status,$emp_code);
							}
						}
					}
				}
			}
			
			if(count($data)!=0)
			{
				//echo $data['user_new_ot_time'];
				
				$tab = AttendanceJobcard::find($value['id']);
				$tab->end_date =$value['end_date'];
				$tab->admin_in_time = $this->getTime($value['admin_in_time'],true);
				$tab->admin_out_time = $this->getTime($value['admin_out_time'],true);
				$tab->admin_day_status =$value['admin_day_status'];
				$tab->admin_total_ot= $data['admin_ot'];
				
				$tab->user_in_time = $this->getTime($value['admin_in_time'],true);
				$tab->user_out_time = $data['user_new_out_time'];;
				$tab->user_day_status = $value['admin_day_status'];
				$tab->user_total_ot= $data['admin_ot'];
				
				if(!empty($data['user_end_date']))
				{
					if($data['user_end_date']!='0000-00-00')
					{
						$tab->user_end_date= $data['user_end_date'];
						
					}
				}
				
				$tab->save();
				
				return 1;
			}
			else
			{
				return 3;
			}
			
            
        }
    }
	
	public function dayStatus() {
        $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        $dayStatus = '';
        /*if (!empty($alt_company_id)) {
            $leave = DB::select("SELECT leave_short_code as day_short_code FROM `leave_policies` WHERE company_id=$alt_company_id");
            $day_short_code = DB::select("SELECT day_short_code FROM `day_types` WHERE company_id=$alt_company_id");
            $dayStatus = array_merge($day_short_code, $leave);
        } else {*/
            //$leave = DB::select("SELECT leave_short_code as day_short_code FROM `leave_policies`");
            $day_short_code = DB::table("day_types")->select('day_short_code')->groupby('day_short_code')->get();
            $dayStatus =$day_short_code;
        //}
        //return response()->json($dayStatus);
		
		return $dayStatus;
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
            if ($empLeaveBalance->remaining_days < 1) {
                return 2;
            } else {
                $newAvailDays = $empLeaveBalance->availed_days + 1;
                $newLeaveRemain = $empLeaveBalance->remaining_days - 1;
                if ($newLeaveRemain > 0) {

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
            
			
			
			
            
            //echo $total_ot;
			//exit();


            //echo $value['in_time'];
           



//echo "<pre>";
//print_r($value);
//exit();
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
                } else {
                    $this->EmptyNInsertNewLeave($jobcard_id, $day_status, $start_date, $emp_code, $ExDay->company_id, $in_time, $out_time, $total_time, $total_ot);
                }
            }


        endforeach;
        return 1;
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
                    ->leftJoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
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
				if(isset($sql))
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
					return "A";
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
				
				if(empty($jobcard_day_status_get))
				{
					$jobcard_day_status_get = 'A';
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
		if(!empty($out_time))
		{
			$Admin_dteEnd = new \DateTime($out_time);
		}
		else
		{
			$Admin_dteEnd =$out_time;
		}
        
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
	
	private function MakeTimeDifferenceShiftUnusualEntryOrOut($auto_start_date, $shift_start_time, $auto_end_date, $shift_end_time) {
        $stdtime=date('Y-m-d H:i:s',strtotime(date('Y-m-d',strtotime($auto_start_date)).' '.date('H:i:s',strtotime($shift_start_time))));
        $eddtime=date('Y-m-d H:i:s',strtotime(date('Y-m-d',strtotime($auto_end_date)).' '.date('H:i:s',strtotime($shift_end_time))));

		//echo $stdtime.'#'.$eddtime;
		//exit();
		
		$start_date=strtotime($stdtime);
		$enddate_date=strtotime($eddtime);
		
		$leftTime=$enddate_date-$start_date;
		$createDate=date('H:i:s',$leftTime);
		
		$chkShiftHour='09:00:00';
		
		$Admin_dteStart = new \DateTime($stdtime);
        $Admin_dteEnd = new \DateTime($eddtime);
        $Admin_dteDiff = $Admin_dteStart->diff($Admin_dteEnd);
        $Admin_Total_WTime = $Admin_dteDiff->format("%d");

		$total_day=$Admin_Total_WTime;
		$newhisreturn=$createDate;
		if($total_day>0)
		{
				if($createDate>$chkShiftHour)
				{
					$auto_start_date=date('Y-m-d H:i:s',strtotime($auto_start_date));
					$objAdminTime = Carbon::parse($auto_start_date);
					$objAdminTime->toDateTimeString();
					$objAdminTime->addHours(23);
					$objAdminTime->addMinutes(59);
					$objAdminTime->addSeconds(59);
					$newhisreturn = $objAdminTime->format('H:i:s');
				}
				else
				{
					$makeHourFromRawLog=date('H',strtotime($createDate));
					$makeHourFromRawLogMIn=intval(date('i',strtotime($createDate)));
					$makeHourFromRawLogSec=intval(date('s',strtotime($createDate)));
					$totalHourlog=intval($makeHourFromRawLog)+24;
					$leftaftershiftHour=$totalHourlog-9;
					
					$auto_start_date=date('Y-m-d H:i:s',strtotime($auto_start_date));
					$objAdminTime = Carbon::parse($auto_start_date);
					$objAdminTime->toDateTimeString();
					$objAdminTime->addHours($leftaftershiftHour);
					$objAdminTime->addMinutes($makeHourFromRawLogMIn);
					$objAdminTime->addSeconds($makeHourFromRawLogSec);
					$newhisreturn = $objAdminTime->format('H:i:s');
					
				}
				
			
			
			//$newhisreturn ='23:00:00';		
			//echo $newhisreturn;
			//exit();
		}

        return array('time'=>$newhisreturn,'day'=>$total_day);
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
				if (date('H:i:s',strtotime($totalOT)) > date('H:i:s',strtotime($attnJobcardPolicy->user_max_ot_hour))) {
					$totalOTs = $attnJobcardPolicy->user_max_ot_hour;
					//echo "Admin - ".$totalOT."<br>";                                   
					//echo "User - ".$user_new_ot_time."<br>";                                   
               
					if ($attnJobcardPolicy->is_user_ot_adjust_with_outtime == 1) {
						$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetween($totalOTs, $totalOT);
						//echo $left_time_to_deduct_from_out_time;
						//exit();
						$user_new_out_time=$this->CalCulateTtalInTimeBetween($out_time, $left_time_to_deduct_from_out_time);
						//echo $user_new_out_time; // this is need to be fixed.
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
	
	

	private function ReviewWeekendOTWithGeneralShift($start_date='0000-00-00',
	$end_date='0000-00-00',
	$in_time='00:00:00',
	$out_time='00:00:00',
	$jobcard_id = 0, 
	$jobcard_day_status = "A", 
	$emp_code = '0') {
		
	$user_new_end_date='';	
		
	$sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->first();

	if (isset($sqlEmp)) {
		$company_id = $sqlEmp->company_id;
	} else {
		$alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
		$company_id = $alt_company_id;
	}	
	$jobcard_out_time=$out_time;	
	/* CHecking Stafgrade & OT CHeck Start */
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
	/* CHecking Stafgrade & OT CHeck End */
        if (($jobcard_day_status == "W" || $jobcard_day_status == "H") && !empty($jobcard_out_time)) {
			
            if ($jobcard_out_time != '00:00:00') {

				

                $chkWHP = WeekendOTPolicy::count();
                if ($chkWHP == 0) {

                    $admin_totalWorkingHour = $this->CalCulateTtalInTimeBetween($start_date . ' ' . $in_time, $end_date . ' ' . $out_time);
					$admin_totalOT = $admin_totalWorkingHour;
					
                } else {



                    $sqlWHP = WeekendOTPolicy::first();
                    if ($sqlWHP->is_ot_count_as_total_working_hour == 1) {
						
						$admin_totalWorkingHour = $this->CalCulateTtalInTimeBetween($start_date . ' ' . $in_time, $end_date . ' ' . $out_time);
						$admin_totalOT=$admin_totalWorkingHour;

                    } elseif ($sqlWHP->is_ot_will_start_after_fix_hour == 1) {



                        if (!empty($sqlWHP->hour_after)) {
							
                            $admin_totalWorkingHour = $this->CalCulateTtalInTimeBetween($start_date . ' ' . $in_time, $end_date . ' ' . $out_time);
							
							if($admin_totalWorkingHour=='00:00:00' || empty($admin_totalWorkingHour))
							{
								$admin_totalOT = '00:00:00';
								
							}
							else
							{
								$admin_totalOT = $this->CalCulateTtalInTimeBetween($sqlWHP->hour_after, $admin_totalWorkingHour);
							}							
							
							
							//echo $formatedTime;
							
                        } else {
                            $admin_totalWorkingHour = $this->CalCulateTtalInTimeBetween($start_date . ' ' . $in_time, $end_date . ' ' . $out_time);
							$admin_totalOT=$admin_totalWorkingHour;
							
                        }
                    }
                }
				
				if($admin_totalWorkingHour=='00:00:00' || empty($admin_totalWorkingHour))
				{
					$admin_totalOT = '00:00:00';
					
				}	
				
				//$admin_totalWorkingHour
				//$admin_totalOT;
				$admin_out_time=$out_time;
				
				
				
				
            }
			else
			{
				$admin_totalWorkingHour='00:00:00';
				$admin_totalOT='00:00:00';
				$admin_out_time=$out_time;				
			}
			
			//$user_new_out_time;
			//$user_new_ot_time
			//$user_new_total_time;
			
			//echo $admin_totalWorkingHour;
			//exit();
			//echo $admin_totalOT;
			//exit();
			//$admin_out_time
			
			$user_new_out_time=$out_time;
			$user_new_ot_time=$admin_totalOT;
			$user_new_total_time=$admin_totalWorkingHour;
			
			$chkattnJobcardPolicy = WeekendOTPolicy::where('is_standard_max_ot_hour',1)->count();
			if($chkattnJobcardPolicy!=0)
			{
				
				
				$attnJobcardPolicy = WeekendOTPolicy::where('is_standard_max_ot_hour',1)->first();
				if ($this->FormatHHMM($admin_totalOT) > $this->FormatHHMM($attnJobcardPolicy->standard_max_ot_hour)) {
					$totalOTs = $attnJobcardPolicy->standard_max_ot_hour;
					
					$countAttenDancePolicyUser=AttendanceJobcardPolicy::where('is_user_ot_adjust_with_outtime',1)->count();
					if($countAttenDancePolicyUser==0)
					{
						$user_new_out_time=$out_time;
						$user_new_ot_time=$totalOTs;
						$user_new_total_time=$admin_totalWorkingHour;
					}
					else
					{
						$AttenDancePolicyUser=AttendanceJobcardPolicy::where('is_user_ot_adjust_with_outtime',1)->first();
						if ($AttenDancePolicyUser->is_user_ot_adjust_with_outtime == 1) {
							$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($admin_totalOT, $totalOTs);
							//echo $left_time_to_deduct_from_out_time;
							//exit();
							$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($out_time, $left_time_to_deduct_from_out_time);
							//echo $user_new_out_time;
							//exit();
							
							$user_new_ot_time=$attnJobcardPolicy->standard_max_ot_hour;
							$user_new_total_time=$this->CalCulateTtalInTimeBetween($start_date.' '.$in_time, $end_date.' '.$out_time);
							
						}
						else
						{
							$user_new_out_time=$out_time;
							$user_new_ot_time=$totalOTs;
							$user_new_total_time=$admin_totalWorkingHour;
						}
						
					}
					
					
                }
				else
				{
					$user_new_out_time=$out_time;
					$user_new_ot_time=$admin_totalOT;
					$user_new_total_time=$admin_totalWorkingHour;
				}
				
				//echo "ddd".$user_new_ot_time;
				//exit();
				
			}
			else
			{
				$user_new_out_time=$out_time;
				$user_new_ot_time=$admin_totalOT;
				$user_new_total_time=$admin_totalWorkingHour;
			}	
			
			
			
			
        }
		else
		{
			$log_date=$start_date;
			
			$chkshift_info = AssignEmployeeToShift::where('emp_code', $emp_code)
							->where('start_date','<=',$start_date)
							->where('end_date','>=',$start_date)
							->count();
			
			
							
			if($chkshift_info==0)
			{
				if($log_date==$end_date)
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
			
			}	
			
			if($chkshift_info==0 && ($log_date!=$end_date))
			{
				$totalShiftHour='09:00:00';
				$totalWorkingHour=$this->CalCulateTtalInTimeBetween($start_date.' '.$in_time, $end_date.' '.$out_time);
				if($totalShiftHour>$totalWorkingHour)
				{
					$totalOT="00:00:00";
				}
				else
				{
					$totalOT=$this->CalCulateTtalInTimeBetween($totalShiftHour, $totalWorkingHour);
				}
			}	
			else
			{
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
					if($totalShiftHour>$totalWorkingHour)
					{
						$totalOT="00:00:00";
					}
					else
					{
						$totalOT=$this->CalCulateTtalInTimeBetween($totalShiftHour, $totalWorkingHour);
					}
				}
				else
				{
					if($start_date==$end_date)
					{
						$totalShiftHour=$this->CalCulateTtalInTimeBetween(date('Y-m-d').' '.$shift_data->shift_start_time, date('Y-m-d').' '.$shift_data->shift_end_time);
						$totalWorkingHour=$this->CalCulateTtalInTimeBetween($start_date.' '.$in_time, $end_date.' '.$out_time);
						if($totalShiftHour>$totalWorkingHour)
						{
							$totalOT="00:00:00";
						}
						else
						{
							$totalOT=$this->CalCulateTtalInTimeBetween($totalShiftHour, $totalWorkingHour);
						}
					}
					else
					{
						$totalShiftHour='09:00:00';
						$totalarraydaytime=$this->MakeTimeDifferenceShiftUnusualEntryOrOut($start_date,$in_time, $end_date,$out_time);
						if($totalarraydaytime['day']>0)
						{
							$totalWorkingHour=$totalarraydaytime['time'];
							$totalOT=$totalarraydaytime['time'];
						}
						else
						{
							$totalWorkingHour=$totalarraydaytime['time'];
							if($totalShiftHour>$totalWorkingHour)
							{
								$totalOT="00:00:00";
							}
							else
							{
								$totalOT=$this->CalCulateTtalInTimeBetween($totalShiftHour, $totalWorkingHour);
							}
						}
						
						
						
					}
					
				}
			}
			
			
			
			
			
			//echo $totalOT;
			//exit();
			
			$admin_totalWorkingHour=$totalWorkingHour;
			$admin_totalOT=$totalOT;
			$admin_out_time=$out_time;
			
			
			
			$user_new_out_time=$out_time;
			$user_new_ot_time=$totalOT;
			$user_new_total_time=$totalWorkingHour;
			
			$user_new_end_date='';
			
			$chkattnJobcardPolicy = AttendanceJobcardPolicy::where('is_user_max_ot_fixed',1)->count();
			if($chkattnJobcardPolicy!=0)
			{
				$attnJobcardPolicy = AttendanceJobcardPolicy::where('is_user_max_ot_fixed',1)->first();
				if (date('H:i:s',strtotime($totalOT)) > date('H:i:s',strtotime($attnJobcardPolicy->user_max_ot_hour))) {
					$totalOTs = $attnJobcardPolicy->user_max_ot_hour;
					//echo "Admin - ".$totalOT."<br>";                                   
					//echo "User - ".$user_new_ot_time."<br>";                                   
               
					if ($attnJobcardPolicy->is_user_ot_adjust_with_outtime == 1) {
						$left_time_to_deduct_from_out_time = $this->CalCulateTtalInTimeBetweenRaw($totalOTs, $totalOT);
						//echo $left_time_to_deduct_from_out_time;
						//exit();
						$user_new_out_time=$this->CalCulateTtalInTimeBetweenRaw($end_date.' '.$out_time, $left_time_to_deduct_from_out_time);
						//echo $user_new_out_time;
						//exit();
						
						$user_new_end_date_time=$this->CarbonMakeOutTime($end_date.' '.$out_time,$left_time_to_deduct_from_out_time);
						$user_for_new_out_time=$user_new_end_date_time[1];
						
						
						if($user_new_out_time!=$user_for_new_out_time)
						{
							$user_new_out_time=$user_new_end_date_time[1];
							$user_new_end_date=$user_new_end_date_time[0];
						}
						
						/*$chkDay=Calendar::where('calendars.date',$start_date)->leftJoin('day_types','calendars.day_type_id','=','day_types.id')->whereIN('day_types.day_short_code',['W','H'])->count();
						if($chkDay==0)
						{
							
						}
						else
						{
							
							
						}*/
						
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
			
		
			
			//$JobcardTab->admin_total_ot;
			
			//exit();
			
		}
		
		
		//$admin_totalWorkingHour=$totalWorkingHour;
		//$admin_totalOT=$totalOT;
		//$admin_out_time=$out_time;
		
		
		
		//$user_new_out_time=$out_time;
		//$user_new_ot_time=$totalOT;
		//$user_new_total_time=$totalWorkingHour;
		
		if(empty($admin_totalOT))
		{
			$admin_totalOT='00:00:00';
		}
		
		if(empty($user_new_out_time))
		{
			$user_new_out_time='00:00:00';
		}
		
		if(empty($user_new_ot_time))
		{
			$user_new_ot_time='00:00:00';
		}
		
		if($isOTElg==1)
		{
			$data=['admin_ot'=>$admin_totalOT,'user_new_out_time'=>$user_new_out_time,'user_new_ot_time'=>$user_new_ot_time,'user_end_date'=>$user_new_end_date];
		}
		else
		{
			$data=['admin_ot'=>'00:00:00','user_new_out_time'=>$out_time,'user_new_ot_time'=>'00:00:00','user_end_date'=>'00:00:00'];
		}
		
		return $data;

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
			if($max_OT<$totalOT)
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
				$totalWeekendStandardOT=$this->AddNewTimeFromArrayCarbon('+',$totalOTs,$hour_after_param);
				
				$totalWeekendStandardOT_param=explode(":",$totalWeekendStandardOT);
				$LeftTimeToDeductFromOutTime=$this->AddNewTimeFromArrayCarbon('-',$AdminOTTime,$totalWeekendStandardOT_param); //1:30
				
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
	
	

}
