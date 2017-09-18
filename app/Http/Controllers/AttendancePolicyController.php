<?php

namespace App\Http\Controllers;

use App\AttendancePolicy;
use App\Company;
use App\CalenderWeekDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class AttendancePolicyController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $company = Company::all();
        $calendar = CalenderWeekDay::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.attendancePolicy', ['company' => $company, 'calendar' => $calendar, 'logged_emp_com' => $logged_emp_company_id]);
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
        $this->validate($request, [
            'company_id' => 'required',
            'title' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'total_hours' => 'required',
        ]);

        $half_day_work = 0;
        if (!empty($request->half_day_work)) {
            $half_day_work = $request->half_day_work;
            $this->validate($request, [
                'half_day_work' => 'required',
            ]);
        } else {
            $half_day_work = 0;
        }

        $half_day_office_time = 0;
        if (!empty($request->half_day_office_time)) {
            $half_day_office_time = $request->half_day_office_time;
            $this->validate($request, [
                'half_day_office_time' => 'required',
            ]);
        } else {
            $half_day_work = 0;
        }

        $entry_buffer_time = 0;
        if (!empty($request->entry_buffer_time)) {
            $this->validate($request, [
                'entry_buffer_time' => 'required',
            ]);
            $entry_buffer_time = $request->entry_buffer_time;
        } else {
            $entry_buffer_time = 0;
        }

        $is_half_day_applicable = 0;
        $half_day_work = 0;
        $half_day_office_end_time = 0;
        $half_day_totla_working_hour = 0;
        if (!empty($request->is_half_day_applicable)) {
            $this->validate($request, [
                'half_day_work' => 'required',
                'half_day_office_end_time' => 'required',
                'half_day_totla_working_hour' => 'required',
            ]);
            $is_half_day_applicable = $request->is_half_day_applicable;
            $half_day_work = $request->half_day_work;
            $half_day_office_end_time = $request->half_day_office_end_time;
            $half_day_totla_working_hour = $request->half_day_totla_working_hour;
        } else {
            $is_half_day_applicable = 0;
            $half_day_work = 0;
            $half_day_office_end_time = 0;
            $half_day_totla_working_hour = 0;
        }




        $is_ot_applicable = 0;
        $is_active_ot_buffer_time = 0;
        $ot_buffer_min = 0;
        $is_max_ot_applicable = 0;
        $max_ot_hour = 0;
        if (!empty($request->is_ot_applicable)) {
            $is_ot_applicable = $request->is_ot_applicable;
            if (!empty($request->is_active_ot_buffer_time)) {
                $is_active_ot_buffer_time = $request->is_active_ot_buffer_time;
                $this->validate($request, [
                    'ot_buffer_min' => 'required',
                ]);
                $ot_buffer_min = $request->ot_buffer_min;
            } else {
                $is_active_ot_buffer_time = 0;
                $ot_buffer_min = 0;
            }

            if (!empty($request->is_max_ot_applicable)) {
                $is_max_ot_applicable = $request->is_max_ot_applicable;
                $this->validate($request, [
                    'max_ot_hour' => 'required',
                ]);
                $max_ot_hour = $request->max_ot_hour;
            } else {
                $is_max_ot_applicable = 0;
                $max_ot_hour = 0;
            }
        } else {
            $is_ot_applicable = 0;
            $is_active_ot_buffer_time = 0;
            $ot_buffer_min = 0;
            $is_max_ot_applicable = 0;
            $max_ot_hour = 0;
        }

        $is_active = 0;
        if (!empty($request->is_active)) {
            $is_active = $request->is_active;
        } else {
            $is_active = 0;
        }

        $attPolicy = new AttendancePolicy();
        $attPolicy->company_id = $request->company_id;
        $attPolicy->policy_title = $request->title;
        $attPolicy->office_start_time = $request->start_time;
        $attPolicy->office_end_time = $request->end_time;
        $attPolicy->entry_buffer_time = $entry_buffer_time;
        $attPolicy->total_hours = $request->total_hours;

        $attPolicy->is_halfday_applicable = $is_half_day_applicable;
        $attPolicy->half_day_name = $half_day_work;
        $attPolicy->half_day_office_end_time = $half_day_office_end_time;
        $attPolicy->half_day_total_working_hour = $half_day_totla_working_hour;


        $attPolicy->is_ot_applicable = $is_ot_applicable;
        $attPolicy->is_ot_buffer_time = $is_active_ot_buffer_time;
        $attPolicy->ot_buffer_time = $ot_buffer_min;
        $attPolicy->is_ot_max_active = $is_max_ot_applicable;
        $attPolicy->max_ot_hour = $max_ot_hour;

        $attPolicy->is_active = $is_active;
        $attPolicy->save();
        return redirect()->action('AttendancePolicyController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AttendancePolicy  $attendancePolicy
     * @return \Illuminate\Http\Response
     */
    public function show(AttendancePolicy $attendancePolicy) {
//        $data = DB::table('attendance_policies')
//                ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
//                ->select(DB::raw('
//                    companies.name as company,
//                    attendance_policies.*
//                    '))
//                ->orderBy('attendance_policies.id', 'DESC')
//                ->get();
        //print_r($data);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (empty($logged_emp_company_id)) {
            $logged_emp_company_id = 0;
        }

        if ($logged_emp_company_id > 0) {

            $data = DB::table('attendance_policies')
                    ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
                    ->select(DB::raw('
                    companies.name as company,
                    attendance_policies.*
                    '))
                    ->where('attendance_policies.company_id', $logged_emp_company_id)
                    ->orderBy('attendance_policies.id', 'DESC')
                    ->get();
        } else {

            $data = DB::table('attendance_policies')
                    ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
                    ->select(DB::raw('
                    companies.name as company,
                    attendance_policies.*
                    '))
                    ->orderBy('attendance_policies.id', 'DESC')
                    ->get();
        }

        return response()->json(array("data" => $data, "total" => count($data)));
    }

    public function showList() {

        return view('module.settings.attendancePolicyList', []);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AttendancePolicy  $attendancePolicy
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendancePolicy $attendancePolicy, $id) {
        $data = AttendancePolicy::find($id);
        $company = Company::all();
        $calendar = CalenderWeekDay::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.attendancePolicy', ['data' => $data, 'company' => $company, 'calendar' => $calendar, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AttendancePolicy  $attendancePolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendancePolicy $attendancePolicy, $id) {

        $this->validate($request, [
            'company_id' => 'required',
            'title' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'total_hours' => 'required',
        ]);

        $half_day_work = 0;
        if (!empty($request->half_day_work)) {
            $half_day_work = $request->half_day_work;
            $this->validate($request, [
                'half_day_work' => 'required',
            ]);
        } else {
            $half_day_work = 0;
        }

        $half_day_office_time = 0;
        if (!empty($request->half_day_office_time)) {
            $half_day_office_time = $request->half_day_office_time;
            $this->validate($request, [
                'half_day_office_time' => 'required',
            ]);
        } else {
            $half_day_work = 0;
        }

        $entry_buffer_time = 0;
        if (!empty($request->entry_buffer_time)) {
            $this->validate($request, [
                'entry_buffer_time' => 'required',
            ]);
            $entry_buffer_time = $request->entry_buffer_time;
        } else {
            $entry_buffer_time = 0;
        }

        $is_half_day_applicable = 0;
        $half_day_work = 0;
        $half_day_office_end_time = 0;
        $half_day_totla_working_hour = 0;
        if (!empty($request->is_half_day_applicable)) {
            $this->validate($request, [
                'half_day_work' => 'required',
                'half_day_office_end_time' => 'required',
                'half_day_totla_working_hour' => 'required',
            ]);
            $is_half_day_applicable = $request->is_half_day_applicable;
            $half_day_work = $request->half_day_work;
            $half_day_office_end_time = $request->half_day_office_end_time;
            $half_day_totla_working_hour = $request->half_day_totla_working_hour;
        } else {
            $is_half_day_applicable = 0;
            $half_day_work = 0;
            $half_day_office_end_time = 0;
            $half_day_totla_working_hour = 0;
        }




        $is_ot_applicable = 0;
        $is_active_ot_buffer_time = 0;
        $ot_buffer_min = 0;
        $is_max_ot_applicable = 0;
        $max_ot_hour = 0;
        if (!empty($request->is_ot_applicable)) {
            $is_ot_applicable = $request->is_ot_applicable;
            if (!empty($request->is_active_ot_buffer_time)) {
                $is_active_ot_buffer_time = $request->is_active_ot_buffer_time;
                $this->validate($request, [
                    'ot_buffer_min' => 'required',
                ]);
                $ot_buffer_min = $request->ot_buffer_min;
            } else {
                $is_active_ot_buffer_time = 0;
                $ot_buffer_min = 0;
            }

            if (!empty($request->is_max_ot_applicable)) {
                $is_max_ot_applicable = $request->is_max_ot_applicable;
                $this->validate($request, [
                    'max_ot_hour' => 'required',
                ]);
                $max_ot_hour = $request->max_ot_hour;
            } else {
                $is_max_ot_applicable = 0;
                $max_ot_hour = 0;
            }
        } else {
            $is_ot_applicable = 0;
            $is_active_ot_buffer_time = 0;
            $ot_buffer_min = 0;
            $is_max_ot_applicable = 0;
            $max_ot_hour = 0;
        }

        $is_active = 0;
        if (!empty($request->is_active)) {
            $is_active = $request->is_active;
        } else {
            $is_active = 0;
        }

        $attPolicy = AttendancePolicy::find($id);
        $attPolicy->company_id = $request->company_id;
        $attPolicy->policy_title = $request->title;
        $attPolicy->office_start_time = $request->start_time;
        $attPolicy->office_end_time = $request->end_time;
        $attPolicy->entry_buffer_time = $entry_buffer_time;
        $attPolicy->total_hours = $request->total_hours;

        $attPolicy->is_halfday_applicable = $is_half_day_applicable;
        $attPolicy->half_day_name = $half_day_work;
        $attPolicy->half_day_office_end_time = $half_day_office_end_time;
        $attPolicy->half_day_total_working_hour = $half_day_totla_working_hour;


        $attPolicy->is_ot_applicable = $is_ot_applicable;
        $attPolicy->is_ot_buffer_time = $is_active_ot_buffer_time;
        $attPolicy->ot_buffer_time = $ot_buffer_min;
        $attPolicy->is_ot_max_active = $is_max_ot_applicable;
        $attPolicy->max_ot_hour = $max_ot_hour;

        $attPolicy->is_active = $is_active;
        $attPolicy->save();

        return redirect()->action('AttendancePolicyController@index')->with('success', 'Information Update Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AttendancePolicy  $attendancePolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendancePolicy $attendancePolicy) {
        //
    }

    public function exportExcel() {
//        $data = DB::table('attendance_policies')
//                ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
//                ->select(DB::raw('
//                    attendance_policies.policy_title,
//                    companies.name as company,
//                    attendance_policies.office_start_time,
//                    attendance_policies.office_end_time,
//                    attendance_policies.entry_buffer_time,
//                    attendance_policies.total_hours,
//                    attendance_policies.is_halfday_applicable,
//                    attendance_policies.half_day_name,
//                    attendance_policies.half_day_office_end_time,
//                    attendance_policies.half_day_total_working_hour,
//                    attendance_policies.is_ot_applicable,
//                    attendance_policies.is_ot_buffer_time,
//                    attendance_policies.ot_buffer_time,
//                    attendance_policies.is_ot_max_active,
//                    attendance_policies.max_ot_hour,
//                    attendance_policies.is_active,
//                    attendance_policies.created_at,
//                    attendance_policies.updated_at
//                    
//                    '))
//                ->orderBy('attendance_policies.id', 'DESC')
//                ->get();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (empty($logged_emp_company_id)) {
            $logged_emp_company_id = 0;
        }

        if ($logged_emp_company_id > 0) {
            $data = DB::table('attendance_policies')
                    ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
                    ->select(DB::raw('
                    attendance_policies.policy_title,
                    companies.name as company,
                    attendance_policies.office_start_time,
                    attendance_policies.office_end_time,
                    attendance_policies.entry_buffer_time,
                    attendance_policies.total_hours,
                    attendance_policies.is_halfday_applicable,
                    attendance_policies.half_day_name,
                    attendance_policies.half_day_office_end_time,
                    attendance_policies.half_day_total_working_hour,
                    attendance_policies.is_ot_applicable,
                    attendance_policies.is_ot_buffer_time,
                    attendance_policies.ot_buffer_time,
                    attendance_policies.is_ot_max_active,
                    attendance_policies.max_ot_hour,
                    attendance_policies.is_active,
                    attendance_policies.created_at,
                    attendance_policies.updated_at
                    
                    '))
                    ->where('attendance_policies.company_id', $logged_emp_company_id)
                    ->orderBy('attendance_policies.id', 'DESC')
                    ->get();
        } else {

            $data = DB::table('attendance_policies')
                    ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
                    ->select(DB::raw('
                    attendance_policies.policy_title,
                    companies.name as company,
                    attendance_policies.office_start_time,
                    attendance_policies.office_end_time,
                    attendance_policies.entry_buffer_time,
                    attendance_policies.total_hours,
                    attendance_policies.is_halfday_applicable,
                    attendance_policies.half_day_name,
                    attendance_policies.half_day_office_end_time,
                    attendance_policies.half_day_total_working_hour,
                    attendance_policies.is_ot_applicable,
                    attendance_policies.is_ot_buffer_time,
                    attendance_policies.ot_buffer_time,
                    attendance_policies.is_ot_max_active,
                    attendance_policies.max_ot_hour,
                    attendance_policies.is_active,
                    attendance_policies.created_at,
                    attendance_policies.updated_at
                    
                    '))
                    ->orderBy('attendance_policies.id', 'DESC')
                    ->get();
        }
        // print_r($data);
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [

            'Policy Title',
            'Company',
            'Office Start Time',
            'Office End Time',
            'Entry buffer time',
            'Total Hours',
            'IS Half Day Applicable',
            'Half Day Name',
            'Half Day Office End Time',
            'Half Day Office Total Working Hours',
            'Is OT Applicable',
            'Is OT Buffer Time',
            'OT Buffer Time',
            'Is OT Max Active',
            'Max OT Hours',
            'Is Active',
            'Created At',
            'Updated At'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($data as $key => $field) {
            $excelArray[] = get_object_vars($field);
        }

        // Generate and return the spreadsheet
        \Excel::create('Attendance Policy_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Employee Info');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('EmployeeInfo');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf() {

        $content = '<h3>Attendance Policy</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = [
            'policy_title',
            'company',
            'office_start_time',
            'office_end_time',
            'entry_buffer_time',
            'total_hours',
            'is_halfday_applicable',
            'half_day_name',
            'half_day_office_end_time',
            'is_ot_applicable',
            'is_ot_buffer_time',
            'is_ot_max_active',
        ];

        if (!empty($excelArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
//            print_r($excelArray);
//            exit();
            foreach ($excelArray as $exhead):
                $content .='<th  style="width:90px !important;font_size:10px !important">' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';

            $rows = count($excelArray);
//            $data = DB::table('attendance_policies')
//                    ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
//                    ->select(DB::raw('
//                    attendance_policies.policy_title,
//                    companies.name as company,
//                    attendance_policies.office_start_time,
//                    attendance_policies.office_end_time,
//                    attendance_policies.entry_buffer_time,
//                    attendance_policies.total_hours,
//                    attendance_policies.is_halfday_applicable,
//                    attendance_policies.half_day_name,
//                    attendance_policies.half_day_office_end_time,
//                   
//                    attendance_policies.is_ot_applicable,
//                    attendance_policies.is_ot_buffer_time,
//                   
//                    attendance_policies.is_ot_max_active,
//                   
//                    
//                    attendance_policies.created_at,
//                    attendance_policies.updated_at
//                    
//                    '))
//                    ->orderBy('attendance_policies.id', 'DESC')
//                    ->get();
            $logged_emp_company_id = MenuPageController::loggedUser('company_id');

            if (empty($logged_emp_company_id)) {
                $logged_emp_company_id = 0;
            }

            if ($logged_emp_company_id > 0) {
                $data = DB::table('attendance_policies')
                        ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
                        ->select(DB::raw('
                    attendance_policies.policy_title,
                    companies.name as company,
                    attendance_policies.office_start_time,
                    attendance_policies.office_end_time,
                    attendance_policies.entry_buffer_time,
                    attendance_policies.total_hours,
                    attendance_policies.is_halfday_applicable,
                    attendance_policies.half_day_name,
                    attendance_policies.half_day_office_end_time,
                   
                    attendance_policies.is_ot_applicable,
                    attendance_policies.is_ot_buffer_time,
                   
                    attendance_policies.is_ot_max_active,
                   
                    
                    attendance_policies.created_at,
                    attendance_policies.updated_at
                    
                    '))
                        ->where('attendance_policies.company_id', $logged_emp_company_id)
                        ->orderBy('attendance_policies.id', 'DESC')
                        ->get();
            } else {

                $data = DB::table('attendance_policies')
                        ->leftjoin('companies', 'companies.id', '=', 'attendance_policies.company_id')
                        ->select(DB::raw('
                    attendance_policies.policy_title,
                    companies.name as company,
                    attendance_policies.office_start_time,
                    attendance_policies.office_end_time,
                    attendance_policies.entry_buffer_time,
                    attendance_policies.total_hours,
                    attendance_policies.is_halfday_applicable,
                    attendance_policies.half_day_name,
                    attendance_policies.half_day_office_end_time,
                   
                    attendance_policies.is_ot_applicable,
                    attendance_policies.is_ot_buffer_time,
                   
                    attendance_policies.is_ot_max_active,
                   
                    
                    attendance_policies.created_at,
                    attendance_policies.updated_at
                    
                    '))
                        ->orderBy('attendance_policies.id', 'DESC')
                        ->get();
            }

            if (!empty($data)) {
                $content .='<tbody>';
                foreach ($data as $draw):
                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td style="width:90px !important;font_size:10px !important">' . $draw->$fid . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';

            $content .='<br />';

            $content .='<h4>Total : ' . count($data) . '</h4>';


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
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }

}
