<?php

namespace App\Http\Controllers;
use App\EmployeeInfo;
use App\Company;
use App\LeavePolicy;
use App\Year;
use App\LeaveAssignedYearlyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;


class LeaveUserDataController extends Controller
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
      $leave_policies=LeavePolicy::where('company_id',$logged_emp_company_id);
      $leave_user_data=LeaveAssignedYearlyData::where('company_id',$logged_emp_company_id);
      $employee=EmployeeInfo::all();
      $year=Year::where('company_id',$logged_emp_company_id);


      //$logged_emp_company_id = '';
      return view('module.settings.leaveUserDataList',['company'=>$company,'leave_policies'=>$leave_policies,'leave_user_data'=>$leave_user_data,'employee'=>$employee,'year'=>$year, 'logged_emp_com' => $logged_emp_company_id]);
    }

    //*For Getting Remaining Leave Balance*//
    public function getLeaveBalance(Request $request)
    {
        $company_id=MenuPageController::loggedUser('company_id');

        $employee_code=$request->employee_code;

        $leave_policy_id=$request->leave_policy_id;

        $leave_starts = $request->leave_starts;
        $leave_end = $request->leave_end;

        //current year
        $year = date('Y');

        //Checking Leave Policy for is_holiday_deduct or not?
        $leave_policy_chk=LeavePolicy::select('is_holiday_deduct')->where('id',$leave_policy_id)->first();
        $leave_policy_is_deduct = $leave_policy_chk->is_holiday_deduct;
        //ends
		
		$cal_company_id = app('App\Http\Controllers\MenuPageController')->UserJobCompany($employee_code);
		if(empty($cal_company_id))
		{
			$cal_company_id=$company_id;
		}

        //generate individual leave dates
        $begin = new \DateTime($leave_starts);
        $end   = new \DateTime($leave_end);
        $offday = 0;
        $leavedays = 0;
        for($i = $begin; $i <= $end; $i->modify('+1 day')){
          $individual_date = $i->format("Y-m-d");
            //select dates,types,status from company calender for the current year with individual_date
            $dates_type_status=DB::table('calendars')
              ->leftjoin('day_types','day_types.id','=','calendars.day_type_id')
              ->select(DB::raw('calendars.date,calendars.is_active,day_types.day_short_code'))
              ->where('calendars.company_id','=',$cal_company_id)
              ->where('calendars.year','=',$year)
              ->where('calendars.date','=',$individual_date)
              ->where(function($q) {
                          $q->where('day_types.day_short_code','=','H')
                            ->orWhere('day_types.day_short_code','=','W');
                      })

              ->get();
              $offday+=count($dates_type_status);
              $leavedays+=count($individual_date);
          }//endfor
          // echo $leavedays;
          // exit();
        //if leave_policy_is_deduct = true
        if($leave_policy_is_deduct==1){
          $leave_datas=DB::table('leave_assigned_yearly_datas')

          ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

          ->select(DB::raw('leave_assigned_yearly_datas.remaining_days,
          leave_policies.is_document_upload,
          leave_policies.document_upload_after_days'))

          //->where('leave_assigned_yearly_datas.company_id','=',$company_id)
          ->where('leave_assigned_yearly_datas.emp_code','=',$employee_code)
          ->where('leave_assigned_yearly_datas.leave_policy_id','=',$leave_policy_id)
          ->where('leave_assigned_yearly_datas.total_days',"!=",0)
          ->get();

          $chk_datas = count($leave_datas);
          if($chk_datas!=0){
            $totalApplied_days = $leavedays;
            $remaining_days = $leave_datas[0]->remaining_days-$totalApplied_days;
            $is_document_upload = $leave_datas[0]->is_document_upload;
            $document_upload_after_days = $leave_datas[0]->document_upload_after_days;


            $datas = [
              'ttl_appl_days'=>$totalApplied_days,
              'remaining_days'=>$remaining_days,
              'is_document_upload'=>$is_document_upload,
              'document_upload_after_days'=>$document_upload_after_days
            ];
          } else {
            $datas = [];
          }

          return response()->json($datas);

        } else {
          $leave_datas=DB::table('leave_assigned_yearly_datas')

          ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

          ->select(DB::raw('leave_assigned_yearly_datas.remaining_days,
          leave_policies.is_document_upload,
          leave_policies.document_upload_after_days'))

          //->where('leave_assigned_yearly_datas.company_id','=',$company_id)
          ->where('leave_assigned_yearly_datas.emp_code','=',$employee_code)
          ->where('leave_assigned_yearly_datas.leave_policy_id','=',$leave_policy_id)
          ->get();

          $chk_datas = count($leave_datas);
          if($chk_datas!=0){
            $totalApplied_days = $leavedays-$offday;
            $remaining_days = $leave_datas[0]->remaining_days-$totalApplied_days;
            $remaining_balance = $remaining_days-$totalApplied_days;

            $is_document_upload = $leave_datas[0]->is_document_upload;
            $document_upload_after_days = $leave_datas[0]->document_upload_after_days;


            $datas = [
              'ttl_appl_days'=>$totalApplied_days,
              'remaining_days'=>$remaining_days,
              'is_document_upload'=>$is_document_upload,
              'document_upload_after_days'=>$document_upload_after_days
            ];
          } else {
            $datas = [];
          }



          return response()->json($datas);
          // return response()->json(array("data"=>$datas,"total"=>count($datas)));
        }
        //ends


        // exit();
        // $leave_datas=DB::table('leave_assigned_yearly_datas')
        //
        // ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')
        //
        // ->select(DB::raw('leave_assigned_yearly_datas.remaining_days,
        // leave_policies.is_document_upload,
        // leave_policies.document_upload_after_days'))
        //
        // ->where('leave_assigned_yearly_datas.company_id','=',$company_id)
        // ->where('leave_assigned_yearly_datas.emp_code','=',$employee_code)
        // ->where('leave_assigned_yearly_datas.leave_policy_id','=',$leave_policy_id)
        // ->get();
        //
        // return response()->json($leave_datas);
    }
    //*End For Getting Remaining Leave Balance*//

    //*For Getting Leave User Data*//
    public function getLeaveUserData(Request $request)
    {
      $logged_emp_company_id = MenuPageController::loggedUser('company_id');

      if (empty($request->company_id) || !isset($request->company_id) || $request->company_id == "undefined" || $request->company_id == "Undefined" || $request->company_id == "" || $request->company_id == 0) {
          $company_id=$logged_emp_company_id;
      } else {
          $company_id=$request->company_id;
      }

        $employee_code=$request->employee_code;

        $leave_policy_id=$request->leave_policy_id;

        $year=$request->year;


          $leave_user_data=DB::table('leave_assigned_yearly_datas')

          ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

          ->select(DB::raw('leave_assigned_yearly_datas.id,
          leave_assigned_yearly_datas.total_days,
          leave_assigned_yearly_datas.availed_days,
          leave_assigned_yearly_datas.remaining_days,
          leave_assigned_yearly_datas.incash_balance,
          leave_assigned_yearly_datas.carry_forward_balance'))

          //->where('leave_assigned_yearly_datas.company_id','=',$company_id)
          ->where('leave_assigned_yearly_datas.emp_code','=',$employee_code)
          ->where('leave_assigned_yearly_datas.leave_policy_id','=',$leave_policy_id)
          ->where('leave_assigned_yearly_datas.year','=',$year)
          ->where('leave_assigned_yearly_datas.total_days',"!=",0)
          ->first();

          if(!empty($leave_user_data)){
            $udatas = [
              'leave_user_data_id'=>$leave_user_data->id,
              'total_days'=>$leave_user_data->total_days,
              'availed_days'=>$leave_user_data->availed_days,
              'remaining_days'=>$leave_user_data->remaining_days,
              'incash_balance'=>$leave_user_data->incash_balance,
              'carry_forward_balance'=>$leave_user_data->carry_forward_balance
            ];
          } else {
            $udatas = [];
          }
          return response()->json($udatas);
        }
        //ends

    public function filterLeaveUserDataList(Request $request)
    {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        //$company_id=$request->company_id;

        if (empty($request->company_id) || !isset($request->company_id) || $request->company_id == "undefined" || $request->company_id == "Undefined" || $request->company_id == "" || $request->company_id == 0) {
            $company_id = $logged_emp_company_id;
        } else {
            $company_id = $request->company_id;
        }


        $department_id=$request->department_id;
        $section_id=$request->section_id;
        $designation_id=$request->designation_id;
        $employee_code=$request->employee_code;
        $leave_policy_id=$request->leave_policy_id;
        $year=$request->year;
        $json=DB::table('leave_assigned_yearly_datas')
        ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')

        ->leftjoin('employee_companies','employee_companies.emp_code','=','leave_assigned_yearly_datas.emp_code')
        ->leftjoin('employee_departments','employee_departments.emp_code','=','leave_assigned_yearly_datas.emp_code')

        ->leftjoin('employee_sections','employee_sections.emp_code','=','leave_assigned_yearly_datas.emp_code')
        ->leftjoin('employee_designations','employee_designations.emp_code','=','leave_assigned_yearly_datas.emp_code')

        ->leftjoin('companies','companies.id','=','leave_assigned_yearly_datas.company_id')
        ->leftjoin('departments','departments.id','=','employee_departments.department_id')

        ->leftjoin('sections','sections.id','=','employee_sections.section_id')
        ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

        ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

        ->select(DB::raw('leave_assigned_yearly_datas.id,
            leave_assigned_yearly_datas.emp_code,
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
            leave_policies.leave_title,
            leave_assigned_yearly_datas.year,
            leave_assigned_yearly_datas.total_days,
            leave_assigned_yearly_datas.availed_days,
            leave_assigned_yearly_datas.remaining_days,
            leave_assigned_yearly_datas.incash_balance,
            leave_assigned_yearly_datas.carry_forward_balance,
            employee_infos.created_at'))

        ->when($company_id, function ($query) use ($company_id) {
            return $query->where('leave_assigned_yearly_datas.company_id',$company_id);
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

        ->when($employee_code, function ($query) use ($employee_code) {
            return $query->where('leave_assigned_yearly_datas.emp_code',$employee_code);
        })

        ->when($leave_policy_id, function ($query) use ($leave_policy_id) {
            return $query->where('leave_assigned_yearly_datas.leave_policy_id',$leave_policy_id);
        })

        ->when($year, function ($query) use ($year) {
            return $query->where('leave_assigned_yearly_datas.year',$year);
        })

        //->groupBy('leave_assigned_yearly_datas.emp_code')
        ->where('leave_assigned_yearly_datas.total_days',"!=",0)
        ->orderBy('leave_assigned_yearly_datas.id','DESC')


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
        return view('settings.leave_user_data_list',['company'=>$data]);
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
  $json=DB::table('leave_assigned_yearly_datas')

  ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')
  ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

  ->select(DB::raw('leave_assigned_yearly_datas.id,
      leave_assigned_yearly_datas.emp_code,
      concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
      leave_policies.leave_title,
      leave_assigned_yearly_datas.year,
      leave_assigned_yearly_datas.total_days,
      leave_assigned_yearly_datas.availed_days,
      leave_assigned_yearly_datas.remaining_days,
      leave_assigned_yearly_datas.incash_balance,
      leave_assigned_yearly_datas.carry_forward_balance,
      leave_assigned_yearly_datas.created_at'))

              //->where('employee_infos.company_id',8)

  //->groupBy('employee_infos.emp_code')
  ->where('leave_assigned_yearly_datas.total_days',"!=",0)

  ->orderBy('leave_assigned_yearly_datas.id','DESC')

  // ->take('10')
  ->get();

    return response()->json(array("data"=>$json,"total"=>count($json)));
}

public function exportFilterExcel($company_id=0,$department_id=0,$section_id=0,$designation_id=0,$employee_code=0,$leave_policy_id=0,$year=0)
{
    if (empty($company_id) || !isset($company_id) || $company_id=='undefined' || $company_id=='Undefined' || $company_id=='' || $company_id==0) {
      $company_id = MenuPageController::loggedUser('company_id');
    } else {
      $company_id;
    }

    $dbfields=DB::table('leave_assigned_yearly_datas')
    ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')

    ->leftjoin('employee_companies','employee_companies.emp_code','=','leave_assigned_yearly_datas.emp_code')
    ->leftjoin('employee_departments','employee_departments.emp_code','=','leave_assigned_yearly_datas.emp_code')

    ->leftjoin('employee_sections','employee_sections.emp_code','=','leave_assigned_yearly_datas.emp_code')
    ->leftjoin('employee_designations','employee_designations.emp_code','=','leave_assigned_yearly_datas.emp_code')

    ->leftjoin('companies','companies.id','=','leave_assigned_yearly_datas.company_id')
    ->leftjoin('departments','departments.id','=','employee_departments.department_id')

    ->leftjoin('sections','sections.id','=','employee_sections.section_id')
    ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

    ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

    ->select(DB::raw('leave_assigned_yearly_datas.id,
        leave_assigned_yearly_datas.emp_code,
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
        leave_policies.leave_title,
        leave_assigned_yearly_datas.year,
        leave_assigned_yearly_datas.total_days,
        leave_assigned_yearly_datas.availed_days,
        leave_assigned_yearly_datas.remaining_days,
        leave_assigned_yearly_datas.incash_balance,
        leave_assigned_yearly_datas.carry_forward_balance,
        employee_infos.created_at'))

    ->when($company_id, function ($query) use ($company_id) {
        return $query->where('leave_assigned_yearly_datas.company_id',$company_id);
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

    ->when($employee_code, function ($query) use ($employee_code) {
        return $query->where('leave_assigned_yearly_datas.emp_code',$employee_code);
    })

    ->when($leave_policy_id, function ($query) use ($leave_policy_id) {
        return $query->where('leave_assigned_yearly_datas.leave_policy_id',$leave_policy_id);
    })

    ->when($year, function ($query) use ($year) {
        return $query->where('leave_assigned_yearly_datas.year',$year);
    })

    ->where('leave_assigned_yearly_datas.total_days',"!=",0)
    //->groupBy('leave_assigned_yearly_datas.emp_code')

    ->orderBy('leave_assigned_yearly_datas.id','DESC')

    ->get();

    // echo $dbfields;
    // exit();
                //->toArray();

        // Initialize the array which will be passed into the Excel
        // generator.
    $excelArray = [];

        // Define the Excel spreadsheet headers
    $excelArray []= [
    'id',
    'emp_code',
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
        $excel->setDescription('Leave User Info');

            // Build the spreadsheet, passing in the payments array
        $excel->sheet('sheet1', function($sheet) use ($excelArray) {
            $sheet->fromArray($excelArray, null, 'A1', false, false);
        });

    })->download('xlsx');
}


public function exportFilterPdf($company_id=0,$department_id=0,$section_id=0,$designation_id=0,$employee_code=0,$leave_policy_id=0,$year=0)
{
    if (empty($company_id) || !isset($company_id) || $company_id=='undefined' || $company_id=='Undefined' || $company_id=='' || $company_id==0) {
      $company_id = MenuPageController::loggedUser('company_id');
    } else {
      $company_id;
    }

    $content='<h3>Leave User Info List</h3>';
    $content .='<h5>Genarated : '.date('d/m/Y H:i:s').'</h5>';
    // instantiate and use the dompdf class

    $excelArray = [
    'id',
    'emp_code',
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
        $datarows = DB::table('leave_assigned_yearly_datas')
        ->leftjoin('employee_infos','leave_assigned_yearly_datas.emp_code','=','employee_infos.emp_code')

        ->leftjoin('employee_companies','employee_companies.emp_code','=','leave_assigned_yearly_datas.emp_code')
        ->leftjoin('employee_departments','employee_departments.emp_code','=','leave_assigned_yearly_datas.emp_code')

        ->leftjoin('employee_sections','employee_sections.emp_code','=','leave_assigned_yearly_datas.emp_code')
        ->leftjoin('employee_designations','employee_designations.emp_code','=','leave_assigned_yearly_datas.emp_code')

        ->leftjoin('companies','companies.id','=','leave_assigned_yearly_datas.company_id')
        ->leftjoin('departments','departments.id','=','employee_departments.department_id')

        ->leftjoin('sections','sections.id','=','employee_sections.section_id')
        ->leftjoin('designations','designations.id','=','employee_designations.designation_id')

        ->leftjoin('leave_policies','leave_assigned_yearly_datas.leave_policy_id','=','leave_policies.id')

        ->select(DB::raw('leave_assigned_yearly_datas.id,
            leave_assigned_yearly_datas.emp_code,
            concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
            leave_policies.leave_title,
            leave_assigned_yearly_datas.year,
            leave_assigned_yearly_datas.total_days,
            leave_assigned_yearly_datas.availed_days,
            leave_assigned_yearly_datas.remaining_days,
            leave_assigned_yearly_datas.incash_balance,
            leave_assigned_yearly_datas.carry_forward_balance,
            employee_infos.created_at'))

        ->when($company_id, function ($query) use ($company_id) {
            return $query->where('leave_assigned_yearly_datas.company_id',$company_id);
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

        ->when($employee_code, function ($query) use ($employee_code) {
            return $query->where('leave_assigned_yearly_datas.emp_code',$employee_code);
        })

        ->when($leave_policy_id, function ($query) use ($leave_policy_id) {
            return $query->where('leave_assigned_yearly_datas.leave_policy_id',$leave_policy_id);
        })

        ->when($year, function ($query) use ($year) {
            return $query->where('leave_assigned_yearly_datas.year',$year);
        })

        //->groupBy('leave_assigned_yearly_datas.emp_code')
        ->where('leave_assigned_yearly_datas.total_days',"!=",0)

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
                //->where('employee_infos.company_id',8)
    //->groupBy('employee_infos.id')
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
        //->groupBy('employee_infos.id')
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
