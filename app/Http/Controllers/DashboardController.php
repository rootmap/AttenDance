<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
       
    public function index()
    {
      $logged_emp_code = MenuPageController::loggedUser('emp_code');
	  $logged_company_id = MenuPageController::loggedUser('company_id');
	  $logged_role_id = MenuPageController::EmployeeRoleInfo($logged_emp_code,$logged_company_id);
	  
	  $HolidayCalday=DB::table('calendars')
						->leftJoin('day_types','calendars.day_type_id','=','day_types.id')
						->whereIN('day_types.day_short_code',["W","H"])
						->where('calendars.company_id',$logged_company_id)
						->groupBy('calendars.date')
						->get();
	  //echo "<pre>";					
	  //print_r($sqlHolidayCalday);
	  //exit();
	  
	  $logged_role_sql=DB::table('system_access_roles')->where('id',$logged_role_id)->first();
	  $logged_role_name=$logged_role_sql->name;

      $logged_emp_gender = MenuPageController::loggedUser('gender');
      if($logged_emp_gender==0)
      {
          $logged_emp_gender=1;
      }
      //print_r($logged_emp_gender);
      //exit();

      $get_emp_gender = Gender::select('name')->where('id', '=', $logged_emp_gender)->first();

      $allbasicinfo = DB::table('employee_infos')
      ->leftjoin('employee_companies', 'employee_infos.emp_code', '=', 'employee_companies.emp_code')
      ->leftjoin('companies', 'employee_infos.company_id', '=', 'companies.id')
      ->leftjoin('employee_departments', 'employee_infos.emp_code', '=', 'employee_departments.emp_code')
      ->leftjoin('departments', 'employee_departments.department_id', '=', 'departments.id')
      ->leftjoin('employee_designations', 'employee_infos.emp_code', '=', 'employee_designations.emp_code')
      ->leftjoin('designations', 'employee_designations.designation_id', '=', 'designations.id')
      ->leftjoin('employee_sections', 'employee_infos.emp_code', '=', 'employee_sections.emp_code')
      ->leftjoin('sections', 'employee_sections.section_id', '=', 'sections.id')
      ->leftjoin('employee_company_branches', 'employee_infos.emp_code', '=', 'employee_company_branches.emp_code')
      ->leftjoin('company_branches', 'employee_company_branches.branch_id', '=', 'company_branches.id')
      ->leftjoin('marital_statuses', 'employee_infos.marital_status', '=', 'marital_statuses.id')
      ->leftjoin('blood_groups', 'employee_infos.blood_group', '=', 'blood_groups.id')
      ->leftjoin('genders', 'employee_infos.gender', '=', 'genders.id')
      ->leftjoin('countries', 'employee_infos.country', '=', 'countries.id')
      ->leftjoin('cities', 'employee_infos.city', '=', 'cities.id')
      ->leftjoin(DB::raw('employee_supervisors as es'), 'employee_infos.emp_code', '=', DB::raw('es.employee_info_id'))
      ->leftjoin(DB::raw('employee_supervisors as esn'), 'employee_infos.emp_code', '=', DB::raw('esn.employee_info_sup_id'))
      ->select(DB::raw('employee_infos.emp_code,
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
        employee_infos.image as emp_photo,
        employee_infos.email as emp_email,
        employee_infos.phone as emp_phone,
        employee_infos.address emp_address,
        employee_infos.dob as emp_birthdate,
        employee_infos.join_date as emp_join_date,
        blood_groups.name as emp_blood_group,
        marital_statuses.name as emp_marital_status,
        genders.name as emp_gender,
        countries.name as emp_country,
        cities.name as emp_city,
        companies.name as emp_company,
        departments.name as emp_department,
        sections.name as emp_section,
        company_branches.name as emp_job_location,
        designations.name as emp_designation'), DB::raw('concat(esn.employee_info_sup_id,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_supervisor_name'))
      ->where('employee_infos.emp_code', '=', $logged_emp_code)
      ->take(1)
      ->get();

      if($get_emp_gender->name=='Male' || $get_emp_gender->name=='male'){
        $leave_summary=DB::table('leave_assigned_yearly_datas')
        ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

        ->select(DB::raw('leave_policies.leave_title,
          leave_assigned_yearly_datas.total_days,
          leave_assigned_yearly_datas.availed_days,
          leave_assigned_yearly_datas.remaining_days,
          leave_assigned_yearly_datas.incash_balance'))
        ->where('leave_assigned_yearly_datas.emp_code','=',$logged_emp_code)
        ->where('leave_policies.leave_short_code',"!=",'ML')

        ->orderBy('leave_assigned_yearly_datas.id','ASC')

        ->get();
      } elseif ($get_emp_gender->name=='Female' || $get_emp_gender->name=='female') {
        $leave_summary=DB::table('leave_assigned_yearly_datas')
        ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

        ->select(DB::raw('leave_policies.leave_title,
          leave_assigned_yearly_datas.total_days,
          leave_assigned_yearly_datas.availed_days,
          leave_assigned_yearly_datas.remaining_days,
          leave_assigned_yearly_datas.incash_balance'))
        ->where('leave_assigned_yearly_datas.emp_code','=',$logged_emp_code)
        ->where('leave_policies.leave_short_code',"!=",'PL')

        ->orderBy('leave_assigned_yearly_datas.id','ASC')

        ->get();
      }
	  
	  

      // echo '<pre>';
      // print_r($leave_summary);
      // exit();
        return view('module.Dashboard.index',
        [
			'all_basic_info'=>$allbasicinfo,
			'leave_summary'=>$leave_summary,
			'logged_emp_code'=>$logged_emp_code,
			'logged_role_name'=>$logged_role_name,
			'holiday_data'=>$HolidayCalday
		]);
    }
    
    
     public function GetEmployeeDetail(Request $request)
    {
      $logged_emp_code = $request->emp_code;

     

      $allbasicinfo = DB::table('employee_infos')
      ->leftjoin('employee_companies', 'employee_infos.emp_code', '=', 'employee_companies.emp_code')
      ->leftjoin('companies', 'employee_infos.company_id', '=', 'companies.id')
      ->leftjoin('employee_departments', 'employee_infos.emp_code', '=', 'employee_departments.emp_code')
      ->leftjoin('departments', 'employee_departments.department_id', '=', 'departments.id')
      ->leftjoin('employee_designations', 'employee_infos.emp_code', '=', 'employee_designations.emp_code')
      ->leftjoin('designations', 'employee_designations.designation_id', '=', 'designations.id')
      ->leftjoin('employee_sections', 'employee_infos.emp_code', '=', 'employee_sections.emp_code')
      ->leftjoin('sections', 'employee_sections.section_id', '=', 'sections.id')
      ->leftjoin('employee_company_branches', 'employee_infos.emp_code', '=', 'employee_company_branches.emp_code')
      ->leftjoin('company_branches', 'employee_company_branches.branch_id', '=', 'company_branches.id')
      ->leftjoin('marital_statuses', 'employee_infos.marital_status', '=', 'marital_statuses.id')
      ->leftjoin('blood_groups', 'employee_infos.blood_group', '=', 'blood_groups.id')
      ->leftjoin('genders', 'employee_infos.gender', '=', 'genders.id')
      ->leftjoin('countries', 'employee_infos.country', '=', 'countries.id')
      ->leftjoin('cities', 'employee_infos.city', '=', 'cities.id')
      ->leftjoin(DB::raw('employee_supervisors as es'), 'employee_infos.emp_code', '=', DB::raw('es.employee_info_id'))
      ->leftjoin(DB::raw('employee_supervisors as esn'), 'employee_infos.emp_code', '=', DB::raw('esn.employee_info_sup_id'))
      ->select(DB::raw('employee_infos.emp_code,
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
        employee_infos.image as emp_photo,
        employee_infos.email as emp_email,
        employee_infos.phone as emp_phone,
        employee_infos.address emp_address,
        employee_infos.dob as emp_birthdate,
        employee_infos.join_date as emp_join_date,
        blood_groups.name as emp_blood_group,
        marital_statuses.name as emp_marital_status,
        genders.name as emp_gender,
        countries.name as emp_country,
        cities.name as emp_city,
        companies.name as emp_company,
        departments.name as emp_department,
        sections.name as emp_section,
        company_branches.name as emp_job_location,
        designations.name as emp_designation'), DB::raw('concat(esn.employee_info_sup_id,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_supervisor_name'))
      ->where('employee_infos.emp_code', '=', $logged_emp_code)
      ->take(1)
      ->get();

      

      // echo '<pre>';
      // print_r($leave_summary);
      // exit();
        return $allbasicinfo;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
