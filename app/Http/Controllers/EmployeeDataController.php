<?php

namespace App\Http\Controllers;
use App\EmployeeInfo;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;


class EmployeeDataController extends Controller
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

    public function filterEmployeeList(Request $request)
    {

        $company_id=$request->company_id;
        $department_id=$request->department_id;
        $section_id=$request->section_id;
        $designation_id=$request->designation_id;
        $json=DB::table('employee_infos')

        ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

        ->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

        ->leftjoin('companies','companies.id','=','employee_companies.company_id')
        ->leftjoin('departments','departments.id','=','employee_departments.department_id')

        ->leftjoin('sections','sections.id','=','employee_sections.section_id')
        ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

        ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
            companies.name as company, 
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
            employee_infos.email, 
            employee_infos.phone, 
            departments.name as department,
            sections.name as section,
            designations.name as designation,
            employee_infos.created_at'))

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
        
        ->groupBy('employee_infos.id')

        ->orderBy('employee_infos.id','DESC')

        ->get();


        return response()->json(array("data"=>$json,"total"=>count($json)));

    }

    public function filterEmployeeReport(Request $request){
		
		
        $company_id=$request->company_id;
		
		if($company_id==0)
		{
			$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
				
			$data = Company::whereIn('id',$RoleAssignedCompany)->get();	
			$company_id=$data[0]->id;
			//echo $company_id;
			//exit();
		}

		
        $department_id=$request->department_id;
        $section_id=$request->section_id;
        $designation_id=$request->designation_id;

        $start_date=$request->start_date;
        $end_date=$request->end_date;


        $json=DB::table('employee_infos')

        ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

        ->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

        ->leftjoin('companies','companies.id','=','employee_companies.company_id')
        ->leftjoin('departments','departments.id','=','employee_departments.department_id')

        ->leftjoin('sections','sections.id','=','employee_sections.section_id')
        ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

        ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
            companies.name as company, 
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
            employee_infos.email, 
            employee_infos.phone, 
            departments.name as department,
            sections.name as section,
            designations.name as designation,
            employee_infos.created_at'))
			
		//->when($start_date, function ($query) use ([$start_date, $end_date]) {
          //  return $query->whereBetween('employee_infos.created_at', [$start_date, $end_date]);
        //})	

        

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
        
        ->groupBy('employee_infos.id')

        ->orderBy('employee_infos.id','DESC')

        ->get();


        return response()->json(array("data"=>$json,"total"=>count($json)));
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

    
    
    public function listShow()
    {
        $data=Company::all();
        return view('module.Employee.employee_list',['company'=>$data]);
    }

    public function reportShow()
    {
		
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $data = Company::whereIn('id',$RoleAssignedCompany)->get();
        //$logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        //$data=Company::all();
        return view('module.Employee.employee_report',['company'=>$data]);
    }

    /*

SELECT 
ei.`id`, 
ei.`emp_code`, 
c.name as company, 
concat(ei.`first_name`,' ',IFNULL(ei.`last_name`,'')) AS name, 
ei.`email`, 
ei.`phone`, 
d.name as department,
ei.`created_at`
FROM employee_infos as ei 
LEFT JOIN employee_companies as ec ON ei.emp_code=ec.emp_code 
LEFT JOIN employee_departments as ed ON ei.emp_code=ed.emp_code
LEFT JOIN companies as c ON ec.company_id=c.id 
LEFT JOIN departments as d ON ed.department_id=d.id 
GROUP BY ei.id

    */

public function show()
{

		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
			
		$data = Company::whereIn('id',$RoleAssignedCompany)->get();	
		$company_id=$data[0]->id;
		//echo $company_id;
		//exit();
	
	
    $json=DB::table('employee_infos')

    ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
    ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

    ->leftjoin('companies','companies.id','=','employee_companies.company_id')
    ->leftjoin('departments','departments.id','=','employee_departments.department_id')

    ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
        companies.name as company, 
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
        employee_infos.email, 
        employee_infos.phone, 
        departments.name as department,
        employee_infos.created_at'))

    ->where('employee_infos.company_id',$company_id)

    ->groupBy('employee_infos.id')

    ->orderBy('employee_infos.id','DESC')
    ->get();

    return response()->json(array("data"=>$json,"total"=>count($json)));
}

public function exportFilterExcel($company_id=0,$department_id=0,$section_id=0,$designation_id=0)
{
	
	$comArray=array();
	$compQuery='';
	$componentCount=DB::table('payroll_salary_components')->orderBy('display_order','ASC')->count();
	if($componentCount!=0)
	{
		$componentGet=DB::table('payroll_salary_components')->orderBy('display_order','ASC')->get();
		$d=1;
		foreach($componentGet as $pCOm):
			$mkFid='salary_'.strtolower(str_replace(' ','_',$pCOm->header_title));
			$compQuery .='(SELECT component_value FROM employee_payroll_infos WHERE employee_payroll_infos.emp_code=employee_infos.emp_code AND employee_payroll_infos.component_field="'.$mkFid.'" ORDER BY id DESC LIMIT 1) AS '.$mkFid;
			if($componentCount!=$d)
			{
				$compQuery .=',';
			}
			array_push($comArray,$mkFid);
			$d++;
		endforeach;
	}
	
	
	
	if(count($comArray)>0)
	{
		$dbfields=DB::table('employee_infos')

		->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
		->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

		->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
		->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

		->leftjoin('companies','companies.id','=','employee_companies.company_id')
		->leftjoin('departments','departments.id','=','employee_departments.department_id')

		->leftjoin('sections','sections.id','=','employee_sections.section_id')
		->leftjoin('designations','designations.id','=','employee_designations.designation_id')
		
		//->leftjoin('employee_company_branches','employee_company_branches.emp_code','=','employee_infos.emp_code')
		//->leftjoin('company_branches','company_branches.id','=','employee_company_branches.branch_id')
		
		//->leftjoin('employee_staff_grades','employee_staff_grades.emp_code','=','employee_infos.emp_code')
		//->leftjoin('staff_grades','staff_grades.id','=','employee_staff_grades.branch_id')
		
		
		
		->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
			companies.name as company, 
			concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
			employee_infos.email, 
			 
			designations.name as designation,
			departments.name as department,
			(SELECT company_branches.name from company_branches WHERE company_branches.id=(SELECT employee_company_branches.branch_id FROM employee_company_branches WHERE employee_company_branches.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1)) as branch,
			
			sections.name as section,
			employee_infos.join_date as date_of_join,
			
			(SELECT staff_grades.name from staff_grades WHERE staff_grades.id=(SELECT employee_staff_grades.staff_grade_id FROM employee_staff_grades WHERE employee_staff_grades.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1)) as staff_grade,
			
			"Direct" as reporting_method,
			
			(SELECT employee_supervisors.employee_info_sup_id FROM employee_supervisors WHERE employee_supervisors.employee_info_id=employee_infos.emp_code ORDER BY id DESC LIMIT 1) as supervisor,
			(SELECT genders.name FROM genders WHERE genders.id=employee_infos.gender ORDER BY id DESC LIMIT 1) as gender,
			(SELECT employee_companies.proposed_confirmation_date FROM employee_companies WHERE employee_companies.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1) as proposed_confirmation_date,
			employee_infos.dob,
			employee_infos.address,
			employee_infos.phone,
			"0000-00-00" as resignation_date,
			(SELECT inreplacement_ofs.replacement_of_emp_code FROM inreplacement_ofs WHERE inreplacement_ofs.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1) as replacement,
			
			
			(SELECT blood_groups.name FROM blood_groups WHERE blood_groups.id=employee_infos.blood_group ORDER BY id DESC LIMIT 1) as blood_group,
			(SELECT marital_statuses.name FROM marital_statuses WHERE marital_statuses.id=employee_infos.marital_status ORDER BY id DESC LIMIT 1) as marital_status,
			(SELECT cities.name FROM cities WHERE cities.id=employee_infos.city ORDER BY id DESC LIMIT 1) as city,
			(SELECT countries.name FROM countries WHERE countries.id=employee_infos.country ORDER BY id DESC LIMIT 1) as country,
			(SELECT system_access_roles.name FROM system_access_roles WHERE system_access_roles.id=(SELECT employee_assign_roles.system_access_role_id FROM employee_assign_roles WHERE employee_assign_roles.emp_code=employee_infos.emp_code ORDER BY employee_assign_roles.id DESC LIMIT 1)) as user_type,
			
			
			employee_infos.created_at'),DB::raw($compQuery))

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
		
		->groupBy('employee_infos.id')

		->orderBy('employee_infos.id','DESC')

		->get();
	}
	else
	{
		$dbfields=DB::table('employee_infos')

		->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
		->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

		->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
		->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

		->leftjoin('companies','companies.id','=','employee_companies.company_id')
		->leftjoin('departments','departments.id','=','employee_departments.department_id')

		->leftjoin('sections','sections.id','=','employee_sections.section_id')
		->leftjoin('designations','designations.id','=','employee_designations.designation_id')
		
		//->leftjoin('employee_company_branches','employee_company_branches.emp_code','=','employee_infos.emp_code')
		//->leftjoin('company_branches','company_branches.id','=','employee_company_branches.branch_id')
		
		//->leftjoin('employee_staff_grades','employee_staff_grades.emp_code','=','employee_infos.emp_code')
		//->leftjoin('staff_grades','staff_grades.id','=','employee_staff_grades.branch_id')
		
		
		
		->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
			companies.name as company, 
			concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
			employee_infos.email, 
			 
			designations.name as designation,
			departments.name as department,
			(SELECT company_branches.name from company_branches WHERE company_branches.id=(SELECT employee_company_branches.branch_id FROM employee_company_branches WHERE employee_company_branches.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1)) as branch,
			
			sections.name as section,
			employee_infos.join_date as date_of_join,
			
			(SELECT staff_grades.name from staff_grades WHERE staff_grades.id=(SELECT employee_staff_grades.staff_grade_id FROM employee_staff_grades WHERE employee_staff_grades.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1)) as staff_grade,
			
			"Direct" as reporting_method,
			
			(SELECT employee_supervisors.employee_info_sup_id FROM employee_supervisors WHERE employee_supervisors.employee_info_id=employee_infos.emp_code ORDER BY id DESC LIMIT 1) as supervisor,
			(SELECT genders.name FROM genders WHERE genders.id=employee_infos.gender ORDER BY id DESC LIMIT 1) as gender,
			(SELECT employee_companies.proposed_confirmation_date FROM employee_companies WHERE employee_companies.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1) as proposed_confirmation_date,
			employee_infos.dob,
			employee_infos.address,
			employee_infos.phone,
			"0000-00-00" as resignation_date,
			(SELECT inreplacement_ofs.replacement_of_emp_code FROM inreplacement_ofs WHERE inreplacement_ofs.emp_code=employee_infos.emp_code ORDER BY id DESC LIMIT 1) as replacement,
			
			
			(SELECT blood_groups.name FROM blood_groups WHERE blood_groups.id=employee_infos.blood_group ORDER BY id DESC LIMIT 1) as blood_group,
			(SELECT marital_statuses.name FROM marital_statuses WHERE marital_statuses.id=employee_infos.marital_status ORDER BY id DESC LIMIT 1) as marital_status,
			(SELECT cities.name FROM cities WHERE cities.id=employee_infos.city ORDER BY id DESC LIMIT 1) as city,
			(SELECT countries.name FROM countries WHERE countries.id=employee_infos.country ORDER BY id DESC LIMIT 1) as country,
			(SELECT system_access_roles.name FROM system_access_roles WHERE system_access_roles.id=(SELECT employee_assign_roles.system_access_role_id FROM employee_assign_roles WHERE employee_assign_roles.emp_code=employee_infos.emp_code ORDER BY employee_assign_roles.id DESC LIMIT 1)) as user_type,
			
			
			employee_infos.created_at'))

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
		
		->groupBy('employee_infos.id')

		->orderBy('employee_infos.id','DESC')

		->get();
	}
	
                //->toArray();

        // Initialize the array which will be passed into the Excel
        // generator.
    $excelArray = []; 
	
	
	$new_exel_fid=[
    'id',
    'emp_code',
    'company',
    'name',
    'email',
    
	'designation',
    'department',
	'branch',
    'section',
    'date_of_join',
	
	'staff_grade',
	'reporting_method',
	
	'supervisor',
	
	
	
	'gender',
	'proposed_confirmation_date',
	'dob',
	'address',
	'phone',
	'resignation_date',
	'replacement',
	
	'blood_group',
	'marital_status',
	'city',
	'country',
	'user_type',
    'created_at'
    ];

        // Define the Excel spreadsheet headers
	if(count($comArray)>0)
	{
		$excelArray []=array_merge($new_exel_fid,$comArray);
	}
	else
	{
		$excelArray []=$new_exel_fid;
	}
	
	//print_r($excelArray);
	//exit();
        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
    foreach ($dbfields as $key=>$field) {
        $excelArray[]=get_object_vars($field);
    }

        // Generate and return the spreadsheet
    \Excel::create('EmployeeInfoData_'.date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
        $excel->setTitle('Employee Info');
        $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
        $excel->setDescription('EmployeeInfo');

            // Build the spreadsheet, passing in the payments array
        $excel->sheet('sheet1', function($sheet) use ($excelArray) {
            $sheet->fromArray($excelArray, null, 'A1', false, false);
        });

    })->download('xlsx');
}

