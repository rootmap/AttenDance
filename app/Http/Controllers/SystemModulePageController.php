<?php

namespace App\Http\Controllers;

use App\SystemModulePage;
use Illuminate\Http\Request;
use App\SystemModule;
use App\SystemSubModule;
use App\Company;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class SystemModulePageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $app = app();
        $routes = $app->routes->getRoutes();

        $company=Company::all();
        return view('module.settings.modulepages',['company'=>$company,'routes'=>$routes]);
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
        $this->validate($request,['company_id'=>'required',
            'name'=>'required',
            'link'=>'required',
            'system_module_id'=>'required',
            'system_sub_module_id'=>'required']);

        if(empty($request->is_active))
        {
            $is_active=0;
        }
        else
        {
            $is_active=$request->is_active;
        }

        $tab=new SystemModulePage;
        $tab->name=$request->name;
        $tab->link=$request->link;
        $tab->company_id=$request->company_id;
        $tab->system_module_id=$request->system_module_id;
        $tab->system_sub_module_id=$request->system_sub_module_id;
        $tab->is_active=$is_active;
        $tab->save();

        return redirect()->action('SystemModulePageController@index')->with('success','Information Added Successfully');

    }

    public function getSubModule(Request $request)
    {
        $company_id=$request->company_id;
        $system_module_id=$request->system_module_id;

        $dataModule=SystemSubModule::where('company_id',$company_id)
                                    ->where('system_module_id',$system_module_id)
                                    ->get();

        return response()->json($dataModule);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $json=SystemModulePage::join('system_sub_modules','system_sub_modules.id','=','system_module_pages.system_sub_module_id')
                            ->join('system_modules','system_modules.id','=','system_module_pages.system_module_id')
                            ->select('system_module_pages.*',\DB::raw('system_modules.name as module_name'),\DB::raw('system_sub_modules.name as sub_module_name'))
                            //->groupby('system_modules.id')
                            ->orderby('system_module_pages.id','DESC')
                            ->get();
        return response()->json(array("data"=>$json,"total"=>count($json)));
    }

    //eloquent example 

    /*
$payments = Payment::join('users', 'users.id', '=', 'payments.id')
        ->select(
          'payments.id', 
          \DB::raw("concat(users.first_name, ' ', users.last_name) as `name`"), 
          'users.email', 
          'payments.total', 
          'payments.created_at')
        ->get();


    */


        public function exportExcel()
        {
            $dbfields = SystemModulePage::all();

        // Initialize the array which will be passed into the Excel
        // generator.
            $excelArray = []; 

        // Define the Excel spreadsheet headers
            $excelArray[] = \DB::getSchemaBuilder()->getColumnListing("system_module_pages");

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
            foreach ($dbfields as $field) {
                $excelArray[] = $field->toArray();
            }

        // Generate and return the spreadsheet
            \Excel::create('SystemModulePageData_'.date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
                $excel->setTitle('System Module Page');
                $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
                $excel->setDescription('System Module Page');

            // Build the spreadsheet, passing in the payments array
                $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                    $sheet->fromArray($excelArray, null, 'A1', false, false);
                });

            })->download('xlsx');
        }


        public function exportPdf()
        {

            $content='<h3>System Module Page List</h3>';
            $content .='<h5>Genarated : '.date('d/m/Y H:i:s').'</h5>';
            // instantiate and use the dompdf class
            $excelArray = \DB::getSchemaBuilder()->getColumnListing("system_module_pages");
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
                $datarows = SystemModulePage::all();
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company=Company::all();
        $module=SystemModule::all();
        $submodule=SystemSubModule::all();
        $data=SystemModulePage::find($id);
        return view('module.settings.modulepages',['data'=>$data,'company'=>$company,'module'=>$module,'submodule'=>$submodule]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $this->validate($request,['company_id'=>'required',
            'name'=>'required',
            //'link'=>'required',
            //'system_module_id'=>'required',
            //'system_sub_module_id'=>'required'
			]);

        if(empty($request->is_active))
        {
            $is_active=0;
        }
        else
        {
            $is_active=$request->is_active;
        }

        $tab=SystemModulePage::find($id);
        $tab->name=$request->name;
        //$tab->link=$request->link;
        //$tab->company_id=$request->company_id;
        //$tab->system_module_id=$request->system_module_id;
        //$tab->system_sub_module_id=$request->system_sub_module_id;
        $tab->is_active=$is_active;
        $tab->save();

        return redirect()->action('SystemModulePageController@index')->with('success','Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $Country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $del=SystemModulePage::destroy($request->id);
        return 1;    
    }
}
