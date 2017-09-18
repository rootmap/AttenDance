<?php

namespace App\Http\Controllers;

use App\Company;
use App\DayType;
use App\LeavePolicy;
use App\AttendanceJobcard;
use App\LeaveAssignedYearlyData;
use App\ManualJobCardEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ManualJobCardEntryController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ReportdayStatus() {
        $alt_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
        $dayStatus = '';
        if (!empty($alt_company_id)) {
            $leave = \DB::table('leave_policies')
                    ->select(\DB::raw("leave_short_code as day_short_code,leave_title"));
                    //->where('company_id', $alt_company_id);

            $day_short_code = \DB::table('day_types')
                    ->select(\DB::raw("day_short_code,title"))
                    ->where('company_id', $alt_company_id);

            $dayStatus = $leave->union($day_short_code)->get();
        } else {
            $leave = \DB::table('leave_policies')
                    ->select(\DB::raw("leave_short_code as day_short_code,leave_title"));

            $day_short_code = \DB::table('day_types')
                    ->select(\DB::raw("day_short_code,title"));

            $dayStatus = $leave->union($day_short_code)->get();
        }
        return $dayStatus;
    }

    public function index() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $company = Company::all();
        $dayType = DB::table('day_types')->where('company_id', $logged_emp_company_id)->get();

        return view('module.settings.manualJobcardEntry', ['company' => $company, 'dayType' => $dayType, 'logged_emp_com' => $logged_emp_company_id]);
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
            'emp_code' => 'required',
            'daytype_id' => 'required',
            'date' => 'required',
        ]);
        
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
		
        if (!empty($logged_emp_company_id)) 
		{
            $company_id = $logged_emp_company_id;
        } 
		else 
		{
            $company_id = $request->company_id;
        }
        
        $currentYear = date('Y');
        $start_date = $request->date;
        $emp_code = $request->emp_code;
        $day_status = $request->daytype_id;

        if ($day_status == 'W' || $day_status == 'P' || $day_status == 'H' || $day_status == 'A') 
		{
            $tab = new ManualJobcardEntry();
            $tab->day_type = $day_status;
            $tab->date = $start_date;
            $tab->emp_code = $emp_code;
            $tab->save();

            $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
            if ($chkjobCard == 0) {
                $tab = new AttendanceJobcard();
                $tab->start_date = $start_date;
                $tab->emp_code = $emp_code;
                $tab->company_id = $company_id;
                $tab->admin_in_time = '00:00:00';
                $tab->admin_out_time = '00:00:00';
                $tab->admin_total_time = '00:00:00';
                $tab->admin_total_ot = '00:00:00';
                $tab->admin_day_status = $day_status;
                $tab->user_day_status = $day_status;
                $tab->audit_day_status = $day_status;
                $tab->save();
                return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Added Successfully');
            } else {
                $chkjobCardId = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                $tab = AttendanceJobcard::find($chkjobCardId->id);
                $tab->admin_day_status = $day_status;
                $tab->user_day_status = $day_status;
                $tab->audit_day_status = $day_status;
                $tab->save();
                return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated Successfully');
            }
        }
		else 
		{
            $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->first();
            $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
            if (!empty($chkLeaveBalance)) {
                $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();

				//print_r($chkLeaveBalance);
				//exit();
				
                if (isset($chkLeaveBalance->remaining_days)) {
						$chkDuplicate = ManualJobCardEntry::where('emp_code', $emp_code)->where('date', $start_date)->delete();
                    

                        $manulaJob = new ManualJobCardEntry();
                        $manulaJob->company_id = $company_id;
                        $manulaJob->emp_code = $emp_code;
                        $manulaJob->day_type = $day_status;
                        $manulaJob->date = $start_date;
                        $manulaJob->save();
						
                        if ($manulaJob->save() == 1) {
                            $pre_availed = $chkLeaveBalance->availed_days;
                            $new_availed = ($pre_availed + 1);
                            $pre_rem = $chkLeaveBalance->remaining_days;
                            $new_rem = ($pre_rem - 1);
							
							if($new_rem<0)
							{
								$new_rem=0;
								$new_availed = $pre_availed;
							}

                            $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

                            //  return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Added Successfully');
                        }

                        $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
                        if ($chkjobCard == 0) {
                            $tab = new AttendanceJobcard();
                            $tab->start_date = $start_date;
                            $tab->emp_code = $emp_code;
                            $tab->company_id = $company_id;
                            $tab->admin_in_time = '00:00:00';
                            $tab->admin_out_time = '00:00:00';
                            $tab->admin_total_time = '00:00:00';
                            $tab->admin_total_ot = '00:00:00';
                            $tab->admin_day_status = $day_status;
                            $tab->user_day_status = $day_status;
                            $tab->audit_day_status = $day_status;
                            $tab->save();
                        } else {
                            $chkjobCardID = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                            $tab = AttendanceJobcard::find($chkjobCardID->id);
                            $tab->admin_day_status = $day_status;
                            $tab->user_day_status = $day_status;
                            $tab->audit_day_status = $day_status;
                            $tab->save();
                        }
                        return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Added Successfully');
                    
                }
				else
				{
					return redirect()->action('ManualJobCardEntryController@index')->with('error', 'Please Assign Leave Balance First.');
				}
            }
			else
			{
				return redirect()->action('ManualJobCardEntryController@index')->with('error', 'Please Assign Leave Balance First.Empty Balance Found.');
			}
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ManualJobCardEntry  $manualJobCardEntry
     * @return \Illuminate\Http\Response
     */
    public function show() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');


        if (empty($logged_emp_company_id) || $logged_emp_company_id == "Undefined") {
            $logged_emp_company_id = 0;
        } else {

            $logged_emp_company_id;
        }
        if (!empty($logged_emp_company_id)) {
            $tab = DB::table('manual_job_card_entries')
                    ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                    ->select(DB::raw('manual_job_card_entries.id,manual_job_card_entries.emp_code,manual_job_card_entries.date,manual_job_card_entries.day_type,manual_job_card_entries.created_at,
                        companies.name as company_id'))
                    ->where('manual_job_card_entries.company_id', $logged_emp_company_id)
                    ->groupBy('manual_job_card_entries.id')
                    ->orderBy('manual_job_card_entries.id', 'DESC')
                    ->get();
        } else {
            $tab = DB::table('manual_job_card_entries')
                    ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                    ->select(DB::raw('manual_job_card_entries.id,manual_job_card_entries.emp_code,manual_job_card_entries.date,manual_job_card_entries.day_type,manual_job_card_entries.created_at,
                        companies.name as company_id'))
                    ->groupBy('manual_job_card_entries.id')
                    ->orderBy('manual_job_card_entries.id', 'DESC')
                    ->get();
        }

        return response()->json(array("data" => $tab, "total" => count($tab)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ManualJobCardEntry  $manualJobCardEntry
     * @return \Illuminate\Http\Response
     */
    public function edit(ManualJobCardEntry $manualJobCardEntry, $id) {
        $data = ManualJobCardEntry::find($id);
        $company = Company::all();
        $dayType = DayType::all();

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.manualJobcardEntry', ['data' => $data, 'dayType' => $dayType, 'company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ManualJobCardEntry  $manualJobCardEntry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ManualJobCardEntry $manualJobCardEntry, $id) {
        $this->validate($request, [

            'emp_code' => 'required',
            'daytype_id' => 'required',
            'date' => 'required',
        ]);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $edited_user_id = MenuPageController::loggedUser('user_id');

        if (!empty($logged_emp_company_id)) {
            $company_id = $logged_emp_company_id;
        } else {
            $company_id = $request->company_id;
        }

        $currentYear = date('Y');
        $start_date = $request->date;
        $emp_code = $request->emp_code;
        $day_status = $request->daytype_id;
        //  $day_status = 'A';


        $chkExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->count();
        if ($chkExDay == 0) {

            if ($day_status == 'W' || $day_status == 'P' || $day_status == 'H' || $day_status == 'A') {
                $tab = new ManualJobcardEntry();
                $tab->day_type = $day_status;
                $tab->date = $start_date;
                $tab->emp_code = $emp_code;
                $tab->save();

                $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
                if ($chkjobCard == 0) {
                    $tab = new AttendanceJobcard();
                    $tab->start_date = $start_date;
                    $tab->emp_code = $emp_code;
                    $tab->company_id = $company_id;
                    $tab->admin_in_time = '00:00:00';
                    $tab->admin_out_time = '00:00:00';
                    $tab->admin_total_time = '00:00:00';
                    $tab->admin_total_ot = '00:00:00';
                    $tab->admin_day_status = $day_status;
                    $tab->user_day_status = $day_status;
                    $tab->audit_day_status = $day_status;
                    $tab->save();
                } else {
                    $chkjobCardGetId = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                    $tab = AttendanceJobcard::find($chkjobCardGetId->id);
                    $tab->admin_day_status = $day_status;
                    $tab->user_day_status = $day_status;
                    $tab->audit_day_status = $day_status;
                    $tab->edit_flag = '1';
                    $tab->edited_emp_code = $edited_user_id;
                    $tab->save();
                }
            } else {
                $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                if (!empty($chkLeaveBalance)) {
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();

                    if (!empty($chkLeaveBalance->remaining_days)) {
                        $chkDuplicate = ManualJobCardEntry::where('emp_code', $emp_code)->where('date', $start_date)->count();
                        if ($chkDuplicate == 0) {

                            $manulaJob = new ManualJobCardEntry();
                            $manulaJob->company_id = $company_id;
                            $manulaJob->emp_code = $emp_code;
                            $manulaJob->day_type = $day_status;
                            $manulaJob->date = $start_date;
                            $manulaJob->save();
                            if ($manulaJob->save() == 1) {
                                $pre_availed = $chkLeaveBalance->availed_days;
                                $new_availed = ($pre_availed + 1);
                                $pre_rem = $chkLeaveBalance->remaining_days;
                                $new_rem = ($pre_rem - 1);

                                $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

                                //  return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Added Successfully');
                            }

                            $chkjobCard = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->count();
                            if ($chkjobCard == 0) {
                                $tab = new AttendanceJobcard();
                                $tab->start_date = $start_date;
                                $tab->emp_code = $emp_code;
                                $tab->company_id = $company_id;
                                $tab->admin_in_time = '00:00:00';
                                $tab->admin_out_time = '00:00:00';
                                $tab->admin_total_time = '00:00:00';
                                $tab->admin_total_ot = '00:00:00';
                                $tab->admin_day_status = $day_status;
                                $tab->user_day_status = $day_status;
                                $tab->audit_day_status = $day_status;
                                $tab->save();
                            } else {
                                $chkjobCardGetID = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                                $tab = AttendanceJobcard::find($chkjobCardGetID->id);

                                $tab->admin_day_status = $day_status;
                                $tab->user_day_status = $day_status;
                                $tab->audit_day_status = $day_status;
                                $tab->edit_flag = '1';
                                $tab->edited_emp_code = $edited_user_id;
                                $tab->save();
                            }
//                            return 1;
                            return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated normal Successfully');
                        }
                    }
                }
            }
        } else {
            // echo 'else';
            $ExDay = ManualJobcardEntry::where('date', $start_date)->where('emp_code', $emp_code)->first();
//            echo $start_date;
            $daytype = $ExDay->day_type;
            if ($daytype == 'W' || $daytype == 'P' || $daytype == 'H' || $daytype == 'A') {
//                echo'else if';
//                    exit();
                //echo $day_status;
                if ($day_status == 'A' || $day_status == 'P' || $day_status == 'W' || $day_status == 'H') {
                    // echo 'last if';
                    $tab = ManualJobcardEntry::find($ExDay->id);
                    $tab->day_type = $day_status;
                    $tab->save();
                    //Attendance job card update
                    $chkjobCardGetid = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                    $tab = AttendanceJobcard::find($chkjobCardGetID->id);
                    $tab->admin_day_status = $day_status;
                    $tab->user_day_status = $day_status;
                    $tab->audit_day_status = $day_status;
                    $tab->save();
//                    return 1;
                    return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated normal Successfully');
                } else {
                    //   echo 'last else';


                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                    if (!empty($chkLeaveBalance)) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                        if (!empty($chkLeaveBalance->remaining_days)) {
                            $pre_availed = $chkLeaveBalance->availed_days;
                            $new_availed = ($pre_availed + 1);
                            $pre_rem = $chkLeaveBalance->remaining_days;
                            $new_rem = ($pre_rem - 1);

                            $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

                            $tab = ManualJobcardEntry::find($ExDay->id);
                            $tab->day_type = $day_status;
                            $tab->save();
                            //Attendance job card update
                            $chkjobCardGetiD = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                            $tab = AttendanceJobcard::find($chkjobCardGetiD->id);
                            $tab->admin_day_status = $day_status;
                            $tab->user_day_status = $day_status;
                            $tab->audit_day_status = $day_status;
                            $tab->edit_flag = '1';
                            $tab->edited_emp_code = $edited_user_id;
                            $tab->save();


//                            return 1;
                            //exit();
                            return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated Successfully');
                        } else {
//                            return 3;

                            return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Balance Found In the System For this Employee');
                            // exit();
                        }
                    } else {
//                        return 2;
                        //exit();
                        return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Entry Found In the System For this Employee');
                    }
                }
            } else {
//                echo 'else else';
                //  echo $day_status;
                if ($day_status == 'W' || $day_status == 'P' || $day_status == 'H' || $day_status == 'A') {
                    //     echo 'if';

                    $tab = ManualJobcardEntry::find($ExDay->id);
                    $tab->day_type = $day_status;
                    $tab->save();
                    //Attendance job card update
                    $chkjobCardgetID = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                    $tab = AttendanceJobcard::find($chkjobCardgetID->id);
                    $tab->admin_day_status = $day_status;
                    $tab->user_day_status = $day_status;
                    $tab->audit_day_status = $day_status;
                    $tab->edit_flag = '1';
                    $tab->edited_emp_code = $edited_user_id;
                    $tab->save();
                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $daytype)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();

                    if (!empty($chkLeaveBalance)) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();

                        if (!empty($chkLeaveBalance->remaining_days)) {
                            //   echo 'last if';
                            $pre_availed = $chkLeaveBalance->availed_days;
                            $new_availed = ($pre_availed - 1);
                            $pre_rem = $chkLeaveBalance->remaining_days;
                            $new_rem = ($pre_rem + 1);

                            $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

//                            return 1;
                            //exit();
                            return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated Successfully');
                        } else {
//                            return 3;

                            return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Balance Found In the System For this Employee');
//                             exit();
                        }
                    } else {
//                        return 2;
                        //exit();
                        return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Entry Found In the System For this Employee');
                    }
                    // return 1;
                } else {
                    // echo'else';
                    // exit()
                    $getLeavePolicyID = LeavePolicy::where('leave_short_code', $day_status)->where('company_id', $company_id)->first();
                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                    if ($chkLeaveBalance != 0) {
                        $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                        if (!empty($chkLeaveBalance->remaining_days)) {
                            //  echo 'if';
                            $tab = ManualJobcardEntry::find($ExDay->id);
                            $tab->day_type = $day_status;

                            $tab->save();

                            //Attendance job card update
                            $chkjobCardgetId = AttendanceJobcard::where('start_date', $start_date)->where('emp_code', $emp_code)->first();
                            $tab = AttendanceJobcard::find($chkjobCardgetId->id);
                            $tab->admin_day_status = $day_status;
                            $tab->user_day_status = $day_status;
                            $tab->audit_day_status = $day_status;
                            $tab->save();

                            // if ($tab->save() == 1) {
                            $pre_availed = $chkLeaveBalance->availed_days;
                            $new_availed = ($pre_availed + 1);
                            $pre_rem = $chkLeaveBalance->remaining_days;
                            $new_rem = ($pre_rem - 1);

                            $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);
                            // return 1;

                            if ($LeaveBalanceUpdate) {
                                $getLeavePolicyID = LeavePolicy::where('leave_short_code', $daytype)->where('company_id', $company_id)->first();
                                $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->count();
                                if ($chkLeaveBalance != 0) {
                                    $chkLeaveBalance = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->first();
                                    if ($chkLeaveBalance->remaining_days != 0) {
                                        $pre_availed = $chkLeaveBalance->availed_days;
                                        $new_availed = ($pre_availed - 1);
                                        $pre_rem = $chkLeaveBalance->remaining_days;
                                        $new_rem = ($pre_rem + 1);

                                        $LeaveBalanceUpdate = LeaveAssignedYearlyData::where('emp_code', $emp_code)->where('year', $currentYear)->where('leave_policy_id', $getLeavePolicyID->id)->update(['availed_days' => $new_availed, 'remaining_days' => $new_rem]);

//                                        return 1;
                                        // exit();
                                        return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated Successfully');
                                    } else {
//                                        return 3;
                                        return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Balance Found In the System For this Employee');
//                                         exit();
                                    }
                                } else {
//                                    return 2;
                                    //exit();
                                    return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Entry Found In the System For this Employee');
                                }
                            }
                            //  }
                            // exit();
                            // return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated Successfully');
                        } else {
//                            return 3;

                            return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Balance Found In the System For this Employee');
                            // exit();
                        }
                    } else {
//                        return 2;
                        //exit();
                        return redirect()->action('ManualJobCardEntryController@index')->with('error', 'NO Leave Entry Found In the System For this Employee');
                    }
                }
            }
        }





//        $manulaJob = ManualJobCardEntry::find($id);
//        $manulaJob->company_id = $request->company_id;
//        $manulaJob->emp_code = $request->emp_code;
//        $manulaJob->day_type = $request->daytype_id;
//        $manulaJob->date = $request->date;
//        $manulaJob->save();
//        return redirect()->action('ManualJobCardEntryController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ManualJobCardEntry  $manualJobCardEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, ManualJobCardEntry $manualJobCardEntry) {
        $del = ManualJobCardEntry::destroy($request->id);
        return 1;
    }

    public function exportExcel() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (empty($logged_emp_company_id) || $logged_emp_company_id == "Undefined") {
            $logged_emp_company_id = 0;
        } else {

            $logged_emp_company_id;
        }
        if (!empty($logged_emp_company_id)) {
            $data = DB::table('manual_job_card_entries')
                    ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                    ->select(DB::raw('manual_job_card_entries.*,
                        companies.name as company_id'))
                    ->where('manual_job_card_entries.company_id', $logged_emp_company_id)
                    ->groupBy('manual_job_card_entries.id')
                    ->orderBy('manual_job_card_entries.id', 'DESC')
                    ->get();
        } else {
            $data = DB::table('manual_job_card_entries')
                    ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                    ->select(DB::raw('manual_job_card_entries.*,
                        companies.name as company_id'))
                    ->groupBy('manual_job_card_entries.id')
                    ->orderBy('manual_job_card_entries.id', 'DESC')
                    ->get();
        }
        // print_r($data);
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [

            'id',
            'Company',
            'Employee Code',
            'Day Status',
            'Date',
            'Created At',
            'Updated At'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($data as $key => $field) {
            $excelArray[] = get_object_vars($field);
        }

        // Generate and return the spreadsheet
        \Excel::create('Manual Jobcard  Entry_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Manual Job Card Entry Data');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Manulajobcard Info');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf() {

        $content = '<h3>Manual Jobcard Entry</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = [

            'Company_Name',
            'Employee_Code',
            'Day_Status',
            'Date'
        ];

        if (!empty($excelArray)) {
            $content .='<table width="100%">';
            $content .='<thead>';
            $content .='<tr>';
//            print_r($excelArray);
//            exit();
            foreach ($excelArray as $exhead):
                $content .='<th>' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';

            $rows = count($excelArray);
            $logged_emp_company_id = MenuPageController::loggedUser('company_id');

            if (empty($logged_emp_company_id) || $logged_emp_company_id == "Undefined") {
                $logged_emp_company_id = 0;
            } else {

                $logged_emp_company_id;
            }
            if (!empty($logged_emp_company_id)) {
                $data = DB::table('manual_job_card_entries')
                        ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                        ->select(DB::raw('
                            companies.name as Company_Name,
                            manual_job_card_entries.emp_code as Employee_Code,
                            manual_job_card_entries.day_type as Day_Status,
                            manual_job_card_entries.date as Date
                        '))
                        ->where('manual_job_card_entries.company_id', $logged_emp_company_id)
                        ->groupBy('manual_job_card_entries.id')
                        ->orderBy('manual_job_card_entries.id', 'DESC')
                        ->get();
            } else {
                $data = DB::table('manual_job_card_entries')
                        ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                        ->select(DB::raw('
                            companies.name as Company_Name,
                            manual_job_card_entries.emp_code as Employee_Code,
                            manual_job_card_entries.day_type as Day_Status,
                            manual_job_card_entries.date as Date
                        '))
                        ->groupBy('manual_job_card_entries.id')
                        ->orderBy('manual_job_card_entries.id', 'DESC')
                        ->get();
            }

            if (!empty($data)) {
                $content .='<tbody>';
                foreach ($data as $draw):
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

            $content .='<h4>Total : ' . count($data) . '</h4>';


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
