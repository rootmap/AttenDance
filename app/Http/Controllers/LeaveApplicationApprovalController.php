<?php

namespace App\Http\Controllers;

use App\LeaveApplicationMaster;
use App\LeaveApplicationDetail;
use App\LeaveApplicationApprovalFlow;
use App\LeaveComment;
use App\LeaveAssignedYearlyData;
use App\LeaveApprovalMethod;
use App\ManualJobCardEntry;
use App\AttendanceJobcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Company;
use App\LeavePolicy;
use App\EmployeeInfo;
use App\Year;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

//For Current Timestamp
use Carbon\Carbon;

class LeaveApplicationApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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

    }

    //For Approving Leave Application
    public function approveLeave(Request $request)
    {
      $id = $request->id;
      $logged_emp_code=MenuPageController::loggedUser('emp_code');
      $logged_emp_company=MenuPageController::loggedUser('company_id');

      $mail_sevice=new LeaveApplicationMasterController();

      //current year
      $year = date('Y');

      //Query For Leave And Employee Detail
      $detail=DB::table('leave_application_masters')
      ->leftjoin('employee_infos','leave_application_masters.emp_code','=','employee_infos.emp_code')
      ->leftjoin('leave_policies','leave_application_masters.leave_policy_id','=','leave_policies.id')
      ->leftjoin('employee_departments','leave_application_masters.emp_code','=','employee_departments.emp_code')
      ->leftjoin('companies','leave_application_masters.company_id','=','companies.id')
      ->leftjoin('departments','employee_departments.department_id','=','departments.id')
      ->leftjoin('employee_designations','leave_application_masters.emp_code','=','employee_designations.emp_code')
      ->leftjoin('designations','employee_designations.designation_id','=','designations.id')

      ->select(DB::raw('leave_application_masters.id,
        employee_infos.emp_code,
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
        leave_policies.leave_title,
        leave_policies.leave_short_code,
        leave_application_masters.start_date,
        leave_application_masters.end_date,
        leave_application_masters.total_days_applied,
        leave_application_masters.is_half_day,
        leave_application_masters.half_day,
        leave_application_masters.leave_status,
        employee_infos.image as emp_photo,
        employee_infos.email as emp_email,
        employee_infos.phone as emp_phone,
        leave_application_masters.company_id,
        companies.name as company_name,
        companies.company_logo,
        companies.hr_email,
        departments.name as emp_department,
        designations.name as emp_designation,
        leave_policies.leave_title,
        leave_application_masters.created_at'))
      ->where('leave_application_masters.id','=',$id)
      ->where('leave_application_masters.company_id','=',$logged_emp_company)
      ->get();


      //setting necessary values for email
      $company_id = $detail[0]->company_id;
      $company_logo = $detail[0]->company_logo;
      $company_name = $detail[0]->company_name;
      $company_hr_email = $detail[0]->hr_email;

      $current_time = Carbon::now('Asia/Dhaka')->toDayDateTimeString();
      //for aplicant
      $applicant_code = $detail[0]->emp_code;
      $applicant_name = $detail[0]->emp_name;
      $applicant_email = $detail[0]->emp_email;
      $applicant_designation = $detail[0]->emp_designation;
      $applicant_department = $detail[0]->emp_department;
      $leave_start_date = $detail[0]->start_date;
      $leave_end_date = $detail[0]->end_date;
      $leave_type = $detail[0]->leave_title;
      $leave_short_code = $detail[0]->leave_short_code;
      $total_days = $detail[0]->total_days_applied;




      //Get leave chain step info
      $leave_approval_step_info = DB::table('leave_application_approval_flows')
       ->select(DB::raw('leave_application_approval_flows.sup_emp_code,leave_application_approval_flows.step'))
       ->where('leave_application_approval_flows.master_id', "=", $id)
       ->where('leave_application_approval_flows.step_flag', "=",'Pending')
       ->where('leave_application_approval_flows.sup_emp_code', "!=", $logged_emp_code)
       ->get();

       $chkstepqty=count($leave_approval_step_info);



       //Get Leave Approval Method Settings
       $leave_approval_method = LeaveApprovalMethod::orderBy('id','DESC')->first();

       $app_method = $leave_approval_method->approval_method;
       $rejection = $leave_approval_method->ends_at_first_rejection;


       $begin = new \DateTime($leave_start_date);
       $end   = new \DateTime($leave_end_date);


       if($app_method=='Individual'){
         //echo $app_method . "-" . $rejection;
         //Update Current Step
         $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Approved','step_flag'=>'Complete']);
         //Update Master Data
         $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Approved']);

         //For Upadate/Insert Data In Mannual Job Card
         if(isset($master)){
           //If leave is approved keep entry to mannual job card //$leave_type
           for($i = $begin; $i <= $end; $i->modify('+1 day')){
             $individual_date = $i->format("Y-m-d") . '<br>';

             //Check and Update Manual Job Card
             $sql_mjobcard = ManualJobCardEntry::where('emp_code',$applicant_code)->where('date',$individual_date)->get();
             $count_mjobcard = count($sql_mjobcard);
             //print_r($sql_mjobcard);


             if($count_mjobcard==0){
               $mjobcard=new ManualJobCardEntry;
               $mjobcard->company_id=$company_id;
               $mjobcard->emp_code=$applicant_code;
               $mjobcard->day_type=$leave_short_code;
               $mjobcard->date=$individual_date;
               $mjobcard->save();
             } else {
               $mjobcard_exist_id = $sql_mjobcard[0]->id;

               $mjobcard= ManualJobCardEntry::find($mjobcard_exist_id);
               $mjobcard->company_id=$company_id;
               $mjobcard->emp_code=$applicant_code;
               $mjobcard->day_type=$leave_short_code;
               $mjobcard->date=$individual_date;
               $mjobcard->save();
             }
             //Ends

             //Check and Update Attendance Job Card
             $sql_attendance_jobcard = AttendanceJobcard::where('emp_code',$applicant_code)->where('start_date',$individual_date)->get();
             $count_attendance_jobcard = count($sql_attendance_jobcard);
             if($count_attendance_jobcard!=0){
               $attendance_jobcard_exist_id = $sql_attendance_jobcard[0]->id;

               $ajobcard= AttendanceJobcard::find($attendance_jobcard_exist_id);
               $ajobcard->admin_day_status=$leave_short_code;
               $ajobcard->user_day_status=$leave_short_code;
               $ajobcard->audit_day_status=$leave_short_code;
               $ajobcard->save();
             }
         }
       }
//exit();
         //starts
         //Get leave policy id
         $leave_policy_query=LeaveApplicationMaster::where('id',$id)->first();
         $leave_policy=$leave_policy_query->leave_policy_id;
         $leave_emp_code=$leave_policy_query->emp_code;

         //Get leave type with id and balance
         $chkLeaveBalance=LeaveAssignedYearlyData::where('emp_code',$leave_emp_code)
                                        ->where('year',$year)
                                        ->where('leave_policy_id',$leave_policy)
                                        ->count();

          $remaining_days=0;
          $availed_days=0;
          $idLeaveBalance=0;
          if($chkLeaveBalance!=0) {
            $leave_type_and_balance_info=LeaveAssignedYearlyData::where('emp_code',$leave_emp_code)
                                                                ->where('year',$year)
                                                                ->where('leave_policy_id',$leave_policy)
                                                                ->first();
            $idLeaveBalance=$leave_type_and_balance_info->id;
            $remaining_days=$leave_type_and_balance_info->remaining_days;
            $availed_days=$leave_type_and_balance_info->availed_days;
          }



          //echo "<pre>";
          //print_r($remaining_days);


          //echo 1;
          //exit();
          //set new values for leave assigned yearly datas
          $rem_days = $remaining_days - $leave_policy_query->total_days_applied;
          $avld_days = $availed_days + $leave_policy_query->total_days_applied;
          $leave_balance_id = $idLeaveBalance;
          $leave_employee_code = $leave_emp_code;

          //Update Leave Balance and availed_days
          $newLeaveData=LeaveAssignedYearlyData::where('id',$leave_balance_id)
          ->where('emp_code',$leave_employee_code)
          ->where('company_id',$logged_emp_company)
          ->where('leave_policy_id',$leave_policy)
          ->where('year',$year)
          ->update(['availed_days'=>$avld_days,'remaining_days'=>$rem_days]);
         //ends


         //Genarating Dynamic Template Contents
         //For Applicant
         $message=$mail_sevice->EmailMessageReplacer('4',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
         //For HR
         $message_hr=$mail_sevice->EmailMessageReplacer('6',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


         //Email Template For Applicant
         $applicant_subject = 'Leave Application Approved';
         $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

         //Email Template For HR
         $hr_subject = 'Leave Application Approved';
         $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


         if(isset($flow) && isset($master)){
           return 1;
           /*if($appmess==1){
             if($hrmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }*/
         } else {
           return 0;
         }

       } elseif ($app_method=='Group') {
         //echo $app_method . "-" . $rejection;
         if($chkstepqty>0){

           //Get Next flow chain step and sup_emp_code
           $nextStep = $leave_approval_step_info[0]->step;
           $nextChainSupEmp = $leave_approval_step_info[0]->sup_emp_code;

           //Update Current Step
           $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Approved','step_flag'=>'Complete']);
           //Update Next Step
           $flow2=LeaveApplicationApprovalFlow::where('master_id',$id)->where('step',$nextStep)->where('sup_emp_code',$nextChainSupEmp)->update(['step_flag'=>'Active']);


           //Get Applicant info From leave_application_approval_flows
           //with leave chain step info
           $supervisor_info = DB::table('leave_application_approval_flows')
           ->leftjoin('employee_infos','leave_application_approval_flows.sup_emp_code','=','employee_infos.emp_code')
           ->select(DB::raw('employee_infos.email as supervisor_email'))
           ->where('leave_application_approval_flows.master_id', "=", $id)
           ->where('leave_application_approval_flows.step_flag', "=",'Active' )
           ->where('leave_application_approval_flows.step', "=", $nextStep)
           ->where('leave_application_approval_flows.sup_emp_code', "=", $nextChainSupEmp)
           ->first();

           $applicant_supervisor_email = $supervisor_info->supervisor_email;

           //For Supervisor
           $message_sup=$mail_sevice->EmailMessageReplacer('3',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
           //Email Template For Supervisor
           $sup_subject = 'Leave Application Recieved';
           $supmess=$mail_sevice->LeaveAppMasterEmailHF($sup_subject,$applicant_name,$applicant_supervisor_email,$company_logo,$company_name,$current_time,$message_sup,$id);

           if(isset($flow) && isset($flow2)){
             if($supmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }


         } else {
           //Update Current Step
           $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Approved','step_flag'=>'Complete']);
           //Update Master Data
           $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Approved']);

           //For Upadate/Insert Data In Mannual Job Card
           if(isset($master)){
             //If leave is approved keep entry to mannual job card //$leave_type
             for($i = $begin; $i <= $end; $i->modify('+1 day')){
               $individual_date = $i->format("Y-m-d") . '<br>';

               //Check and Update Manual Job Card
               $sql_mjobcard = ManualJobCardEntry::where('emp_code',$applicant_code)->where('date',$individual_date)->get();
               $count_mjobcard = count($sql_mjobcard);
               //print_r($sql_mjobcard);


               if($count_mjobcard==0){
                 $mjobcard=new ManualJobCardEntry;
                 $mjobcard->company_id=$company_id;
                 $mjobcard->emp_code=$applicant_code;
                 $mjobcard->day_type=$leave_short_code;
                 $mjobcard->date=$individual_date;
                 $mjobcard->save();
               } else {
                 $mjobcard_exist_id = $sql_mjobcard[0]->id;

                 $mjobcard= ManualJobCardEntry::find($mjobcard_exist_id);
                 $mjobcard->company_id=$company_id;
                 $mjobcard->emp_code=$applicant_code;
                 $mjobcard->day_type=$leave_short_code;
                 $mjobcard->date=$individual_date;
                 $mjobcard->save();
               }
               //Ends

               //Check and Update Attendance Job Card
               $sql_attendance_jobcard = AttendanceJobcard::where('emp_code',$applicant_code)->where('start_date',$individual_date)->get();
               $count_attendance_jobcard = count($sql_attendance_jobcard);
               if($count_attendance_jobcard!=0){
                 $attendance_jobcard_exist_id = $sql_attendance_jobcard[0]->id;

                 $ajobcard= AttendanceJobcard::find($attendance_jobcard_exist_id);
                 $ajobcard->admin_day_status=$leave_short_code;
                 $ajobcard->user_day_status=$leave_short_code;
                 $ajobcard->audit_day_status=$leave_short_code;
                 $ajobcard->save();
               }
           }
         }
         //starts
         //Get leave policy id
         $leave_policy_query=LeaveApplicationMaster::where('id',$id)->first();
         $leave_policy=$leave_policy_query->leave_policy_id;
         $leave_emp_code=$leave_policy_query->emp_code;

         //Get leave type with id and balance
         $chkLeaveBalance=LeaveAssignedYearlyData::where('emp_code',$leave_emp_code)
                                        ->where('year',$year)
                                        ->where('leave_policy_id',$leave_policy)
                                        ->count();

          $remaining_days=0;
          $availed_days=0;
          $idLeaveBalance=0;
          if($chkLeaveBalance!=0) {
            $leave_type_and_balance_info=LeaveAssignedYearlyData::where('emp_code',$leave_emp_code)
                                                                ->where('year',$year)
                                                                ->where('leave_policy_id',$leave_policy)
                                                                ->first();
            $idLeaveBalance=$leave_type_and_balance_info->id;
            $remaining_days=$leave_type_and_balance_info->remaining_days;
            $availed_days=$leave_type_and_balance_info->availed_days;
          }



          //echo "<pre>";
          //print_r($remaining_days);


          //echo 1;
          //exit();
          //set new values for leave assigned yearly datas
          $rem_days = $remaining_days - $leave_policy_query->total_days_applied;
          $avld_days = $availed_days + $leave_policy_query->total_days_applied;
          $leave_balance_id = $idLeaveBalance;
          $leave_employee_code = $leave_emp_code;

            //Update Leave Balance and availed_days
            $newLeaveData=LeaveAssignedYearlyData::where('id',$leave_balance_id)
            ->where('emp_code',$leave_employee_code)
            ->where('company_id',$logged_emp_company)
            ->where('leave_policy_id',$leave_policy)
            ->where('year',$year)
            ->update(['availed_days'=>$avld_days,'remaining_days'=>$rem_days]);
           //ends


           //Genarating Dynamic Template Contents
           //For Applicant
           $message=$mail_sevice->EmailMessageReplacer('4',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
           //For HR
           $message_hr=$mail_sevice->EmailMessageReplacer('6',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


           //Email Template For Applicant
           $applicant_subject = 'Leave Application Approved';
           $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

           //Email Template For HR
           $hr_subject = 'Leave Application Approved';
           $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


           if(isset($flow) && isset($master)){
             return 1;
             /*if($appmess==1){
               if($hrmess==1){
                 return 1;
               } else {
                 return 0;
               }
             } else {
               return 0;
             }*/
           } else {
             return 0;
           }
         }
       } else {
         //echo 'None';
         if($chkstepqty>0){

           //Get Next flow chain step and sup_emp_code
           $nextStep = $leave_approval_step_info[0]->step;
           $nextChainSupEmp = $leave_approval_step_info[0]->sup_emp_code;

           //Update Current Step
           $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Approved','step_flag'=>'Complete']);
           //Update Next Step
           $flow2=LeaveApplicationApprovalFlow::where('master_id',$id)->where('step',$nextStep)->where('sup_emp_code',$nextChainSupEmp)->update(['step_flag'=>'Active']);


           //Get Applicant info From leave_application_approval_flows
           //with leave chain step info
           $supervisor_info = DB::table('leave_application_approval_flows')
           ->leftjoin('employee_infos','leave_application_approval_flows.sup_emp_code','=','employee_infos.emp_code')
           ->select(DB::raw('employee_infos.email as supervisor_email'))
           ->where('leave_application_approval_flows.master_id', "=", $id)
           ->where('leave_application_approval_flows.step_flag', "=",'Active' )
           ->where('leave_application_approval_flows.step', "=", $nextStep)
           ->where('leave_application_approval_flows.sup_emp_code', "=", $nextChainSupEmp)
           ->first();

           $applicant_supervisor_email = $supervisor_info->supervisor_email;

           //For Supervisor
           $message_sup=$mail_sevice->EmailMessageReplacer('3',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
           //Email Template For Supervisor
           $sup_subject = 'Leave Application Recieved';
           $supmess=$mail_sevice->LeaveAppMasterEmailHF($sup_subject,$applicant_name,$applicant_supervisor_email,$company_logo,$company_name,$current_time,$message_sup);

           if(isset($flow) && isset($flow2)){
             if($supmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }


         } else {
           //Update Current Step
           $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Approved','step_flag'=>'Complete']);
           //Update Master Data
           $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Approved']);

           //For Upadate/Insert Data In Mannual Job Card
           if(isset($master)){
             //If leave is approved keep entry to mannual job card //$leave_type
             for($i = $begin; $i <= $end; $i->modify('+1 day')){
               $individual_date = $i->format("Y-m-d") . '<br>';

               //Check and Update Manual Job Card
               $sql_mjobcard = ManualJobCardEntry::where('emp_code',$applicant_code)->where('date',$individual_date)->get();
               $count_mjobcard = count($sql_mjobcard);
               //print_r($sql_mjobcard);


               if($count_mjobcard==0){
                 $mjobcard=new ManualJobCardEntry;
                 $mjobcard->company_id=$company_id;
                 $mjobcard->emp_code=$applicant_code;
                 $mjobcard->day_type=$leave_short_code;
                 $mjobcard->date=$individual_date;
                 $mjobcard->save();
               } else {
                 $mjobcard_exist_id = $sql_mjobcard[0]->id;

                 $mjobcard= ManualJobCardEntry::find($mjobcard_exist_id);
                 $mjobcard->company_id=$company_id;
                 $mjobcard->emp_code=$applicant_code;
                 $mjobcard->day_type=$leave_short_code;
                 $mjobcard->date=$individual_date;
                 $mjobcard->save();
               }
               //Ends

               //Check and Update Attendance Job Card
               $sql_attendance_jobcard = AttendanceJobcard::where('emp_code',$applicant_code)->where('start_date',$individual_date)->get();
               $count_attendance_jobcard = count($sql_attendance_jobcard);
               if($count_attendance_jobcard!=0){
                 $attendance_jobcard_exist_id = $sql_attendance_jobcard[0]->id;

                 $ajobcard= AttendanceJobcard::find($attendance_jobcard_exist_id);
                 $ajobcard->admin_day_status=$leave_short_code;
                 $ajobcard->user_day_status=$leave_short_code;
                 $ajobcard->audit_day_status=$leave_short_code;
                 $ajobcard->save();
               }
           }
         }
         //starts
         //Get leave policy id
         $leave_policy_query=LeaveApplicationMaster::where('id',$id)->first();
         $leave_policy=$leave_policy_query->leave_policy_id;
         $leave_emp_code=$leave_policy_query->emp_code;

         //Get leave type with id and balance
        $chkLeaveBalance=LeaveAssignedYearlyData::where('emp_code',$leave_emp_code)
                                        ->where('year',$year)
                                        ->where('leave_policy_id',$leave_policy)
                                        ->count();

          $remaining_days=0;
          $availed_days=0;
          $idLeaveBalance=0;
          if($chkLeaveBalance!=0) {
            $leave_type_and_balance_info=LeaveAssignedYearlyData::where('emp_code',$leave_emp_code)
                                                                ->where('year',$year)
                                                                ->where('leave_policy_id',$leave_policy)
                                                                ->first();
            $idLeaveBalance=$leave_type_and_balance_info->id;
            $remaining_days=$leave_type_and_balance_info->remaining_days;
            $availed_days=$leave_type_and_balance_info->availed_days;
          }



          //echo "<pre>";
          //print_r($remaining_days);


          //echo 1;
          //exit();
          //set new values for leave assigned yearly datas
          $rem_days = $remaining_days - $leave_policy_query->total_days_applied;
          $avld_days = $availed_days + $leave_policy_query->total_days_applied;
          $leave_balance_id = $idLeaveBalance;
          $leave_employee_code = $leave_emp_code;

            //Update Leave Balance and availed_days
            $newLeaveData=LeaveAssignedYearlyData::where('id',$leave_balance_id)
            ->where('emp_code',$leave_employee_code)
            ->where('company_id',$logged_emp_company)
            ->where('leave_policy_id',$leave_policy)
            ->where('year',$year)
            ->update(['availed_days'=>$avld_days,'remaining_days'=>$rem_days]);
           //ends


           //Genarating Dynamic Template Contents
           //For Applicant
           $message=$mail_sevice->EmailMessageReplacer('4',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
           //For HR
           $message_hr=$mail_sevice->EmailMessageReplacer('6',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


           //Email Template For Applicant
           $applicant_subject = 'Leave Application Approved';
           $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

           //Email Template For HR
           $hr_subject = 'Leave Application Approved';
           $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


           if(isset($flow) && isset($master)){
             return 1;
             /*if($appmess==1){
               if($hrmess==1){
                 return 1;
               } else {
                 return 0;
               }
             } else {
               return 0;
             }*/
           } else {
             return 0;
           }
         }
       }
       //exit();



      //return 1;
    }

    //For Rejecting Leave Application
    public function rejectLeave(Request $request)
    {
      // return 1;
      // exit();
      $id = $request->id;
      $logged_emp_code=MenuPageController::loggedUser('emp_code');
      $logged_emp_company=MenuPageController::loggedUser('company_id');

      $mail_sevice=new LeaveApplicationMasterController();

      //Query For Leave And Employee Detail
      $detail=DB::table('leave_application_masters')
      ->leftjoin('employee_infos','leave_application_masters.emp_code','=','employee_infos.emp_code')
      ->leftjoin('leave_policies','leave_application_masters.leave_policy_id','=','leave_policies.id')
      ->leftjoin('employee_departments','leave_application_masters.emp_code','=','employee_departments.emp_code')
      ->leftjoin('companies','leave_application_masters.company_id','=','companies.id')
      ->leftjoin('departments','employee_departments.department_id','=','departments.id')
      ->leftjoin('employee_designations','leave_application_masters.emp_code','=','employee_designations.emp_code')
      ->leftjoin('designations','employee_designations.designation_id','=','designations.id')

      ->select(DB::raw('leave_application_masters.id,
        employee_infos.emp_code,
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
        leave_policies.leave_title,
        leave_application_masters.start_date,
        leave_application_masters.end_date,
        leave_application_masters.total_days_applied,
        leave_application_masters.is_half_day,
        leave_application_masters.half_day,
        leave_application_masters.leave_status,
        employee_infos.image as emp_photo,
        employee_infos.email as emp_email,
        employee_infos.phone as emp_phone,
        companies.name as company_name,
        companies.company_logo,
        companies.hr_email,
        departments.name as emp_department,
        designations.name as emp_designation,
        leave_policies.leave_title,
        leave_application_masters.created_at'))
      ->where('leave_application_masters.id','=',$id)
      ->where('leave_application_masters.company_id','=',$logged_emp_company)
      ->get();


      //setting necessary values for email
      $company_logo = $detail[0]->company_logo;
      $company_name = $detail[0]->company_name;
      $company_hr_email = $detail[0]->hr_email;

      $current_time = Carbon::now('Asia/Dhaka')->toDayDateTimeString();
      //for aplicant
      $applicant_name = $detail[0]->emp_name;
      $applicant_email = $detail[0]->emp_email;
      $applicant_designation = $detail[0]->emp_designation;
      $applicant_department = $detail[0]->emp_department;
      $leave_start_date = $detail[0]->start_date;
      $leave_end_date = $detail[0]->end_date;
      $leave_type = $detail[0]->leave_title;
      $total_days = $detail[0]->total_days_applied;

      // echo $detail;
      // exit();


      //Get leave chain step info
      $leave_approval_step_info = DB::table('leave_application_approval_flows')
       ->select(DB::raw('leave_application_approval_flows.sup_emp_code,leave_application_approval_flows.step'))
       ->where('leave_application_approval_flows.master_id', "=", $id)
       ->where('leave_application_approval_flows.step_flag', "=",'Pending')
       ->where('leave_application_approval_flows.sup_emp_code', "!=", $logged_emp_code)
       ->get();

       $chkstepqty=count($leave_approval_step_info);



       //Get Leave Approval Method Settings
       $leave_approval_method = LeaveApprovalMethod::where('company_id',$logged_emp_company)->first();

       $app_method = $leave_approval_method->approval_method;
       $rejection = $leave_approval_method->ends_at_first_rejection;

       if($app_method=='Individual'){
         //Update Current Flow Chain Step
         $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Reject','step_flag'=>'Complete']);
         //Update Master Data
         $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Reject']);


         //Genarating Dynamic Template Contents
         //For Applicant
         $message=$mail_sevice->EmailMessageReplacer('5',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
         //For HR
         $message_hr=$mail_sevice->EmailMessageReplacer('7',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


         //Email Template For Applicant
         $applicant_subject = 'Leave Application Rejected';
         $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

         //Email Template For HR
         $hr_subject = 'Leave Application Rejected';
         $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


         if(isset($flow) && isset($master)){
           return 1;
           /*if($appmess==1){
             if($hrmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }*/
         } else {
           return 0;
         }
       } elseif ($app_method=='Group') {
         if($chkstepqty>0){

           //Get Next flow chain step and sup_emp_code
           $nextStep = $leave_approval_step_info[0]->step;
           $nextChainSupEmp = $leave_approval_step_info[0]->sup_emp_code;

           //Update Current Step
           $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Reject','step_flag'=>'Complete']);
           //Update Next Step
           $flow2=LeaveApplicationApprovalFlow::where('master_id',$id)->where('step',$nextStep)->where('sup_emp_code',$nextChainSupEmp)->update(['step_flag'=>'Active']);


           //Get Applicant info From leave_application_approval_flows
           //with leave chain step info
           $supervisor_info = DB::table('leave_application_approval_flows')
           ->leftjoin('employee_infos','leave_application_approval_flows.sup_emp_code','=','employee_infos.emp_code')
           ->select(DB::raw('employee_infos.email as supervisor_email'))
           ->where('leave_application_approval_flows.master_id', "=", $id)
           ->where('leave_application_approval_flows.step_flag', "=",'Active' )
           ->where('leave_application_approval_flows.step', "=", $nextStep)
           ->where('leave_application_approval_flows.sup_emp_code', "=", $nextChainSupEmp)
           ->first();

           $applicant_supervisor_email = $supervisor_info->supervisor_email;

           //For Supervisor
           $message_sup=$mail_sevice->EmailMessageReplacer('3',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
           //Email Template For Supervisor
           $sup_subject = 'Leave Application Recieved';
           $supmess=$mail_sevice->LeaveAppMasterEmailHF($sup_subject,$applicant_name,$applicant_supervisor_email,$company_logo,$company_name,$current_time,$message_sup,$id);

           if(isset($flow) && isset($flow2)){
             if($supmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }


         } else {
           //Update Current Flow Chain Step
           $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Reject','step_flag'=>'Complete']);
           //Update Master Data
           $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Reject']);


           //Genarating Dynamic Template Contents
           //For Applicant
           $message=$mail_sevice->EmailMessageReplacer('5',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
           //For HR
           $message_hr=$mail_sevice->EmailMessageReplacer('7',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


           //Email Template For Applicant
           $applicant_subject = 'Leave Application Rejected';
           $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

           //Email Template For HR
           $hr_subject = 'Leave Application Rejected';
           $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


           if(isset($flow) && isset($master)){
             return 1;
             /*if($appmess==1){
               if($hrmess==1){
                 return 1;
               } else {
                 return 0;
               }
             } else {
               return 0;
             }*/
           } else {
             return 0;
           }
         }
       } elseif ($app_method=='Group' && $rejection=='1') {
         //Update Current Flow Chain Step
         $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Reject','step_flag'=>'Complete']);
         //Update Master Data
         $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Reject']);


         //Genarating Dynamic Template Contents
         //For Applicant
         $message=$mail_sevice->EmailMessageReplacer('5',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
         //For HR
         $message_hr=$mail_sevice->EmailMessageReplacer('7',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


         //Email Template For Applicant
         $applicant_subject = 'Leave Application Rejected';
         $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

         //Email Template For HR
         $hr_subject = 'Leave Application Rejected';
         $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


         if(isset($flow) && isset($master)){
           return 1;
           /*if($appmess==1){
             if($hrmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }*/
         } else {
           return 0;
         }
       } elseif ($app_method=='Individual' && $rejection=='1') {
         //Update Current Flow Chain Step
         $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Reject','step_flag'=>'Complete']);
         //Update Master Data
         $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Reject']);


         //Genarating Dynamic Template Contents
         //For Applicant
         $message=$mail_sevice->EmailMessageReplacer('5',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
         //For HR
         $message_hr=$mail_sevice->EmailMessageReplacer('7',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


         //Email Template For Applicant
         $applicant_subject = 'Leave Application Rejected';
         $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

         //Email Template For HR
         $hr_subject = 'Leave Application Rejected';
         $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


         if(isset($flow) && isset($master)){
           return 1;
           /*if($appmess==1){
             if($hrmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }*/
         } else {
           return 0;
         }
       } else {
         //Update Current Flow Chain Step
         $flow=LeaveApplicationApprovalFlow::where('master_id',$id)->where('sup_emp_code',$logged_emp_code)->update(['approval_status'=>'Reject','step_flag'=>'Complete']);
         //Update Master Data
         $master=LeaveApplicationMaster::where('id',$id)->update(['leave_status'=>'Reject']);


         //Genarating Dynamic Template Contents
         //For Applicant
         $message=$mail_sevice->EmailMessageReplacer('5',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);
         //For HR
         $message_hr=$mail_sevice->EmailMessageReplacer('7',$applicant_name,$applicant_designation,$applicant_department,$company_name,$leave_type,$leave_start_date,$leave_end_date,$total_days);


         //Email Template For Applicant
         $applicant_subject = 'Leave Application Rejected';
         $appmess=$mail_sevice->LeaveAppMasterEmailHF($applicant_subject,$applicant_name,$applicant_email,$company_logo,$company_name,$current_time,$message,$id);

         //Email Template For HR
         $hr_subject = 'Leave Application Rejected';
         $hrmess=$mail_sevice->LeaveAppMasterEmailHF($hr_subject,$applicant_name,$company_hr_email,$company_logo,$company_name,$current_time,$message_hr,$id);


         if(isset($flow) && isset($master)){
           return 1;
           /*if($appmess==1){
             if($hrmess==1){
               return 1;
             } else {
               return 0;
             }
           } else {
             return 0;
           }*/
         } else {
           return 0;
         }
       }

     }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */

    public function show()
    {

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveApplicationApproval $leaveApplicationApproval)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveApplicationApproval $leaveApplicationApproval)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveApplicationApproval $leaveApplicationApproval)
    {
        //
    }
}
