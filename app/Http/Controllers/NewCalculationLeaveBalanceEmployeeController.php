<?php

namespace App\Http\Controllers;

use App\LeaveAssignedYearlyData;
use App\LeaveApplicationMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Company;
use App\LeavePolicy;
use App\Employeeinfo;
use App\Year;
USE App\Calendar;
use App\EmployeeCompany;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\AttendanceJobcard;
use Carbon\Carbon;

class NewCalculationLeaveBalanceEmployeeController extends Controller {
	
	
	public function CountDayNumber($start_date='0000-00-00',$end_date='0000-00-00')
	{
		 $day=(strtotime($start_date) - strtotime($end_date)) / (60 * 60 * 24);
		if(substr($day,0,1)=='-')
		{
			$day=substr($day,1,30);
		}
		
		return $day;
	}
	
	public function CountYearNumber($start_date='0000-00-00',$end_date='0000-00-00')
	{
		 $day=((strtotime($start_date) - strtotime($end_date)) / (60 * 60 * 24))/365;
		if(substr($day,0,1)=='-')
		{
			$day=substr($day,1,30);
		}
		
		$day=floor($day);
		return $day;
	}
	
	public function checkNpullLeaveBalanceForUser($emp_code=0)
	{
		if(!empty($emp_code))
		{			
	
			$chkThisYearLeave=LeaveAssignedYearlyData::where('emp_code',$emp_code)->where('year',date('Y'))->count();
			if($chkThisYearLeave==0)
			{
				return $this->AssignNewYearLeaveBalance($emp_code);
			}
			else
			{
				$plugin=0;
				$LeavePolicy=LeavePolicy::all();
				foreach($LeavePolicy as $lp):
					$chkleaveAssignYearData=LeaveAssignedYearlyData::where('emp_code',$emp_code)
																->where('year',date('Y'))
																->where('leave_policy_id',$lp->id)
																->count();
					if($chkleaveAssignYearData==0)
					{
						$plugin+=1;
					}
				endforeach;
				
				if($plugin!=0)
				{
					LeaveAssignedYearlyData::where('emp_code',$emp_code)->where('year',date('Y'))->delete();
					return $this->AssignNewYearLeaveBalance($emp_code);
				}
				else
				{
					return 0;
				}
				
				
			}
		}
		else
		{
			return 0;
		}
	}
	
