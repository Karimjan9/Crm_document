<?php

namespace App\Console;

use App\Models\User;
use App\Models\StatementModel;
use App\Models\UserOnCriteria;
use App\Models\AnnouncementModel;
use Illuminate\Support\Facades\Log;
use App\Models\ArchiveCriteriaModel;
use App\Models\ArchiveStatementModel;
use App\Models\ArchiveAnnouncementModel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        Log::info('fix: {id}', ['id' => date("Y-m-d 00:00:00", strtotime("last day of -1 month"))]);

        // dd('asd');

        if (count(UserOnCriteria::all()) > 0) {
            foreach (UserOnCriteria::all() as $key) {
                ArchiveCriteriaModel::create([
                    'user_id' => $key['user_id'],
                    'data' => $key['data'],
                    'increase' => $key['increase'],
                    'criteria_id' => $key['criteria_id'],
                    'status' => $key['status'],
                    'states' => $key['states'],
                    'created_at' => date("Y-m-d 00:00:00", strtotime("last day of -1 month")),
                    'updated_at' => date("Y-m-d 00:00:00", strtotime("last day of -1 month")),
                ]);
            }

            

            UserOnCriteria::truncate();

            User::query()->update([
                "percent" => 0
            ]);
        }

        if (count(AnnouncementModel::all()) > 0) {
            foreach (AnnouncementModel::all() as $key) {
                ArchiveAnnouncementModel::create([
                    'unfulfilled' => $key['unfulfilled'],
                    'time' => $key['time'],
                    'date' => $key['date'],
                    'theme' => $key['theme'],
                    'pair' => $key['pair'],
                    'group' => $key['group'],
                    'group_name' => $key['group_name'],
                    'subject' => $key['subject'],
                    'location' => $key['location'],
                    'user_id' => $key['user_id'],
                    'status' => $key['status'],
                    'description' => $key['description'],
                    'created_at' => date("Y-m-d 00:00:00", strtotime("last day of -1 month")),
                    'updated_at' => date("Y-m-d 00:00:00", strtotime("last day of -1 month")),
                ]);
            }

            

            AnnouncementModel::truncate();

           
        }

        if (count(StatementModel::all()) > 0) {
            foreach (StatementModel::all() as $key) {
                ArchiveStatementModel::create([
                    'unfulfilled' => $key['unfulfilled'],
                    'date' => $key['date'],
                    'theme' => $key['theme'],
                    'pair' => $key['pair'],
                    'group' => $key['group'],
                    'group_name' => $key['group_name'],
                    'subject' => $key['subject'],
                    'location' => $key['location'],
                    'user_id' => $key['user_id'],
                    'status' => $key['status'],
                    'description' => $key['description'],
                    'created_at' => date("Y-m-d 00:00:00", strtotime("last day of -1 month")),
                    'updated_at' => date("Y-m-d 00:00:00", strtotime("last day of -1 month")),
                ]);
            }

            

            StatementModel::truncate();

           
        }

        $schedule->call(function () {
            Log::info('fix: {id}', ['id' => date('Y-m-d-i-m')]);
        })->everyMinute();
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
