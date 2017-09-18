<?php

namespace App\Http\Controllers;

use App\AssignEmployeeToShift;
use App\Shift;
use App\EmployeeCompanyBranch;
use App\EmployeeDepartment;
use App\EmployeeSection;
use App\EmployeeInfo;
use App\Department;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class AssignEmployeeToShiftController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //$company = Company::all();
        $shift = $this->companyWiseShift();
        //$logged_emp_company_id = MenuPageController::loggedUser('company_id');
		
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');

        return view('module.settings.shiftassign', ['company' => $company, 'shift' => $shift, 'logged_emp_com' => $logged_emp_company_id]);
    }

    public function companyWiseShift($company = '') {

        $shift = Shift::where('company_id', '8')->get();
        return $shift;
    }

    public function showList() {

        return view('module.settings.assignemployeeshiftList', []);
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
    public function store(Request $request) 
	{
        $this->validate($request, [
            'company_id' => 'required',
            'shift_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'shiftassign' => 'required',
        ]);

        foreach ($request->shiftassign as $key => $file):
            $shift = new AssignEmployeeToShift();
            $shift->company_id = $request->company_id;
            $shift->shift_id = $request->shift_id;
            $shift->start_date = $request->start_date;
            $shift->end_date = $request->end_date;
            $shift->emp_code = $file;
            $shift->save();
        endforeach;

        return redirect()->action('AssignEmployeeToShiftController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AssignEmployeeToShift  $assignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function show(AssignEmployeeToShift $assignEmployeeToShift) {

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $data = DB::table('assign_employee_to_shifts')
                    ->leftjoin('companies', 'companies.id', '=', 'assign_employee_to_shifts.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'assign_employee_to_shifts.shift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'assign_employee_to_shifts.emp_code')
                    ->select(DB::raw('assign_employee_to_shifts.id as id,assign_employee_to_shifts.emp_code as emp_code,companies.name as company,shifts.name as shift_name,
                    assign_employee_to_shifts.start_date as start_date,assign_employee_to_shifts.end_date as end_date,
                    shifts.is_night_shift as night,shifts.is_roster_shift as roster,
          concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name'))
                    ->where('assign_employee_to_shifts.company_id', $logged_emp_company_id)
                    ->groupBy('assign_employee_to_shifts.id')
                    ->orderBy('assign_employee_to_shifts.id', 'DESC')
                    ->get();
        } else {
            $data = DB::table('assign_employee_to_shifts')
                    ->leftjoin('companies', 'companies.id', '=', 'assign_employee_to_shifts.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'assign_employee_to_shifts.shift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'assign_employee_to_shifts.emp_code')
                    ->select(DB::raw('assign_employee_to_shifts.id as id,assign_employee_to_shifts.emp_code as emp_code,companies.name as company,shifts.name as shift_name,
                    assign_employee_to_shifts.start_date as start_date,assign_employee_to_shifts.end_date as end_date,
                    shifts.is_night_shift as night,shifts.is_roster_shift as roster,
          concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name'))
                    ->groupBy('assign_employee_to_shifts.id')
                    ->orderBy('assign_employee_to_shifts.id', 'DESC')
                    ->get();
        }
        return response()->json(array("data" => $data, "total" => count($data)));
    }

    public function filterEmployee(Request $request) {
        $company_id = $request->company_id;
        $department_id = $request->department_id;
        $section_id = $request->section_id;
        $designation_id = $request->designation_id;
		
		//echo $company_id;
		
		
		$data=EmployeeInfo::when($company_id, function ($query) use ($company_id) {
								return $query->where('employee_infos.company_id', $company_id);
						  })
						  ->leftjoin('employee_departments','employee_infos.emp_code','=', 'employee_departments.emp_code')
						  ->when($department_id, function ($query) use ($department_id) {
								return $query->where('employee_departments.department_id', $department_id);
						  })
						  ->leftjoin('employee_sections','employee_infos.emp_code','=','employee_sections.emp_code')
						  ->when($section_id, function ($query) use ($section_id) {
								return $query->where('employee_sections.section_id',$section_id);
						  })
						  ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,employee_infos.first_name AS name'))
						  ->groupBy('employee_infos.emp_code')
						  ->get();

        return response()->json($data);
    }
	
	public function ExcelFilterDataListForExsis($company_id,$start_date,$end_date,$shift_id,$emp_code) {
		if(empty($shift_id))
		{
			$shift_id='';
		}
		
		if(empty($emp_code))
		{
			$emp_code='';
		}
		
		if(!empty($start_date) && !empty($end_date) && !empty($company_id))
		{
			$data=DB::table('assign_employee_to_shifts')
					->leftjoin('employee_infos', 'assign_employee_to_shifts.emp_code', '=', 'employee_infos.emp_code')
					->leftjoin('shifts','assign_employee_to_shifts.shift_id','=','shifts.id')
					
					->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
					 employee_infos.first_name AS name,shifts.name AS shift_name,assign_employee_to_shifts.start_date,assign_employee_to_shifts.end_date'))
					 
					 ->when($emp_code, function ($query) use ($emp_code) {
						return $query->where('assign_employee_to_shifts.emp_code', $emp_code);
					 })
					 ->when($shift_id, function ($query) use ($shift_id) {
						return $query->where('assign_employee_to_shifts.shift_id', $shift_id);
					 })
					->where('employee_infos.company_id',$company_id) 
					->whereRaw("(assign_employee_to_shifts.start_date >= '$start_date' AND assign_employee_to_shifts.end_date <= '$end_date')")
					
					->get();
			//return response()->json($data);							
		}
		else
		{
			return 5;
		}
       
		$excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [
            '#',
			'Emp_code',
			'Employee Name',
            'Shift_Name',
            'Start_Date',
            'End_Date'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($data as $key => $field) {
            $excelArray[] = get_object_vars($field);
        }

        // Generate and return the spreadsheet
        \Excel::create('Existing Shift ' . $start_date.' To '.$end_date, function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Employee Info');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('EmployeeInfo');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('existing_shift_info', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');

	   
    }

    public function FilterDataListForExsis(Request $request) 
	{

        $company_id = $request->company_id;
        $shift_id = $request->shift_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
		$emp_code = $request->emp_code;
		
		if(!empty($start_date) && !empty($end_date) && !empty($company_id))
		{
			if(!empty($emp_code))
			{

				$data=DB::table('assign_employee_to_shifts')
						->leftjoin('employee_infos', 'assign_employee_to_shifts.emp_code', '=', 'employee_infos.emp_code')
						->leftjoin('shifts','assign_employee_to_shifts.shift_id','=','shifts.id')
						
						->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
						 employee_infos.first_name AS name,shifts.name AS shift_name,assign_employee_to_shifts.start_date,assign_employee_to_shifts.end_date,assign_employee_to_shifts.created_at'))
						 
						 ->when($emp_code, function ($query) use ($emp_code) {
							return $query->where('assign_employee_to_shifts.emp_code', $emp_code);
						 })
						 ->when($shift_id, function ($query) use ($shift_id) {
							return $query->where('assign_employee_to_shifts.shift_id', $shift_id);
						 })
						->where('employee_infos.company_id',$company_id) 
						->whereRaw("((assign_employee_to_shifts.start_date<= '$start_date' AND assign_employee_to_shifts.end_date >= '$end_date') OR (assign_employee_to_shifts.start_date BETWEEN '$start_date' AND '$end_date') OR (assign_employee_to_shifts.end_date BETWEEN '$start_date' AND '$end_date'))")
						->orderBy('assign_employee_to_shifts.id','DESC')
						//->orderBy('assign_employee_to_shifts.created_at','ASC')
						//->groupBy('assign_employee_to_shifts.emp_code')->groupBy('assign_employee_to_shifts.start_date')
						->take('1')
						->get();
			}
			else
			{
						$data=DB::table('assign_employee_to_shifts')
						->leftjoin('employee_infos', 'assign_employee_to_shifts.emp_code', '=', 'employee_infos.emp_code')
						->leftjoin('shifts','assign_employee_to_shifts.shift_id','=','shifts.id')
						
						->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
						 employee_infos.first_name AS name,shifts.name AS shift_name,assign_employee_to_shifts.start_date,assign_employee_to_shifts.end_date,assign_employee_to_shifts.created_at'))
						 
						 ->when($emp_code, function ($query) use ($emp_code) {
							return $query->where('assign_employee_to_shifts.emp_code', $emp_code);
						 })
						 ->when($shift_id, function ($query) use ($shift_id) {
							return $query->where('assign_employee_to_shifts.shift_id', $shift_id);
						 })
						->where('employee_infos.company_id',$company_id) 
						->whereRaw("((assign_employee_to_shifts.start_date<= '$start_date' AND assign_employee_to_shifts.end_date >= '$end_date') OR (assign_employee_to_shifts.start_date BETWEEN '$start_date' AND '$end_date') OR (assign_employee_to_shifts.end_date BETWEEN '$start_date' AND '$end_date'))")
						->orderBy('assign_employee_to_shifts.id','DESC')
						//->orderBy('assign_employee_to_shifts.created_at','DESC')
						//->groupBy('assign_employee_to_shifts.emp_code')
						//->take('1')
						->get();

			}
			return response()->json($data);							
		}
		else
		{
			return 5;
		}
        
    }

    public function exportExcel() {

        // print_r($data);

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $data = DB::table('assign_employee_to_shifts')
                    ->leftjoin('companies', 'companies.id', '=', 'assign_employee_to_shifts.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'assign_employee_to_shifts.shift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'assign_employee_to_shifts.emp_code')
                    ->select(DB::raw('assign_employee_to_shifts.emp_code as emp_code,
                    concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name,
                    companies.name as company,
                    shifts.name as shift_name,
                     shifts.is_night_shift as Is_Night_Shift,
                    shifts.is_roster_shift as Is_Roster_Shift,
                    assign_employee_to_shifts.start_date as start_date,
                    assign_employee_to_shifts.end_date as end_date
          '))
                    ->orderBy('assign_employee_to_shifts.id', 'DESC')
                    ->where('assign_employee_to_shifts.company_id', $logged_emp_company_id)
//                    ->groupBy('employee_infos.id')
                    ->get();
        } else {
            $data = DB::table('assign_employee_to_shifts')
                    ->leftjoin('companies', 'companies.id', '=', 'assign_employee_to_shifts.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'assign_employee_to_shifts.shift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'assign_employee_to_shifts.emp_code')
                    ->select(DB::raw('assign_employee_to_shifts.emp_code as emp_code,
                    concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name,
                    companies.name as company,
                    shifts.name as shift_name,
                    shifts.is_night_shift as night,
                    shifts.is_roster_shift as roster,
                    assign_employee_to_shifts.start_date as start_date,
                    assign_employee_to_shifts.end_date as end_date
          '))
                    ->orderBy('assign_employee_to_shifts.id', 'DESC')
//                    ->groupBy('employee_infos.id')
                    ->get();
        }

        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [

            'Emp_code',
            'Name',
            'Company',
            'Shift_name',
            'Is_Night_Shift',
            'Is_Roster_Shift',
            'Start_date',
            'End_date'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($data as $key => $field) {
            $excelArray[] = get_object_vars($field);
        }

        // Generate and return the spreadsheet
        \Excel::create('Assign Employee Shift_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

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

        $content = '<h3>Assign Employee Shift</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = [
            'Emp_code',
            'Name',
            'Company',
            'Shift_name',
            'Is_Night_Shift',
            'Is_Roster_Shift',
            'Start_date',
            'End_date'
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


            $logged_emp_company_id = MenuPageController::loggedUser('company_id');
            if (!empty($logged_emp_company_id)) {

                $data = DB::table('assign_employee_to_shifts')
                        ->leftjoin('companies', 'companies.id', '=', 'assign_employee_to_shifts.company_id')
                        ->leftjoin('shifts', 'shifts.company_id', '=', 'assign_employee_to_shifts.company_id')
                        ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'assign_employee_to_shifts.emp_code')
                        ->select(DB::raw('assign_employee_to_shifts.emp_code as Emp_code,
                    concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS Name,
                    companies.name as Company,
                    shifts.name as Shift_name,
                    shifts.is_night_shift as Is_Night_Shift,
                    shifts.is_roster_shift as Is_Roster_Shift,
                    assign_employee_to_shifts.start_date as Start_date,
                    assign_employee_to_shifts.end_date as End_date
          '))
                        ->orderBy('assign_employee_to_shifts.id', 'DESC')
                        ->where('assign_employee_to_shifts.company_id', $logged_emp_company_id)
//                        ->groupBy('employee_infos.id')
                        ->get();
            } else {
                $data = DB::table('assign_employee_to_shifts')
                        ->leftjoin('companies', 'companies.id', '=', 'assign_employee_to_shifts.company_id')
                        ->leftjoin('shifts', 'shifts.company_id', '=', 'assign_employee_to_shifts.company_id')
                        ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'assign_employee_to_shifts.emp_code')
                        ->select(DB::raw('assign_employee_to_shifts.emp_code as Emp_code,
                    concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS Name,
                    companies.name as Company,
                    shifts.name as Shift_name,
                    shifts.is_night_shift as Is_Night_Shift,
                    shifts.is_roster_shift as Is_Roster_Shift,
                    assign_employee_to_shifts.start_date as Start_date,
                    assign_employee_to_shifts.end_date as End_date
          '))
                        ->orderBy('assign_employee_to_shifts.id', 'DESC')
//                        ->groupBy('employee_infos.id')
                        ->get();
            }

            if (!empty($data)) {
                $content .='<tbody>';
                foreach ($data as $draw):
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AssignEmployeeToShift  $assignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function edit(AssignEmployeeToShift $assignEmployeeToShift) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AssignEmployeeToShift  $assignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AssignEmployeeToShift $assignEmployeeToShift) {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AssignEmployeeToShift  $assignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {

        $del = AssignEmployeeToShift::destroy($request->id);
        return 1;
    }

}
