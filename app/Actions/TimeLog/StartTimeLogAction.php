<?php

namespace App\Actions\TimeLog;

use App\Models\TimeLog;
use App\Models\Project;

class StartTimeLogAction
{
    public function execute(Project $project, ?string $description = null): TimeLog
    {
        // Stop any running time logs for the user first
        $runningLogs = TimeLog::whereHas('project.client', function ($query) use ($project) {
            $query->where('user_id', $project->client->user_id);
        })->running()->get();

        foreach ($runningLogs as $runningLog) {
            $runningLog->stop();
        }

        return TimeLog::create([
            'project_id' => $project->id,
            'start_time' => now(),
            'description' => $description,
            'is_billable' => true,
        ]);
    }
}
