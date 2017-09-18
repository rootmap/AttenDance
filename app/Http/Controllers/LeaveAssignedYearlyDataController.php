<?php

namespace App\Http\Controllers;

use App\LeaveAssignedYearlyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Company;
use App\LeavePolicy;
use App\EmployeeInfo;
use App\Year;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class LeaveAssignedYearlyDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $logged_emp_company_id = MenuPageController::loggedUser('company_id');
      $company=Company::all();
      $leave_policies=LeavePolicy::all();
      $leave_user_data=LeaveAssignedYearlyData::all();
      $employee=EmployeeInfo::all();
      $year=Year::where('company_id',$logged_emp_company_id)->get();
      return view('module.settings.leaveUserData',['logged_emp_com'=>$logged_emp_company_id,'company'=>$company,'leave_policies'=>$leave_policies,'employee'=>$employee,'year'=>$year]);
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
      $this->validate($request,[
        'company_id'=>'required',
        'emp_code'=>'required',
        'leave_policy_id'=>'required',
        'year'=>'required',
        'total_days'=>'required|numeric'
      ]);
      $logged_emp_company_id = MenuPageController::loggedUser('company_id');

      if(empty($request->company_id))
      {
        $company_id=$logged_emp_company_id;
      } else {
        $company_id=$request->company_id;
      }

      if(empty($request->carry_forward_balance))
      {
        $carry_forward_balance=0;
      } else {
        $carry_forward_balance=$request->carry_forward_balance;
      }

      if(empty($request->incash_balance))
      {
        $incash_balance=0;
      } else {
        $incash_balance=$request->incash_balance;
      }

      if(empty($request->availed_days))
      {
        $availed_days=0;
      } else {
        $availed_days=$request->availed_days;
      }

      $chksqlexists=LeaveAssignedYearlyData::where('company_id',$company_id)
      ->where('emp_code',$request->emp_code)
      ->where('leave_policy_id',$request->leave_policy_id)
      ->where('year',$request->year)
      ->count();
      
      $sqlexists=LeaveAssignedYearlyData::where('company_id',$company_id)
      ->where('emp_code',$request->emp_code)
      ->where('leave_policy_id',$request->leave_policy_id)
      ->where('year',$request->year)
      ->get();

      $data_exists = count($sqlexists);

      if($chksqlexists!=0){
        $id = $sqlexists[0]->id;
        $remaining_days=$request->total_days-$request->availed_days;
        
        $tab= LeaveAssignedYearlyData::find($id);
        $tab->company_id=$company_id;
        $tab->emp_code=$request->emp_code;
        $tab->leave_policy_id=$request->leave_policy_id;
        $tab->year=$request->year;
        $tab->total_days=$request->total_days;
        $tab->availed_days=$availed_days;
        $tab->remaining_days=$remaining_days;
        $tab->carry_forward_balance=$carry_forward_balance;
        $tab->incash_balance=$incash_balance;
        $tab->save();
        
      } else {
        $remaining_days=$request->total_days-$request->availed_days;

        $tab=new LeaveAssignedYearlyData;
        $tab->company_id=$company_id;
        $tab->emp_code=$request->emp_code;
        $tab->leave_policy_id=$request->leave_policy_id;
        $tab->year=$request->year;
        $tab->total_days=$request->total_days;
        $tab->availed_days=$availed_days;
        $tab->remaining_days=$remaining_days;
        $tab->carry_forward_balance=$carry_forward_balance;
        $tab->incash_balance=$incash_balance;
        $tab->save();
      }



      return redirect()->action('LeaveAssignedYearlyDataController@index')->with('success','Information Added Successfully');
    }
    //End

    public function getEmployees(Request $request)
    {
        $company_id=$request->company_id;
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
        $dataEmployee=EmployeeInfo::whereIN('company_id',$RoleAssignedCompany)
        ->select(\DB::raw('emp_code, concat(emp_code," - ",first_name," ",IFNULL(last_name,"")) as emp_name'))
        ->get();
        return response()->json($dataEmployee);

    }
	
	
	public function getEmployeesSection(Request $request)
    {
        $company_id=$request->company_id;
		
		$emp_code=app('App\Http\Controllers\MenuPageController')->loggedUser('emp_code');
		$getSection=DB::table('employee_sections')->where('emp_code',$emp_code)->select('section_id')->orderBy('id','DESC')->first();
		$section_id=0;
		if(isset($getSection))
		{
			$section_id=$getSection->section_id;
		}
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
        $dataEmployee=DB::table('employee_sections')
		->leftjoin('employee_infos','employee_sections.emp_code','=','employee_infos.emp_code')
		->where('employee_sections.section_id',$section_id)
        ->select(\DB::raw('employee_infos.emp_code, concat(employee_infos.emp_code," - ",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name'))
        ->groupBy('employee_sections.emp_code')
		->get();
        return response()->json($dataEmployee);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */

    public function show(LeaveAssignedYearlyData $leaveAssignedYearlyData)
    {
      $logged_emp_company_id=MenuPageController::loggedUser('company_id');
      $json=DB::table('leave_assigned_yearly_datas')

      ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')
      ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

      ->select(DB::raw('leave_assigned_yearly_datas.id,
          concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
          leave_policies.leave_title,
          leave_assigned_yearly_datas.year,
          leave_assigned_yearly_datas.total_days,
          leave_assigned_yearly_datas.availed_days,
          leave_assigned_yearly_datas.remaining_days,
          leave_assigned_yearly_datas.incash_balance,
          leave_assigned_yearly_datas.carry_forward_balance,
          leave_assigned_yearly_datas.created_at'))

      ->where('leave_assigned_yearly_datas.company_id',$logged_emp_company_id)
      ->where('leave_assigned_yearly_datas.total_days',"!=",0)
      //->groupBy('leave_assigned_yearly_datas.id')

      ->orderBy('leave_assigned_yearly_datas.id','DESC')

      ->get();

      return response()->json(array("data"=>$json,"total"=>count($json)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveAssignedYearlyData $leaveAssignedYearlyData,$id)
    {
      $company=Company::all();
      $leave_policies=LeavePolicy::all();
      $employee=EmployeeInfo::all();
      $year=Year::all();
      $data=LeaveAssignedYearlyData::find($id);
      return view('module.settings.leaveUserData',['data'=>$data,'company'=>$company,'leave_policies'=>$leave_policies,'employee'=>$employee,'year'=>$year]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveAssignedYearlyData $leaveAssignedYearlyData, $id)
    {
      $this->validate($request,[
        'total_days'=>'required|numeric',
        'availed_days'=>'numeric',
        'remaining_days'=>'numeric'
      ]);
      $remaining_days=$request->total_days-$request->availed_days;

      $tab= LeaveAssignedYearlyData::find($id);
      $tab->total_days=$request->total_days;
      $tab->availed_days=$request->availed_days;
      $tab->remaining_days=$remaining_days;

      if(empty($request->carry_forward_balance))
      {
        $carry_forward_balance=0;
      } else {
        $carry_forward_balance=$request->carry_forward_balance;
      }
      $tab->carry_forward_balance=$carry_forward_balance;

      if(empty($request->incash_balance))
      {
        $incash_balance=0;
      } else {
        $incash_balance=$request->incash_balance;
      }
      $tab->incash_balance=$incash_balance;

      $tab->save();

      return redirect()->action('LeaveAssignedYearlyDataController@index')->with('success','Information Added Successfully');
    }

    /*For exporting into excel and pdf*/

    public function exportExcel()
    {
        $logged_emp_company_id=MenuPageController::loggedUser('company_id');

        $dbfields = DB::table('leave_assigned_yearly_datas')

        ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')
        ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

        ->select(DB::raw('leave_assigned_yearly_datas.id,
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
            leave_policies.leave_title,
            leave_assigned_yearly_datas.year,
            leave_assigned_yearly_datas.total_days,
            leave_assigned_yearly_datas.availed_days,
            leave_assigned_yearly_datas.remaining_days,
            leave_assigned_yearly_datas.incash_balance,
            leave_assigned_yearly_datas.carry_forward_balance,
            leave_assigned_yearly_datas.created_at'))

        ->where('leave_assigned_yearly_datas.company_id',$logged_emp_company_id)
        //->groupBy('employee_infos.emp_code')

        ->orderBy('leave_assigned_yearly_datas.id','DESC')
        ->get();
                    //->toArray();

            // Initialize the array which will be passed into the Excel
            // generator.
        $excelArray = [];

            // Define the Excel spreadsheet headers
        $excelArray []= [
        'id',
        'emp_name',
        'leave_title',
        'year',
        'total_days',
        'availed_days',
        'remaining_days',
        'incash_balance',
        'carry_forward_balance',
        'created_at'
        ];

            // Convert each member of the returned collection into an array,
            // and append it to the payments array.
        foreach ($dbfields as $key=>$field) {
            $excelArray[]=get_object_vars($field);
        }

            // Generate and return the spreadsheet
        \Excel::create('LeaveUserData_'.date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

                // Set the spreadsheet title, creator, and description
            $excel->setTitle('Leave User Info');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('LeaveUserInfo');

                // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });

        })->download('xlsx');
    }


    public function exportPdf()
    {

        $content='<h3>Leave User Info List</h3>';
        $content .='<h5>Genarated : '.date('d/m/Y H:i:s').'</h5>';
                // instantiate and use the dompdf class
        $excelArray = [
          'id',
          'emp_name',
          'leave_title',
          'year',
          'total_days',
          'availed_days',
          'remaining_days',
          'incash_balance',
          'carry_forward_balance',
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
            $logged_emp_company_id=MenuPageController::loggedUser('company_id');
            $datarows = $json=DB::table('leave_assigned_yearly_datas')

            ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')
            ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

            ->select(DB::raw('leave_assigned_yearly_datas.id,
                concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
                leave_policies.leave_title,
                leave_assigned_yearly_datas.year,
                leave_assigned_yearly_datas.total_days,
                leave_assigned_yearly_datas.availed_days,
                leave_assigned_yearly_datas.remaining_days,
                leave_assigned_yearly_datas.incash_balance,
                leave_assigned_yearly_datas.carry_forward_balance,
                leave_assigned_yearly_datas.created_at'))

            ->where('leave_assigned_yearly_datas.company_id',$logged_emp_company_id)

            //->groupBy('employee_infos.emp_code')

            ->orderBy('leave_assigned_yearly_datas.id','DESC')
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
        $dompdf->setPaper('Legal', 'landscape');

                // Render the HTML as PDF
        $dompdf->render();

                // Output the generated PDF to Browser
        $dompdf->stream();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveAssignedYearlyData  $leaveAssignedYearlyData
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
      $del=LeaveAssignedYearlyData::destroy($request->id);
      return 1;
    }
}
