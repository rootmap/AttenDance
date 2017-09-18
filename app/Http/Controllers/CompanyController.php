<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

       return view('module.settings.company');
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
        $this->validate($request,[
            'name'=>'required',
            'address'=>'required',
            'phone'=>'required',
            'companyemail'=>'required',
            'HRemail'=>'required',
            'Leaveemail'=>'required',
            'logo'=>'required',
            'emp_code_length'=>'required|numeric|max:20'
            ]);

        $is_company_prefix=0;
        $company_prefix='';
        if(isset($request->is_company_prefix))
        {
            $is_company_prefix=1;
            $this->validate($request,[
                'company_prefix'=>'required|string|max:5'
                ]);

            $company_prefix=$request->company_prefix;

        }
        else
        {
            $is_company_prefix=0;
            $company_prefix='';
        }

        $logo_name="";
        if ($request->hasFile('logo')) {
            if($request->file('logo')->isValid()) {
                try {
                    $file = $request->file('logo');
                    $name = "company_".time() . '.' . $file->getClientOriginalExtension();
                    $logo_name=$name;

                    $request->file('logo')->move("./upload/company_logo", $name);
                } catch (Illuminate\Filesystem\FileNotFoundException $e) {

                }
            } 
        }

//musa novi biography
        $is_company_staffgrade=$request->is_company_staffgrade?$request->is_company_staffgrade:0;
        $is_active=$request->is_active?$request->is_active:0;


        $tab=new Company;
        $tab->name=$request->name;
        $tab->address=$request->address;
        $tab->phone=$request->phone;
        $tab->company_email=$request->companyemail;
        $tab->hr_email=$request->HRemail;
        $tab->leave_email=$request->Leaveemail;
        $tab->company_logo=$logo_name;
        $tab->emp_code_length=$request->emp_code_length;
        $tab->is_company_prefix=$is_company_prefix;
        $tab->is_company_staffgrade=$is_company_staffgrade;
        $tab->company_prefix=$request->company_prefix;
        $tab->is_active=$is_active;
        $tab->save();

        return redirect()->action('CompanyController@index')->with('success','Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        $json=Company::all();
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
        $data=Company::find($id);
        return view('module.settings.company',['data'=>$data]);
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
     $this->validate($request,[
        'name'=>'required',
        'address'=>'required',
        'phone'=>'required',
        'companyemail'=>'required',
        'HRemail'=>'required',
        'Leaveemail'=>'required',
        'emp_code_length'=>'required|numeric|max:20'
        ]);

     $is_company_prefix=0;
     $company_prefix='';
     if(isset($request->is_company_prefix))
     {
        $is_company_prefix=1;
        $this->validate($request,[
            'company_prefix'=>'required|string|max:10'
            ]);

        $company_prefix=$request->company_prefix;

    }
    else
    {
        $is_company_prefix=0;
        $company_prefix='';
    }

    $logo_name="";
    if ($request->hasFile('logo')) {
        if($request->file('logo')->isValid()) {
            unlink("./upload/company_logo/".$request->ex_logo);
            try {
                $file = $request->file('logo');
                $name = "company_".time() . '.' . $file->getClientOriginalExtension();
                $logo_name=$name;

                $request->file('logo')->move("./upload/company_logo", $name);
            } catch (Illuminate\Filesystem\FileNotFoundException $e) {

            }
        } 
    }
    else
    {
        $logo_name=$request->ex_logo;
    }


    $is_company_staffgrade=$request->is_company_staffgrade?$request->is_company_staffgrade:0;
    $is_active=$request->is_active?$request->is_active:0;


    $tab=Company::find($id);;
    $tab->name=$request->name;
    $tab->address=$request->address;
    $tab->phone=$request->phone;
    $tab->company_email=$request->companyemail;
    $tab->hr_email=$request->HRemail;
    $tab->leave_email=$request->Leaveemail;
    $tab->company_logo=$logo_name;
    $tab->emp_code_length=$request->emp_code_length;
    $tab->is_company_prefix=$is_company_prefix;
    $tab->is_company_staffgrade=$is_company_staffgrade;
    $tab->company_prefix=$request->company_prefix;
    $tab->is_active=$is_active;

    $tab->save();


    return redirect()->action('CompanyController@index')->with('success','Information Updated Successfully');
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $del=Company::destroy($request->id);
        return 1; 
    }
}
