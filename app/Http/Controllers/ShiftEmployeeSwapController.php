<?php

namespace App\Http\Controllers;

use App\AssignEmployeeToShift;
use App\Shift;
use App\Company;
use App\ShiftEmployeeSwap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\ShiftSwapLoop;
use App\EmployeeInfo;

class ShiftEmployeeSwapController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //$company = Company::all();

        //$logged_emp_company_id = MenuPageController::loggedUser('company_id');
		
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
		
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
        $logged_emp_company_id = app('App\Http\Controllers\MenuPageController')->loggedUser('company_id');
		
        return view('module.settings.shiftswap', ['company' => $company, 'logged_emp_com' => $logged_emp_company_id]);
    }
	
	private function ProcessToShiftIND($company_id,$to_shift_id,$from_shift_id,$NewStartDate,$NewEndDate,$emp_code,$LastShiftSwapDateStart,$LastShiftSwapDate)
	{
		$shift = new AssignEmployeeToShift;
		$shift->company_id=$company_id;
		$shift->shift_id=$to_shift_id;
		$shift->start_date=$NewStartDate;
		$shift->end_date = $NewEndDate;
		$shift->emp_code = $emp_code;
		$shift->save();

		
		$swap = new ShiftEmployeeSwap();
		$swap->company_id = $company_id;
		$swap->fromshift_id = $from_shift_id;
		$swap->formstart_date = $LastShiftSwapDateStart;
		$swap->formend_date = $LastShiftSwapDate;

		$swap->toshift_id = $to_shift_id;
		$swap->tostart_date = $NewStartDate;
		$swap->toend_date = $NewEndDate;
		$swap->emp_code ='System_Robot';
		$swap->save();
		
		return 1;
	}
	
	public function ProcessAutoData($from_shift_id,$to_shift_id)
	{
		$ddone=$from_shift_id;
		$ddtwo=$to_shift_id;
		$processone=$this->MakeAutoSwapBySystem($from_shift_id,$to_shift_id);
		$processtwo=$this->MakeAutoSwapBySystem($ddtwo,$ddone);
		//echo "<pre>";
		//print_r($processone);
		//print_r($processtwo);
		//exit();
		
		$company_id=$processone[3];
		$NewStartDate=$processone[1];
		$NewEndDate=$processone[2];
		$LastShiftSwapDateStart=$processone[4];
		$LastShiftSwapDate=$processone[5];
		
		$company_id_two=$processtwo[3];
		$NewStartDate_two=$processtwo[1];
		$NewEndDate_two=$processtwo[2];
		$LastShiftSwapDateStart_two=$processtwo[4];
		$LastShiftSwapDate_two=$processtwo[5];
		
		foreach($processone[0] as $rowone):
			$this->ProcessToShiftIND($company_id,$to_shift_id,$from_shift_id,$NewStartDate,$NewEndDate,$rowone,$LastShiftSwapDateStart,$LastShiftSwapDate);
		endforeach;
		
		foreach($processtwo[0] as $rowtwo):
			$this->ProcessToShiftIND($company_id_two,$from_shift_id,$to_shift_id,$NewStartDate_two,$NewEndDate_two,$rowtwo,$LastShiftSwapDateStart_two,$LastShiftSwapDate_two);
		endforeach;
		
		return 1;
	}
	
	public function MakeAutoSwapBySystem($from_shift_id,$to_shift_id)
	{
		  
		  $chkLastShiftSwapDate=AssignEmployeeToShift::where('shift_id',$from_shift_id)
													 ->orderBy('start_date','DESC')
													 ->orderBy('end_date','DESC')
													 ->count();
		  if($chkLastShiftSwapDate!=0)
		  {
			  $sqlLastShiftSwapDate=AssignEmployeeToShift::where('shift_id',$from_shift_id)
														 ->select('start_date','end_date')
														 ->orderBy('start_date','DESC')
														 ->orderBy('end_date','DESC')
														 ->first();
														 
			  
			  //$DayName=$this->DayNameByDate($LastShiftSwapDate);
			  $day_date='';
			  $day_after_change=7;
			  $day_after_change_by_deductoneday=$day_after_change-1;
			  
			  /* last two date which is mention for specific shift */
			  $LastShiftSwapDate=$sqlLastShiftSwapDate->end_date;
			  if(empty($sqlLastShiftSwapDate->end_date))
			  {
				  $LastShiftSwapDate=$sqlLastShiftSwapDate->start_date;
			  }
			  
			  //echo "  || ";
			  $LastShiftSwapDateStart=$this->CreateDateByNumber("-",$day_after_change_by_deductoneday,$LastShiftSwapDate);
			  /* last two date which is mention for specific shift */
			  
			  //echo $LastShiftSwapDateStart." - ".$LastShiftSwapDate;
			  //exit();
			  
			  //$the_saturday_after_that = date('Y-m-d',strtotime("next saturday", strtotime($LastShiftSwapDate)));
			  //echo $the_saturday_after_that;
			  //exit();
			  
			  //echo $LastShiftSwapDate;
			  
			  //exit();
			  
			  
			  for($i=1; $i<=$day_after_change; $i++):
				$day_name = date('l', strtotime("+" . $i . " day", strtotime($LastShiftSwapDate)));
				if($day_name=="Saturday")
				{
					$day_date .= date('Y-m-d', strtotime("+" . $i . " day", strtotime($LastShiftSwapDate)));
				}
			  endfor;
			  
			  
			  
			  
			  //echo $LastShiftSwapDateStart;
			  //exit();
			  
			  /*new date creation for swap to future */
			  $startDay=$day_date;
			  //exit();
			  $NewStartDate=$startDay;
			  $NewEndDate=$this->CreateDateByNumber("+",$day_after_change_by_deductoneday,$NewStartDate);
			  /*new date creation for swap to future */
			  //echo $NewStartDate."||".$NewEndDate."-----";
			  
			  //echo "---------------------------<br>";
			  $dataimp = AssignEmployeeToShift::leftjoin('employee_infos', 'assign_employee_to_shifts.emp_code', '=', 'employee_infos.emp_code')
					->select(DB::raw('employee_infos.id as id,employee_infos.company_id,employee_infos.emp_code as emp_code,
					 employee_infos.first_name AS name'))
					->where('assign_employee_to_shifts.shift_id',$from_shift_id)
					->whereRaw("(assign_employee_to_shifts.start_date >= '$LastShiftSwapDateStart' AND assign_employee_to_shifts.end_date <= '$LastShiftSwapDate')")
					->orderBy('assign_employee_to_shifts.emp_code', 'DESC')
					->groupBy('assign_employee_to_shifts.emp_code')
					->first();
			  $global_company_id=$dataimp->company_id;	
			   		
			  $data = AssignEmployeeToShift::leftjoin('employee_infos', 'assign_employee_to_shifts.emp_code', '=', 'employee_infos.emp_code')
					->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
					 employee_infos.first_name AS name'))
					->where('assign_employee_to_shifts.shift_id',$from_shift_id)
					->whereRaw("(assign_employee_to_shifts.start_date >= '$LastShiftSwapDateStart' AND assign_employee_to_shifts.end_date <= '$LastShiftSwapDate')")
					->orderBy('assign_employee_to_shifts.emp_code', 'DESC')
					->groupBy('assign_employee_to_shifts.emp_code')
					->get();
			 //echo "<pre>";
			 //print_r($data);		
			 $newShiftEmparray=array();
			 foreach($data as $row):
				if(!empty($row->emp_code))
				{
					$emp_code=$row->emp_code;
					$empInfo=EmployeeInfo::where('emp_code',$emp_code)->first();
					$company_id=$empInfo->company_id;
					$chkAssignBeforeShift=DB::table('assign_employee_to_shifts')
										->where('assign_employee_to_shifts.emp_code',$emp_code)
										->where('assign_employee_to_shifts.shift_id',$to_shift_id)
										->whereRaw("(assign_employee_to_shifts.start_date >= '$NewStartDate' AND assign_employee_to_shifts.end_date <= '$NewEndDate')")
										->count();
					if($chkAssignBeforeShift==0)
					{
						
						
						array_push($newShiftEmparray,$emp_code);
						/*$shift = new AssignEmployeeToShift;
						$shift->company_id=$company_id;
						$shift->shift_id=$to_shift_id;
						$shift->start_date=$NewStartDate;
						$shift->end_date = $NewEndDate;
						$shift->emp_code = $emp_code;
						$shift->save();

						
						$swap = new ShiftEmployeeSwap();
						$swap->company_id = $company_id;
						$swap->fromshift_id = $from_shift_id;
						$swap->formstart_date = $LastShiftSwapDateStart;
						$swap->formend_date = $LastShiftSwapDate;

						$swap->toshift_id = $to_shift_id;
						$swap->tostart_date = $NewStartDate;
						$swap->toend_date = $NewEndDate;
						$swap->emp_code ='System_Robot';
						$swap->save();*/
						
						//echo "Thank You to Assigning Me in a Shift = ".$to_shift_id." - ".$row->emp_code." || ".$NewStartDate." TO ".$NewEndDate." <br>";
					}
					
				}
				
			 endforeach;
			 	
			  	
			  
			  return array($newShiftEmparray,$NewStartDate,$NewEndDate,$global_company_id,$LastShiftSwapDateStart,$LastShiftSwapDate);
			  
		  }
		  
		  
		
		 /*$data = DB::table('assign_employee_to_shifts')
                    ->leftjoin('employee_infos', 'assign_employee_to_shifts.emp_code', '=', 'employee_infos.emp_code')
                    ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
                     employee_infos.first_name AS name'))
                    ->where('assign_employee_to_shifts.shift_id',$shift_id)
                    ->where('assign_employee_to_shifts.start_date','<=', $start_date)
                    ->where('assign_employee_to_shifts.end_date','>=', $end_date)
					->whereNotNull('assign_employee_to_shifts.emp_code')
                    ->orderBy('assign_employee_to_shifts.emp_code', 'DESC')
                    ->groupBy('assign_employee_to_shifts.emp_code')
                    ->get();*/
	}
	
	
	
	
	
	public function ShiftSwapLoopIndex() {
        $RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
		
		$sqlAllShift=Shift::all();
		$WeekDays=$this->WeekDays();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.shiftSwapLoop', ['company' => $company, 
		'logged_emp_com' => $logged_emp_company_id,
		'shift' => $sqlAllShift,'WeekDays'=>$WeekDays]);
    }
	
	public function ShiftSwapLoopAdd(Request $request)
	{
		$this->validate($request, [
            'company_id' => 'required|unique:shift_swap_loops',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'swap_after_days' => 'required',
			'start_day_name' => 'required',
        ]);
		
		
		$tab=new ShiftSwapLoop;
		$tab->company_id=$request->company_id;
		$tab->shift_start=$request->shift_start;
		$tab->shift_end=$request->shift_end;
		$tab->swap_after_days=$request->swap_after_days;
		$tab->start_day_name=$request->start_day_name;
		$tab->save();
		
		return redirect()->action('ShiftEmployeeSwapController@ShiftSwapLoopIndex')->with('success', "Swap Loop Info Successfully Saved.");
		
	}
	
	public function ShiftSwapLoopEdit($id)
	{
		
		$tab=ShiftSwapLoop::find($id);
		$RoleAssignedCompany = app('App\Http\Controllers\MenuPageController')->AssignedCompany();
        $company = Company::whereIn('id',$RoleAssignedCompany)->get();
		$WeekDays=$this->WeekDays();
		$sqlAllShift=Shift::all();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        return view('module.settings.shiftSwapLoop', ['company' => $company, 
		'logged_emp_com' => $logged_emp_company_id,
		'shift' => $sqlAllShift,'data'=>$tab,'WeekDays'=>$WeekDays]);
		
	}
	
	public function ShiftSwapLoopUpdate(Request $request)
	{
		$this->validate($request, [
            'company_id' => 'required',
            'shift_start' => 'required',
            'shift_end' => 'required',
            'swap_after_days' => 'required',
            'start_day_name' => 'required',
        ]);
		
		
		$tab=ShiftSwapLoop::where('company_id',$request->company_id)->first();
		$tab->shift_start=$request->shift_start;
		$tab->shift_end=$request->shift_end;
		$tab->swap_after_days=$request->swap_after_days;
		$tab->start_day_name=$request->start_day_name;
		$tab->save();
		
		return redirect()->action('ShiftEmployeeSwapController@ShiftSwapLoopIndex')->with('success', "Swap Loop Info Successfully Updated.");
		
	}
	
	public function DayNameByDate($date)
	{

		$day_name = date('l', strtotime($date));
	
		return $day_name;
	}
	
	public function WeekDays()
	{
		$day_array=array();
		$date=date('Y-m-d');
		for($i=1; $i<=7; $i++):
			$day_name = date('l', strtotime("+" . $i . " day", strtotime($date)));
			array_push($day_array,$day_name);
		endfor;
		
		return $day_array;
	}
	
	public function CreateDateByNumber($aggregation,$increMentDecreMentDate,$defaultDate)
	{
		$date=$defaultDate;
		$day_name = date('Y-m-d', strtotime($aggregation." " . $increMentDecreMentDate . " day", strtotime($date)));

		return $day_name;
	}
	
	public function ShiftSwapLoopDelete(Request $request)
	{
		$this->validate($request, [
            'id' => 'required'
        ]);
		
		
		$tab=ShiftSwapLoop::find($request->id);
		$tab->delete();
		
		return 1;
		
	}
	
	public function ShiftSwapLoopJson() {
		
        $data=DB::table('shift_swap_loops')
				->leftjoin('companies','shift_swap_loops.company_id','=','companies.id')
				->select(DB::Raw('shift_swap_loops.id,
				companies.name as company_name,
				shift_swap_loops.shift_start as shift_start_id,
				shift_swap_loops.shift_end as shift_end_id,
				(select shifts.name from shifts where shifts.id=shift_swap_loops.shift_start) as shift_start,
				(select shifts.name from shifts where shifts.id=shift_swap_loops.shift_end) as shift_end,
				shift_swap_loops.swap_after_days,shift_swap_loops.start_day_name,shift_swap_loops.created_at'))
				->get();
        return response()->json(array("data" => $data, "total" => count($data)));
		
    }

	

    public function filterEmployee(Request $request) {

        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        $company_id = $request->company_id;

		if(!empty($company_id))
		{
			
        $shift_id = $request->shift_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;



            $data = DB::table('assign_employee_to_shifts')
                    ->leftjoin('employee_infos', 'assign_employee_to_shifts.emp_code', '=', 'employee_infos.emp_code')
                    ->select(DB::raw('employee_infos.id as id,employee_infos.emp_code as emp_code,
                     employee_infos.first_name AS name'))
                    ->where('employee_infos.company_id',$company_id)
					->where('assign_employee_to_shifts.shift_id',$shift_id)
                    ->where('assign_employee_to_shifts.start_date','<=', $start_date)
                    ->where('assign_employee_to_shifts.end_date','>=', $end_date)
                    ->orderBy('employee_infos.emp_code', 'ASC')
                    ->groupBy('employee_infos.emp_code')
                    ->get();
        
			return response()->json($data);
		}
		else
		{
			return 0;
		}
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
//            'company_id' => 'required',
//            'shift_id' => 'required',
//            'start_date' => 'required',
//            'end_date' => 'required',
        ]);
//        ->Where('toshift_id', $request->swapshift_id)->Where('tostart_date', $request->start_date)->Where('toend_date', $request->end_date)
        if (empty($request->shiftassign)) {
            return redirect()->action('ShiftEmployeeSwapController@index')->with('error', "Please Select Employee ");
        }
        $count_col = 0;
        foreach ($request->shiftassign as $key => $file):


            $chkExShift = AssignEmployeeToShift::where('emp_code', $request->shiftassign[$key])
                    ->where('shift_id', $request->swapshift_id)
                    ->where('start_date', '<=', $request->start_date)
                    ->where('end_date', '>=', $request->end_date)
                    ->first();

//              echo "<pre>";
//               print_r($chkExShift);
            //   exit();
//            $count_col = count($chkExShift);
            //if (count($chkExShift) == 0) {
//                $shift = AssignEmployeeToShift::find($chkExShift->id);
                $shift = new AssignEmployeeToShift();
                $shift->company_id = $request->company_id;

                $shift->shift_id = $request->swapshift_id;
                $shift->start_date = $request->start_date;
                $shift->end_date = $request->end_date;
                $shift->emp_code = $request->shiftassign[$key];
                $shift->save();
                //if ($shift->save() == 1) {
                $swap = new ShiftEmployeeSwap();
                $swap->company_id = $request->company_id;

                $swap->fromshift_id = $request->shift_id;
                $swap->formstart_date = $request->fstart_date;
                $swap->formend_date = $request->fend_date;

                $swap->toshift_id = $request->swapshift_id;
                $swap->tostart_date = $request->start_date;
                $swap->toend_date = $request->end_date;

                $swap->emp_code = $request->shiftassign[$key];
                $swap->save();
                //  }
                // return redirect()->action('ShiftEmployeeSwapController@index')->with('success', "Information save Successfully");
            //} else {
                //echo 'else';
                //$status = 1;
//                return redirect()->action('ShiftEmployeeSwapController@index')->with('error', "Please Check Some Employee is Already Exist in a Shift between $chkExShift->start_date to $chkExShift->end_date")->with('emp', $chkExShift->emp_code);
           // }

        endforeach;
		$status = 0;
        if ($status == 1) {
            $e_code = '';
            $ss = 1;

            foreach ($request->shiftassign as $key => $file) {
                $chkExShift1 = AssignEmployeeToShift::where('emp_code', $request->shiftassign[$key])
                        ->where('shift_id', $request->swapshift_id)
                        ->where('start_date', '<=', $request->start_date)
                        ->where('end_date', '>=', $request->end_date)
                        ->first();

                 $count_col = count($chkExShift1->emp_code);
                if ($ss != $count_col) {
                    $e_code .=$chkExShift1->emp_code;
                    $e_code .=',';
                } else {
                    $e_code .=$chkExShift1->emp_code;
                }
                $ss++;
            }
//            echo $e_code;
             return redirect()->action('ShiftEmployeeSwapController@index')->with('error', "Please Check Some Employee is Already Exist in a Shift between $chkExShift->start_date to $chkExShift->end_date")->with('emp', $e_code);
        }

        return redirect()->action('ShiftEmployeeSwapController@index')->with('success', "Information save Successfully");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ShiftEmployeeSwap  $shiftEmployeeSwap
     * @return \Illuminate\Http\Response
     */
    public function showList() {

        return view('module.settings.shiftSwaplist');
    }

    public function show(ShiftEmployeeSwap $shiftEmployeeSwap) {
        $company = Company::all();
//        $data = DB::table('shift_employee_swaps')
//                ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
//                ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
//                ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
//                ->select(DB::raw('shift_employee_swaps.*,
//                    concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
//                    companies.name as company_name,
//                    shift_employee_swaps.emp_code as emp_code,
//                    shifts.name as shift_name
//          '))
//                ->groupBy('shift_employee_swaps.id')
//                ->orderBy('shift_employee_swaps.id', 'DESC')
//                ->get();
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (empty($logged_emp_company_id) || $logged_emp_company_id == '' || $logged_emp_company_id == "Undefined" || $logged_emp_company_id == null) {
            $logged_emp_company_id = 0;
        }

        if ($logged_emp_company_id > 0) {

            $data = DB::table('shift_employee_swaps')
                    ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
                    ->select(DB::raw('shift_employee_swaps.*,
                    concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
                    companies.name as company_name,
                    shift_employee_swaps.emp_code as emp_code,
                    shifts.name as shift_name
          '))
                    ->where('attendance_policies.company_id', $logged_emp_company_id)
                    ->groupBy('shift_employee_swaps.id')
                    ->orderBy('shift_employee_swaps.id', 'DESC')
                    ->get();
        } else {

            $data = DB::table('shift_employee_swaps')
                    ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
                    ->select(DB::raw('shift_employee_swaps.*,
                    concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
                    companies.name as company_name,
                    shift_employee_swaps.emp_code as emp_code,
                    shifts.name as shift_name
          '))
                    ->groupBy('shift_employee_swaps.id')
                    ->orderBy('shift_employee_swaps.id', 'DESC')
                    ->get();
        }

        return response()->json(array("data" => $data, "total" => count($data)));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ShiftEmployeeSwap  $shiftEmployeeSwap
     * @return \Illuminate\Http\Response
     */
    public function edit(ShiftEmployeeSwap $shiftEmployeeSwap) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ShiftEmployeeSwap  $shiftEmployeeSwap
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShiftEmployeeSwap $shiftEmployeeSwap) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ShiftEmployeeSwap  $shiftEmployeeSwap
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShiftEmployeeSwap $shiftEmployeeSwap) {
        //
    }

    public function exportExcel() {
//        $data = DB::table('shift_employee_swaps')
//                ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
//                ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
//                ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
//                ->select(DB::raw('
//                    concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
//                    shift_employee_swaps.emp_code as emp_code,
//                    companies.name as company_name,
//                    shifts.name as shift_name,
//                    shift_employee_swaps.tostart_date,shift_employee_swaps.toend_date
//          '))
//                ->groupBy('shift_employee_swaps.id')
//                ->orderBy('shift_employee_swaps.id', 'DESC')
//                ->get();
        // print_r($data);
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');

        if (empty($logged_emp_company_id) || $logged_emp_company_id == '' || $logged_emp_company_id == "Undefined" || $logged_emp_company_id == null) {
            $logged_emp_company_id = 0;
        }

        if ($logged_emp_company_id > 0) {

            $data = DB::table('shift_employee_swaps')
                    ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
                    ->select(DB::raw('
                    concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
                    shift_employee_swaps.emp_code as emp_code,
                    companies.name as company_name,
                    shifts.name as shift_name,
                    shift_employee_swaps.tostart_date,shift_employee_swaps.toend_date
          '))
                    ->where('attendance_policies.company_id', $logged_emp_company_id)
                    ->groupBy('shift_employee_swaps.id')
                    ->orderBy('shift_employee_swaps.id', 'DESC')
                    ->get();
        } else {

            $data = DB::table('shift_employee_swaps')
                    ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
                    ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
                    ->select(DB::raw('
                    concat(employee_infos.first_name," ",employee_infos.last_name) as emp_name,
                    shift_employee_swaps.emp_code as emp_code,
                    companies.name as company_name,
                    shifts.name as shift_name,
                    shift_employee_swaps.tostart_date,shift_employee_swaps.toend_date
          '))
                    ->groupBy('shift_employee_swaps.id')
                    ->orderBy('shift_employee_swaps.id', 'DESC')
                    ->get();
        }
        $excelArray = [];

        // Define the Excel spreadsheet headers
        $excelArray [] = [

            'Employee Name',
            'Empolyee code',
            'Company Name',
            'Shift Name',
            'Start_date',
            'End_date'
        ];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($data as $key => $field) {
            $excelArray[] = get_object_vars($field);
        }

        // Generate and return the spreadsheet
        \Excel::create('Employee Shift Swap List_' . date('d_m_Y_H_i_s'), function($excel) use ($excelArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Employee Shift Swap Info');
            $excel->setCreator('HRMS Lv2')->setCompany('Systech Unimax Ltd.');
            $excel->setDescription('Employee Shift Swap Info');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($excelArray) {
                $sheet->fromArray($excelArray, null, 'A1', false, false);
            });
        })->download('xlsx');
    }

    public function exportPdf() {

        $content = '<h3>Employee Shift Swap List</h3>';
        $content .='<h5>Genarated : ' . date('d/m/Y H:i:s') . '</h5>';
        // instantiate and use the dompdf class
        $excelArray = [
            'Employee_name',
            'Employee_code',
            'Company_name',
            'Shift_name',
            'Start_date',
            'End_date'
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

//            $data = DB::table('shift_employee_swaps')
//                    ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
//                    ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
//                    ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
//                    ->select(DB::raw('
//                    concat(employee_infos.first_name," ",employee_infos.last_name) as Employee_name,
//                    shift_employee_swaps.emp_code as Employee_code,
//                    companies.name as Company_name,
//                    shifts.name as Shift_name,
//                    shift_employee_swaps.tostart_date as Start_date,
//                    shift_employee_swaps.toend_date  as End_date
//          '))
//                    ->groupBy('shift_employee_swaps.id')
//                    ->orderBy('shift_employee_swaps.id', 'DESC')
//                    ->get();

            $logged_emp_company_id = MenuPageController::loggedUser('company_id');

            if (empty($logged_emp_company_id) || $logged_emp_company_id == '' || $logged_emp_company_id == "Undefined" || $logged_emp_company_id == null) {
                $logged_emp_company_id = 0;
            }

            if ($logged_emp_company_id > 0) {

                $data = DB::table('shift_employee_swaps')
                        ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
                        ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
                        ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
                        ->select(DB::raw('
                                        concat(employee_infos.first_name," ",employee_infos.last_name) as Employee_name,
                                        shift_employee_swaps.emp_code as Employee_code,
                                        companies.name as Company_name,
                                        shifts.name as Shift_name,
                                        shift_employee_swaps.tostart_date as Start_date,
                                        shift_employee_swaps.toend_date  as End_date
                        '))
                        ->where('attendance_policies.company_id', $logged_emp_company_id)
                        ->groupBy('shift_employee_swaps.id')
                        ->orderBy('shift_employee_swaps.id', 'DESC')
                        ->get();
            } else {

                $data = DB::table('shift_employee_swaps')
                        ->leftjoin('companies', 'companies.id', '=', 'shift_employee_swaps.company_id')
                        ->leftjoin('shifts', 'shifts.id', '=', 'shift_employee_swaps.toshift_id')
                        ->leftjoin('employee_infos', 'employee_infos.emp_code', '=', 'shift_employee_swaps.emp_code')
                        ->select(DB::raw('
                    concat(employee_infos.first_name," ",employee_infos.last_name) as Employee_name,
                    shift_employee_swaps.emp_code as Employee_code,
                    companies.name as Company_name,
                    shifts.name as Shift_name,
                    shift_employee_swaps.tostart_date as Start_date,
                    shift_employee_swaps.toend_date  as End_date
                        '))
                        ->groupBy('shift_employee_swaps.id')
                        ->orderBy('shift_employee_swaps.id', 'DESC')
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
