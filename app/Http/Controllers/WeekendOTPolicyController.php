<?php

namespace App\Http\Controllers;

use App\WeekendOTPolicy;
use App\Company;
use Illuminate\Http\Request;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

class WeekendOTPolicyController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $data = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.weekendotpolicy', ['company' => $data, 'logged_emp_com' => $logged_emp_company_id]);
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


        $is_ot_will_start_after_fix_hour = $request->is_ot_will_start_after_fix_hour ? $request->is_ot_will_start_after_fix_hour : '0';
		$is_standard_max_ot_hour = $request->is_standard_max_ot_hour ? $request->is_standard_max_ot_hour : '0';
        $is_ot_count_as_total_working_hour = 0;
        $is_ot_will_start_after_fix_hours = 0;
        if ($is_ot_will_start_after_fix_hour == 1) {
            $is_ot_count_as_total_working_hour = 1;
        } else {
            $this->validate($request, ['hour_after' => 'required']);
            $is_ot_will_start_after_fix_hours = 1;
        }
		
		if(!empty($is_standard_max_ot_hour))
		{
			$this->validate($request, ['standard_max_ot_hour' => 'required']);
		}
		

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (!empty($logged_emp_company_id)) {
            $company_id = $logged_emp_company_id;
        } else {
            $company_id = $request->company_id;
        }

        $hour_after = $request->hour_after ? $request->hour_after : '00:00:00';

        $countOTPolicy = WeekendOTPolicy::where('company_id', $company_id)->count();


        if ($countOTPolicy == 0) {
            $tab = new WeekendOTPolicy;
            $tab->company_id = $company_id;
            $tab->is_ot_count_as_total_working_hour = $is_ot_count_as_total_working_hour;
            $tab->is_ot_will_start_after_fix_hour = $is_ot_will_start_after_fix_hours;
            $tab->hour_after = $hour_after;
            $tab->is_standard_max_ot_hour = $is_standard_max_ot_hour;
            $tab->standard_max_ot_hour = $request->standard_max_ot_hour;
            $tab->save();
            
            return redirect()->action('WeekendOTPolicyController@index')->with('success', 'Information Added Successfully');
        } else {
            $sqlOTPolicy = WeekendOTPolicy::where('company_id', $company_id)->first();
            $tabID=$sqlOTPolicy->id;
            $tab = WeekendOTPolicy::find($tabID);
            $tab->is_ot_count_as_total_working_hour = $is_ot_count_as_total_working_hour;
            $tab->is_ot_will_start_after_fix_hour = $is_ot_will_start_after_fix_hours;
            $tab->hour_after = $hour_after;
            $tab->save();
            
            return redirect()->action('WeekendOTPolicyController@index')->with('success', 'Information Updated Successfully');
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
        /*if (!empty($logged_emp_company_id)) {
            $json = DB::table('weekend_o_t_policies')
                    ->join('companies', 'companies.id', '=', 'weekend_o_t_policies.company_id')
                    ->select(DB::raw(' weekend_o_t_policies.*,
                         companies.name as company_id'))
                    ->where('weekend_o_t_policies.company_id', $logged_emp_company_id)
                    ->get();
        } else {*/
            $json = DB::table('weekend_o_t_policies')
                    ->join('companies', 'companies.id', '=', 'weekend_o_t_policies.company_id')
                    ->select(DB::raw('weekend_o_t_policies.*,
                        companies.name as company_id'))
                    ->get();
        //}

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
        $dbfields = WeekendOTPolicy::all();

        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray[] = \DB::getSchemaBuilder()->getColumnListing("weekend_o_t_policies");

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $field) {
            $excelArray[] = $field->toArray();
        }

        // Generate and return the spreadsheet
        \Excel::create('WeekendOTPolicyController' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Weekend OT Policy Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Weekend OT Policy Report');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf() {

        $content = '<h3>Weekend OT Policy Report</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = \DB::getSchemaBuilder()->getColumnListing("weekend_o_t_policies");
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
            $datarows = WeekendOTPolicy::all();
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
        $data = WeekendOTPolicy::find($id);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.weekendotpolicy', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {


        $is_ot_will_start_after_fix_hour = $request->is_ot_will_start_after_fix_hour ? $request->is_ot_will_start_after_fix_hour : '0';
		$is_standard_max_ot_hour = $request->is_standard_max_ot_hour ? $request->is_standard_max_ot_hour : '0';
        $is_ot_count_as_total_working_hour = 0;
        $is_ot_will_start_after_fix_hours = 0;
        if ($is_ot_will_start_after_fix_hour == 1) {

            $is_ot_count_as_total_working_hour = 1;
            $hour_after = '00:00:00';
        } else {
            $this->validate($request, ['hour_after' => 'required']);
            $is_ot_will_start_after_fix_hours = 1;
            $hour_after = $request->hour_after ? $request->hour_after : '00:00:00';
        }
		
		if(!empty($is_standard_max_ot_hour))
		{
			$this->validate($request, ['standard_max_ot_hour' => 'required']);
		}

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (!empty($logged_emp_company_id)) {
            $company_id = $logged_emp_company_id;
        } else {
            $company_id = $request->company_id;
        }



        $tab = WeekendOTPolicy::find($id);
        $tab->company_id = $company_id;
        $tab->is_ot_count_as_total_working_hour = $is_ot_count_as_total_working_hour;
        $tab->is_ot_will_start_after_fix_hour = $is_ot_will_start_after_fix_hours;
        $tab->hour_after = $hour_after;
		$tab->is_standard_max_ot_hour = $is_standard_max_ot_hour;
        $tab->standard_max_ot_hour = $request->standard_max_ot_hour;
        $tab->save();

        return redirect()->action('WeekendOTPolicyController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = WeekendOTPolicy::destroy($request->id);
        return 1;
    }

}
