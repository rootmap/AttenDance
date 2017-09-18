<?php

namespace App\Http\Controllers;

use App\ImportEmployee;
use App\EmployeeInfo;
use App\EmployeeDepartment;
use App\EmployeeDesignation;
use App\EmployeeSection;
use App\EmployeeStaffGrade;
use App\User;
use App\EmployeeAssignRole;
use App\UploadAttendanceSetting;
use Illuminate\Http\Request;
use Excel;

class ImportEmployeeController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('module.settings.employeeImport');
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
        ini_set('max_execution_time', 500);


        $company_id = MenuPageController::loggedUser('company_id');

        $this->validate($request, [
            'employee' => 'required|file',
        ]);



        $sqlPatternGet = UploadAttendanceSetting::where('company_id', $company_id)
                ->where('is_manual', '1')
                ->get();

        if (isset($sqlPatternGet)) {
            $manual_file_type = $sqlPatternGet[0]->manual_file_type;
        } else {
            $manual_file_type = "Not Mention";
        }

        /* echo $manual_file_type;
          exit(); */



        $new_file_name = '';

        $file = $request->file('employee');

        //Display File Name
        $new_file_name = date('Y_m_d_H_i_s') . '.' . $file->getClientOriginalExtension();


        // $file->getClientOriginalExtension();


        $filearray = array('txt', 'csv', 'xls', 'xlsx');

        if (in_array(strtolower($file->getClientOriginalExtension()), $filearray) && $manual_file_type == strtolower($file->getClientOriginalExtension())) {

            //Move Uploaded File
            $destinationPath = 'upload/employeefile/raw_file';
            $file->move($destinationPath, $new_file_name);

            $company_id = MenuPageController::loggedUser('company_id');
            $emp_code = MenuPageController::loggedUser('emp_code');

            $tab = new ImportEmployee();
            $tab->company_id = $company_id;
            $tab->filename = $new_file_name;
            $tab->emp_code = $emp_code;
            $tab->uploaded_user_ip = $request->ip();
            $tab->is_read = '0';
            $tab->save();


            if ($file->getClientOriginalExtension() == "txt") {
                $this->readTXT($destinationPath, $new_file_name);
            } elseif ($file->getClientOriginalExtension() == "csv") {

                $this->readCSV($destinationPath . '/' . $new_file_name);
            } elseif ($file->getClientOriginalExtension() == "xls" || $file->getClientOriginalExtension() == "xlsx") {
                $this->readXLS($destinationPath . '/' . $new_file_name);
            }

            //  exit();
            return redirect()->action('ImportEmployeeController@index')->with('success', 'Attendance File Uploaded Successfully');
        } else {
            // exit();
            return redirect()->action('ImportEmployeeController@index')->with('warning', 'Invalid File Format.');
        }
    }