public function exportDatewiseFilterExcel($company_id=0,$department_id=0,$section_id=0,$designation_id=0,$start_date=0,$end_date=0)
{

	
	if($company_id==0)
	{
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
			
		$data = Company::whereIn('id',$RoleAssignedCompany)->get();	
		$company_id=$data[0]->id;
		//echo $company_id;
		//exit();
	}

    $dbfields=DB::table('employee_infos')
	->leftjoin('employee_company_branches','employee_company_branches.emp_code','=','employee_infos.emp_code')
    ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

    ->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
    ->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

    //->leftjoin('companies','companies.id','=','employee_companies.company_id')
    ->leftjoin('departments','departments.id','=','employee_departments.department_id')

    ->leftjoin('sections','sections.id','=','employee_sections.section_id')
    ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

    ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
        (SELECT name FROM companies WHERE companies.id=employee_infos.company_id) as company, 
		
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
        employee_infos.email, 
        employee_infos.phone, 
        departments.name as department,
        sections.name as section,
        designations.name as designation,
		(SELECT name FROM company_branches WHERE company_branches.id=employee_company_branches.branch_id) as job_location, 
        employee_infos.created_at'))

    //->whereBetween('employee_infos.created_at', [$start_date, $end_date])

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
    
    ->groupBy('employee_infos.id')

    ->orderBy('employee_infos.id','DESC')

    ->get();
                //->toArray();

        // Initialize the array which will be passed into the Excel
        // generator.
    $excelArray = []; 

        // Define the Excel spreadsheet headers
    $excelArray []= [
    'id',
    'emp_code',
    'company',
    'name',
    'email',
    'phone',
    'department',
    'section',
    'designation',
	'job_location',
    'created_at'
    ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
    foreach ($dbfields as $key=>$field) {
        $excelArray[]=get_object_vars($field);
    }

        // Generate and return the spreadsheet
    \Excel::create('EmployeeInfoData_'.date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
        $excel->setTitle('Employee Info');
        $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
        $excel->setDescription('EmployeeInfo');

            // Build the spreadsheet, passing in the payments array
        $excel->sheet('sheet1', function($sheet) use ($excelArray) {
            $sheet->fromArray($excelArray, null, 'A1', false, false);
        });

    })->download('xlsx');
}


