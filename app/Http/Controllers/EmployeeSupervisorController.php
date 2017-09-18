<?php

namespace App\Http\Controllers;

use App\EmployeeSupervisor;
use App\Department;
use App\Company;
use App\Designation;
use App\Section;
use App\CompanyBranch;

use Illuminate\Http\Request;

class EmployeeSupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        
         $company = Company::all();
         $department = Department::all();
          $designation = Designation::all();
          $section = Section::all();
          $branch = CompanyBranch::all();
          
          
        return view('module.settings.supervisor',['company'=>$company,'department'=>$department,'section'=>$section,'branch'=>$branch,'designation'=>$designation]);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeSupervisor  $employeeSupervisor
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeSupervisor $employeeSupervisor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeSupervisor  $employeeSupervisor
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeSupervisor $employeeSupervisor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeSupervisor  $employeeSupervisor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeSupervisor $employeeSupervisor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmployeeSupervisor  $employeeSupervisor
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeSupervisor $employeeSupervisor)
    {
        //
    }
}
