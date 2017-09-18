<?php

namespace App\Http\Controllers;

use App\LeaveApplicationMaster;
use App\LeaveApplicationDetail;
use App\LeaveApplicationApprovalFlow;
use App\LeaveComment;
use App\SickLeaveDocument;
use App\LeaveEmailMsgTemplateSettings;
use App\leaveWorkFlowSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Company;
use App\LeavePolicy;
use App\EmployeeInfo;
use App\Year;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
//For PhpMailer
use PHPMailerAutoload;
use PHPMailer;
//For Current Timestamp
use Carbon\Carbon;

class LeaveApplicationMasterController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $logged_emp_code = MenuPageController::loggedUser('emp_code');
        $company = Company::all();
        $leave_policies = LeavePolicy::all();
        $employee = EmployeeInfo::all();

        return view('module.Leave.leaveApplication', ['company' => $company,
            'leave_policies' => $leave_policies,
            'employee' => $employee,
            'logged_emp_com' => $logged_emp_company_id,
            'logged_emp_code' => $logged_emp_code]);
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
        $this->validate($request, [
            'company_id' => 'required',
            'emp_code' => 'required',
            'leave_reason' => 'required',
            'leave_policy_id' => 'required',
            'leave_starts' => 'required',
            'leave_end' => 'required',
            'ttldays' => 'required'
        ]);


        $half_day_leave = $request->half_day_leave ? $request->half_day_leave : 0;


        if (!empty($half_day_leave)) {
            $this->validate($request, [
                'day_part' => 'required',
            ]);
        }

        $day_part = $request->day_part ? $request->day_part : '';

        //For checking in leave application master if any other application already exists or Not
        $startDate = $request->leave_starts;
        $endDate = $request->leave_end;

        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        $check_leave_exists = DB::table('leave_application_masters')
                ->leftjoin('leave_application_details', 'leave_application_details.master_id', '=', 'leave_application_masters.id')
                ->select(DB::raw('leave_application_details.date'))

