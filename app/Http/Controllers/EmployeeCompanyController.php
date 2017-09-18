<?php

namespace App\Http\Controllers;

use App\EmployeeCompany;
use Illuminate\Http\Request;

class EmployeeCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
            'department_id' => 'required',
            'section_id' => 'required',
            'branch_id' => 'required',
            'supervisor' => 'required',
            'effective_date' => 'required',
            'designation_id' => 'required',
            'emp_code' => 'required',
            
        ]);
        $compa=new EmployeeCompany;
        $compa->company_id=$request->company_id;
        $compa->department=$request->department_id;
        $compa->section=$request->section;
        $compa->designation=$request->designation_id;
        $compa->branch=$request->branch_id;
        $compa->supervisor=$request->supervisor;
        $compa->emp_code=$request->emp_code;
        $compa->effective_date=$request->effective_date;
        
        $compa->save();
        return redirect()->action('EmployeeInfoController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeCompany  $employeeCompany
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeCompany $employeeCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeCompany  $employeeCompany
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeCompany $employeeCompany)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeCompany  $employeeCompany
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeCompany $employeeCompany)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmployeeCompany  $employeeCompany
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeCompany $employeeCompany)
    {
        //
    }
}
