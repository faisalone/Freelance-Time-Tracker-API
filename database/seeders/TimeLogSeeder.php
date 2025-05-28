<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();

        if ($projects->count() >= 2) {
            $firstProject = $projects->first();
            $secondProject = $projects->skip(1)->first();

            // Create time logs for the last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                
                // Morning session for first project
                $startTime = $date->copy()->setTime(9, 0);
                $endTime = $date->copy()->setTime(12, 30);
                $hours = $endTime->diffInMinutes($startTime) / 60;

                TimeLog::create([
                    'project_id' => $firstProject->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'description' => 'Frontend development - implementing user authentication and dashboard components',
                    'hours' => $hours,
                    'is_billable' => true,
                    'tags' => ['development', 'frontend', 'react'],
                ]);

                // Afternoon session for second project
                $startTime = $date->copy()->setTime(14, 0);
                $endTime = $date->copy()->setTime(17, 0);
                $hours = $endTime->diffInMinutes($startTime) / 60;

                TimeLog::create([
                    'project_id' => $secondProject->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'description' => 'SEO analysis and keyword research for target pages',
                    'hours' => $hours,
                    'is_billable' => true,
                    'tags' => ['seo', 'research', 'analysis'],
                ]);
            }

            // Add some non-billable time logs
            TimeLog::create([
                'project_id' => $firstProject->id,
                'start_time' => Carbon::now()->subDays(2)->setTime(18, 0),
                'end_time' => Carbon::now()->subDays(2)->setTime(19, 30),
                'description' => 'Code review and documentation',
                'hours' => 1.5,
                'is_billable' => false,
                'tags' => ['code-review', 'documentation'],
            ]);

            // Add a currently running time log (no end_time)
            TimeLog::create([
                'project_id' => $firstProject->id,
                'start_time' => Carbon::now()->subHours(2),
                'end_time' => null,
                'description' => 'Working on API integration',
                'hours' => null,
                'is_billable' => true,
                'tags' => ['api', 'integration'],
            ]);
        }

        // Create additional time logs using factory
        TimeLog::factory(15)->create();
    }
}
