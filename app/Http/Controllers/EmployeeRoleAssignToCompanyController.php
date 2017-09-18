<?php

namespace App\Http\Controllers;

use App\EmployeeRoleAssignToCompany;
use App\EmployeeAssignRole;
use Illuminate\Http\Request;
use App\Company;
use App\SystemAccessRole;

class EmployeeRoleAssignToCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$company=Company::all();
		$SystemAccessRole=SystemAccessRole::all();
        return view('module.Employee.employeeAssigntoCompany',
		['company'=>$company,
		'role'=>$SystemAccessRole]);
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
        $this->validate($request,['emp_code'=>'required','role_id'=>'required']);
		
		$total_company=count($request->company_id);
		if($total_company==0)
		{
				return redirect()->action('EmployeeRoleAssignToCompanyController@index')->with('error','Information Failed To Add');
		}
		else
		{
			$chkEmAssC=EmployeeAssignRole::where('emp_code',$request->emp_code)->count();
			if($chkEmAssC==0)
			{
				$tab=new EmployeeAssignRole;
				$tab->emp_code=$request->emp_code;
				$tab->system_access_role_id=$request->role_id;
				$tab->company_id=$request->$com;
				$tab->save();
			}
			else
			{
				EmployeeAssignRole::where('emp_code',$request->emp_code)->update(['system_access_role_id'=>$request->role_id]);
			}
			
			$chkExCompany=EmployeeRoleAssignToCompany::where('emp_code',$request->emp_code)->count();
			if($chkExCompany!=0)
			{
				EmployeeRoleAssignToCompany::where('emp_code',$request->emp_code)->delete();
			}
			
				foreach($request->company_id as $com):
					$tab=new EmployeeRoleAssignToCompany;
					$tab->emp_code=$request->emp_code;
					$tab->company_id=$com;
					$tab->save();
				endforeach;
				
				//print_r($request->company_id);
			//exit();
			
		}
		
		
        
        return redirect()->action('EmployeeRoleAssignToCompanyController@index')->with('success','Information Added Successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeRoleAssignToCompany  $employeeRoleAssignToCompany
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $json=EmployeeRoleAssignToCompany::leftJoin('companies','employee_role_assign_to_companies.company_id','=','companies.id')
			  ->select('employee_role_assign_to_companies.*','companies.name')
			  ->get();
        return response()->json(array("data"=>$json,"total"=>count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeRoleAssignToCompany  $employeeRoleAssignToCompany
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data=EmployeeRoleAssignToCompany::find($id);
        $country=Country::all();
        return view('module.settings.city',['data'=>$data,'country'=>$country]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\City  $City
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $this->validate($request,['emp_code'=>'required','role_id'=>'required']);
		
		$total_company=count($request->company_id);
		if($total_company==0)
		{
				return redirect()->action('EmployeeRoleAssignToCompanyController@index')->with('error','Information Failed To Update');
		}
		else
		{
			$chkEmAssC=EmployeeAssignRole::where('emp_code',$request->emp_code)->count();
			if($chkEmAssC==0)
			{
				$tab=new EmployeeAssignRole;
				$tab->emp_code=$request->emp_code;
				$tab->system_access_role_id=$request->role_id;
				$tab->company_id=$request->$com;
				$tab->save();
			}
			else
			{
				EmployeeAssignRole::where('emp_code',$request->emp_code)->update(['system_access_role_id'=>$request->role_id]);
			}
			
			$chkExCompany=EmployeeRoleAssignToCompany::where('emp_code',$request->emp_code)->count();
			if($chkExCompany!=0)
			{
				EmployeeRoleAssignToCompany::where('emp_code',$request->emp_code)->delete();
			}
			
				foreach($request->company_id as $com):
					$tab=new EmployeeRoleAssignToCompany;
					$tab->emp_code=$request->emp_code;
					$tab->company_id=$com;
					$tab->save();
				endforeach;
				
				//print_r($request->company_id);
			//exit();
			
		}
		
		
        
        return redirect()->action('EmployeeRoleAssignToCompanyController@index')->with('success','Information Added Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\City  $City
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $del=EmployeeRoleAssignToCompany::destroy($request->id);
        return 1;
    }
}