public function exportFilterPdf($company_id=0,$department_id=0,$section_id=0,$designation_id=0)
{

    $content='<h3>Employee Info List</h3>';
    $content .='<h5>Genarated : '.date('d/m/Y H:i:s').'</h5>';
            // instantiate and use the dompdf class
    $excelArray = [
    'id',
    'emp_code',
    'company',
    'name',
    'email',
    'phone',
    'department',
    'created_at'
    ];

    if(!empty($excelArray))
    {
        $content .='<table width="100%">';
        $content .='<thead>';
        $content .='<tr>';
        foreach($excelArray as $exhead):
            $content .='<th>'.$exhead.'</th>';
        endforeach;
        $content .='</tr>';
        $content .='</thead>';


        $rows=count($excelArray);
        $datarows = DB::table('employee_infos')

        ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

        ->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

        ->leftjoin('companies','companies.id','=','employee_companies.company_id')
        ->leftjoin('departments','departments.id','=','employee_departments.department_id')

        ->leftjoin('sections','sections.id','=','employee_sections.section_id')
        ->leftjoin('designations','designations.id','=','employee_designations.designation_id')


        ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
            companies.name as company, 
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
            employee_infos.email, 
            employee_infos.phone, 
            departments.name as department,
            employee_infos.created_at'))

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
                //->where('employee_infos.company_id',8)
        ->groupBy('employee_infos.id')
        ->orderBy('employee_infos.id','DESC')
        ->get();

        if(!empty($datarows))
        {
            $content .='<tbody>';
            foreach($datarows as $draw):
                $content .='<tr>';
            for($i=0; $i<=$rows-1; $i++):
                $fid=$excelArray[$i];
            $content .='<td>'.$draw->$fid.'</td>';
            endfor;
            $content .='</tr>';
            endforeach;
            $content .='</tbody>';

        }


        $content .='</table>';

        $content .='<br />';

        $content .='<h4>Total : '.count($datarows).'</h4>';


        $content .='<br /><br /><br /><table border="0" width="100%">';
        $content .='<tr>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
        $content .='</tr>';


        $content .='</table>';

    }

            //echo $content;

            //print_r($excelArray);



            //exit();
    $dompdf = new Dompdf();
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->loadHtml($content);

            // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

            // Render the HTML as PDF
    $dompdf->render();

            // Output the generated PDF to Browser
    $dompdf->stream();
}

