<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
// Only For Test Environment

Auth::routes();

Route::get('/', function () {
    return redirect(url('Dashboard'));
});

Route::get('/Blank', function() {
    return view('module.BlankPage.index');
});
Route::get('/Forms', function() {
    return view('module.Forms.index');
});

Route::get('/Kendo', function() {
    return view('module.Kendo.index');
});


Route::get('/ProcessAtLog', function() {
    $job = new \App\Jobs\AttendanceLogProcess();
    dispatch($job);
});

Route::get('pagenotfound',['as'=>'notfound','uses'=>'KendoController@pagenotfound']);

//kendo json data
Route::get('KendoTest', 'KendoController@index');
Route::get('loginLayout', function() {
    return view('authentication.login');
});

Route::get('loginReset', function() {
    return view('authentication.resetpassword');
});

Route::get('loginForget', function() {
    return view('authentication.forgetpassword');
});

//route end test
//Route Group Data
/* Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {
  Route::get('/', 'Auth\LoginController@showLoginForm');
  Route::post('login', 'Auth\LoginController@login');
  Route::post('logout', 'Auth\LoginController@logout');
  Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
  Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
  Route::post('password/reset', 'Auth\ResetPasswordController@reset');
  Route::get('register', 'Auth\RegisterController@showRegistrationForm');
  Route::post('register', 'Auth\RegisterController@register');
  Route::get('home', 'HomeController@index');
  }); */


//pdf dompdf library using composer
//url = https://github.com/dompdf/dompdf
//Route Group data end