//ProcessNCheckRawLog($company_id,$emp_code,$machine_id,$date,$time)

    private function ProcessNCheckRawLog($name = '', $emp_code = 0, $company_id = 0, $email = '', $user_id = 0, $department_id = 0, $section_id = 0, $designation_id = 0, $staff_grade_id = 0) {

//        $chkLog = EmployeeInfo::where('company_id', $company_id)
//                ->where('emp_code', $emp_code)
//                ->count();
//        if ($chkLog == 0) {
            if(empty($email))
            {
                $email="noemail@systechunimax.com";
            }
            
            //$chk_user_ex = User::where('email', $email)->count();

            //if ($chk_user_ex == 0) {
                if (!empty($email)) {
                    $count_user = User::where('email', $email)->count();
                    if ($count_user == 0) {
                        $register = new User();
                        $register->name = $name;
                        $register->email = $email;
                        $register->password = bcrypt('123456');
                        $register->save();
                    }
                    else
                    {
                        $register = User::where('email', $email)->first();
                    }
                } else {
                    $email = "noemail@systechunimax.com";
                    $counttabDef = User::where('email', $def_email)->count();
                    if ($counttabDef == 0) {
                        $register = new User();
                        $register->name = $name;
                        $register->email = $email;
                        $register->password = bcrypt('noemail');
                        $register->save();
                    } else {
                        $register = User::where('email', $email)->first();
                    }
                }

                $user_id = $register->id;
                if ($register->save() == 1) {
                    
                    $tab = new EmployeeInfo();
                    $tab->company_id = $company_id;
                    $tab->emp_code = $emp_code;
                    $tab->first_name = $name;
                    $tab->email = $email;
                    $tab->user_id = $user_id;
                    $tab->save();

                    $dept = new EmployeeDepartment();
                    $dept->company_id = $company_id;
                    $dept->emp_code = $emp_code;
                    $dept->department_id = $department_id;
                    $dept->save();

                    $sec = new EmployeeSection();
                    $sec->company_id = $company_id;
                    $sec->emp_code = $emp_code;
                    $sec->department_id = $department_id;
                    $sec->section_id = $section_id;
                    $sec->save();

                    $desi = new EmployeeDesignation();
                    $desi->company_id = $company_id;
                    $desi->emp_code = $emp_code;
                    $desi->department_id = $department_id;
                    $desi->designation_id = $designation_id;
                    $desi->save();

                    $staff = new EmployeeStaffGrade();
                    $staff->company_id = $company_id;
                    $staff->emp_code = $emp_code;
                    $staff->department_id = $department_id;
                    $staff->section_id = $section_id;
                    $staff->staff_grade_id = $staff_grade_id;
                    $staff->save();

                    $staff_role = new EmployeeAssignRole();
                    $staff_role->company_id = $company_id;
                    $staff_role->emp_code = $emp_code;
                    $staff_role->system_access_role_id = 5;
                    $staff_role->is_active = 1;
                    $staff_role->save();
                }
            //}
        //}
    }

    private function readXLS($destinationPath) {
        if (!empty($destinationPath)) {
            $company_id = MenuPageController::loggedUser('company_id');
            $filePath = $destinationPath;
            if (!empty($filePath)) {
                $i = 0;
                Excel::load($filePath, function ($reader) {
                    $company_id = MenuPageController::loggedUser('company_id');
                    $results = $reader->get();
                    $results = $reader->all();
                    foreach ($results as $sheet) {

                        $this->ProcessNCheckRawLog(
                                $sheet->name, $sheet->emp_code, $company_id, $sheet->email, $sheet->user_id, $sheet->department, $sheet->section, $sheet->designation, $sheet->staffgrade
                        );
                    }
                });


                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function csv_to_array($filePath) {

        $json = [];

        ini_set('auto_detect_line_endings', TRUE);
        $row = 1;

        if (($handle = fopen($filePath, "r")) !== FALSE) {

            $indexI = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                //echo "<p> $num fields in line $row: <br /></p>\n";
                //print_r($data);


                $json[] = array(
                    'name' => $data[0],
                    'emp_code' => $data[1],
                    'company_id' => $data[2],
                    'email' => $data[3] ? $data[3] : 'noemail@systechunimax.com',
                    'user_id' => $data[4],
                    'department' => $data[5],
                    'section' => $data[6],
                    'designation' => $data[7],
                    'staffgrade' => $data[8],
                );


                //echo "<hr>-".$indexI."-<br>";
                $row++;
                $indexI++;
            }
            fclose($handle);
            //echo "<pre>";
            //print_r($json);
            //exit();
            return $json;
        }

        ini_set('auto_detect_line_endings', FALSE);
    }

    private function readCSV($destinationPath) {
        if (!empty($destinationPath)) {
            $company_id = MenuPageController::loggedUser('company_id');
            $filePath = $destinationPath;
            if (!empty($filePath)) {
                $i = 0;

                $sqlPatternGet = UploadAttendanceSetting::where('company_id', $company_id)
                        ->where('is_manual', '1')
                        ->get();



                $csv = $this->csv_to_array($filePath);

                if (count($csv) == 0) {
                    return 0;
                } else {
                    foreach ($csv as $data):
//                        echo "<pre>";
//                        print_r($data);
//                        exit();

                        $this->ProcessNCheckRawLog(
                                $data['name'], $data['emp_code'], $company_id, $data['email'], $data['user_id'], $data['department'], $data['section'], $data['designation'], $data['staffgrade']
                        );


                    endforeach;

                    return 1;
                }
            }
            else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function readTXT($destinationPath = '', $fileName = '') {
        if (!empty($fileName)) {
            $company_id = MenuPageController::loggedUser('company_id');
            $filePath = file($destinationPath . '/' . $fileName);
            if (!empty($filePath)) {
                $i = 0;

                $sqlPatternGet = UploadAttendanceSetting::where('company_id', $company_id)
                        ->where('is_manual', '1')
                        ->get();


                if (isset($sqlPatternGet)) {
                    $spiPattern = $sqlPatternGet[0]->txt_data_separetor;
                } else {
                    $spiPattern = ":";
                }



                foreach ($filePath as $line):

                    $data = $this->SplitData($spiPattern, $line);

                    $this->ProcessNCheckRawLog(
                            $company_id, $data['Employee_Raw_Id'], $data['Machine_id'], date('Y-m-d', strtotime($data['Raw_Date'])), date('H:i:s', strtotime($data['Raw_Time']))
                    );

                    //echo $raw_time;

                    $i++;
                    /* if($i==3)
                      {
                      break;
                      } */

                endforeach;
                return 1;
                //return print_r($filePath);
            }
            else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function SplitData($spiPattern = '', $line = array()) {
        if (!empty($spiPattern)) {
            if ($spiPattern == ":") {
                $split = explode($spiPattern, $line);
                if (count($split) > 4) {
                    $machine_id = $split[0];

                    //get device id
                    $employee_raw_id = $split[1];

                    //get raw Date
                    $raw_date = $split[2];

                    //get raw Time
                    $raw_time = $split[3] . ":" . $split[4] . ":" . $split[5];
                } else {
                    $machine_id = "00";

                    //get device id
                    $employee_raw_id = "00";

                    //get raw Date
                    $raw_date = "0000-00-00";

                    //get raw Time
                    $raw_time = "00:00:00";
                }
            } elseif ($spiPattern == "#") {
                $split = explode($spiPattern, $line);
                //get device id
                $machine_id = $split[0];

                //get device id
                $employee_raw_id = $split[1];

                //get raw Date
                $raw_date = $split[2];

                //get raw Time
                $raw_time = $split[3];
            } elseif ($spiPattern == ",") {
                $split = explode($spiPattern, $line);
                //get device id
                $machine_id = $split[0];

                //get device id
                $employee_raw_id = $split[1];

                //get raw Date
                $raw_date = $split[2];

                //get raw Time
                $raw_time = $split[3];
            } elseif ($spiPattern == ";") {
                $split = explode($spiPattern, $line);
                //get device id
                $machine_id = $split[0];

                //get device id
                $employee_raw_id = $split[1];

                //get raw Date
                $raw_date = $split[2];

                //get raw Time
                $raw_time = $split[3];
            } elseif ($spiPattern == "   ") {
                $split = explode($spiPattern, $line);
                //get device id
                $machine_id = $split[0];

                //get device id
                $employee_raw_id = $split[1];

                //get raw Date
                $raw_date = $split[2];

                //get raw Time
                $raw_time = $split[3];
            }


            return array('Machine_id' => $machine_id, 'Employee_Raw_Id' => $employee_raw_id, 'Raw_Date' => $raw_date, 'Raw_Time' => $raw_time);
        } else {
            return array();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ImportEmployee  $importEmployee
     * @return \Illuminate\Http\Response
     */
    public function show(ImportEmployee $importEmployee) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ImportEmployee  $importEmployee
     * @return \Illuminate\Http\Response
     */
    public function edit(ImportEmployee $importEmployee) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ImportEmployee  $importEmployee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ImportEmployee $importEmployee) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ImportEmployee  $importEmployee
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImportEmployee $importEmployee) {
        //
    }

}
