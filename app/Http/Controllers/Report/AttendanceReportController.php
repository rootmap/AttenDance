<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\Calendar;
use Illuminate\Support\Facades\DB;

use APP\EmployeeInfo;
use App\AttendanceJobcard;
use App\ManualJobCardEntry;

use App\LeaveApplicationMaster;

use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class AttendanceReportController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexSummary() {
		
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
		// echo $logged_emp_company_id;
		// exit();
		
        return view('module.settings.attendanceSummary', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    public function indexReport() {
        $company = Company::all();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');

        return view('module.settings.AttendanceReport', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
    private function ManualJobCardEntryCheck($param = 0, $date = '0000-00-00', $emp_code = '0', $day_status = '', $company_id = '') {

        if ($param == "A") {
            $tab_log_date = ManualJobCardEntry::where('emp_code', $emp_code)
                    ->where('date', $date)
                    ->count();
            if (count($tab_log_date) == 0) {
                $jobcard_day_status = "A";
            } else {

                $tab_log_date = DB::table('manual_job_card_entries')
                        ->where('emp_code', $emp_code)
                        ->where('date', $date)
                        ->take(1)
                        ->get();
                //print_r($tab_log_date);
                $jobcard_day_status = "A";
                if (isset($tab_log_date[0])) {
                    $tab_log_dates = $tab_log_date[0];
                    $jobcard_day_status = $tab_log_dates->day_type;


                    $chkDate = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $date)->count();
//                    print_r($chkDate);
//                          exit();
                    if (!empty($chkDate)) {
                        $tab = AttendanceJobcard::where('emp_code', $emp_code)->where('start_date', $date)
                                ->update(['admin_day_status' => $jobcard_day_status]);
                    } else {
                        $tab = new AttendanceJobcard();
                        $tab->emp_code = $tab_log_dates->emp_code;
                        $tab->company_id = $tab_log_dates->company_id;
                        $tab->start_date = $tab_log_dates->date;
                        $tab->$day_status = $jobcard_day_status;
                        $tab->save();
                    }
                } else {
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                }
            }

            return $jobcard_day_status;
        } else {
            return $param;
        }
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


        /*$sqlEmp = DB::table('employee_departments')
                ->select('employee_departments.id', 'employee_departments.emp_code')
                ->where('company_id', $company_id)
				->when($department, function ($query) use ($department) {
                    return $query->where('department_id', $department);
                })
				//->where('employee_departments.emp_code','RPAC0578')
                ->groupby('employee_departments.emp_code')
                ->orderby('employee_departments.id')
                ->get();*/
				
		$sqlEmp = DB::table('employee_infos')
                ->select('employee_infos.id', 'employee_infos.emp_code')
                ->where('employee_infos.company_id', $company_id)
				
				/*->when($department, function ($query) use ($department) {
                    return $query->where('department_id', $department);
                })*/
				
				//->where('employee_departments.emp_code','RPAC0578')
                //->groupby('employee_departments.emp_code')
                ->orderby('employee_infos.emp_code')
                ->get();		

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

        $json[] = array('emp_codeH' => 'EMP CODE', 'ddataH' => $ddates);
		
		// print_r($sqlEmp);
		// exit();
		
		//                        ->select(DB::raw("calendars.date, (SELECT attendance_jobcards.admin_day_status FROM attendance_jobcards WHERE attendance_jobcards.start_date=" . DB::raw("calendars.date") . " AND attendance_jobcards.emp_code='" . $row->emp_code . "') as day_status"))
		
        if (!empty($sqlEmp)) {

            foreach ($sqlEmp as $row):
                $ddate = [];
				$emp_code=$row->emp_code;
				$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($emp_code);
				if(empty($cal_company_id))
				{
					$cal_company_id=$company_id;
				}
				
				//echo $cal_company_id;
				
                $sqlDates = DB::table('calendars')
						->join('day_types','calendars.day_type_id','=','day_types.id')
						->leftjoin('attendance_jobcards', function ($join) use ($emp_code) {
                            $join->on('calendars.date', '=', 'attendance_jobcards.start_date')
                                 ->where('attendance_jobcards.emp_code', '=',$emp_code);
                        })
                        ->select(DB::raw("calendars.date,day_types.day_short_code,IFNULL(attendance_jobcards.admin_day_status,'A') as day_status"))
                        ->where("calendars.company_id",$cal_company_id)
                        ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                        ->groupby(DB::raw("calendars.date"))
                        ->get();
							


                if (!empty($sqlDates)) {
                    foreach ($sqlDates as $log):
						$dateRow=$log->date;
						$dateRowType=$log->day_short_code;
						
						$std_day_primary=array("W","H","A");
						if(in_array($log->day_status,$std_day_primary))
						{
							
							$NewLeaveDayType=$this->LeaveDayManuallyCheck($dateRow,$emp_code,$log->day_status);
							if(in_array($NewLeaveDayType,$std_day_primary))
							{
								if(in_array($dateRowType,$std_day_primary) && $NewLeaveDayType=='A')
								{
									$ddate[] = array('date' => $log->date, 'day_status' =>$dateRowType);
								}
								else
								{
									$ddate[] = array('date' => $log->date, 'day_status' =>$NewLeaveDayType);
								}
							}
							else
							{
								$ddate[] = array('date' => $log->date, 'day_status' =>$log->day_status);
							}
						}
						else
						{
							$stdDayLate=array("Late IN","Late OUT");
							if(in_array($log->day_status,$stdDayLate))
							{
								$ddate[] = array('date' => $log->date, 'day_status' =>"P");
							}
							else
							{
								$ddate[] = array('date' => $log->date, 'day_status' => $log->day_status);
							}
							
						}
                    endforeach;
                }

                $json[] = array('emp_code' => $row->emp_code, 'ddata' => $ddate);
            endforeach;
        }

        return response()->json($json);
        //  return response()->json(array("data" => $json, "total" => count($json)));
    }
	
	private function LeaveDayManuallyCheck($date = '0000-00-00', $emp_code = '0',$defDay='A') {
        $chk = LeaveApplicationMaster::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->where('emp_code', $emp_code)
				->where('leave_status','Approved')
                ->count();
				
				
				
        if ($chk == 0) {
            return $defDay;
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
					return $defDay;
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
					return $defDay;
				}
				elseif($sql->total_days_applied=="1")
				{
					return $sql->leave_short_code;
				}
				else
				{
					return $defDay;
				}
			}
        }
		
		
		
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

        if (!empty($sqlDates)) {
            $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;

                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.start_date', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_out_time', 'attendance_jobcards.admin_total_time', 'attendance_jobcards.admin_total_ot', 'attendance_jobcards.admin_day_status'
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                            $q->orWhere('attendance_jobcards.end_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();

                if (count($data) != 0) {
                    $jobcard_id = $data[0]->id;
//                    $jobcard_in_time = $data[0]->admin_in_time;
//                    $jobcard_out_time = $data[0]->admin_out_time;
//                    $jobcard_total_time = $data[0]->admin_total_time;
//                    $jobcard_total_ot = $data[0]->admin_total_ot;
                    $jobcard_day_status = $data[0]->admin_day_status;
                } else {
                    $jobcard_id = 0;
//                    $jobcard_in_time = "00:00:00";
//                    $jobcard_out_time = "00:00:00";
//                    $jobcard_total_time = "00:00:00";
//                    $jobcard_total_ot = "00:00:00";
                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                }

                $json[] = array(
                    'date' => $ld,
//                    'in_time' => $jobcard_in_time,
//                    'out_time' => $jobcard_in_time,
//                    'total_time' => $jobcard_total_time,
//                    'total_ot' => $jobcard_total_ot,
                    'day_status' => $jobcard_day_status
                );

            endforeach;
        }

        //print_r($json);

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AttendanceRawData  $attendanceRawData
     * @return \Illuminate\Http\Response
     */
    public function exportExcelSummary($company_id=0, $department = '', $start_date = 0, $end_date = 0) {

        if (empty($company_id)) {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }
		
		if(empty($department))
		{
			$department='';
		}
		
		
		$sqlEmp = DB::table('employee_infos')
                ->select('employee_infos.id', 'employee_infos.emp_code')
                ->where('employee_infos.company_id', $company_id)
				
				/*->when($department, function ($query) use ($department) {
                    return $query->where('department_id', $department);
                })*/
				
				//->where('employee_departments.emp_code','RPAC0578')
                //->groupby('employee_departments.emp_code')
                ->orderby('employee_infos.emp_code')
                ->get();

        /*$sqlEmp = DB::table('employee_departments')
                ->select('employee_departments.id', 'employee_departments.emp_code')
                ->where('company_id', $company_id)
				->when($department, function ($query) use ($department) {
                    return $query->where('department_id', $department);
                })
				//->where('employee_departments.emp_code','RPAC0578')
                ->groupby('employee_departments.emp_code')
                ->orderby('employee_departments.id')
                ->get();*/

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
		
		$ddates[] = array('date' =>'Total Summary');

//        $json[] = array('emp_codeH' => 'EMP CODE', 'ddataH' => $ddates);
        $jsonH[] = array('ddataH' => $ddates);
		
		//print_r($jsonH);
		//exit();
		
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
                        ->join('day_types','calendars.day_type_id','=','day_types.id')
						->leftjoin('attendance_jobcards', function ($join) use ($emp_code) {
                            $join->on('calendars.date', '=', 'attendance_jobcards.start_date')
                                 ->where('attendance_jobcards.emp_code', '=',$emp_code);
                        })
                        ->select(DB::raw("calendars.date,day_types.day_short_code, IFNULL(attendance_jobcards.admin_day_status,'A') as day_status"))
                        ->where("calendars.company_id",$cal_company_id)
                        ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                        ->groupby(DB::raw("calendars.date"))
                        ->get();
						
				//print_r($sqlDates);		
				//exit();
				$liste=array();
                if (!empty($sqlDates)) {
                    foreach ($sqlDates as $log):
					
					
						$dateRow=$log->date;
						$dateRowType=$log->day_short_code;
						
						$std_day_primary=array("W","H","A");
						$new_day_type='A';
						if(in_array($log->day_status,$std_day_primary))
						{
							
							$NewLeaveDayType=$this->LeaveDayManuallyCheck($dateRow,$emp_code,$log->day_status);
							if(in_array($NewLeaveDayType,$std_day_primary))
							{
								if(in_array($dateRowType,$std_day_primary) && $NewLeaveDayType=='A')
								{
									$ddate[] = $dateRowType;
									$new_day_type=$dateRowType;
								}
								else
								{
									$ddate[] =$NewLeaveDayType;
									$new_day_type=$NewLeaveDayType;
								}
							}
							else
							{
								$ddate[] = $log->day_status;
								$new_day_type=$log->day_status;
							}
						}
						else
						{
							$stdDayLate=array("Late IN","Late OUT");
							if(in_array($log->day_status,$stdDayLate))
							{
								$ddate[] = "P";
								$new_day_type="P";
							}
							else
							{
								$ddate[] = $log->day_status;
								$new_day_type=$log->day_status;
							}
						}
						
						array_push($liste,$new_day_type);
						
                    endforeach;
                }
				
				$content='';
				$arrayUnique=array_count_values($liste);
				//print_r($arrayUnique);
				$ik=0;
				foreach($arrayUnique as $key=>$unq):
					if($ik!=0)
					{
						$content .=' | ';
					}
					$content .=$key.':'.$unq;
					$ik++;
				endforeach;
				
				$ddate[] = $content;

                array_unshift($ddate, $row->emp_code);
				
                $ExcelDataArray[] = $ddate;
				//$ExcelDataArray[] = $content;
            endforeach;
        }

		 //echo "<pre>";
		 //print_r($ExcelDataArray);

		 //exit();

        $ExcelHeadding = [];
        $ExcelHeadding = array("Employee Code");

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
		
				// echo "<pre>";
		// print_r($excelArray);

		// exit();
		
        // Generate and return the spreadsheet
        // \Excel::create('Attendance Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            //Set the spreadsheet title, creator, and description
            // $excel->setTitle('Attendance Report');
            // $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            // $excel->setDescription('Attendance Report');

            //Build the spreadsheet, passing in the payments array
            // $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                // $sheet->fromArray($excelArray, null, 'A1', false, false);
            // });
        // })->download('xlsx');
		
		
		
		\Excel::create('Attendance Report ' .$start_date.' TO '.$end_date, function($excel) use ($excelArray) {
			
            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Attendance Summary Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Attendance Summary Report');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('Attendance_Report', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
			
			//echo "Download Excel Error";
			//exit();
			
        })->download('xlsx');
		
    }

    public function exportPdfSummary($company=0, $department = '',  $start_date = 0, $end_date = 0) {
        app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        $content = '<h3>Attendance Report</h3>';
        $content .= '<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';


        if (empty($company)) {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }
		else
		{
			$company_id=$company;
		}
		
		if(empty($department))
		{
			$department='';
		}
		
        // instantiate and use the dompdf class
        $excelArray = [];
        $excelArray [] = "Employee code";
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
//
//
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



            /*$sqlEmp = DB::table('employee_departments')
                    ->select(
                            'employee_departments.id', 'employee_departments.emp_code'
                    )
                    ->where('company_id', $company_id)
					->when($department, function ($query) use ($department) {
						return $query->where('department_id', $department);
					})
                    ->groupby('employee_departments.emp_code')
                    ->orderby('employee_departments.id')
                    ->get();
			*/		
			$sqlEmp = DB::table('employee_infos')
                ->select('employee_infos.id', 'employee_infos.emp_code')
                ->where('employee_infos.company_id', $company_id)
				
				/*->when($department, function ($query) use ($department) {
                    return $query->where('department_id', $department);
                })*/
				
				//->where('employee_departments.emp_code','RPAC0578')
                //->groupby('employee_departments.emp_code')
                ->orderby('employee_infos.emp_code')
                ->get();		

            $json = [];
            $ddate = [];
            if (!empty($sqlEmp)) {

                foreach ($sqlEmp as $row):

                    $emp_code=$row->emp_code;
					$sqlDates = DB::table('calendars')
							->leftjoin('attendance_jobcards', function ($join) use ($emp_code) {
								$join->on('calendars.date', '=', 'attendance_jobcards.start_date')
									 ->where('attendance_jobcards.emp_code', '=',$emp_code);
							})
                            ->where("calendars.company_id", $company_id)
                            ->whereBetween("calendars.date", [DB::raw($start_date), DB::raw($end_date)])
                            ->groupby(DB::raw("calendars.date"))
                            ->get();


                    if (!empty($sqlDates)) {
                        foreach ($sqlDates as $key => $log):
                            
                            $ddate['Employee code'] = $row->emp_code;
                            $ddate[date('d-M', strtotime($log->date))] = $log->day_status;

                        endforeach;
                    }
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

        if (!empty($sqlDates)) {
            // $json = [];
            foreach ($sqlDates as $line):

                $ld = $line->date;

                $data = DB::table('attendance_jobcards')
                        ->select(
                                'attendance_jobcards.id', 'attendance_jobcards.admin_in_time', 'attendance_jobcards.admin_day_status'
                        )
                        ->where('attendance_jobcards.emp_code', $emp_code)
                        ->where(function($q) use ($ld) {
                            $q->where('attendance_jobcards.start_date', $ld);
                            $q->orWhere('attendance_jobcards.end_date', $ld);
                        })
                        ->orderBy('attendance_jobcards.id', 'DESC')
                        ->get();

                if (count($data) != 0) {
                    $jobcard_id = $data[0]->id;

                    $jobcard_day_status = $data[0]->admin_day_status;
                } else {
                    $jobcard_id = 0;

                    $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                }

                $json[] = array('date' => $ld,
                    'day_status' => $jobcard_day_status,
                );

            endforeach;
        }


        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [
            'Date',
            'Day Status',
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
        //exit();
        // Generate and return the spreadsheet
        \Excel::create('Attendance Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Attendance Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Attendance Report');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdfReport(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
        $content = '<h3>Attendance Report</h3>';
        $content .= '<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = [
            'date',
            'day_status',
        ];

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
            $emp_code = $request->emp_code;
            $start_date = $request->start_date;
            $end_date = $request->end_date;


            $sqlEmp = DB::table('employee_infos')->where('emp_code', $emp_code)->get();

            $company_id = $sqlEmp[0]->company_id;


            $sqlDates = Calendar::where('company_id', $company_id)
                    ->whereBetween('date', [$start_date, $end_date])
                    ->get();

            if (!empty($sqlDates)) {
                $datarows = [];
                foreach ($sqlDates as $line):

                    $ld = $line->date;

                    $data = DB::table('attendance_jobcards')
                            ->select(
                                    'attendance_jobcards.id', 'attendance_jobcards.admin_day_status'
                            )
                            ->where('attendance_jobcards.emp_code', $emp_code)
                            ->where(function($q) use ($ld) {
                                $q->where('attendance_jobcards.start_date', $ld);
                                $q->orWhere('attendance_jobcards.end_date', $ld);
                            })
                            ->orderBy('attendance_jobcards.id', 'DESC')
                            ->get();

                    if (count($data) != 0) {
                        $jobcard_id = $data[0]->id;

                        $jobcard_day_status = $data[0]->admin_day_status;
                    } else {
                        $jobcard_id = 0;

                        $jobcard_day_status = $this->ManualJobCardEntryCheck("A", $ld, $emp_code, 'admin_day_status', $company_id);
                    }

                    $datarows[] = array('date' => $ld,
                        'day_status' => $jobcard_day_status,
                    );

                endforeach;
            }
            if (!empty($datarows)) {
                $content .= '<tbody>';
                foreach ($datarows as $draw):

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

            $content .= '<h4>Total : ' . count($datarows) . '</h4>';


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

}
