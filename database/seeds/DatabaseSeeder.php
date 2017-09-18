<?php

use Illuminate\Database\Seeder;
use App\CalenderWeekDay;
use App\Gender;
use App\EmployeeInfo;
use Carbon\Carbon;


class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        
        $this->GrabUserInfoFromEmployee();
        
        // $this->call(UsersTableSeeder::class);
        //$this->GenarateDefaultUser();
        
        //$this->Weekday();
        
        //$this->Gender();
        
        
    }
    
    private function GrabUserInfoFromEmployee()
    {
        $countEmpInfo=EmployeeInfo::count();
        if($countEmpInfo!=0)
        {
            DB::table('users')->delete();
            $EmpInfo=EmployeeInfo::all();
            foreach ($EmpInfo as $emp):
                $chkUser=DB::table('users')->where('username',$emp->emp_code)->count();
                if($chkUser==0)
                {
                    if(empty($emp->email))
                    {
                        $email="noemail@systechunimax.com";
                    }
                    else
                    {
                        $email=$emp->email;
                    }
                    
                    
                    
                    DB::table('users')->insert([
                        'name' => $emp->first_name,
                        'username' => $emp->emp_code,
                        'email' => $email,
                        'password' => bcrypt('123456')
                    ]);
                    
                    $getUserID=DB::table('users')->where('username',$emp->emp_code)->first();
                    $userID=$getUserID->id;
                    
                    DB::table('employee_infos')->where('emp_code',$emp->emp_code)->update(['user_id' => $userID]);
                }
            endforeach;
        }
        
    }
    
    private function GenarateDefaultUser()
    {
        for ($i = 1; $i <= 100; $i++) {
            DB::table('users')->insert([
                'name' => str_random(10),
                'email' => str_random(10) . '@gmail.com',
                'password' => bcrypt('123456')
            ]);
        }
    }
    
    private function Weekday()
    {
        $timestamp = strtotime('next Friday');
        $days = array();
        for ($i = 0; $i < 7; $i++) {
            $days[] = strftime('%A', $timestamp);
            $timestamp = strtotime('+1 day', $timestamp);

            $day_name = date("l", $timestamp) . "-";
            $day_short_code = date("D", $timestamp) . "<br>";
            $chkDay = CalenderWeekDay::where('name', $day_name)
                    ->where('day_short_code', $day_short_code)
                    ->count();
            if ($chkDay == 0) {
                $tab = new CalenderWeekDay();
                $tab->name = $day_name;
                $tab->day_short_code = $day_short_code;
                $tab->save();
            }
        }
        
        return "Week Day Genarated Successfully";
    }

    private function Gender() {
        if (Gender::where('name', 'Male')->count() == 0) {
            $tab = new Gender();
            $tab->name = "Male";
            $tab->save();
        }

        if (Gender::where('name', 'Female')->count() == 0) {
            $tab = new Gender();
            $tab->name = "Female";
            $tab->save();
        }

        if (Gender::where('name', 'Other')->count() == 0) {
            $tab = new Gender();
            $tab->name = "Other";
            $tab->save();
        }
        
        return "Gender Info Genarated Successfully";
    }

}
