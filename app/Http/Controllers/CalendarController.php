<?php

namespace App\Http\Controllers;

use App\Calendar;
use App\Company;
use App\CompanyWorkWeekendDay;
use App\DayType;
use App\Month;
use App\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $dataCompany = Company::all();
        $dataDayType = DayType::where('company_id', $logged_emp_company_id)->get();


        return view("module/settings/calendar", ['company' => $dataCompany, 'daytype' => $dataDayType, 'logged_emp_com' => $logged_emp_company_id]);
    }

    public function showCalendar() {

        $json = DB::table('calendars')
                ->join('day_types', 'day_types.id', '=', 'calendars.day_type_id')
                ->select('calendars.*', 'day_types.title')
                ->get();

        //$json=Calendar::all();
        return response()->json(array("data" => $json, "total" => count($json)));
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
            'company_id' => 'required',
            'saturday' => 'required',
            'sunday' => 'required',
            'monday' => 'required',
            'tuesday' => 'required',
            'wednesday' => 'required',
            'thursday' => 'required',
            'friday' => 'required',
            'year' => 'required',
        ]);


        $chkCompanyWorkWeekendDay = CompanyWorkWeekendDay::where('company_id', $request->company_id)
                ->count();

        if ($chkCompanyWorkWeekendDay == 0) {

            $tab = new CompanyWorkWeekendDay;
            $tab->company_id = $request->company_id;
            $tab->saturday = $request->saturday;
            $tab->sunday = $request->sunday;
            $tab->monday = $request->monday;
            $tab->tuesday = $request->tuesday;
            $tab->wednesday = $request->wednesday;
            $tab->thursday = $request->thursday;
            $tab->friday = $request->friday;
            $tab->save();
        }


        $chktaby = Year::where('year', $request->year)
                ->where('company_id', $request->company_id)
                ->count();

        if ($chktaby == 0) {
            $taby = new Year;
            $taby->company_id = $request->company_id;
            $taby->year = $request->year;
            $taby->save();
        }


        $year = $request->year;

        for ($i = 1; $i <= 12; $i++) {
            $date = $year . '-' . $this->checkMonthID($i) . '-01';
            $end = $year . '-' . $this->checkMonthID($i) . '-' . date('t', strtotime($date));

            $monthNum = $i;
            $dateObj = \DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj->format('F'); // March
            //echo $date.'-------'.$end;

            $monthdays = date('t', strtotime($date));

            $chkmonth = Month::where('company_id', $request->company_id)
                    ->where('year', $year)
                    ->where('name', $monthName)
                    ->count();

            if (isset($chkmonth)) {
                if ($chkmonth == 0) {
                    $tabm = new Month;
                    $tabm->company_id = $request->company_id;
                    $tabm->year = $request->year;
                    $tabm->month_number = $i;
                    $tabm->name = $monthName;
                    $tabm->save();
                }
            }

            for ($di = 0; $di <= $monthdays - 1; $di++) {
                //$day_num = date('Y-m-d', strtotime("+".$di." day", strtotime($date)));
                $day_name = date('l', strtotime("+" . $di . " day", strtotime($date)));
                $daten = date("Y-m-d", strtotime("+" . $di . " day", strtotime($date)));

                //$daten . "-" . $monthName . "-" . strtolower($day_name);

                $day_type_id = 0;
                if (strtolower($day_name) == "saturday") {
                    $day_type_id = $request->saturday;
                } elseif (strtolower($day_name) == "sunday") {
                    $day_type_id = $request->sunday;
                } elseif (strtolower($day_name) == "monday") {
                    $day_type_id = $request->monday;
                } elseif (strtolower($day_name) == "tuesday") {
                    $day_type_id = $request->tuesday;
                } elseif (strtolower($day_name) == "wednesday") {
                    $day_type_id = $request->wednesday;
                } elseif (strtolower($day_name) == "thursday") {
                    $day_type_id = $request->thursday;
                } elseif (strtolower($day_name) == "friday") {
                    $day_type_id = $request->friday;
                }


                $chkdaydetail = Calendar::where('company_id', $request->company_id)
                        ->where('year', $year)
                        ->where('date', $daten)
                        ->count();

                if ($chkdaydetail == 0) {

                    $calender = new Calendar();
                    $calender->date = $daten;
                    $calender->year = $year;
                    $calender->day_title = $day_name;
                    $calender->day_note = "";
                    $calender->month_number = $i;
                    $calender->day_type_id = $day_type_id;
                    $calender->company_id = $request->company_id;
                    ;
                    $calender->is_active = 'Active';
                    $calender->save();
                }
            }
        }



        return redirect()->action('CalendarController@index')->with('success', 'Information Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    private function checkMonthID($mm) {
        if (strlen($mm) == 1) {
            $newmonth = "0" . $mm;
        } elseif (strlen($mm) == 2) {
            $newmonth = $mm;
        } else {
            $newmonth = "00";
        }

        return $newmonth;
    }

    public function show() {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $json = DB::table('calendars')
                ->join('day_types', 'day_types.id', '=', 'calendars.day_type_id')
                ->select('calendars.*', 'day_types.title')
                ->where('calendars.company_id', $logged_emp_company_id)
                ->get();

        //$json=Calendar::all();
        return response()->json(array("data" => $json, "total" => count($json)));
    }

    public function checkIndex() {
        $dataCompany = Company::all();
        return view("module/settings/checkcalendar", ['company' => $dataCompany]);
    }

    public function getYear(Request $request) {
        $company_id = $request->company_id;

        $dataYear = Year::where('company_id', $company_id)->get();
        return response()->json($dataYear);
    }

    public function getMonth(Request $request) {
        $company_id = $request->company_id;
        $year = $request->year;

        $dataMonth = Month::where('company_id', $company_id)
                ->where('year', $year)
                ->get();
        return response()->json($dataMonth);
    }

    public function calendarAlocate(Request $request) {

        $company_id = $request->company_id;
        ;
        $year = $request->year;
        $month = $request->month;

        if ($month == 'all') {

            $data = DB::table('calendars')
                    ->join('day_types', 'day_types.id', '=', 'calendars.day_type_id')
                    ->where('calendars.company_id', $company_id)
                    ->where('calendars.year', $year)
                    ->select('calendars.*', 'day_types.title')
                    ->get();
        } else {
            $data = DB::table('calendars')
                    ->join('day_types', 'day_types.id', '=', 'calendars.day_type_id')
                    ->where('calendars.company_id', $company_id)
                    ->where('calendars.year', $year)
                    ->where('calendars.month_number', $month)
                    ->select('calendars.*', 'day_types.title')
                    ->get();
        }

        $dataCompany = Company::all();

        return view("module/settings/checkcalendar", ['data' => $data, 'company' => $dataCompany]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function edit(Calendar $calendar, $id) {
        $logged_emp_company_id = MenuPageController::loggedUser('company_id');
        $dataCompany = Company::all();
        $dataDayType = DayType::where('company_id', $logged_emp_company_id)->get();

        $json = DB::table('calendars')
                ->join('day_types', 'day_types.id', '=', 'calendars.day_type_id')
                ->select('calendars.*', 'day_types.title')
                ->where('calendars.id', $id)
                ->take(1)
                ->get();

        return view("module/settings/calendar", ['data' => $json, 'company' => $dataCompany, 'daytype' => $dataDayType, 'logged_emp_com' => $logged_emp_company_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Calendar $calendar, $id) {
        $this->validate($request, [
            'company_id' => 'required',
            'day_type_id' => 'required',
        ]);

        $tab = Calendar::find($id);
        $tab->company_id = $request->company_id;
        $tab->day_type_id = $request->day_type_id;
        $tab->save();

        return redirect()->action('CalendarController@index')->with('success', 'Information Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Calendar $calendar) {
        //
    }

}
