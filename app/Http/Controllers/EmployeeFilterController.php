<?php

namespace App\Http\Controllers;

use App\User;
use App\EmployeeCompany;
use App\EmployeeStaffGrade;
use App\EmployeeCompanyBranch;
use App\EmployeeDepartment;
use App\EmployeeDesignation;
use App\EmployeeSection;
use App\EmployeeJobExperienceHistory;
use App\EmployeeEducationalQualification;
use App\EmployeeAssignRole;
use App\EmployeeSupervisor;
use App\InreplacementOf;
use App\EmployeeInfo;
use App\Department;
use App\Company;
use App\Country;
use App\City;
use App\MaritalStatus;
use App\Gender;
use App\BloodGroup;
use App\Designation;
use App\Section;
use App\CompanyBranch;
use App\SystemAccessRole;
use App\EmployeeCode;
use App\StaffGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeFilterController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     //filter
	//filterAllEmployee 
	
	public function filterAllEmployee(Request $request)
    {
      $company_id=$request->company_id;
      $department_id=$request->department_id;
      $section_id=$request->section_id;
      $designation_id=$request->designation_id;
      
      $data=DB::table('employee_infos')

      ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
          concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name'))

      ->orderBy('employee_infos.emp_code','ASC')

      ->get();


      //return response()->json(array("data"=>$json,"total"=>count($json)));
        return response()->json($data);
    }
	
    public function filterEmployee(Request $request)
    {
      $company_id=$request->company_id;
      $department_id=$request->department_id;
      $section_id=$request->section_id;
      $designation_id=$request->designation_id;
      
      $data=DB::table('employee_infos')

      ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
      ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

      ->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
      ->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

      ->leftjoin('companies','companies.id','=','employee_companies.company_id')
      ->leftjoin('departments','departments.id','=','employee_departments.department_id')

      ->leftjoin('sections','sections.id','=','employee_sections.section_id')
      ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

      ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
          concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name'))

      ->when($company_id, function ($query) use ($company_id) {
          return $query->where('employee_infos.company_id',$company_id);
      })

      ->when($department_id, function ($query) use ($department_id) {
          return $query->where('employee_departments.department_id',$department_id);
      })

      ->when($section_id, function ($query) use ($section_id) {
          return $query->where('employee_sections.section_id',$section_id);
      })

      ->when($designation_id, function ($query) use ($designation_id) {
          return $query->where('employee_designations.designation_id',$designation_id);
      })

      ->groupBy('employee_infos.emp_code')

      ->orderBy('employee_infos.emp_code','DESC')

      ->get();


      //return response()->json(array("data"=>$json,"total"=>count($json)));
        return response()->json($data);
    }


    public function getCompanyEmployees($company_id=0)
    {

      $data=DB::table('employee_infos')

      ->select(DB::raw('employee_infos.emp_code as emp_code,
                        concat(employee_infos.emp_code,"-",employee_infos.first_name," ",employee_infos.last_name) AS emp_name'))
      ->when($company_id, function ($query) use ($company_id) {
          return $query->where('employee_infos.company_id',$company_id);
      })

      ->groupBy('employee_infos.emp_code')

      ->orderBy('employee_infos.emp_code','DESC')

      ->get();


      return response()->json(array("data"=>$data,"total"=>count($data)));
        //return response()->json($data);
    }


    public function index() {
        //
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
      //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeInfo  $employeeInfo
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeInfo $employeeInfo) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeInfo  $employeeInfo
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeInfo  $employeeInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeInfo $employeeInfo) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmployeeInfo  $employeeInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeInfo $employeeInfo) {
        //
    }

}
