<?php

namespace App\Actions\TimeLog;

use App\Models\TimeLog;

class StopTimeLogAction
{
    public function execute(TimeLog $timeLog): TimeLog
    {
        if (!$timeLog->isRunning()) {
            throw new \Exception('Time log is not currently running.');
        }

        $timeLog->stop();
        
        return $timeLog;
    }
}
