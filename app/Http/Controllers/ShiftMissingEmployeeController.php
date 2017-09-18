<?php

namespace App\Http\Controllers;

use App\ShiftMissingEmployee;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\AssignEmployeeToShift;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;
class ShiftMissingEmployeeController extends Controller
{
	
	public function reportUpdate(Request $request)
	{
		$sql = DB::table('shift_missing_employees')
				->leftJoin('shifts','shift_missing_employees.shift_id','=','shifts.id')
				->leftJoin('employee_infos','shift_missing_employees.emp_code','=','employee_infos.emp_code')
				->select('shift_missing_employees.*',DB::Raw('shifts.name as shift_name'))
				->where('employee_infos.company_id',$request->company_id)
				->where('shift_missing_employees.review_status','Pending')
				->whereBetween('shift_missing_employees.date',[$request->start_date,$request->end_date])->get();
		foreach($sql as $row):
			$tab=ShiftMissingEmployee::find($row->id);
			$tab->review_status="Done";
			$tab->save();
		endforeach;
		return 1;
	}
	
	public function reportUpdateAll()
	{
		DB::table('shift_missing_employees')->update(['review_status'=>'Done']);

		return 1;
	}
	
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        return view('module.settings.shiftMissingReport',['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
		$company_id = $request->company_id;
		
		$SwapLoopCount=DB::table('shift_swap_loops')->where('company_id',$company_id)->count();
		if($SwapLoopCount==0)
		{

			$sqlDates = DB::table('shift_missing_employees')
							->leftJoin('shifts','shift_missing_employees.shift_id','=','shifts.id')
							->leftJoin('employee_infos','shift_missing_employees.emp_code','=','employee_infos.emp_code')
							->select('shift_missing_employees.*',DB::Raw('shifts.name as shift_name'))
							->where('employee_infos.company_id',$request->company_id)
							->where('shift_missing_employees.review_status','Pending')
							->whereBetween('shift_missing_employees.date',[$start_date,$end_date])->get();
		}
		else
		{
			$SwapLoop=DB::table('shift_swap_loops')->where('company_id',$company_id)->first();
			//$loopShiftID="(".$SwapLoop->shift_start.",".$SwapLoop->shift_end.")";
			
			$sqlDates = DB::table('shift_missing_employees')
							->leftJoin('shifts','shift_missing_employees.shift_id','=','shifts.id')
							->leftJoin('employee_infos','shift_missing_employees.emp_code','=','employee_infos.emp_code')
							->select('shift_missing_employees.*',DB::Raw('shifts.name as shift_name'))
							->whereRaw('shift_missing_employees.emp_code IN (SELECT shift_missing_employees.emp_code FROM assign_employee_to_shifts WHERE shift_missing_employees.emp_code=assign_employee_to_shifts.emp_code AND assign_employee_to_shifts.shift_id IN ('.$SwapLoop->shift_start.','.$SwapLoop->shift_end.'))')
							->where('employee_infos.company_id',$request->company_id)
							->where('shift_missing_employees.review_status','Pending')
							->whereBetween('shift_missing_employees.date',[$start_date,$end_date])
							->get();	
		}

        if (!empty($sqlDates)) {
            $json = [];
            foreach ($sqlDates as $line):

                $json[] = array(
                    'id' => $line->id,
                    'emp_code' => $line->emp_code,
                    'date' => $line->date,
                    'shift' => $line->shift_name,
                    'review_status' => $line->review_status,
                    'reviewed_emp_code' => $line->reviewed_emp_code,
                    'created_at' => $line->created_at,
                    'updated_at' => $line->updated_at
                );

            endforeach;
        }

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ShiftMissingEmployee  $shiftMissingEmployee
     * @return \Illuminate\Http\Response
     */
    public function show(ShiftMissingEmployee $shiftMissingEmployee)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ShiftMissingEmployee  $shiftMissingEmployee
     * @return \Illuminate\Http\Response
     */
    public function edit($id=0)
    {
		$sqlShift=DB::table('shifts')->get();
		$emp_code = app('App\Http\Controllers\MenuPageController')->loggedUser('emp_code');
        $tabUpd=ShiftMissingEmployee::find($id);
		$tabUpd->reviewed_emp_code=$emp_code;
		$tabUpd->save();
		
		return view('module.settings.shiftMissingModify',['data'=>$tabUpd,'shift'=>$sqlShift]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ShiftMissingEmployee  $shiftMissingEmployee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id=0)
    {
		$emp_code = app('App\Http\Controllers\MenuPageController')->loggedUser('emp_code');
        $tabUpd=ShiftMissingEmployee::find($id);
		$tabUpd->reviewed_emp_code=$emp_code;
		$tabUpd->review_status='Done';
		$tabUpd->save();
		
		$company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
		
		$nTab=new AssignEmployeeToShift;
		$nTab->company_id=$company_id;
		$nTab->shift_id=$request->shift_id;
		$nTab->emp_code=$request->emp_code;
		$nTab->start_date=$request->start_date;
		$nTab->end_date=$request->end_date;
		$nTab->save();
		
		
		
		return redirect()->action('ShiftMissingEmployeeController@index')->with('success','Information Updated Successfully');
		
		
		
    }
	
	
	public function showLog($status='Pending')
	{
		$tabUpd=ShiftMissingEmployee::where('review_status',$status)->count();
		echo $tabUpd;
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ShiftMissingEmployee  $shiftMissingEmployee
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShiftMissingEmployee $shiftMissingEmployee)
    {
        //
    }
	
	
    public function ExportExcel($company_id=0,$start_date = 0, $end_date = 0) 
	{

        // $emp_code = $request->emp_code;
        // $start_date = $request->start_date;
        // $end_date = $request->end_date;

      
		$SwapLoopCount=DB::table('shift_swap_loops')->where('company_id',$company_id)->count();
		if($SwapLoopCount==0)
		{
			$sqlDates = DB::table('shift_missing_employees')
							->leftJoin('shifts','shift_missing_employees.shift_id','=','shifts.id')
							->leftJoin('employee_infos','shift_missing_employees.emp_code','=','employee_infos.emp_code')
							->select('shift_missing_employees.*',DB::Raw('shifts.name as shift_name'))
							->where('employee_infos.company_id',$company_id)
							->where('shift_missing_employees.review_status','Pending')
							->whereBetween('shift_missing_employees.date',[$start_date,$end_date])->get();
		}
		else
		{
			$SwapLoop=DB::table('shift_swap_loops')->where('company_id',$company_id)->first();
			//$loopShiftID="(".$SwapLoop->shift_start.",".$SwapLoop->shift_end.")";
			
			$sqlDates = DB::table('shift_missing_employees')
							->leftJoin('shifts','shift_missing_employees.shift_id','=','shifts.id')
							->leftJoin('employee_infos','shift_missing_employees.emp_code','=','employee_infos.emp_code')
							->select('shift_missing_employees.*',DB::Raw('shifts.name as shift_name'))
							->whereRaw('shift_missing_employees.emp_code IN (SELECT shift_missing_employees.emp_code FROM assign_employee_to_shifts WHERE shift_missing_employees.emp_code=assign_employee_to_shifts.emp_code AND assign_employee_to_shifts.shift_id IN ('.$SwapLoop->shift_start.','.$SwapLoop->shift_end.'))')
							->where('employee_infos.company_id',$company_id)
							->where('shift_missing_employees.review_status','Pending')
							->whereBetween('shift_missing_employees.date',[$start_date,$end_date])
							->get();
		}

        if (!empty($sqlDates)) {
            $json = [];
            foreach ($sqlDates as $line):

                $json[] = array(
                    'emp_code' => $line->emp_code,
                    'date' => $line->date,
                    'shift' => $line->shift_name,
                    'review_status' => $line->review_status,
                    'reviewed_emp_code' => $line->reviewed_emp_code,
                    'created_at' => $line->created_at,
                    'updated_at' => $line->updated_at
                );

            endforeach;
        }

        // return response()->json(array("data" => $json, "total" => count($json)));



        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [
            'emp_code',
            'date',
            'shift',
            'review_status',
            'reviewed_emp_code',
            'created_at',
            'updated_at',
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.get_object_vars()
        foreach ($json as $key => $field) {
            $excelArray[] = $field;
        }
        //exit();
        // Generate and return the spreadsheet
        \Excel::create('Shift Missing Report_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Shift Missing Report');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Shift Missing Report');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

   public function exportPdfReport($company_id=0,$start_date = 0, $end_date = 0) 
   {

        $content = '<h3>Shift Missing Time Report</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
// instantiate and use the dompdf class
        $excelArray = [
             'id',
             'emp_code',
            'date',
            'shift',
            'review_status',
            'reviewed_emp_code',
            'created_at',
            'updated_at',
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
            
		$SwapLoopCount=DB::table('shift_swap_loops')->where('company_id',$company_id)->count();
		if($SwapLoopCount==0)
		{

            $sqlDates = DB::table('shift_missing_employees')
                        ->leftJoin('shifts','shift_missing_employees.shift_id','=','shifts.id')
						->leftJoin('employee_infos','shift_missing_employees.emp_code','=','employee_infos.emp_code')
                        ->select('shift_missing_employees.*',DB::Raw('shifts.name as shift_name'))
						->where('employee_infos.company_id',$company_id)
						->where('shift_missing_employees.review_status','Pending')
                        ->whereBetween('shift_missing_employees.date',[$start_date,$end_date])->get();
		}
		else
		{
			$SwapLoop=DB::table('shift_swap_loops')->where('company_id',$company_id)->first();
			//$loopShiftID="(".$SwapLoop->shift_start.",".$SwapLoop->shift_end.")";
			
			$sqlDates = DB::table('shift_missing_employees')
							->leftJoin('shifts','shift_missing_employees.shift_id','=','shifts.id')
							->leftJoin('employee_infos','shift_missing_employees.emp_code','=','employee_infos.emp_code')
							->select('shift_missing_employees.*',DB::Raw('shifts.name as shift_name'))
							->whereRaw('shift_missing_employees.emp_code IN (SELECT shift_missing_employees.emp_code FROM assign_employee_to_shifts WHERE shift_missing_employees.emp_code=assign_employee_to_shifts.emp_code AND assign_employee_to_shifts.shift_id IN ('.$SwapLoop->shift_start.','.$SwapLoop->shift_end.'))')
							->where('employee_infos.company_id',$company_id)
							->where('shift_missing_employees.review_status','Pending')
							->whereBetween('shift_missing_employees.date',[$start_date,$end_date])
							->get();
		}

        if (!empty($sqlDates)) {
            $json = [];
            foreach ($sqlDates as $line):

                $json[] = array(
                      'id' => $line->id,
                    'emp_code' => $line->emp_code,
                    'date' => $line->date,
                    'shift' => $line->shift_name,
                    'review_status' => $line->review_status,
                    'reviewed_emp_code' => $line->reviewed_emp_code,
                    'created_at' => $line->created_at,
                    'updated_at' => $line->updated_at,
                );

            endforeach;
        }

        // return response()->json(array("data" => $json, "total" => count($json)));

            if (!empty($json)) {
                $content .='<tbody>';
                foreach ($json as $draw):

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

            $content .='<h4>Total : ' . count($json) . '</h4>';


            $content .='<br /><br /><br /><table border="0" width="100%">';
            $content .='<tr>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
            $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
            $content .='</tr>';


            $content .='</table>';
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
