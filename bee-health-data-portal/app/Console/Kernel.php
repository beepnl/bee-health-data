<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Classes\Notificator;
use App\Models\Notification as ModelNotification;

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
        $schedule->call(function () {
            $notifications = ModelNotification::frequency(ModelNotification::WEEKLY)->get();
            foreach($notifications as $notification){
                $notificator = new Notificator($notification);
                $notificator->notify();
            }
        })->weeklyOn(
            env('NOTIFICATIONS_WEEKLY_ON', '1'), 
            env('NOTIFICATIONS_WEEKLY_AT', '1:00')
        );
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
