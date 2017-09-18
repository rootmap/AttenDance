<?php

namespace App\Http\Controllers;

use App\Shift;
use App\Company;
use Illuminate\Http\Request;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.shift', ['logged_emp_com' => $logged_emp_company_id]);
    }

    //filter
    public function filterShift(Request $request) {
        //$company_id = $request->company_id;
//        $data = Shift::where('company_id', $company_id)->get();
        $data = Shift::all();
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.shiftadd', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
            'name' => 'required',
            'shift_start_time' => 'required',
            'shift_end_time' => 'required'
        ]);


        $is_night_shift = $request->is_night_shift ? $request->is_night_shift : "0";
        $is_roster_shift = $request->is_roster_shift ? $request->is_roster_shift : "0";

        $tab = new Shift();
        $tab->company_id = $request->company_id;
        $tab->name = $request->name;
        $tab->is_night_shift = $is_night_shift;
        $tab->shift_start_time = $request->shift_start_time;
        $tab->shift_start_buffer_time = $request->shift_start_buffer_time;
        $tab->shift_end_time = $request->shift_end_time;
        $tab->shift_end_buffer_time = $request->shift_end_buffer_time;
        $tab->is_roster_shift = $is_roster_shift;
        $tab->save();

        return redirect()->action('ShiftController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function show() {

        $json = Shift::all();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $json = DB::table('shifts')
                    ->select(DB::raw('shifts.*'))
                    ->where('shifts.company_id', $logged_emp_company_id)
                    ->get();
        } else {
            $json = DB::table('shifts')
                    ->select(DB::raw('shifts.*'))
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
        $dbfields = Shift::all();

        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray[] = \DB::getSchemaBuilder()->getColumnListing("shifts");

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $field) {
            $excelArray[] = $field->toArray();
        }

        // Generate and return the spreadsheet
        \Excel::create('shiftsData_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Shift');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Habijabi');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf() {

        $content = '<h3>Shift List</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = \DB::getSchemaBuilder()->getColumnListing("shifts");
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
            $datarows = Shift::all();
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
        $data = Shift::find($id);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.shiftadd', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, [
            'company_id' => 'required',
            'name' => 'required',
            'shift_start_time' => 'required',
            'shift_end_time' => 'required'
        ]);


        $is_night_shift = $request->is_night_shift ? $request->is_night_shift : "0";
        $is_roster_shift = $request->is_roster_shift ? $request->is_roster_shift : "0";

        $tab = Shift::find($id);
        $tab->company_id = $request->company_id;
        $tab->name = $request->name;
        $tab->is_night_shift = $is_night_shift;
        $tab->shift_start_time = $request->shift_start_time;
        $tab->shift_start_buffer_time = $request->shift_start_buffer_time;
        $tab->shift_end_time = $request->shift_end_time;
        $tab->shift_end_buffer_time = $request->shift_end_buffer_time;
        $tab->is_roster_shift = $is_roster_shift;
        $tab->save();

        return redirect()->action('ShiftController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = Shift::destroy($request->id);
        return 1;
    }

}
