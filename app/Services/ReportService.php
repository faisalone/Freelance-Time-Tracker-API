<?php

namespace App\Services;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ReportService
{
    public function getTotalHoursByProject(User $user, ?string $from = null, ?string $to = null): Collection
    {
        $query = $user->timeLogs()->with('project.client');

        if ($from && $to) {
            $query->dateRange($from, $to);
        }

        return $query->get()
            ->groupBy('project_id')
            ->map(function ($logs, $projectId) {
                $project = $logs->first()->project;
                return [
                    'project_id' => $projectId,
                    'project_name' => $project->name,
                    'client_name' => $project->client->name,
                    'total_hours' => $logs->sum('hours'),
                    'total_earnings' => $logs->sum(function ($log) {
                        return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
                    }),
                ];
            })->values();
    }

    public function getTotalHoursByClient(User $user, ?string $from = null, ?string $to = null): Collection
    {
        $query = $user->timeLogs()->with('project.client');

        if ($from && $to) {
            $query->dateRange($from, $to);
        }

        return $query->get()
            ->groupBy('project.client.id')
            ->map(function ($logs, $clientId) {
                $client = $logs->first()->project->client;
                return [
                    'client_id' => $clientId,
                    'client_name' => $client->name,
                    'total_hours' => $logs->sum('hours'),
                    'total_earnings' => $logs->sum(function ($log) {
                        return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
                    }),
                    'projects_count' => $logs->groupBy('project_id')->count(),
                ];
            })->values();
    }

    public function getDailyHours(User $user, ?string $from = null, ?string $to = null): Collection
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to) : Carbon::now();

        $query = $user->timeLogs()->with('project.client');
        $query->dateRange($from->startOfDay(), $to->endOfDay());

        return $query->get()
            ->groupBy(function ($log) {
                return $log->start_time->format('Y-m-d');
            })
            ->map(function ($logs, $date) {
                return [
                    'date' => $date,
                    'total_hours' => $logs->sum('hours'),
                    'total_earnings' => $logs->sum(function ($log) {
                        return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
                    }),
                    'logs_count' => $logs->count(),
                ];
            })->values();
    }

    public function getWeeklyHours(User $user, ?string $from = null, ?string $to = null): Collection
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subWeeks(12);
        $to = $to ? Carbon::parse($to) : Carbon::now();

        $query = $user->timeLogs()->with('project.client');
        $query->dateRange($from->startOfWeek(), $to->endOfWeek());

        return $query->get()
            ->groupBy(function ($log) {
                return $log->start_time->format('Y-W');
            })
            ->map(function ($logs, $week) {
                $firstLog = $logs->first();
                return [
                    'week' => $week,
                    'week_start' => $firstLog->start_time->startOfWeek()->format('Y-m-d'),
                    'week_end' => $firstLog->start_time->endOfWeek()->format('Y-m-d'),
                    'total_hours' => $logs->sum('hours'),
                    'total_earnings' => $logs->sum(function ($log) {
                        return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
                    }),
                    'logs_count' => $logs->count(),
                ];
            })->values();
    }

    public function generateTimeReportPDF(User $user, array $filters = []): string
    {
        $groupBy = $filters['group_by'] ?? 'daily';
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $filters['end_date'] ?? Carbon::now()->format('Y-m-d');
        $clientId = $filters['client_id'] ?? null;
        $projectId = $filters['project_id'] ?? null;

        // Build query
        $query = $user->timeLogs()->with('project.client')->completed();
        
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }
        
        if ($clientId) {
            $query->whereHas('project.client', function ($q) use ($clientId) {
                $q->where('id', $clientId);
            });
        }
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $timeLogs = $query->orderBy('start_time', 'desc')->get();

        // Calculate summary data
        $totalHours = $timeLogs->sum('hours');
        $totalEarnings = $timeLogs->sum(function ($log) {
            return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
        });
        $averageHourlyRate = $totalHours > 0 ? $totalEarnings / $totalHours : 0;

        // Get related models for report info
        $client = $clientId ? Client::find($clientId) : null;
        $project = $projectId ? Project::find($projectId) : null;

        // Generate report title
        $reportTitle = $this->generateReportTitle($groupBy, $startDate, $endDate, $client, $project);

        // Prepare data for view
        $data = [
            'reportTitle' => $reportTitle,
            'groupBy' => $groupBy,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'client' => $client,
            'project' => $project,
            'timeLogs' => $timeLogs,
            'totalHours' => $totalHours,
            'totalEarnings' => $totalEarnings,
            'averageHourlyRate' => $averageHourlyRate,
        ];

        // Generate PDF
        $pdf = PDF::loadView('reports.time-report', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->output();
    }

    private function generateReportTitle(string $groupBy, string $startDate, string $endDate, ?Client $client = null, ?Project $project = null): string
    {
        $title = ucfirst(str_replace('_', ' ', $groupBy)) . ' Time Report';
        
        if ($project) {
            $title .= ' - ' . $project->name;
        } elseif ($client) {
            $title .= ' - ' . $client->name;
        }
        
        $title .= ' (' . Carbon::parse($startDate)->format('M j') . ' - ' . Carbon::parse($endDate)->format('M j, Y') . ')';
        
        return $title;
    }

    public function getUserSummary(User $user, ?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::now()->subDays(30);
        $to = $to ? Carbon::parse($to) : Carbon::now();

        $query = $user->timeLogs()->with('project.client');
        $query->dateRange($from, $to);

        $timeLogs = $query->get();

        return [
            'total_hours' => $timeLogs->sum('hours'),
            'total_earnings' => $timeLogs->sum(function ($log) {
                return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
            }),
            'total_projects' => $timeLogs->groupBy('project_id')->count(),
            'total_clients' => $timeLogs->groupBy('project.client.id')->count(),
            'average_hours_per_day' => $timeLogs->count() > 0 ? round($timeLogs->sum('hours') / $from->diffInDays($to), 2) : 0,
            'most_worked_project' => $this->getMostWorkedProject($timeLogs),
            'recent_activity' => $timeLogs->sortByDesc('start_time')->take(5)->values(),
        ];
    }

    private function getMostWorkedProject(Collection $timeLogs): ?array
    {
        $projectHours = $timeLogs->groupBy('project_id')
            ->map(function ($logs) {
                return [
                    'project' => $logs->first()->project,
                    'total_hours' => $logs->sum('hours'),
                ];
            })
            ->sortByDesc('total_hours')
            ->first();

        return $projectHours ? [
            'name' => $projectHours['project']->name,
            'hours' => $projectHours['total_hours'],
            'client' => $projectHours['project']->client->name,
        ] : null;
    }
}
