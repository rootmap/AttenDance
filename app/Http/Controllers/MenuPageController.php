<?php

namespace App\Http\Controllers;

use App\SystemModule;
use App\SystemSubModule;
use App\SystemModulePage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use App\EmployeeInfo;
use App\Company;
use App\UserMapRole;
use App\EmployeeAssignRole;
use Auth;
use App\EmployeeRoleAssignToCompany;
//MenuPageController::loggedUser('company_prefix')

class MenuPageController extends Facade {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected static function getFacadeAccessor() {
        //what you want
        return $this;
    }

    public static function dayStatus() {
        $obj = new MenuPageController();


        $company_id = $obj->loggedUser('company_id');

        $alt_company_id = $company_id;
        $dayStatus = '';
        if (!empty($alt_company_id)) {
            $leave = DB::select("SELECT leave_short_code as day_short_code FROM `leave_policies` WHERE company_id=$alt_company_id");
            $day_short_code = DB::select("SELECT day_short_code FROM `day_types` WHERE company_id=$alt_company_id");
            $dayStatus = array_merge($day_short_code, $leave);
        } else {
            $leave = DB::select("SELECT leave_short_code as day_short_code FROM `leave_policies`");
            $day_short_code = DB::select("SELECT day_short_code FROM `day_types`");
            $dayStatus = array_merge($day_short_code, $leave);
        }
        return response()->json($dayStatus);
//         return $dayStatus;
    }

    public static function EmployeeRoleInfo($emp_code = 0, $company_id = 0) {
        $role_id = 0;
        $chk = EmployeeAssignRole::where('emp_code', $emp_code)
                ->count();

        if ($chk != 0) {
            $sql = EmployeeAssignRole::where('emp_code', $emp_code)->first();
            $role_id = $sql->system_access_role_id;
        }

			//echo $role_id;
			//exit();

        return $role_id;
    }
	
	
	

public static function userModulePagePermissionCheck($url='')
    {
        $obj = new MenuPageController();
        

        $company_id = $obj->loggedUser('company_id');
        $emp_code = $obj->loggedUser('emp_code');
        $role_id = $obj->EmployeeRoleInfo($emp_code, $company_id);
		//exit();
        $permission_Denied=0;
        $chkPermittedModule=DB::table('system_module_pages')->where('link',$url)->count();
        if($chkPermittedModule!=0)
        {
            $PermittedModule=DB::table('system_module_pages')->where('link',$url)->first();
            $PermittedModulePage_id=$PermittedModule->id;


            $PermittedModuleNRole=DB::table('user_map_roles')
                                 ->where('system_access_role_id',$role_id)
                                 ->where('system_module_page_id',$PermittedModulePage_id)
                                 ->count();
            if($PermittedModuleNRole==1)
            {
                $permission_Denied=0;
            }
            else
            {
                $permission_Denied=1;
            }


        }

        if($permission_Denied==1)
        {
            $html='';
            $html .='<html>';
            $html .='<head>';
            $html .='<script>';
            
            $html .='window.location.href="'.url(route('notfound')).'";';


            $html .='</script>';
            

            $html .='</head>';
            $html .='</html>';

            echo $html;

            exit();
        }
        
        //echo $permission_Denied;
        

    }




    public static function userModulePermission($emp_role = 0, $emp_code=0, $company_id = 0) {

        //echo $emp_role;
        //echo $emp_code;
		//exit();
        $role_module_id = [];
        if ($emp_code==0) {
			//echo $emp_role;
			//exit();
            $chk = UserMapRole::where('system_access_role_id', $emp_role)->count();
            if ($chk != 0) {
                $sql = UserMapRole::where('system_access_role_id', $emp_role)->get();
				
				//print_r($sql);
				//exit();
				
                foreach ($sql as $row):
                    $role_module_id[] = $row->system_module_id;
                endforeach;
            }
        }
        else {
            $chk = UserMapRole::where('system_access_role_id',$emp_role)
                    ->where('user_id',$emp_code)
                    ->count();
            if ($chk != 0) {
                $sql = UserMapRole::where('system_access_role_id', $emp_role)
                        ->where('user_id',$emp_code)
                        ->get();
                foreach ($sql as $row):
                    $role_module_id[] = $row->system_module_id;
                endforeach;
            }
        }
		
		
		//print_r($role_module_id);
		//exit();

        if(count($role_module_id)==0)
        {
            $sql = UserMapRole::all();
                foreach ($sql as $row):
                    $role_module_id[] = $row->system_module_id;
                endforeach;
        }



        //echo 3;
        return $role_module_id;
    }