//      ->where('leave_application_masters.start_date', '<=', $startDate)
//      ->where('leave_application_masters.end_date', '>=', $endDate)
                ->whereBetween('leave_application_masters.start_date', array($startDate, $endDate))
                ->whereBetween('leave_application_masters.end_date', array($startDate, $endDate))
                ->where('leave_application_masters.emp_code', $request->emp_code)
                ->count();

        //$check_leave_exists_qty=count($check_leave_exists);
        // echo '<pre>';
        // echo $check_leave_exists_qty;
        // print_r($check_leave_exists);
        // exit();

        if (empty($check_leave_exists) || $check_leave_exists == 0) {
            //For checking in leave application approval settings and then proceed
            $employee = $request->emp_code;
            $leave_approval_chain_info = leaveWorkFlowSetting::where('leave_work_flow_settings.emp_code', $employee)->count();

            if (!empty($leave_approval_chain_info)) {
                //For saving data in leave application
                $master = new LeaveApplicationMaster;
                $master->company_id = $request->company_id;
                $master->emp_code = $request->emp_code;
                $master->leave_policy_id = $request->leave_policy_id;
                $master->start_date = $request->leave_starts;
                $master->end_date = $request->leave_end;
                $master->total_days_applied = $request->ttldays;
                $master->is_half_day = $half_day_leave;
                $master->half_day = $day_part;
                $master->save();

                $master_id = $master->id; //getting master id from leave master
                //For saving data in leave application detail
                // $startDate = $request->leave_starts;
                // $endDate = $request->leave_end;
                //
          // $begin = new \DateTime($startDate);
                // $end   = new \DateTime($endDate);

                for ($i = $begin; $i <= $end; $i->modify('+1 day')) {
                    $individual_date = $i->format("Y-m-d");

                    $detail = new LeaveApplicationDetail;
                    $detail->company_id = $request->company_id;
                    $detail->master_id = $master_id;
                    $detail->leave_policy_id = $request->leave_policy_id;
                    $detail->date = $individual_date;
                    $detail->emp_code = $request->emp_code;
                    $detail->save();
                }

                //For saving data in leave application approval flows
                $employee = $request->emp_code;

                $leave_approval_chain_info = leaveWorkFlowSetting::where('leave_work_flow_settings.emp_code', $employee)->get();

                foreach ($leave_approval_chain_info as $val):
                    $flow = new LeaveApplicationApprovalFlow;
                    $flow->company_id = $val->company_id;
                    $flow->emp_code = $val->emp_code;
                    $flow->sup_emp_code = $val->sup_emp_code;
                    $flow->step = $val->step;
                    $flow->master_id = $master_id;
                    $flow->save();
                endforeach;

                //create step_flag for step 1 suppervisor in leave flow chain
                $stepset = LeaveApplicationApprovalFlow::where('master_id', $master_id)
                        ->where('step', '1')
                        ->update(['step_flag' => 'Active']);

                //For Saving Data in Sick Leave Document
                //generate file name and move file to upload folder
                //$doc_name="";
                if (!empty($request->leave_docs)) {
                    $name = "leaveDoc_" . time() . '.' . $request->leave_docs->getClientOriginalExtension();
                    $image = $name;
                    $request->leave_docs->move("./upload/leave_documents", $name);

                    //if not empty leave documents
                    $document = new SickLeaveDocument;
                    $document->company_id = $request->company_id;
                    ;
                    $document->emp_code = $request->emp_code;
                    $document->document_name = $image;
                    $document->master_id = $master_id;
                    $document->save();
                }

                //For saving data in leave comments
                $comment = new LeaveComment;
                $comment->company_id = $request->company_id;
                $comment->emp_code = $request->emp_code;
                $comment->comment = $request->leave_reason;
                $comment->master_id = $master_id;
                $comment->save();
                //exit();
                //For Sending Email Notification About This Leave Application To Applicant/Supervisor 1/HR
                //dynamically get company_id
                $logged_emp_company_id = MenuPageController::loggedUser('company_id');

                //Query For Leave And Employee Detail

                $detail = DB::table('leave_application_masters')
                        ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                        ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                        ->leftjoin('employee_departments', 'leave_application_masters.emp_code', '=', 'employee_departments.emp_code')
                        ->leftjoin('companies', 'leave_application_masters.company_id', '=', 'companies.id')
                        ->leftjoin('departments', 'employee_departments.department_id', '=', 'departments.id')
                        ->leftjoin('employee_designations', 'leave_application_masters.emp_code', '=', 'employee_designations.emp_code')
                        ->leftjoin('designations', 'employee_designations.designation_id', '=', 'designations.id')
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
                        ->where('leave_application_masters.id', '=', $master_id)
                        //->where('leave_application_masters.company_id', '=', $logged_emp_company_id)
                        ->get();

                //Get Applicant info From leave_application_approval_flows
                //with leave chain step info
                $supervisor_info = DB::table('leave_application_approval_flows')
                        ->leftjoin('employee_infos', 'leave_application_approval_flows.sup_emp_code', '=', 'employee_infos.emp_code')
                        ->select(DB::raw('employee_infos.email as supervisor_email'))
                        ->where('leave_application_approval_flows.master_id', "=", $master_id)
                        ->where('leave_application_approval_flows.step_flag', "=", 'Active')
                        ->where('leave_application_approval_flows.step', "=", '1')
                        ->first();


                // $email_to=MenuPageController::loggedUser('email');
                // $full_name=MenuPageController::loggedUser('emp_code');
                // $msg_subject = 'Leave Application Submission Successful';
                // $msg_body = 'Test Leave Application Mail';
                //setting necessary values for email
                $company_logo = $detail[0]->company_logo;
                $company_name = $detail[0]->company_name;
                $company_hr_email = $detail[0]->hr_email;

                $applicant_supervisor_email = $supervisor_info->supervisor_email;
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



                //Genarating Dynamic Template Contents
                //For Applicant
                $message = $this->EmailMessageReplacer('1', $applicant_name, $applicant_designation, $applicant_department, $company_name, $leave_type, $leave_start_date, $leave_end_date, $total_days);
                //For HR
                $message_hr = $this->EmailMessageReplacer('2', $applicant_name, $applicant_designation, $applicant_department, $company_name, $leave_type, $leave_start_date, $leave_end_date, $total_days);
                //For Supervisor
                $message_sup = $this->EmailMessageReplacer('3', $applicant_name, $applicant_designation, $applicant_department, $company_name, $leave_type, $leave_start_date, $leave_end_date, $total_days);


                //Email Template For Applicant
                $applicant_subject = 'Leave Application Submission Successful';
                $appmess = $this->LeaveAppMasterEmailHF($applicant_subject, $applicant_name, $applicant_email, $company_logo, $company_name, $current_time, $message, $master_id);



                //Email Template For HR
                $hr_subject = 'Leave Application Recieved';
                $hrmess = $this->LeaveAppMasterEmailHF($hr_subject, $applicant_name, $company_hr_email, $company_logo, $company_name, $current_time, $message_hr, $master_id);


                //Email Template For Supervisor
                $sup_subject = 'Leave Application Recieved';
                $supmess = $this->LeaveAppMasterEmailHF($sup_subject, $applicant_name, $applicant_supervisor_email, $company_logo, $company_name, $current_time, $message_sup, $master_id);


                //exit();

                if ($appmess == 1) {
                    if ($hrmess == 1) {
                        if ($supmess == 1) {
                            return redirect()->action('LeaveApplicationMasterController@index')->with('success', 'Information Added Successfully');
                            //return 1;
                        } else {
                            return redirect()->action('LeaveApplicationMasterController@index')->with('error', 'Sorry! Something Went Wrong. Please Try Again.');
                            //return 0;
                        }
                    } else {
                        return redirect()->action('LeaveApplicationMasterController@index')->with('error', 'Sorry! Something Went Wrong. Please Try Again.');
                        //return 0;
                    }
                } else {
                    return redirect()->action('LeaveApplicationMasterController@index')->with('error', 'Sorry! Something Went Wrong. Please Try Again.');
                    //return 0;
                }
            } else {
                return redirect()->action('LeaveApplicationMasterController@index')->with('error', 'Sorry! You Can Not Apply For Leave. Please Contact With HR.');
                //return 0;
            }
        } else {
            return redirect()->action('LeaveApplicationMasterController@index')->with('error', 'Sorry! You Can Not Apply Again For Leave For Similar Dates. Please Contact With HR.');
        }
    }

    public function EmailMessageReplacer($template_type = 0, $applicant_name, $applicant_designation, $applicant_department, $company_name, $leave_type, $leave_start_date, $leave_end_date, $total_days) {
        $message = '';
        if (!empty($template_type)) {
            $dynamic_mail_temp_applicant = LeaveEmailMsgTemplateSettings::where('template_type_id', $template_type)->select('msg_template')->first();
            $message = $dynamic_mail_temp_applicant->msg_template;
            //Replace Shortcode Values With Dynamic Data
            $findmess = str_replace("APPNAME", $applicant_name, $message);
            $findmess = str_replace("APPDES", $applicant_designation, $findmess);
            $findmess = str_replace("APPDEP", $applicant_department, $findmess);
            $findmess = str_replace("COMPANY", $company_name, $findmess);
            $findmess = str_replace("LEAVETYPE", $leave_type, $findmess);
            $findmess = str_replace("LEAVESTART", $leave_start_date, $findmess);
            $findmess = str_replace("LEAVEEND", $leave_end_date, $findmess);
            $findmess = str_replace("TOTALDAYS", $total_days, $findmess);

            $message = $findmess;
            $message = html_entity_decode($message);
        }

        return $message;
    }

    public function LeaveAppMasterEmailHF($applicant_subject, $applicant_name, $applicant_email, $company_logo, $company_name, $current_time, $message, $master_id) {
        $applicant_msg = "<html>
        <body>
          <div width='100%' style='background: #eceff4; padding: 50px 20px; color: #514d6a;'>
            <div style='max-width: 700px; margin: 0px auto; font-size: 14px'>
              <table border='0' cellpadding='0' cellspacing='0' style='width: 100%; margin-bottom: 20px'>
                <tr>
                  <td style='vertical-align: top; text-align: center;'>
                    <img src='" . url('upload/company_logo') . "/" . $company_logo . "' alt='" . $company_name . "' style='height: 40px'/>
                  </td>
                </tr>
              </table>
              <div style='padding: 40px 40px 20px 40px; background: #fff;  box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);'>
                <p>" . $current_time . "</p>
                " . $message . "
                <a target='_new' href='" . url('Leave/LeaveApplication/Detail/' . $master_id) . "'>View Leave Application</a>
              </div>
              <div style='text-align: center; font-size: 12px; color: #a09bb9; margin-top: 20px'>
                <a href='http://systechunimax.com/'><img class='img-responsive' src='http://192.168.1.36:8000/modules/images/sul-f-logo-BLACK.png' alt='Powered By - Systech Unimax Ltd.'></a>
              </div>
            </div>
          </div>
        </body>
        </html>";

        $app_mail = new EmailController();
        $sendEMail = $app_mail->sendMail($applicant_email, $applicant_subject, $applicant_msg, $applicant_name);
        if ($sendEMail == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    //For showing all data list and detail from leave application master
    public function show() {
        return view('module.Leave.leaveApplicationList');
    }

    public function showDetail($id) {
        //Query For Leave And Employee Detail
        $json = DB::table('leave_application_masters')
                ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                ->leftjoin('employee_departments', 'leave_application_masters.emp_code', '=', 'employee_departments.emp_code')
                ->leftjoin('companies', 'leave_application_masters.company_id', '=', 'companies.id')
                ->leftjoin('departments', 'employee_departments.department_id', '=', 'departments.id')
                ->leftjoin('employee_designations', 'leave_application_masters.emp_code', '=', 'employee_designations.emp_code')
                ->leftjoin('designations', 'employee_designations.designation_id', '=', 'designations.id')
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
        departments.name as emp_department,
        designations.name as emp_designation,
        leave_application_masters.created_at'))
                ->where('leave_application_masters.id', '=', $id)
                ->groupBy('leave_application_masters.emp_code')
                ->get();

        //Query For Leave Approval Workflow Detail
        $status = DB::table('leave_application_approval_flows')
                ->join('employee_infos', 'leave_application_approval_flows.sup_emp_code', '=', 'employee_infos.emp_code')
                ->select(DB::raw('leave_application_approval_flows.step,
        leave_application_approval_flows.sup_emp_code,
        concat(employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as sup_emp_name,
        leave_application_approval_flows.approval_status'))
                ->where('leave_application_approval_flows.master_id', '=', $id)
                ->groupBy('leave_application_approval_flows.step')
                ->orderBy('leave_application_approval_flows.id', 'ASC')
                ->get();

        //Query For Leave Approval Workflow status with master id and sup_emp_code
        $logged_emp_code = MenuPageController::loggedUser('emp_code');
        $appstatus = DB::table('leave_application_approval_flows')
                ->select(DB::raw('leave_application_approval_flows.master_id,
        leave_application_approval_flows.approval_status'))
                ->where('leave_application_approval_flows.master_id', '=', $id)
                ->where('leave_application_approval_flows.sup_emp_code', '=', $logged_emp_code)
                ->where('leave_application_approval_flows.approval_status', '=', 'Pending')
                ->where('leave_application_approval_flows.step_flag', '=', 'Active')
                ->get();


        //Query For Leave Comments Showing with master id and sup_emp_code
        $leaveComments = DB::table('leave_comments')
                ->leftjoin('employee_infos', 'leave_comments.emp_code', '=', 'employee_infos.emp_code')
                ->selectRaw(DB::raw('leave_comments.master_id,
        leave_comments.emp_code,
        leave_comments.comment,
        DATE_FORMAT(leave_comments.created_at, "%D, %M, %Y %h:%i:%s %p") as created_at,
        employee_infos.image,
        concat(employee_infos.first_name," ",employee_infos.last_name) as sup_emp_name'))
                ->where('leave_comments.master_id', '=', $id)
                ->orderBy('leave_comments.id', 'ASC')
                ->get();

        //Query For Leave Approval Workflow sup_emp_code checking
        $suppervisor = DB::table('leave_application_approval_flows')
                ->select(DB::raw('leave_application_approval_flows.sup_emp_code'))
                ->where('leave_application_approval_flows.master_id', '=', $id)
                //->where('leave_application_approval_flows.sup_emp_code','=',$logged_emp_code)
                ->get();

        return view('module.Leave.leaveApplicationDetail', array("data" => $json, "status" => $status, "appstatus" => $appstatus, "leaveComments" => $leaveComments, "suppervisor" => $suppervisor));
    }

    //For Leave Summary of an employee
    public function getSummary($emp_code) {
		
        $sqlEmpGen = EmployeeInfo::select(DB::raw('employee_infos.gender'))
					 ->where('employee_infos.emp_code', $emp_code)
					 ->first();
					 
		app('App\Http\Controllers\NewCalculationLeaveBalanceEmployeeController')->checkNpullLeaveBalanceForUser($emp_code);			 
				
        $ml_or_pl='';
        if ($sqlEmpGen->gender == 1) {
            $ml_or_pl = "AND lp.leave_short_code != 'ML'";
        } elseif ($sqlEmpGen->gender == 2) {
            $ml_or_pl = "AND lp.leave_short_code != 'PL'";
        } else {
            $ml_or_pl = "AND (lp.leave_short_code != 'ML' AND lp.leave_short_code != 'PL')";
        }
        //current year
        $year = date('Y');
        
		
        $json = DB::select("SELECT
                            lp.leave_title,
                            lp.leave_short_code,
                            layd.total_days,
                            layd.availed_days,
                            layd.remaining_days,
                            layd.incash_balance
                            FROM leave_assigned_yearly_datas AS layd
                            LEFT JOIN leave_policies AS lp ON lp.id=layd.leave_policy_id
                            WHERE layd.emp_code='".$emp_code."'
                            AND layd.year = '".$year."'    
                            ".$ml_or_pl."
                            GROUP BY layd.leave_policy_id");
							

//        $json2 = DB::table('leave_assigned_yearly_datas')
//                ->leftjoin('leave_policies', 'leave_assigned_yearly_datas.leave_policy_id', '=', 'leave_policies.id')
//                ->select(DB::raw('leave_policies.leave_title,
//        leave_assigned_yearly_datas.total_days,
//        leave_assigned_yearly_datas.availed_days,
//        leave_assigned_yearly_datas.remaining_days,
//        leave_assigned_yearly_datas.incash_balance'))
//                ->where('leave_assigned_yearly_datas.emp_code', '=', $emp_code)
//                ->where('leave_assigned_yearly_datas.total_days', "!=", 0)
//                ->where('leave_policies.leave_short_code', "!=", $ml_or_pl)
//                ->orderBy('leave_assigned_yearly_datas.id', 'DESC')
//                ->groupBy('leave_assigned_yearly_datas.leave_policy_id')
//                ->get();

//        print_r($json);
//        exit();
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    //end
    //For Leave Application History of an employee
    public function getHistory($emp_code) {
        //current year
        $year = date('Y');
        $yearStart = (new \DateTime(date("Y") . "-01-01"))->format("Y-m-d");
        $yearEnd = (new \DateTime(date("Y") . "-12-31"))->format("Y-m-d");
        
        $json = DB::table('leave_application_masters')
                ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                ->select(DB::raw('leave_policies.leave_title,
        leave_application_masters.start_date,
        leave_application_masters.end_date,
        leave_application_masters.total_days_applied,
        leave_application_masters.is_half_day,
        leave_application_masters.half_day,
        leave_application_masters.leave_status'))
                ->where('leave_application_masters.emp_code', '=', $emp_code)
                ->whereBetween('leave_application_masters.start_date', array($yearStart, $yearEnd))
                ->whereBetween('leave_application_masters.end_date', array($yearStart, $yearEnd))
                ->orderBy('leave_application_masters.id', 'DESC')
                ->get();


        return response()->json(array("data" => $json, "total" => count($json)));
    }

    //end

    public function listShow() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $logged_emp_code = MenuPageController::loggedUser('emp_code');
        $json = DB::table('leave_application_masters')
                ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                ->select(DB::raw('leave_application_masters.id,
        concat(employee_infos.emp_code,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
        leave_policies.leave_title,
        leave_application_masters.start_date,
        leave_application_masters.end_date,
        leave_application_masters.total_days_applied,
        leave_application_masters.is_half_day,
        leave_application_masters.half_day,
        leave_application_masters.leave_status,
        leave_application_masters.created_at'))
                //->where('leave_application_masters.company_id', '=', $logged_emp_company_id)
                ->where('leave_application_masters.emp_code', '=', $logged_emp_code)
                //->groupBy('leave_application_masters.emp_code')
                ->orderBy('leave_application_masters.id', 'DESC')
                ->get();

        return response()->json(array("data" => $json, "total" => count($json)));
    }

    //For Exporting All Data From Leave Application Master with Company Id
    public function exportAllExcel() {

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $logged_emp_code = MenuPageController::loggedUser('emp_code');
        $dbfields = DB::table('leave_application_masters')
                ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                ->select(DB::raw('leave_application_masters.id,
        concat(employee_infos.emp_code,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
        leave_policies.leave_title,
        leave_application_masters.start_date,
        leave_application_masters.end_date,
        leave_application_masters.total_days_applied,
        leave_application_masters.is_half_day,
        leave_application_masters.half_day,
        leave_application_masters.leave_status,
        leave_application_masters.created_at'))
                //->where('leave_application_masters.company_id', '=', $logged_emp_company_id)
                ->where('leave_application_masters.emp_code', '=', $logged_emp_code)
                //->groupBy('leave_application_masters.emp_code')
                ->orderBy('leave_application_masters.id', 'DESC')
                ->get();
        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [
            'id',
            'emp_name',
            'leave_title',
            'start_date',
            'end_date',
            'total_days_applied',
            'is_half_day',
            'half_day',
            'leave_status',
            'created_at'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $key => $field) {
            $excelArray[] = get_object_vars($field);
        }

        // Generate and return the spreadsheet
        \Excel::create('LeaveApplicationData_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Leave Application Info');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Leave Application Info');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportAllPdf() {

        $content = '<h3>Leave Application Info List</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class

        $excelArray = [
            'id',
            'emp_name',
            'leave_title',
            'start_date',
            'end_date',
            'total_days_applied',
            'is_half_day',
            'half_day',
            'leave_status',
            'created_at'
        ];

        if (!empty($excelArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            foreach ($excelArray as $exhead):

                $content .='<th>' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
            $logged_emp_company_id = MenuPageController::loggedUser('company_id');
            $logged_emp_code = MenuPageController::loggedUser('emp_code');
            $datarows = DB::table('leave_application_masters')
                    ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                    ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                    ->select(DB::raw('leave_application_masters.id,
          concat(employee_infos.emp_code,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
          leave_policies.leave_title,
          leave_application_masters.start_date,
          leave_application_masters.end_date,
          leave_application_masters.total_days_applied,
          leave_application_masters.is_half_day,
          leave_application_masters.half_day,
          leave_application_masters.leave_status,
          leave_application_masters.created_at'))
                    //->where('leave_application_masters.company_id', '=', $logged_emp_company_id)
                    ->where('leave_application_masters.emp_code', '=', $logged_emp_code)
                    //->groupBy('leave_application_masters.emp_code')
                    ->orderBy('leave_application_masters.id', 'DESC')
                    ->get();

            if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):
                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td>' . $draw->$fid . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';

            $content .='<br />';

            $content .='<h4>Total : ' . count($datarows) . '</h4>';


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

    //End
    //For showing all data from leave application master
    public function showPending() {
        return view('module.Leave.leaveApplicationListPending');
    }

    public function listShowPending() {
        $logged_emp_code = MenuPageController::loggedUser('emp_code');
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        //Get Employee Codes From
        //  $leave_applied_emp_codes = DB::table('leave_application_masters')
        //  ->select(DB::raw('leave_application_masters.emp_code'))
        //  ->where('leave_application_masters.leave_status', "=", 'Pending')
        //  ->where('leave_application_masters.company_id', "=", $logged_emp_company_id)
        //  ->groupBy('leave_application_masters.emp_code')
        //  ->get();
        //
    //  foreach ($leave_applied_emp_codes as $emp):
        //    $employee_codes = $emp->emp_code;
        //    echo '<pre>';
        //    echo $leave_approval_step_info = DB::table('leave_work_flow_settings')
        //    ->select(DB::raw('leave_work_flow_settings.*'))
        //    ->where('leave_work_flow_settings.emp_code', "=", $employee_codes)
        //    ->get();
        //
    //  endforeach;
        //  exit();





        $json = DB::table('leave_application_masters')
                ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                ->leftjoin('leave_application_approval_flows', 'leave_application_masters.id', '=', 'leave_application_approval_flows.master_id')
                ->select(DB::raw('leave_application_masters.id,
       concat(employee_infos.emp_code,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
       leave_policies.leave_title,
       leave_application_masters.start_date,
       leave_application_masters.end_date,
       leave_application_masters.total_days_applied,
       leave_application_masters.is_half_day,
       leave_application_masters.half_day,
       leave_application_masters.leave_status,
       leave_application_masters.created_at'))
                ->where('leave_application_approval_flows.approval_status', "=", 'Pending')
                ->where('leave_application_approval_flows.step_flag', "=", 'Active')
                ->where('leave_application_approval_flows.sup_emp_code', "=", $logged_emp_code)
                ->orderBy('leave_application_masters.id', 'DESC')
                ->get();


        return response()->json(array("data" => $json, "total" => count($json)));
    }

    //For Exporting Pending Data From Leave Application Master with Company Id
    public function exportPendingExcel() {

        $logged_emp_code = MenuPageController::loggedUser('emp_code');
        $dbfields = DB::table('leave_application_masters')
                ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                ->leftjoin('leave_application_approval_flows', 'leave_application_masters.id', '=', 'leave_application_approval_flows.master_id')
                ->select(DB::raw('leave_application_masters.id,
       concat(employee_infos.emp_code,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
       leave_policies.leave_title,
       leave_application_masters.start_date,
       leave_application_masters.end_date,
       leave_application_masters.total_days_applied,
       leave_application_masters.is_half_day,
       leave_application_masters.half_day,
       leave_application_masters.leave_status,
       leave_application_masters.created_at'))
                ->where('leave_application_masters.leave_status', "=", 'Pending')
                ->where('leave_application_approval_flows.sup_emp_code', "=", $logged_emp_code)

                //->groupBy('leave_application_masters.emp_code')
                ->orderBy('leave_application_masters.id', 'DESC')
                ->get();
        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [
            'id',
            'emp_name',
            'leave_title',
            'start_date',
            'end_date',
            'total_days_applied',
            'is_half_day',
            'half_day',
            'leave_status',
            'created_at'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $key => $field) {
            $excelArray[] = get_object_vars($field);
        }

        // Generate and return the spreadsheet
        \Excel::create('LeaveApplicationPendingData_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Leave Application Pending List');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Leave Application Pending List');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPendingPdf() {

        $content = '<h3>Leave Application Pending List</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class

        $excelArray = [
            'id',
            'emp_name',
            'leave_title',
            'start_date',
            'end_date',
            'total_days_applied',
            'is_half_day',
            'half_day',
            'leave_status',
            'created_at'
        ];

        if (!empty($excelArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
            foreach ($excelArray as $exhead):

                $content .='<th>' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
            $logged_emp_code = MenuPageController::loggedUser('emp_code');
            $datarows = DB::table('leave_application_masters')
                    ->leftjoin('employee_infos', 'leave_application_masters.emp_code', '=', 'employee_infos.emp_code')
                    ->leftjoin('leave_policies', 'leave_application_masters.leave_policy_id', '=', 'leave_policies.id')
                    ->leftjoin('leave_application_approval_flows', 'leave_application_masters.id', '=', 'leave_application_approval_flows.master_id')
                    ->select(DB::raw('leave_application_masters.id,
         concat(employee_infos.emp_code,"-",employee_infos.first_name," ",IFNULL(employee_infos.last_name,"")) as emp_name,
         leave_policies.leave_title,
         leave_application_masters.start_date,
         leave_application_masters.end_date,
         leave_application_masters.total_days_applied,
         leave_application_masters.is_half_day,
         leave_application_masters.half_day,
         leave_application_masters.leave_status,
         leave_application_masters.created_at'))
                    ->where('leave_application_masters.leave_status', "=", 'Pending')
                    ->where('leave_application_approval_flows.sup_emp_code', "=", $logged_emp_code)//E-2 code will be replaced with session logged in emp code
                    //->groupBy('leave_application_masters.emp_code')
                    ->orderBy('leave_application_masters.id', 'DESC')
                    ->get();

            if (!empty($datarows)) {
                $content .='<tbody>';
                foreach ($datarows as $draw):
                    $content .='<tr>';
                    for ($i = 0; $i <= $rows - 1; $i++):
                        $fid = $excelArray[$i];
                        $content .='<td>' . $draw->$fid . '</td>';
                    endfor;
                    $content .='</tr>';
                endforeach;
                $content .='</tbody>';
            }


            $content .='</table>';

            $content .='<br />';

            $content .='<h4>Total : ' . count($datarows) . '</h4>';


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

    //End

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveApplicationMaster $leaveApplicationMaster, $id) {
        $data = LeaveApplicationMaster::find($id);
        $company = Company::all();
        $leave_policies = LeavePolicy::all();
        $employee = EmployeeInfo::all();

        //Query For Leave Comments Showing with master id and sup_emp_code
        $leaveComments = leaveComment::where('master_id', $id)->first();



        return view('module.leave.leaveApplication', ['data' => $data, 'leave_comments' => $leaveComments, 'company' => $company, 'leave_policies' => $leave_policies, 'employee' => $employee]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveApplicationMaster $leaveApplicationMaster, $id) {
        $this->validate($request, [
            'company_id' => 'required',
            'emp_code' => 'required',
            'leave_reason' => 'required',
            'leave_policy_id' => 'required',
            'leave_starts' => 'required',
            'leave_end' => 'required',
            'ttldays' => 'required'
        ]);

        if (empty($request->half_day_leave)) {
            $half_day_leave = 0;
        } else {
            $half_day_leave = $request->half_day_leave;
            if (!empty($half_day_leave)) {
                $this->validate($request, [
                    'day_part' => 'required',
                ]);
            }
        }

        if (empty($request->day_part)) {
            $day_part = "";
        } else {
            $day_part = $request->day_part;
        }

        // if(empty($request->leave_docs))
        // {
        //   $leave_docs="";
        // } else {
        //   $leave_docs=$request->leave_docs;
        // }
        //For checking in leave application approval settings and then proceed
        $employee = $request->emp_code;
        $leave_approval_chain_info = DB::table('leave_work_flow_settings')
                ->where('leave_work_flow_settings.emp_code', "=", $employee)
                ->count();
        // print_r($leave_approval_chain_info);
        // exit();
        if (!empty($leave_approval_chain_info) || $leave_approval_chain_info != 0) {
            //For saving data in leave application
            $master = LeaveApplicationMaster::find($id);
            $master->company_id = $request->company_id;
            $master->emp_code = $request->emp_code;
            $master->leave_policy_id = $request->leave_policy_id;
            $master->start_date = $request->leave_starts;
            $master->end_date = $request->leave_end;
            $master->total_days_applied = $request->ttldays;
            $master->is_half_day = $half_day_leave;
            $master->half_day = $day_part;
            $master->save();
            $master_id = $id;

            //For saving data in leave application detail
            $startDate = $request->leave_starts;
            $endDate = $request->leave_end;
            $begin = new \DateTime($startDate);
            $end = new \DateTime($endDate);

            for ($i = $begin; $i <= $end; $i->modify('+1 day')) {
                $individual_date = $i->format("Y-m-d");

                $detail = new LeaveApplicationDetail;
                $detail->company_id = $request->company_id;
                $detail->master_id = $master_id;
                $detail->leave_policy_id = $request->leave_policy_id;
                $detail->date = $individual_date;
                $detail->emp_code = $request->emp_code;
                $detail->save();
            }
            //For saving data in leave application approval flows
            $employee = $request->emp_code;
            $leave_approval_chain_info = DB::table('leave_work_flow_settings')
                    ->where('leave_work_flow_settings.emp_code', "=", $employee)
                    ->get();
            foreach ($leave_approval_chain_info as $val):
                $flow = new LeaveApplicationApprovalFlow;
                $flow->company_id = $val->company_id;
                $flow->emp_code = $val->emp_code;
                $flow->sup_emp_code = $val->sup_emp_code;
                $flow->step = $val->step;
                $flow->master_id = $master_id;
                $flow->save();
            endforeach;

            //For Saving Data in Sick Leave Document
            //generate file name and move file to upload folder
            //$doc_name="";
            if (!empty($request->leave_docs)) {
                // try {
                //     $file = $request->file('leave_docs');
                //     $name = "leaveDoc_".time() . '.' . $file->getClientOriginalExtension();
                //     $doc_name=$name;
                //
            //     $request->file('leave_docs')->move("./upload/leave_documents", $name);
                // } catch (Illuminate\Filesystem\FileNotFoundException $e) {
                //
            // }

                $name = "leaveDoc_" . time() . '.' . $request->leave_docs->getClientOriginalExtension();
                $image = $name;
                $request->leave_docs->move("./upload/leave_documents", $name);
            }



            $document = new SickLeaveDocument;
            $document->company_id = $request->company_id;
            ;
            $document->emp_code = $request->emp_code;
            $document->document_name = $image;
            $document->master_id = $master_id;
            $document->save();



            //For saving data in leave comments
            $comment = new LeaveComment;
            $comment->company_id = $request->company_id;
            $comment->emp_code = $request->emp_code;
            $comment->comment = $request->leave_reason;
            $comment->master_id = $master_id;
            $comment->save();
            //exit();
            return redirect()->action('LeaveApplicationMasterController@index')->with('success', 'Information Added Successfully');
        } else {
            return redirect()->action('LeaveApplicationMasterController@index')->with('error', 'Sorry! You Can Not Apply For Leave. Please Contact With HR.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveApplicationMaster  $leaveApplicationMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveApplicationMaster $leaveApplicationMaster) {
        //
    }

}
