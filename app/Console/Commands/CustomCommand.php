<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\CronLogs;
use App\AttendanceJobcard;
use Log;
class CustomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command Command Description';

    /**
     * Create a new command instance.
     * 
     * @return void
     */

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     */
    public function handle()
    {
        $process=app('App\Http\Controllers\AttendanceJobcardController')->create();
        if($process==1)
        {
            $tab=new CronLogs;
            $tab->name="Attendance Log";
            $tab->save();

            \Log::info('Log Process Successfully.');       
        }
        else
        {
            $tab=new CronLogs;
            $tab->name="Attendance Log Failed To Process";
            $tab->save();
        }

         
    }
}