public function exportDatewiseFilterPdf($company_id=0,$department_id=0,$section_id=0,$designation_id=0,$start_date=0, $end_date=0)
{

    $content='<h3>Employee Report List</h3>';
    $content .='<h5>Genarated : '.date('d/m/Y H:i:s').'</h5>';
            // instantiate and use the dompdf class
    $excelArray = [
    'id',
    'emp_code',
    'company',
    'name',
    'email',
    'phone',
    'department',
    'created_at'
    ];

    if(!empty($excelArray))
    {
        $content .='<table width="100%">';
        $content .='<thead>';
        $content .='<tr>';
        foreach($excelArray as $exhead):
            $content .='<th>'.$exhead.'</th>';
        endforeach;
        $content .='</tr>';
        $content .='</thead>';


        $rows=count($excelArray);
        $datarows = DB::table('employee_infos')

        ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

        ->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

        ->leftjoin('companies','companies.id','=','employee_companies.company_id')
        ->leftjoin('departments','departments.id','=','employee_departments.department_id')

        ->leftjoin('sections','sections.id','=','employee_sections.section_id')
        ->leftjoin('designations','designations.id','=','employee_designations.designation_id')


        ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
            companies.name as company, 
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
            employee_infos.email, 
            employee_infos.phone, 
            departments.name as department,
            employee_infos.created_at'))

        ->whereBetween('employee_infos.created_at', [$start_date, $end_date])

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
                //->where('employee_infos.company_id',8)
        ->groupBy('employee_infos.id')
        ->orderBy('employee_infos.id','DESC')
        ->get();

        if(!empty($datarows))
        {
            $content .='<tbody>';
            foreach($datarows as $draw):
                $content .='<tr>';
            for($i=0; $i<=$rows-1; $i++):
                $fid=$excelArray[$i];
            $content .='<td>'.$draw->$fid.'</td>';
            endfor;
            $content .='</tr>';
            endforeach;
            $content .='</tbody>';

        }


        $content .='</table>';

        $content .='<br />';

        $content .='<h4>Total : '.count($datarows).'</h4>';


        $content .='<br /><br /><br /><table border="0" width="100%">';
        $content .='<tr>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
        $content .='</tr>';


        $content .='</table>';

    }

            //echo $content;

            //print_r($excelArray);



            //exit();
    $dompdf = new Dompdf();
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->loadHtml($content);

            // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

            // Render the HTML as PDF
    $dompdf->render();

            // Output the generated PDF to Browser
    $dompdf->stream();
}


