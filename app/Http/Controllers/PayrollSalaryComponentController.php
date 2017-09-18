<?php

namespace App\Http\Controllers;

use App\PayrollSalaryComponent;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollSalaryComponentController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        $company = Company::where('is_active', '1')->get();

        $componentList = PayrollSalaryComponent::orderBy('display_order')->get();

        return view('module/payroll/salaryComponent', ['company' => $company,
            'logged_emp_com' => $logged_emp_company_id,
            'componentList' => $componentList]);
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
    private function DisplayOrderID($oiD=0) {
        $retOid = 0;
        if ($oiD == 'first') {
            DB::table('payroll_salary_components')->increment('display_order', 1);
            $retOid = 1;
        } elseif ($oiD == 'last') {
            $getlast = DB::table('payroll_salary_components')->orderBy('display_order', 'DESC')->first();
            $retOid = $getlast->display_order + 1;
        } else {

            $getlast = DB::table('payroll_salary_components')->where('id', $oiD)->first();
            $retOid = $getlast->display_order + 1;
            DB::table('payroll_salary_components')->where('id', '>', $oiD)->increment('display_order', 2);
        }

        return $retOid;
    }

    public function store(Request $request) {
        $this->validate($request, [
            'header_title' => 'required',
            //'headerDisplayOn' => 'required',
            //'display_order' => 'required',
        ]);


        $display_order = $this->DisplayOrderID($request->display_order);
        //echo $display_order;
        //exit();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (!empty($logged_emp_company_id)) {
            $company_id = $logged_emp_company_id;
        } else {
            $company_id = $request->company_id;
        }

        $is_monthly = 0;
        $is_monthly = $request->is_monthly ? $request->is_monthly : '0';
        

        $is_optional = 0;
        $is_optional = $request->is_optional ? $request->is_optional : '0';
        
		
		$is_gross = 0;
        $is_gross = $request->is_gross ? $request->is_gross : '0';
        
		
		$is_calculative = 0;
        $is_calculative = $request->is_calculative ? $request->is_calculative : '0';
		
		$is_salary_sheet = 'None';
        $is_salary_sheet = $request->is_salary_sheet ? 'Show in salary sheet' : 'None';
        
		$headerDisplayOn=$request->headerDisplayOn?$request->headerDisplayOn:'None';

        $trimmed = strtolower(trim($request->header_title)); // Trims both ends
        $field_name = preg_replace('/\s+/', '_', $trimmed); // str_replace('', '_', $trimmed);

        $tab = new PayrollSalaryComponent();
        $tab->company_id = $company_id;
        $tab->header_title = $request->header_title;
        $tab->DisplayOnSalarySheet = $is_salary_sheet;
        $tab->headerDisplayOn = $headerDisplayOn;
        $tab->is_monthly = $is_monthly;
        $tab->is_optional = $is_optional;
        $tab->is_gross = $is_gross;
        $tab->is_calculative = $is_calculative;
        $tab->field_name = $field_name;
        $tab->display_order = $display_order;
        $tab->save();
        return redirect()->action('PayrollSalaryComponentController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PayrollSalaryComponent  $payrollSalaryComponent
     * @return \Illuminate\Http\Response
     */
    public function show(PayrollSalaryComponent $payrollSalaryComponent) {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        /* if (!empty($logged_emp_company_id)) {
          $json = DB::table('payroll_salary_components')
          ->leftjoin('companies', 'companies.id', '=', 'payroll_salary_components.company_id')
          ->select(DB::raw('payroll_salary_components.*,companies.name'))
          ->where('payroll_salary_components.company_id', $logged_emp_company_id)
          ->get();
          } else { */
        $json = DB::table('payroll_salary_components')
                ->leftjoin('companies', 'companies.id', '=', 'payroll_salary_components.company_id')
                ->select(DB::raw('payroll_salary_components.*,companies.name'))
                ->orderBy('display_order')
                ->get();
        //}
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PayrollSalaryComponent  $payrollSalaryComponent
     * @return \Illuminate\Http\Response
     */
    public function edit(PayrollSalaryComponent $payrollSalaryComponent, $id) {
        $company = Company::all();
        $data = PayrollSalaryComponent::find($id);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        $componentList = PayrollSalaryComponent::orderBy('display_order')->get();
        return view('module/payroll/salaryComponent', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id, 'componentList' => $componentList]);
    }

    private function UpDisplayOrderID($oiD) {
        $retOid = 0;
        if ($oiD == 'first') {
            DB::table('payroll_salary_components')->increment('display_order', 1);
            $retOid = 1;
        } elseif ($oiD == 'last') {
            $getlast = DB::table('payroll_salary_components')->orderBy('display_order', 'DESC')->first();
            $retOid = $getlast->display_order + 1;
        } else {
            $getlast = PayrollSalaryComponent::where('display_order', $oiD)->first();
            $retOid = $getlast->display_order + 1;
            DB::table('payroll_salary_components')->where('display_order', '>', $oiD)->increment('display_order', 2);
        }

        return $retOid;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PayrollSalaryComponent  $payrollSalaryComponent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PayrollSalaryComponent $payrollSalaryComponent, $id) {
        $this->validate($request, [
            'header_title' => 'required',
            //'headerDisplayOn' => 'required',
        ]);

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (!empty($logged_emp_company_id)) {
            $company_id = $logged_emp_company_id;
        } else {
            $company_id = $request->company_id;
        }

        $is_monthly = 0;
        $is_monthly = $request->is_monthly ? $request->is_monthly : '0';
        

        $is_optional = 0;
        $is_optional = $request->is_optional ? $request->is_optional : '0';
        
		
		$is_gross = 0;
        $is_gross = $request->is_gross ? $request->is_gross : '0';
		
		$is_salary_sheet = 'None';
        $is_salary_sheet = $request->is_salary_sheet ? 'Show in salary sheet' : 'None';
        
		
		$is_calculative = 0;
        $is_calculative = $request->is_calculative ? $request->is_calculative : '0';

        $display_order = $this->UpDisplayOrderID($request->display_order);
		$headerDisplayOn=$request->headerDisplayOn?$request->headerDisplayOn:'None';
        $trimmed = strtolower(trim($request->header_title)); // Trims both ends
        $field_name = preg_replace('/\s+/', '_', $trimmed); // str_replace('', '_', $trimmed);

        $tab = PayrollSalaryComponent::find($id);
        $tab->company_id = $company_id;
        $tab->header_title = $request->header_title;
        $tab->DisplayOnSalarySheet = $is_salary_sheet;
        $tab->headerDisplayOn = $headerDisplayOn;
        $tab->is_monthly = $is_monthly;
        $tab->is_optional = $is_optional;
		$tab->is_gross = $is_gross;
        $tab->is_calculative = $is_calculative;
        $tab->field_name = $field_name;
        $tab->display_order = $display_order;
        $tab->save();
        return redirect()->action('PayrollSalaryComponentController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PayrollSalaryComponent  $payrollSalaryComponent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = PayrollSalaryComponent::destroy($request->id);
        return 1;
    }

}
