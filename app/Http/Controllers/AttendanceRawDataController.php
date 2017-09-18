<?php

namespace App\Http\Controllers;

use App\AttendanceRawData;
use Illuminate\Http\Request;

use Artisan;
use Illuminate\Support\Facades\DB;
use App\AttendanceJobcard;
use App\Company;
use App\EmployeeInfo;


class AttendanceRawDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	  $logged_emp_company_id = MenuPageController::loggedUser('company_id');
      $logged_emp_code = MenuPageController::loggedUser('emp_code');
      $company=Company::all();
      //$employee=EmployeeInfo::all();
      
      $raw_emp =AttendanceRawData::groupBy('raw_emp_code')->get();
      $emp_array=array();
      if (count($raw_emp) != 0) {
        foreach ($raw_emp as $row):
            
            $companyInfo = Company::find($row->company_id);
            $company_code_length = $companyInfo->emp_code_length;
            $company_prefix = $companyInfo->company_prefix;

            $log_emp_code = intval($row->raw_emp_code);

            $only_code = str_pad($log_emp_code, $company_code_length, '0', STR_PAD_LEFT); //   
            //$this->zerofill($company_code_length, $incre_id);
            $emp_code = $company_prefix . $only_code;
            
            $chkEmp=EmployeeInfo::where('emp_code',$emp_code)->count();
            if($chkEmp!=0)
            {
                $emp_info=EmployeeInfo::where('emp_code',$emp_code)->select('first_name','last_name')->first();


                $final_emp_code = $emp_code.' - '.$emp_info->first_name;
                if(!empty($emp_info->last_name))
                {
                    $final_emp_code .=' '.$emp_info->last_name;
                }

                $push_able_array=array('emp_code'=>$row->raw_emp_code,'name'=>$final_emp_code);

                array_push($emp_array, $push_able_array);
                //print_r($emp_array);
            }
            //exit();
        endforeach;
      }
      
      //echo '<pre>';
     // print_r($emp_array);
      //exit();
      
      
        
        
        

      return view('module.settings.AttendanceLogManualEntry',
      ['company'=>$company,
      'employee'=>$emp_array,
      'logged_emp_com' => $logged_emp_company_id]);
        //return view('module.settings.AttendanceLogManualEntry');
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
		$company_id = MenuPageController::loggedUser('company_id');
		
		$chkData=AttendanceRawData::where('raw_emp_code',$request->emp_code)
									->where('raw_date',$request->start_date)
									->where('raw_time',$request->raw_log_time)
									->count();
		if($chkData==0)							
		{
			$tab=new AttendanceRawData;
			$tab->company_id=$company_id;
			$tab->raw_emp_code=$request->emp_code;
			$tab->machine_id=0;
			$tab->raw_date=$request->start_date;
			$tab->raw_time=$request->raw_log_time;
			$tab->is_read=0;
			$tab->save();
			
			return redirect()->action('AttendanceRawDataController@index')->with('success', 'Manual Log Entry Is Added Successfully.');
		}
		else
		{
			return redirect()->action('AttendanceRawDataController@index')->with('error', 'Manual Log Entry Already Exits.');
		}
		
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AttendanceRawData  $attendanceRawData
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        
        $json = AttendanceRawData::where('machine_id','0')->where('raw_date',date('Y-m-d'))->get();
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AttendanceRawData  $attendanceRawData
     * @return \Illuminate\Http\Response
     */
    public function edit(AttendanceRawData $attendanceRawData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AttendanceRawData  $attendanceRawData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendanceRawData $attendanceRawData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AttendanceRawData  $attendanceRawData
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceRawData $attendanceRawData)
    {
        //
    }
}
