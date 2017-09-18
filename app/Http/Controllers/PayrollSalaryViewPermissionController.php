<?php

namespace App\Http\Controllers;

use App\PayrollSalaryViewPermission;
use Illuminate\Http\Request;
use App\Company;
use App\EmployeeInfo;
use App\StaffGrade;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

class PayrollSalaryViewPermissionController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $employee = EmployeeInfo::all();
        $staffgrade = StaffGrade::orderBy('name')->get();
        return view('module.payroll.payrollSalaryViewPermission', ['employee' => $employee, 'staffgrade' => $staffgrade]);
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
        $this->validate($request, ['emp_code' => 'required', 'staff_grade' => 'required']);

        if(count($request->staff_grade)==0)
		{
				return redirect()->action('PayrollSalaryViewPermissionController@index')->with('error', 'Information failed  to Add');
		}
		else
		{
				PayrollSalaryViewPermission::where('emp_code',$request->emp_code)->delete();
				foreach ($request->staff_grade as $value):
					$tabs = new PayrollSalaryViewPermission;
					$tabs->emp_code = $request->emp_code;
					$tabs->staff_grade_id = $value;
					$tabs->status = 0;
					$tabs->save();
				endforeach;
				
				return redirect()->action('PayrollSalaryViewPermissionController@index')->with('success', 'Information Added Successfully');

		}
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PayrollSalaryViewPermission  $payrollSalaryViewPermission
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request) {
        $json = DB::table('payroll_salary_view_permissions')
                ->leftjoin('staff_grades', 'payroll_salary_view_permissions.staff_grade_id', '=', 'staff_grades.id')
                ->select(DB::raw('payroll_salary_view_permissions.*,staff_grades.name as staff_grade_name'))
				->where('payroll_salary_view_permissions.emp_code',$request->emp_code)
				->orderBy('staff_grades.name')
                ->get();
		$str='';
		if(count($json)!=0)
		{
			foreach($json as $row):
				$str .='<span class="btn btn-info" style="margin:2px;">'.$row->staff_grade_name.'</span>';
			endforeach;
				
		}
		else
		{
			$str='No Record Found';
		}
		return $str;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PayrollSalaryViewPermission  $payrollSalaryViewPermission
     * @return \Illuminate\Http\Response \\192.168.1.36\hrms\resources\views\module\settings\payrollSalaryViewPermission.blade.php
     */
    public function edit(PayrollSalaryViewPermission $payrollSalaryViewPermission, $emp_code) {
        $data = PayrollSalaryViewPermission::where('payroll_salary_view_permissions.emp_code', $emp_code)->get();
        $employee = EmployeeInfo::all();
//        $staffgrade = StaffGrade::take(count($data))->get();
        $staffgrade = StaffGrade::all();


        return view('module.settings.payrollSalaryViewPermission', ['data' => $data, 'employee' => $employee, 'staffgrade' => $staffgrade]);
//        echo count($data);
//        exit();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PayrollSalaryViewPermission  $payrollSalaryViewPermission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PayrollSalaryViewPermission $payrollSalaryViewPermission, $emp_code) {
        $this->validate($request, ['emp_code' => 'required', 'staff_grade' => 'required']);

        $delExData = PayrollSalaryViewPermission::where('emp_code', $emp_code)->delete();
        if (isset($delExData)) {
//            foreach ($request->staff_grade as $key => $val):
//                $tab = new PayrollSalaryViewPermission;
//                $tab->emp_code = $request->emp_code;
//                $tab->staff_grade_id = $request->staff_grade[$key];
//                $tab->save();
//            endforeach;
            $staffgrade = StaffGrade::all();
            foreach ($staffgrade as $value):
                $tabs = new PayrollSalaryViewPermission;
                $tabs->emp_code = $request->emp_code;
                $tabs->staff_grade_id = $value->id;
                $tabs->status = 0;
                $tabs->save();
            endforeach;


            foreach ($request->staff_grade as $key => $val):
                $tab = PayrollSalaryViewPermission::where('emp_code', $request->emp_code)->where('staff_grade_id', $request->staff_grade[$key])->update(['status' => $request->staff_grade[$key]]);
            endforeach;

            return redirect()->action('PayrollSalaryViewPermissionController@index')->with('success', 'Information Updated Successfully');
        } else {
            return redirect()->action('PayrollSalaryViewPermissionController@index')->with('error', 'Sorry! Failed To Update Data. Please Try Again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PayrollSalaryViewPermission  $payrollSalaryViewPermission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        PayrollSalaryViewPermission::where('emp_code',$request->id)->delete();
		return 1;
    }

}