public function exportExcel()
{

    $dbfields = DB::table('employee_infos')
    ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
    ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')

    ->leftjoin('employee_sections','employee_sections.emp_code','=','employee_infos.emp_code')
    ->leftjoin('employee_designations','employee_designations.emp_code','=','employee_infos.emp_code')

    ->leftjoin('companies','companies.id','=','employee_companies.company_id')
    ->leftjoin('departments','departments.id','=','employee_departments.department_id')

    ->leftjoin('sections','sections.id','=','employee_sections.section_id')
    ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

    ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
        companies.name as company, 
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
        employee_infos.email, 
        employee_infos.phone, 
        departments.name as department,
        sections.name as section,
        designations.name as designation,
        employee_infos.created_at'))

    ->groupBy('employee_infos.id')
    ->orderBy('employee_infos.id','DESC')
    ->get();
                //->toArray();

        // Initialize the array which will be passed into the Excel
        // generator.
    $excelArray = []; 

        // Define the Excel spreadsheet headers
    $excelArray []= [
    'id',
    'emp_code',
    'company',
    'name',
    'email',
    'phone',
    'department',
    'section',
    'designation',
    'created_at'
    ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
    foreach ($dbfields as $key=>$field) {
        $excelArray[]=get_object_vars($field);
    }

        // Generate and return the spreadsheet
    \Excel::create('EmployeeInfoData_'.date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
        $excel->setTitle('Employee Info');
        $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
        $excel->setDescription('EmployeeInfo');

            // Build the spreadsheet, passing in the payments array
        $excel->sheet('sheet1', function($sheet) use ($excelArray) {
            $sheet->fromArray($excelArray, null, 'A1', false, false);
        });

    })->download('xlsx');
}


