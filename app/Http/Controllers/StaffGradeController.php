<?php

namespace App\Http\Controllers;

use App\StaffGrade;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffGradeController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $company = Company::all();


        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.staffGrade', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
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
        $this->validate($request, ['name' => 'required', 'company_id' => 'required']);

        $is_active = $request->is_active ? $request->is_active : "0";
        $is_ot_eligible = $request->is_ot_eligible ? $request->is_ot_eligible : "0";

        $staffGrade = new StaffGrade;
        $staffGrade->name = $request->name;
        $staffGrade->company_id = $request->company_id;
        $staffGrade->is_active = $is_active;
        $staffGrade->is_ot_eligible = $is_ot_eligible;
        $staffGrade->save();
        return redirect()->action('StaffGradeController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StaffGrade  $staffGrade
     * @return \Illuminate\Http\Response
     */
    public function show(StaffGrade $staffGrade) {
       // $designation = StaffGrade::all();

        /*$logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $designation = DB::table('staff_grades')
                    ->join('companies', 'companies.id', '=', 'staff_grades.company_id')
                    ->select(DB::raw('staff_grades.*,
                        companies.name as company_id'))
                    //->where('staff_grades.company_id', $logged_emp_company_id)
                    ->groupBy('staff_grades.id')
                    ->get();
        } else { */
            $designation = DB::table('staff_grades')
                    ->join('companies', 'companies.id', '=', 'staff_grades.company_id')
                    ->select(DB::raw('staff_grades.*,
                        companies.name as company_id'))
                    ->groupBy('staff_grades.id')
                    ->get();
        //}


        return response()->json(array("data" => $designation, "total" => count($designation)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StaffGrade  $staffGrade
     * @return \Illuminate\Http\Response
     */
    public function edit(StaffGrade $staffGrade, $id) {
        $data = StaffGrade::find($id);
        $company = Company::all();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.staffGrade', ['data' => $data, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StaffGrade  $staffGrade
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, ['name' => 'required', 'company_id' => 'required']);


        $is_active = $request->is_active ? $request->is_active : "0";
        $is_ot_eligible = $request->is_ot_eligible ? $request->is_ot_eligible : "0";

        $staffGrade = StaffGrade::find($id);
        $staffGrade->name = $request->name;
        $staffGrade->company_id = $request->company_id;
        $staffGrade->is_active = $is_active;
        $staffGrade->is_ot_eligible = $is_ot_eligible;
        $staffGrade->save();

        return redirect()
                        ->action('StaffGradeController@index')
                        ->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StaffGrade  $staffGrade
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = StaffGrade::destroy($request->id);
        return 1;
    }

}
