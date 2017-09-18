<?php

namespace App\Http\Controllers;

use App\ShiftPattern;
use Illuminate\Http\Request;
use App\Shift;
use App\Company;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ShiftPatternController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.shiftpattern', ['logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $company = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.shiftpatternadd', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
            'shift_id' => 'required',
            'name' => 'required',
            'start_in_time_pattern' => 'required',
            'end_in_time_pattern' => 'required',
            'start_out_time_pattern' => 'required',
            'end_out_time_pattern' => 'required'
        ]);



        $tab = new ShiftPattern;
        $tab->company_id = $request->company_id;
        $tab->shift_id = $request->shift_id;
        $tab->name = $request->name;
        $tab->start_in_time_pattern = $request->start_in_time_pattern;
        $tab->end_in_time_pattern = $request->end_in_time_pattern;
        $tab->start_out_time_pattern = $request->start_out_time_pattern;
        $tab->end_out_time_pattern = $request->end_out_time_pattern;
        $tab->save();

        return redirect()->action('ShiftPatternController@index')->with('success', 'Pattern Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function show() {



        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $json = $json = DB::table('shift_patterns')
                    ->join('shifts', 'shift_patterns.shift_id', '=', 'shifts.id')
                    ->select('shift_patterns.*', DB::raw('shifts.name as shift_name'))
                    ->where('shift_patterns.company_id', $logged_emp_company_id)
                    ->get();
        } else {
            $json = DB::table('shift_patterns')
                    ->join('shifts', 'shift_patterns.shift_id', '=', 'shifts.id')
                    ->select('shift_patterns.*', DB::raw('shifts.name as shift_name'))
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
        //$dbfields = ShiftPattern::all();
      
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $dbfields = DB::table('shift_patterns')
                    ->select('shift_patterns.*')
                    ->where('shift_patterns.company_id', $logged_emp_company_id)
                    ->get();
        } else {
            $dbfields = ShiftPattern::all();
        }
        // print_r($dbfields);
        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray[] = [
            'id',
            'company_id',
            'shift_id',
            'name',
            'start_in_time_pattern',
            'end_in_time_pattern',
            'start_out_time_pattern',
            'end_out_time_pattern',
            'created_at',
            'updated_at'
        ];

        // Define the Excel spreadsheet headers
       // $excelArray[] = \DB::getSchemaBuilder()->getColumnListing("shifts");
       
        // Convert each member of the returned collection into an array,
        // and append it to the payments array.

        foreach ($dbfields as $key => $field) {
            $excelArray[] = get_object_vars($field);

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
       // $excelArray = \DB::getSchemaBuilder()->getColumnListing("shifts");
        $excelArray= [
            'id',
            'company_id',
            'shift_id',
            'name',
            'start_in_time_pattern',
            'end_in_time_pattern',
            'start_out_time_pattern',
            'end_out_time_pattern',
            'created_at',
            'updated_at'
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


            $logged_emp_company_id = MenuPageController::loggedUser('company_id');
            if (!empty($logged_emp_company_id)) {
                $datarows= DB::table('shift_patterns')
                        ->select('shift_patterns.*')
                        ->where('shift_patterns.company_id', $logged_emp_company_id)
                        ->get();
            } else {
                $datarows = ShiftPattern::all();
            }
            
            
            $rows = count($excelArray);
           // $datarows = ShiftPattern::all();
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
        $data = ShiftPattern::find($id);
        $shiftdata = Shift::where('company_id', $data->company_id)->get();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.shiftpatternadd', ['data' => $data, 'company' => $company, 'shift' => $shiftdata, 'logged_emp_com' => $logged_emp_company_id]);
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
            'shift_id' => 'required',
            'name' => 'required',
            'start_in_time_pattern' => 'required',
            'end_in_time_pattern' => 'required',
            'start_out_time_pattern' => 'required',
            'end_out_time_pattern' => 'required'
        ]);

        $tab = ShiftPattern::find($id);
        $tab->company_id = $request->company_id;
        $tab->shift_id = $request->shift_id;
        $tab->name = $request->name;
        $tab->start_in_time_pattern = $request->start_in_time_pattern;
        $tab->end_in_time_pattern = $request->end_in_time_pattern;
        $tab->start_out_time_pattern = $request->start_out_time_pattern;
        $tab->end_out_time_pattern = $request->end_out_time_pattern;
        $tab->save();

        return redirect()->action('ShiftPatternController@index')->with('success', 'Paterrn Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = ShiftPattern::destroy($request->id);
        return 1;
    }

}
