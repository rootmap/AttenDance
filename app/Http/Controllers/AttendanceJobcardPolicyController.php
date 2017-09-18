<?php

namespace App\Http\Controllers;

use App\AttendanceJobcardPolicy;
use App\Company;
use Illuminate\Http\Request;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

class AttendanceJobcardPolicyController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $data = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');


        return view('module.settings.jobcardPolicy', ['company' => $data, 'logged_emp_com' => $logged_emp_company_id]);
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
        $this->validate($request, ['company_id' => 'required']);

        $is_admin_data_show_policy = $request->is_admin_data_show_policy ? $request->is_admin_data_show_policy : '0';
        $is_admin_max_ot_fixed = $request->is_admin_max_ot_fixed ? $request->is_admin_max_ot_fixed : '0';

        if (!empty($is_admin_data_show_policy)) {
            $this->validate($request, [
                'admin_addition_deduction' => 'required',
                'admin_with_intime' => 'required',
                'admin_with_outime' => 'required'
            ]);
        }

        if (!empty($is_admin_max_ot_fixed)) {
            $this->validate($request, ['admin_max_ot_hour' => 'required']);
        }

        $is_user_data_show_policy = $request->is_user_data_show_policy ? $request->is_user_data_show_policy : '0';
        $is_user_max_ot_fixed = $request->is_user_max_ot_fixed ? $request->is_user_max_ot_fixed : '0';
        if (!empty($is_user_data_show_policy)) {
            $this->validate($request, [
                'user_addition_deduction' => 'required',
                'user_with_intime' => 'required',
                'user_with_outime' => 'required'
            ]);
        }
        if (!empty($is_user_max_ot_fixed)) {
            $this->validate($request, ['user_max_ot_hour' => 'required']);
        }


        $is_audit_data_show_policy = $request->is_audit_data_show_policy ? $request->is_audit_data_show_policy : '0';
        $is_audit_max_ot_fixed = $request->is_audit_max_ot_fixed ? $request->is_audit_max_ot_fixed : '0';


        if (!empty($is_audit_data_show_policy)) {
            $this->validate($request, [
                'audit_addition_deduction' => 'required',
                'audit_with_intime' => 'required',
                'audit_with_outime' => 'required'
            ]);
        }
        if (!empty($is_audit_max_ot_fixed)) {
            $this->validate($request, ['audit_max_ot_hour' => 'required']);
        }


        $admin_max_ot_hour = $request->admin_max_ot_hour ? $request->admin_max_ot_hour : '00:00:00';
        $user_max_ot_hour = $request->user_max_ot_hour ? $request->user_max_ot_hour : '00:00:00';
        $audit_max_ot_hour = $request->audit_max_ot_hour ? $request->audit_max_ot_hour : '00:00:00';


        $admin_addition_deduction = $request->admin_addition_deduction ? $request->admin_addition_deduction : '+';
        $admin_with_intime = $request->admin_with_intime ? $request->admin_with_intime : '00:00:00';
        $admin_with_outime = $request->admin_with_outime ? $request->admin_with_outime : '00:00:00';

        $user_addition_deduction = $request->user_addition_deduction ? $request->user_addition_deduction : '+';
        $user_with_intime = $request->user_with_intime ? $request->user_with_intime : '00:00:00';
        $user_with_outime = $request->user_with_outime ? $request->user_with_outime : '00:00:00';

        $audit_addition_deduction = $request->audit_addition_deduction ? $request->audit_addition_deduction : '+';
        $audit_with_intime = $request->audit_with_intime ? $request->audit_with_intime : '00:00:00';
        $audit_with_outime = $request->audit_with_outime ? $request->audit_with_outime : '00:00:00';

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (!empty($logged_emp_company_id)) {
            $company_id = $logged_emp_company_id;
        } else {
            $company_id = $request->company_id;
        }

        $chkDuplicate = AttendanceJobcardPolicy::where('company_id', $company_id)->first();
        
        if (empty(count($chkDuplicate))) {
            $tab = new AttendanceJobcardPolicy;
            $tab->company_id = $request->company_id;
            $tab->is_admin_data_show_policy = $is_admin_data_show_policy;
            $tab->admin_addition_deduction = $admin_addition_deduction;
            $tab->admin_with_intime = $admin_with_intime;
            $tab->admin_with_outime = $admin_with_outime;
            $tab->is_admin_max_ot_fixed = $is_admin_max_ot_fixed;
            $tab->admin_max_ot_hour = $admin_max_ot_hour;
            $tab->is_user_data_show_policy = $is_user_data_show_policy;
            $tab->user_addition_deduction = $user_addition_deduction;
            $tab->user_with_intime = $user_with_intime;
            $tab->user_with_outime = $user_with_outime;
            $tab->is_user_max_ot_fixed = $is_user_max_ot_fixed;
            $tab->user_max_ot_hour = $user_max_ot_hour;
            $tab->is_audit_data_show_policy = $is_audit_data_show_policy;
            $tab->audit_addition_deduction = $audit_addition_deduction;
            $tab->audit_with_intime = $audit_with_intime;
            $tab->audit_with_outime = $audit_with_outime;
            $tab->is_audit_max_ot_fixed = $is_audit_max_ot_fixed;
            $tab->audit_max_ot_hour = $audit_max_ot_hour;
            $tab->save();

            return redirect()->action('AttendanceJobcardPolicyController@index')->with('success', 'Information Added Successfully');
        } else {
            $tab =AttendanceJobcardPolicy::find($chkDuplicate->id);
            $tab->company_id = $request->company_id;
            $tab->is_admin_data_show_policy = $is_admin_data_show_policy;
            $tab->admin_addition_deduction = $admin_addition_deduction;
            $tab->admin_with_intime = $admin_with_intime;
            $tab->admin_with_outime = $admin_with_outime;
            $tab->is_admin_max_ot_fixed = $is_admin_max_ot_fixed;
            $tab->admin_max_ot_hour = $admin_max_ot_hour;
            $tab->is_user_data_show_policy = $is_user_data_show_policy;
            $tab->user_addition_deduction = $user_addition_deduction;
            $tab->user_with_intime = $user_with_intime;
            $tab->user_with_outime = $user_with_outime;
            $tab->is_user_max_ot_fixed = $is_user_max_ot_fixed;
            $tab->user_max_ot_hour = $user_max_ot_hour;
            $tab->is_audit_data_show_policy = $is_audit_data_show_policy;
            $tab->audit_addition_deduction = $audit_addition_deduction;
            $tab->audit_with_intime = $audit_with_intime;
            $tab->audit_with_outime = $audit_with_outime;
            $tab->is_audit_max_ot_fixed = $is_audit_max_ot_fixed;
            $tab->audit_max_ot_hour = $audit_max_ot_hour;
            $tab->save();

            return redirect()->action('AttendanceJobcardPolicyController@index')->with('success', 'Information Update Successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function show() {
        // $json = AttendanceJobcardPolicy::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $json = DB::table('attendance_jobcard_policies')
                    ->join('companies', 'companies.id', '=', 'attendance_jobcard_policies.company_id')
                    ->select(DB::raw('attendance_jobcard_policies.*,
                         companies.name as company_id'))
                    ->where('attendance_jobcard_policies.company_id', $logged_emp_company_id)
                    ->get();
        } else {
            $json = DB::table('attendance_jobcard_policies')
                    ->join('companies', 'companies.id', '=', 'attendance_jobcard_policies.company_id')
                    ->select(DB::raw('attendance_jobcard_policies.*,
                        companies.name as company_id'))
                    ->get();
        }

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    //eloquent example 

    /*
      $payments = Payment::join('users', 'users.id', '=', 'payments.id')
      ->select(
      'payments.id',
      \DB::raw("concat(users.first_name, ' ', users.last_name) as `name`"),
      'users.email',
      'payments.total',
      'payments.created_at')
      ->get();


     */


    public function exportExcel() {
        $dbfields = AttendanceJobcardPolicy::all();

        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray[] = \DB::getSchemaBuilder()->getColumnListing("countries");

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $field) {
            $excelArray[] = $field->toArray();
        }

        // Generate and return the spreadsheet
        \Excel::create('AttendanceJobcardPolicyController' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Jobcardpolicy');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Habijabi');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf() {

        $content = '<h3>Job Card Policy List</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = \DB::getSchemaBuilder()->getColumnListing("countries");
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
            $datarows = AttendanceJobcardPolicy::all();
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
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $company = Company::all();
        $data = AttendanceJobcardPolicy::find($id);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.jobcardPolicy', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, ['company_id' => 'required']);

        $is_admin_data_show_policy = $request->is_admin_data_show_policy ? $request->is_admin_data_show_policy : '0';
        $is_admin_max_ot_fixed = $request->is_admin_max_ot_fixed ? $request->is_admin_max_ot_fixed : '0';

        if (!empty($is_admin_max_ot_fixed)) {
            $this->validate($request, [
                'admin_max_ot_hour' => 'required',
            ]);
        }

        $is_admin_ot_adjust_with_outtime = $request->is_admin_ot_adjust_with_outtime ? $request->is_admin_ot_adjust_with_outtime : '0';

        $is_user_data_show_policy = $request->is_user_data_show_policy ? $request->is_user_data_show_policy : '0';
        $is_user_max_ot_fixed = $request->is_user_max_ot_fixed ? $request->is_user_max_ot_fixed : '0';

        if (!empty($is_user_max_ot_fixed)) {
            $this->validate($request, ['user_max_ot_hour' => 'required']);
        }

        $is_user_ot_adjust_with_outtime = $request->is_user_ot_adjust_with_outtime ? $request->is_user_ot_adjust_with_outtime : '0';


        $is_audit_data_show_policy = $request->is_audit_data_show_policy ? $request->is_audit_data_show_policy : '0';
        $is_audit_max_ot_fixed = $request->is_audit_max_ot_fixed ? $request->is_audit_max_ot_fixed : '0';

        if (!empty($is_audit_max_ot_fixed)) {
            $this->validate($request, ['audit_max_ot_hour' => 'required']);
        }

        $is_audit_ot_adjust_with_outtime = $request->is_audit_ot_adjust_with_outtime ? $request->is_audit_ot_adjust_with_outtime : '0';



        $tab = AttendanceJobcardPolicy::find($id);
        $tab->company_id = $request->company_id;


        $tab->is_admin_data_show_policy = $is_admin_data_show_policy;
        $tab->admin_addition_deduction = $request->admin_addition_deduction;
        $tab->admin_with_intime = $request->admin_with_intime;
        $tab->admin_with_outime = $request->admin_with_outime;
        $tab->is_admin_max_ot_fixed = $is_admin_max_ot_fixed;
        $tab->admin_max_ot_hour = $request->admin_max_ot_hour;
        $tab->is_admin_ot_adjust_with_outtime = $is_admin_ot_adjust_with_outtime;

        $tab->is_user_data_show_policy = $is_user_data_show_policy;
        $tab->user_addition_deduction = $request->user_addition_deduction;
        $tab->user_with_intime = $request->user_with_intime;
        $tab->user_with_outime = $request->user_with_outime;
        $tab->is_user_max_ot_fixed = $is_user_max_ot_fixed;
        $tab->user_max_ot_hour = $request->user_max_ot_hour;
        $tab->is_user_ot_adjust_with_outtime = $is_user_ot_adjust_with_outtime;

        $tab->is_audit_data_show_policy = $is_audit_data_show_policy;
        $tab->audit_addition_deduction = $request->audit_addition_deduction;
        $tab->audit_with_intime = $request->audit_with_intime;
        $tab->audit_with_outime = $request->audit_with_outime;
        $tab->is_audit_max_ot_fixed = $is_audit_max_ot_fixed;
        $tab->audit_max_ot_hour = $request->audit_max_ot_hour;
        $tab->is_audit_ot_adjust_with_outtime = $is_audit_ot_adjust_with_outtime;
        $tab->save();

        return redirect()->action('AttendanceJobcardPolicyController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = AttendanceJobcardPolicy::destroy($request->id);
        return 1;
    }

}
