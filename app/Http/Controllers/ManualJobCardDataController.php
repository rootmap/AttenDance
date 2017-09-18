<?php

namespace App\Http\Controllers;

use App\EmployeeInfo;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ManualJobCardDataController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    public function filterReport(Request $request) {
        $company_id = $request->company_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if (empty($request->company_id) || !isset($request->company_id) || $request->company_id == "undefined") {
            $company_id = 0;
        } else {
            $company_id = $request->company_id;
        }

        if ($company_id > 0) {

            $json = DB::table('manual_job_card_entries')
                    ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                    ->select(DB::raw('manual_job_card_entries.*,
                        companies.name as company_id'))
                    ->groupBy('manual_job_card_entries.id')
                    ->where('manual_job_card_entries.company_id', $company_id)
                    ->whereBetween('manual_job_card_entries.date', [$start_date, $end_date])
                    ->orderBy('manual_job_card_entries.id', 'DESC')
                    ->get();
        } else {
            $logged_emp_company_id = MenuPageController::loggedUser('company_id');
            if (!empty($logged_emp_company_id)) {
                $json = DB::table('manual_job_card_entries')
                        ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                        ->select(DB::raw('manual_job_card_entries.*,
                        companies.name as company_id'))
                        ->where('manual_job_card_entries.company_id', $logged_emp_company_id)
                        ->whereBetween('manual_job_card_entries.date', [$start_date, $end_date])
                        ->groupBy('manual_job_card_entries.id')
                        ->orderBy('manual_job_card_entries.id', 'DESC')
                        ->get();
            }
        }


        return response()->json(array("data" => $json, "total" => count($json)));
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

    public function reportShow() {
        $data = Company::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        // $logged_emp_company_id='';
        return view('module.settings.manualjobcard_report', ['company' => $data, 'logged_emp_com' => $logged_emp_company_id]);
    }

    public function show() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (empty($logged_emp_company_id) || $logged_emp_company_id == "Undefined") {
            $logged_emp_company_id = 0;
        } else {

            $logged_emp_company_id;
        }
        // echo $logged_emp_company_id;
        if ($logged_emp_company_id > 0) {

            $branch = DB::table('manual_job_card_entries')
                    ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                    ->select(DB::raw('manual_job_card_entries.*,
                        companies.name as company_id'))
                    ->where('manual_job_card_entries.company_id', $logged_emp_company_id)
                    ->groupBy('manual_job_card_entries.id')
                    ->orderBy('manual_job_card_entries.id', 'DESC')
                    ->get();
        } else {
            $branch = DB::table('manual_job_card_entries')
                    ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                    ->select(DB::raw('manual_job_card_entries.*,
                        companies.name as company_id'))
                    ->groupBy('manual_job_card_entries.id')
                    ->orderBy('manual_job_card_entries.id', 'DESC')
                    ->get();
        }

        return response()->json(array("data" => $branch, "total" => count($branch)));
    }

    public function exportDatewiseFilterExcel(Request $request, $company_id = 0, $start_date = 0, $end_date = 0) {


       // $company_id = $request->company_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

         if (empty($request->company_id) || $request->company_id == "Undefined" || $request->company_id == "undefined" || $request->company_id == '') {

                if ($end_date == 0 || $start_date == 0) {
                    // echo "if";
                    $company_id = 0;
                } else {
                    //  echo "else";
                    $company_id = $logged_emp_company_id = MenuPageController::loggedUser('company_id');
                    ;
                }
            } else {

                $company_id = $request->company_id;
            }

           
            if ($company_id > 0) {
                // if (!empty($logged_emp_company_id) || isset($logged_emp_company_id) || $logged_emp_company_id != "Undefined") {
                $dbfields    = DB::table('manual_job_card_entries')
                        ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                        ->select(DB::raw('
                            companies.name as Company_Name,
                            manual_job_card_entries.emp_code as Employee_Code,
                            manual_job_card_entries.day_type as Day_Status,
                            manual_job_card_entries.date as Date
                        '))
                        ->where('manual_job_card_entries.company_id', $company_id)
                        ->whereBetween('manual_job_card_entries.date', [$start_date, $end_date])
                        ->groupBy('manual_job_card_entries.id')
                        ->orderBy('manual_job_card_entries.id', 'DESC')
                        ->get();
            } else {
                $logged_emp_company_id = MenuPageController::loggedUser('company_id');
                if (empty($logged_emp_company_id) || $logged_emp_company_id == "Undefined") {
                    $logged_emp_company_id = 0;
                } else {

                    $logged_emp_company_id;
                }


                if ($logged_emp_company_id > 0) {
                    $dbfields = DB::table('manual_job_card_entries')
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
                    $dbfields = DB::table('manual_job_card_entries')
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
            }
        //->toArray();
        // Initialize the array which will be passed into the Excel
        // generator.
        $excelArray = [];
     
        // Define the Excel spreadsheet headers
        $excelArray [] = [
            'Company',
            'Employee Code',
            'Day Status',
            'Date',
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($dbfields as $key => $field) {
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

    public function exportDatewiseFilterPdf(Request $request, $company_id = 0, $start_date = 0, $end_date = 0) {

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
            foreach ($excelArray as $exhead):
                $content .='<th>' . $exhead . '</th>';
            endforeach;
            $content .='</tr>';
            $content .='</thead>';


            $rows = count($excelArray);
            $start_date = $request->start_date;
            $end_date = $request->end_date;



            if (empty($request->company_id) || $request->company_id == "Undefined" || $request->company_id == "undefined" || $request->company_id == '') {

                if ($end_date == 0 || $start_date == 0) {
                    // echo "if";
                    $company_id = 0;
                } else {
                    //  echo "else";
                    $company_id = $logged_emp_company_id = MenuPageController::loggedUser('company_id');
                    ;
                }
            } else {

                $company_id = $request->company_id;
            }

           
            if ($company_id > 0) {
                // if (!empty($logged_emp_company_id) || isset($logged_emp_company_id) || $logged_emp_company_id != "Undefined") {
                $datarows = DB::table('manual_job_card_entries')
                        ->leftjoin('companies', 'companies.id', '=', 'manual_job_card_entries.company_id')
                        ->select(DB::raw('
                            companies.name as Company_Name,
                            manual_job_card_entries.emp_code as Employee_Code,
                            manual_job_card_entries.day_type as Day_Status,
                            manual_job_card_entries.date as Date
                        '))
                        ->where('manual_job_card_entries.company_id', $company_id)
                        ->whereBetween('manual_job_card_entries.date', [$start_date, $end_date])
                        ->groupBy('manual_job_card_entries.id')
                        ->orderBy('manual_job_card_entries.id', 'DESC')
                        ->get();
            } else {
                $logged_emp_company_id = MenuPageController::loggedUser('company_id');
                if (empty($logged_emp_company_id) || $logged_emp_company_id == "Undefined") {
                    $logged_emp_company_id = 0;
                } else {

                    $logged_emp_company_id;
                }


                if ($logged_emp_company_id > 0) {
                    $datarows = DB::table('manual_job_card_entries')
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
                    $datarows = DB::table('manual_job_card_entries')
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
            }


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
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }

}