    public static function userSubModulePermission($emp_role = 0, $emp_code = 0, $company_id = 0) {
        
        $role_SUbmodule_id = [];
        if ($emp_code == 0) {
            $chk = UserMapRole::where('system_access_role_id', $emp_role)->count();
            if ($chk != 0) {
                $sql = UserMapRole::where('system_access_role_id', $emp_role)->get();
                foreach ($sql as $row):
                    $role_SUbmodule_id[] = $row->system_sub_module_id;
                endforeach;
            }
        }
        else 
        {
            $chk = UserMapRole::where('system_access_role_id', $emp_role)
                    //->where('company_id', $company_id)
                    ->where('emp_code', $emp_code)
                    ->count();
            if ($chk != 0) {
                $sql = UserMapRole::where('system_access_role_id', $emp_role)
                        //->where('company_id', $company_id)
                        ->where('emp_code', $emp_code)
                        ->get();
                foreach ($sql as $row):
                    $role_SUbmodule_id[] = $row->system_sub_module_id;
                endforeach;
            }
        }

        if(count($role_SUbmodule_id)==0)
        {
            $sql = UserMapRole::all();
                foreach ($sql as $row):
                    $role_SUbmodule_id[] = $row->system_sub_module_id;
                endforeach;
        }
        
        return $role_SUbmodule_id;
    }
    
    public static function userPagesModulePermission($emp_role = 0, $emp_code = 0, $company_id = 0) {
        
        $role_page_id = [];
        if ($emp_code == 0) {
            $chk = UserMapRole::where('system_access_role_id', $emp_role)->count();
            if ($chk != 0) {
                $sql = UserMapRole::where('system_access_role_id', $emp_role)->get();
                foreach ($sql as $row):
                    $role_page_id[] = $row->system_module_page_id;
                endforeach;
            }
            
//            echo "<pre>";
//            print_r($sql);
//            exit();
            
        }
        else 
        {
            $chk = UserMapRole::where('system_access_role_id', $emp_role)
                    //->where('company_id', $company_id)
                    ->where('emp_code', $emp_code)
                    ->count();
            if ($chk != 0) {
                $sql = UserMapRole::where('system_access_role_id', $emp_role)
                        //->where('company_id', $company_id)
                        ->where('emp_code', $emp_code)
                        ->get();
                foreach ($sql as $row):
                    $role_page_id[] = $row->system_module_page_id;
                endforeach;
            }
        }

        if(count($role_page_id)==0)
        {
            $sql = UserMapRole::all();
                foreach ($sql as $row):
                    $role_page_id[] = $row->system_module_page_id;
                endforeach;
        }
        
        return $role_page_id;
    }

    public static function showMenuSite($param = 0) {
        $obj = new MenuPageController();


        $company_id = $obj->loggedUser('company_id');
        $emp_code = $obj->loggedUser('emp_code');
        $role_id = $obj->EmployeeRoleInfo($emp_code, $company_id);


        $personal_module = array_unique($obj->userModulePermission($role_id, $emp_code, $company_id));


         /*print_r($personal_module);
          exit(); */

        if (empty($param)) {
            $data = SystemModule::whereIn('id', $personal_module)
                    ->orderBy('name', 'ASC')
                    ->get();
            return $data;
        } else {
            $data = SystemModule::where('id', $param)
                    //->where('company_id', $company_id)
                    ->whereIn('id', $personal_module)
                    ->orderBy('name', 'ASC')
                    ->get();
            return $data;
        }
    }
	
	

    public static function showSubMenuSite($param = 0) { 
        
        $obj = new MenuPageController();


        $company_id = $obj->loggedUser('company_id');
        $emp_code = $obj->loggedUser('emp_code');
        $role_id = $obj->EmployeeRoleInfo($emp_code, $company_id);

        $personal_module = array_unique($obj->userSubModulePermission($role_id, $emp_code, $company_id));
        
        if (!empty($param)) {
            $data = SystemSubModule::where('system_module_id', $param)
                    ->whereIn('id', $personal_module)
                    ->orderBy('name', 'ASC')
                    ->get();
        } else {
            $data = array();
        }

        return $data;
    }

    public static function showSitePage($param = 0, $param1 = 0) {
        
        $obj = new MenuPageController();
        $company_id = $obj->loggedUser('company_id');
        $emp_code = $obj->loggedUser('emp_code');
        $role_id = $obj->EmployeeRoleInfo($emp_code, $company_id);

        $personal_module = array_unique($obj->userPagesModulePermission($role_id, $emp_code, $company_id));
        if (!empty($param)) {
            $data = SystemModulePage::where('system_module_id', $param)
                    ->whereIn('id', $personal_module)
                    ->where('system_sub_module_id', $param1)
                    ->orderBy('name', 'ASC')
                    ->get();
        } else {
            $data = array();
        }

        return $data;
    }

    public static function showSitePageByUrlCategory($param = '') {


        if (!empty($param)) {

            $data = DB::table('system_module_pages')
                    ->where('system_module_pages.link', $param)
                    ->orderBy('name', 'ASC')
                    ->get();

        } else {
            $data = array();
        }



        return $data;
    }

