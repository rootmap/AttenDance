<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;
use Illuminate\Support\Facades\DB;
use App\AttendanceRawData;
use App\AttendanceJobcard;
use App\Company;
use App\EmployeeInfo;

class AttendanceReProcessController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
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
      
      
        
        
        

      return view('module.settings.AttendanceReprocess',
      ['company'=>$company,
      'employee'=>$emp_array,
      'logged_emp_com' => $logged_emp_company_id]);
    }

    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ReprocessLog(Request $request) {
        if(!empty($request->raw_emp_code) && !empty($request->start_date) && !empty($request->end_date))
		{
			//return 1;
			
			$raw_emp_code=$request->raw_emp_code;
			
			$LogDetail=AttendanceRawData::where('raw_emp_code',$raw_emp_code)->first();
			$companyInfo = Company::find($LogDetail->company_id);
            $company_code_length = $companyInfo->emp_code_length;
            $company_prefix = $companyInfo->company_prefix;

            $log_emp_code = intval($LogDetail->raw_emp_code);

            $only_code = str_pad($log_emp_code, $company_code_length, '0', STR_PAD_LEFT); //   
            //$this->zerofill($company_code_length, $incre_id);
            $emp_code = $company_prefix . $only_code;
            
			//return $emp_code;
			
			$start_date=$request->start_date;
			$end_date=$request->end_date;
			
			$clearJobcard=AttendanceJobcard::whereRaw("emp_code='$emp_code' AND (start_date BETWEEN '$start_date' AND '$end_date')")->delete();
			$clearRawLogRead=AttendanceRawData::whereRaw("raw_emp_code='$raw_emp_code' AND (raw_date BETWEEN '$start_date' AND '$end_date')")->update(['is_read'=>0]);
			
			return 1;
			
		}
		else
		{
			return 2;
		}
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
    
    /*
    SELECT * FROM `attendance_raw_datas`
    WHERE `raw_emp_code` LIKE '%0171%'
    AND `raw_date` BETWEEN '11/3/2012 00:00:00' AND '11/5/2012 23:59:00'
    */
    public function show(Request $request) {
        $emp_code = $request->emp_code;
        
        //$employee = str_replace("RPAC","0000",$emp_code);
        $start_date = date_create($request->start_date);
        $startdate = date_format($start_date, 'Y-m-d H:i:s');
        
        $end_date = date_create($request->end_date);
        $enddate = date_format($end_date, 'Y-m-d H:i:s');
        
        $json = DB::select("SELECT * FROM `attendance_raw_datas`
                            WHERE `raw_emp_code`='$emp_code'
                            AND `raw_date` BETWEEN '$startdate' AND '$enddate' ORDER BY raw_date ASC");
        return response()->json(array("data" => $json, "total" => count($json)));
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
