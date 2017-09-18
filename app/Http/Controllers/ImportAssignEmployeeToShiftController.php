<?php

namespace App\Http\Controllers;

use App\AssignEmployeeToShift;
use App\ImportAssignEmployeeToShift;
use App\UploadAttendanceSetting;
use App\Shift;
use App\Company;
use App\EmployeeInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Excel;

class ImportAssignEmployeeToShiftController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
		$company = Company::all();
		$shift = Shift::all();
        return view('module.settings.inputShiftAssign',['company'=>$company,'shift'=>$shift]);
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
		$this->validate($request, [
            'company_id' => 'required',
            'shift_id' => 'required',
            'fstart_date' => 'required',
            'fend_date' => 'required',
        ]);
        
//        echo $request->request_file;
//        exit();
        $company_id = MenuPageController::loggedUser('company_id');

        $this->validate($request, [
            'request_file' => 'required',
        ]);
        


        $sqlPatternGet = UploadAttendanceSetting::where('company_id', $company_id)
                ->where('is_manual', '1')
                ->get();

        if (isset($sqlPatternGet)) {
            $manual_file_type = $sqlPatternGet[0]->manual_file_type;
        } else {
            $manual_file_type = "Not Mention";
        }




        $new_file_name = '';

        $file = $request->file('request_file');

        //Display File Name
        $new_file_name = date('Y_m_d_H_i_s') . '.' . $file->getClientOriginalExtension();


         $file->getClientOriginalExtension();
        

        $filearray = array('txt', 'csv', 'xls', 'xlsx');

        if (in_array(strtolower($file->getClientOriginalExtension()), $filearray) && $manual_file_type == strtolower($file->getClientOriginalExtension())) {

            //Move Uploaded File
            $destinationPath = 'upload/leavebalance/raw_file';
            $file->move($destinationPath, $new_file_name);

            $company_id = MenuPageController::loggedUser('company_id');
            $emp_code = MenuPageController::loggedUser('emp_code');

            $tab = new ImportAssignEmployeeToShift();
            $tab->company_id = $company_id;
            $tab->filename = $new_file_name;
            $tab->emp_code = $emp_code;
            $tab->uploaded_user_ip = $request->ip();
            $tab->is_read = '0';
            $tab->save();


            /*if ($file->getClientOriginalExtension() == "txt") {
                $this->readTXT($destinationPath, $new_file_name);
            } elseif ($file->getClientOriginalExtension() == "csv") {

                $this->readCSV($destinationPath . '/' . $new_file_name);
            } elseif ($file->getClientOriginalExtension() == "xls" || $file->getClientOriginalExtension() == "xlsx") {
                $this->readXLS($destinationPath . '/' . $new_file_name);
            }*/
			
			$filePath=$destinationPath . '/' . $new_file_name;
			if (!empty($filePath)) {
                $i = 0;
                
                Excel::load($filePath, function ($reader) use ($request) {
                    $company_id = MenuPageController::loggedUser('company_id');
                    $results = $reader->get();
                    $results = $reader->all();
                    foreach ($results as $sheet) {
						//echo "<pre>";
						//print_r($sheet);
						
						foreach($sheet as $row):
							
							//print_r($row);
							//if(!empty($row->emp_code))
							//{
								$this->ProcessNCheckRawLog($row,$request->shift_id,$request->company_id,$request->fstart_date,$request->fend_date); 
							//}
						endforeach;
						//echo $sheet->emp_code."</br>";
                        // $this->ProcessNCheckRawLog(
                        //        $sheet->emp_code
                       // ); 
					}
					
                });
				
				exit();

                return redirect()->action('ImportAssignEmployeeToShiftController@index')->with('success', 'Attendance File Uploaded Successfully');
            } else {
                return redirect()->action('ImportAssignEmployeeToShiftController@index')->with('warning', 'Invalid File Format.');
            }

            return redirect()->action('ImportAssignEmployeeToShiftController@index')->with('success', 'Attendance File Uploaded Successfully');
        } else {
            return redirect()->action('ImportAssignEmployeeToShiftController@index')->with('warning', 'Invalid File Format.');
        }
    }

    //ProcessNCheckRawLog($company_id,$emp_code,$machine_id,$date,$time)

    private function ProcessNCheckRawLog($emp_code = 0,$shift_id,$company_id,$start_date,$end_date) {
		 
		 //echo $emp_code."<br>";
		/* echo $company_id."<br>";
		 echo $shift_id."<br>";
		 echo $start_date."<br>";
		 echo $end_date."<br>";
		 echo"<pre>"; */
        
		//echo $emp_code;
		
			if(!empty($emp_code))
			{
				
				//$chk=EmployeeInfo::where('emp_code',$emp_code)->count();
				//if($chk!=0)
				//{
					$tab = new AssignEmployeeToShift();
					$tab->company_id = $company_id;
					$tab->shift_id = $shift_id;
					$tab->start_date = $start_date;
					$tab->end_date = $end_date;
					$tab->emp_code = $emp_code;
					$tab->save();
				//}
				
			}
		
				
            
        
		/* else {
            
            $chkLog = AssignEmployeeToShift::where('company_id', $company_id)
                    ->where('shift_id', $shift_id)
                    ->where('emp_code', $emp_code)
                    ->where('start_date', $shift_id)
                    ->first();

            $tab = AssignEmployeeToShift::find($chkLog->id);
            $tab->id = $id;
            $tab->company_id = $company_id;
            $tab->shift_id = $shift_id;
            $tab->start_date = $start_date;
            $tab->end_date = $end_date;
            $tab->emp_code = $emp_code;
            $tab->save();
            
        } */
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
						foreach($sheet as $row):
							if(!empty($row->emp_code))
							{
									$this->ProcessNCheckRawLog($row->emp_code);
							}
							 
						endforeach;
						//echo $sheet->emp_code."</br>";
                        // $this->ProcessNCheckRawLog(
                        //        $sheet->emp_code
                       // ); 
					}
					
                });
				
				//exit();

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
            
                    $json[] = array(
                       
                        'emp_code' => $data[0]
                    );


                //echo "<hr>-".$indexI."-<br>";
                $row++;
                $indexI++;
            }
            fclose($handle);
            //echo "<pre>";
            //print_r($json);

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
//                         print_r($data);
//                         exit();

                        $this->ProcessNCheckRawLog(
                                $data['id'], $company_id, $data['shift_id'], $data['start_date'],$data['end_date'], $data['emp_code']
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
     * @param  \App\ImportAssignEmployeeToShift  $importAssignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function show(ImportAssignEmployeeToShift $importAssignEmployeeToShift) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ImportAssignEmployeeToShift  $importAssignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function edit(ImportAssignEmployeeToShift $importAssignEmployeeToShift) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ImportAssignEmployeeToShift  $importAssignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ImportAssignEmployeeToShift $importAssignEmployeeToShift) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ImportAssignEmployeeToShift  $importAssignEmployeeToShift
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImportAssignEmployeeToShift $importAssignEmployeeToShift) {
        //
    }

}
