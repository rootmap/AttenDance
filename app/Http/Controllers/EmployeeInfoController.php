<?php

namespace App\Http\Controllers;

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
use App\LeavePolicy;
use App\LeaveAssignedYearlyData;
use Illuminate\Http\Request;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\EmployeeWeekendDifferentCompanyPolicy;
use App\EmployeeEmploymentType;
use App\EmploymentType;

class EmployeeInfoController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function emp_code() {
        $obj = new EmployeeCodeController();
        return $obj->filterEmployeeCode();
    }

    /* public function Insertemp_code($compnay_id) {
      $obj = new EmployeeCodeController();
      $emp = $obj->filterEmployeeCode();
      $Epm_code = new EmployeeCode();
      $Epm_code->company_id = $compnay_id;
      $Epm_code->emp_code = $emp;
      $Epm_code->raw_emp_code = $emp;
      //$Epm_code->prefix = $preFix;
      $Epm_code->save();
      } */
	  
	public function oldDataMerge()
	{
		ini_set('max_execution_time', 72000);
		$sqlLoadData=EmployeeInfo::whereRaw('emp_code NOT IN (SELECT emp_code FROM dummy_employee)')->select('emp_code')->take('500')->get();
		//$sqlLoadData=EmployeeInfo::where('emp_code','RPAC0578')->select('emp_code')->take('500')->get();
		
		//echo "<pre>";
		//print_r($sqlLoadData);
		//exit();
		foreach($sqlLoadData as $ems)
		{
		$response = file_get_contents('http://192.168.1.20/rpac_payroll/admin/view/employee/emp_api.php?emp_code='.$ems->emp_code);
		$response=json_decode($response,true);
		//$chkEmp=DB::table('dummy_employee')->where('emp_code',$response['emp_code'])->count();
		//if($chkEmp==0)
		//{
			
			
			
			$new_company_id=0;
			$ex_company=$response['company_id'];
			$new_company_id=$this->ProcessRawCompany($response['company_id']);
			
			$new_attn_company_id=$this->ProcessRawCompany($response['emp_attn_company']);
			
			$TEXTgEN=$this->RenderGender($response['emp_gender']);
			//echo $TEXTgEN;
			//exit();
			$is_pf_eligible=0;
			if(!empty($response['is_pf_eligible']))
			{
				if($response['is_pf_eligible']=="yes")
				{
					$is_pf_eligible=1;
				}
				else
				{
					$is_pf_eligible=0;
				}
			}
			
			$blood_group_id=$this->OldToNewBloodGroup($response['emp_blood_group']);
			$marital_status_id=$this->OldToNewMaritalStatus($response['emp_marital_status']);
			$gender_id=$this->OldToNewGender($TEXTgEN);
			$country_id=$this->OldToNewCountry($response['country']);
			$city_id=$this->OldToNewCity($response['city'],$country_id);
			$upd=[
					'company_id'=>$new_company_id,
					'first_name'=>$response['emp_firstname'],
					'last_name'=>$response['emp_lastname'],
					'email'=>$response['emp_email'],
					'phone'=>$response['emp_contact_number'],
					'address'=>$response['emp_address'],
					'dob'=>$response['emp_dateofbirth'],
					'join_date'=>$response['emp_dateofjoin'],
					'blood_group'=>$blood_group_id,
					'marital_status'=>$marital_status_id,
					'gender'=>$gender_id,
					'country'=>$country_id,
					'city'=>$city_id
				  ];
																		  
			if($new_company_id!=0)
			{
			
				EmployeeInfo::where('emp_code',$response['emp_code'])->update($upd);
																			  
				$this->SyncStaffGrade($response['emp_staff_grade'],$new_company_id,$response['emp_code']);
				
				$dep_id=$this->SyncDep($response['emp_department'],$new_company_id,$response['emp_code']);  //To Merge Employee Department and get return department id
				$sec_id=$this->SyncSection($response['emp_subsection'],$new_company_id,$response['emp_code'],$dep_id);  //To Merge Employee Department and get return section id
				$des_id=$this->SyncDes($response['emp_designation'],$new_company_id,$response['emp_code'],$dep_id,$sec_id);  //To Merge Employee Department and get return section id
				$this->SyncStaffGrade($response['emp_staff_grade'],$new_company_id,$response['emp_code']); //To Merge Staff Grade
				$this->SyncBranch($response['job_location'],$new_company_id,$response['emp_code']); //To Merge Staff Grade
				$this->SyncInReplacement($response['emp_replacement_of'],$new_company_id,$response['emp_code']); //To Merge Staff Grade
				$this->SyncSupervisor($response['emp_supervisor'],$new_company_id,$response['emp_code']); //To Merge Staff Grade
				$this->SyncWeekendDifferentCompany($new_attn_company_id,$response['emp_code'],$response['emp_attn_implement_from_date']); //To Merge Staff Grade
				$this->SyncExCompanyAndOthers($new_company_id,$response['emp_code'],$response['emp_prop_confirmation_date'],$response['emp_prop_confirmation_date'],$response['pf_effective_from'],$is_pf_eligible);

					DB::table('dummy_employee')->insert([
							'emp_code'=>$response['emp_code']
					  ]);
			}		
			else
			{
				
				DB::table('dummy_invalidEmployee')->insert([
							'emp_code'=>$response['emp_code']
				]);
			}
		//}
		echo "<pre>";
		//print_r($upd);
		print_r($response['emp_code']);
		//exit();
		
		}
	}
	
	public function MakePesHead($id=0)
	{
		$ret_com='';
		if(!empty($id))
		{
			if($id=="6"){ $ret_com='salary_basic'; }
			elseif($id=="7"){ $ret_com='salary_medical'; }
			elseif($id=="8"){ $ret_com='salary_hra'; }
			elseif($id=="16"){ $ret_com='salary_conveyance'; }
			elseif($id=="17"){ $ret_com='salary_gross_salary'; }
			elseif($id=="18"){ $ret_com='salary_lunch'; }
			else{ $ret_com='salary_none'; }
		}
		else
		{
			$ret_com='salary_none';
		}
		
		return $ret_com;
		
	}
	
	public function PushPayroll($fid='',$emp_code=0,$fid_value=0)
	{
		$created_by=date('Y-m-d H:i:s');
			
		$chkComponent=DB::table('employee_payroll_infos')
		->where('emp_code',$emp_code)
		->where('component_field',$fid)
		->count();
		
		if($chkComponent==0)
		{
			DB::table('employee_payroll_infos')
			->insert([
				'emp_code'=>$emp_code,
				'component_field'=>$fid,
				'component_value'=>$fid_value,
				'created_by'=>$created_by
			]);
		}
	}
	
	public function oldPayrollData()
	{
		$sql=DB::table('payroll_employee_salary')
			 ->select('PES_employee_code','PES_PSH_id','PES_amount')
			 ->take(1)
			 ->get();
		foreach($sql as $row){
			$emp_code=$row->PES_employee_code;
			$fid_value=$row->PES_amount;
			$fid=$this->MakePesHead($row->PES_PSH_id);
			$this->PushPayroll($fid,$emp_code,$fid_value=0);
			print_r($row);
		}
		
		exit();
	}
	
	public function ProcessRawCompany($ex_company=0)
	{
		if($ex_company==1)
		{
			$new_company_id=11;
		}
		elseif($ex_company==2)
		{
			$new_company_id=12;
		}
		elseif($ex_company==4)
		{
			$new_company_id=13;
		}
		elseif($ex_company==5)
		{
			$new_company_id=14;
		}
		else
		{
			$new_company_id=0;
		}
		
		return $new_company_id;
	}
	
	
	public function SyncExCompanyAndOthers($company_id=0,$emp_code=0,$effective_from='0000-00-00',$proposed_confirmation_date='0000-00-00',$pf_effective_from='0000-00-00',$is_pf_eligible=0)
	{
		$doingOk=0;
			
			$compa = new EmployeeCompany();
			$compa->company_id = $company_id;
			$compa->emp_code = $emp_code;
			$compa->company_effective_start_date = $effective_from;
			$compa->company_effective_end_date = '0000-00-00';
			$compa->proposed_confirmation_date =$proposed_confirmation_date;
			$compa->pf_effective_from =$pf_effective_from;
			$compa->is_pf_eligible =$is_pf_eligible;
			$compa->status = '1';
			$compa->save();
		
			$doingOk=1;
		
		return $doingOk;
	}
	
	public function SyncWeekendDifferentCompany($company_id=0,$emp_code=0,$effective_from='0000-00-00')
	{
		$doingOk=0;
		if (!empty($company_id)) {
			
			$weekend = new EmployeeWeekendDifferentCompanyPolicy;
			$weekend->company_id = $company_id;
			$weekend->emp_code = $emp_code;
			$weekend->effective_from = $effective_from;
			$weekend->save();
		
			$doingOk=1;
		}
		
		return $doingOk;
	}
	
	public function SyncSupervisor($supervisor='0',$company_id=0,$emp_code=0)
	{
		$doingOk=0;
		if (!empty($supervisor)) {
			
			//echo $company_id;
			//exit();
			$sup = new EmployeeSupervisor();
			$sup->company_id = $company_id;
			$sup->employee_info_id = $emp_code;
			$sup->employee_info_sup_id = $supervisor;
			$sup->save();
			
			
		
			$doingOk=1;
		}
		
		return $doingOk;
	}
	
	public function SyncInReplacement($inReplacement='0',$company_id=0,$emp_code=0)
	{
		$doingOk=0;
		if (!empty($inReplacement)) {
			
			

				$inReplace = new InreplacementOf();
				$inReplace->emp_code = $emp_code;
				$inReplace->company_id = $company_id;
				$inReplace->replacement_of_emp_code = $inReplacement;
				$inReplace->save();
			
				$doingOk=1;
		}
		
		return $doingOk;
	}
	
	public function SyncBranch($name='',$company_id=0,$emp_code=0)
	{
		$doingOk=0;
		if (!empty($name)) {
			
			$chk=DB::table('company_branches')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('company_branches')
				->insert([
				'name'=>$name,
				'company_id'=>$company_id,
				'is_active'=>'Active'
				]);
			}
			
			
			$stfN=DB::table('company_branches')->where('name',$name)->first();
			
				$branch = new EmployeeCompanyBranch();
				$branch->branch_id = $stfN->id;
				$branch->emp_code = $emp_code;
				$branch->company_id = $company_id;
				$branch->status = '1';
				$branch->save();
				$doingOk=1;
			
			
		}
		
		return $doingOk;
	}
	
	
	
	
	public function SyncDes($name='',$company_id=0,$emp_code=0,$dep_id=0,$sec_id=0)
	{
		$doingOk=0;
		if (!empty($name)) {
			
			$chk=DB::table('designations')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('designations')
				->insert([
				'name'=>$name,
				'company_id'=>$company_id,
				'department_id'=>$dep_id,
				'section_id'=>$sec_id,
				'is_active'=>'Active'
				]);
			}
			
			
			$stfN=DB::table('designations')->where('name',$name)->first();
			$chkSFN=EmployeeDesignation::where('emp_code',$emp_code)
			->where('designation_id',$stfN->id)
			->where('department_id',$dep_id)
			->count();
			
				$desi = new EmployeeDesignation();
                $desi->designation_id = $stfN->id;
                $desi->emp_code = $emp_code;
                $desi->company_id = $company_id;
                $desi->department_id = $dep_id;
                $desi->status = '1';
                $desi->save();
				$doingOk=$stfN->id;
			
			
		}
		
		return $doingOk;
	}
	
	public function SyncSection($name='',$company_id=0,$emp_code=0,$dep_id=0)
	{
		$doingOk=0;
		if (!empty($name)) {
			
			$chk=DB::table('sections')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('sections')
				->insert([
				'name'=>$name,
				'company_id'=>$company_id,
				'department_id'=>$dep_id,
				'is_active'=>'Active'
				]);
			}
			
			
			$stfN=DB::table('sections')->where('name',$name)->first();
			$chkSFN=EmployeeSection::where('emp_code',$emp_code)->where('section_id',$stfN->id)->count();
			//if($chkSFN==0)
			//{
				$sec = new EmployeeSection();
                $sec->section_id = $stfN->id;
                $sec->emp_code = $emp_code;
                $sec->company_id = $company_id;
                $sec->department_id = $dep_id;
                $sec->status = '1';
                $sec->save();
				$doingOk=$stfN->id;
			//}
			//else
			//{
			//	$doingOk=$stfN->id;
			//}
			
		}
		
		return $doingOk;
	}
	
	public function SyncDep($name='',$company_id=0,$emp_code=0)
	{
		$doingOk=0;
		if (!empty($name)) {
			
			$chk=DB::table('departments')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('departments')
				->insert([
				'name'=>$name,
				'company_id'=>$company_id,
				'is_active'=>'Active'
				]);
			}
			
			
			$stfN=DB::table('departments')->where('name',$name)->first();
			//$chkSFN=EmployeeDepartment::where('emp_code',$emp_code)->where('department_id',$stfN->id)->count();
			//if($chkSFN==0)
			//{
				$dept = new EmployeeDepartment();
				$dept->department_id = $stfN->id;
				$dept->emp_code = $emp_code;
				$dept->company_id = $company_id;
				$dept->status = '1';
				$dept->save();
				$doingOk=$stfN->id;
			//}
			//else
			//{
			//	$doingOk=$stfN->id;
			//}
			
		}
		
		return $doingOk;
	}
	
	public function SyncStaffGrade($name='',$company_id=0,$emp_code=0)
	{
		$doingOk=0;
		if (!empty($name)) {
			
			$chk=DB::table('staff_grades')->where('name',$name)->count();
			if($chk!=0)
			{
				$stfN=DB::table('staff_grades')->where('name',$name)->first();
				$chkSFN=EmployeeStaffGrade::where('emp_code',$emp_code)->where('staff_grade_id',$stfN->id)->count();
				//if($chkSFN==0)
				//{
					$staffGrade = new EmployeeStaffGrade();
					$staffGrade->emp_code = $emp_code;
					$staffGrade->company_id = $company_id;
					$staffGrade->staff_grade_id = $stfN->id;
					$staffGrade->staffgrade_effective_start_date = '0000-00-00';
					$staffGrade->staffgrade_effective_end_date = '0000-00-00';
					$staffGrade->status = '1';
					$staffGrade->save();
					$doingOk=1;
				//}
			}
		}
		
		return $doingOk;
	}
	
	public function RenderGender($emp_gender)
	{
		$txtGend=$emp_gender;
		if(isset($emp_gender))
		{
			if(!empty($emp_gender))
			{
				if($emp_gender=="M")
				{
					$txtGend="Male";
				}
				elseif($emp_gender=="m")
				{
					$txtGend="Male";
				}
				elseif($emp_gender=="F")
				{
					$txtGend="Female";
				}
				elseif($emp_gender=="f")
				{
					$txtGend="Female";
				}
				else
				{
					$txtGend=$emp_gender;
				}
			}
		}
		
		return $txtGend;
	}
	
	public function OldToNewCity($name='',$country_id=0)
	{
		$bid=0;
		if(!empty($name))
		{
			$chk=DB::table('cities')->where('name',$name)->where('country_id',$country_id)->count();
			if($chk==0)
			{
				DB::table('cities')->insert(['name'=>$name,'country_id'=>$country_id]);
			}
				
			$blinfo=DB::table('cities')->where('name',$name)->where('country_id',$country_id)->first();
			if(isset($blinfo))
			{
				$bid=$blinfo->id;
			}
		}

		return $bid;
		
	}
	
	public function OldToNewCountry($name='')
	{
		$bid=0;
		if(!empty($name))
		{
			$chk=DB::table('countries')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('countries')->insert(['name'=>$name]);
			}
				
			$blinfo=DB::table('countries')->where('name',$name)->first();
			if(isset($blinfo))
			{
				$bid=$blinfo->id;
			}
		}

		return $bid;
		
	}
	
	public function OldToNewGender($name='')
	{
		$bid=0;
		if(!empty($name))
		{
			$chk=DB::table('genders')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('genders')->insert(['name'=>$name]);
			}
				
			$blinfo=DB::table('genders')->where('name',$name)->first();
			if(isset($blinfo))
			{
				$bid=$blinfo->id;
			}
		}

		return $bid;
		
	}
	
	
	public function OldToNewMaritalStatus($name='')
	{
		$bid=0;
		if(!empty($name))
		{
			$chk=DB::table('marital_statuses')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('marital_statuses')->insert(['name'=>$name]);
			}
				
			$blinfo=DB::table('marital_statuses')->where('name',$name)->first();
			if(isset($blinfo))
			{
				$bid=$blinfo->id;
			}
		}

		return $bid;
		
	}
	
	public function OldToNewBloodGroup($name='')
	{
		//echo $name;
		//exit();
		$bid=0;
		if(!empty($name))
		{
			$chk=DB::table('blood_groups')->where('name',$name)->count();
			if($chk==0)
			{
				DB::table('blood_groups')->insert(['name'=>$name]);
			}
				
			$blinfo=DB::table('blood_groups')->where('name',$name)->first();
			if(isset($blinfo))
			{
				$bid=$blinfo->id;
			}
			
				
			
		}
		//echo $bid;
		//exit();
		return $bid;
		
	}

    public function index() {


        $emp = $this->emp_code();
		
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $company = Company::whereIn('id',$RoleAssignedCompany)->where('is_active', '1')->get();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
		$component=DB::table('payroll_salary_components')->orderBy('display_order','ASC')->get();
		$componentPlus=DB::table('payroll_salary_components')->where('headerDisplayOn','+')->orderBy('display_order','ASC')->get();
		$componentMinus=DB::table('payroll_salary_components')->where('headerDisplayOn','-')->orderBy('display_order','ASC')->get();
		$componentGross=DB::table('payroll_salary_components')->where('is_gross','1')->orderBy('display_order','ASC')->first();
		
		$componentNonFunctional=DB::table('payroll_salary_components')
								->whereNotIN('is_gross',[1])
								->whereNotIN('headerDisplayOn',['+','-'])
								->orderBy('display_order','ASC')->get();
		
        if (!empty($logged_emp_company_id)) {
            //$company = Company::find($logged_emp_company_id);
            $role = SystemAccessRole::all();
            $supervisor = EmployeeInfo::whereIn('company_id',$RoleAssignedCompany)->get();
            $country = Country::all();
            $marital_status = MaritalStatus::all();
            $gender = Gender::all();
            $bloodGroup = BloodGroup::all();
            $staff = StaffGrade::whereIn('company_id',$RoleAssignedCompany)->where('is_active', '1')->get();
        } else {
            //$company = Company::where('is_active', '1')
                    //->get();
            $role = SystemAccessRole::all();
            $supervisor = EmployeeInfo::all();
            $country = Country::all();
            $marital_status = MaritalStatus::all();
            $gender = Gender::all();
            $bloodGroup = BloodGroup::all();
            $staff = StaffGrade::where('is_active', '1')
                    ->get();
        }
		
		$emType=EmploymentType::all();
		//echo "<pre>";
		//print_r($company);
		//exit();
		

        //$department = Department::all();
        //$city = City::all();
        //$designation = Designation::all();
        //$section = Section::all();
        //$branch = CompanyBranch::all();


        return view('module.Employee.employeeInfo', [
            'emp_Code' => $emp,
            'com' => $company,
            'staff' => $staff,
            'country' => $country,
            'marital_status' => $marital_status,
            'gender' => $gender,
            'bloodGroup' => $bloodGroup,
            'role' => $role,
            'emp' => $role,
            'logged_emp_com' => $logged_emp_company_id,
            'supervisor' => $supervisor,
            'inRplace' => $supervisor,
			'emtype' => $emType,
			'componentPlus' => $componentPlus,
			'componentMinus' => $componentMinus,
			'componentGross' => $componentGross,
			'componentNonFunctional' => $componentNonFunctional,
			'component'=>$component]);
    }

    //For Showing Employee Profile Detail
    ///employee_companies
    public function showDetail($emp_code) {
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
                ->select(DB::raw('employee_infos.emp_code,
      concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
      employee_infos.image as emp_photo,
      employee_infos.email as emp_email,
      employee_infos.phone as emp_phone,
      employee_infos.address emp_address,
      employee_infos.dob as emp_birthdate,
      blood_groups.name as emp_blood_group,
      marital_statuses.name as emp_marital_status,
      genders.name as emp_gender,
      countries.name as emp_country,
      cities.name as emp_city,
      companies.name as emp_company,
      departments.name as emp_department,
      sections.name as emp_section,
      company_branches.name as emp_job_location,
      designations.name as emp_designation,
	  (SELECT concat(sdm.emp_code," - ",sdm.first_name," ",IFNULL(sdm.last_name,"")) FROM employee_infos as sdm WHERE emp_code=(SELECT employee_info_sup_id FROM employee_supervisors WHERE employee_info_id=employee_infos.emp_code ORDER BY id DESC LIMIT 1)) as emp_supervisor_name'))
	  
                ->where('employee_infos.emp_code', '=', $emp_code)
				->groupBy('employee_infos.emp_code')
                ->get();
		
		//echo "<pre>";
		//print_r($allbasicinfo);
		///exit();
		
		//->select(DB::raw('(SELECT employee_info_sup_id FROM employee_supervisors WHERE employee_info_id=employee_infos.emp_code) as emp_supervisor_name'))
		
        //for educational Information
        $eduinfo = EmployeeEducationalQualification::where('emp_code', $emp_code)
                ->get();


        //for job experience Information
        $jobexinfo = EmployeeJobExperienceHistory::where('emp_code', $emp_code)
                ->get();

        return view('module.Employee.employeeProfile', array("allbasicinfo" => $allbasicinfo, "eduinfo" => $eduinfo, "jobexinfo" => $jobexinfo));
    }

    //Export PDF Employee Profile
    public function exportProfilePdf($emp_code = 0) {
        //$emp_code = $request->emp_code;
        $img_path = "upload";
        //Query Starts Here
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
                ->where('employee_infos.emp_code', '=', $emp_code)
                ->get();

        //for educational Information
        $eduinfos = EmployeeEducationalQualification::where('emp_code', $emp_code)
                ->get();


        //for job experience Information
        $jobexinfos = EmployeeJobExperienceHistory::where('emp_code', $emp_code)
                ->get();

        //Query Ends Here

        if (!empty($allbasicinfo)) {
            $content = '<h3>Employee Profile Detail Information</h3>';
            $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
            // instantiate and use the dompdf class
            //Basic info Starts here
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            $content .='<th colspan="3"><h4 style="font-weight:bold;">Basic Information</h4></th>';
            $content .='</tr>';
            $content .='</thead>';

            $content .='<tbody>';
            $content .='<tr>';
            $content .='<td rowspan="5"><img style="max-width:250px; margin:0 auto;" src="' . url("upload/employee_image") . '/' . $allbasicinfo[0]->emp_photo . '" alt="Image Not Found"></td>';
            $content .='<td>Employee code:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_code . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>Date Of Birth:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_birthdate . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>Blood Group:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_blood_group . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>Gender:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_gender . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>Marital Status:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_marital_status . '</td>';
            $content .='</tr>';

            $content .='</tbody>';

            $content .='</table>';

            $content .='<br />';
            //Ends Here
            //Contact Information Starts Here
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            $content .='<th colspan="4"><h4 style="font-weight:bold;">Contact Information</h4></th>';
            $content .='</tr>';
            $content .='</thead>';

            $content .='<tbody>';
            $content .='<tr>';
            $content .='<td>Email:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_email . '</td>';
            $content .='<td>Phone:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_phone . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>Address:</td>';
            $content .='<td style="font-weight:bold;" colspan="3">' . $allbasicinfo[0]->emp_address . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>City:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_city . '</td>';
            $content .='<td>Country:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_country . '</td>';
            $content .='</tr>';

            $content .='</tbody>';

            $content .='</table>';

            $content .='<br />';
            //Ends here
            //Job Information Starts Here
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            $content .='<th colspan="4"><h4 style="font-weight:bold;">Job Detail</h4></th>';
            $content .='</tr>';
            $content .='</thead>';

            $content .='<tbody>';
            $content .='<tr>';
            $content .='<td>Designation:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_designation . '</td>';
            $content .='<td>Department:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_department . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>Section:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_section . '</td>';
            $content .='<td>Job Location:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_job_location . '</td>';
            $content .='</tr>';

            $content .='<tr>';
            $content .='<td>Company:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_company . '</td>';
            $content .='<td>Supervisor:</td>';
            $content .='<td style="font-weight:bold;">' . $allbasicinfo[0]->emp_supervisor_name . '</td>';
            $content .='</tr>';

            $content .='</tbody>';

            $content .='</table>';

            $content .='<br />';
            //Ends
        }


        //echo $content;
        //exit();
        //Educational Information starts here
        $eduArray = [
            'certification_name',
            'institute',
            'institute_add',
            'result',
            'start_date',
            'end_date'];
        /* ,
          'cirtificateupload' */

        if (!empty($eduArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            $content .='<th colspan="6"><h4 style="font-weight:bold;">Educational Qualification</h4></th>';
            $content .='</tr>';
            $content .='<tr>';
            $rowed = count($eduArray);
            foreach ($eduArray as $eduhead):

                $content .='<th>' . $eduhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';

            if (!empty($eduinfos)) {
                $content .='<tbody>';
                foreach ($eduinfos as $eduraw):
                    $content .='<tr>';
                    for ($i = 0; $i <= $rowed - 1; $i++):
                        $eid = $eduArray[$i];
                        $content .='<td>' . $eduraw->$eid . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';
            $content .='<br />';
        }
        //Educational Information Ends here
        //Job Experience Information starts here
        $jobexArray = [
            'company_name',
            'company_address',
            'desigantion',
            'responsibility',
            'start_date',
            'end_date'];
        /* ,
          'cirtificateupload' */

        if (!empty($jobexArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            $content .='<th colspan="6"><h4 style="font-weight:bold;">Job Experience History</h4></th>';
            $content .='</tr>';
            $content .='<tr>';
            $rowj = count($jobexArray);
            foreach ($jobexArray as $jobexhead):

                $content .='<th>' . $jobexhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';

            if (!empty($jobexinfos)) {
                $content .='<tbody>';
                foreach ($jobexinfos as $jobexraw):
                    $content .='<tr>';
                    for ($i = 0; $i <= $rowj - 1; $i++):
                        $jid = $jobexArray[$i];
                        $content .='<td>' . $jobexraw->$jid . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';
        }
        //Job Experience Information Ends here
        //echo $content;
        //print_r($excelArray);
        //exit();
        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($content);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'Protrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }

    //End

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
            'fname' => 'required',
            // 'lname' => 'required',
            'email' => 'required|email',
            // 'phone' => 'required',
            'address' => 'required',
            // 'city_id' => 'required',
            // 'country_id' => 'required',
            // 'blood_id' => 'required',
            'gender_id' => 'required',
            // 'marital_id' => 'required',
            // 'DOB' => 'required',
            'company_id' => 'required',
            'department_id' => 'required',
            'section_id' => 'required',
            // 'branch_id' => 'required',
            'supervisor' => 'required',
            // 'effective_date' => 'required',
            'designation_id' => 'required',
            'emp_code' => 'required'
                //'com_upload' => 'required',
                // 'edu_upload' => 'required',
        ]);
		
		//echo "<h1>Employee is ON Development, Please WAIT.</h1>";
		$created_by = MenuPageController::loggedUser('emp_code');
		$component=DB::table('payroll_salary_components')->orderBy('display_order','ASC')->get();
		foreach($component as $cop)
		{
			$fid="salary_".strtolower(str_replace(' ','_',$cop->header_title));
			$fid_value=$request->$fid;
			
			$chkComponent=DB::table('employee_payroll_infos')
			->where('emp_code',$request->emp_code)
			->where('component_field',$fid)
			->count();
			
			if($chkComponent==0)
			{
				DB::table('employee_payroll_infos')
				->insert([
					'emp_code'=>$request->emp_code,
					'component_field'=>$fid,
					'component_value'=>$fid_value,
					'created_by'=>$created_by
				]);
			}
				
		}
		

		
		//echo "<pre>";
		//print_r($component);
		
		//exit();

        $is_pf_eligible = 0;
        $pf_effective_date = '';
		
		$weekend_policy_as_diff_company = 0;
		$wp_effective_from = '';
		$weekend_policy_as_diff_company_eligible = $request->weekend_policy_as_diff_company ? $request->weekend_policy_as_diff_company : '0';
		if (!empty($weekend_policy_as_diff_company_eligible)) {

			$this->validate($request, [
				'wp_effective_from' => 'required',
			]);
		}

        $pf_eligible = $request->pf_eligible ? $request->pf_eligible : '0';
        if (!empty($pf_eligible)) {
            $is_pf_eligible = 1;
            $pf_effective_date = $request->pf_effective_from;
            $this->validate($request, [
                'pf_effective_from' => 'required',
            ]);
        } else {
            $is_pf_eligible = 0;
            $pf_effective_date = '0000-00-00';
        }

        $chk_user_ex = User::where('username', $request->emp_code)->count();

        if ($chk_user_ex == 0) {
            //get company leave policies
            $sqlLeavePolicy = leavePolicy::all();

            $leave_policy_exists = count($sqlLeavePolicy);
            if ($leave_policy_exists != 0) {

                $register = new User();
                $register->name = $request->fname;
				$register->username = $request->emp_code;
                $register->email = $request->email;
                $register->password = bcrypt($request->password);
                $register->save();

                //raw and prefix save to database
                $getPrefix = MenuPageController::loggedUser('company_prefix');
                $raw_emp_code = str_replace($getPrefix, "", $request->emp_code);
                $tabPushCode = new EmployeeCode;
                $tabPushCode->company_id = $request->company_id;
                $tabPushCode->raw_emp_code = $raw_emp_code;
                $tabPushCode->prefix = $getPrefix;
                $tabPushCode->emp_code = $request->emp_code;
                $tabPushCode->save();
                //raw emp code and prefix save to database

                $compa = new EmployeeCompany();
                $compa->company_id = $request->company_id;
                $compa->emp_code = $request->emp_code;
                $compa->company_effective_start_date = $request->effective_date;
                $compa->company_effective_end_date = $request->effective_date;
                $compa->proposed_confirmation_date = $request->pc_date;
                $compa->pf_effective_from = $pf_effective_date;
                $compa->is_pf_eligible = $is_pf_eligible;
                $compa->status = '1';
                $compa->save();

                $is_ot_eligible = $request->is_ot_eligible ? $request->is_ot_eligible : '0';
                $image = '';
                $basic = new EmployeeInfo();
                if (!empty($request->emp_image)) {
                    $name = "emp_" . time() . '.' . $request->emp_image->getClientOriginalExtension();
                    $image = $name;
                    $request->emp_image->move("./upload/employee_image", $name);
                }
                $basic->company_id = $request->company_id;
                $basic->user_id = $register->id;
                $basic->is_ot_eligible = $is_ot_eligible;
                $basic->emp_code = $request->emp_code;
                $basic->first_name = $request->fname;
                $basic->last_name = $request->lname;
                $basic->email = $request->email;
                $basic->phone = $request->phone;
                $basic->address = $request->address;
                $basic->city = $request->city_id;
                $basic->country = $request->country_id;
                $basic->blood_group = $request->blood_id;
                $basic->gender = $request->gender_id;
                $basic->marital_status = $request->marital_id;
				if(empty($request->DOB))
				{
					$basic->dob = '0000-00-00';
				}
				else
				{
					$basic->dob = $request->DOB;
				}
                
                $basic->join_date = $request->join_date;
                if (!empty($request->emp_image)) {
                    $basic->image = $image;
                }
                $basic->save();
				
				if(isset($request->employee_employment_type_id))
				{
					$dept = new EmployeeEmploymentType();
					$dept->emp_code = $request->emp_code;
					$dept->employee_employment_type_id = $request->employee_employment_type_id;
					$dept->save();
				}
				
                $empAssignRole = new EmployeeAssignRole();
                $empAssignRole->emp_code = $request->emp_code;
                $empAssignRole->company_id = $request->company_id;
                $empAssignRole->system_access_role_id = $request->assignRole;
                $empAssignRole->is_active = '1';
                $empAssignRole->save();
				
				if (!empty($weekend_policy_as_diff_company_eligible)) {

                    $wp_effective_from = $request->wp_effective_from;
                    $emp_weekend_info = EmployeeWeekendDifferentCompanyPolicy::where('emp_code', $request->emp_code)
                                    ->where('company_id', $request->company_id)
                                    ->where('effective_from', '>=', $request->wp_effective_from)->count();
                    // echo $emp_weekend_info;

                    if ($emp_weekend_info == 0) {
                        $weekend = new EmployeeWeekendDifferentCompanyPolicy;
                        $weekend->company_id = $request->wp_company_id;
                        $weekend->emp_code = $request->emp_code;
                        $weekend->effective_from = $request->wp_effective_from;
                        $weekend->save();
                    }
                }
				

                if (!empty($request->inReplacement)) {
                    $inReplace = new InreplacementOf();
                    $inReplace->emp_code = $request->emp_code;
                    $inReplace->company_id = $request->company_id;
                    $inReplace->replacement_of_emp_code = $request->inReplacement;
                    $inReplace->save();
                } else {
                    $inReplace = new InreplacementOf();
                    $inReplace->emp_code = $request->emp_code;
                    $inReplace->company_id = $request->company_id;
                    $inReplace->replacement_of_emp_code = '';
                    $inReplace->save();
                }



                $dept = new EmployeeDepartment();
                $dept->department_id = $request->department_id;
                $dept->emp_code = $request->emp_code;
                $dept->company_id = $request->company_id;
                // $dept->department_effective_start_date = $request->effective_date;
                // $dept->department_effective_end_date = $request->effective_date;
                $dept->status = '1';
                $dept->save();


                $sec = new EmployeeSection();
                $sec->section_id = $request->section_id;
                $sec->emp_code = $request->emp_code;
                $sec->company_id = $request->company_id;
                $sec->department_id = $request->department_id;
                // $sec->section_effective_start_date = $request->effective_date;
                // $sec->section_effective_end_date = $request->effective_date;
                $sec->status = '1';
                $sec->save();

    
                    $supervisor = new EmployeeSupervisor();
                    $supervisor->company_id = $request->company_id;
                    $supervisor->employee_info_id = $request->emp_code;
                    $supervisor->employee_info_sup_id = $request->supervisor;
                    $supervisor->save();
                

                $desi = new EmployeeDesignation();
                $desi->designation_id = $request->designation_id;
                $desi->emp_code = $request->emp_code;
                $desi->company_id = $request->company_id;
                $desi->department_id = $request->department_id;
                $desi->desigantion_effective_start_date = '0000-00-00';
                $desi->desigantion_effective_end_date = '0000-00-00';
                $desi->status = '1';
                $desi->save();

                if (!empty($request->branch_id)) {

                    $branch = new EmployeeCompanyBranch();
                    $branch->branch_id = $request->branch_id;
                    $branch->emp_code = $request->emp_code;
                    $branch->company_id = $request->company_id;
                    $branch->branch_effective_start_date = '0000-00-00';
                    $branch->branch_effective_end_date = '0000-00-00';
                    $branch->status = '1';
                    $branch->save();
                }


                if (!empty($request->staffgrade_id)) {

                    $staffGrade = new EmployeeStaffGrade();
                    $staffGrade->emp_code = $request->emp_code;
                    $staffGrade->company_id = $request->company_id;
                    $staffGrade->department_id = $request->department_id;
                    $staffGrade->branch_id = $request->branch_id;
                    $staffGrade->section_id = $request->section_id;
                    $staffGrade->staff_grade_id = $request->staffgrade_id;
                    $staffGrade->staffgrade_effective_start_date = '0000-00-00';
                    $staffGrade->staffgrade_effective_end_date = '0000-00-00';
                    $staffGrade->status = '1';
                    $staffGrade->save();
                }




                if (count($request->com_upload) > 0) {

                    foreach ($request->com_upload as $key => $file):
                        $name = "job_experience_" . time() . $key . '.' . $file->getClientOriginalExtension();
                        $logo_cirtificate = $name;
                        $file->move("./upload/experience_certificate", $name);
                        $logo_cirtificate;

                        $job = new EmployeeJobExperienceHistory();
                        $job->company_name = $request->com_name[$key];
                        $job->company_id = $request->company_id;
                        $job->emp_code = $request->emp_code;
                        //$request->company_id;
                        $job->company_address = $request->com_address[$key];
                        $job->desigantion = $request->com_desigantion[$key];
                        $job->responsibility = $request->com_responsibility[$key];
                        $job->start_date = $request->com_s_date[$key];
                        $job->end_date = $request->com_e_date[$key];
                        $job->cirtificateupload = $logo_cirtificate;
                        $job->save();
                    endforeach;
                }



                if (count($request->edu_upload) > 0) {

                    foreach ($request->edu_upload as $key => $file):

                        $name = "education_" . time() . $key . '.' . $file->getClientOriginalExtension();
                        $logo_cirtificate = $name;

                        $file->move("./upload/educational_certificate", $name);
                        $logo_cirtificate;

                        $edu = new EmployeeEducationalQualification();
                        $edu->certification_name = $request->certification[$key];
                        $edu->company_id = $request->company_id;
                        $edu->emp_code = $request->emp_code;
                        //$request->company_id;
                        $edu->institute = $request->institute[$key];
                        $edu->institute_add = $request->institute_add[$key];
                        $edu->result = $request->result[$key];
                        $edu->start_date = $request->edu_s_date[$key];
                        $edu->end_date = $request->edu_e_date[$key];
                        $edu->cirtificateupload = $logo_cirtificate;
                        $edu->save();
                    endforeach;
                }

                //shanto
                //initially add 0 leave balance for new employees
                //current year
                $year = date('Y');
                // echo '<pre>';
                // print_r($sqlLeavePolicy);
                /*
				foreach ($sqlLeavePolicy as $lprow):
                    //sql check already exists or Not
                    $sqlexists = LeaveAssignedYearlyData::where('company_id', $request->company_id)
								->where('emp_code', $request->emp_code)
								->where('leave_policy_id', $lprow->leave_policy_id)
								->where('year', $year)
								->get();

                    $data_exists = count($sqlexists);

                    $total_days = 0;
                    $availed_days = 0;
                    $remaining_days = 0;
                    $carry_forward_balance = 0;
                    $incash_balance = 0;

                    if ($data_exists == 0) {
						
                        $ld = new LeaveAssignedYearlyData;
                        $ld->company_id = $request->company_id;
                        $ld->emp_code = $request->emp_code;
                        $ld->leave_policy_id = $lprow->id;
                        $ld->year = $year;
                        $ld->total_days = $total_days;
                        $ld->availed_days = $availed_days;
                        $ld->remaining_days = $remaining_days;
                        $ld->carry_forward_balance = $carry_forward_balance;
                        $ld->incash_balance = $incash_balance;
                        $ld->save();
						
                    }
                endforeach;
				*/
                //ends initial leave balance add for new employees
                //exit();
				
				app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($request->emp_code);

                return redirect()->action('EmployeeInfoController@index')->with('success', 'Information Added Successfully');
            } else {
                return redirect()->action('EmployeeInfoController@index')->with('warning', 'No Leave Policy Found For Your Requested Company Please Add !');
            }
        } else {
            return redirect()->action('EmployeeInfoController@index')->with('warning', 'Information Already Exists in database');
        }
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

        // echo $id;
		
		
        $basic = EmployeeInfo::where('id', $id)->first();

        $c_id = $basic->company_id;
        $super = $basic->emp_code;
        $user_id = $basic->user_id;
		app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($super);
		
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
		
        //return view('module.Employee.employeeinfo',['basic'=>$basic]);
		
		$component=DB::table('payroll_salary_components')->orderBy('display_order','ASC')->get();
		$componentPlus=DB::table('payroll_salary_components')->where('headerDisplayOn','+')->orderBy('display_order','ASC')->get();
		$componentMinus=DB::table('payroll_salary_components')->where('headerDisplayOn','-')->orderBy('display_order','ASC')->get();
		$componentGross=DB::table('payroll_salary_components')->where('is_gross','1')->orderBy('display_order','ASC')->first();
		
		$componentNonFunctional=DB::table('payroll_salary_components')
								->whereNotIN('is_gross',[1])
								->whereNotIN('headerDisplayOn',['+','-'])
								->orderBy('display_order','ASC')->get();
		$payrollDataArray=[];						
		$exEmpPayrollEmpo=DB::table('employee_payroll_infos')->where('emp_code',$super)->get();
		$CkexEmpPayrollEmpo=DB::table('employee_payroll_infos')->where('emp_code',$super)->count();
		if($CkexEmpPayrollEmpo!=0)
		{
			//echo "<pre>";
			foreach($exEmpPayrollEmpo as $emo):
				//print_r($emo);
				$pushArray=array($emo->component_field=>$emo->component_value);
				$payrollDataArray[]=$pushArray;
			endforeach;
		}
		//print_r($payrollDataArray);
		//exit();
		//$exEmpPayrollEmpoGross=DB::table('employee_payroll_infos')->where('emp_code',$super)->where('component_field',''.strtolower(str_replace(' ','_',$componentGross->header_title)))->first();				

        $reg = User::where('id', $user_id)->first();

        $email = $reg->email;
        $password = $reg->password;

        $compa = EmployeeCompany::where('emp_code', $super)->orderBy('id','DESC')->first();


        //   $empAssignRole = EmployeeAssignRole::where('emp_code', $super)->first();
        $inReplace = InreplacementOf::where('replacement_of_emp_code', $super)->orderBy('id','DESC')->take(1)->get();

		
        $dept = EmployeeDepartment::where('emp_code', $super)->orderBy('id','DESC')->first();
		//print_r($dept);
		//exit();
		//print_r($dept);
		//exit();
		if(count($dept)==0)
		{
			$dept=0;
		}
		
		
		//print_r($dept);
		//exit();
        $sec = EmployeeSection::where('emp_code', $super)->orderBy('id','DESC')->first();
		if(count($sec)==0)
		{
			$sec='';
		}
		
        $desi = EmployeeDesignation::where('emp_code', $super)->orderBy('id','DESC')->first();
		if(count($desi)==0)
		{
			$desi='';
		}
		
		

        $job_location = EmployeeCompanyBranch::where('emp_code', $super)->orderBy('id','DESC')->count() ? EmployeeCompanyBranch::where('emp_code', $super)->orderBy('id','DESC')->get() : '';



        $staffGrade = EmployeeStaffGrade::where('emp_code', $super)->orderBy('id','DESC')->first();



        $role = SystemAccessRole::all();

        $Inreplace = EmployeeInfo::all();

        $chkothers_info = DB::table('employee_companies')
                ->leftjoin('inreplacement_ofs', 'employee_companies.emp_code', '=', 'inreplacement_ofs.emp_code')
                ->select(DB::raw('employee_companies.is_pf_eligible,
            employee_companies.pf_effective_from,
            employee_companies.company_effective_start_date,
            employee_companies.proposed_confirmation_date ,
            inreplacement_ofs.replacement_of_emp_code
            '))
                ->where('employee_companies.company_id', $c_id)
                ->where('employee_companies.emp_code', $super)
                //->orderBy('employee_companies.company_id', 'LIMIT 1')
				->orderBy('employee_companies.id','DESC')
				->take(1)
                ->count();

        $others_infos = DB::table('employee_companies')
                ->leftjoin('inreplacement_ofs', 'employee_companies.emp_code', '=', 'inreplacement_ofs.emp_code')
                ->select(DB::raw('employee_companies.is_pf_eligible,
            employee_companies.pf_effective_from,
            employee_companies.company_effective_start_date,
            employee_companies.proposed_confirmation_date ,
            inreplacement_ofs.replacement_of_emp_code
            '))
                ->where('employee_companies.company_id', $c_id)
                ->where('employee_companies.emp_code', $super)
                ->orderBy('employee_companies.id','DESC')
				->take(1)
                ->get();
        $others_info = $chkothers_info ? $others_infos : '';
		
        $systemAccseeRoleCHK = DB::table('employee_assign_roles')
                ->leftjoin('system_access_roles', 'system_access_roles.id', '=', 'employee_assign_roles.system_access_role_id')
                ->select(DB::raw('employee_assign_roles.*,
            system_access_roles.name
            '))
                ->where('employee_assign_roles.company_id', $c_id)
                ->where('employee_assign_roles.emp_code', $super)
                //->orderBy('employee_assign_roles.id', 'LIMIT 1')
				->orderBy('employee_assign_roles.id','DESC')
				->take(1)
                ->get();
				//echo"<pre>";
				//print_r($systemAccseeRoleCHK);
				if(!empty(count($systemAccseeRoleCHK))){
					$systemAccseeRole=$systemAccseeRoleCHK;
				}else{
					$systemAccseeRole=0;
				}

        $job_exp = DB::table('employee_job_experience_histories')->where('company_id', $c_id)->where('emp_code', $super)->count() ? DB::table('employee_job_experience_histories')->where('company_id', $c_id)->where('emp_code', $super)->get() : '';

        $emp_edu = DB::table('employee_educational_qualifications')->where('company_id', $c_id)->where('emp_code', $super)->count() ? DB::table('employee_educational_qualifications')->where('company_id', $c_id)->where('emp_code', $super)->get() : '';



        $company = Company::whereIn('id',$RoleAssignedCompany)->get();

        $department = Department::whereIn('company_id',$RoleAssignedCompany)->get();

        $country = Country::all();
        $city = City::all();
        $marital_status = MaritalStatus::all();
        $gender = Gender::all();
        $bloodGroup = BloodGroup::all();
        $designation = Designation::all();
        $staff = StaffGrade::all();

        $section = Section::all();
        $branch = CompanyBranch::all();

        $supervisor = EmployeeSupervisor::where('employee_info_id', $super)->count() ? EmployeeSupervisor::where('employee_info_id', $super)->orderBy('id','DESC')->first() : '';


// echo '<pre>';
//        print_r($supervisor);

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

//        print_r($logged_emp_company_id);
//        exit();

		$wp_effective_fromD = EmployeeWeekendDifferentCompanyPolicy::where('emp_code', $super)->count() ? EmployeeWeekendDifferentCompanyPolicy::where('emp_code', $super)->where('company_id', $c_id)->orderBy('id','DESC')->get():0;
		
		//echo "<pre>";
		//print_r($wp_effective_fromD);
		//exit();
		
        if (count($wp_effective_fromD)!=0) {
			if(isset($wp_effective_fromD[0]->effective_from))
			{
            $wp_effective_from = $wp_effective_fromD[0]->effective_from;
			$wp_company_id=$wp_effective_fromD[0]->company_id;
			}
			else
			{
				$wp_effective_from = '';
			$wp_company_id=0;
			}
        } else {
            $wp_effective_from = '';
			$wp_company_id=0;
        }

		$emType=EmploymentType::all();
		
		
		
		
        return view('module.Employee.employeeInfo', ['logged_emp_com' => $logged_emp_company_id,
            'basic' => $basic,
            'compa' => $compa,
            'dept' => $dept,
            'sec' => $sec,
            'desi' => $desi,
            'staffGrade' => $staffGrade,
            'job_location' => $job_location,
            'super' => $super,
            'job_exp' => $job_exp,
            'emp_edu' => $emp_edu,
            'others_info' => $others_info,
            'role' => $role,
            'Inreplac' => $Inreplace,
            'Accessrole' => $systemAccseeRole,
            'country' => $country,
            'company' => $company,
            'department' => $department,
            'section' => $section,
            'designation' => $designation,
            'city' => $city,
            'staff' => $staff,
            'branch' => $branch,
            'supervisor' => $supervisor,
            'marital_status' => $marital_status,
            'gender' => $gender,
            'b_group' => $bloodGroup,
            'password' => $password,
            'email' => $email,
			'wp_effective_from' => $wp_effective_from,
			'wp_company_id' => $wp_company_id,
			'emtype' => $emType,
			'componentPlus' => $componentPlus,
			'componentMinus' => $componentMinus,
			'componentGross' => $componentGross,
			'componentNonFunctional' => $componentNonFunctional,
			'component'=>$component,
			'exEmpPayrollEmpo'=>$payrollDataArray]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeInfo  $employeeInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $Eid) {

        $this->validate($request, [
            'fname' => 'required',
            'email' => 'required',
            'address' => 'required',
            //'marital_id' => 'required',
            'gender_id' => 'required',
            //'DOB' => 'required',
            'company_id' => 'required',
            'department_id' => 'required',
            'section_id' => 'required',
            //'supervisor' => 'required',
            //'effective_date' => 'required',
            'designation_id' => 'required',
        ]);
		
		
		//echo "<h1>Employee is ON Development, Please WAIT.</h1>";
		$created_by = MenuPageController::loggedUser('emp_code');
		$component=DB::table('payroll_salary_components')->orderBy('display_order','ASC')->get();
		foreach($component as $cop)
		{
			$fid="salary_".strtolower(str_replace(' ','_',$cop->header_title));
			$fid_value=$request->$fid;
			
			$chkComponent=DB::table('employee_payroll_infos')
			->where('emp_code',$Eid)
			->where('component_field',$fid)
			->count();
			
			if($chkComponent==0)
			{
				DB::table('employee_payroll_infos')
				->insert([
					'emp_code'=>$Eid,
					'component_field'=>$fid,
					'component_value'=>$fid_value,
					'created_by'=>$created_by,
					'updated_by'=>$created_by
				]);
			}
			else
			{
				
				DB::table('employee_payroll_infos')
				->where('emp_code',$Eid)
				->where('component_field',$fid)
				->update([
					'component_value'=>$fid_value,
					'updated_by'=>$created_by
				]);
				
			}
				
		}
		
		$weekend_policy_as_diff_company = 0;
        $wp_effective_from = '';
        $weekend_policy_as_diff_company_eligible = $request->weekend_policy_as_diff_company ? $request->weekend_policy_as_diff_company : '0';

        if (!empty($weekend_policy_as_diff_company_eligible)) {

            $this->validate($request, [
                'wp_effective_from' => 'required',
				'wp_company_id' => 'required',
            ]);
		}
		
		app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($request->Eid);

        $is_pf_eligible = 0;
        $pf_effective_date = '';
        if (!empty($request->pf_eligible)) {
            $is_pf_eligible = 1;
            $pf_effective_date = $request->pf_effective_from;
            $this->validate($request, [
                'pf_effective_from' => 'required',
            ]);
        } else {
            $is_pf_eligible = 0;
            $pf_effective_date = '0000-00-00';
        }

        $user_id = EmployeeInfo::where('emp_code', $Eid)->first();
        $reg = User::where('id', $user_id->user_id)->first();

        $reg->email = $request->email;
        $reg->password = bcrypt($request->password);

        $reg->save();
		
		
		if(isset($request->employee_employment_type_id))
		{
			$chkDept =EmployeeEmploymentType::where('emp_code',$Eid)->count();
			if($chkDept==0)
			{
				$dept =new EmployeeEmploymentType;
				$dept->emp_code = $request->emp_code;
			
			}
			else
			{
				$dept =EmployeeEmploymentType::where('emp_code',$Eid)->first();
			}
			$dept->employee_employment_type_id = $request->employee_employment_type_id;
			$dept->save();
		}
		
		
		if (!empty($weekend_policy_as_diff_company_eligible)) {


            $wp_effective_from = $request->wp_effective_from;

            $emp_weekend_info = EmployeeWeekendDifferentCompanyPolicy::where('emp_code', $Eid)
                            ->where('company_id', $request->wp_company_id)
                            ->where('effective_from', '>=', $request->wp_effective_from)->count();
            if ($emp_weekend_info == 0) {
                $weekend =new EmployeeWeekendDifferentCompanyPolicy;
                $weekend->company_id = $request->wp_company_id;
                $weekend->emp_code = $Eid;
                $weekend->effective_from = $request->wp_effective_from;
                $weekend->save();
            }
			else
			{
				$chk = $emp_weekend_info = EmployeeWeekendDifferentCompanyPolicy::where('emp_code', $Eid)
                        ->where('company_id', $request->wp_company_id)
                        ->orderBy('id', 'DESC')
                        ->first();
                $weekend = EmployeeWeekendDifferentCompanyPolicy::find($chk->id);
                $weekend->company_id = $request->wp_company_id;
                $weekend->emp_code = $Eid;
                $weekend->effective_from = $request->wp_effective_from;
                $weekend->save();
			}
        }



		$chkcompaUP = EmployeeCompany::where('emp_code', $Eid)->count();
		if($chkcompaUP==0)
		{
			$compaUP =new EmployeeCompany;
			$compaUP->emp_code = $Eid;
		}
		else
		{
			$compaUP = EmployeeCompany::where('emp_code', $Eid)->first();
		}
        
		if (!empty($request->company_id)) {
            $compaUP->company_id = $request->company_id;
        }

        if (!empty($request->effective_date)) {
            $compaUP->company_effective_start_date = $request->effective_date;
        }

        if (!empty($request->effective_date)) {
            $compaUP->company_effective_end_date = $request->effective_date;
        }

        if (!empty($request->pc_date)) {
            $compaUP->proposed_confirmation_date = $request->pc_date;
        }
        $compaUP->pf_effective_from = $pf_effective_date;
        $compaUP->is_pf_eligible = $is_pf_eligible;
        $compaUP->status = '1';
        $compaUP->save();


        $is_ot_eligible = $request->is_ot_eligible ? $request->is_ot_eligible : '0';

        if (!empty($request->emp_image)) {
            $name = "emp_" . time() . '.' . $request->emp_image->getClientOriginalExtension();
            $image = $name;
            $request->emp_image->move("./upload/employee_image", $name);
        }

        $basicUP = EmployeeInfo::where('emp_code', $Eid)->first();


        if (!empty($request->company_id)) {
            $basicUP->company_id = $request->company_id;
        }
        $basicUP->user_id = $reg->id;
        $basicUP->is_ot_eligible = $is_ot_eligible;

        if (!empty($request->fname)) {
            $basicUP->first_name = $request->fname;
        }

        if (!empty($request->lname)) {
            $basicUP->last_name = $request->lname;
        }

        if (!empty($request->phone)) {
            $basicUP->phone = $request->phone;
        }

        if (!empty($request->address)) {
            $basicUP->address = $request->address;
        }

        if (!empty($request->city_id)) {
            $basicUP->city = $request->city_id;
        }

        if (!empty($request->country_id)) {
            $basicUP->country = $request->country_id;
        }

        if (!empty($request->blood_id)) {
            $basicUP->blood_group = $request->blood_id;
        }

        if (!empty($request->gender_id)) {
            $basicUP->gender = $request->gender_id;
        }

        if (!empty($request->marital_id)) {
            $basicUP->marital_status = $request->marital_id;
        }

        if (!empty($request->DOB)) {
            $basicUP->dob = $request->DOB;
        }

        if (!empty($request->join_date)) {
            $basicUP->join_date = $request->join_date;
        }
        if (!empty($request->emp_image)) {
            $basicUP->image = $image;
        }
        $basicUP->save();

        $CHKempAssignRole = EmployeeAssignRole::where('emp_code', $Eid)->count();
		if($CHKempAssignRole==0)
		{
			$empAssignRole =new EmployeeAssignRole;
			$empAssignRole->emp_code = $Eid;	
			if (!empty($request->company_id)) {
				$empAssignRole->company_id = $request->company_id;
			}

			if (!empty($request->assignRole)) {
				$empAssignRole->system_access_role_id = $request->assignRole;
			}
			$empAssignRole->is_active = '1';
			$empAssignRole->save();
		}
		else
		{
			$empAssignRole = EmployeeAssignRole::where('emp_code', $Eid)->first();

			if (!empty($request->company_id)) {
				$empAssignRole->company_id = $request->company_id;
			}

			if (!empty($request->assignRole)) {
				$empAssignRole->system_access_role_id = $request->assignRole;
			}
			$empAssignRole->is_active = '1';
			$empAssignRole->save();
		}
		
		
		
		

		
		$chkinReplace = InreplacementOf::where('emp_code', $Eid)->count();
		if($chkinReplace==0)
		{
				$inReplace =new InreplacementOf;
				$inReplace->emp_code =$Eid;

		}
		else{
			$inReplace = InreplacementOf::where('emp_code', $Eid)->first();

		}
        
        if (!empty($request->company_id)) {
            $inReplace->company_id = $request->company_id;
        }

        if (!empty($request->inReplacement)) {
            $inReplace->replacement_of_emp_code = $request->inReplacement;
        }
        $inReplace->save();



		$CHKdept = EmployeeDepartment::where('emp_code', $Eid)->count();
		if($CHKdept==0)
		{
			$dept =new EmployeeDepartment;
			$dept->emp_code = $Eid;
			if (!empty($request->department_id)) {
				$dept->department_id = $request->department_id;
			}

			if (!empty($request->company_id)) {
				$dept->company_id = $request->company_id;
			}

			if (!empty($request->effective_date)) {
				$dept->department_effective_start_date = $request->effective_date;
			}

			if (!empty($request->effective_date)) {
				$dept->department_effective_end_date = $request->effective_date;
			}
			$dept->status = '1';
			$dept->save();

		}
		else
		{
			$dept = EmployeeDepartment::where('emp_code', $Eid)->first();
			if (!empty($request->department_id)) {
				$dept->department_id = $request->department_id;
			}

			if (!empty($request->company_id)) {
				$dept->company_id = $request->company_id;
			}

			if (!empty($request->effective_date)) {
				$dept->department_effective_start_date = $request->effective_date;
			}

			if (!empty($request->effective_date)) {
				$dept->department_effective_end_date = $request->effective_date;
			}
			$dept->status = '1';
			$dept->save();
		}
		
		


        $CHKsec = EmployeeSection::where('emp_code', $Eid)->count();
		
		if($CHKsec==0)
		{
			$sec =new EmployeeSection;
			$sec->emp_code=$Eid;
			if (!empty($request->section_id)) {
				$sec->section_id = $request->section_id;
			}

			if (!empty($request->company_id)) {
				$sec->company_id = $request->company_id;
			}

			if (!empty($request->department_id)) {
				$sec->department_id = $request->department_id;
			}

			if (!empty($request->effective_date)) {
				$sec->section_effective_start_date = $request->effective_date;
			}

			if (!empty($request->effective_date)) {
				$sec->section_effective_end_date = $request->effective_date;
			}
			$sec->status = '1';
			$sec->save();

		}
		else
		{
			$sec = EmployeeSection::where('emp_code', $Eid)->first();

			if (!empty($request->section_id)) {
				$sec->section_id = $request->section_id;
			}

			if (!empty($request->company_id)) {
				$sec->company_id = $request->company_id;
			}

			if (!empty($request->department_id)) {
				$sec->department_id = $request->department_id;
			}

			if (!empty($request->effective_date)) {
				$sec->section_effective_start_date = $request->effective_date;
			}

			if (!empty($request->effective_date)) {
				$sec->section_effective_end_date = $request->effective_date;
			}
			$sec->status = '1';
			$sec->save();
		}

        $chksupervisor = EmployeeSupervisor::where('employee_info_id', $Eid)->count();
        if ($chksupervisor != 0) {
            $supervisor = EmployeeSupervisor::where('employee_info_id', $Eid)->orderBy('id','DESC')->first();
            if (!empty($request->company_id)) {
                $supervisor->company_id = $request->company_id;
            }
            $supervisor->employee_info_id = $Eid;

            if (!empty($request->supervisor)) {
                $supervisor->employee_info_sup_id = $request->supervisor;
            }
            $supervisor->save();
        } else {
			
            $supervisor = new EmployeeSupervisor;
            if (!empty($request->company_id)) {
                $supervisor->company_id = $request->company_id;
            }
            $supervisor->employee_info_id = $Eid;
            if (!empty($request->supervisor)) {
                $supervisor->employee_info_sup_id = $request->supervisor;
            }
            $supervisor->save();
        }





        $CHKdesi = EmployeeDesignation::where('emp_code', $Eid)->count();
        if($CHKdesi==0)
		{
			$desi =new EmployeeDesignation;
			$desi->emp_code = $Eid;
			if (!empty($request->designation_id)) {
				$desi->designation_id = $request->designation_id;
			}

			if (!empty($request->company_id)) {
				$desi->company_id = $request->company_id;
			}

			if (!empty($request->department_id)) {
				$desi->department_id = $request->department_id;
			}
			$desi->status = '1';
			$desi->save();
		}
		else
		{
			$desi = EmployeeDesignation::where('emp_code', $Eid)->first();

			if (!empty($request->designation_id)) {
				$desi->designation_id = $request->designation_id;
			}

			if (!empty($request->company_id)) {
				$desi->company_id = $request->company_id;
			}

			if (!empty($request->department_id)) {
				$desi->department_id = $request->department_id;
			}
			$desi->status = '1';
			$desi->save();
		}


        if (!empty($request->branch_id)) {
            $chkbranch = EmployeeCompanyBranch::where('emp_code', $Eid)->count();
            if ($chkbranch != 0) {
                $branch = EmployeeCompanyBranch::where('emp_code', $Eid)->first();
                // $branch->emp_code = $request->emp_code;
                $branch->branch_id = $request->branch_id;
                $branch->company_id = $request->company_id;
                $branch->status = '1';
                $branch->save();
            } else {
                $branch = new EmployeeCompanyBranch();
                $branch->emp_code = $Eid;
                $branch->branch_id = $request->branch_id;
                $branch->company_id = $request->company_id;
                $branch->status = '1';
                $branch->save();
            }
        }


        if (!empty($request->staffgrade_id)) {
            $chkstaffGrade = EmployeeStaffGrade::where('emp_code', $Eid)->count();
            if ($chkstaffGrade !== 0) {
                $staffGrade = EmployeeStaffGrade::where('emp_code', $Eid)->first();
                $staffGrade->company_id = $request->company_id;
                $staffGrade->department_id = $request->department_id;
                $staffGrade->branch_id = $request->branch_id;
                $staffGrade->section_id = $request->section_id;
                $staffGrade->staff_grade_id = $request->staffgrade_id;
                $staffGrade->status = '1';
                $staffGrade->save();
            } else {
                $staffGrade = new EmployeeStaffGrade;
                $staffGrade->emp_code = $Eid;
                $staffGrade->company_id = $request->company_id;
                $staffGrade->department_id = $request->department_id;
                $staffGrade->branch_id = $request->branch_id;
                $staffGrade->section_id = $request->section_id;
                $staffGrade->staff_grade_id = $request->staffgrade_id;
                $staffGrade->status = '1';
                $staffGrade->save();
            }
        }

        //Problem to update starts here
        //print_r($request->com_name);
        $chk = '';
        foreach ($request->com_name as $key => $value) {
            $chk = $value;

            if (!empty($chk)) {
                $chkJob = EmployeeJobExperienceHistory::where('company_name', $chk)->where('emp_code', $Eid)->count();
                if ($chkJob != 0) {
                    $delAll = EmployeeJobExperienceHistory::where('company_name', $chk)->where('emp_code', $Eid)->delete();
                    if (isset($delAll)) {
                        foreach ($request->com_upload as $keys => $file):
                            $name = "job_experience_" . time() . $keys . '.' . $file->getClientOriginalExtension();
                            $logo_cirtificate = $name;
                            $file->move("./upload/experience_certificate", $name);
                            $logo_cirtificate;

                            $job = new EmployeeJobExperienceHistory();
                            $job->company_name = $request->com_name[$keys];
                            $job->company_id = $request->company_id;
                            $job->emp_code = $Eid;
                            $job->company_address = $request->com_address[$keys];
                            $job->desigantion = $request->com_desigantion[$keys];
                            $job->responsibility = $request->com_responsibility[$keys];
                            $job->start_date = $request->com_s_date[$keys];
                            $job->end_date = $request->com_e_date[$keys];
                            $job->cirtificateupload = $logo_cirtificate;
                            $job->save();
                        endforeach;
                    }
                } else {
                    foreach ($request->com_upload as $keys => $file):
                        $name = "job_experience_" . time() . $keys . '.' . $file->getClientOriginalExtension();
                        $logo_cirtificate = $name;
                        $file->move("./upload/experience_certificate", $name);
                        $logo_cirtificate;

                        $job = new EmployeeJobExperienceHistory();
                        $job->company_name = $request->com_name[$keys];
                        $job->company_id = $request->company_id;
                        $job->emp_code = $Eid;
                        $job->company_address = $request->com_address[$keys];
                        $job->desigantion = $request->com_desigantion[$keys];
                        $job->responsibility = $request->com_responsibility[$keys];
                        $job->start_date = $request->com_s_date[$keys];
                        $job->end_date = $request->com_e_date[$keys];
                        $job->cirtificateupload = $logo_cirtificate;
                        $job->save();
                    endforeach;
                    //echo 'success';
                }
            }
        }


        $chk2 = '';
        foreach ($request->certification as $keys => $values) {
            $chk2 = $values;

            if (!empty($chk2)) {
                $chkEdu = EmployeeEducationalQualification::where('certification_name', $chk2)->where('emp_code', $Eid)->count();
                if ($chkEdu != 0) {
                    $delAll2 = EmployeeEducationalQualification::where('certification_name', $chk2)->where('emp_code', $Eid)->delete();
                    if (isset($delAll2)) {
                        foreach ($request->edu_upload as $keys => $file):

                            $name = "education_" . time() . $keys . '.' . $file->getClientOriginalExtension();
                            $logo_cirtificate = $name;

                            $file->move("./upload/educational_certificate", $name);
                            $logo_cirtificate;

                            $edu = new EmployeeEducationalQualification();
                            $edu->certification_name = $request->certification[$keys];
                            $edu->company_id = $request->company_id;
                            $edu->emp_code = $Eid;
                            $edu->institute = $request->institute[$keys];
                            $edu->institute_add = $request->institute_add[$keys];
                            $edu->result = $request->result[$keys];
                            $edu->start_date = $request->edu_s_date[$keys];
                            $edu->end_date = $request->edu_e_date[$keys];
                            $edu->cirtificateupload = $logo_cirtificate;
                            $edu->save();
                        endforeach;
                    }
                } else {
                    foreach ($request->edu_upload as $keys => $file):

                        $name = "education_" . time() . $keys . '.' . $file->getClientOriginalExtension();
                        $logo_cirtificate = $name;

                        $file->move("./upload/educational_certificate", $name);
                        $logo_cirtificate;

                        $edu = new EmployeeEducationalQualification();
                        $edu->certification_name = $request->certification[$keys];
                        $edu->company_id = $request->company_id;
                        $edu->emp_code = $Eid;
                        $edu->institute = $request->institute[$keys];
                        $edu->institute_add = $request->institute_add[$keys];
                        $edu->result = $request->result[$keys];
                        $edu->start_date = $request->edu_s_date[$keys];
                        $edu->end_date = $request->edu_e_date[$keys];
                        $edu->cirtificateupload = $logo_cirtificate;
                        $edu->save();
                    endforeach;
                }
            }
        }


          //echo "if";
        return redirect()->action('EmployeeInfoController@index')->with('success', 'Information Updated Successfully');
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