Route::group(['middleware' => 'auth'], function () {
    //Module Raw Routes
//Dashboard
    Route::get('/Dashboard', 'DashboardController@index');



    //Attendance-PolicyInfo Module RAW Code
    Route::get('/Settings/AttendancePolicy', 'AttendancePolicyController@index');
    Route::post('/Settings/AttendancePolicy/Add', 'AttendancePolicyController@store');

    Route::get('/Settings/AttendancePolicy/List', 'AttendancePolicyController@showList');
    Route::get('/Settings/AttendancePolicy/json', 'AttendancePolicyController@show');

    Route::post('/Settings/AttendancePolicy/Delete', 'AttendancePolicyController@destroy');
    //Export All shift assign List Excel & PDF
    Route::get('/Export/Settings/AttendancePolicy/List/Excel', 'AttendancePolicyController@exportExcel');
    Route::get('/Export/Settings/AttendancePolicy/List/Pdf', 'AttendancePolicyController@exportPdf');
    Route::get('/Settings/AttendancePolicy/Edit/{id}', 'AttendancePolicyController@edit');
    Route::post('/Settings/AttendancePolicy/Update/{id}', 'AttendancePolicyController@update');

    //shift swap Module RAW Code
//    Route::get('Settings/ShiftSwap', function() {
//        return view('module.settings.shiftswap');
//    });
    Route::get('/Settings/ShiftSwap', 'ShiftEmployeeSwapController@index');
    Route::get('/Settings/ShiftSwap/List', 'ShiftEmployeeSwapController@showList');
    Route::get('/Settings/ShiftSwap/json', 'ShiftEmployeeSwapController@show');
    Route::post('/Settings/ShiftSwap/Add', 'ShiftEmployeeSwapController@store');
    Route::post('/Settings/ShiftSwap/FilterShiftWiseEmployee', 'ShiftEmployeeSwapController@filterEmployee');
//    Route::get('/Settings/ShiftAssign/Edit/{id}','AttendancePolicyController@edit');
    //   Route::post('/Settings/ShiftAssign/Update/{id}','AttendancePolicyController@update');
//Export All shift assign List Excel & PDF
    Route::get('/Export/Settings/ShiftSwap/List/Excel', 'ShiftEmployeeSwapController@exportExcel');
    Route::get('/Export/Settings/ShiftSwap/List/Pdf', 'ShiftEmployeeSwapController@exportPdf');

	//Shift Swap Loop
	Route::get('/Settings/Swap/Loop', 'ShiftEmployeeSwapController@ShiftSwapLoopIndex');
	Route::post('/Settings/Swap/Loop/Add', 'ShiftEmployeeSwapController@ShiftSwapLoopAdd');
	Route::get('/Settings/Swap/Loop/Edit/{id}', 'ShiftEmployeeSwapController@ShiftSwapLoopEdit');
	Route::post('/Settings/Swap/Loop/Update/{id}', 'ShiftEmployeeSwapController@ShiftSwapLoopUpdate');
	Route::post('/Settings/Swap/Loop/Delete', 'ShiftEmployeeSwapController@ShiftSwapLoopDelete');
	Route::get('/Settings/Swap/Loop/Json', 'ShiftEmployeeSwapController@ShiftSwapLoopJson');
	
	

//shift assign Module RAW Code

    Route::get('/Settings/ShiftAssign', 'AssignEmployeeToShiftController@index');
    Route::post('/Settings/ShiftAssign/FilterDataList', 'AssignEmployeeToShiftController@filterEmployee');
    Route::post('/Settings/ShiftAssign/FilterShiftWiseEmployee', 'AssignEmployeeToShiftController@FilterDataListForExsis');

Route::get('/Settings/ShiftAssign/FilterShiftWiseEmployee/Excel/{company_id}/{start_date}/{end_date}/{shift_id}/{emp_code}', 'AssignEmployeeToShiftController@ExcelFilterDataListForExsis');

    Route::post('/Settings/ShiftAssign/Add', 'AssignEmployeeToShiftController@store');
    Route::get('/Settings/ShiftAssign/List', 'AssignEmployeeToShiftController@showList');
    Route::get('/Settings/ShiftAssign/json', 'AssignEmployeeToShiftController@show');

    Route::post('/Settings/ShiftAssign/Delete', 'AssignEmployeeToShiftController@destroy');
    //Export All shift assign List Excel & PDF
    Route::get('/Export/Settings/ShiftAssign/List/Excel', 'AssignEmployeeToShiftController@exportExcel');
    Route::get('/Export/Settings/ShiftAssign/List/Pdf', 'AssignEmployeeToShiftController@exportPdf');



//Starts Test controller routes
    Route::get('/Settings/Jobcard', 'testController@index');
    Route::get('/Settings/Test', 'testController@index2');
    Route::get('/Settings/ChangePassword', 'testController@changePass');
    Route::get('/Settings/ForgotPassword', 'testController@forgotPass');
    Route::get('/Settings/EmployeeSendMessage', 'testController@employeesendmessage');
//End Test controller routes
    Route::get('/Settings/User/ChangePassword', 'UserPasswordController@changePass');
    Route::post('/Settings/User/ChangePassword/Save', 'UserPasswordController@update');
//User Password Controller
//Employee Info Module RAW Code
    Route::get('/Employee/Employeeinfo', 'EmployeeInfoController@index');
    Route::get('/Employee/Employeeinfo/ProfileDetail/{emp_code}', 'EmployeeInfoController@showDetail');
    Route::get('/Employee/Employeeinfo/ProfileDetail/ExportPDF/{emp_code}', 'EmployeeInfoController@exportProfilePdf');
    Route::get('/Employee/list', 'EmployeeDataController@listShow');
    Route::get('/Employee/report', 'EmployeeDataController@reportShow');
    Route::post('/Employee/Employeeinfo/Add', 'EmployeeInfoController@store');
    Route::get('/Employee/Employeeinfo/Edit/{id}', 'EmployeeInfoController@edit');
    Route::post('/Employee/Employeeinfo/Update/{id}', 'EmployeeInfoController@update');

    //Route::get('/Employee/list/Export/Excel',['as' => 'employee.export.excel','uses' => 'EmployeeDataController@exportExcel']);
    //Route::get('/Employee/list/Export/Pdf',['as' => 'employee.export.pdf','uses' => 'EmployeeDataController@exportPdf']);
    //Access Role Info Module RAW Code
    Route::get('/Settings/Role', 'SystemAccessRoleController@index');
    Route::post('/Settings/Role/Add', 'SystemAccessRoleController@store');
    Route::get('/Settings/Role/Edit/{id}', 'SystemAccessRoleController@edit');
    Route::post('/Settings/Role/Update/{id}', 'SystemAccessRoleController@update');

//Company Module RAW Code
    Route::get('/Settings/Company', 'CompanyController@index');
    Route::post('/Settings/Company/Add', 'CompanyController@store');
    Route::get('/Settings/Company/Edit/{id}', 'CompanyController@edit');
    Route::post('/Settings/Company/Update/{id}', 'CompanyController@update');

//Department Module RAW Code
    Route::get('/Settings/Department', 'DepartmentController@index');
    Route::post('/Settings/Department/Add', 'DepartmentController@store');
    Route::get('/Settings/Department/Edit/{id}', 'DepartmentController@edit');
    Route::post('/Settings/Department/Update/{id}', 'DepartmentController@update');

//Section Module RAW Code
    Route::get('/Settings/Section', 'SectionController@index');
    Route::post('/Settings/Section/Add', 'SectionController@store');
    Route::get('/Settings/Section/Edit/{id}', 'SectionController@edit');
    Route::post('/Settings/Section/Update/{id}', 'SectionController@update');


//Designation Module RAW Code
    Route::get('/Settings/Designation', 'DesignationController@index');
    Route::post('/Settings/Designation/Add', 'DesignationController@store');
    Route::get('/Settings/Designation/Edit/{id}', 'DesignationController@edit');
    Route::post('/Settings/Designation/Update/{id}', 'DesignationController@update');

    //StaffGrade Module RAW Code
    Route::get('/Settings/StaffGrade', 'StaffGradeController@index');
    Route::post('/Settings/StaffGrade/Add', 'StaffGradeController@store');
    Route::get('/Settings/StaffGrade/Edit/{id}', 'StaffGradeController@edit');
    Route::post('/Settings/StaffGrade/Update/{id}', 'StaffGradeController@update');

    //Branch Module RAW Code
    Route::get('/Settings/Branch', 'CompanyBranchController@index');
    Route::post('/Settings/Branch/Add', 'CompanyBranchController@store');
    Route::get('/Settings/Branch/Edit/{id}', 'CompanyBranchController@edit');
    Route::post('/Settings/Branch/Update/{id}', 'CompanyBranchController@update');

    //Calendar Module RAW Code
    Route::get('/Settings/Daytype', 'DayTypeController@index');
    Route::post('/Settings/Daytype/Add', 'DayTypeController@store');
    Route::get('/Settings/Daytype/Edit/{id}', 'DayTypeController@edit');
    Route::post('/Settings/Daytype/Update/{id}', 'DayTypeController@update');

//Country Module RAW Code
    Route::get('/Settings/Country', 'CountryController@index');
    Route::post('/Settings/Country/Add', 'CountryController@store');
    Route::get('/Settings/Country/Edit/{id}', 'CountryController@edit');
    Route::post('/Settings/Country/Update/{id}', 'CountryController@update');


//City Module RAW Code
    Route::get('/Settings/City', 'CityController@index');
    Route::post('/Settings/City/Add', 'CityController@store');
    Route::get('/Settings/City/Edit/{id}', 'CityController@edit');
    Route::post('/Settings/City/Update/{id}', 'CityController@update');
    //});
//Gender Module RAW Code
    Route::get('/Settings/Gender', 'GenderController@index');
    Route::post('/Settings/Gender/Add', 'GenderController@store');
    Route::get('/Settings/Gender/Edit/{id}', 'GenderController@edit');
    Route::post('/Settings/Gender/Update/{id}', 'GenderController@update');

//Gender Module RAW Code
    Route::get('/Settings/LeavePolicy', 'LeavePolicyController@index');
    Route::post('/Settings/LeavePolicy/Add', 'LeavePolicyController@store');
    Route::get('/Settings/LeavePolicy/Edit/{id}', 'LeavePolicyController@edit');
    Route::post('/Settings/LeavePolicy/Update/{id}', 'LeavePolicyController@update');

//Marital Status Module RAW Code
    Route::get('/Settings/MaritalStatus', 'MaritalStatusController@index');
    Route::post('/Settings/MaritalStatus/Add', 'MaritalStatusController@store');
    Route::get('/Settings/MaritalStatus/Edit/{id}', 'MaritalStatusController@edit');
    Route::post('/Settings/MaritalStatus/Update/{id}', 'MaritalStatusController@update');

//Blood Group Module RAW Code
    Route::get('/Settings/BloodGroup', 'BloodGroupController@index');
    Route::post('/Settings/BloodGroup/Add', 'BloodGroupController@store');
    Route::get('/Settings/BloodGroup/Edit/{id}', 'BloodGroupController@edit');
    Route::post('/Settings/BloodGroup/Update/{id}', 'BloodGroupController@update');

    //Supervisor Module RAW Code
    Route::get('/Settings/Supervisor', 'EmployeeSupervisorController@index');
    Route::post('/Settings/Supervisor/Add', 'EmployeeSupervisorController@store');
    Route::get('/Settings/Supervisor/Edit/{id}', 'EmployeeSupervisorController@edit');
    Route::post('/Settings/Supervisor/Update/{id}', 'EmployeeSupervisorController@update');

    //Calendar Module RAW Code
    Route::get('/Settings/Calendar', 'CalendarController@index');
    Route::post('/Settings/Calendar/Add', 'CalendarController@store');
    Route::post('/Settings/calender/show', 'CalendarController@calendarAlocate');
    Route::get('/Settings/Calendar/Edit/{id}', 'CalendarController@edit');
    Route::post('/Settings/Calendar/Update/{id}', 'CalendarController@update');

    //Calendar Check Module RAW Code
    Route::get('/Settings/Check/Calendar', 'CalendarController@checkIndex');

    //Leave User Data Module RAW Code
    Route::get('/Settings/LeaveUserData', 'LeaveAssignedYearlyDataController@index');
    Route::post('/Settings/LeaveUserData/Add', 'LeaveAssignedYearlyDataController@store');
    Route::get('/Settings/LeaveUserData/Edit/{id}', 'LeaveAssignedYearlyDataController@edit');
    Route::post('/Settings/LeaveUserData/Update/{id}', 'LeaveAssignedYearlyDataController@update');

	//Company Policy
    Route::get('/Settings/Company-Policy', 'CompanyPolicyController@index');
	Route::get('/Company-Policy', 'CompanyPolicyController@showDetail');
    Route::post('/Settings/Company-Policy/Add', 'CompanyPolicyController@store');
    Route::get('/Settings/Company-Policy/Edit/{id}', 'CompanyPolicyController@edit');
    Route::post('/Settings/Company-Policy/Update/{id}', 'CompanyPolicyController@update');


    //Manual workflow setting Module RAW Code
    Route::get('/Settings/LeaveWorkflowSetting', 'LeaveWorkFlowSettingController@index');
    Route::post('/Settings/LeaveWorkflowSetting/Add', 'LeaveWorkFlowSettingController@store');
    Route::get('/Settings/LeaveWorkflowSetting/Edit/{id}', 'LeaveWorkFlowSettingController@edit');
    Route::post('/Settings/LeaveWorkflowSetting/Update/{id}', 'LeaveWorkFlowSettingController@update');

    //Leave Application Module RAW Code
    //for getting leave balance
    Route::post('/Leave/LeaveApplication/Get/LeaveBalance', 'LeaveUserDataController@getLeaveBalance');
    //end
    //for getting leave user data
    Route::post('/Leave/LeaveApplication/Get/LeaveUserData', 'LeaveUserDataController@getLeaveUserData');
    //end
    //for automatically check and leave balance for new employees
    Route::get('/Leave/AutoAddLeaveBalance', 'AutoAddLeaveBalanceNewEmployeeController@AutoAddNewLeaveBalance');
    Route::get('/Leave/AutoAddAnnualLeaveBalance', 'AutoAddLeaveBalanceNewEmployeeController@AutoAddAnnualLeaveBalance');
    Route::get('/Leave/AutoAddLWPLeaveBalance', 'AutoAddLeaveBalanceNewEmployeeController@AutoAddLWPLeaveBalance');
    //end
    //for automatically check and calculate leave balance for existing all employees
    Route::get('/Leave/CalculateLeaveBalanceExisting', 'NewCalculationLeaveBalanceEmployeeController@CalculateLeaveBalanceExisting');
    //for automatically check and calculate leave balance for existing single employee
    Route::post('/Leave/CalculateLeaveBalanceExistingSingleEmployee', 'NewCalculationLeaveBalanceEmployeeController@CalculateLeaveBalanceExistingSingleEmployee');
    //end

    Route::get('/Leave/LeaveApplication/ApplyForLeave', 'LeaveApplicationMasterController@index');
    Route::get('/Leave/LeaveApplication/ApplyForLeave/HR', 'LeaveApplicationMasterHRController@index');
    Route::post('/Leave/LeaveApplication/Add', 'LeaveApplicationMasterController@store');
    Route::post('/Leave/LeaveApplication/HR/Add', 'LeaveApplicationMasterHRController@store');
    Route::get('/Leave/LeaveApplication/Edit/{id}', 'LeaveApplicationMasterController@edit');
    Route::post('/Leave/LeaveApplication/Update/{id}', 'LeaveApplicationMasterController@update');
    Route::get('/Leave/LeaveApplication/LeaveApplicationList', 'LeaveApplicationMasterController@show');
    Route::get('/Leave/LeaveApplication/LeaveApplicationList/Pending', 'LeaveApplicationMasterController@showPending');
    Route::get('/Leave/LeaveApplication/Detail/{id}', 'LeaveApplicationMasterController@showDetail');

    //Team Leader
    Route::get('/Leave/LeaveApplication/ApplyForLeave/SectionHead', 'LeaveApplicationMasterSectionHeadController@index');
    Route::post('/Leave/LeaveApplication/SectionHead/Add', 'LeaveApplicationMasterSectionHeadController@store');



    //Leave Application Comment Post
    Route::post('/Leave/LeaveApplication/Comment', 'LeaveCommentController@postComment');

    //Leave Email Template
    Route::get('/Leave/EmailTemplate/Settings', 'LeaveEmailMsgTemplateSettingsController@index');
    Route::post('/Leave/EmailTemplate/Settings/Add', 'LeaveEmailMsgTemplateSettingsController@store');
    Route::get('/Leave/EmailTemplate/Settings/Edit/{id}', 'LeaveEmailMsgTemplateSettingsController@edit');
    Route::post('/Leave/EmailTemplate/Settings/Update/{id}', 'LeaveEmailMsgTemplateSettingsController@update');
    Route::post('/Leave/EmailTemplate/Settings/Delete', 'LeaveEmailMsgTemplateSettingsController@destroy');
    //Leave Email Template JSON
    Route::get('/Leave/EmailTemplate/Settings/TemplateList/Json', 'LeaveEmailMsgTemplateSettingsController@show');
    Route::post('/Leave/EmailTemplate/Settings/Add/load', 'LeaveEmailMsgTemplateSettingsController@loadMsgTemplate');

    //Leave Application Approve/Reject
    Route::post('/Leave/LeaveApplication/Approve', 'LeaveApplicationApprovalController@approveLeave');
    Route::post('/Leave/LeaveApplication/Reject', 'LeaveApplicationApprovalController@rejectLeave');

    //Export All Leave Application List Excel & PDF
    Route::get('/Export/Leave/LeaveApplication/LeaveApplicationList/Excel', ['as' => 'leave.leaveApplicationList.export.excel', 'uses' => 'LeaveApplicationMasterController@exportAllExcel']);
    Route::get('/Export/Leave/LeaveApplication/LeaveApplicationList/Pdf', ['as' => 'leave.leaveApplicationList.export.pdf', 'uses' => 'LeaveApplicationMasterController@exportAllPdf']);

    //Export Pending Leave Application List Excel & PDF
    Route::get('/Export/Leave/LeaveApplication/LeaveApplicationPendingList/Excel', ['as' => 'leave.leaveApplicationPendingList.export.excel', 'uses' => 'LeaveApplicationMasterController@exportPendingExcel']);
    Route::get('/Export/Leave/LeaveApplication/LeaveApplicationPendingList/Pdf', ['as' => 'leave.leaveApplicationPendingList.export.pdf', 'uses' => 'LeaveApplicationMasterController@exportPendingPdf']);

    //Leave Application Module JSON
    Route::get('/Leave/LeaveApplication/LeaveApplicationList/Json', 'LeaveApplicationMasterController@listShow');
    Route::get('/Leave/LeaveApplication/LeaveApplicationList/Pending/Json', 'LeaveApplicationMasterController@listShowPending');
    Route::get('/Leave/LeaveApplication/LeaveApplicationSummary/Json/{emp_code}', 'LeaveApplicationMasterController@getSummary');
    Route::get('/Leave/LeaveApplication/LeaveApplicationHistory/Json/{emp_code}', 'LeaveApplicationMasterController@getHistory');



//Module Json Routes
    //Employee Module JSON
    Route::get('/Employee/Employeeinfo/Json', 'EmployeeDataController@show');
    Route::post('/Employee/Employeeinfo/Delete', 'EmployeeDataController@destroy');
    Route::post('/Filter/Employee/List', ['uses' => 'EmployeeDataController@filterEmployeeList', 'as' => 'Employee.Filter']);
    Route::post('/Filter/Datewise/Employee/Report', ['uses' => 'EmployeeDataController@filterEmployeeReport', 'as' => 'Employee.Report']);
    Route::get('/Export/Employee/Excel/{company_id}/{department_id}/{section_id}/{designation_id}', 'EmployeeDataController@exportFilterExcel');
    Route::get('/Export/Employee/Pdf/{company_id}/{department_id}/{section_id}/{designation_id}', 'EmployeeDataController@exportFilterPdf');

    Route::get('/Export/Datewise/Employee/Excel/{company_id}/{department_id}/{section_id}/{designation_id}/{start_date}/{end_date}', 'EmployeeDataController@exportDatewiseFilterExcel');
    Route::get('/Export/Datewise/Employee/Pdf/{company_id}/{department_id}/{section_id}/{designation_id}/{start_date}/{end_date}', 'EmployeeDataController@exportDatewiseFilterPdf');

    //SystemAccessRole Module JSON
    Route::get('/Settings/Role/Json', 'SystemAccessRoleController@show');
    Route::post('/Settings/Role/Delete', 'SystemAccessRoleController@destroy');


    //Calendar Module JSON
    Route::get('/Settings/Calendar/Json', 'CalendarController@show');
    Route::post('/Settings/Calendar/Get/Year/Json', 'CalendarController@getYear');
    Route::post('/Settings/Calendar/Get/Month/Json', 'CalendarController@getMonth');
    Route::post('/Settings/Calendar/Delete', 'CalendarController@destroy');

    //Company Module JSON
    Route::get('/Settings/Company/Json', 'CompanyController@show');
    Route::post('/Settings/Company/Delete', 'CompanyController@destroy');
	
	//Company Module JSON
    Route::get('/Settings/Company-Policy/Json', 'CompanyPolicyController@show');
    Route::post('/Settings/Company-Policy/Delete', 'CompanyPolicyController@destroy');

    //Department Module JSON
    Route::get('/Settings/Department/Json', 'DepartmentController@show');
    Route::post('/Settings/Department/Delete', 'DepartmentController@destroy');

    //Section Module JSON
    Route::get('/Settings/Section/Json', 'SectionController@show');
    Route::post('/Settings/Section/Delete', 'SectionController@destroy');

    //Staffgrade Module JSON
    Route::get('/Settings/StaffGrade/Json', 'StaffGradeController@show');
    Route::post('/Settings/StaffGrade/Delete', 'StaffGradeController@destroy');

    //Designation Module JSON
    Route::get('/Settings/Designation/Json', 'DesignationController@show');
    Route::post('/Settings/Designation/Delete', 'DesignationController@destroy');

    //Branch Module JSON
    Route::get('/Settings/Branch/Json', 'CompanyBranchController@show');
    Route::post('/Settings/Branch/Delete', 'CompanyBranchController@destroy');

    //Daytype Module JSON
    Route::get('/Settings/Daytype/Json', 'DayTypeController@show');
    Route::post('/Settings/Daytype/Delete', 'DayTypeController@destroy');

//Country Module JSON
    Route::get('/Settings/Country/Json', 'CountryController@show');
    Route::post('/Settings/Country/Delete', 'CountryController@destroy');
    Route::get('/Settings/Country/Export/Excel', ['as' => 'settings.country.export.excel', 'uses' => 'CountryController@exportExcel']);
    Route::get('/Settings/Country/Export/Pdf', ['as' => 'settings.country.export.pdf', 'uses' => 'CountryController@exportPdf']);

//City Module JSON
    Route::get('/Settings/City/Json', 'CityController@show');
    Route::post('/Settings/City/Delete', 'CityController@destroy');

//Gender Module JSON
    Route::get('/Settings/Gender/Json', 'GenderController@show');
    Route::post('/Settings/Gender/Delete', 'GenderController@destroy');

//Leave Policy Module JSON
    Route::get('/Settings/LeavePolicy/Json', 'LeavePolicyController@show');
    Route::post('/Settings/LeavePolicy/Delete', 'LeavePolicyController@destroy');
    Route::get('/Settings/LeavePolicy/Export/Excel', ['as' => 'settings.leavePolicy.export.excel', 'uses' => 'LeavePolicyController@exportExcel']);
    Route::get('/Settings/LeavePolicy/Export/Pdf', ['as' => 'settings.leavePolicy.export.pdf', 'uses' => 'LeavePolicyController@exportPdf']);


    //Leave User Data Module JSON
    Route::get('/Settings/LeaveUserData/Json', 'LeaveAssignedYearlyDataController@show');
    Route::post('/Settings/LeaveUserData/Get/Employees/Json', 'LeaveAssignedYearlyDataController@getEmployees');
	Route::post('/Settings/LeaveUserData/Get/Employees/Section/Json', 'LeaveAssignedYearlyDataController@getEmployeesSection');
    Route::post('/Settings/LeaveUserData/Delete', 'LeaveAssignedYearlyDataController@destroy');
    Route::get('/Settings/LeaveUserData/Export/Excel', ['as' => 'settings.leaveUserData.export.excel', 'uses' => 'LeaveAssignedYearlyDataController@exportExcel']);
    Route::get('/Settings/LeaveUserData/Export/Pdf', ['as' => 'settings.leaveUserData.export.pdf', 'uses' => 'LeaveAssignedYearlyDataController@exportPdf']);


    //Leave User Data Filter JSON
    Route::get('/Filter/LeaveUserData/Json', 'LeaveUserDataController@show');
    Route::get('/Filter/LeaveUserData/List', 'LeaveUserDataController@index');
    Route::post('/Filter/LeaveUserData/List', ['uses' => 'LeaveUserDataController@filterLeaveUserDataList', 'as' => 'LeaveUserData.Filter']);
    Route::get('/Export/LeaveUserData/Excel/{company_id}/{department_id}/{section_id}/{designation_id}/{employee_code}/{leave_policy_id}/{year}', 'LeaveUserDataController@exportFilterExcel');
    Route::get('/Export/LeaveUserData/Pdf/{company_id}/{department_id}/{section_id}/{designation_id}/{employee_code}/{leave_policy_id}/{year}', 'LeaveUserDataController@exportFilterPdf');


    // Leave Register Report
    Route::get('/LeaveRegister', 'Report\LeaveRegisterController@indexRegister');
    Route::post('/LeaveRegister/Report', 'Report\LeaveRegisterController@showRegister');
    Route::get('/LeaveRegister/Export/Excel/{company}/{year}', 'Report\LeaveRegisterController@exportExcelRegister');
    Route::get('/LeaveRegister/Export/Pdf/{company}/{year}', 'Report\LeaveRegisterController@exportPdfRegister');


    //Leave Application Approval/Rejection Method
    Route::get('/Leave/LeaveApplication/ApprovalMethod', 'LeaveApprovalMethodController@index');
    Route::post('/Leave/LeaveApplication/ApprovalMethod/Add', 'LeaveApprovalMethodController@store');
    Route::post('Leave/LeaveApplication/ApprovalMethod/Update/{id}', 'LeaveApprovalMethodController@update');

//Marital Status Module JSON
    Route::get('/Settings/MaritalStatus/Json', 'MaritalStatusController@show');
    Route::post('/Settings/MaritalStatus/Delete', 'MaritalStatusController@destroy');

//Blood Group Module JSON
    Route::get('/Settings/BloodGroup/Json', 'BloodGroupController@show');
    Route::post('/Settings/BloodGroup/Delete', 'BloodGroupController@destroy');

    //Supervisor Module JSON
    Route::get('/Settings/Supervisor/Json', 'EmployeeSupervisorController@show');
    Route::post('/Settings/Supervisor/Delete', 'EmployeeSupervisorController@destroy');

    //Leave Work FLow Module JSON
    Route::get('/Settings/LeaveWorkflowSetting/Json', 'LeaveWorkFlowSettingController@show');
    Route::post('/Settings/LeaveWorkflowSetting/Delete', 'LeaveWorkFlowSettingController@destroy');

//basic login module
//vendor is hacked :D
    //system modules routes
    //Module Module RAW Code
    Route::get('/Settings/Module', 'SystemModuleController@index');
    Route::post('/Settings/Module/Add', 'SystemModuleController@store');
    Route::get('/Settings/Module/Edit/{id}', 'SystemModuleController@edit');
    Route::post('/Settings/Module/Update/{id}', 'SystemModuleController@update');

    //Module Module JSON
    Route::get('/Settings/Module/Json', 'SystemModuleController@show');
    Route::post('/Settings/Module/Delete', 'SystemModuleController@destroy');
    Route::get('/Settings/Module/Export/Excel', ['as' => 'settings.module.export.excel', 'uses' => 'SystemModuleController@exportExcel']);
    Route::get('/Settings/Module/Export/Pdf', ['as' => 'settings.module.export.pdf', 'uses' => 'SystemModuleController@exportPdf']);

    //SubModule Module RAW Code
    Route::get('/Settings/SubModule', 'SystemSubModuleController@index');
    Route::post('/Settings/SubModule/Add', 'SystemSubModuleController@store');
    Route::get('/Settings/SubModule/Edit/{id}', 'SystemSubModuleController@edit');
    Route::post('/Settings/SubModule/Update/{id}', 'SystemSubModuleController@update');

    //submodule Module JSON
    Route::get('/Settings/SubModule/Json', 'SystemSubModuleController@show');
    Route::post('/Settings/SubModule/Get/Module/Json', 'SystemSubModuleController@getModule');
    Route::post('/Settings/SubModule/Delete', 'SystemSubModuleController@destroy');
    Route::get('/Settings/SubModule/Export/Excel', ['as' => 'settings.submodule.export.excel', 'uses' => 'SystemSubModuleController@exportExcel']);
    Route::get('/Settings/SubModule/Export/Pdf', ['as' => 'settings.submodule.export.pdf', 'uses' => 'SystemSubModuleController@exportPdf']);


    //SubModule Module RAW Code
    Route::get('/Settings/ModulePages', 'SystemModulePageController@index');
    Route::post('/Settings/ModulePages/Add', 'SystemModulePageController@store');
    Route::get('/Settings/ModulePages/Edit/{id}', 'SystemModulePageController@edit');
    Route::post('/Settings/ModulePages/Update/{id}', 'SystemModulePageController@update');

    //submodule Module JSON
    Route::get('/Settings/ModulePages/Json', 'SystemModulePageController@show');
    Route::post('/Settings/ModulePages/Get/SubModule/Json', 'SystemModulePageController@getSubModule');
    Route::post('/Settings/ModulePages/Delete', 'SystemModulePageController@destroy');
    Route::get('/Settings/ModulePages/Export/Excel', ['as' => 'settings.modulepages.export.excel', 'uses' => 'SystemModulePageController@exportExcel']);
    Route::get('/Settings/ModulePages/Export/Pdf', ['as' => 'settings.modulepages.export.pdf', 'uses' => 'SystemModulePageController@exportPdf']);



    //system modules routes
    //Master Filter Route JSON
    Route::post('Filter/Country/Get/City/Json', 'CityController@filterCity');
    Route::get('Filter/Employee/Get/Code/Json', 'EmployeeCodeController@filterEmployeeCode');

    Route::post('Filter/Company/Get/Branch/Json', 'CompanyBranchController@filterBranch');
    Route::post('Filter/Company/Get/Department/Json', 'DepartmentController@filterDepartment');
    Route::post('Filter/Company/Get/Shift/Json', 'ShiftController@filterShift');
    Route::post('Filter/Department/Get/Section/Json', 'SectionController@filterSection');
    Route::post('Filter/Section/Get/Designation/Json', 'DesignationController@filterDesignation');

    Route::post('Filter/Designation/Get/Employee/Json', 'EmployeeFilterController@filterEmployee');
	Route::post('Filter/All/Get/Employee/Json', 'EmployeeFilterController@filterAllEmployee');

    //Only With company_id Get Employees List
    Route::get('Filter/Company/Get/Employee/Json/{company_id}', 'EmployeeFilterController@getCompanyEmployees');

    //Extra Filter Route JSON
    Route::post('Filter/Company/Get/LeavePolicies/Json', 'LeavePolicyController@filterLeavePolicy');
    Route::post('Filter/Company/Get/Year/Json', 'YearController@filterYear');












    //leave & Employee Module Ends Here



    /*     * *****************************
     *
     *  Attendance Module Start From Here
     *
     * ****************************** */



    //Staff Module RAW Code
    Route::get('/Settings/Shift', 'ShiftController@index');
    Route::get('/Settings/Shift/Add', 'ShiftController@create');
    Route::post('/Settings/Shift/Add', 'ShiftController@store');
    Route::get('/Settings/Shift/Edit/{id}', 'ShiftController@edit');
    Route::post('/Settings/Shift/Update/{id}', 'ShiftController@update');

    //Company Module JSON
    Route::get('/Settings/Shift/Json', 'ShiftController@show');
    Route::post('/Settings/Shift/Delete', 'ShiftController@destroy');

    //export
    Route::get('/Settings/Shift/Export/Excel', ['as' => 'settings.shift.export.excel', 'uses' => 'ShiftController@exportExcel']);
    Route::get('/Settings/Shift/Export/Pdf', ['as' => 'settings.shift.export.pdf', 'uses' => 'ShiftController@exportPdf']);


    //ShiftPattern Module RAW Code
    Route::get('/Settings/ShiftPattern', 'ShiftPatternController@index');
    Route::get('/Settings/ShiftPattern/Add', 'ShiftPatternController@create');
    Route::post('/Settings/ShiftPattern/Add', 'ShiftPatternController@store');
    Route::get('/Settings/ShiftPattern/Edit/{id}', 'ShiftPatternController@edit');
    Route::post('/Settings/ShiftPattern/Update/{id}', 'ShiftPatternController@update');

    //ShiftPattern Module JSON
    Route::get('/Settings/ShiftPattern/Json', 'ShiftPatternController@show');
    Route::post('/Settings/ShiftPattern/Delete', 'ShiftPatternController@destroy');

    //export
    Route::get('/Settings/ShiftPattern/Export/Excel', ['as' => 'settings.shiftpattern.export.excel', 'uses' => 'ShiftPatternController@exportExcel']);
    Route::get('/Settings/ShiftPattern/Export/Pdf', ['as' => 'settings.shiftpattern.export.pdf', 'uses' => 'ShiftPatternController@exportPdf']);

	//AttendanceFile Module RAW Code
	Route::get('/Settings/AttendanceFile', 'AttendanceFileController@index');
	Route::get('/Settings/AttendanceFile/Add', 'AttendanceFileController@create');
	Route::post('/Settings/AttendanceFile/Add', 'AttendanceFileController@store');
	Route::get('/Settings/AttendanceFile/Edit/{id}', 'AttendanceFileController@edit');
	Route::post('/Settings/AttendanceFile/Update/{id}', 'AttendanceFileController@update');
	
	//AttendanceLod Manual Entry
    Route::get('/Attendance/log-manual-entry', 'AttendanceRawDataController@index');
    Route::post('/Attendance/log-manual-entry', 'AttendanceRawDataController@store');
	Route::get('/ManualRawLog/Json','AttendanceRawDataController@show');

    //ShiftPattern Module JSON
    Route::get('/Settings/AttendanceFile/Json', 'AttendanceFileController@show');
    Route::post('/Settings/AttendanceFile/Delete', 'AttendanceFileController@destroy');

    //export
    Route::get('/Settings/AttendanceFile/Export/Excel', ['as' => 'settings.attendancefile.export.excel', 'uses' => 'AttendanceFileController@exportExcel']);
    Route::get('/Settings/AttendanceFile/Export/Pdf', ['as' => 'settings.attendancefile.export.pdf', 'uses' => 'AttendanceFileController@exportPdf']);

    //AttendanceSettings Module RAW Code
    Route::get('/Settings/AttendanceSettings', 'UploadAttendanceSettingController@index');
    Route::get('/Settings/AttendanceSettings/Add', 'UploadAttendanceSettingController@create');
    Route::post('/Settings/AttendanceSettings/Add', 'UploadAttendanceSettingController@store');
    Route::get('/Settings/AttendanceSettings/Edit/{id}', 'UploadAttendanceSettingController@edit');
    Route::post('/Settings/AttendanceSettings/Update/{id}', 'UploadAttendanceSettingController@update');

    //AttendanceSettings Module JSON
    Route::get('/Settings/AttendanceSettings/Json', 'UploadAttendanceSettingController@show');
    Route::post('/Settings/AttendanceSettings/Delete', 'UploadAttendanceSettingController@destroy');

    //AttendanceSettingsexport
    Route::get('/Settings/AttendanceSettings/Export/Excel', ['as' => 'settings.attendancesetting.export.excel', 'uses' => 'UploadAttendanceSettingController@exportExcel']);
    Route::get('/Settings/AttendanceSettings/Export/Pdf', ['as' => 'settings.attendancesetting.export.pdf', 'uses' => 'UploadAttendanceSettingController@exportPdf']);




    //AttendanceJobcardPolicy Module RAW Code
    Route::get('/Settings/AttendanceJobcardPolicy', 'AttendanceJobcardPolicyController@index');
    Route::get('/Settings/AttendanceJobcardPolicy/Add', 'AttendanceJobcardPolicyController@create');
    Route::post('/Settings/AttendanceJobcardPolicy/Add', 'AttendanceJobcardPolicyController@store');
    Route::get('/Settings/AttendanceJobcardPolicy/Edit/{id}', 'AttendanceJobcardPolicyController@edit');
    Route::post('/Settings/AttendanceJobcardPolicy/Update/{id}', 'AttendanceJobcardPolicyController@update');

    //AttendanceSettings Module JSON
    Route::get('/Settings/AttendanceJobcardPolicy/Json', 'AttendanceJobcardPolicyController@show');
    Route::post('/Settings/AttendanceJobcardPolicy/Delete', 'AttendanceJobcardPolicyController@destroy');

    //AttendanceSettingsexport
    Route::get('/Settings/AttendanceJobcardPolicy/Export/Excel', ['as' => 'settings.attendancejobcardpolicy.export.excel', 'uses' => 'AttendanceJobcardPolicyController@exportExcel']);
    Route::get('/Settings/AttendanceJobcardPolicy/Export/Pdf', ['as' => 'settings.attendancejobcardpolicy.export.pdf', 'uses' => 'AttendanceJobcardPolicyController@exportPdf']);



    //WeekendOTPolicyController Module RAW Code
    Route::get('/Settings/WeekendOTPolicy', 'WeekendOTPolicyController@index');
    // Route::get('/Settings/WeekendOTPolicy/Add', 'WeekendOTPolicyController@create');
    Route::post('/Settings/WeekendOTPolicy/Add', 'WeekendOTPolicyController@store');
    Route::get('/Settings/WeekendOTPolicy/Edit/{id}', 'WeekendOTPolicyController@edit');
    Route::post('/Settings/WeekendOTPolicy/Update/{id}', 'WeekendOTPolicyController@update');

    //AttendanceSettings Module JSON
    Route::get('/Settings/WeekendOTPolicy/Json', 'WeekendOTPolicyController@show');
    Route::post('/Settings/WeekendOTPolicy/Delete', 'WeekendOTPolicyController@destroy');

    //AttendanceSettingsexport
    Route::get('/Settings/WeekendOTPolicy/Export/Excel', 'WeekendOTPolicyController@exportExcel');
    Route::get('/Settings/WeekendOTPolicy/Export/Pdf', 'WeekendOTPolicyController@exportPdf');
	
	//EmployeeWeekendDiffrentCompanyPolicy
	Route::get('/Settings/WeekendDiffrentCompanyPolicy', 'EmployeeWeekendDifferentCompanyPolicyController@index');
    Route::post('/Settings/WeekendDiffrentCompanyPolicy/Add', 'EmployeeWeekendDifferentCompanyPolicyController@store');
    Route::get('/Settings/WeekendDiffrentCompanyPolicy/Edit/{id}', 'EmployeeWeekendDifferentCompanyPolicyController@edit');
    Route::post('/Settings/WeekendDiffrentCompanyPolicy/Update/{id}', 'EmployeeWeekendDifferentCompanyPolicyController@update');

	//EmployeeEmploymentType
	Route::get('/Settings/EmployeeEmploymentType', 'EmployeeEmploymentTypeController@index');
    Route::post('/Settings/EmployeeEmploymentType/Add', 'EmployeeEmploymentTypeController@store');
    Route::get('/Settings/EmployeeEmploymentType/Edit/{id}', 'EmployeeEmploymentTypeController@edit');
    Route::post('/Settings/EmployeeEmploymentType/Update/{id}', 'EmployeeEmploymentTypeController@update');

	//Settings/WeekendDiffrentCompanyPolicy/Json
	Route::get('/Settings/WeekendDiffrentCompanyPolicy/Json', 'EmployeeWeekendDifferentCompanyPolicyController@show');
	Route::post('/Settings/WeekendDiffrentCompanyPolicy/Delete', 'EmployeeWeekendDifferentCompanyPolicyController@destroy');
	
    //In Time Missing Punch Report
    Route::get('/Report/InTimeMissingPunch', 'Report\MissingPunchReportController@InTimeIndex');
    Route::post('/Report/InTimeMissingPunch/filter', 'Report\MissingPunchReportController@filterInTimeMissingPunch');
    Route::post('/Report/InTimeMissingPunch/Update', 'Report\MissingPunchReportController@updateInTimeMissingPunch');
    Route::get('/Report/InTimeMissingPunch/Export/Excel/{company_id}/{start_date}/{end_date}', 'Report\MissingPunchReportController@exportExcelIn');
    Route::get('/Report/InTimeMissingPunch/Export/Pdf/{company_id}/{start_date}/{end_date}', 'Report\MissingPunchReportController@exportPdfIn');
    
    //Out Time Missing Punch Report
    Route::get('/Report/OutTimeMissingPunch', 'Report\MissingPunchReportController@OutTimeIndex');
    Route::post('/Report/OutTimeMissingPunch/filter', 'Report\MissingPunchReportController@filterOutTimeMissingPunch');
    Route::post('/Report/OutTimeMissingPunch/Update', 'Report\MissingPunchReportController@updateOutTimeMissingPunch');
    Route::get('/Report/OutTimeMissingPunch/Export/Excel/{company_id}/{start_date}/{end_date}', 'Report\MissingPunchReportController@exportExcelOut');
    Route::get('/Report/OutTimeMissingPunch/Export/Pdf/{company_id}/{start_date}/{end_date}', 'Report\MissingPunchReportController@exportPdfOut');

	//Missing Punch Report
    Route::get('/Report/MissingPunch', 'Report\MissingPunchReportINOUTController@OutTimeIndex');
    Route::post('/Report/MissingPunch/filter', 'Report\MissingPunchReportINOUTController@filterMissingPunch');
    Route::post('/Report/MissingPunch/Update', 'JobcardBatchEditController@updateMissingPunch');
    Route::get('/Report/MissingPunch/Export/Excel/{company_id}/{start_date}/{end_date}', 'Report\MissingPunchReportINOUTController@exportExcelOut');
    Route::get('/Report/MissingPunch/Export/Pdf/{company_id}/{start_date}/{end_date}', 'Report\MissingPunchReportINOUTController@exportPdfOut');


    //Jobcard Module RAW Code
    Route::get('/Jobcard/Admin', 'AttendanceJobcardController@Adminindex');
    Route::get('/Jobcard/Audit', 'AttendanceJobcardController@Auditindex');
    Route::get('Jobcard/JobUser', 'AttendanceJobcardController@Userindex');
    Route::get('/Jobcard/Process', 'AttendanceJobcardController@create');
    Route::post('/Jobcard/Add', 'AttendanceJobcardController@store');
    Route::get('/Jobcard/Edit/{id}', 'AttendanceJobcardController@edit');
    Route::post('/Jobcard/AdminUpdate', 'Report\JobcardReportController@Adminupdate');
    Route::post('/Jobcard/UserUpdate', 'Report\JobcardReportController@Userupdate');
    Route::post('/Jobcard/AuditUpdate', 'Report\JobcardReportController@Auditupdate');
	Route::get('/Jobcard/LateIN', 'Report\JobcardReportController@LateINindex');

    Route::get('/Jobcard/Company', 'Report\JobcardReportController@CompanyAttendanceindex');
    Route::post('/Jobcard/CompanyJson', 'Report\JobcardReportController@CompanyAttendanceLogshow');
    Route::get('/Jobcard/Company/Export/{company_id}/{date}', 'Report\JobcardReportController@CompanyWiseexportExcel');

    //AttendanceSettings Module JSON													 													
    Route::post('Jobcard/GetEpmloyeedetails/', 'DashboardController@GetEmployeeDetail');
											
    Route::post('/Jobcard/AdminJson', 'Report\JobcardReportController@Adminshow');
	Route::post('/Jobcard/LateINJson', 'Report\JobcardReportController@LateINShow');
    Route::post('/Jobcard/AuditJson', 'Report\JobcardReportController@Auditshow');
    Route::post('/Jobcard/UserJson', 'Report\JobcardReportController@Usershow');
    //Route::post('/Jobcard/Delete', 'AttendanceJobcardController@destroy');
    //AttendanceSettingsexport
    Route::get('/Jobcard/Export/AdminExcel/{emp_code}/{start_date}/{end_date}', 'Report\JobcardReportController@AdminexportExcel');
    Route::get('/Jobcard/Export/AdminPdf/{emp_code}/{start_date}/{end_date}', 'Report\JobcardReportController@AdminexportPdf');
    Route::get('/Jobcard/Export/LateINPdf/{company_id}/{start_date}/{end_date}', 'Report\JobcardReportController@LateINexportPdf');
    Route::get('/Jobcard/Export/LateINExcel/{company_id}/{start_date}/{end_date}', 'Report\JobcardReportController@LateINexportExcel');

    Route::get('/Jobcard/Export/AuditExcel/{emp_code}/{start_date}/{end_date}', 'Report\JobcardReportController@AuditexportExcel');
    Route::get('/Jobcard/Export/AuditPdf/{emp_code}/{start_date}/{end_date}', 'Report\JobcardReportController@AuditexportPdf');

    Route::get('/Jobcard/Export/UserExcel/{emp_code}/{start_date}/{end_date}', 'Report\JobcardReportController@UserexportExcel');
    Route::get('/Jobcard/Export/UserPdf/{emp_code}/{start_date}/{end_date}', 'Report\JobcardReportController@UserexportPdf');

    // Edit Job card
    Route::get('/Jobcard/day_status', 'Report\JobcardReportController@dayStatus');
    Route::get('/Jobcard/User', 'Report\JobcardReportController@ReportdayStatus');


    //Manula Jobcard Module RAW Code
    Route::get('/ManualJobcard', 'ManualJobCardEntryController@index');
    Route::post('/ManualJobcard/Add', 'ManualJobCardEntryController@store');
    Route::get('/ManualJobcard/Json', 'ManualJobCardEntryController@show');
    Route::get('/ManualJobcard/Edit/{id}', 'ManualJobCardEntryController@edit');
    Route::post('/ManualJobcard/Update/{id}', 'ManualJobCardEntryController@update');
    Route::post('/ManualJobcard/Delete', 'ManualJobCardEntryController@destroy');
    Route::get('/Export/ManualJobcard/List/Pdf', 'ManualJobCardEntryController@exportPdf');
    Route::get('/Export/ManualJobcard/List/Excel', 'ManualJobCardEntryController@exportExcel');

    Route::get('/Filter/ManualJobcard/List', 'ManualJobCardDataController@reportShow');
    Route::get('/Filter/ManualJobcard/Json', 'ManualJobCardDataController@show');

    Route::post('/Filter/Datewise/ManualJobcard/Report', ['uses' => 'ManualJobCardDataController@filterReport', 'as' => 'ManualJobcard.Report']);
    Route::get('/Export/Datewise/ManualJobcard/Excel/{company_id}/{start_date}/{end_date}', 'ManualJobCardDataController@exportDatewiseFilterExcel');
    Route::get('/Export/Datewise/ManualJobcard/Pdf/{company_id}/{start_date}/{end_date}', 'ManualJobCardDataController@exportDatewiseFilterPdf');

    // Attendance Summary & Report
    Route::get('AttendanceSummary', 'Report\AttendanceReportController@indexSummary');
    Route::post('AttendanceSummary/Report', 'Report\AttendanceReportController@showSummary');
    Route::get('AttendanceSummary/Export/Excel/{company_id}/{department}/{start_date}/{end_date}','Report\AttendanceReportController@exportExcelSummary');
    Route::get('AttendanceSummary/Export/Pdf/{company}/{department}/{start_date}/{end_date}', 'Report\AttendanceReportController@exportPdfSummary');

    Route::get('/AttendanceReport', 'Report\AttendanceReportController@indexReport');
    Route::post('/AttendanceReport/Report', 'Report\AttendanceReportController@showReport');
    Route::get('/AttendanceReport/Export/Excel/{emp_code}/{start_date}/{end_date}', 'Report\AttendanceReportController@exportExcelReport');
    Route::get('/AttendanceReport/Export/Pdf/{emp_code}/{start_date}/{end_date}', 'Report\AttendanceReportController@exportPdfReport');


    //Standard OT Summary
    Route::get('/StandardOTSummary', 'Report\StandardOTReportController@indexSummary');
    Route::post('/StandardOTSummary/Report', 'Report\StandardOTReportController@showSummary');
    Route::get('/StandardOTSummary/Export/Excel/{company}/{department}/{start_date}/{end_date}', 'Report\StandardOTReportController@exportExcelSummary');
    Route::get('/StandardOTSummary/Export/Pdf/{company}/{department}/{start_date}/{end_date}', 'Report\StandardOTReportController@exportPdfSummary');

    // OT Summary & Report
    Route::get('/OTSummary', 'Report\OTReportController@indexSummary');
    Route::post('/OTSummary/Report', 'Report\OTReportController@showSummary');
    Route::get('/OTSummary/Export/Excel/{company}/{department}/{start_date}/{end_date}', 'Report\OTReportController@exportExcelSummary');
    Route::get('/OTSummary/Export/Pdf/{company}/{department}/{start_date}/{end_date}', 'Report\OTReportController@exportPdfSummary');


    Route::get('/OTReport', 'Report\OTReportController@indexReport');
    Route::post('/OTReport/Report', 'Report\OTReportController@showReport');
    Route::get('/OTReport/Export/Excel/{emp_code}/{start_date}/{end_date}', 'Report\OTReportController@exportExcelReport');
    Route::get('/OTReport/Export/Pdf/{emp_code}/{start_date}/{end_date}', 'Report\OTReportController@exportPdfReport');

    //Additional  OT Summary & Report
    Route::get('/AdditionalOTSummary', 'Report\AdditionalOTReportController@indexSummary');
    Route::post('/AdditionalOTSummary/Report', 'Report\AdditionalOTReportController@showSummary');
    Route::get('/AdditionalOTSummary/Export/Excel/{company}/{department}/{start_date}/{end_date}', 'Report\AdditionalOTReportController@exportExcelSummary');
    Route::get('/AdditionalOTSummary/Export/Pdf/{company}/{department}/{start_date}/{end_date}', 'Report\AdditionalOTReportController@exportPdfSummary');

    Route::get('/AdditionalOTReport', 'Report\AdditionalOTReportController@indexReport');
    Route::post('/AdditionalOTReport/Report', 'Report\AdditionalOTReportController@showReport');
    Route::get('/AdditionalOTReport/Export/Excel/{emp_code}/{start_date}/{end_date}', 'Report\AdditionalOTReportController@exportExcelReport');
    Route::get('/AdditionalOTReport/Export/Pdf/{emp_code}/{start_date}/{end_date}', 'Report\AdditionalOTReportController@exportPdfReport');


    //AttendanceJobcardPolicy Module RAW Code
    Route::get('/Settings/UserRoleMap', 'UserMapRoleController@index');
    Route::get('/Settings/UserRoleMap/Add', 'UserMapRoleController@create');
    Route::post('/Settings/UserRoleMap/Add', 'UserMapRoleController@store');
    Route::get('/Settings/UserRoleMap/Edit/{id}', 'UserMapRoleController@edit');
    Route::post('/Settings/UserRoleMap/Update/{id}', 'UserMapRoleController@update');

    //AttendanceSettings Module JSON
    Route::get('/Settings/UserRoleMap/Json', 'UserMapRoleController@show');
    Route::post('/Settings/UserRoleMap/Delete', 'UserMapRoleController@destroy');


    //Import Empolyee File Module RAW Code
    Route::get('/Settings/ImportEmpoleeInfo', 'ImportEmployeeController@index');
//    Route::get('/Settings/ImportEmpoleeInfo/Add', 'ImportEmployeeController@create');
    Route::post('/Settings/ImportEmpoleeInfo/Add', 'ImportEmployeeController@store');

    //Import leave Balance User Balance File Module RAW Code
    Route::get('/Settings/UserLeaveBalance', 'ImportLeaveUserBalanceController@index');
    Route::post('/Settings/UserLeaveBalance/Add', 'ImportLeaveUserBalanceController@store');
	
	Route::get('/Settings/UserLeaveCustomBalance', 'ImportLeaveUserCustomBalanceController@index');
    Route::post('/Settings/UserLeaveCustomBalance/Add', 'ImportLeaveUserCustomBalanceController@store');

    //Import Shift File Module RAW Code
    Route::get('/Settings/InputShift', 'ImportShiftController@index');
    Route::post('/Settings/InputShift/Add', 'ImportShiftController@store');

    //Import Assign Employee to Shift File Module RAW Code
    Route::get('/Settings/InputShiftAssign', 'ImportAssignEmployeeToShiftController@index');
    Route::post('/Settings/InputShiftAssign/Add', 'ImportAssignEmployeeToShiftController@store');

    Route::post('/Settings/AttendanceJobcardPolicy/Json', 'ManualJobCardEntryController@ReportdayStatus');

    // Indivisul Employee Process 
    Route::get('/Settings/IndivisulemployeeProcess', 'IndivisulEmployeeProcessController@index');
    Route::post('/Settings/IndivisulemployeeProcess/Add', 'IndivisulEmployeeProcessController@store');
	
	
	//Payroll Salary View Permission Settings
    Route::get('/Payroll/SalaryViewPermissionSettings', 'PayrollSalaryViewPermissionController@index');
    Route::post('/Payroll/SalaryViewPermissionSettings/Json', 'PayrollSalaryViewPermissionController@show');
    Route::post('/Payroll/SalaryViewPermissionSettings/Add', 'PayrollSalaryViewPermissionController@store');
    Route::get('/Payroll/SalaryViewPermissionSettings/Edit/{emp_code}', 'PayrollSalaryViewPermissionController@edit');
    Route::post('/Payroll/SalaryViewPermissionSettings/Update/{emp_code}', 'PayrollSalaryViewPermissionController@update');Route::post('/Payroll/SalaryViewPermissionSettings/Delete', 'PayrollSalaryViewPermissionController@destroy');

    //Payroll Salary Component Settings
    Route::get('/Payroll/SalaryComponent', 'PayrollSalaryComponentController@index');
    Route::get('/Payroll/SalaryComponent/Json', 'PayrollSalaryComponentController@show');
    Route::post('/Payroll/SalaryComponent/Add', 'PayrollSalaryComponentController@store');
    Route::post('/Payroll/SalaryComponent/Delete', 'PayrollSalaryComponentController@destroy');
    Route::get('/Payroll/SalaryComponent/Edit/{emp_code}', 'PayrollSalaryComponentController@edit');
    Route::post('/Payroll/SalaryComponent/Update/{emp_code}', 'PayrollSalaryComponentController@update');
	
	//Employee Role Assign for Company
    Route::get('/RoleAndPermission/AssignedToCompany', 'EmployeeRoleAssignToCompanyController@index');
    Route::get('/RoleAndPermission/AssignedToCompany/Json', 'EmployeeRoleAssignToCompanyController@show');
    Route::post('/RoleAndPermission/AssignedToCompany/Add', 'EmployeeRoleAssignToCompanyController@store');
    Route::post('RoleAndPermission/AssignedToCompany/Delete', 'EmployeeRoleAssignToCompanyController@destroy');
    Route::get('/RoleAndPermission/AssignedToCompany/Edit/{emp_code}', 'EmployeeRoleAssignToCompanyController@edit');
    Route::post('/RoleAndPermission/AssignedToCompany/Update/{emp_code}', 'EmployeeRoleAssignToCompanyController@update');
	
	//Jobcard Batch Update
    Route::get('/Jobcard/EditMode', 'JobcardBatchEditController@index');
	Route::get('/Jobcard/EditMode/{emp_code}/{start_date}/{end_date}/{msg_type}', 'JobcardBatchEditController@indexEx');
	Route::post('/Jobcard/EditMode/{emp_code}/{start_date}/{end_date}/{msg_type}', 'JobcardBatchEditController@create');
    Route::post('/Jobcard/EditMode', 'JobcardBatchEditController@create');
	Route::post('/Jobcard/Row/viewMode', 'JobcardBatchEditController@reviewOT');
	Route::post('/Jobcard/Row/Save/Multiple', 'JobcardBatchEditController@saveBatch');
	
	//Shift Missing Log Create
    Route::get('/Shift/missing/report', 'ShiftMissingEmployeeController@index');
    Route::post('/Shift/missing/report', 'ShiftMissingEmployeeController@create');
    Route::post('/Shift/update/missing/report', 'ShiftMissingEmployeeController@reportUpdate');
	Route::get('/Shift/missing/report/Edit/{id}', 'ShiftMissingEmployeeController@edit');
	Route::post('/Shift/missing/report/Update/{id}', 'ShiftMissingEmployeeController@update');
	Route::get('/Shift/missing/Log/{status}', 'ShiftMissingEmployeeController@showLog');
	Route::post('/Shift/updateAll/missing/report', 'ShiftMissingEmployeeController@reportUpdateAll');
	
	
    Route::get('/ShiftMissing/Export/Excel/{company_id}/{start_date}/{end_date}', 'ShiftMissingEmployeeController@ExportExcel');
    Route::get('/ShiftMissing/Export/Pdf/{company_id}/{start_date}/{end_date}', 'ShiftMissingEmployeeController@exportPdfReport');
	
	
	Route::get('/AttendanceReprocess', 'AttendanceReProcessController@index');
    Route::post('/AttendanceReprocess/Filter', 'AttendanceReProcessController@show');
    Route::post('Attendance/Jobcard/Modify/Process/Log', 'AttendanceReProcessController@ReprocessLog');
	
	
});
Route::get('AutoProcessBackend/{start_date}/{end_date}', 'Report\OTReportController@proONWH');
Route::get('AttendanceLogProcessCheck', 'AjaxProcessController@AttendanceLogProcesscheck');
Route::get('TotalUser', 'AjaxProcessController@totalUser');
Route::get('TotalEmployee', 'AjaxProcessController@totalEmployee');
Route::get('TotalLeave', 'AjaxProcessController@totalLeave');
Route::get('LeavePending', 'AjaxProcessController@totalLeavePending');
Route::get('TotalLog', 'AjaxProcessController@totalLog');
Route::get('MergeCompanyInfoFromEx', 'AjaxProcessController@updateCompanyInfoFromEx');

Route::get('AttendanceLogProcess/New/{flag}', 'AjaxProcessController@Attendance');
Route::get('AttendanceLog/Process/New', 'AttendanceJobcardController@create');
Route::get('OTWHProcess/New/{flag}', 'Report\JobcardReportController@ProcessWeekendOTExJobCard');
Route::get('WeekendHolidayLogProcess', 'AjaxProcessController@PushWeekendNHoliday');
Route::get('AttendanceLeaveProcess/LL/{company_id}/{date}/{emp_code}', 'Report\JobcardReportController@LeaveDayManuallyCheck');
Route::get('ReplaceCompanyID', 'AjaxProcessController@ReplacEmployeeCompany');
Route::get('ReplaceDeP', 'AjaxProcessController@ReplaceDepartment');
//ajax swap loop
Route::get('Swap/Loop/{from_shift_id}/{to_shift_id}', 'ShiftEmployeeSwapController@ProcessAutoData');
Route::get('old/employee/merge', 'EmployeeInfoController@oldDataMerge');

Route::get('old/Payroll/merge', 'EmployeeInfoController@oldPayrollData');
//return view('module.settings.gender');