    public static function genarateKendoDatePicker($arr = array()) {
        $str = '';
        if (!empty($arr)) {



            $str .='<link rel="stylesheet" type="text/css" href="' . url('vendors/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') . '">
        <link rel="stylesheet" type="text/css" href="' . url('vendors/fullcalendar/dist/fullcalendar.min.css') . '">

                                            <script src="' . url('vendors/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') . '"></script>
                                            <script src="' . url('vendors/fullcalendar/dist/fullcalendar.min.js') . '"></script>';

            foreach ($arr as $fid):
                $str .="<script> $(document).ready(function() {

                $('input[name=" . $fid . "]').datetimepicker({
                    format: 'YYYY-MM-DD',
                    icons: {
                        time: 'fa fa-clock-o',
                        date: 'fa fa-calendar',
                        up: 'fa fa-chevron-up',
                        down: 'fa fa-chevron-down',
                        previous: 'fa fa-chevron-left',
                        next: 'fa fa-chevron-right',
                        today: 'fa fa-screenshot',
                        clear: 'fa fa-trash',
                        close: 'fa fa-remove'
                    }
                }).on('dp.change', function () {
                    console.log($(this).val());
                });

             }); </script>  ";
            endforeach;
        }








        return $str;
    }
	
	public static function UserJobCompany($emp_code)
	{
		$alt_company_id=0;
		if(!empty($emp_code))
		{
			$chkJObCompany=DB::table('employee_weekend_different_company_policies')
						   ->where('emp_code',$emp_code)
						   ->orderBy('id','DESC')
						   ->count();
						   
			if($chkJObCompany!=0)
			{
				$sqlJObCompany=DB::table('employee_weekend_different_company_policies')
						   ->where('emp_code',$emp_code)
						   ->orderBy('id','DESC')
						   ->first();
				$alt_company_id=$sqlJObCompany->company_id;		   
			}
				
						   
		}
		
		return $alt_company_id;
			
	}

    public static function loggedUser($keyParam = '') {
        $data = array();
        //echo Auth::id();

        if (Auth::id()) {
            $count = EmployeeInfo::where('emp_code', Auth::user()->username)->count();
            if ($count == 0) {
                //$finddata=EmployeeInfo::where('user_id',121)->get();
                /*                echo "<pre>";
                  print_r($finddata);
                  exit(); */
                //$company_info=Company::where('id',$finddata[0]->company_id)->get();
                 $data = array('empInfo' => array(), 'comInfo' => array());
            } else {
                $finddata = EmployeeInfo::where('emp_code', Auth::user()->username)->get();
                $company_info = Company::where('id', $finddata[0]->company_id)->get();
				

                $data = array('empInfo' => $finddata, 'comInfo' => $company_info);
            }
        } else {
            @$finddata = EmployeeInfo::orderby('id','DESC')->take(1)->get();
            @$company_info = Company::orderby('id','DESC')->take(1)->get();

            //print_r($finddata);
            //exit();
            $data = array('empInfo' => $finddata, 'comInfo' => $company_info);
        }

        if (empty($keyParam)) {
            return $data;
        } else {
            if (isset($data['empInfo'][0]->$keyParam)) {
                return $data['empInfo'][0]->$keyParam;
            } elseif (isset($data['comInfo'][0]->$keyParam)) {
                return $data['comInfo'][0]->$keyParam;	
            } else {
                return 0;
            }
        }
    }
	
	
	
	
	public static function showRawMenuSite($param = 0) {

        $data = SystemModule::orderBy('name', 'ASC')
							->get();
        return $data;
        
    }
	
	public static function showRawSubMenuSite($param = 0) { 
	

        if (!empty($param)) {
            $data = SystemSubModule::where('system_module_id', $param)
                    ->orderBy('name', 'ASC')
                    ->get();
        } else {
            $data = array();
        }

        return $data;
    }
	
	public static function showRawSitePage($param = 0, $param1 = 0) {
        
        if (!empty($param)) {
            $data = SystemModulePage::where('system_module_id', $param)
                    ->where('system_sub_module_id', $param1)
                    ->orderBy('name', 'ASC')
                    ->get();
        } else {
            $data = array();
        }

        return $data;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }
	
	public static function AssignedCompany()
	{
		$obj = new MenuPageController();
        $emp_code = $obj->loggedUser('emp_code');
		$company_id = $obj->loggedUser('company_id');
		$chkAssignedCompany=EmployeeRoleAssignToCompany::where('emp_code',$emp_code)->count();
		$loopCompany=array();
		if($chkAssignedCompany!=0)
		{
			$sqlAssignedCompany=EmployeeRoleAssignToCompany::where('emp_code',$emp_code)->get();
			foreach($sqlAssignedCompany as $row):
				array_push($loopCompany,$row->company_id);
			endforeach;
		}
		else
		{
			array_push($loopCompany,$company_id);
		}
		
		return $loopCompany;
		
	}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}
