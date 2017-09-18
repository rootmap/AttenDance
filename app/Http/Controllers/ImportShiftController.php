<?php

namespace App\Http\Controllers;
use App\Shift;
use App\ImportShift;
use App\UploadAttendanceSetting;
use Illuminate\Http\Request;
use Excel;
class ImportShiftController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
          return view('module.settings.inputShift');
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
            $destinationPath = 'upload/leavebalance/raw_file';
            $file->move($destinationPath, $new_file_name);

            $company_id = MenuPageController::loggedUser('company_id');
            $emp_code = MenuPageController::loggedUser('emp_code');

            $tab = new ImportShift();
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

//            exit();
            return redirect()->action('ImportShiftController@index')->with('success', 'Attendance File Uploaded Successfully');
        } else {
//            exit();
            return redirect()->action('ImportShiftController@index')->with('warning', 'Invalid File Format.');
        }
    }

//ProcessNCheckRawLog($company_id,$emp_code,$machine_id,$date,$time)

    private function ProcessNCheckRawLog($id, $company_id = 0, $shift_title = '', $start_day = 0, $end_day = 0, $is_night_shift = 0) {

        $chkLog = Shift::where('company_id', $company_id)
                ->where('name', $shift_title)
                ->count();
        if ($chkLog == 0) {

            $tab = new Shift();
            $tab->id = $id;
            $tab->company_id = $company_id;
            $tab->name = $shift_title;
            $tab->shift_start_time = $start_day;
            $tab->shift_end_time = $end_day;
            $tab->is_night_shift = $is_night_shift;
            $tab->save();
            
        } else {
            
            $chkLog = Shift::where('company_id', $company_id)
                    ->where('name', $shift_title)
                    ->first();

            $tab = Shift::find($chkLog->id);
            $tab->id = $id;
            $tab->company_id = $company_id;
            $tab->name = $shift_title;
            $tab->shift_start_time = $start_day;
            $tab->shift_end_time = $end_day;
            $tab->is_night_shift = $is_night_shift;
            $tab->save();
            
        }
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
//                        echo '<pre>';
//                        print_r($sheet);
//                        exit();
                        $this->ProcessNCheckRawLog(
                                $sheet->id, $company_id, $sheet->name, $sheet->start_time, $sheet->end_time, $sheet->is_night_shift
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

                //if ($indexI != 0) {
                    $json[] = array(
                        'id' => $data[0],
                        'name' => $data[1],
                        'start_time' => $data[2],
                        'end_time' => $data[3],
                        'is_night_shift' => $data[4]
                    );
                //}


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
                        
                        $this->ProcessNCheckRawLog(
                                $data['id'], $company_id, $data['name'], $data['start_time'], $data['end_time'], $data['is_night_shift']
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
     * @param  \App\ImportShift  $importShift
     * @return \Illuminate\Http\Response
     */
    public function show(ImportShift $importShift) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ImportShift  $importShift
     * @return \Illuminate\Http\Response
     */
    public function edit(ImportShift $importShift) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ImportShift  $importShift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ImportShift $importShift) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ImportShift  $importShift
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImportShift $importShift) {
        //
    }

}
