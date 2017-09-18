<?php

namespace App\Http\Controllers;
use App\UploadAttendanceField;
use App\UploadAttendanceSetting;
use Illuminate\Http\Request;

class UploadAttendanceSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       return view('module.settings.attendancesetting');
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

        $company_id=MenuPageController::loggedUser('company_id');

        $chk=UploadAttendanceSetting::where('company_id',$company_id)->count();
        if($chk==0)
        {
            $is_txt=0;

            $is_manual=$request->is_manual?$request->is_manual:0;
            if(!empty($is_manual))
            {
                $this->validate($request,['manual_file_type'=>'required']);
            }


            $manual_file_type=$request->manual_file_type?$request->manual_file_type:0;
            if(!empty($manual_file_type) && $manual_file_type=="txt")
            {
                $is_txt=1;
                $this->validate($request,['txt_data_separetor'=>'required']);
            }

            $is_automatic=$request->is_automatic?$request->is_automatic:0;

            $tab=new UploadAttendanceSetting;
            $tab->company_id=$company_id;
            $tab->is_manual=$is_manual;
            $tab->manual_file_type=$manual_file_type;
            $tab->is_txt=$is_txt;
            $tab->txt_data_separetor=$request->txt_data_separetor;
            $tab->is_automatic=$is_automatic;
            $tab->save();

            return redirect()->action('UploadAttendanceSettingController@index')->with('success','Information Added Successfully');
        }
        else
        {
            return redirect()->action('UploadAttendanceSettingController@index')->with('warning','File Setup Already Done for this Company.');
        }
        


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $json=UploadAttendanceSetting::all();
        return response()->json(array("data"=>$json,"total"=>count($json)));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data=UploadAttendanceSetting::find($id);
        return view('module.settings.attendancesetting',['data'=>$data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
     
        $company_id=MenuPageController::loggedUser('company_id');

        $is_txt=0;
        $chkCompany=UploadAttendanceSetting::where('company_id',$company_id)->count();
        if($chkCompany==1)
        {

            $is_manual=$request->is_manual?$request->is_manual:0;
            if(!empty($is_manual))
            {
                $this->validate($request,['manual_file_type'=>'required']);
            }


            $manual_file_type=$request->manual_file_type?$request->manual_file_type:0;
            if(!empty($manual_file_type) && $manual_file_type=="txt")
            {
                $is_txt=1;
                $this->validate($request,['txt_data_separetor'=>'required']);
            }

            $txt_data_separetor=$request->txt_data_separetor;
            if($manual_file_type!="txt")
            {
                $txt_data_separetor="False";
            }

            $is_automatic=$request->is_automatic?$request->is_automatic:0;

            $tab=UploadAttendanceSetting::find($id);
            $tab->company_id=$company_id;
            $tab->is_manual=$is_manual;
            $tab->manual_file_type=$manual_file_type;
            $tab->is_txt=$is_txt;
            $tab->txt_data_separetor=$txt_data_separetor;
            $tab->is_automatic=$is_automatic;
            $tab->save();

            


            return redirect()->action('UploadAttendanceSettingController@index')->with('success','Information Updated Successfully');
        }
        else
        {
            return redirect()->action('UploadAttendanceSettingController@index')->with('warning','Information failed to modify.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $del=UploadAttendanceSetting::destroy($request->id);
        return 1; 
    }
}
