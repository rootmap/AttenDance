<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\Year;
use App\Calendar;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class LeaveRegisterController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexRegister() {
        $company = Company::all();
        $year=Year::all();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');

        return view('module.settings.leaveRegister', ['year'=>$year, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
    public function showRegister(Request $request) {

        $year = $request->year;
        if (!empty($request->company)) {
            $company_id = $request->company;
        } else {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }

        //First Get Employees List From leave_assigned_yearly_datas
        $emp_data=DB::table('leave_assigned_yearly_datas')
        ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_departments','leave_assigned_yearly_datas.emp_code','=','employee_departments.emp_code')
        ->leftjoin('departments','employee_departments.department_id','=','departments.id')
        ->leftjoin('employee_designations','leave_assigned_yearly_datas.emp_code','=','employee_designations.emp_code')
        ->leftjoin('designations','employee_designations.designation_id','=','designations.id')
        ->leftjoin('employee_staff_grades','leave_assigned_yearly_datas.emp_code','=','employee_staff_grades.emp_code')
        ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')

        ->select(DB::raw('employee_infos.emp_code,
          concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
          departments.name as emp_department,
          designations.name as emp_designation,
          staff_grades.name as emp_staff_grade'))
        ->where('leave_assigned_yearly_datas.company_id','=',$company_id)
        ->where('leave_assigned_yearly_datas.year','=',$year)
        ->groupBy('leave_assigned_yearly_datas.emp_code')
        ->orderBy('leave_assigned_yearly_datas.id','ASC')

        ->get();


        $emp_leave = [];
        foreach ($emp_data as $rowemp) {
          $leave_data=DB::table('leave_policies')
          ->leftjoin(DB::raw("(SELECT * FROM leave_assigned_yearly_datas WHERE emp_code='".$rowemp->emp_code."' AND year='".$year."' AND company_id='".$company_id."') as yld"),DB::raw('yld.leave_policy_id'),'=','leave_policies.id')
          ->select(DB::raw('leave_policies.leave_title,
            IFNULL(yld.total_days, 0) AS total_days,
            IFNULL(yld.availed_days, 0) AS availed_days,
            IFNULL(yld.remaining_days, 0) AS remaining_days'))
          ->where('leave_policies.company_id',$company_id)
          ->groupBy('leave_policies.id')
          ->get();
          //echo "<pre>";
          //print_r($leave_data);
          //exit();

          $retarray=array("emp_code"=>$rowemp->emp_code,
          "emp_name"=>$rowemp->emp_name,
          "emp_department"=>$rowemp->emp_department,
          "emp_designation"=>$rowemp->emp_designation,
          "emp_staff_grade"=>$rowemp->emp_staff_grade,
          "leave_data"=>$leave_data);

           $emp_leave []= $retarray;
        }

        return response()->json($emp_leave);
        //  return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AttendanceRawData  $attendanceRawData
     * @return \Illuminate\Http\Response
     */
    public function exportExcelRegister(Request $request) {


        if (!empty($request->company)) {
            $company_id = $request->company;
        } else {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }

        if (!empty($request->year)) {
            $year = $request->year;
        } else {
            $year = date('Y');
        }


        //First Get Employees List From leave_assigned_yearly_datas
        $emp_data=DB::table('leave_assigned_yearly_datas')
        ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_departments','leave_assigned_yearly_datas.emp_code','=','employee_departments.emp_code')
        ->leftjoin('departments','employee_departments.department_id','=','departments.id')
        ->leftjoin('employee_designations','leave_assigned_yearly_datas.emp_code','=','employee_designations.emp_code')
        ->leftjoin('designations','employee_designations.designation_id','=','designations.id')
        ->leftjoin('employee_staff_grades','leave_assigned_yearly_datas.emp_code','=','employee_staff_grades.emp_code')
        ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')

        ->select(DB::raw('employee_infos.emp_code,
          concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
          departments.name as emp_department,
          designations.name as emp_designation,
          staff_grades.name as emp_staff_grade'))
        ->where('leave_assigned_yearly_datas.company_id','=',$company_id)
        ->where('leave_assigned_yearly_datas.year','=',$year)
        ->groupBy('leave_assigned_yearly_datas.emp_code')
        ->orderBy('leave_assigned_yearly_datas.id','ASC')

        ->get();


        $emp_leave = [];
        foreach ($emp_data as $rowemp) {
          $leave_data=DB::table('leave_policies')
          ->leftjoin(DB::raw("(SELECT * FROM leave_assigned_yearly_datas WHERE emp_code='".$rowemp->emp_code."' AND year='".$year."' AND company_id='".$company_id."') as yld"),DB::raw('yld.leave_policy_id'),'=','leave_policies.id')
          ->select(DB::raw('leave_policies.leave_title,
            IFNULL(yld.total_days, 0) AS total_days,
            IFNULL(yld.availed_days, 0) AS availed_days,
            IFNULL(yld.remaining_days, 0) AS remaining_days'))
          ->where('leave_policies.company_id',$company_id)
          ->groupBy('leave_policies.id')
          ->get();
          //echo "<pre>";
          //print_r($leave_data);
          //exit();

          $retarray=array("emp_code"=>$rowemp->emp_code,
          "emp_name"=>$rowemp->emp_name,
          "emp_department"=>$rowemp->emp_department,
          "emp_designation"=>$rowemp->emp_designation,
          "emp_staff_grade"=>$rowemp->emp_staff_grade);

          $ii=0;
          $new_array=[];
          foreach($leave_data as $row):
              $keys=str_replace(" ","_",strtolower($row->leave_title));
              $new_array[$keys.'_total_days']=$row->total_days;
              $new_array[$keys.'_availed_days']=$row->availed_days;
              $new_array[$keys.'_remaining_days']=$row->remaining_days;
          endforeach;


          $res_array=array_merge($retarray,$new_array);



          // $retarray=array("emp_code"=>$rowemp->emp_code,
          // "emp_name"=>$rowemp->emp_name,
          // "emp_department"=>$rowemp->emp_department,
          // "emp_designation"=>$rowemp->emp_designation,
          // "emp_staff_grade"=>$rowemp->emp_staff_grade,
          // "leave_data"=>$leave_data);


           $emp_leave []= $res_array;
        }

        // return response()->json(array("data" => $json, "total" => count($json)));


        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [
            'Employee Code',
            'Name',
            'Designation',
            'Department',
            'Staff Grade',
        ];

        $chk_leave_data_head=DB::table('leave_policies')
                      ->select(DB::raw('leave_policies.leave_title'))
                      ->where('leave_policies.company_id',$company_id)
                      ->count();

        if($chk_leave_data_head!=0)
        {
          $leave_data_head=DB::table('leave_policies')
                        ->select(DB::raw('leave_policies.leave_title'))
                        ->where('leave_policies.company_id',$company_id)
                        ->get();



            foreach($leave_data_head as $row):
              for($i=1; $i<=3; $i++):
                if($i==1)
                {
                  array_push($excelArray[0],$row->leave_title." Total Days");
                }
                elseif($i==2)
                {
                  array_push($excelArray[0],$row->leave_title." Availed Days");
                }
                elseif($i==3)
                {
                  array_push($excelArray[0],$row->leave_title." Remaining Days");
                }

              endfor;
            endforeach;
            //array_merge($leave_data_head,$excelArray);

            //$result = array_merge((array)$excelArray, (array)$leave_data_head);


        }


         //echo "<pre>";
         //print_r($excelArray);
         //print_r($emp_leave);

         //exit();
  //exit();


        // Convert each member of the returned collection into an array,
        // and append it to the payments array.get_object_vars()
        foreach ($emp_leave as $key => $field) {
            $excelArray[] = $field;
            //echo '<pre>';
            //  print_r($excelArray);
        }

        //exit();
        // Generate and return the spreadsheet
        \Excel::create('Leave Register_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Leave Register Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Leave Register Report');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdfRegister(Request $request) {
        if (!empty($request->company)) {
            $company_id = $request->company;
        } else {
            $company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        }

        if (!empty($request->year)) {
            $year = $request->year;
        } else {
            $year = date('Y');
        }

        $content = '<h3>Leave Register Report</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = [
            'Employee Code',
            'Name',
            'Designation',
            'Department',
            'Staff Grade',
        ];

        $chk_leave_data_head=DB::table('leave_policies')
                      ->select(DB::raw('leave_policies.leave_title'))
                      ->where('leave_policies.company_id',$company_id)
                      ->count();

        if($chk_leave_data_head!=0)
        {
          $leave_data_head=DB::table('leave_policies')
                        ->select(DB::raw('leave_policies.leave_title'))
                        ->where('leave_policies.company_id',$company_id)
                        ->get();



            foreach($leave_data_head as $row):
              for($i=1; $i<=3; $i++):
                if($i==1)
                {
                  array_push($excelArray,$row->leave_title." Total Days");
                }
                elseif($i==2)
                {
                  array_push($excelArray,$row->leave_title." Availed Days");
                }
                elseif($i==3)
                {
                  array_push($excelArray,$row->leave_title." Remaining Days");
                }

              endfor;
            endforeach;
            //array_merge($leave_data_head,$excelArray);

            //$result = array_merge((array)$excelArray, (array)$leave_data_head);


        }

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



            //First Get Employees List From leave_assigned_yearly_datas
            $emp_data=DB::table('leave_assigned_yearly_datas')
            ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')
            ->leftjoin('employee_departments','leave_assigned_yearly_datas.emp_code','=','employee_departments.emp_code')
            ->leftjoin('departments','employee_departments.department_id','=','departments.id')
            ->leftjoin('employee_designations','leave_assigned_yearly_datas.emp_code','=','employee_designations.emp_code')
            ->leftjoin('designations','employee_designations.designation_id','=','designations.id')
            ->leftjoin('employee_staff_grades','leave_assigned_yearly_datas.emp_code','=','employee_staff_grades.emp_code')
            ->leftjoin('staff_grades','employee_staff_grades.staff_grade_id','=','staff_grades.id')

            ->select(DB::raw('employee_infos.emp_code,
              concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
              departments.name as emp_department,
              designations.name as emp_designation,
              staff_grades.name as emp_staff_grade'))
            ->where('leave_assigned_yearly_datas.company_id','=',$company_id)
            ->where('leave_assigned_yearly_datas.year','=',$year)
            ->groupBy('leave_assigned_yearly_datas.emp_code')
            ->orderBy('leave_assigned_yearly_datas.id','ASC')

            ->get();
            //ss
            $emp_leave = [];
            foreach ($emp_data as $rowemp) {
              $leave_data=DB::table('leave_policies')
              ->leftjoin(DB::raw("(SELECT * FROM leave_assigned_yearly_datas WHERE emp_code='".$rowemp->emp_code."' AND year='".$year."' AND company_id='".$company_id."') as yld"),DB::raw('yld.leave_policy_id'),'=','leave_policies.id')
              ->select(DB::raw('leave_policies.leave_title,
                IFNULL(yld.total_days, 0) AS total_days,
                IFNULL(yld.availed_days, 0) AS availed_days,
                IFNULL(yld.remaining_days, 0) AS remaining_days'))
              ->where('leave_policies.company_id',$company_id)
              ->groupBy('leave_policies.id')
              ->get();
              //echo "<pre>";
              //print_r($leave_data);
              //exit();

              $retarray=array("emp_code"=>$rowemp->emp_code,
              "emp_name"=>$rowemp->emp_name,
              "emp_department"=>$rowemp->emp_department,
              "emp_designation"=>$rowemp->emp_designation,
              "emp_staff_grade"=>$rowemp->emp_staff_grade);

              $new_array=[];
              foreach($leave_data as $row):
                  $keys=str_replace(" ","_",strtolower($row->leave_title));
                  $new_array[$keys.'_total_days']=$row->total_days;
                  $new_array[$keys.'_availed_days']=$row->availed_days;
                  $new_array[$keys.'_remaining_days']=$row->remaining_days;
              endforeach;

              $res_array=array_merge($retarray,$new_array);

              $emp_leave []= $res_array;
            }
            // echo '<pre>';
            // print_r($emp_leave);
            // exit();
            // $json = [];
            // if (!empty($emp_data)) {
            //
            //     foreach ($sqlEmp as $row):
            //
            //
            //         $json[] = array('Date' => $log->date, 'Employee_Code' => $row->emp_code, 'Day_Status' => $day_status);
            //     endforeach;
            // }

            if (!empty($emp_leave)) {
                $content .='<tbody>';
                foreach ($emp_leave as $draw):

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

            $content .='<h4>Total : ' . count($emp_leave) . '</h4>';


            $content .='<br /><br /><br /><table border="0" width="100%">';
            $content .='<tr>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
            $content .='</tr>';


            $content .='</table>';
        }

        echo $content;
        exit();
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($content);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('legal', 'landscape');

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

}
