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

class MissingPunchReportINOUTController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    public function InTimeIndex() {
        $company = Company::all();

        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        return view('module.settings.MissingPunchReport', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }
    
    
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

    public function filterInTimeMissingPunch(Request $request) {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if (!empty($request->company_id) || $request->company_id != 0) {
            $company_id = $request->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }

        $json = DB::select("SELECT aj.id,
                    ei.emp_code,
                    concat(ei.first_name,' ',IFNULL(ei.last_name,'')) as emp_name,
                    aj.start_date,
                    aj.admin_in_time
                    FROM `attendance_jobcards` AS aj
                    LEFT JOIN employee_infos AS ei ON aj.emp_code=ei.emp_code
                    WHERE aj.company_id='" . $company_id . "'
                    AND (aj.start_date BETWEEN '" . $start_date . "' AND '" . $end_date . "') AND (aj.admin_in_time='00:00:00' AND aj.admin_in_time='')");
//            print_r($json);
//            exit();
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    

    public function updateInTimeMissingPunch(Request $request) {

        foreach ($request->models as $key => $value) {
            $tab = AttendanceJobcard::find($value['id']);
            $tab->admin_in_time = $this->getTime($value['admin_in_time'],true);
            $tab->save();
        }
    }

    

    /* For exporting into excel and pdf */

    public function exportExcelIn(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {
        
        $company_id = $request->company_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        
        if (!empty($request->company_id) || $request->company_id != 0) {
            $company_id = $request->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }

        $dbfields = DB::select("SELECT aj.id,
                    ei.emp_code,
                    concat(ei.first_name,' ',IFNULL(ei.last_name,'')) as emp_name,
                    aj.start_date,
                    aj.admin_in_time
                    FROM `attendance_jobcards` AS aj
                    LEFT JOIN employee_infos AS ei ON aj.emp_code=ei.emp_code
                    WHERE aj.company_id='" . $company_id . "'
                    AND aj.start_date BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                    AND aj.admin_in_time='00:00:00'");

        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray[] = [
        'id',
        'emp_code',
        'emp_name',
        'start_date',
        'admin_in_time'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $field) {
            $excelArray[] = $field->toArray();
        }

        // Generate and return the spreadsheet
        \Excel::create('InTimeMissingPunchData_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('InTimeMissingPunchReport');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('InTimeMissingPunchReport');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdfIn(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {

        $content = '<h3>In Time Missing Punch Report</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = [
        'id',
        'emp_code',
        'emp_name',
        'start_date',
        'admin_in_time'
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
            
            $company_id = $request->company_id;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            if (!empty($request->company_id) || $request->company_id != 0) {
                $company_id = $request->company_id;
            } else {
                $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
                $company_id = $alt_company_id;
            }


            $datarows = DB::select("SELECT aj.id,
                    ei.emp_code,
                    concat(ei.first_name,' ',IFNULL(ei.last_name,'')) as emp_name,
                    aj.start_date,
                    aj.admin_in_time
                    FROM `attendance_jobcards` AS aj
                    LEFT JOIN employee_infos AS ei ON aj.emp_code=ei.emp_code
                    WHERE aj.company_id='" . $company_id . "'
                    AND aj.start_date BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                    AND aj.admin_in_time='00:00:00'");
        
            if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):
                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td>' . $draw->$fid . '</td>';
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

        //echo $content;
        //print_r($excelArray);
        //exit();
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($content);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('Legal', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
    
    
    /*
     * 
     * 
     * OUT TIME MISSING PUNCH REPORT STARTS HERE
     * 
     * 
     */
    
    
    
    public function OutTimeIndex() {
        $company = Company::all();

        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        return view('module.settings.MissingPunchReport', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }
    
    
    

    public function filterMissingPunch(Request $request) {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if (!empty($request->company_id) || $request->company_id != 0) {
            $company_id = $request->company_id;
        } else {
            $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
            $company_id = $alt_company_id;
        }			

        $json = DB::select("SELECT aj.id,
                    ei.emp_code,
                    ei.first_name as emp_name,
                    aj.start_date,
					aj.admin_day_status,
					aj.end_date,
                    aj.admin_in_time,
					aj.admin_out_time
                    FROM `attendance_jobcards` AS aj
                    LEFT JOIN employee_infos AS ei ON aj.emp_code=ei.emp_code
                    WHERE ei.company_id='" . $company_id . "'
					AND (aj.admin_day_status NOT IN ('SL','CL','W','H','A','AL','LL','LOP','T','ML'))
                    AND (aj.start_date BETWEEN '" . $start_date . "' AND '" . $end_date . "') 
					AND ((aj.admin_in_time='00:00:00' OR (aj.admin_in_time IS NULL)) OR (aj.admin_out_time='00:00:00' OR (aj.admin_out_time IS NULL))  OR (aj.admin_in_time=aj.admin_out_time)) ORDER BY aj.emp_code");

						
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    

    public function updateOutTimeMissingPunch(Request $request) {

        foreach ($request->models as $key => $value) {
            $tab = AttendanceJobcard::find($value['id']);
            $tab->admin_out_time = $this->getTime($value['admin_out_time'],true);
            $tab->save();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /* For exporting into excel and pdf */
	
	 public function exportExcelOut($company_id = 0, $start_date = 0, $end_date = 0) {

				
		$dbfields = DB::table('attendance_jobcards')
					->leftJoin('employee_infos','attendance_jobcards.emp_code','=','employee_infos.emp_code')
					->select(DB::Raw('attendance_jobcards.id,
                    employee_infos.emp_code,
                    employee_infos.first_name as emp_name,
                    attendance_jobcards.start_date,
					attendance_jobcards.end_date,
                    attendance_jobcards.admin_in_time,
					attendance_jobcards.admin_out_time,attendance_jobcards.admin_day_status'))
					->whereRaw("employee_infos.company_id='" . $company_id . "' 
					AND (attendance_jobcards.admin_day_status NOT IN ('SL','CL','W','H','A','AL','LL','LOP','T','ML'))
                    AND (attendance_jobcards.start_date BETWEEN '" . $start_date . "' AND '" . $end_date . "') 
					AND ((attendance_jobcards.admin_in_time='00:00:00' OR (attendance_jobcards.admin_in_time IS NULL)) OR (attendance_jobcards.admin_out_time='00:00:00' OR (attendance_jobcards.admin_out_time IS NULL))  OR (attendance_jobcards.admin_in_time=attendance_jobcards.admin_out_time))")
					->orderBy('attendance_jobcards.emp_code')
					->get();		

        if (!empty($dbfields)) {
			$json = [];
            foreach ($dbfields as $line):

                $json[] = array(
                    'id' => $line->id,
					'emp_code' => $line->emp_code,
					'emp_name' => $line->emp_name,
                    'start_date' => $line->start_date,
                    'in_time' => $line->admin_in_time,
					'end_date' => $line->end_date,
                    'out_time' => $line->admin_out_time,
                    'day_status' => $line->admin_day_status,
                );

            endforeach;
        }


        $excelArray = [];

// Define the Excel spreadsheet headers
        $excelArray [] = [
            'ID',
            'EMP Code',
            'Name',
            'Start Date',
            'In Time',
			'End Date',
            'Out Time',
            'Day Status',
        ];

// Convert each member of the returned collection into an array,
// and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
//exit();
// Generate and return the spreadsheet
        \Excel::create('Attendance Missing Punch Report_' . $start_date.' TO '.$end_date, function($excel) use ($excelArray) {

// Set the spreadsheet title, creator, and description
            $excel->setTitle('Attendance Missing Punch Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Attendance Missing Punch Report');

// Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }



    public function exportPdfOut(Request $request, $emp_code = 0, $start_date = 0, $end_date = 0) {

        $content = '<h3>Missing Punch Report</h3>';
        $content .='<h5>Genarated : ' . $start_date . ' To '.$end_date.'</h5>';
        // instantiate and use the dompdf class
        $excelArray = [
        'id',
        'emp_code',
        'emp_name',
        'start_date',
        'in_time',
        'end_date',
        'out_time',
        'status',
        ];
        if (!empty($excelArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            foreach ($excelArray as $exhead):
                $content .='<th>' . ucwords(str_replace('_',' ',$exhead)) . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
            
            $company_id = $request->company_id;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            if (!empty($request->company_id) || $request->company_id != 0) {
                $company_id = $request->company_id;
            } else {
                $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
                $company_id = $alt_company_id;
            }


            $datarows = DB::table('attendance_jobcards')
					->leftJoin('employee_infos','attendance_jobcards.emp_code','=','employee_infos.emp_code')
					->select(DB::Raw('attendance_jobcards.id,
                    employee_infos.emp_code,
                    employee_infos.first_name as emp_name,
                    attendance_jobcards.start_date,
					attendance_jobcards.end_date,
                    attendance_jobcards.admin_in_time as in_time,
					attendance_jobcards.admin_out_time as out_time,attendance_jobcards.admin_day_status as status'))
					->whereRaw("employee_infos.company_id='" . $company_id . "' 
					AND (attendance_jobcards.admin_day_status NOT IN ('SL','CL','W','H','A','AL','LL','LOP','T','ML')) 
                    AND (attendance_jobcards.start_date BETWEEN '" . $start_date . "' AND '" . $end_date . "') 
					AND ((attendance_jobcards.admin_in_time='00:00:00' OR (attendance_jobcards.admin_in_time IS NULL)) OR (attendance_jobcards.admin_out_time='00:00:00' OR (attendance_jobcards.admin_out_time IS NULL))  OR (attendance_jobcards.admin_in_time=attendance_jobcards.admin_out_time))")
					->orderBy('attendance_jobcards.emp_code')
					->get();	
        
            if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):
                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td style="font-size:13px;">' . $draw->$fid . '</td>';
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

        //echo $content;
        //print_r($excelArray);
        //exit();
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