	private function AssignNewYearLeaveBalance($emp_code=0)
	{
		$chkLeavePolicy=LeavePolicy::count();
		if($chkLeavePolicy==0)
		{
			return 0;
		}
		else
		{
			
			$empInfo=DB::table('employee_infos')
			->leftJoin('genders','employee_infos.gender','=','genders.id')
			->select('employee_infos.*',DB::Raw('genders.name as gender'))
			->where('employee_infos.emp_code',$emp_code)
			->first();
			
			if(isset($empInfo))
			{
			
				if(isset($empInfo->join_date))
				{
					$joining=$empInfo->join_date;
				}
				else
				{
					$joining='0000-00-00';
				}
				$company_id=$empInfo->company_id;
				$gender=$empInfo->gender;
				if(empty($gender))
				{
					$gender='Male';
				}
				
				$joinYear=date('Y',strtotime($joining));
				$joinMonth=date('m',strtotime($joining));
				$joinDay=date('d',strtotime($joining));
				
				$weekAndHoliday=Calendar::join('day_types','calendars.day_type_id','=','day_types.id')
								->where('calendars.year',date('Y'))
								->where('calendars.company_id',$company_id)
								->whereIN('day_types.day_short_code',['W','H'])
								->count();
				
				$tourDay=Calendar::join('day_types','calendars.day_type_id','=','day_types.id')
								->where('calendars.year',date('Y'))
								->where('calendars.company_id',$company_id)
								->whereNotIN('day_types.day_short_code',['W','H'])
								->count();
								
				$yearStartDate=date('Y-m-d',strtotime(date('Y').'-01-01'));	
				$yearEndDate=date('Y-m-d',strtotime(date('Y').'-12-31'));				
				
				$numServiceYear=$this->CountYearNumber($joining,date('Y-m-d'));
				$dayGoneService=$this->CountDayNumber($joining,date('Y').'-12-31');
				$LeavePolicy=LeavePolicy::all();
				foreach($LeavePolicy as $lp):
					
					if($joinYear==date('Y'))
					{
						
						
						if($this->GenSLCLLOPAL($lp->leave_short_code)==1)
						{
							if($lp->leave_short_code=='AL')
							{
								
								if($this->CountDayNumber($joining,date('Y-m-d'))>365)
								{
									$leave_new_total_days=$lp->total_days;
								}
								else
								{
									$leavePolicyTotal=$lp->total_days;
									$leftDayDivision=$leavePolicyTotal/365;
									$availableDayDecimal=($leftDayDivision*$dayGoneService);
									$leave_new_total_days=round($availableDayDecimal);
									//echo $dayGoneService;
									//exit();
									//echo $leave_new_total_days;
									//exit();
								}
								

								
							}
							else
							{
								$leavePolicyTotal=$lp->total_days;
								$leftDayDivision=$leavePolicyTotal/365;
								$availableDayDecimal=$leftDayDivision*$dayGoneService;
								$leave_new_total_days=round($availableDayDecimal);
							}
							
							
						}
						elseif($lp->leave_short_code=='LL') 
						{
							$leave_new_total_days=$weekAndHoliday;
						}
						elseif($lp->leave_short_code=='T') 
						{
							$leave_new_total_days=$tourDay;
						}
						elseif($lp->leave_short_code=='ML' && $gender=='Female')
						{
							$leave_new_total_days=$lp->total_days;
						}
						elseif($lp->leave_short_code=='PL' && $gender=='Male')
						{
							$leave_new_total_days=$lp->total_days;
						}
						else
						{
							$leave_new_total_days=0;
						}
						
					}
					else
					{
						if($this->GenSLCLLOPAL($lp->leave_short_code)==1)
						{
							if($lp->leave_short_code=='AL')
							{
								if($this->CountDayNumber($joining,date('Y-m-d'))>365)
								{
									$leave_new_total_days=$lp->total_days;
								}
								else
								{
									$leave_new_total_days=0;
								}
							}
							else
							{
								$leavePolicyTotal=$lp->total_days;
								$leave_new_total_days=$leavePolicyTotal;
							}
						}
						elseif($lp->leave_short_code=='LL') 
						{
							$leave_new_total_days=$weekAndHoliday;
						}
						elseif($lp->leave_short_code=='T') 
						{
							$leave_new_total_days=$tourDay;
						}
						elseif($lp->leave_short_code=='ML' && $gender=='Female')
						{
							$leave_new_total_days=$lp->total_days;
						}
						elseif($lp->leave_short_code=='PL' && $gender=='Male')
						{
							$leave_new_total_days=$lp->total_days;
						}
						else
						{
							$leave_new_total_days=0;
						}
					}
					
					$chkleaveAssignYearData=LeaveAssignedYearlyData::where('emp_code',$emp_code)
																	->where('year',date('Y'))
																	->where('leave_policy_id',$lp->id)
																	->count();
																	
					$LeaveAvailFromLeave=LeaveApplicationMaster::where('emp_code',$emp_code)
															->where('leave_policy_id',$lp->id)
															->where('leave_status','Approved')
															->where('start_date', '<=', $yearStartDate)
															->where('end_date', '>=', $yearEndDate)
															->sum('total_days_applied');
					
					
					/*$LeaveAvailFromJobcard=AttendanceJobcard::where('emp_code',$emp_code)
															->where('admin_day_status',$lp->leave_short_code)
															->whereBetween('start_date',[$yearStartDate,$yearEndDate])
															//->whereNotIN('start_date',DB::Raw())
															->count();	*/											
					$totalAvailed=$LeaveAvailFromLeave;			
					$newRemainLeave=$leave_new_total_days-$totalAvailed;					
					if($chkleaveAssignYearData==0)
					{
						$tab=new LeaveAssignedYearlyData;
						$tab->company_id=$company_id;
						$tab->emp_code=$emp_code;
						$tab->leave_policy_id=$lp->id;
						$tab->year=date('Y');
						$tab->total_days=$leave_new_total_days;
						$tab->availed_days=$totalAvailed;
						$tab->remaining_days=$newRemainLeave;
						$tab->carry_forward_balance=0;
						$tab->incash_balance=0;
						$tab->save();
						
					}
					else
					{
						
																
						//echo "ex".$LeaveAvailFromLeave;										
						//exit();										
						
						
						
						
						$tab=LeaveAssignedYearlyData::where('emp_code',$emp_code)
														->where('year',date('Y'))
														->where('leave_policy_id',$lp->id)
														->first();
														
													
														
						$tab->company_id=$company_id;
						$tab->emp_code=$emp_code;
						$tab->leave_policy_id=$lp->id;
						$tab->year=date('Y');
						$tab->total_days=$leave_new_total_days;
						$tab->availed_days=$totalAvailed;
						$tab->remaining_days=$newRemainLeave;
						$tab->carry_forward_balance=0;
						$tab->incash_balance=0;
						$tab->save();
					}
					
					//return 1;
					
					//$leave_new_total_days
				endforeach;
			}
			return 1;
		}
		
	}
	
