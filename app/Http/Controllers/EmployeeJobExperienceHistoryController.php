<?php

namespace App\Http\Controllers;

use App\EmployeeJobExperienceHistory;
use Illuminate\Http\Request;

class EmployeeJobExperienceHistoryController extends Controller
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
            'com_name' => 'required',
            'com_address' => 'required',
            'com_desigantion' => 'required',
            'com_responsibility' => 'required',
            'com_s_date' => 'required',
            'com_e_date' => 'required',
            'com_upload' => 'required',
          
            
        ]);
        $job=new EmployeeJobExperienceHistory;
        $job->company_name=$request->com_name;
        $job->company_address=$request->com_address;
        $job->desigantion=$request->com_desigantion;
        $job->responsibility=$request->com_responsibility;
        $job->start_date=$request->com_s_date;
        $job->end_date=$request->com_e_date;
        $job->cirtificateupload=$request->com_upload;
       
        
        $job->save();
        return redirect()->action('EmployeeInfoController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeJobExperienceHistory  $employeeJobExperienceHistory
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeJobExperienceHistory $employeeJobExperienceHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeJobExperienceHistory  $employeeJobExperienceHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeJobExperienceHistory $employeeJobExperienceHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeJobExperienceHistory  $employeeJobExperienceHistory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeJobExperienceHistory $employeeJobExperienceHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmployeeJobExperienceHistory  $employeeJobExperienceHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeJobExperienceHistory $employeeJobExperienceHistory)
    {
        //
    }
}