public function exportPdf()
{

    $content='<h3>Employee Info List</h3>';
    $content .='<h5>Genarated : '.date('d/m/Y H:i:s').'</h5>';
            // instantiate and use the dompdf class
    $excelArray = [
    'id',
    'emp_code',
    'company',
    'name',
    'email',
    'phone',
    'department',
    'created_at'
    ];

    if(!empty($excelArray))
    {
        $content .='<table width="100%">';
        $content .='<thead>';
        $content .='<tr>';
        foreach($excelArray as $exhead):
            $content .='<th>'.$exhead.'</th>';
        endforeach;
        $content .='</tr>';
        $content .='</thead>';


        $rows=count($excelArray);
        $datarows = $json=DB::table('employee_infos')
        ->leftjoin('employee_companies','employee_companies.emp_code','=','employee_infos.emp_code')
        ->leftjoin('employee_departments','employee_departments.emp_code','=','employee_infos.emp_code')
        ->leftjoin('companies','companies.id','=','employee_companies.company_id')
        ->leftjoin('departments','departments.id','=','employee_departments.department_id')
        ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code, 
            companies.name as company, 
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) AS name, 
            employee_infos.email, 
            employee_infos.phone, 
            departments.name as department,
            employee_infos.created_at'))
                //->where('employee_infos.company_id',8)
        ->groupBy('employee_infos.id')
        ->orderBy('employee_infos.id','DESC')
        ->get();

        if(!empty($datarows))
        {
            $content .='<tbody>';
            foreach($datarows as $draw):
                $content .='<tr>';
            for($i=0; $i<=$rows-1; $i++):
                $fid=$excelArray[$i];
            $content .='<td>'.$draw->$fid.'</td>';
            endfor;
            $content .='</tr>';
            endforeach;
            $content .='</tbody>';

        }


        $content .='</table>';

        $content .='<br />';

        $content .='<h4>Total : '.count($datarows).'</h4>';


        $content .='<br /><br /><br /><table border="0" width="100%">';
        $content .='<tr>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Genarated By</span></b></td>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Reviewed By</span></b></td>';
        $content .='<td align="center"><b><span style="border-top:3px #ccc solid; line-height:30px;">Approved By</span></b></td>';
        $content .='</tr>';


        $content .='</table>';

    }

            //echo $content;

            //print_r($excelArray);



            //exit();
    $dompdf = new Dompdf();
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->loadHtml($content);

            // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

            // Render the HTML as PDF
    $dompdf->render();

            // Output the generated PDF to Browser
    $dompdf->stream();
}


}
