<?php

namespace App\Http\Controllers;

use App\Designation;
use App\Department;
use App\Company;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignationController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $section = Section::all();
        $company = Company::all();
        $department = Department::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.designation', ['section' => $section, 'company' => $company, 'department' => $department, 'logged_emp_com' => $logged_emp_company_id]);
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
            'name' => 'required',
            'company_id' => 'required',
            'department_id' => 'required',
            'section_id' => 'required',
            'is_active' => 'required',
        ]);


        $tab = new Designation;
        $tab->name = $request->name;
        $tab->company_id = $request->company_id;
        ;
        $tab->department_id = $request->department_id;
        $tab->section_id = $request->section_id;
        $tab->is_active = $request->is_active;
        $tab->save();

        return redirect()->action('DesignationController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function show(Designation $designation) {
        //$designation = Designation::all();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        /*if (!empty($logged_emp_company_id)) {
            $designation = DB::table('designations')
                    ->join('companies', 'companies.id', '=', 'designations.company_id')
                    ->join('departments', 'departments.id', '=', 'designations.department_id')
                    ->join('sections', 'sections.id', '=', 'designations.section_id')
                    ->select(DB::raw('designations.*,
                        departments.name as department_id,
                        sections.name as section_id,
                        companies.name as company_id'))
                    ->where('designations.company_id', $logged_emp_company_id)
                    ->where('designations.is_active','Active')
                    ->groupBy('designations.id')
                    ->get();
        } else {*/
            $designation = DB::table('designations')
                    ->join('companies', 'companies.id', '=', 'designations.company_id')
                    ->join('departments', 'departments.id', '=', 'designations.department_id')
                    ->join('sections', 'sections.id', '=', 'designations.section_id')
                    ->select(DB::raw('designations.*,
                        departments.name as department_id,
                        sections.name as section_id,
                        companies.name as company_id'))
                    ->where('designations.is_active','Active')
                    ->groupBy('designations.id')
                    ->get();
       // }


        return response()->json(array("data" => $designation, "total" => count($designation)));
    }

    //filter
    public function filterDesignation(Request $request) {


        $company_id = $request->company_id;
        $department_id = $request->department_id;
        $section_id = $request->section_id;
        /*$data = Designation::where('company_id', $company_id)
                ->where('department_id', $department_id)
                ->where('section_id', $section_id)
                ->where('is_active','Active')
                ->get();*/
				
		$data = Designation::where('department_id', $department_id)
                //->where('section_id', $section_id)
                ->where('is_active','Active')
                ->get();		
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function edit(Designation $designation, $id) {
        $data = Designation::find($id);
        $section = Section::all();
        $company = Company::all();
        $department = Department::all();


        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.designation', ['data' => $data, 'section' => $section, 'company' => $company, 'department' => $department, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, [
            'name' => 'required',
            'Department_id' => 'required',
            'section_id' => 'required',
            'is_active' => 'required',
        ]);
        $tab = Designation::find($id);
        $tab->name = $request->name;
        $tab->company_id = $request->company_id;
        $tab->department_id = $request->Department_id;
        $tab->section_id = $request->section_id;
        $tab->is_active = $request->is_active;
        $tab->save();

        return redirect()->action('DesignationController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Designation  $designation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = Designation::destroy($request->id);
        return 1;
    }

}