	private function GenSLCLLOPAL($lp)
	{
		if(($lp=='SL') || ($lp=='CL') || ($lp=='LOP') || ($lp=='AL'))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    //For All Employees
    public function CalculateLeaveBalanceExisting() {
        //Calculation For Automatic leave Balance Add starts

        $today = Carbon::today()->format('Y-m-d');
        //current year
        $year = date('Y');

        $yearStart = (new \DateTime(date("Y") . "-01-01"))->format("Y-m-d");
        $yearEnd = (new \DateTime(date("Y") . "-12-31"))->format("Y-m-d");

//        echo $yearStart." : ".$yearEnd;
//        $emp_code = 'RPAC0537';
//        $company_id = '11';
        //Get All Employees From Database
        $sqlEmployees = DB::select("SELECT `emp_code`,`company_id` FROM `employee_infos`");

        foreach ($sqlEmployees as $emp) {
            $emp_code = $emp->emp_code;
            $company_id = $emp->company_id;

            //Get Data From Attendance Job Card/Manual Job Card Entries/Leave Application Master
            $sqlLeaveEntries = DB::select("SELECT
                                        alldata.emp_code,
                                        lps.id AS leave_policy_id,
                                        alldata.short_code,
                                        COUNT(alldata.short_code) as availed_days,
                                        
                                        (
                                            CASE
                                               WHEN alldata.short_code = 'AL' 
                                               THEN (SELECT
                                                layds.total_days
                                                FROM leave_assigned_yearly_datas AS layds
                                                LEFT JOIN leave_policies AS lpls ON lpls.id=layds.leave_policy_id
                                                WHERE layds.emp_code='" . $emp_code . "'
                                                AND layds.year='" . $year . "'
                                                AND lpls.leave_short_code='AL' OR lpls.leave_short_code='EL'
                                                GROUP BY layds.emp_code)
                                               ELSE lps.total_days
                                            END
                                         ) AS total_days
                                        
                                        FROM 
                                        (
                                            SELECT result.*
                                                FROM (
                                                 (SELECT emp_code,
                                                        start_date AS date,
                                                        admin_day_status AS short_code
                                                        FROM attendance_jobcards
                                                        WHERE emp_code='" . $emp_code . "'
                                                        AND admin_day_status!='P'
                                                        AND admin_day_status!='A'
                                                        AND admin_day_status!='W'
                                                        AND admin_day_status!='H'
                                                        AND admin_day_status!='LATE IN'
                                                        AND start_date BETWEEN '" . $yearStart . "' AND '" . $yearEnd . "')
                                                 UNION
                                                 (SELECT emp_code,
                                                        date,
                                                        day_type AS short_code
                                                        FROM manual_job_card_entries
                                                        WHERE emp_code='" . $emp_code . "'
                                                        AND day_type!='P'
                                                        AND day_type!='H'
                                                        AND day_type!='W'
                                                        AND day_type!='A'
                                                        AND day_type!='LATE IN'
                                                        AND date BETWEEN '" . $yearStart . "' AND '" . $yearEnd . "')
                                                 UNION
                                                 (SELECT lam.emp_code,
                                                        lad.date,
                                                        lp.leave_short_code AS short_code
                                                        FROM leave_application_masters AS lam
                                                        LEFT JOIN leave_application_details AS lad ON lad.master_id=lam.id
                                                        LEFT JOIN leave_policies AS lp ON lp.id=lam.leave_policy_id
                                                        WHERE lam.emp_code='" . $emp_code . "'
                                                        AND lam.leave_status='Approved'
                                                        AND lad.date BETWEEN '" . $yearStart . "' AND '" . $yearEnd . "'
                                                        GROUP BY lad.date)
                                                ) result
                                            GROUP BY result.date
                                            #ORDER BY result.date DESC
                                        ) AS alldata
                                        LEFT JOIN leave_policies AS lps ON lps.leave_short_code=alldata.short_code
                                        GROUP BY alldata.short_code");

            foreach ($sqlLeaveEntries as $value) {

                $remaining_days = ($value->total_days - 0) - ($value->availed_days - 0);
                //Check Leave Policy Balance Already Exists Or Not
                $ChkExistingLeave = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $value->leave_policy_id)
                        ->where('year', $year)
                        ->groupBy('leave_policy_id')
                        ->count();

                if (!empty($ChkExistingLeave) || $ChkExistingLeave != 0) {
                    //Get Existing Leave Balance IDs
                    $ChkExistingLeave = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $value->leave_policy_id)
                            ->where('year', $year)
                            ->groupBy('leave_policy_id')
                            ->first();

                    $elid = $ChkExistingLeave->id;
                    //Update Leave Balance
                    $tab = LeaveAssignedYearlyData::find($elid);
                    $tab->company_id = $company_id;
                    $tab->emp_code = $value->emp_code;
                    $tab->leave_policy_id = $value->leave_policy_id;
                    $tab->year = $year;
                    $tab->total_days = $value->total_days;
                    $tab->availed_days = $value->availed_days;
                    $tab->remaining_days = $remaining_days;
                    //$tab->save();
                    return 'Successfully Updated All Leave Balance Data';
                } else {
                    //Create/Add Leave Balance
                    $tab = new LeaveAssignedYearlyData;
                    $tab->company_id = $company_id;
                    $tab->emp_code = $value->emp_code;
                    $tab->leave_policy_id = $value->leave_policy_id;
                    $tab->year = $year;
                    $tab->total_days = $value->total_days;
                    $tab->availed_days = $value->availed_days;
                    $tab->remaining_days = $remaining_days;
                    //$tab->save();
                    return 'Successfully Added All Leave Balance Data';
                }
            }
        }//end sqlEmployees 
        //exit();
    }

