<?php

namespace App\Http\Controllers;
use App\Company;
use App\EmployeeCompany;
use App\User;
use Auth;
use App\EmployeeCode;
use App\EmployeeInfo;
use Illuminate\Http\Request;

class EmployeeCodeController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function zerofill($num, $zerofill) {

        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }

    /*private function dynamicallyEmpCode($num,$fillzero) {
        for ($i = 1; $i < $num; $i++) {
            return zerofill($i,$num);
        }
    }*/


    /*public function filterNewEmployeeCode()
    {
        $logged_user_id=Auth::id();
        $check_user_info=EmployeeInfo::where('user_id',$logged_user_id)->count();
        if($check_user_info==0)
        {
            $dataCompany=Company::orderBy('id','DESC')->take(1)->get();
        }


        $company_id=$dataCompany[0]->company_id;
        $company_code_length=$dataCompany[0]->emp_code_length;
        $company_prefix=$dataCompany[0]->company_prefix; 

        $emplastid=EmployeeCode::orderBy('id','DESC')->take(1)->get();

        $incre_id=$emplastid[0]->id+1;

        $only_code=str_pad($incre_id,$company_code_length, '0', STR_PAD_LEFT); //   $this->zerofill($company_code_length,$incre_id);


        return $company_prefix.$only_code;


        //echo "ehheheehahahahah";
    }*/


    public function filterEmployeeCode()
    {
        $logged_user_id=Auth::id();
        //echo $logged_user_id;
        //exit();
        $check_user_info=EmployeeInfo::where('user_id',$logged_user_id)->count();
        if($check_user_info==0)
        {
            $dataCompany=Company::orderBy('id','DESC')->take(1)->get();
        }
        else
        {
            $bas_user_info=EmployeeInfo::where('user_id',$logged_user_id)->get();
            $dataCompany=Company::where('id',$bas_user_info[0]->company_id)->get();
        }
        //echo "<pre>";
        //print_r($bas_user_info);
        //exit();



        $company_id=$dataCompany[0]->company_id;
        $company_code_length=$dataCompany[0]->emp_code_length;
        $company_prefix=$dataCompany[0]->company_prefix; 

        $emplastid=EmployeeCode::orderBy('id','DESC')->take(1)->get();

        $incre_id=$emplastid[0]->id+1;

        $only_code=str_pad($incre_id,$company_code_length, '0', STR_PAD_LEFT); //   $this->zerofill($company_code_length,$incre_id);

        return $company_prefix.$only_code;

        //echo "ehheheehahahahah";
    }


    public function index() {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmployeeCode  $employeeCode
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeCode $employeeCode) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\EmployeeCode  $employeeCode
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeCode $employeeCode) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmployeeCode  $employeeCode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeCode $employeeCode) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EmployeeCode  $employeeCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeCode $employeeCode) {
        //
    }

}
