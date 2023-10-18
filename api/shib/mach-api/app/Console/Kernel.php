<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call(function() {
        //     $forms = DB::table('forms')->where('no_login',1)->get();
        //     foreach($forms as $form) {
        //         $submissions = DB::table('submissions')->where('form_id',$form->id)->where('owner_id',131)->where('owner_type','App\\Models\\Group')->where('confirmed',0)->where('created_at', '<=', now()->addMinutes(-90))->delete();
        //     }
        // })->daily();

        $schedule->call('App\Http\Controllers\api\ArchiveController@crawler')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
