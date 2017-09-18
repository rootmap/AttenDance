<?php

namespace App\Http\Controllers;

use App\LeavePolicy;
use Illuminate\Http\Request;
use App\Company;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

class LeavePolicyController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $company = Company::all();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.leavePolicy', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
            'leave_title' => 'required',
            'leave_short_code' => 'required',
            'total_days' => 'required'
        ]);

        if (empty($request->is_applicable_for_all)) {
            $is_applicable_for_all = 0;
        } else {
            $is_applicable_for_all = $request->is_applicable_for_all;
        }

        if (empty($request->is_leave_cut_applicable)) {
            $is_leave_cut_applicable = 0;
        } else {
            $is_leave_cut_applicable = $request->is_leave_cut_applicable;
        }

        if (empty($request->is_carry_forward)) {
            $is_carry_forward = 0;
        } else {
            $is_carry_forward = $request->is_carry_forward;
            if (!empty($is_carry_forward)) {
                $this->validate($request, [
                    'max_carry_forward_days' => 'required',
                ]);
            }
        }

        if (empty($request->max_carry_forward_days)) {
            $max_carry_forward_days = 0;
        } else {
            $max_carry_forward_days = $request->max_carry_forward_days;
        }


        if (empty($request->is_document_upload)) {
            $is_document_upload = 0;
        } else {
            $is_document_upload = $request->is_document_upload;
            if (!empty($is_document_upload)) {
                $this->validate($request, [
                    'document_upload_after_days' => 'required',
                ]);
            }
        }

        if (empty($request->document_upload_after_days)) {
            $document_upload_after_days = 0;
        } else {
            $document_upload_after_days = $request->document_upload_after_days;
        }

        if (empty($request->is_holiday_deduct)) {
            $is_holiday_deduct = 0;
        } else {
            $is_holiday_deduct = $request->is_holiday_deduct;
        }

        $tab = new LeavePolicy;
        $tab->company_id = $request->company_id;
        $tab->leave_title = $request->leave_title;
        $tab->leave_short_code = $request->leave_short_code;
        $tab->total_days = $request->total_days;
        $tab->is_applicable_for_all = $is_applicable_for_all;
        $tab->is_leave_cut_applicable = $is_leave_cut_applicable;
        $tab->is_carry_forward = $is_carry_forward;
        $tab->max_carry_forward_days = $max_carry_forward_days;
        $tab->is_document_upload = $is_document_upload;
        $tab->document_upload_after_days = $document_upload_after_days;
        $tab->is_holiday_deduct = $is_holiday_deduct;

        $tab->save();

        return redirect()->action('LeavePolicyController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function show(LeavePolicy $leavePolicy) {
//        $json = LeavePolicy::all();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $json = DB::table('leave_policies')

                    ->select(DB::raw('leave_policies.*'))
                    ->where('leave_policies.company_id', $logged_emp_company_id)
                    ->groupBy('leave_policies.id')
                    ->get();
        } else {
            $json = DB::table('leave_policies')
                    ->select(DB::raw('leave_policies.*'))
                    ->groupBy('leave_policies.id')
                    ->get();
        }
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /* Added For Getting Leave Policy List From Outside */

    public function filterLeavePolicy(Request $request) {
        $company_id = $request->company_id;
        $data = LeavePolicy::where('company_id', $company_id)
                ->get();
        return response()->json($data);
    }

    /* For exporting into excel and pdf */

    public function exportExcel() {
        $dbfields = LeavePolicy::all();

        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray[] = \DB::getSchemaBuilder()->getColumnListing("leave_policies");

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $field) {
            $excelArray[] = $field->toArray();
        }

        // Generate and return the spreadsheet
        \Excel::create('LeavePolicyData_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('LeavePolicy');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('LeavePolicy');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf() {

        $content = '<h3>Leave Policy List</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = \DB::getSchemaBuilder()->getColumnListing("leave_policies");
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
            $datarows = LeavePolicy::all();
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function edit(LeavePolicy $leavePolicy, $id) {
        $company = Company::all();
        $data = LeavePolicy::find($id);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.leavePolicy', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeavePolicy $leavePolicy, $id) {
        $this->validate($request, [
            'company_id' => 'required',
            'leave_title' => 'required',
            'leave_short_code' => 'required',
            'total_days' => 'required'
        ]);

        if (empty($request->is_applicable_for_all)) {
            $is_applicable_for_all = 0;
        } else {
            $is_applicable_for_all = $request->is_applicable_for_all;
        }

        if (empty($request->is_leave_cut_applicable)) {
            $is_leave_cut_applicable = 0;
        } else {
            $is_leave_cut_applicable = $request->is_leave_cut_applicable;
        }

        if (empty($request->is_carry_forward)) {
            $is_carry_forward = 0;
        } else {
            $is_carry_forward = $request->is_carry_forward;
            if (!empty($is_carry_forward)) {
                $this->validate($request, [
                    'max_carry_forward_days' => 'required',
                ]);
            }
        }

        if (empty($request->is_document_upload)) {
            $is_document_upload = 0;
        } else {
            $is_document_upload = $request->is_document_upload;
            if (!empty($is_document_upload)) {
                $this->validate($request, [
                    'document_upload_after_days' => 'required',
                ]);
            }
        }

        if (empty($request->document_upload_after_days)) {
            $document_upload_after_days = 0;
        } else {
            $document_upload_after_days = $request->document_upload_after_days;
        }

        if (empty($request->is_holiday_deduct)) {
            $is_holiday_deduct = 0;
        } else {
            $is_holiday_deduct = $request->is_holiday_deduct;
        }

        $tab = LeavePolicy::find($id);
        $tab->company_id = $request->company_id;
        $tab->leave_title = $request->leave_title;
        $tab->leave_short_code = $request->leave_short_code;
        $tab->total_days = $request->total_days;
        $tab->is_applicable_for_all = $is_applicable_for_all;
        $tab->is_leave_cut_applicable = $is_leave_cut_applicable;
        $tab->is_carry_forward = $is_carry_forward;
        $tab->max_carry_forward_days = $request->max_carry_forward_days;
        $tab->is_document_upload = $is_document_upload;
        $tab->document_upload_after_days = $document_upload_after_days;
        $tab->is_holiday_deduct = $is_holiday_deduct;

        $tab->save();

        return redirect()->action('LeavePolicyController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = LeavePolicy::destroy($request->id);
        return 1;
    }

}
