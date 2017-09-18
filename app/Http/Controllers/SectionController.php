<?php

namespace App\Http\Controllers;

use App\Department;
use App\Company;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $company = Company::all();
        $department = Department::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        return view('module.settings.section', ['company' => $company, 'department' => $department, 'logged_emp_com' => $logged_emp_company_id]);
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
            'name' => 'required',
            'department_id' => 'required',
            'is_active' => 'required'
        ]);

       
        $tab = new Section;
        $tab->name = $request->name;
        $tab->company_id = $request->company_id;
        $tab->department_id = $request->department_id;
        $tab->is_active = $request->is_active;
        $tab->save();

        return redirect()->action('SectionController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show() {

        //$section = Section::all();


        /*$logged_emp_company_id = MenuPageController::loggedUser('company_id');
        if (!empty($logged_emp_company_id)) {
            $section = DB::table('sections')
                    ->join('companies', 'companies.id', '=', 'sections.company_id')
                    ->join('departments', 'departments.id', '=', 'sections.department_id')
                    ->select(DB::raw('sections.*,
                        departments.name as department_id,
                        companies.name as company_id'))
                    ->where('sections.company_id', $logged_emp_company_id)
                    ->where('sections.is_active','Active')
                    ->groupBy('sections.id')
                    ->get();
        } else {*/
            $section = DB::table('sections')
                    ->join('companies', 'companies.id', '=', 'sections.company_id')
                    ->join('departments', 'departments.id', '=', 'sections.department_id')
                    ->select(DB::raw('sections.*,
                        departments.name as department_id,
                        companies.name as company_id'))
                    ->where('sections.is_active','Active')
                    ->groupBy('sections.id')
                    ->get();
        //}

        return response()->json(array("data" => $section, "total" => count($section)));
    }

    //filter
    public function filterSection(Request $request) {
        $company_id = $request->company_id;
        $department_id = $request->department_id;
        /*
		$data = Section::where('company_id', $company_id)
                ->where('department_id', $department_id)
                ->where('is_active','Active')
                ->get();
		*/	
		$data = Section::where('department_id', $department_id)
                ->where('is_active','Active')
                ->get();		
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $data = Section::find($id);
        $company = Company::all();
        $department = Department::all();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.section', ['data' => $data, 'company' => $company, 'department' => $department, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->validate($request, [
            'name' => 'required',
            'company_id' => 'required',
            'Department_id' => 'required',
        ]);
        $tab = Section::find($id);
        $tab->name = $request->name;
        $tab->company_id = $request->company_id;
        $tab->department_id = $request->Department_id;
        $tab->is_active = $request->is_active;
        $tab->save();

        return redirect()->action('SectionController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        $del = Section::destroy($request->id);
        return 1;
    }

}
