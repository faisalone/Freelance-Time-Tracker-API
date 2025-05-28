<?php

namespace App\Listeners;

use App\Events\TimeLogUpdated;
use App\Models\TimeLog;
use App\Notifications\WorkdayExceededNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CheckWorkdayHours
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TimeLogUpdated $event): void
    {
        $timeLog = $event->timeLog;
        $user = $timeLog->project->client->user;
        $today = Carbon::today()->toDateString();
        
        // Calculate total hours for today
        $totalHours = TimeLog::whereHas('project.client', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereDate('start_time', $today)
            ->whereNotNull('hours')
            ->sum('hours');

        // Check if we've already sent a notification today for this user
        $cacheKey = "workday_notification_{$user->id}_{$today}";
        
        if ($totalHours >= 8.0 && !Cache::has($cacheKey)) {
            // Get today's time logs for the email
            $todayLogs = TimeLog::with('project')
                ->whereHas('project.client', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereDate('start_time', $today)
                ->whereNotNull('hours')
                ->get()
                ->map(function ($log) {
                    return [
                        'project' => $log->project->name,
                        'hours' => $log->hours,
                        'description' => $log->description,
                    ];
                })
                ->toArray();

            // Send notification
            $user->notify(new WorkdayExceededNotification(
                $totalHours,
                Carbon::today()->format('F j, Y'),
                $todayLogs
            ));

            // Cache to prevent multiple notifications for the same day
            Cache::put($cacheKey, true, Carbon::tomorrow());
        }
    }
}