    //For Single Employee With Employee Code
    public function CalculateLeaveBalanceExistingSingleEmployee(Request $request) {
        //Calculation For Automatic leave Balance Add starts
        $emp_code = $request->emp_code;
        $company_id = $request->company_id;
        
        $today = Carbon::today()->format('Y-m-d');
        //current year
        $year = date('Y');

        $yearStart = (new \DateTime(date("Y") . "-01-01"))->format("Y-m-d");
        $yearEnd = (new \DateTime(date("Y") . "-12-31"))->format("Y-m-d");

//        echo $yearStart." : ".$yearEnd;
//        $emp_code = 'RPAC0537';
//        $company_id = '11';

        //Get Data From Attendance Job Card/Manual Job Card Entries/Leave Application Master
        $sqlLeaveEntries = DB::select("SELECT
                                        alldata.emp_code,
                                        lps.id AS leave_policy_id,
                                        alldata.short_code,
                                        COUNT(alldata.short_code) as availed_days,
                                        
                                        (
                                            CASE
                                               WHEN alldata.short_code = 'AL' 
                                               THEN (SELECT
                                                layds.total_days
                                                FROM leave_assigned_yearly_datas AS layds
                                                LEFT JOIN leave_policies AS lpls ON lpls.id=layds.leave_policy_id
                                                WHERE layds.emp_code='" . $emp_code . "'
                                                AND layds.year='" . $year . "'
                                                AND lpls.leave_short_code='AL' OR lpls.leave_short_code='EL'
                                                GROUP BY layds.emp_code)
                                               ELSE lps.total_days
                                            END
                                         ) AS total_days
                                        
                                        FROM 
                                        (
                                            SELECT result.*
                                                FROM (
                                                 (SELECT emp_code,
                                                        start_date AS date,
                                                        admin_day_status AS short_code
                                                        FROM attendance_jobcards
                                                        WHERE emp_code='" . $emp_code . "'
                                                        AND admin_day_status!='P'
                                                        AND admin_day_status!='A'
                                                        AND admin_day_status!='W'
                                                        AND admin_day_status!='H'
                                                        AND admin_day_status!='LATE IN'
                                                        AND start_date BETWEEN '" . $yearStart . "' AND '" . $yearEnd . "')
                                                 UNION
                                                 (SELECT emp_code,
                                                        date,
                                                        day_type AS short_code
                                                        FROM manual_job_card_entries
                                                        WHERE emp_code='" . $emp_code . "'
                                                        AND day_type!='P'
                                                        AND day_type!='H'
                                                        AND day_type!='W'
                                                        AND day_type!='A'
                                                        AND day_type!='LATE IN'
                                                        AND date BETWEEN '" . $yearStart . "' AND '" . $yearEnd . "')
                                                 UNION
                                                 (SELECT lam.emp_code,
                                                        lad.date,
                                                        lp.leave_short_code AS short_code
                                                        FROM leave_application_masters AS lam
                                                        LEFT JOIN leave_application_details AS lad ON lad.master_id=lam.id
                                                        LEFT JOIN leave_policies AS lp ON lp.id=lam.leave_policy_id
                                                        WHERE lam.emp_code='" . $emp_code . "'
                                                        AND lam.leave_status='Approved'
                                                        AND lad.date BETWEEN '" . $yearStart . "' AND '" . $yearEnd . "'
                                                        GROUP BY lad.date)
                                                ) result
                                            GROUP BY result.date
                                            #ORDER BY result.date DESC
                                        ) AS alldata
                                        LEFT JOIN leave_policies AS lps ON lps.leave_short_code=alldata.short_code
                                        GROUP BY alldata.short_code");

        foreach ($sqlLeaveEntries as $value) {

            $remaining_days = ($value->total_days - 0) - ($value->availed_days - 0);
            //Check Leave Policy Balance Already Exists Or Not
            $ChkExistingLeave = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $value->leave_policy_id)
                    ->where('year', $year)
                    ->groupBy('leave_policy_id')
                    ->count();

            if (!empty($ChkExistingLeave) || $ChkExistingLeave != 0) {
                //Get Existing Leave Balance IDs
                $ChkExistingLeave = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('leave_policy_id', $value->leave_policy_id)
                        ->where('year', $year)
                        ->groupBy('leave_policy_id')
                        ->first();

                $elid = $ChkExistingLeave->id;
                //Update Leave Balance
                $tab = LeaveAssignedYearlyData::find($elid);
                $tab->company_id = $company_id;
                $tab->emp_code = $value->emp_code;
                $tab->leave_policy_id = $value->leave_policy_id;
                $tab->year = $year;
                $tab->total_days = $value->total_days;
                $tab->availed_days = $value->availed_days;
                $tab->remaining_days = $remaining_days;
                //$tab->save();
                //return 'Successfully Updated All Leave Balance Data';
//                echo $company_id." -".$value->emp_code." -".$value->leave_policy_id." -".$year." -".$value->total_days." -".$value->availed_days." -".$remaining_days.'<br/><hr/>';
            } else {
                //Create/Add Leave Balance
                $tab = new LeaveAssignedYearlyData;
                $tab->company_id = $company_id;
                $tab->emp_code = $value->emp_code;
                $tab->leave_policy_id = $value->leave_policy_id;
                $tab->year = $year;
                $tab->total_days = $value->total_days;
                $tab->availed_days = $value->availed_days;
                $tab->remaining_days = $remaining_days;
                //$tab->save();
//                return 'Successfully Added All Leave Balance Data';
//                echo $company_id." -".$value->emp_code." -".$value->leave_policy_id." -".$year." -".$value->total_days." -".$value->availed_days." -".$remaining_days.'<br/><hr/>';
            }
        }


        //exit();
    }
	
	

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

    //End

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveAssignedYearlyData $leaveAssignedYearlyData) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveAssignedYearlyData $leaveAssignedYearlyData, $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        //
    }

}
