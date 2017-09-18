<?php

namespace App\Http\Controllers;

use App\EmployeeEducationalQualification;
use Illuminate\Http\Request;

class EmployeeEducationalQualificationController extends Controller
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
            'certification' => 'required',
            'institute' => 'required',
            'institute_add' => 'required',
            'result' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'edu_upload' => 'required',
            
        ]);
        $job=new EmployeeJobExperienceHistory;
        $job->certification=$request->certification;
        $job->institute=$request->institute;
        $job->institute_add=$request->institute_add;
        $job->result=$request->result;
        $job->start_date=$request->start_date;
        $job->end_date=$request->end_date;
        $job->cirtificateupload=$request->edu_upload;
       
        
        $job->save();
        return redirect()->action('EmployeeInfoController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeEducationalQualification  $employeeEducationalQualification
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeEducationalQualification $employeeEducationalQualification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeEducationalQualification  $employeeEducationalQualification
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeEducationalQualification $employeeEducationalQualification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeEducationalQualification  $employeeEducationalQualification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeEducationalQualification $employeeEducationalQualification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmployeeEducationalQualification  $employeeEducationalQualification
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeEducationalQualification $employeeEducationalQualification)
    {
        //
    }
}
